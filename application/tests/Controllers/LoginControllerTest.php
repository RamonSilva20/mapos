<?php

namespace Tests\Controllers;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ControllerTestCase;
use Tests\Support\Transaction\TransactsDatabase;

/**
 * Testa o controller Login inteiro: a tela (GET /index.php/login), o endpoint
 * de autenticação (POST /index.php/login/verificarLogin) e o logout
 * (GET /index.php/login/sair).
 *
 * As credenciais vêm das fixtures criadas por tests/bin/setup-db.php:
 * admin@admin.com / 123456 (ativo), inativo@admin.com (situacao 0) e
 * expirado@admin.com (dataExpiracao no passado).
 *
 * O que este arquivo não cobre, e por quê, está em AGENTS.md, na seção
 * "Writing a controller test": um token CSRF inválido não é coberto, porque
 * `Security::csrf_verify()` chama `show_error(403)` e isso encerra o processo do
 * PHPUnit.
 *
 * Esta classe renderiza a tela várias vezes no mesmo processo, e é isso que cobre
 * a regressão do `function_exists()` em `saudacao()`, na view: sem o guard, o CI3
 * dá include de novo e a segunda renderização morre com "Cannot redeclare".
 *
 * Uma armadilha que os testes de logout precisam conhecer: o `sess_destroy()` do
 * CI3 não limpa o array `$_SESSION` do processo, então logo depois do logout
 * `userdata('logado')` continua devolvendo `true`. A deslogagem só fica visível na
 * requisição seguinte, e é por isso que o caso reabre a sessão antes de conferir.
 */
final class LoginControllerTest extends ControllerTestCase
{
    use TransactsDatabase;

    /**
     * A transação sozinha não dá conta desta classe: ela mexe no
     * `dataExpiracao` das contas para cobrir o chk_date() do Login.
     */
    protected function resetsBaselineData(): bool
    {
        return true;
    }

    /**
     * @return array<string, array{?string, string, string}>
     */
    public static function rejectedRequests(): array
    {
        return [
            'sem corpo de POST' => [null, null, 'Preencha o e-mail e a senha.'],
            'e-mail e senha vazios' => ['', '', 'Preencha o e-mail e a senha.'],
            'e-mail inválido' => ['nao-e-email', '123456', 'Insira um e-mail válido.'],
            'senha vazia' => ['admin@admin.com', '', 'Preencha o e-mail e a senha.'],
            'senha errada' => ['admin@admin.com', 'errada', 'Os dados de acesso estão incorretos.'],
            'e-mail inexistente' => ['nao-existe@admin.com', 'errada', 'Os dados de acesso estão incorretos.'],
            'usuário inativo' => ['inativo@admin.com', '123456', 'Os dados de acesso estão incorretos.'],
            'conta expirada' => ['expirado@admin.com', '123456', 'A conta do usuário está expirada, por favor entre em contato com o administrador do sistema.'],
        ];
    }

    /**
     * Toda recusa devolve a mesma coisa: falha, uma mensagem, um token novo e
     * ninguém logado.
     *
     * O token não é um detalhe do caminho de credenciais: a view renova o token
     * do formulário a partir deste campo depois de cada tentativa, então um
     * caminho de falha sem ele deixaria o formulário com o token velho e a
     * tentativa seguinte bateria no 403 do `csrf_verify()`.
     */
    #[DataProvider('rejectedRequests')]
    public function testVerificarLoginRejectsTheRequest(
        ?string $email,
        ?string $password,
        string $expectedMessage
    ): void {
        // `null` é "nenhum corpo de POST", que é um defeito diferente de "corpo
        // com campos vazios": `Form_validation::set_rules()` volta sem fazer nada
        // quando o método da requisição não é POST, então o `run()` devolvia FALSE
        // sem nenhum erro registrado e o controller respondia com "message" vazio.
        if ($email !== null) {
            $this->postLogin($email, (string) $password);
        }

        $response = $this->callController('Login', 'verificarLogin');

        $this->assertFalse($response['result']);
        $this->assertSame($expectedMessage, $response['message']);
        $this->assertStringNotContainsString('<', $response['message'], 'A view escreve a mensagem com .text(), então HTML apareceria como tag.');
        $this->assertNotEmpty($response['MAPOS_TOKEN'] ?? null);
        $this->assertNull($this->ci()->session->userdata('logado'));
    }

    public function testVerificarLoginAuthenticatesValidCredentials(): void
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
        // em produção.
        $this->assertSame('Admin', $session->userdata('nome_admin'));
        $this->assertSame('admin@admin.com', $session->userdata('email_admin'));

