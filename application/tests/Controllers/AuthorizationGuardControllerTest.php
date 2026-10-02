<?php

namespace Tests\Controllers;

use Exceptions\Http\AuthenticationRequired;
use Exceptions\Http\AuthorizationDenied;
use PHPUnit\Framework\Attributes\After;
use Tests\Support\ControllerTestCase;

/**
 * Os guards que impedem um controller de `MY_Controller` de existir sem sessão
 * ou sem permissão.
 *
 * Antes desta suíte, nenhum deles tinha teste, e a razão é a mesma para todos: o
 * guard chamava `redirect()`, e `redirect()` chama `exit()`. Um `exit()` no meio
 * de um processo de PHPUnit acaba com ele — sem summary, sem asserção, sem dizer
 * qual caso morreu. Trocar o `exit()` por uma exceção foi o que deixou estes
 * controllers testáveis, e o que este arquivo confere é que a troca não mudou o
 * que o servidor responde. Essa metade é medida em
 * {@see \Tests\Controllers\FrontendBoundaryTest}, com um processo filho, porque
 * `header()` não existe em CLI.
 *
 * O `new` abaixo não é um atalho para ler um estado: é o construtor do controller
 * rodando de verdade, guard incluído. Por isso os casos que esperam negação
 * chamam `constructController()` e não `callControllerRaw()` — o guard lança
 * antes de qualquer método, e escolher um método só para ter o que chamar seria
 * escolher às cegas, num controller que grava no banco.
 *
 * `Permissoes` e `Usuarios` repetem o par de `Auditoria` e não são exercitados
 * aqui, por um motivo que é do app e não do teste; o porquê inteiro está em
 * AGENTS.md, em "A name can be two classes at once". O que fica de fora são as
 * duas frases específicas de cada controller — a constância delas é uma
 * comparação de string contra o código, não um teste de comportamento.
 *
 * O status que distingue 401 de 403 é de `HttpException` e é testado em
 * {@see \Tests\Helpers\GeneralHelperTest}.
 */
final class AuthorizationGuardControllerTest extends ControllerTestCase
{
    /**
     * O papel administrativo do seed, que tem todas as atividades ligadas.
     */
    private const ADMIN_PERMISSION = 1;

    /**
     * A atividade que o guard de `Auditoria` exige, e a que este arquivo tira
     * para exercitar a negação.
     */
    private const AUDITORIA_ACTIVITY = 'cAuditoria';

    private const AUDITORIA_MESSAGE = 'Você não tem permissão para visualizar logs do sistema.';

    /**
     * A concessão original do papel, para devolver o fixture ao estado em que
     * estava. Ver `restoreGrants()`.
     */
    private ?string $originalGrants = null;

    /**
     * Sem sessão, `MY_Controller` exige autenticação antes de olhar permissão.
     *
     * `Auditoria` serve porque tem os dois guards: o de sessão vem de
     * `parent::__construct()` e o de permissão vem logo depois. Se a ordem
     * invertesse, este caso receberia um 403 — a exceção errada para quem nem
     * entrou.
     */
    public function testConstructorWithoutSessionRequiresAuthentication(): void
    {
        $this->expectException(AuthenticationRequired::class);

        $this->constructController('Auditoria');
    }

    /**
     * Confirma os dois lados da distinção de uma vez: a sessão existe, então não é
     * `AuthenticationRequired`, e o status é o do `AuthorizationDenied`. Um
     * `assertInstanceOf(HttpException::class)` passaria aqui e não diria nada.
     */
    public function testConstructorDeniesASessionWithoutTheActivity(): void
    {
        $this->loginAs(self::ADMIN_PERMISSION);
        $this->forbidActivity(self::AUDITORIA_ACTIVITY);

        $caught = $this->catchDenial('Auditoria');

        $this->assertInstanceOf(AuthorizationDenied::class, $caught);
        $this->assertNotInstanceOf(AuthenticationRequired::class, $caught);
        $this->assertSame(403, $caught->status());
    }

