<?php

namespace Tests\Controllers;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ControllerTestCase;
use Tests\Support\Transaction\TransactsDatabase;

/**
 * Testa o fluxo de recuperação de senha da área do cliente: o pedido do token
 * (`gerarTokenResetarSenha`), a tela que o link do e-mail abre
 * (`verifyTokenSenha`), a gravação da senha nova (`senhaSalvar`), o login
 * (`login`) e a autorização de uma OS (`visualizarOs`).
 *
 * Os dados vêm de fixtures criadas aqui dentro do caso: os três usuários que o
 * `tests/bin/setup-db.php` instala são da tela administrativa, e esta área é a
 * do cliente, que vive na tabela `clientes`. A tabela fica de fora da
 * reinstalação de linha de base de propósito — ela mexe só em `usuarios`, e
 * `clientes`, `resets_de_senha` e `email_queue` já são descartadas pela
 * transação do caso.
 *
 * O que este arquivo não cobre, e por quê, está em AGENTS.md, na seção
 * "Writing a controller test": um token CSRF inválido não é coberto, porque
 * `Security::csrf_verify()` chama `show_error(403)` e isso encerra o processo do
 * PHPUnit. O caminho aceito do CSRF é o que toda chamada POST aqui exercita.
 */
final class MineControllerTest extends ControllerTestCase
{
    use TransactsDatabase;

    private const INVALID_TOKEN_MESSAGE = 'Token inválido ou expirado. Solicite uma nova recuperação de senha.';

    /**
     * @return array<string, array{?string, string}>
     */
    public static function missingTokens(): array
    {
        return [
            // `null` é "nenhum corpo de POST", que é um defeito diferente de
            // "corpo com o campo vazio": sem corpo o `input->post('token')` é
            // null, e com corpo vazio é a string vazia. Os dois caem na mesma
            // guarda, e é o mesmo destino.
            'sem corpo de POST' => [null, 'sem-corpo'],
            'token vazio' => ['', 'vazio'],
        ];
    }

    #[DataProvider('missingTokens')]
    public function testSenhaSalvarRedirectsWhenTheTokenIsMissing(?string $token, string $_cenario): void
    {
        $email = $this->installCliente('cliente@exemplo.com');
        $antes = $this->senhaDoCliente($email);

        $this->postWithCsrfToken(['senha' => 'nova-senha']);

        if ($token !== null) {
            $_POST['token'] = $token;
        }

        $this->callControllerRaw('Mine', 'senhaSalvar');

        $this->assertSame(
            site_url('mine'),
            $this->ci()->output->get_header('Location'),
            'Sem token não há o que salvar, então o cliente volta para a tela de entrada.'
        );
        $this->assertSame($antes, $this->senhaDoCliente($email), 'A senha não pode mudar sem token.');
    }

    /**
     * As cinco formas de recusa do `senhaSalvar`, que são o mesmo caminho com
     * entradas diferentes.
     *
     * `estado` descreve o token e `senha` o campo enviado; a última coluna é a
     * mensagem exata. Todas as linhas usam o mesmo par token/senha válido, que
     * vem do cenário 'valido' e é o que qualquer um dos outros quatro quebra.
     *
     * O token expirado é de dois dias atrás porque o `validateDate()` só
     * considera vencido o que já passou de um dia inteiro (`days >= 1`); o
     * cenário válido, com a expiração em agora, é a afirmação do outro lado
     * desse mesmo corte.
     *
     * @return array<string, array{string, ?string, string}>
     */
    public static function rejectedRequests(): array
    {
        return [
            'senha vazia' => ['valido', '', 'Por favor digite uma senha'],
            'token inexistente' => ['inexistente', 'nova-senha', self::INVALID_TOKEN_MESSAGE],
            'token já utilizado' => ['utilizado', 'nova-senha', self::INVALID_TOKEN_MESSAGE],
            'token expirado' => ['expirado', 'nova-senha', self::INVALID_TOKEN_MESSAGE],
            'token de cliente inexistente' => ['sem-cliente', 'nova-senha', self::INVALID_TOKEN_MESSAGE],
        ];
    }

