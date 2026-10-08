<?php

use PHPUnit\Framework\Attributes\DataProvider;

require_once APPPATH . 'models/Clientes_model.php';

/**
 * Ficha do cliente (#2841): status em pill-status, formatação e as consultas
 * de OS e vendas do cliente.
 */
final class ClienteFichaTest extends MaposTestCase
{
    public static function statusDeOs(): array
    {
        return [
            ['Orçamento', 'neutral'], ['Negociação', 'warning'], ['Aberto', 'info'], ['Aprovado', 'info'],
            ['Em Andamento', 'progress'], ['Aguardando Peças', 'warning'], ['Finalizado', 'success'],
            ['Faturado', 'success'], ['Cancelado', 'danger'],
        ];
    }

    #[DataProvider('statusDeOs')]
    public function testStatusDaOsViraPillComAPalavra(string $status, string $variante): void
    {
        $this->assertSame(['label' => $status, 'variant' => $variante], osStatusPill($status));
    }

    public function testStatusDesconhecidoOuVazio(): void
    {
        $this->assertSame(['label' => 'Garantia', 'variant' => 'neutral'], osStatusPill('Garantia'));
        $this->assertSame(['label' => 'Sem status', 'variant' => 'neutral'], osStatusPill(null));
        // Nenhum status usa o laranja (primary), reservado às ações.
        $this->assertNotContains('primary', OS_STATUS_VARIANTES);
    }

    public function testVendaFaturadaDinheiroEData(): void
    {
        $this->assertSame('success', vendaFaturadaPill('1')['variant']);
        $this->assertSame('Em aberto', vendaFaturadaPill(0)['label']);
        $this->assertSame('R$ 1.234,50', dinheiro('1234.5'));
        $this->assertSame('R$ 0,00', dinheiro(null));
        $this->assertSame('08/10/2026', dataBr('2026-10-08'));
        $this->assertSame('08/10/2026', dataBr('2026-10-08 14:00:00'));
        $this->assertSame('', dataBr('0000-00-00'));
        $this->assertSame('', dataBr(null));
    }

    public function testOsEVendasDoCliente(): void
    {
        $this->db->query('CREATE TABLE os (idOs INTEGER PRIMARY KEY AUTOINCREMENT, clientes_id INTEGER, status TEXT)');
        $this->db->query('CREATE TABLE vendas (idVendas INTEGER PRIMARY KEY AUTOINCREMENT, clientes_id INTEGER)');
        foreach ([1, 1, 2, 1] as $cliente) {
            $this->db->query('INSERT INTO os (clientes_id, status) VALUES (?, ?)', [$cliente, 'Aberto']);
            $this->db->query('INSERT INTO vendas (clientes_id) VALUES (?)', [$cliente]);
        }

        $model = $this->makeInstance(Clientes_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        $this->assertSame(3, $model->contarOsDoCliente(1));
        $this->assertSame(1, $model->contarVendasDoCliente(2));
        $this->assertSame([4, 2], array_map('intval', array_column($model->osDoCliente(1, 2), 'idOs')));
        $this->assertSame([4, 2, 1], array_map('intval', array_column($model->vendasDoCliente(1, 10), 'idVendas')));
    }

    /**
     * As ações da aba de vendas seguem as permissões de vendas (a v4 conferia
     * vOs/eOs), e a aba vem de uma lista fechada.
     */
    public function testFichaUsaAsPermissoesCertas(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Clientes.php');
        preg_match('/public function visualizar\(\).*?\n    }\n/s', $codigo, $trecho);

        $this->assertNotEmpty($trecho);
        $this->assertStringContainsString("'ver_venda' => \$this->permite('vVenda')", $trecho[0]);
        $this->assertStringContainsString("'editar_venda' => \$this->permite('eVenda')", $trecho[0]);
        $this->assertStringContainsString("['aba' => ['dados', 'os', 'vendas']]", $trecho[0]);
        $this->assertStringContainsString("\$this->data['legacy_assets'] = false;", $trecho[0]);
    }
}