    /**
     * São dois usos de uma frase só: o flashdata para a tela, a mensagem para a
     * API, que não tem flashdata. O caso afirma que as duas não se
     * desincronizaram.
     */
    public function testDenialKeepsTheFlashdataAndCarriesTheSameMessage(): void
    {
        $this->loginAs(self::ADMIN_PERMISSION);
        $this->forbidActivity(self::AUDITORIA_ACTIVITY);

        $caught = $this->catchDenial('Auditoria');

        $this->assertSame(self::AUDITORIA_MESSAGE, $caught->getMessage());
        $this->assertSame(
            self::AUDITORIA_MESSAGE,
            $this->ci()->session->userdata('error'),
            'O flashdata do guard foi perdido: ele é escrito antes do throw e precisa sobreviver a ele.'
        );
    }

    /**
     * O caso que impede a família de virar um filtro que barra todo mundo: sem
     * ele, uma exceção que respondesse 403 sempre passaria esta suíte inteira e
     * ninguém notaria.
     */
    public function testConstructorWithTheActivityBuildsTheController(): void
    {
        $this->loginAs(self::ADMIN_PERMISSION);

        $controller = $this->constructController('Auditoria');

        $this->assertInstanceOf('Auditoria', $controller);
    }

    /**
     * Um helper, e não um `expectException()`, porque o caso precisa da exceção
     * depois de capturada: mensagem, status e flashdata são afirmações sobre o
     * objeto, e `expectException()` só dá acesso ao tipo.
     */
    private function catchDenial(string $controller): AuthorizationDenied
    {
        try {
            $this->constructController($controller);
        } catch (AuthorizationDenied $e) {
            return $e;
        }

        $this->fail("{$controller} não lançou AuthorizationDenied para um papel sem a atividade.");
    }

    /**
     * ## Por que isto escreve com autocommit, sem TransactsDatabase
     *
     * Porque o guard lê por uma conexão que NÃO é a da transação do caso. Por
     * isso esta classe não abre transação: uma transação aqui daria a aparência
     * de isolamento sem dar isolamento, que é pior do que não usar. O porquê do
     * CI3 está em AGENTS.md, em "The guard reads through a second connection".
     *
     * O risco de escrever fora de transação é o estado vazado, e ele é
     * endereçado dos dois lados: `tearDown()` devolve a string original, e
     * `forbidActivity()` afirma que a atividade existia. Se um caso morrer no
     * meio, a próxima execução falha nesta afirmação, com o nome da atividade,
     * em vez de ficar negando (ou autorizando) em silêncio.
     */
    private function forbidActivity(string $activity): void
    {
        $raw = (string) $this->ci()->db
            ->select('permissoes')
            ->where('idPermissao', self::ADMIN_PERMISSION)
            ->get('permissoes')
            ->row('permissoes');

        $grants = json_decode_legacy($raw);

        $this->assertIsArray(
            $grants,
            'A coluna permissoes do papel administrativo não deserializou: o formato do fixture mudou.'
        );

        $this->assertArrayHasKey(
            $activity,
            $grants,
            "O papel administrativo não tem a atividade {$activity}. Ou o fixture mudou, ou um caso anterior vazou a alteração deste fixture e não restaurou — o tearDown() deste arquivo é o que deveria ter evitado isso."
        );

        $this->originalGrants = $raw;

        unset($grants[$activity]);

        $this->ci()->db
            ->where('idPermissao', self::ADMIN_PERMISSION)
            ->update('permissoes', ['permissoes' => serialize($grants)]);
    }

    /**
     * Devolve a concessão do papel ao valor lido antes da negação.
     *
     * O PHPUnit chama o `tearDown()` mesmo quando o caso falhou, então isto roda
     * também no caminho do `fail()`. Sem o `originalGrants` guardado, um caso que
     * morre antes de chegar na escrita não tem o que restaurar — e nesse caso
     * também não escreveu nada, que é o motivo da guarda existir.
     */
    #[After]
    public function restoreGrants(): void
    {
        if ($this->originalGrants === null) {
            return;
        }

        $this->ci()->db
            ->where('idPermissao', self::ADMIN_PERMISSION)
            ->update('permissoes', ['permissoes' => $this->originalGrants]);

        $this->originalGrants = null;
    }
}
