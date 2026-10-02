<?php

namespace Tests\Controllers;

use Auditoria;
use Tests\Support\ControllerTestCase;
use Tests\Support\Transaction\TransactsDatabase;

/**
 * Testa a tela de auditoria (GET /index.php/auditoria) e a limpeza de logs
 * (auditoria/clean).
 *
 * O guard de construtor deste controller — sessão exigida, atividade
 * `cAuditoria`, 401 em vez de 403 — já é coberto em
 * {@see \Tests\Controllers\AuthorizationGuardControllerTest}, que o usa como
 * representante da família, então aqui o foco é o comportamento dos dois
 * métodos.
 *
 * O que este arquivo não cobre, e por quê, está em AGENTS.md, na seção
 * "Writing a controller test": a limpeza era fechada por `redirect()` do CI3,
 * que chama `exit()` e mataria o processo do PHPUnit, e agora sai por
 * `respond_redirect()`, que emite o `Location` pelo `CI_Output` sem encerrar o
 * processo.
 *
 * Nenhum teste aqui precisa de reinstall da linha de base: as escritas ficam
 * em `logs` e a transação da trait desfaz tudo, e nenhum outro fixture é
 * alterado.
 */
final class AuditoriaControllerTest extends ControllerTestCase
{
    use TransactsDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Todos os casos passam pelo guard do construtor, então a sessão
        // precisa parecer de quem já entrou; e a sessão também é a fonte do
        // usuário que `log_info()` grava no caminho de sucesso de clean() e do
        // nome que o topo.php do layout mostra.
        $this->loginAs();
        $_SESSION['nome_admin'] = 'Admin';
    }

    public function testIndexRendersTheLogsPage(): void
    {
        $html = $this->callControllerRaw('Auditoria', 'index');

        $this->assertStringContainsString('>Logs</h5>', $html);
        $this->assertStringContainsString('Nenhum registro encontrado.', $html);
        $this->assertStringContainsString('Remover Logs - 30 dias ou mais', $html);
        $this->assertStringContainsString('auditoria/clean', $html);
    }

    public function testIndexListsTheLogRows(): void
    {
        $this->insertLog('Efetuou login no sistema', 'Maria');
        $this->insertLog('Gerou relatório de clientes', 'José');

        $html = $this->callControllerRaw('Auditoria', 'index');

        $this->assertStringContainsString('<td>Maria</td>', $html);
        $this->assertStringContainsString('<td>José</td>', $html);
        $this->assertStringContainsString('<td>Efetuou login no sistema</td>', $html);
        $this->assertStringContainsString('<td>Gerou relatório de clientes</td>', $html);
    }

    /**
     * `tarefa` é um texto gravado pela aplicação, mas a coluna é livre e o guard
     * de permissão é o único filtro: um `<script>` aqui seria executado se a
     * view fizesse `echo` puro.
     */
    public function testIndexEscapesTheLogContent(): void
    {
        $this->insertLog('<script>alert(1)</script>', 'Maria');

        $html = $this->callControllerRaw('Auditoria', 'index');

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    /**
     * Duas renderizações no mesmo caso são o que torna o descarte do retrato
     * velho do Loader verificável; ver AGENTS.md, "The Loader freezes a view
     * snapshot". As duas asserções de linha são o corte: a mais antiga fica na
     * página seguinte, e o deslocamento viria do terceiro segmento da URI, que
     * em processo não existe.
     */
    public function testIndexRendersPaginationReflectingCurrentData(): void
    {
        $this->callControllerRaw('Auditoria', 'index');

        for ($i = 1; $i <= 11; $i++) {
            $this->insertLog("Tarefa {$i}");
        }

        $html = $this->callControllerRaw('Auditoria', 'index');

        $this->assertStringContainsString('<td>Tarefa 11</td>', $html);
        $this->assertStringNotContainsString('<td>Tarefa 1</td>', $html, 'A 11ª linha vazou para a primeira página.');
        $this->assertStringContainsString(
            'pagination',
            $html,
            'O create_links() do pagination não renderizou os links de página.'
        );
    }

    /**
     * A data exata de 30 dias atrás NÃO é removida: o model faz `data <`
     * ('- 30 dias'), estrito. O corte é afirmado por causa do dia, e não por
     * cima, porque trocar o `<` por `<=` não quebraria nenhum outro caso.
     */
    public function testCleanRemovesOnlyLogsOlderThanThirtyDays(): void
    {
        $this->insertLog('Velho 1', 'Maria', 31);
        $this->insertLog('Velho 2', 'Maria', 60);
        $this->insertLog('Limite', 'Maria', 30);
        $this->insertLog('Recente', 'José', 0);

        $this->callControllerRaw('Auditoria', 'clean');

        foreach (['Velho 1', 'Velho 2'] as $gone) {
            $this->assertSame(
                0,
                $this->countLogs($gone),
                'O log com mais de 30 dias deveria ter sido removido.'
            );
        }

        foreach (['Limite', 'Recente'] as $kept) {
            $this->assertSame(
                1,
                $this->countLogs($kept),
                'O log sem mais de 30 dias deveria ter sido mantido.'
            );
        }

        $this->assertStringContainsString(
            Auditoria::CLEAN_SUCCESS_MESSAGE,
            (string) $this->ci()->session->userdata('success')
        );
    }

    /**
     * Uma linha nova com o usuário da sessão é o que torna a limpeza auditável,
     * e é uma linha que o caso anterior não força a existir.
     */
    public function testCleanRecordsTheCleanupInTheAuditLog(): void
    {
        $this->insertLog('Velho', 'Maria', 31);

        $this->callControllerRaw('Auditoria', 'clean');

        $this->assertSame(1, $this->countLogs(Auditoria::CLEAN_AUDIT_TASK));

        $row = $this->ci()->db->where('tarefa', Auditoria::CLEAN_AUDIT_TASK)->get('logs')->row();
        $this->assertSame('Admin', $row->usuario);
    }

    /**
     * O `log_info()` fica fora deste caminho: uma limpeza que não limpou nada
     * não é uma limpeza para registrar.
     */
    public function testCleanWithNothingOldSetsTheErrorFlash(): void
    {
        $this->insertLog('Recente', 'Maria', 0);

        $this->callControllerRaw('Auditoria', 'clean');

        $this->assertSame(
            1,
            $this->countLogs('Recente'),
            'A limpeza não deveria ter apagado um log com menos de 30 dias.'
        );
        $this->assertSame(
            Auditoria::CLEAN_EMPTY_MESSAGE,
            $this->ci()->session->userdata('error')
        );
        $this->assertNull($this->ci()->session->userdata('success'));

        $this->assertSame(
            0,
            $this->countLogs(Auditoria::CLEAN_AUDIT_TASK),
            'Um clean() que não removeu nada não pode registrar uma limpeza.'
        );
    }

    public function testCleanRedirectsBackToTheAuditoriaPage(): void
    {
        $this->insertLog('Velho', 'Maria', 31);

        $this->callControllerRaw('Auditoria', 'clean');

        $this->assertSame(
            site_url('auditoria'),
            $this->ci()->output->get_header('Location')
        );
    }

    /**
     * Sempre a tabela toda por chave, para as asserções sobre o que o clean()
     * removeu e o que manteve não dependam de contagem por diferença, que é
     * como um `count_all()` confunde a remoção com a linha que o próprio
     * clean() registra.
     */
    private function countLogs(string $task): int
    {
        $db = $this->ci()->db;
        $db->where('tarefa', $task);

        return (int) $db->count_all_results('logs');
    }

    /**
     * Sempre uma linha nova, então a ordem de `idLogs` entre inserções é a
     * ordem das chamadas — que é o que o teste de paginação usa. A `hora` é
     * fixa de propósito, para a asserção não depender do relógio.
     */
    private function insertLog(string $task, string $user = 'Admin', int $daysAgo = 0): void
    {
        $this->ci()->db->insert('logs', [
            'usuario' => $user,
            'tarefa' => $task,
            'data' => date('Y-m-d', strtotime("- {$daysAgo} days")),
            'hora' => '12:00:00',
        ]);
    }
}
