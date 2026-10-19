<?php

require_once APPPATH . 'models/Os_model.php';
require_once APPPATH . 'models/Vendas_model.php';

use Libraries\Gateways\BasePaymentGateway;
use Libraries\Gateways\Contracts\PaymentGateway;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Valor da cobrança (#3014): os gateways cobram o mesmo total que a tela da OS
 * e da venda mostra, em vez de recalcular os itens cada um do seu jeito.
 */
final class CobrancaValorTest extends MaposTestCase
{
    private function criarBanco(): void
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, celular TEXT, telefone TEXT, contato TEXT, email TEXT, documento TEXT)');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, telefone TEXT, email TEXT, situacao INTEGER)');
        $this->db->query('CREATE TABLE garantias (idGarantias INTEGER PRIMARY KEY AUTOINCREMENT, refGarantia TEXT, textoGarantia TEXT)');
        $this->db->query('CREATE TABLE os (idOs INTEGER PRIMARY KEY AUTOINCREMENT, clientes_id INTEGER, usuarios_id INTEGER, garantias_id INTEGER, status TEXT, faturado INTEGER DEFAULT 0,
            desconto REAL DEFAULT 0, valor_desconto REAL DEFAULT 0, tipo_desconto TEXT)');
        $this->db->query('CREATE TABLE produtos (idProdutos INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT, precoVenda REAL)');
        $this->db->query('CREATE TABLE servicos (idServicos INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, preco REAL)');
        $this->db->query('CREATE TABLE produtos_os (idProdutos_os INTEGER PRIMARY KEY AUTOINCREMENT, quantidade INTEGER, preco REAL, os_id INTEGER, produtos_id INTEGER, subTotal REAL, desconto REAL DEFAULT 0, tipo_desconto TEXT)');
        $this->db->query('CREATE TABLE servicos_os (idServicos_os INTEGER PRIMARY KEY AUTOINCREMENT, quantidade REAL, preco REAL, os_id INTEGER, servicos_id INTEGER, subTotal REAL, desconto REAL DEFAULT 0, tipo_desconto TEXT)');
        $this->db->query('CREATE TABLE vendas (idVendas INTEGER PRIMARY KEY AUTOINCREMENT, clientes_id INTEGER, usuarios_id INTEGER, lancamentos_id INTEGER, status TEXT,
            desconto REAL DEFAULT 0, valor_desconto REAL DEFAULT 0, tipo_desconto TEXT)');
        $this->db->query('CREATE TABLE itens_de_vendas (idItens INTEGER PRIMARY KEY AUTOINCREMENT, subTotal REAL, quantidade INTEGER, preco REAL, vendas_id INTEGER, produtos_id INTEGER, desconto REAL DEFAULT 0, tipo_desconto TEXT)');
        $this->db->query('CREATE TABLE lancamentos (idLancamentos INTEGER PRIMARY KEY AUTOINCREMENT, vendas_id INTEGER, data_vencimento TEXT)');

        $this->db->query("INSERT INTO clientes (nomeCliente, celular) VALUES ('Ana', '11999998888')");
        $this->db->query("INSERT INTO usuarios (nome, situacao) VALUES ('Técnico', 1)");
        $this->db->query("INSERT INTO produtos (descricao, precoVenda) VALUES ('Tela', 100)");
        $this->db->query("INSERT INTO servicos (nome, preco) VALUES ('Mão de obra', 80)");
    }

    private function gateway(): object
    {
        $os = new class() extends Os_model {
            public function __construct()
            {
            }
        };
        $os->db = $this->db;
        $vendas = new class() extends Vendas_model {
            public function __construct()
            {
            }
        };
        $vendas->db = $this->db;
        $ci = (object) ['Os_model' => $os, 'vendas_model' => $vendas];

        return new class($ci) extends BasePaymentGateway {
            public function __construct(object $ci)
            {
                $this->ci = $ci;
            }

            protected function gerarCobrancaBoleto($id, $tipo)
            {
            }

            protected function gerarCobrancaLink($id, $tipo)
            {
            }

            public function valor(object $entity, string $tipo): float
            {
                return $this->valorDaCobranca($entity, $tipo);
            }

            public function ci(): object
            {
                return $this->ci;
            }
        };
    }

    /**
     * Cria uma OS com os serviços e produtos informados e devolve o que o
     * gateway recebe de findEntity(). Cada serviço é um array de colunas de
     * servicos_os.
     */
    private function os(array $servicos, array $produtos = [], array $colunas = []): object
    {
        $this->db->insert('os', $colunas + ['clientes_id' => 1, 'usuarios_id' => 1, 'status' => 'Aberto']);
        $id = (int) $this->db->insert_id();
        foreach ($servicos as $servico) {
            $this->db->insert('servicos_os', $servico + ['os_id' => $id, 'servicos_id' => 1]);
        }
        foreach ($produtos as $produto) {
            $this->db->insert('produtos_os', $produto + ['os_id' => $id, 'produtos_id' => 1]);
        }

        return $this->gateway()->findEntity($id, PaymentGateway::PAYMENT_TYPE_OS);
    }

    private function valorDaOs(object $entity): float
    {
        return $this->gateway()->valor($entity, PaymentGateway::PAYMENT_TYPE_OS);
    }

    public function testServicoComQuantidadeFracionadaCobraOTotalDaOs(): void
    {
        $this->criarBanco();
        $os = $this->os([['quantidade' => 1.5, 'preco' => 150, 'subTotal' => 225]]);

        $this->assertSame(225.0, $this->valorDaOs($os), 'R$ 150,00 x 1,5; o gateway antigo cobrava 150,00.');
    }

    public function testServicoSemPrecoUsaOPrecoDoCadastro(): void
    {
        $this->criarBanco();
        $os = $this->os([['quantidade' => 1, 'preco' => null, 'subTotal' => null]]);

        $this->assertSame(80.0, $this->valorDaOs($os), 'O cadastro do serviço custa R$ 80,00; o gateway antigo cobrava 0.');
    }

    public function testServicoSemQuantidadeContaUm(): void
    {
        $this->criarBanco();
        $os = $this->os([['quantidade' => null, 'preco' => 100, 'subTotal' => null]]);

        $this->assertSame(100.0, $this->valorDaOs($os));
    }

    public function testSomaProdutosEServicosComoATela(): void
    {
        $this->criarBanco();
        $os = $this->os(
            [['quantidade' => 1.5, 'preco' => 150, 'subTotal' => 225]],
            [['quantidade' => 3, 'preco' => 100, 'subTotal' => 300]]
        );

        $this->assertSame(525.0, $this->valorDaOs($os));
        $this->assertSame(525.0, $this->gateway()->ci()->Os_model->totais($os)['total'], 'Mesmo total da tela da OS.');
    }

    public function testDescontoPorItemEDescontoGeralComoATela(): void
    {
        $this->criarBanco();
        // Serviço de 50 com R$ 20 de desconto (30) + produto de 100 = 130; 10% sobre isso = 117.
        $os = $this->os(
            [['quantidade' => 1, 'preco' => 50, 'subTotal' => 30, 'desconto' => 20, 'tipo_desconto' => 'real']],
            [['quantidade' => 1, 'preco' => 100, 'subTotal' => 100]],
            ['desconto' => 10, 'tipo_desconto' => 'porcento', 'valor_desconto' => 117]
        );

        $this->assertSame(117.0, $this->valorDaOs($os));
    }

    public function testVendaCobraOTotalDaVenda(): void
    {
        $this->criarBanco();
        $this->db->query("INSERT INTO vendas (clientes_id, usuarios_id, status, desconto, valor_desconto, tipo_desconto) VALUES (1, 1, 'Aberto', 10, 270, 'porcento')");
        $this->db->query('INSERT INTO itens_de_vendas (quantidade, preco, vendas_id, produtos_id, subTotal) VALUES (3, 100, 1, 1, 300)');
        $gateway = $this->gateway();
        $venda = $gateway->findEntity(1, PaymentGateway::PAYMENT_TYPE_VENDAS);

        $this->assertSame(270.0, $gateway->valor($venda, PaymentGateway::PAYMENT_TYPE_VENDAS));
    }

    public function testOsSemItensNaoTemValorParaCobrar(): void
    {
        $this->criarBanco();
        $os = $this->os([]);

        $this->assertSame(0.0, $this->valorDaOs($os), 'Os gateways recusam valor menor ou igual a zero.');
    }

    public function testTipoInvalidoLanca(): void
    {
        $this->criarBanco();
        $os = $this->os([['quantidade' => 1, 'preco' => 10, 'subTotal' => 10]]);

        $this->expectException(\Exception::class);
        $this->gateway()->valor($os, 'outro');
    }

    /** @return array<string, array{0: float|string, 1: int}> */
    public static function valoresEmCentavos(): array
    {
        return [
            'inteiro' => [225.0, 22500],
            '1,15 não vira 114' => [1.15, 115],
            '19,99 não vira 1998' => [19.99, 1999],
            '0,29 não vira 28' => [0.29, 29],
            '4,35 não vira 434' => [4.35, 435],
            'texto numérico' => ['8.20', 820],
            'zero' => [0, 0],
        ];
    }

    #[DataProvider('valoresEmCentavos')]
    public function testValorEmCentavosNaoPerdeCentavo(float|string|int $valor, int $centavos): void
    {
        $this->assertSame($centavos, getMoneyAsCents($valor));
    }

    public function testValorEmCentavosRecusaTexto(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        getMoneyAsCents('abc');
    }
}