    #[DataProvider('rejectedRequests')]
    public function testSenhaSalvarRejectsTheRequest(
        string $estado,
        ?string $senha,
        string $expectedMessage
    ): void {
        $email = $this->installCliente('cliente@exemplo.com');
        $antes = $this->senhaDoCliente($email);
        $token = $this->tokenPara($estado, $email);

        $this->postWithCsrfToken(['token' => $token, 'senha' => $senha]);

        $response = $this->callController('Mine', 'senhaSalvar');

        $this->assertFalse($response['result']);
        $this->assertSame($expectedMessage, $response['message']);
        $this->assertStringNotContainsString('<', $response['message'], 'A tela escreve a mensagem com Swal .text(), então HTML apareceria como tag.');
        $this->assertSame($antes, $this->senhaDoCliente($email), 'Uma senha recusada não pode ser gravada.');
    }

    /**
     * Token inexistente, já utilizado e expirado precisam devolver a mesma
     * mensagem, senão a resposta diz quais tokens já foram emitidos.
     *
     * Fica fora do provider acima de propósito: uma propriedade de segurança que
     * só pode ficar vermelha como "data set 3 de 5" não vai ser encontrada por
     * quem a quebrou.
     */
    public function testSenhaSalvarDoesNotRevealWhetherTheTokenExists(): void
    {
        $email = $this->installCliente('cliente@exemplo.com');
        $antes = $this->senhaDoCliente($email);

        $messages = [];

        foreach (['inexistente', 'utilizado', 'expirado'] as $estado) {
            $this->postWithCsrfToken([
                'token' => $this->tokenPara($estado, $email),
                'senha' => 'nova-senha',
            ]);

            $messages[$estado] = $this->callController('Mine', 'senhaSalvar')['message'];
        }

        $this->assertSame(
            [$messages['inexistente'], $messages['inexistente']],
            [$messages['utilizado'], $messages['expirado']],
            'Token inexistente, utilizado e expirado precisam devolver a mesma mensagem.'
        );
        $this->assertSame($antes, $this->senhaDoCliente($email));
    }

    public function testSenhaSalvarChangesThePasswordAndMarksTheTokenUsed(): void
    {
        $email = $this->installCliente('cliente@exemplo.com');
        $this->installResetToken($email, 'token-valido-abc123');

        $this->postWithCsrfToken(['token' => 'token-valido-abc123', 'senha' => 'nova-senha']);

        $response = $this->callController('Mine', 'senhaSalvar');

        $this->assertTrue($response['result']);

        $this->assertTrue(
            password_verify('nova-senha', (string) $this->senhaDoCliente($email)),
            'A senha nova deveria estar gravada.'
        );
        $this->assertSame(
            '1',
            (string) $this->estadoDoToken('token-valido-abc123')['token_utilizado'],
            'O token precisa ser marcado como utilizado, senão o mesmo link serve para trocar a senha de novo.'
        );

        // O `senhaSalvar` deixa o cliente logado com o nome, e não com
        // `conectado`: é a sessão que a tela `conecte/painel` exige, e ela
        // continua negando acesso a quem não passou por aqui.
        $this->assertSame('Cliente Teste', $this->ci()->session->userdata('nome'));
        $this->assertNull($this->ci()->session->userdata('conectado'));
    }