        $db = $this->ci()->db;
        $db->where('tarefa', 'Efetuou login no sistema');
        $this->assertSame(1, (int) $db->count_all_results('logs'), 'O login deveria ter deixado um registro de auditoria.');
    }

    /**
     * E-mail inexistente e senha errada precisam devolver a mesma mensagem, senão
     * a resposta revela quais e-mails têm conta.
     *
     * Fica fora do provider acima de propósito: uma propriedade de segurança que
     * só pode ficar vermelha como "data set 5 de 8" não vai ser encontrada por
     * quem a quebrou.
     */
    public function testVerificarLoginDoesNotRevealWhetherTheEmailExists(): void
    {
        $this->postLogin('admin@admin.com', 'senha-errada');
        $wrongPassword = $this->callController('Login', 'verificarLogin');

        $this->postLogin('nao-existe@admin.com', 'senha-errada');
        $unknownEmail = $this->callController('Login', 'verificarLogin');

        $this->assertSame(
            $wrongPassword['message'],
            $unknownEmail['message'],
            'E-mail inexistente e senha errada precisam devolver a mesma mensagem.'
        );
    }

    /**
     * `dataExpiracao` é date DEFAULT NULL, então null é um valor legítimo: conta
     * sem expiração. O chk_date() antigo fazia new DateTime(null).
     */
    public function testVerificarLoginAcceptsAnAccountWithoutAnExpirationDate(): void
    {
        $this->ci()->db->where('email', 'admin@admin.com')->update('usuarios', ['dataExpiracao' => null]);

        $this->postLogin('admin@admin.com', '123456');

        $response = $this->callController('Login', 'verificarLogin');

        $this->assertTrue($response['result'], 'Uma conta sem expiração deveria entrar.');
        $this->assertTrue($this->ci()->session->userdata('logado'));
    }

    public function testVerificarLoginAcceptsAValidCsrfToken(): void
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
     * Os cabeçalhos de CORS saem pelo CI_Output, e não pelo header() do PHP. O
     * teste existe para o segundo motivo: a suíte envolve a chamada do controller
     * em `ignoringCliHeaderWarnings()`, o que faria uma volta ao header() passar
     * despercebida, e um header() cru não aparece no CI_Output.
     */
    public function testVerificarLoginSetsTheCorsHeadersThroughTheOutput(): void
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
     * O campo de CSRF é o elo entre a tela e o endpoint: se o nome do input
     * deixar de bater com o `csrf_token_name`, o POST volta recusado e ninguém
     * consegue entrar.
     */
    public function testIndexRendersTheLoginPage(): void
    {
        $html = $this->callControllerRaw('Login', 'index');

        $this->assertNotSame('', trim($html), 'A view mapos/login.php não devolveu nada.');
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('id="formLogin"', $html);
        $this->assertStringContainsString('method="post"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="senha"', $html);
        $this->assertStringContainsString('type="password"', $html, 'A senha não pode renderizar como texto visível.');
        $this->assertStringContainsString('login/verificarLogin', $html);
        $this->assertStringContainsString(
            'login/verificarLogin?ajax=true',
            $html,
            'O JavaScript do formulário precisa bater no endpoint ajax.'
        );

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

        foreach (['Fatal error', 'Parse error', 'Whoops', 'Undefined ', 'Warning:', 'Notice:', 'Deprecated:'] as $marker) {
            $this->assertStringNotContainsString($marker, $html, "A página saiu com '{$marker}' no corpo.");
        }
    }

    /**
     * A mensagem de erro do form vem do flashdata, que é alimentado por conteúdo
     * que o usuário chegou a enviar.
     */
    public function testIndexEscapesTheFlashMessage(): void
    {
        $payload = '<script>alert(1)</script>';

        $this->ci()->session->set_flashdata('error', $payload);

        $html = $this->callControllerRaw('Login', 'index');

        $this->assertStringNotContainsString($payload, $html, 'O flashdata entrou na página sem escapar.');
        $this->assertStringContainsString('&lt;script&gt;', $html, 'O flashdata deveria aparecer escapado.');
    }

    public function testSairDestroysTheSession(): void
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

    public function testSairLeavesNoLoggedInUserForTheNextRequest(): void
    {
        $this->login();
        $this->callControllerRaw('Login', 'sair');

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

    public function testSairRedirectsToTheLoginPage(): void
    {
        $this->callControllerRaw('Login', 'sair');

        $this->assertSame(
            site_url('login'),
            $this->ci()->output->get_header('Location'),
            'O logout precisa mandar o usuário de volta para a tela de login.'
        );
    }

    /**
     * O Referer é controlado pelo cliente. Se o logout redirecionasse para ele,
     * qualquer terceiro montaria um link que manda o usuário sair do Map-OS e
     * cair num site dele.
     */
    public function testSairIgnoresTheRefererWhenRedirecting(): void
    {
        $_SERVER['HTTP_REFERER'] = 'https://evil.example/pagina';

        $this->callControllerRaw('Login', 'sair');

        $location = (string) $this->ci()->output->get_header('Location');

        $this->assertSame(site_url('login'), $location, 'O logout seguiu o Referer do cliente.');
        $this->assertStringNotContainsString('evil.example', $location);
    }

    public function testSairWorksWhenNobodyIsLoggedIn(): void
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
     * Faz login de verdade, para que sair() tenha o que destruir.
     */
    private function login(): void
    {
        $this->postLogin('admin@admin.com', '123456');
        $this->callController('Login', 'verificarLogin');
    }
}
