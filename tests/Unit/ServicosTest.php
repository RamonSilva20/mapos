<?php

use PHPUnit\Framework\Attributes\DataProvider;

require_once APPPATH . 'models/Servicos_model.php';

/**
 * Serviços migrados (#2841): valor em reais, listagem filtrada e as colunas
 * de preço com centavos (#2938).
 */
final class ServicosTest extends MaposTestCase
{
    public static function valores(): array
    {
        return [
            ['1.234,56', '1234.56'], ['1234,5', '1234.50'], ['1234.56', '1234.56'], ['99,90', '99.90'],
            ['R$ 10,00', '10.00'], ['12', '12.00'], ['1.234.567,89', '1234567.89'], ['0', '0.00'],
            ['99.999.999,99', '99999999.99'], ['100.000.000,00', null], ['999999999', null], ['00012,00', '12.00'],
            ['abc', null], ['-5', null], ['1,234', null], ['', null], [null, null], [['1'], null],
        ];
    }

    #[DataProvider('valores')]
    public function testValorDecimal($digitado, ?string $esperado): void
    {
        $this->assertSame($esperado, valorDecimal($digitado));
    }

    public function testListagemFiltraPorNomeOuDescricao(): void
    {
        $this->db->query('CREATE TABLE servicos (idServicos INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, descricao TEXT, preco REAL)');
        foreach ([['Formatação', 'Windows'], ['Limpeza', 'interna'], ['Troca de tela', 'notebook windows']] as [$nome, $descricao]) {
            $this->db->query('INSERT INTO servicos (nome, descricao, preco) VALUES (?, ?, 10)', [$nome, $descricao]);
        }

        $model = $this->makeInstance(Servicos_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        $this->assertSame(3, $model->contar([]));
        $this->assertSame(2, $model->contar(['pesquisa' => 'windows']));
        $this->assertSame(['Troca de tela', 'Formatação'], array_column($model->listar(['pesquisa' => 'windows'], 10, 0), 'nome'));
        $this->assertSame(['Limpeza'], array_column($model->listar([], 1, 1), 'nome'));
    }

    public function testControllerSegueOsPadroes(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Servicos.php');

        $this->assertStringContainsString("\$this->validarFormulario('servicos')", $codigo);
        $this->assertStringContainsString("valorDecimal(\$this->input->post('preco'))", $codigo);
        $this->assertStringContainsString("'idServicos', (int) \$servico->idServicos", $codigo);
        $this->assertStringNotContainsString("post('idServicos')", $codigo);
        $this->assertSame(2, substr_count($codigo, "\$this->data['legacy_assets'] = false;"));
    }

    /**
     * 'constraint' => 10, 2 vira constraint 10 e um elemento solto: a coluna
     * saía DECIMAL(10), sem centavos.
     */
    public function testMigrationBaseDeclaraDecimalComCentavos(): void
    {
        $base = (string) file_get_contents(APPPATH . 'database/migrations/20121031100537_create_base.php');

        $this->assertDoesNotMatchRegularExpression("/'constraint' => \\d+, \\d+/", $base);
        $this->assertSame(4, substr_count($base, "'constraint' => '10,2'"));

        $correcao = (string) file_get_contents(APPPATH . 'database/migrations/20261009020000_fix_decimal_scale_of_prices.php');
        foreach (['saldo', 'precoCompra', 'precoVenda', "'preco'"] as $coluna) {
            $this->assertStringContainsString($coluna, $correcao);
        }
        $this->assertStringContainsString('DECIMAL(10,2)', $correcao);
    }
}
