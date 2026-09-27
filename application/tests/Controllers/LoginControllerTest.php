<?php

namespace Tests\Controllers;

use Tests\Support\ControllerTestCase;
use Tests\Support\TransactsDatabase;

/**
 * Testa o controller Login inteiro: a tela (GET /index.php/login), o endpoint
 * de autenticação (POST /index.php/login/verificarLogin) e o logout
 * (GET /index.php/login/sair).
 *
 * As credenciais vêm das fixtures criadas por tests/bin/setup-db.php:
 * admin@admin.com / 123456 (ativo), inativo@admin.com (situacao 0) e
 * expirado@admin.com (dataExpiracao no passado).
 *
 * O __construct() não tem teste próprio: ele só carrega o model, e cada teste
 * aqui já o exercita ao construir o controller.
 *
 * Limites honestos desta classe, todos por consequência do in-process:
 *  - a rejeição de um token CSRF inválido não é coberta. Security::csrf_verify
 *    chama show_error(403) no caminho inválido, e em testing isso encerra o
 *    processo, o que mataria o PHPUnit. Só o caminho de aceitação é verificado,
 *    em testAcceptsValidCsrfToken.
 *  - sem servidor HTTP não há como checar 404 de CSS/JS, nem se o jquery-validate
 *    carrega, nem layout. O que se garante é que o PHP da view renderiza sem
 *    erro e entrega o formulário com o token.
 *  - o Whoops converte warning em exceção, então um problema de PHP na view
 *    aborta o teste. testRendersWithoutPhpErrors é a rede extra para o que for
 *    impresso em vez de lançado.
 *
 * Esta classe renderiza a tela várias vezes no mesmo processo, e é isso que
 * cobre a regressão do function_exists() em saudacao(), na view: sem o guard, o
 * CI3 dá include de novo e a segunda renderização morre com "Cannot redeclare".
 * Não precisa de um teste dedicado para isso.
 *
 * Uma armadilha que os testes de logout precisam conhecer: o sess_destroy() do
 * CI3 chama session_destroy(), que destrói a sessão persistente mas NÃO limpa o
 * array $_SESSION do processo. Logo depois do logout, userdata('logado')
 * continua devolvendo true aqui dentro. A deslogagem só fica visível na
 * próxima requisição, e é por isso que os testes reabrem a sessão antes de
 * conferir. Afirmar logado logo após o logout daria um falso positivo.
 */
class LoginControllerTest extends ControllerTestCase
{
    use TransactsDatabase;

    /**
     * Pede a reinstateção da linha de base antes de cada caso.
     *
     * A transação sozinha não dá conta desta classe: ela mexe no
     * `dataExpiracao` das contas para cobrir o chk_date() do Login, e como uma
     * alteração de coluna é um UPDATE comum a transação a desfaz. O que
     * precisava mais era o outro sentido — o primeiro caso da execução anterior
     * deixa a conta como estava, e qualquer estado que sobre de um teste que
     * passou pelo caminho errado chegaria aqui sem ninguém reclamar.
     */
    protected function resetsBaselineData(): bool
    {
        return true;
    }

    /**
     * POST login/verificarLogin: autentica e preenche a sessão.
     */
    public function testAuthenticatesWithValidCredentials(): void
    {
        $this->postLogin('admin@admin.com', '123456');

        $response = $this->callController('Login', 'verificarLogin');

        $this->assertTrue($response['result']);

        $session = $this->ci()->session;
        $this->assertTrue($session->userdata('logado'));

        // assertEquals, e não assertSame: id_admin e permissao vem de uma
        // coluna MySQL, então o mysqli entrega string e a comparação estrita
        // com int reprovaria mesmo com o valor certo.
        $this->assertEquals(1, $session->userdata('id_admin'));
        $this->assertEquals(1, $session->userdata('permissao'));
        // 'Admin', e não 'Administrador': o nome vem da seed
        // application/database/seeds/Usuarios.php, que é a mesma do Tools::seed()
        // em produção. Afirmar o valor antigo fixaria a suíte a uma cópia
        // descartada do fixture.
        $this->assertSame('Admin', $session->userdata('nome_admin'));
        $this->assertSame('admin@admin.com', $session->userdata('email_admin'));
    }