    /**
     * Um e-mail sem cadastro precisa receber a mesma resposta de um e-mail com
     * cadastro, e não criar nada: senão a tela de recuperação diz se a conta
     * existe.
     *
     * A comparação é entre as duas requisições de verdade, com o mesmo texto de
     * e-mail, e não contra uma string escrita aqui — que quebraria junto com a
     * tela.
     */
    public function testGerarTokenResetarSenhaAnswersTheSameForAnUnknownEmail(): void
    {
        $this->installEmitente();

        $email = 'ninguem@exemplo.com';

        $this->postWithCsrfToken(['email' => $email]);
        $this->callControllerRaw('Mine', 'gerarTokenResetarSenha');
        $semConta = $this->ci()->session->userdata('success');

        $this->installCliente($email, 'Cliente Com Conta');

        $this->postWithCsrfToken(['email' => $email]);
        $this->callControllerRaw('Mine', 'gerarTokenResetarSenha');
        $comConta = $this->ci()->session->userdata('success');

        $this->assertNotEmpty($semConta);
        $this->assertSame(
            $comConta,
            $semConta,
            'Um e-mail sem cadastro e um com cadastro precisam receber a mesma resposta.'
        );
    }

    public function testGerarTokenResetarSenhaStoresTheTokenAndQueuesTheEmail(): void
    {
        $this->installEmitente();

        $email = $this->installCliente('cliente@exemplo.com');

        $this->postWithCsrfToken(['email' => $email]);

        $this->callControllerRaw('Mine', 'gerarTokenResetarSenha');

        $reset = $this->ci()->db->from('resets_de_senha')->get()->row();

        $this->assertNotNull($reset, 'A solicitação de um cliente existente deveria gravar o token.');
        $this->assertSame($email, $reset->email);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{32}$/', $reset->token);

        // `token_utilizado` não é enviada pelo controller: quem dá o 0 é o
        // DEFAULT da coluna, e é o que a migration 20261005120000 instalou.
        $this->assertSame('0', (string) $reset->token_utilizado);

        $queued = $this->ci()->db->from('email_queue')->get()->row();

        $this->assertNotNull($queued, 'O e-mail com o link de recuperação deveria entrar na fila.');
        $this->assertSame($email, $queued->to);
        $this->assertSame('pending', $queued->status);
        $this->assertStringContainsString(
            'index.php/mine/verifyTokenSenha/token/' . $reset->token,
            (string) $queued->message,
            'O e-mail precisa carregar o link com o token que foi gravado.'
        );
    }

    /**
     * Sem emitente não há remetente, e um e-mail de recuperação sai com o
     * `From` vazio. O aviso existe para o administrador cadastrar o emitente, e
     * nada pode entrar na fila.
     */
    public function testGerarTokenResetarSenhaReportsTheMissingIssuer(): void
    {
        $email = $this->installCliente('cliente@exemplo.com');

        $this->postWithCsrfToken(['email' => $email]);

        $this->callControllerRaw('Mine', 'gerarTokenResetarSenha');

        $this->assertSame(
            'Cadastrar Emitente.' . "\n\n" . ' Por favor contate o administrador do sistema.',
            $this->ci()->session->userdata('error')
        );
        $this->assertSame(
            0,
            $this->ci()->db->count_all_results('email_queue'),
            'Sem emitente não pode entrar e-mail na fila.'
        );
    }

    public function testVerifyTokenSenhaRendersTheFormForAValidToken(): void
    {
        $email = $this->installCliente('cliente@exemplo.com');
        $this->installResetToken($email, 'token-valido-abc123');

        $this->resetUriSegments('mine/verifyTokenSenha/token/token-valido-abc123');

        $html = $this->callControllerRaw('Mine', 'verifyTokenSenha');

        $this->assertNotSame('', trim($html), 'A tela de nova senha não devolveu nada.');
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('index.php/mine/senhaSalvar', $html);
        $this->assertStringContainsString(
            'var token = "token-valido-abc123"',
            $html,
            'A tela precisa mandar ao endpoint o token que veio do link do e-mail.'
        );

        foreach (['Fatal error', 'Parse error', 'Whoops', 'Warning:', 'Notice:', 'Deprecated:'] as $marker) {
            $this->assertStringNotContainsString($marker, $html, "A página saiu com '{$marker}' no corpo.");
        }
    }

