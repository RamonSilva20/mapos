<?php

namespace Tests\Support;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

/**
 * Prova a reinstateção da linha de base, o opt-in da TransactsDatabase.
 *
 * O TransactsDatabaseTest prova que a transação descarta. Isto prova a outra
 * metade, que só existe porque o schema passou a ser reaproveitado: a suíte não
 * derruba mais o banco entre execuções, então o estado que sobra de uma execução
 * precisa ser descartado por outro mecanismo. Quem faz isso é a reinstateção da
 * linha de base, e é o que impede que um teste dependa, sem saber, do que um
 * teste anterior deixou.
 *
 * Esta classe é a única que pede a reinstateção de propósito, e é por isso que
 * vive num arquivo próprio: o TransactsDatabaseTest precisa NÃO pedir, porque o
 * par de casos dele é a prova de que o rollback acontece, e uma reinstateção
 * limpando `logs` entre os dois transformaria essa prova em tautologia.
 *
 * ## O que este arquivo não consegue provar, e por quê
 *
 * A primeira versão deste arquivo sujava a linha de base no primeiro caso e
 * conferia no seguinte que a sujeira não tinha chegado. Não provava nada: com a
 * reinstateção DESLIGADA de propósito, a suíte continuava verde. O rollback do
 * caso anterior já desfaz a sujeira, então o segundo caso enxerga a linha de base
 * de qualquer jeito, e um par de casos que passa com o recurso desligado não
 * está testando o recurso.
 *
 * A regra que sai daí é mais geral que este arquivo: em uma suíte onde tudo roda
 * dentro de transação, o estado dentro da execução é invariante e não distingue
 * nenhuma-feature de feature-desligada. O que a reinstateção de fato protege é o
 * estado que chega de FORA — de uma execução anterior, ou de um caminho que não
 * passa por transação. Trazer sujeira desse lugar para dentro de um caso exige
 * uma segunda conexão, e aqui esbarra no isolamento:
 *
 *   - O MySQL está em REPEATABLE READ, então o snapshot da transação fecha no
 *     primeiro SELECT. Uma linha commitada por outra conexão depois do setUp
 *     simplesmente não aparece, e o teste passaria sem ver a sujeira que ele
 *     acabou de plantar. Um teste que planta sujeira que não consegue ver é
 *     pior do que nenhum teste, porque parece estar cobrindo a reinstateção.
 *
 * Por isso os casos abaixo atacam a reinstateção diretamente, chamando o que
 * ela faz, e deixam a sujeira dentro da transação do próprio caso — onde ela é
 * visível e some no rollback. E o guard de `logs`, que é a única coisa que
 * distingue "descartou" de "commitou", é testado pelo sintoma que ele produziria.
 */
final class BaselineDataResetTest extends TestCase
{
    use TransactsDatabase;

    /**
     * Esta classe é a que pede a reinstateção.
     *
     * Um método sobrescrito, e não uma propriedade: o PHP trata a colisão entre
     * uma propriedade de trait e uma da classe como definições incompatíveis e
     * mata o processo, mesmo com os tipos iguais, porque os padrões divergem.
     */
    protected function resetsBaselineData(): bool
    {
        return true;
    }

    /**
     * O gancho rodou, e a transação que o instalou continua aberta.
     *
     * As duas afirmações juntas são o contrato do setUp: a reinstateção deixou a
     * linha de base no estado das fixtures (3 usuários) e a transação que a trait
     * abriu ainda está aberta (depth 1, autocommit desligado).
     */
    public function testTheBaselineIsTheFixtureStateWhenTheTestBodyRuns(): void
    {
        $db = get_instance()->db;

        $this->assertSame(
            1,
            $this->transactionDepth($db),
            'O gancho deveria ter aberto a transação do caso, e a reinstateção deveria ter rodado dentro dela.'
        );

        $this->assertSame(
            0,
            $this->autocommit(),
            'A reinstateção não pode rodar fora da transação: um DELETE commitado escapa do rollback do caso.'
        );

        $this->assertSame(
            3,
            (int) $db->count_all_results('usuarios'),
            'A reinstateção deveria ter deixado os três usuários das fixtures antes do corpo do caso.'
        );

        $this->assertSame(
            14,
            (int) $db->count_all_results('configuracoes'),
            'A reinstateção não pode tocar em `configuracoes`: 13 linhas vêm da seed e a `email_automatico` vem de uma migration, '
            . 'e nenhuma seed recria a última.'
        );
    }