    public function testRecordsTheAccessInTheAuditLog(): void
    {
        $db = $this->ci()->db;
        $db->where('tarefa', 'Efetuou login no sistema');
        $before = (int) $db->count_all_results('logs');

        $this->postLogin('admin@admin.com', '123456');
        $this->callController('Login', 'verificarLogin');

        $this->assertSame($before + 1, (int) $db->count_all_results('logs'));
    }

    public function testRejectsWrongPasswordWithAGenericMessage(): void
    {
        $this->postLogin('admin@admin.com', 'senha-errada');

        $response = $this->callController('Login', 'verificarLogin');

        $this->assertFalse($response['result']);
        $this->assertSame('Os dados de acesso estão incorretos.', $response['message']);
        $this->assertNull($this->ci()->session->userdata('logado'));
    }

    public function testDoesNotRevealWhetherTheEmailExists(): void
    {
        $this->postLogin('admin@admin.com', 'senha-errada');
        $senhaErrada = $this->callController('Login', 'verificarLogin');

        $this->postLogin('nao-existe@admin.com', 'senha-errada');
        $emailInexistente = $this->callController('Login', 'verificarLogin');

        $this->assertSame(
            $senhaErrada['message'],
            $emailInexistente['message'],
            'E-mail inexistente e senha errada precisam devolver a mesma mensagem, senão a resposta revela quais e-mails têm conta.'
        );
    }

    public function testRejectsInactiveUser(): void
    {
        $this->postLogin('inativo@admin.com', '123456');

        $response = $this->callController('Login', 'verificarLogin');

        $this->assertFalse($response['result']);
        $this->assertSame('Os dados de acesso estão incorretos.', $response['message']);
        $this->assertNull($this->ci()->session->userdata('logado'));
    }

    public function testRejectsExpiredAccount(): void
    {
        $this->postLogin('expirado@admin.com', '123456');

        $response = $this->callController('Login', 'verificarLogin');

        $this->assertFalse($response['result']);
        $this->assertSame(
            'A conta do usuário está expirada, por favor entre em contato com o administrador do sistema.',
            $response['message']
        );
        $this->assertNull($this->ci()->session->userdata('logado'));
    }

    public function testReturnsRenewedTokenInErrorResponses(): void
    {
        $this->postLogin('admin@admin.com', 'senha-errada');

        $response = $this->callController('Login', 'verificarLogin');

        $this->assertArrayHasKey('MAPOS_TOKEN', $response);
        $this->assertNotEmpty($response['MAPOS_TOKEN']);
    }

    public function testRejectsIncompletePayload(): void
    {
        $this->postLogin('', '');

        $response = $this->callController('Login', 'verificarLogin');

        $this->assertFalse($response['result']);
        $this->assertNotEmpty($response['message']);
        $this->assertNull($this->ci()->session->userdata('logado'));
    }

    /**
     * Regressão: sem corpo de POST a resposta precisa dizer o que falta.
     *
     * Antes isto passava pelo Form_validation, e o caso do bug original é mais
     * amplo: Form_validation::set_rules() volta sem fazer nada quando o método
     * da requisição não é POST (Form_validation.php:172), então o run() devolvia
     * FALSE sem nenhum erro registrado e o controller respondia com "message"
     * vazio. A validação agora é explícita no controller, sem a armadilha do
     * set_rules().
     *
     * A mensagem também é texto puro, e não o HTML do validation_errors(): a
     * view escreve com .text(), então <p>...</p> apareceria como tag visível.
     */
    public function testReportsMissingFieldsWithoutAPostBody(): void
    {
        $response = $this->callController('Login', 'verificarLogin');

        $this->assertFalse($response['result']);
        $this->assertSame('Preencha o e-mail e a senha.', $response['message']);
        $this->assertStringNotContainsString('<', $response['message']);
        $this->assertNull($this->ci()->session->userdata('logado'));
    }

