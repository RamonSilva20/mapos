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
 * A limpeza era fechada por `redirect()` do CI3, que chama `exit()` e mataria
 * o processo do PHPUnit. O controller agora usa `respond_redirect()` (o mesmo
 * que o `Login::sair()` usa), que emite o `Location` pelo `CI_Output` sem
 * encerrar o processo; o status continua o que o `redirect()` escolheria —
 * 303 num POST — porque `respond_redirect()` resolve o número por
 * `redirect_status_for()`.
 *
 * Nenhum teste aqui precisa de reinstall da linha de base: as escritas ficam
 * em `logs` e a transação da trait desfaz tudo, e nenhum outro fixture é
 * alterado.
 */
class AuditoriaControllerTest extends ControllerTestCase
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

    /**
     * GET auditoria: a tela renderiza, e sem logs traz o estado vazio.
     *
     * Este é o primeiro teste que renderiza o layout inteiro
     * (tema/topo, tema/menu, tema/conteudo, tema/rodape) em processo, e é ele
     * que pegaria um "Cannot redeclare" como o que o guard `function_exists()`
     * de `saudacao()` já resolveu. O descarte do retrato velho do Loader entre
     * controllers não depende desta ordem: ele é pinado de verdade em
     * `testIndexRendersPaginationReflectingCurrentData`, com duas renderizações
     * no mesmo caso.
     */
    public function testIndexRendersTheLogsPage(): void
    {
        $html = $this->callControllerRaw('Auditoria', 'index');

        $this->assertStringContainsString('>Logs</h5>', $html);
        $this->assertStringContainsString('Nenhum registro encontrado.', $html);
        $this->assertStringContainsString('Remover Logs - 30 dias ou mais', $html);
        $this->assertStringContainsString('auditoria/clean', $html);
    }

    /**
     * GET auditoria: as linhas da tabela logs aparecem na tela.
     *
     * A ordem — `order_by('idLogs', 'desc')`, a mais recente primeiro — é
     * fixada em `testIndexRendersPaginationReflectingCurrentData`; aqui basta
     * que as duas linhas existam na página.
     */
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
     * GET auditoria: o conteúdo do log sai escapado.
     *
     * `tarefa` é um texto gravado pela aplicação, mas a coluna é livre e o
     * guard de permissão é o único filtro: qualquer script que entre aqui seria
     * executado se a view fizesse `echo` puro. A view usa `esc()` e este caso
     * fixa que o `<script>` chega como texto.
     */
    public function testIndexEscapesTheLogContent(): void
    {
        $this->insertLog('<script>alert(1)</script>', 'Maria');

        $html = $this->callControllerRaw('Auditoria', 'index');

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    /**
     * GET auditoria: com mais logs que o per_page da configuração, a página
     * corta na décima linha e o pagination renderiza os links — e a segunda
     * renderização reflete os dados de agora, não os do primeiro controller.
     *
     * As duas renderizações no mesmo caso são o que torna o
     * `Ci3Introspection::resetLoaderViewAliases()` verificável de verdade: a
     * primeira congela (em tese) um retrato vazio no Loader, e a segunda lê o
     * `$this->pagination` da view — que apontaria para o retrato velho, com
     * `total_rows` de zero, se o harness não o tivesse descartado. Um teste de
     * uma renderização só pegaria isso se acontecesse de correr depois de um
     * outro que deixasse o retrato no Loader — dependência de ordem, e não uma
     * prova.
     *
     * O conhecido é a ordem e o corte: a primeira inserida (a mais antiga,
     * `idLogs` menor) fica na página seguinte. O deslocamento viria do terceiro
     * segmento da URI, que em processo não existe; aqui o que importa é que a
     * primeira página mostra 10 e o resto aparece no link. O `'pagination'` só
     * existe na página pela `create_links()`, então a presença dele diz que os
     * links foram realmente montados.
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
     * auditoria/clean: remove o que é mais velho que 30 dias e mantém o resto.
     *
     * A data exata de 30 dias atrás NÃO é removida: o model faz `data <`
     * ('- 30 dias'), estrito, então um log com exatamente 30 dias fica. O corte
     * é afirmado por causa do dia, não por cima: é fácil trocar o `<` por `<=`
     * e nenhum teste perceber.
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
     * auditoria/clean: no caminho de sucesso, o próprio ato é registrado.
     *
     * O `log_info()` escreve uma linha nova em `logs`, com o usuário da
     * sessão. É isso que deixa a limpeza auditável — e é uma linha que o caso
     * anterior não força a existir, porque o assert vazio passaria tanto com
     * ela quanto sem ela.
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
     * auditoria/clean: sem logs antigos, nada é apagado e a tela recebe o
     * aviso.
     *
     * O model devolve false quando `affected_rows()` é 0, e o controller
     * responde com o flashdata de erro. O `log_info()` fica fora desse caminho:
     * uma limpeza que não limpou nada não é uma limpeza para registrar.
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

    /**
     * Nos dois caminhos, clean() manda a tela de volta para a auditoria.
     *
     * Antes o `Location` existia, mas saía por `redirect()` do CI3, que chama
     * `exit()` — e era isso que mantinha o fluxo não testável. O status segue o
     * que o `redirect()` escolheria, regra que vive em
     * `redirect_status_for()` e já é coberta em GeneralHelperTest; este caso
     * segura o destino.
     */
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
     * Conta quantas linhas de log têm a tarefa escolhida.
     *
     * Sempre a tabela toda por chave, para as asserções sobre o que o clean()
     * removeu e o que manteve não dependem de contagem verificada por
     * diferença — que é como um `count_all()` sobre restante confunde a
     * remoção com a linha que o próprio clean() registra.
     */
    private function countLogs(string $task): int
    {
        $db = $this->ci()->db;
        $db->where('tarefa', $task);

        return (int) $db->count_all_results('logs');
    }

    /**
     * Insere uma linha de log com a data, o usuário e a tarefa escolhidos.
     *
     * Sempre uma linha nova, então a ordem de `idLogs` entre inserções é a
     * ordem das chamadas — que é o que o teste de paginação usa para saber o
     * que vaza e o que não vaza da primeira página. A `hora` é fixa de
     * propósito, para a asserção não depender do relógio.
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