    /**
     * A reinstateção apaga o que sobrou e devolve as três contas das fixtures.
     *
     * Este é o caso que pega a reinstateção desligada, e ele existe porque o par
     * de casos não pega. A sujeira é gravada aqui dentro, pelo mesmo `db` da
     * trait, e então a reinstateção é chamada diretamente. Nada de segunda
     * conexão: sob REPEATABLE READ ela não veria a própria escrita, e a
     * reinstalação abortaria com 1062 em `usuarios`.PRIMARY.
     *
     * Os quatro jeitos de a reinstateção quebrar, e o que cada um faria aqui:
     * não rodar (4 usuários, em vez de 3), apagar sem reinstalar (0), instalar
     * sem apagar (1062 da seed, exception), e reinstalar as contas erradas (a
     * conferida pelo e-mail logo abaixo).
     */
    public function testTheBaselineResetDeletesAndReinstallsTheFixtureUsers(): void
    {
        $db = get_instance()->db;

        $db->insert('usuarios', [
            'nome' => 'Sujo',
            'rg' => 'MG-25.502.561',
            'cpf' => '517.565.356-40',
            'cep' => '01024-900',
            'rua' => 'R. Cantareira',
            'numero' => '306',
            'bairro' => 'Centro Histórico de São Paulo',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'email' => 'sujo@admin.com',
            'senha' => 'irrelevante',
            'telefone' => '0000-0000',
            'celular' => '',
            'situacao' => 1,
            'dataCadastro' => '2018-09-09',
            'permissoes_id' => 1,
        ]);

        $this->assertSame(4, (int) $db->count_all_results('usuarios'), 'A sujeira plantada deveria estar visível antes da reinstateção.');

        $this->chamarReinstalacao();

        $this->assertSame(
            3,
            (int) $db->count_all_results('usuarios'),
            'A reinstateção deveria ter apagado o usuário estranho e devolvido as três contas das fixtures.'
        );

        $this->assertSame(
            0,
            (int) $db->where('email', 'sujo@admin.com')->count_all_results('usuarios'),
            'O usuário estranho sobreviveu, então o DELETE da reinstateção não rodou.'
        );

        // E as três contas são as de verdade, com a expiração que os testes de
        // login dependem. Um DELETE sem reinstall, ou um reinstall de outra
        // fonte, deixaria a tabela vazia ou com as contas erradas.
        $emails = $db->select('email')
            ->from('usuarios')
            ->order_by('idUsuarios', 'ASC')
            ->get()
            ->result_array();

        $this->assertSame(
            ['admin@admin.com', 'inativo@admin.com', 'expirado@admin.com'],
            array_column($emails, 'email'),
            'A reinstateção devolveu um conjunto de contas diferente do das fixtures.'
        );
    }

    /**
     * A reinstateção se recusa a rodar com `logs` suja, e diz como resolver.
     *
     * A linha é gravada DENTRO da transação do caso, e não por outra conexão,
     * pelas mesmas razões de REPEATABLE READ do caso acima. O guard é a mesma
     * contagem nos dois casos — o que muda é o MySQL decidir quando enxerga o
     * que está commitado por fora, e isso não é o guard.
     *
     * `logs` é a única tabela que sobra vazia quando a transação descarta, e é por
     * isso que ela é a escolha: uma linha aqui é indistinguível de um commit.
     */
    public function testTheBaselineResetRefusesToRunWithALoggedLeak(): void
    {
        get_instance()->db->insert('logs', [
            'usuario' => 'harness',
            'tarefa' => 'BaselineDataResetTest',
            'data' => '2026-01-01',
            'hora' => '00:00:00',
            'ip' => '127.0.0.1',
        ]);

        $falha = $this->chamarReinstalacao();

        $this->assertNotNull(
            $falha,
            'A reinstateção rodou com uma linha em `logs`, que é a assinatura de uma transação commitada em vez de descartada.'
        );

        $this->assertStringContainsString('logs', $falha->getMessage(), 'A falha deveria apontar a tabela.');
        $this->assertStringContainsString(
            'test:fresh',
            $falha->getMessage(),
            'A falha deveria dizer como recuperar, senão quem a encontra não sabe para onde ir.'
        );
    }

    /**
     * Chama a reinstateção e devolve a falha, ou null se ela passou.
     *
     * Closure amarrada ao escopo do caso, a mesma técnica que a trait usa para o
     * `_trans_depth` do driver: o método é privado e Reflection seria mais frágil
     * que isso. Chamar direto é necessário porque o `setUp` do PHPUnit não tem como
     * ser testado do corpo de um caso, e a falha de um setUp não é capturável.
     */
    private function chamarReinstalacao(): ?AssertionFailedError
    {
        try {
            (function (): void {
                $this->resetBaselineData(get_instance()->db);
            })->call($this);
        } catch (AssertionFailedError $e) {
            return $e;
        }

        return null;
    }

    /**
     * O autocommit da conexão, que é como o CI3 marca a transação aberta.
     */
    private function autocommit(): int
    {
        $row = get_instance()->db->query('SELECT CAST(@@autocommit AS UNSIGNED) AS autocommit')->row_array();

        return (int) ($row['autocommit'] ?? 1);
    }

    /**
     * Lê o `_trans_depth`, que é protected. Ver TransactsDatabase.
     */
    private function transactionDepth(object $db): int
    {
        return (function (): int {
            return $this->_trans_depth;
        })->call($db);
    }
}