    public function testRejectsAMalformedEmail(): void
    {
        $this->postLogin('nao-e-email', '123456');

        $response = $this->callController('Login', 'verificarLogin');

        $this->assertFalse($response['result']);
        $this->assertSame('Insira um e-mail válido.', $response['message']);
    }

    /**
     * Toda falha devolve MAPOS_TOKEN, não só a de credenciais.
     *
     * A view renova o token do formulário a partir deste campo depois de cada
     * tentativa. Um caminho de falha sem ele deixaria o formulário com o token
     * velho, e a tentativa seguinte bateria no 403 do csrf_verify().
     */
    public function testEveryFailurePathReturnsAFreshCsrfToken(): void
    {
        $casos = [
            'sem corpo' => static function (): void {
            },
            'e-mail inválido' => function (): void {
                $this->postLogin('nao-e-email', '123456');
            },
            'senha vazia' => function (): void {
                $this->postLogin('admin@admin.com', '');
            },
            'usuário inativo' => function (): void {
                $this->postLogin('inativo@admin.com', '123456');
            },
            'conta expirada' => function (): void {
                $this->postLogin('expirado@admin.com', '123456');
            },
            'senha errada' => function (): void {
                $this->postLogin('admin@admin.com', 'errada');
            },
        ];

        foreach ($casos as $descricao => $prepara) {
            $this->resetApplicationState();
            $prepara();

            $response = $this->callController('Login', 'verificarLogin');

            $this->assertFalse($response['result'], "{$descricao} deveria falhar.");
            $this->assertNotEmpty(
                $response['MAPOS_TOKEN'] ?? null,
                "{$descricao} não devolveu MAPOS_TOKEN."
            );
            $this->assertNotEmpty($response['message'], "{$descricao} devolveu mensagem vazia.");
        }
    }

    public function testAcceptsValidCsrfToken(): void
    {
        $this->postLogin('admin@admin.com', '123456');

        $tokenName = $this->ci()->security->get_csrf_token_name();

        $this->ignoringCliHeaderWarnings(function (): void {
            $this->ci()->security->csrf_verify();
        });

        $this->assertFalse(
            isset($_POST[$tokenName]),
            'O CI3 deve remover o token de $_POST depois de verificar.'
        );
    }

    /**
     * GET login: smoke test da tela. Sem ela, uma view quebrada, um <form>
     * perdido ou um campo de CSRF removido passariam despercebidos, porque
     * nenhum teste exercise Login::index().
     */
    public function testRendersTheLoginPage(): void
    {
        $html = $this->callControllerRaw('Login', 'index');

        $this->assertNotSame('', trim($html), 'A view mapos/login.php não devolveu nada.');
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('id="formLogin"', $html);
        $this->assertStringContainsString('method="post"', $html);
    }

    public function testRendersTheCredentialFields(): void
    {
        $html = $this->callControllerRaw('Login', 'index');

        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="senha"', $html);
        $this->assertStringContainsString('type="password"', $html, 'A senha não pode renderizar como texto visível.');
    }

    /**
     * O campo de CSRF é o elo entre a tela e o endpoint: se o nome do input
     * deixar de bater com o csrf_token_name, o POST volta recusado por
     * csrf_verify() e ninguém consegue entrar.
     */
    public function testRendersTheCsrfFieldTheEndpointExpects(): void
    {
        $html = $this->callControllerRaw('Login', 'index');
        $tokenName = $this->ci()->security->get_csrf_token_name();

        $this->assertStringContainsString(
            'name="' . $tokenName . '"',
            $html,
            "A view não renderizou o input de CSRF com o nome {$tokenName} que o endpoint espera."
        );

        $this->assertMatchesRegularExpression(
            '/name="' . preg_quote($tokenName, '/') . '" value="[a-f0-9]{32,}"/i',
            $html,
            'O campo de CSRF veio sem hash.'
        );
    }

