<?php

namespace Tests\Controllers;

use Exceptions\Http\AuthenticationRequired;
use Exceptions\Http\AuthorizationDenied;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Test;
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
 * A negação sai de um papel real sem a atividade, e não de um id inexistente. Um
 * id que não existe é o caminho mais curto, e é justamente o que não pode ser
 * usado aqui: `Permission::loadPermission()` faz `count($array)` sobre o
 * `row_array()` de uma linha que não existe, e `row_array()` devolve `null` — o
 * que é um TypeError em PHP 8, não uma negação. Um papel que existe e não tem a
 * atividade é o caso que o código pretende tratar, e é o que a tela encontra.
 *
 * Escrever essa alteração exige sair da transação, por um motivo que é do CI3 e
 * não do teste: o guard lê por uma segunda conexão. Está em `forbidActivity()`.
 *
 * ## O guard de `Auditoria` representa os outros dois
 *
 * `Permissoes` e `Usuarios` repetem o par de `Auditoria` — sessão com papel,
 * atividade específica, 403 com mensagem própria — e não são exercitados aqui
 * por um motivo que é do app e não do teste: os dois são nomes de DUAS classes
 * ao mesmo tempo, o controller e a seed em `database/seeds/`. As duas são
 * globais e o CI3 carrega as duas por caminho de arquivo, então o processo
 * inteiro não consegue ter as duas de uma vez: declarar a segunda é "Cannot
 * redeclare class", e o erro é fatal — mata o PHPUnit no meio de outro arquivo.
 *
 * Um processo de requisição real nunca vê isso, porque `Tools::seed()` carrega
 * seeds e o roteamento carrega controllers, nunca os dois. A suíte in-process é
 * o único lugar do projeto onde os dois convivem, e é por isso que este arquivo
 * não pode declarar `class Usuarios` sem derrubar `LoginControllerTest`, que
 * precisa da seed para instalar os usuários de fixture. Isolar em processo
 * separado seria a saída sem tocar no app — e foi tentado, com
 * `RunInSeparateProcess` por método e `RunTestsInSeparateProcesses` por classe:
 * o PHPUnit aceita os dois atributos, e nenhum dos dois efetivamente forka
 * nesta suíte, o que foi confirmado comparando o PID de um caso isolado com o de
 * um caso vizinho, e os dois saem iguais.
 *
 * Renomear a seed também não é opção: `Seeder::call()` faz `new $seeder` com o
 * nome do arquivo, e `Tools seed Usuarios` é contrato de operação.
 *
 * O que fica de fora, então, são as duas frases específicas: "não tem permissão
 * para configurar as permissões" e "para configurar os usuários". A mecânica do
 * 403 — status, flashdata escrito antes do throw, exceção capturada, e o fato de
 * não ser um 401 — está coberta; o que falta é a constância de que cada
 * controller continua com a sua frase, e isso é uma comparação de string contra
 * o código, não um teste de comportamento. Se as duas frases divergirem, o
 * usuário vê a mensagem de outro acesso, e o caminho para fechar essa lacuna é
 * o teste de fronteira, com um login de verdade e o `Location` do 403.
 *
 * Limite honesto: o flashdata, a mensagem e o status são conferidos; o destino do
 * redirect não, porque `redirect()` encerra o processo. O destino é uma função
 * pura testada em GeneralHelperTest, e o header `Location` que sai na vida real
 * é o teste de fronteira.
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
     * entrou. A sessão também precisa estar viva: o guard testa `session_id()`
     * antes de `userdata('logado')`, e o setUp() garante a sessão aberta.
     */
    #[Test]
    public function testRequestWithoutSessionRequiresAuthentication(): void
    {
        $this->expectException(AuthenticationRequired::class);

        $this->constructController('Auditoria');
    }

    /**
     * Sem sessão, a exceção é 401 — e não 403.
     *
     * O motivo de a exceção carregar status: quem responde JSON precisa do
     * número, e o número é o que separa "não entrei" de "entrei e não posso".
     * Uma família sem status serviria para a tela, onde tudo vira redirect, e
     * quebraria a API — que é o único lugar onde o 401 e o 403 não se confundem.
     */
    #[Test]
    public function testMissingSessionCarriesTheUnauthorizedStatus(): void
    {
        $this->assertSame(401, (new AuthenticationRequired())->status());
        $this->assertSame(403, (new AuthorizationDenied())->status());
    }

    /**
     * Um papel sem a atividade recebe 403, não 401.
     *
     * Confirma os dois lados da distinção de uma vez: a sessão existe, então não é
     * `AuthenticationRequired`, e o status é o do `AuthorizationDenied`. Um
     * `assertInstanceOf(HttpException::class)` passaria aqui e não diria nada.
     */
    #[Test]
    public function testSessionWithoutTheActivityIsDeniedRatherThanUnauthenticated(): void
    {
        $this->loginAs(self::ADMIN_PERMISSION);
        $this->forbidActivity(self::AUDITORIA_ACTIVITY);

        $caught = $this->catchDenial('Auditoria');

        $this->assertInstanceOf(AuthorizationDenied::class, $caught);
        $this->assertNotInstanceOf(AuthenticationRequired::class, $caught);
        $this->assertSame(403, $caught->status());
    }

    /**
     * O guard nega, e a negação carrega a mesma frase que ele põe no flashdata.
     *
     * São dois usos de uma frase só: o flashdata para a tela, a mensagem para a
     * API, que não tem flashdata. O caso afirma que as duas não se
     * desincronizaram — se um dia divergirem, quem usa a tela e quem consome a
     * API recebem textos diferentes para a mesma falha.
     */
    #[Test]
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
     * Com a atividade, o controller é construído e nada é lançado.
     *
     * O caso que impede a família de virar um filtro que barra todo mundo: sem
     * ele, uma exceção que respondesse 403 sempre passaria esta suíte inteira e
     * ninguém notaria — o sintoma seria a tela de permissões vazia para o
     * administrador, longe de um teste vermelho.
     */
    #[Test]
    public function testSessionWithTheRequiredPermissionBuildsTheController(): void
    {
        $this->loginAs(self::ADMIN_PERMISSION);

        $controller = $this->constructController('Auditoria');

        $this->assertInstanceOf('Auditoria', $controller);
    }

    /**
     * Constrói o controller esperando a negação e devolve a exceção.
     *
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
     * Tira uma atividade do papel administrativo, escrevendo no banco.
     *
     * A concessão é lida do seed e devolvida em `tearDown()`. Um id de papel
     * inexistente pareceria mais curto, e é justamente o que não serve: o
     * `Permission::loadPermission()` faz `count()` sobre o `row_array()` de uma
     * linha que não existe, e `row_array()` devolve `null` — TypeError em PHP 8,
     * não negação. Um papel que existe e não tem a atividade é o caso que o
     * código pretende tratar.
     *
     * ## Por que isto escreve com autocommit, sem TransactsDatabase
     *
     * Porque o guard lê por uma conexão que NÃO é a da transação do caso.
     * `CI_Controller::get_instance()` devolve por referência, então
     * `Permission::$this->CI` acompanha `CI_Controller::$instance`, que durante a
     * construção aponta para o controller que está nascendo — e esse controller
     * ainda não tem a propriedade `db`, então a guarda do `Loader::database()`
     * falha e ele abre uma segunda conexão. É por essa conexão que o guard
     * consulta, e ela está fora da transação: um UPDATE dela é invisível para o
     * guard, que volta a ler a concessão completa e autoriza.
     *
     * Por isso esta classe não abre transação. Uma transação aqui daria a
     * aparência de isolamento sem dar isolamento, que é pior do que não usar.
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
     * O PHPUnit chama o `tearDown()` mesmo quando o caso falhou, então isto
     * roda também no caminho do `fail()`. Sem o `originalGrants` guardado, um
     * caso que morre antes de chegar na escrita não tem o que restaurar — e
     * nesse caso também não escreveu nada, que é o motivo de a guarda existir.
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