    public function testVerifyTokenSenhaRedirectsForAnExpiredToken(): void
    {
        $email = $this->installCliente('cliente@exemplo.com');
        $this->installResetToken($email, 'token-expirado', -2);

        $this->resetUriSegments('mine/verifyTokenSenha/token/token-expirado');

        $this->callControllerRaw('Mine', 'verifyTokenSenha');

        $this->assertSame(
            base_url() . 'index.php/mine',
            $this->ci()->output->get_header('Location'),
            'Um token vencido não pode abrir a tela de nova senha.'
        );
        $this->assertSame('Token inválido ou expirado', $this->ci()->session->userdata('error'));
    }

    /**
     * E-mail inexistente e senha errada precisam devolver a mesma mensagem, senão
     * a resposta revela quais e-mails têm conta na área do cliente.
     *
     * Fica fora do provider de recusas de propósito: uma propriedade de
     * segurança que só pode ficar vermelha como "data set 3 de 5" não vai ser
     * encontrada por quem a quebrou.
     */
    public function testLoginAnswersTheSameMessageForAnUnknownEmailAndAWrongPassword(): void
    {
        $email = $this->installCliente('cliente@exemplo.com');

        $this->postWithCsrfToken(['email' => $email, 'senha' => 'senha-errada']);
        $senhaErrada = $this->callController('Mine', 'login');

        $this->postWithCsrfToken(['email' => 'ninguem@exemplo.com', 'senha' => 'senha-errada']);
        $emailInexistente = $this->callController('Mine', 'login');

        $this->assertSame(
            $senhaErrada['message'],
            $emailInexistente['message'],
            'E-mail inexistente e senha errada precisam devolver a mesma mensagem.'
        );

        $this->assertNotEmpty($senhaErrada['MAPOS_TOKEN'] ?? null);
        $this->assertNotEmpty($emailInexistente['MAPOS_TOKEN'] ?? null);
        $this->assertNull($this->ci()->session->userdata('conectado'));
    }

    /**
     * O `MAPOS_TOKEN` da área do cliente não é detalhe do caminho de credenciais:
     * a tela renova o campo de CSRF do formulário a partir dele depois de cada
     * tentativa, e um caminho de falha sem o campo deixaria o formulário com o
     * token velho — a tentativa seguinte bateria no 403 do `csrf_verify()`.
     *
     * A mensagem vem do `validation_errors()` do CI3 e por isso é HTML; a tela
     * não a mostra (mostra um título fixo) e usa só o token, que é o que este
     * caso segura.
     */
    public function testLoginAnswersWithAFreshCsrfTokenWhenTheFieldsAreInvalid(): void
    {
        $this->postWithCsrfToken(['email' => 'nao-e-email', 'senha' => '123456']);

        $response = $this->callController('Mine', 'login');

        $this->assertFalse($response['result']);
        $this->assertNotEmpty($response['message']);
        $this->assertNotEmpty($response['MAPOS_TOKEN'] ?? null);
        $this->assertNull($this->ci()->session->userdata('conectado'));
    }

    public function testLoginAuthenticatesValidCredentials(): void
    {
        $email = $this->installCliente('cliente@exemplo.com');

        $this->postWithCsrfToken(['email' => $email, 'senha' => 'antiga']);

        $response = $this->callController('Mine', 'login');

        $this->assertTrue($response['result']);

        $session = $this->ci()->session;

        $this->assertTrue($session->userdata('conectado'));
        $this->assertTrue($session->userdata('isCliente'));
        $this->assertSame('Cliente Teste', $session->userdata('nome'));
        $this->assertSame($email, $session->userdata('email'));
        $this->assertEquals($this->clienteId($email), $session->userdata('cliente_id'));

        $log = $this->ci()->db
            ->where('tarefa', 'Cliente Cliente Teste efetuou login')
            ->get('logs')
            ->row();

        $this->assertNotNull($log, 'O login do cliente deveria ter deixado um registro de auditoria.');
        $this->assertSame('127.0.0.1', $log->ip);
    }

