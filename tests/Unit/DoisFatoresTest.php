<?php

use RobThree\Auth\Providers\Qr\IQRCodeProvider;
use RobThree\Auth\TwoFactorAuth;

require_once APPPATH . 'libraries/Totp.php';
require_once APPPATH . 'models/Mapos_model.php';

/**
 * Autenticação em dois fatores (TOTP) opcional (#2876).
 */
final class DoisFatoresTest extends MaposTestCase
{
    private Totp $totp;

    private Mapos_model $modelo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->totp = new Totp(['chave' => 'chave-da-instalacao-de-teste', 'emissor' => 'Map-OS']);

        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, email TEXT, senha TEXT, situacao INTEGER DEFAULT 1, permissoes_id INTEGER, totp_secreto TEXT NULL, totp_ativado_em TEXT NULL, totp_ultimo_passo INTEGER NULL)');
        $this->db->query('CREATE TABLE permissoes (idPermissao INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, situacao INTEGER DEFAULT 1, exigir_2fa INTEGER DEFAULT 0)');
        $this->db->query('CREATE TABLE usuarios_codigos_recuperacao (id INTEGER PRIMARY KEY AUTOINCREMENT, usuario_id INTEGER, codigo_hash TEXT UNIQUE, criado_em TEXT, usado_em TEXT NULL)');
        $this->db->query("INSERT INTO permissoes (nome, exigir_2fa) VALUES ('Livre', 0), ('Exige', 1)");
        $this->db->query("INSERT INTO usuarios (nome, email, senha, permissoes_id) VALUES ('Ana', 'ana@x.com', 'h', 1)");

        $this->modelo = $this->makeInstance(Mapos_model::class);
        $this->setPrivateProperty($this->modelo, 'db', $this->db);
    }

    /** Código que um aplicativo autenticador mostraria agora. */
    private function codigoDeAgora(string $segredo, ?int $tempo = null): string
    {
        $tfa = new TwoFactorAuth(new class() implements IQRCodeProvider {
            public function getQRCodeImage(string $qrText, int $size): string
            {
                return '';
            }

            public function getMimeType(): string
            {
                return '';
            }
        });

        return $tfa->getCode($segredo, $tempo);
    }

    public function testSegredoTemFormatoBase32EQrCodeEPngLocal(): void
    {
        $segredo = $this->totp->criarSegredo();

        $this->assertMatchesRegularExpression('/^[A-Z2-7]{32}$/', $segredo);
        $this->assertStringStartsWith('otpauth://totp/', $this->totp->uri('ana@x.com', $segredo));
        $this->assertStringContainsString('issuer=Map-OS', $this->totp->uri('ana@x.com', $segredo));

        $uri = $this->totp->qrCodeDataUri('ana@x.com', $segredo);
        $this->assertStringStartsWith('data:image/png;base64,', $uri);
        $this->assertStringStartsWith("\x89PNG", (string) base64_decode(substr($uri, strlen('data:image/png;base64,'))));
        $this->assertSame('ABCD EFGH', Totp::formatarSegredo('ABCDEFGH'));
    }

    public function testVerificaOCodigoDaJanelaEVizinhasMasNaoDeOutras(): void
    {
        $segredo = $this->totp->criarSegredo();
        $agora = 1_800_000_000;

        $this->assertNotNull($this->totp->verificar($segredo, $this->codigoDeAgora($segredo, $agora), null, $agora));
        $this->assertNotNull($this->totp->verificar($segredo, $this->codigoDeAgora($segredo, $agora - 30), null, $agora), 'Relógio 30 s atrasado.');
        $this->assertNotNull($this->totp->verificar($segredo, $this->codigoDeAgora($segredo, $agora + 30), null, $agora), 'Relógio 30 s adiantado.');
        $this->assertNull($this->totp->verificar($segredo, $this->codigoDeAgora($segredo, $agora - 90), null, $agora), 'Janela velha demais.');
        $this->assertNull($this->totp->verificar($segredo, '000000', null, $agora));
        $this->assertNull($this->totp->verificar($segredo, '12345', null, $agora));
        $this->assertNull($this->totp->verificar($segredo, 'abcdef', null, $agora));
        $this->assertNull($this->totp->verificar($segredo, '', null, $agora));
    }

    public function testCodigoNaoValeDuasVezes(): void
    {
        $segredo = $this->totp->criarSegredo();
        $agora = 1_800_000_000;
        $codigo = $this->codigoDeAgora($segredo, $agora);

        $passo = $this->totp->verificar($segredo, $codigo, null, $agora);
        $this->assertNotNull($passo);
        $this->assertNull($this->totp->verificar($segredo, $codigo, $passo, $agora), 'Mesma janela: recusado.');
        $this->assertNull($this->totp->verificar($segredo, $this->codigoDeAgora($segredo, $agora - 30), $passo, $agora), 'Janela anterior à última aceita.');
        $this->assertNotNull($this->totp->verificar($segredo, $this->codigoDeAgora($segredo, $agora + 30), $passo, $agora), 'Janela seguinte vale.');
        $this->assertNotNull($this->totp->verificar($segredo, substr($codigo, 0, 3) . ' ' . substr($codigo, 3), null, $agora), 'Aceita o código com espaço (o aplicativo mostra 123 456).');
    }

    public function testSegredoVaiCifradoParaOBancoENaoDecifraComOutraChave(): void
    {
        $segredo = $this->totp->criarSegredo();
        $cifrado = $this->totp->cifrar($segredo);

        $this->assertStringNotContainsString($segredo, $cifrado);
        $this->assertNotSame($cifrado, $this->totp->cifrar($segredo), 'Nonce novo a cada cifragem.');
        $this->assertSame($segredo, $this->totp->decifrar($cifrado));
        $this->assertNull((new Totp(['chave' => 'outra-chave']))->decifrar($cifrado));
        $this->assertNull($this->totp->decifrar(null));
        $this->assertNull($this->totp->decifrar('lixo'));

        $adulterado = base64_decode($cifrado, true);
        $adulterado[strlen($adulterado) - 1] = $adulterado[strlen($adulterado) - 1] ^ "\x01";
        $this->assertNull($this->totp->decifrar(base64_encode($adulterado)), 'GCM detecta adulteração.');
    }

    public function testCodigosDeRecuperacaoTemFormatoEHashNormalizado(): void
    {
        $codigos = $this->totp->gerarCodigosDeRecuperacao();

        $this->assertCount(10, $codigos);
        $this->assertCount(10, array_unique($codigos));
        foreach ($codigos as $codigo) {
            $this->assertMatchesRegularExpression('/^[a-z2-9]{4}-[a-z2-9]{4}-[a-z2-9]{4}$/', $codigo);
            $this->assertTrue(Totp::pareceCodigoDeRecuperacao($codigo));
            $this->assertSame(Totp::hashDoCodigo($codigo), Totp::hashDoCodigo(strtoupper(str_replace('-', ' ', $codigo))), 'Maiúsculas, espaços e traços não importam.');
        }
        $this->assertFalse(Totp::pareceCodigoDeRecuperacao('123456'), 'Código do aplicativo não é de recuperação.');
        $this->assertFalse(Totp::pareceCodigoDeRecuperacao('abcd-efgh'));
    }

    public function testAtivarGravaSegredoCifradoEOsHashesDosCodigos(): void
    {
        $segredo = $this->totp->criarSegredo();
        $codigos = $this->totp->gerarCodigosDeRecuperacao(3);

        $this->assertTrue($this->modelo->ativarTotp(1, $this->totp->cifrar($segredo), array_map([Totp::class, 'hashDoCodigo'], $codigos), 123));

        $linha = $this->db->where('idUsuarios', 1)->get('usuarios')->row();
        $this->assertNotNull($linha->totp_ativado_em);
        $this->assertSame($segredo, $this->totp->decifrar($linha->totp_secreto));
        $this->assertSame(123, (int) $linha->totp_ultimo_passo);
        $this->assertSame(3, $this->modelo->codigosDeRecuperacaoRestantes(1));
        $armazenados = array_column($this->db->get('usuarios_codigos_recuperacao')->result_array(), 'codigo_hash');
        $this->assertNotContains($codigos[0], $armazenados, 'Só o hash fica no banco.');
        $this->assertContains(Totp::hashDoCodigo($codigos[0]), $armazenados);
    }

    public function testCodigoDeRecuperacaoValeUmaVezSo(): void
    {
        $codigos = $this->totp->gerarCodigosDeRecuperacao(2);
        $this->modelo->substituirCodigosDeRecuperacao(1, array_map([Totp::class, 'hashDoCodigo'], $codigos));

        $this->assertTrue($this->modelo->usarCodigoDeRecuperacao(1, Totp::hashDoCodigo($codigos[0])));
        $this->assertFalse($this->modelo->usarCodigoDeRecuperacao(1, Totp::hashDoCodigo($codigos[0])), 'Já usado.');
        $this->assertFalse($this->modelo->usarCodigoDeRecuperacao(2, Totp::hashDoCodigo($codigos[1])), 'De outro usuário.');
        $this->assertSame(1, $this->modelo->codigosDeRecuperacaoRestantes(1));

        $novos = $this->totp->gerarCodigosDeRecuperacao(4);
        $this->modelo->substituirCodigosDeRecuperacao(1, array_map([Totp::class, 'hashDoCodigo'], $novos));
        $this->assertFalse($this->modelo->usarCodigoDeRecuperacao(1, Totp::hashDoCodigo($codigos[1])), 'Gerar novos invalida os antigos.');
        $this->assertSame(4, $this->modelo->codigosDeRecuperacaoRestantes(1));
    }

    public function testPassoSoAvancaEIssoImpedeReusoEmRequisicoesJuntas(): void
    {
        $this->assertTrue($this->modelo->registrarPassoTotp(1, 100));
        $this->assertFalse($this->modelo->registrarPassoTotp(1, 100), 'Segunda requisição com o mesmo código.');
        $this->assertFalse($this->modelo->registrarPassoTotp(1, 99));
        $this->assertTrue($this->modelo->registrarPassoTotp(1, 101));
    }

    public function testAceitarUsaOCodigoDoAplicativoOuODeRecuperacao(): void
    {
        $segredo = $this->totp->criarSegredo();
        $codigos = $this->totp->gerarCodigosDeRecuperacao(1);
        $this->modelo->ativarTotp(1, $this->totp->cifrar($segredo), [Totp::hashDoCodigo($codigos[0])], 1);
        $usuario = $this->db->where('idUsuarios', 1)->get('usuarios')->row();

        $this->assertFalse($this->totp->aceitar($this->modelo, $usuario, '000000'));
        $this->assertTrue($this->totp->aceitar($this->modelo, $usuario, strtoupper($codigos[0])), 'Recuperação, em maiúsculas.');
        $this->assertFalse($this->totp->aceitar($this->modelo, $usuario, $codigos[0]), 'Só uma vez.');

        $agora = $this->codigoDeAgora($segredo);
        $this->assertTrue($this->totp->aceitar($this->modelo, $usuario, $agora));
        $usuario = $this->db->where('idUsuarios', 1)->get('usuarios')->row();
        $this->assertFalse($this->totp->aceitar($this->modelo, $usuario, $agora), 'Mesmo código do aplicativo de novo.');
    }

    public function testDesativarApagaSegredoECodigos(): void
    {
        $this->modelo->ativarTotp(1, $this->totp->cifrar($this->totp->criarSegredo()), ['a', 'b'], 1);
        $this->modelo->desativarTotp(1);

        $linha = $this->db->where('idUsuarios', 1)->get('usuarios')->row();
        $this->assertNull($linha->totp_secreto);
        $this->assertNull($linha->totp_ativado_em);
        $this->assertSame(0, $this->modelo->codigosDeRecuperacaoRestantes(1));
    }

    public function testGrupoQueExigeOTotp(): void
    {
        $this->assertFalse($this->modelo->grupoExige2fa(1));
        $this->assertTrue($this->modelo->grupoExige2fa(2));
        $this->assertFalse($this->modelo->grupoExige2fa(99));

        $this->db->query('DROP TABLE permissoes');
        $this->db->query('CREATE TABLE permissoes (idPermissao INTEGER PRIMARY KEY, nome TEXT, situacao INTEGER)');
        $this->assertFalse($this->modelo->grupoExige2fa(2), 'Sem a coluna (migration não rodada) ninguém é bloqueado.');
    }

    public function testLoginEmDoisPassosNaoDaSessaoAntesDoCodigo(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Login.php');
        preg_match('/function verificarLogin\(\).*?\n    }\n/s', $codigo, $trecho);

        $this->assertNotEmpty($trecho);
        $verificar = $trecho[0];
        $this->assertStringContainsString("set_userdata('dois_fatores'", $verificar);
        $this->assertLessThan(strpos($verificar, '$this->iniciarSessao($user)'), strpos($verificar, "set_userdata('dois_fatores'"));
        // A sessão de verdade (logado) só nasce em iniciarSessao(), e o 2FA responde antes.
        $this->assertStringNotContainsString("'logado' => true", $verificar);
        $this->assertLessThan(strpos($verificar, 'registrarSucesso'), strpos($verificar, "'destino' => site_url('login/doisFatores')"), 'As falhas só zeram depois do código certo.');
    }

    public function testSegundoPassoLimitaTentativasEUsaOLimiteDeLogin(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Login.php');
        preg_match('/function verificarDoisFatores\(\).*?\n    }\n/s', $codigo, $trecho);

        $this->assertNotEmpty($trecho);
        $this->assertStringContainsString("bloqueado('usuario'", $trecho[0]);
        $this->assertStringContainsString("registrarFalha('usuario'", $trecho[0]);
        $this->assertStringContainsString('DOIS_FATORES_TENTATIVAS', $trecho[0]);
        $this->assertStringContainsString('$this->totp->aceitar(', $trecho[0]);
        $this->assertMatchesRegularExpression('/DOIS_FATORES_VALIDADE = 300/', $codigo);
        $this->assertMatchesRegularExpression('/DOIS_FATORES_TENTATIVAS = 5/', $codigo);
    }

    public function testGrupoQueExigeFicaPresoNaTelaDeAtivacao(): void
    {
        $base = (string) file_get_contents(APPPATH . 'core/MY_Controller.php');
        preg_match('/function exigirDoisFatores\(\).*?\n    }\n/s', $base, $trecho);

        $this->assertNotEmpty($trecho);
        $this->assertStringContainsString("'mapos/doisfatores', 'mapos/ativardoisfatores'", $trecho[0]);
        $this->assertLessThan(strpos($base, 'verificarPermissaoDaRota();'), strpos($base, '$this->exigirDoisFatores();'));

        $login = (string) file_get_contents(APPPATH . 'controllers/Login.php');
        $this->assertStringContainsString("set_userdata('exigir_2fa_cadastro', true)", $login);
    }

    public function testApiExigeOCodigoDeQuemTemOTotpEBarraGrupoSemEle(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/api/v1/UsuariosController.php');
        preg_match('/function login_post\(\).*?\n    }\n/s', $codigo, $login);
        preg_match('/function conferirDoisFatores\(.*?\n    }\n/s', $codigo, $dois);

        $this->assertLessThan(strrpos($login[0], 'registrarSucesso'), strpos($login[0], '$this->conferirDoisFatores('), 'O 2FA vem antes de zerar as falhas e de gerar o token.');
        $this->assertLessThan(strpos($login[0], 'accessTokenDoUsuario'), strpos($login[0], '$this->conferirDoisFatores('));
        $this->assertStringContainsString("'two_factor_required' => true", $dois[0]);
        $this->assertStringContainsString("'two_factor_setup_required' => true", $dois[0]);
        $this->assertStringContainsString("post('totp', true)", $dois[0]);
        $this->assertStringContainsString('grupoExige2fa', $dois[0]);
        $this->assertStringContainsString('HTTP_FORBIDDEN', $dois[0]);
    }

    public function testTelaTemRotuloNoBreadcrumbSemClasseInexistenteENaoQuebraAChave(): void
    {
        $url = static fn ($c) => 'http://x/' . $c;
        $this->assertSame('Autenticação em dois fatores', layoutBreadcrumb(['mapos', 'doisFatores', ''], $url)[2]['label']);
        $this->assertSame('Autenticação em dois fatores', layoutBreadcrumb(['mapos', 'ativarDoisFatores', ''], $url)[2]['label']);

        $view = (string) file_get_contents(APPPATH . 'views/mapos/doisFatores.php');
        $this->assertStringNotContainsString('bg-white', $view, 'A paleta padrão do Tailwind está desligada.');
        $this->assertStringContainsString('inline-block whitespace-nowrap', $view, 'A chave só quebra entre os grupos de 4.');
    }

    public function testExcluirUsuarioApagaOsCodigosDeRecuperacao(): void
    {
        require_once APPPATH . 'models/Usuarios_model.php';
        $usuarios = $this->makeInstance(Usuarios_model::class);
        $this->setPrivateProperty($usuarios, 'db', $this->db);
        $this->modelo->substituirCodigosDeRecuperacao(1, ['a', 'b']);

        $usuarios->delete('usuarios', 'idUsuarios', 1);

        $this->assertSame(0, $this->modelo->codigosDeRecuperacaoRestantes(1));
    }

    public function testRotasDeDoisFatoresEstaoNoMapaEOGrupoTemOSwitch(): void
    {
        $mapa = (string) file_get_contents(APPPATH . 'config/permissions_map.php');
        foreach (['doisFatores', 'ativarDoisFatores', 'novosCodigosDoisFatores', 'desativarDoisFatores'] as $metodo) {
            $this->assertStringContainsString("'$metodo' => '*'", $mapa, $metodo);
        }
        $this->assertStringContainsString("'redefinirDoisFatores' => 'cUsuario'", $mapa);

        $this->assertStringContainsString("'name' => 'exigir_2fa'", (string) file_get_contents(APPPATH . 'views/permissoes/formulario.php'));
        $this->assertStringContainsString("field_exists('exigir_2fa', 'permissoes')", (string) file_get_contents(APPPATH . 'controllers/Permissoes.php'));
    }

    public function testTrocaDeSenhaEDesativacaoRevogamSessoesDaApiEAteVoltaAoPainel(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');
        preg_match('/function desativarDoisFatores\(\).*?\n    }\n/s', $codigo, $trecho);

        $this->assertStringContainsString("confirmarSenhaDoUsuario(\$usuario, (string) \$this->input->post('senha')", $trecho[0], 'Desativar pede a senha, com limite de tentativas (#2879).');
        $this->assertStringContainsString('grupoExige2fa', $trecho[0], 'Grupo que exige não deixa desativar.');
        preg_match('/function novosCodigosDoisFatores\(\).*?\n    }\n/s', $codigo, $novos);
        $this->assertStringContainsString("confirmarSenhaDoUsuario(\$usuario, (string) \$this->input->post('senha')", $novos[0], 'Gerar novos códigos pede a senha, com limite de tentativas (#2879).');
    }
}