    public function testPostsToTheLoginEndpoint(): void
    {
        $html = $this->callControllerRaw('Login', 'index');

        $this->assertStringContainsString('login/verificarLogin', $html);
        $this->assertStringContainsString(
            'login/verificarLogin?ajax=true',
            $html,
            'O JavaScript do formulário precisa bater no endpoint ajax.'
        );
    }

    public function testRendersWithoutPhpErrors(): void
    {
        $html = $this->callControllerRaw('Login', 'index');

        foreach (['Fatal error', 'Parse error', 'Whoops', 'Undefined ', 'Warning:', 'Notice:', 'Deprecated:'] as $marker) {
            $this->assertStringNotContainsString($marker, $html, "A página saiu com '{$marker}' no corpo.");
        }
    }

    /**
     * A mensagem de erro do form vem do flashdata, que é alimentado por
     * conteúdo que o usuário chegou a enviar. Precisa sair escapada, senão
     * qualquer ponto de entrada que chegue nela vira XSS armazenado.
     */
    public function testEscapesTheFlashMessage(): void
    {
        $payload = '<script>alert(1)</script>';

        $this->ci()->session->set_flashdata('error', $payload);

        $html = $this->callControllerRaw('Login', 'index');

        $this->assertStringNotContainsString($payload, $html, 'O flashdata entrou na página sem escapar.');
        $this->assertStringContainsString('&lt;script&gt;', $html, 'O flashdata deveria aparecer escapado.');
    }

    /**
     * GET login/sair: o logout precisa derrubar a sessão.
     */
    public function testDestroysTheSessionOnLogout(): void
    {
        $this->login();

        $this->assertTrue($this->ci()->session->userdata('logado'), 'O login não deixou a sessão ativa.');

        $this->callControllerRaw('Login', 'sair');

        $this->assertSame(
            PHP_SESSION_NONE,
            session_status(),
            'sair() deveria ter destruído a sessão nativa.'
        );
    }

    public function testLeavesNoLoggedInUserForTheNextRequest(): void
    {
        $this->login();
        $this->callControllerRaw('Login', 'sair');

        // Simula a requisição seguinte: quem abrir sessão nova tem de estar
        // deslogado.
        session_start();

        $this->assertNull(
            $this->ci()->session->userdata('logado'),
            'Uma sessão aberta depois do logout não pode vir com o usuário logado.'
        );
        $this->assertNull(
            $this->ci()->session->userdata('id_admin'),
            'O id_admin não pode sobreviver ao logout.'
        );
    }

    public function testRedirectsToTheLoginPageOnLogout(): void
    {
        $this->callControllerRaw('Login', 'sair');

        $this->assertSame(
            site_url('login'),
            $this->ci()->output->get_header('Location'),
            'O logout precisa mandar o usuário de volta para a tela de login.'
        );
    }

    /**
     * Regressão de open redirect.
     *
     * O Referer é controlado pelo cliente. Se o logout redirecionasse para ele,
     * qualquer terceiro montaria um link que manda o usuário sair do Map-OS e
     * cair num site dele.
     */
    public function testIgnoresTheRefererWhenRedirecting(): void
    {
        $_SERVER['HTTP_REFERER'] = 'https://evil.example/pagina';

        $this->callControllerRaw('Login', 'sair');

        $location = (string) $this->ci()->output->get_header('Location');

        $this->assertSame(site_url('login'), $location, 'O logout seguiu o Referer do cliente.');
        $this->assertStringNotContainsString('evil.example', $location);
    }

    public function testWorksWhenNobodyIsLoggedIn(): void
    {
        $location = $this->callControllerRaw('Login', 'sair');

        $this->assertSame('', $location, 'Um logout sem sessão não deveria devolver corpo.');
        $this->assertSame(
            site_url('login'),
            $this->ci()->output->get_header('Location'),
            'Um logout sem sessão ainda precisa redirecionar para o login.'
        );
    }