    public function testVisualizarOsRedirectsWhenTheClientIsNotAuthenticated(): void
    {
        $email = $this->installCliente('cliente@exemplo.com');
        $osId = $this->installOrdemDeServico($this->clienteId($email));

        $this->resetUriSegments("mine/visualizarOs/{$osId}");

        $this->callControllerRaw('Mine', 'visualizarOs');

        $this->assertSame(
            site_url('mine'),
            $this->ci()->output->get_header('Location'),
            'Uma OS não pode ser aberta sem sessão de cliente.'
        );
    }

    public function testVisualizarOsRendersTheOrderOfTheClient(): void
    {
        $email = $this->installCliente('cliente@exemplo.com');
        $osId = $this->installOrdemDeServico($this->clienteId($email));
        $this->installEmitente();

        $this->loginAsCliente($this->clienteId($email));
        $this->resetUriSegments("mine/visualizarOs/{$osId}");

        $html = $this->callControllerRaw('Mine', 'visualizarOs');

        $this->assertNotSame('', trim($html));
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('N° OS: </b>' . $osId, $html, 'A tela deveria mostrar a OS do cliente.');
        $this->assertNull(
            $this->ci()->output->get_header('Location'),
            'A OS é do cliente, então não deveria redirecionar.'
        );
    }

    /**
     * O identificador da URL é do cliente, não do técnico: a OS de outra conta
     * precisa ser negada, e não renderizada.
     */
    public function testVisualizarOsRejectsAnOrderThatIsNotTheClients(): void
    {
        $email = $this->installCliente('cliente@exemplo.com');
        $outroEmail = $this->installCliente('outro@exemplo.com', 'Outro Cliente');
        $osId = $this->installOrdemDeServico($this->clienteId($outroEmail));

        $this->loginAsCliente($this->clienteId($email));
        $this->resetUriSegments("mine/visualizarOs/{$osId}");

        $html = $this->callControllerRaw('Mine', 'visualizarOs');

        $this->assertSame(
            site_url('mine/painel'),
            $this->ci()->output->get_header('Location'),
            'A OS de outro cliente precisa ser negada.'
        );
        $this->assertSame(
            'Esta OS não pertence ao cliente logado.',
            $this->ci()->session->userdata('error')
        );
        $this->assertStringNotContainsString('N° OS: </b>' . $osId, $html, 'A OS de outro cliente não pode ser renderizada.');
    }

    /**
     * `getById()` devolve null para uma OS que não existe, e o `idClientes` logo
     * abaixo seria lido de um null — um erro fatal em vez de um aviso.
     *
     * A resposta é a mesma de uma OS de outro cliente, e a mesma que
     * `minha_ordem_de_servico()` já usava: o motivo na tela é o mesmo, "não
     * achei a sua OS", e a diferença entre as duas só interessaria a quem
     *andasse pela tabela.
     */
    public function testVisualizarOsAnswersWhenTheOrderDoesNotExist(): void
    {
        $email = $this->installCliente('cliente@exemplo.com');
        $osId = $this->installOrdemDeServico($this->clienteId($email));

        $this->loginAsCliente($this->clienteId($email));
        $this->resetUriSegments('mine/visualizarOs/' . ($osId + 1000));

        $this->callControllerRaw('Mine', 'visualizarOs');

        $this->assertSame(
            site_url('mine/painel'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Ordem de serviço não encontrada.',
            $this->ci()->session->userdata('error')
        );
    }

    private function loginAsCliente(int $clienteId): void
    {
        $_SESSION['conectado'] = true;
        $_SESSION['isCliente'] = true;
        $_SESSION['cliente_id'] = $clienteId;
        $_SESSION['nome'] = 'Cliente Teste';
    }

    private function installCliente(
        string $email,
        string $nome = 'Cliente Teste',
        string $senha = 'antiga'
    ): string {
        $this->ci()->db->insert('clientes', [
            'nomeCliente' => $nome,
            'documento' => '12345678909',
            'telefone' => '0000-0000',
            'email' => $email,
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'dataCadastro' => date('Y-m-d'),
        ]);

        return $email;
    }

    private function clienteId(string $email): int
    {
        return (int) $this->ci()->db
            ->select('idClientes')
            ->where('email', $email)
            ->limit(1)
            ->get('clientes')
            ->row('idClientes');
    }

    private function senhaDoCliente(string $email): string
    {
        return (string) $this->ci()->db
            ->select('senha')
            ->where('email', $email)
            ->limit(1)
            ->get('clientes')
            ->row('senha');
    }

    /**
     * `token_utilizado` fica de fora quando o token nasce não utilizado: é
     * exatamente o que o `Mine::gerarTokenResetarSenha()` faz, e o que o
     * DEFAULT 0 da coluna garante. Passar o 0 à mão aqui esconderia um default
     * quebrado de todos os testes.
     */
    private function installResetToken(
        string $email,
        string $token,
        int $expiracaoEmDias = 0,
        bool $utilizado = false
    ): void {
        $data = [
            'email' => $email,
            'token' => $token,
            'data_expiracao' => date('Y-m-d H:i:s', strtotime($expiracaoEmDias . ' days')),
        ];

        if ($utilizado) {
            $data['token_utilizado'] = 1;
        }

        $this->ci()->db->insert('resets_de_senha', $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function estadoDoToken(string $token): array
    {
        return (array) $this->ci()->db
            ->where('token', $token)
            ->limit(1)
            ->get('resets_de_senha')
            ->row_array();
    }

    /**
     * Monta o token de um cenário do provider de recusas e devolve o valor que
     * vai no POST.
     */
    private function tokenPara(string $estado, string $email): string
    {
        return match ($estado) {
            'valido' => (function () use ($email): string {
                $this->installResetToken($email, 'token-valido-abc123');

                return 'token-valido-abc123';
            })(),
            'inexistente' => 'token-que-nunca-foi-emitido',
            'utilizado' => (function () use ($email): string {
                $this->installResetToken($email, 'token-utilizado-abc123', 0, true);

                return 'token-utilizado-abc123';
            })(),
            'expirado' => (function () use ($email): string {
                $this->installResetToken($email, 'token-expirado-abc123', -2);

                return 'token-expirado-abc123';
            })(),
            'sem-cliente' => (function (): string {
                $this->installResetToken('fantasma@exemplo.com', 'token-fantasma-abc123');

                return 'token-fantasma-abc123';
            })(),
            default => $this->fail("Cenário de token desconhecido: {$estado}"),
        };
    }

    /**
     * O emitente é o remetente do e-mail e o cabeçalho da tela da OS. Sem a linha,
     * a view da OS lê `->url_logo` de um null.
     */
    private function installEmitente(): void
    {
        $this->ci()->db->insert('emitente', [
            'nome' => 'Map-OS Emitente',
            'cnpj' => '00.000.000/0000-00',
            'rua' => 'Rua do Emitente',
            'numero' => '1',
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'telefone' => '0000-0000',
            'email' => 'emitente@exemplo.com',
            'url_logo' => base_url() . 'assets/img/logo-mapos.png',
        ]);
    }

    private function installOrdemDeServico(int $clienteId): int
    {
        $this->ci()->db->insert('os', [
            'dataInicial' => date('Y-m-d'),
            'status' => 'Aberto',
            'valorTotal' => 0,
            'clientes_id' => $clienteId,
            'usuarios_id' => $this->firstUsuarioId(),
            'faturado' => 0,
        ]);

        return (int) $this->ci()->db->insert_id('os');
    }

    /**
     * O `usuarios_id` da OS é obrigatório e tem chave estrangeira, então a OS não
     * pode ser criada sem um técnico. A conta administrativa que a seed
     * `application/database/seeds/Usuarios.php` grava é o id 1.
     */
    private function firstUsuarioId(): int
    {
        $id = (int) $this->ci()->db
            ->select('idUsuarios')
            ->order_by('idUsuarios', 'ASC')
            ->limit(1)
            ->get('usuarios')
            ->row('idUsuarios');

        $this->assertGreaterThan(0, $id, 'Nenhuma conta em `usuarios` para ser o técnico da OS.');

        return $id;
    }
}