    /**
     * Os cabeçalhos de CORS saem pelo CI_Output, e não pelo header() do PHP.
     *
     * Dois motivos, e o teste existe para o segundo:
     *
     *  - o Output só emite os cabeçalhos no fim da requisição, então o header sai
     *    no lugar certo, e o status 200 do cabeçalho não é afetado;
     *  - header() do PHP reclama em CLI, e o Whoops transforma o aviso em
     *    exceção. A suíte envolve a chamada do controller em
     *    ignoringCliHeaderWarnings(), o que faria uma volta ao header() passar
     *    despercebida. Afirmar que os cabeçalhos estão no CI_Output é o que
     *    segura essa regressão: um header() cru não aparece aqui.
     */
    public function testSetsTheCorsHeadersThroughTheOutput(): void
    {
        $this->postLogin('admin@admin.com', 'errada');

        $this->callController('Login', 'verificarLogin');

        $output = $this->ci()->output;

        $this->assertSame(base_url(), $output->get_header('Access-Control-Allow-Origin'));
        $this->assertSame('POST, GET, OPTIONS', $output->get_header('Access-Control-Allow-Methods'));
        $this->assertSame('1000', $output->get_header('Access-Control-Max-Age'));
        $this->assertSame('Content-Type', $output->get_header('Access-Control-Allow-Headers'));
    }

    /**
     * Uma conta sem dataExpiracao não pode ser bloqueada.
     *
     * dataExpiracao é date DEFAULT NULL, então null é um valor legítimo: conta
     * sem expiração. O chk_date() antigo fazia new DateTime(null), que é
     * depreciado no PHP 8.1 e viraria erro sob failOnDeprecation.
     *
     * O null é gravado direto, sem salvar e restaurar o valor anterior. É a
     * TransactsDatabase que desfaz a alteração no fim do caso, e o ganho não é
     * só de linhas: o `finally` que fazia a restauração manual era pulado por
     * qualquer `fail()` antes dele, e aí o `dataExpiracao` ficava null para os
     * testes seguintes — um vazamento que só apareceria como um "conta
     * expirada" inexplicável em outro caso.
     */
    public function testAnAccountWithoutAnExpirationDateCanLogIn(): void
    {
        $this->ci()->db->where('email', 'admin@admin.com')->update('usuarios', ['dataExpiracao' => null]);

        $this->postLogin('admin@admin.com', '123456');

        $response = $this->callController('Login', 'verificarLogin');

        $this->assertTrue($response['result'], 'Uma conta sem expiração deveria entrar.');
        $this->assertTrue($this->ci()->session->userdata('logado'));
    }

    /**
     * O redirect do logout precisa continuar sendo o que o CI3 escolheria.
     *
     * respond_redirect() existe só para não chamar exit(), e o exit() era a
     * única diferença em relação ao redirect() do helper url. Se o cálculo do
     * status divergir, o navegador passa a tratar o logout como outra coisa:
     * 307/303 preservam o método do POST, 302 não.
     *
     * A regra é verificada em redirect_status_for() e não pelo controller,
     * porque o status sai por header() nativo e em CLI não há como lê-lo: o
     * CI_Output não expõe getter e xdebug_get_headers() não existe.
     */
    public function testTheRedirectStatusFollowsTheCi3Rule(): void
    {
        // O que o redirect() do CI3 faria para cada caso.
        $this->assertSame(307, redirect_status_for('GET', 'HTTP/1.1'));
        $this->assertSame(303, redirect_status_for('POST', 'HTTP/1.1'));
        $this->assertSame(303, redirect_status_for('DELETE', 'HTTP/1.1'));

        // Fora de HTTP/1.1 não há informação confiável, e o CI3 cai em 302.
        $this->assertSame(302, redirect_status_for('POST', 'HTTP/1.0'));
        $this->assertSame(302, redirect_status_for('POST', null));
        $this->assertSame(302, redirect_status_for(null, 'HTTP/1.1'));
    }

    /**
     * Faz login de verdade, para que sair() tenha o que destruir.
     */
    private function login(): void
    {
        $this->postLogin('admin@admin.com', '123456');
        $this->callController('Login', 'verificarLogin');
    }
}
