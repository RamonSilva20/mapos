<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once APPPATH . 'libraries/Authorization_Token.php';

/**
 * Correções da revisão de segurança da v5 (#2879): autenticação, sessão,
 * delegação de permissões e API.
 */
final class RevisaoDeSegurancaTest extends MaposTestCase
{
    private function fonte(string $caminho): string
    {
        return (string) file_get_contents(MAPOS_ROOT . '/' . $caminho);
    }

    /** Corpo de um método, para conferir o que ele faz. */
    private function metodo(string $caminho, string $nome): string
    {
        preg_match('/function ' . preg_quote($nome, '/') . '\(.*?\n    }\n/s', $this->fonte($caminho), $m);
        $this->assertNotEmpty($m, $nome);

        return $m[0];
    }

    public function testDelegacaoSoConcedeOQueOOperadorTem(): void
    {
        $this->assertTrue(delegacaoPermitida(false, ['vOs', 'eOs', 'cUsuario'], ['vOs']));
        $this->assertTrue(delegacaoPermitida(false, ['vOs'], []), 'Grupo vazio não escala nada.');
        $this->assertFalse(delegacaoPermitida(false, ['vOs', 'cUsuario'], ['vOs', 'cSistema']), 'cUsuario não vira cSistema.');
        $this->assertFalse(delegacaoPermitida(false, ['cPermissao'], ['cPermissao', 'cBackup']));
        $this->assertTrue(delegacaoPermitida(true, [], ['cSistema', 'cBackup']), 'O administrador delega tudo.');
    }

    public function testUsuariosEPermissoesAplicamADelegacao(): void
    {
        $base = $this->fonte('application/core/MY_Controller.php');
        $this->assertStringContainsString('function podeDelegar(array $codigosDoAlvo)', $base);
        $this->assertStringContainsString('delegacaoPermitida(false, permissoesMarcadas($meu->permissoes', $base);
        $this->assertStringContainsString('USUARIO_SUPER_ADMIN || (int) $this->session->userdata(\'permissao\') === PERMISSAO_ADMIN', $base);

        $usuarios = $this->fonte('application/controllers/Usuarios.php');
        $this->assertStringContainsString("podeDelegarGrupo((int) \$usuario->permissoes_id)", $this->metodo('application/controllers/Usuarios.php', 'editar'), 'Editar usuário de grupo maior é recusado.');
        $this->assertStringContainsString('podeDelegarGrupo((int) $grupo->idPermissao)', $usuarios, 'Atribuir grupo maior é recusado.');
        $this->assertStringContainsString('podeDelegarGrupo((int) $alvo->permissoes_id)', $this->metodo('application/controllers/Usuarios.php', 'excluir'));
        $reset = $this->metodo('application/controllers/Usuarios.php', 'redefinirDoisFatores');
        $this->assertStringContainsString('usuarioPodeSerEditadoPor(', $reset, 'O 2FA do administrador principal só ele redefine.');
        $this->assertStringContainsString('podeDelegarGrupo(', $reset);

        $permissoes = $this->fonte('application/controllers/Permissoes.php');
        $this->assertStringContainsString('podeDelegar(permissoesMarcadas($grupo->permissoes', $permissoes, 'Editar grupo maior é recusado.');
        $this->assertStringContainsString("podeDelegar(permissoesMarcadas(\$dados['permissoes']))", $permissoes, 'Marcar permissão que não tem é recusado.');
    }

    public function testSessaoWebERevalidadaContraOBancoACadaRequisicao(): void
    {
        $base = $this->fonte('application/core/MY_Controller.php');

        $this->assertLessThan(strpos($base, '$this->exigirDoisFatores();'), strpos($base, '$this->revalidarSessao();'));
        $metodo = $this->metodo('application/core/MY_Controller.php', 'revalidarSessao');
        foreach (['usuarioParaLoginPorId', 'usuarioContaExpirada(', 'grupoAtivo(', 'sess_destroy()', "set_userdata('permissao'"] as $trecho) {
            $this->assertStringContainsString($trecho, $metodo, $trecho);
        }
    }

    public function testContaExpirada(): void
    {
        $this->assertFalse(usuarioContaExpirada(null));
        $this->assertFalse(usuarioContaExpirada(''));
        $this->assertFalse(usuarioContaExpirada('   '));
        $this->assertFalse(usuarioContaExpirada(date('Y-m-d', strtotime('+2 days'))));
        $this->assertTrue(usuarioContaExpirada('2020-01-01'));
    }

    public function testBackupNaoLevaSessoesNemSegredosTemporarios(): void
    {
        $backup = $this->metodo('application/controllers/Mapos.php', 'backup');

        foreach (['ci_sessions', 'login_attempts', 'api_refresh_tokens', 'api_rate_limit', 'resets_de_senha'] as $tabela) {
            $this->assertStringContainsString("'$tabela'", $backup, $tabela);
        }
        $this->assertStringContainsString("'ignore' =>", $backup);
    }

    public function testApiDesligadaNaoResponde(): void
    {
        $rest = $this->fonte('application/libraries/REST_Controller.php');
        preg_match('/function early_checks\(\).*?\n    }\n/s', $rest, $m);

        $this->assertStringContainsString("FILTER_VALIDATE_BOOLEAN", $m[0]);
        $this->assertStringContainsString('show_404()', $m[0]);
        $this->assertLessThan(strpos($m[0], 'limiteDeRequisicoes'), strpos($m[0], 'show_404()'));
    }

    public function testTokenDaApiPerdeAcessoComContaExpiradaOuGrupoDesativado(): void
    {
        $rest = $this->fonte('application/libraries/REST_Controller.php');
        preg_match('/function logged_user\(.*?\n    }\n/s', $rest, $m);

        $this->assertStringContainsString('usuarioContaExpirada(', $m[0]);
        $this->assertStringContainsString('grupoAtivo(', $m[0]);
    }

    public function testRenovacaoLegadaTemTetoDesdeOLogin(): void
    {
        $autorizacao = $this->makeInstance(Authorization_Token::class);
        $this->setPrivateProperty($autorizacao, 'token_key', 'chave-de-teste-com-mais-de-32-caracteres!!');
        $this->setPrivateProperty($autorizacao, 'token_algorithm', 'HS256');
        $this->setPrivateProperty($autorizacao, 'token_expire_time', 3600);

        $primeiro = JWT::decode($autorizacao->generateToken(['uid' => 7]), new Key('chave-de-teste-com-mais-de-32-caracteres!!', 'HS256'));
        $this->assertEqualsWithDelta(time(), $primeiro->orig, 5, 'No login a cadeia começa agora.');

        $renovado = JWT::decode($autorizacao->generateToken(['uid' => 7, 'orig' => $primeiro->orig - 86400]), new Key('chave-de-teste-com-mais-de-32-caracteres!!', 'HS256'));
        $this->assertSame($primeiro->orig - 86400, $renovado->orig, 'A renovação preserva o início.');
        $this->assertEqualsWithDelta(time(), $renovado->iat, 5);

        $reGen = $this->metodo('application/controllers/api/v1/UsuariosController.php', 'reGenToken_get');
        $this->assertStringContainsString('API_REGENTOKEN_MAX_AGE', $reGen);
        $this->assertStringContainsString("'orig' => \$inicio", $reGen);
        $this->assertStringContainsString('usuarioContaExpirada(', $reGen);
    }

    public function testEdicaoDeUsuarioPelaApiFoiDesativada(): void
    {
        $put = $this->metodo('application/controllers/api/v1/UsuariosController.php', 'index_put');

        $this->assertStringContainsString('HTTP_NOT_IMPLEMENTED', $put);
        $this->assertStringNotContainsString('permissoes_id', $put);
        $this->assertStringNotContainsString("put('senha'", $put);
    }

    public function testApiDeClientesNaoDevolveOHashDaSenha(): void
    {
        $fonte = $this->fonte('application/controllers/api/v1/ClientesController.php');

        $this->assertStringContainsString('private function semSenha(', $fonte);
        $this->assertStringContainsString("array_map([\$this, 'semSenha']", $fonte, 'Lista.');
        $this->assertSame(3, substr_count($fonte, '$this->semSenha('), 'Detalhe, cadastro e edição.');
        $this->assertStringNotContainsString("telefone = '{\$data['telefone']}'", $fonte, 'O cadastro devolve o registro pelo id inserido.');
    }

    public function testClienteTrocaEmailOuSenhaSoComASenhaAtual(): void
    {
        $mine = $this->metodo('application/controllers/Mine.php', 'editarDados');
        $this->assertStringContainsString("password_verify((string) \$this->input->post('senha_atual')", $mine);
        $this->assertStringContainsString("strcasecmp((string) \$dados['email'], (string) \$cliente->email)", $mine);

        $this->assertStringContainsString("'name' => 'senha_atual'", $this->fonte('application/views/conecte/editar_dados.php'));
    }

    public function testAtivarOs2faPedeASenha(): void
    {
        $ativar = $this->metodo('application/controllers/Mapos.php', 'ativarDoisFatores');

        $this->assertStringContainsString("password_verify((string) \$this->input->post('senha'), \$usuario->senha)", $ativar);
        $this->assertLessThan(strpos($ativar, '$this->totp->verificar('), strpos($ativar, 'password_verify'), 'A senha vem antes do código.');
        $this->assertStringContainsString("'name' => 'senha'", $this->fonte('application/views/mapos/doisFatores.php'));
    }

    public function testPlanilhaNeutralizaFormulas(): void
    {
        require_once APPPATH . 'helpers/relatorios_helper.php';

        $this->assertSame(
            ["'=HYPERLINK(\"http://x\")", "'+1+1", "'@SUM(A1)", 'Ana', 12.5, '-3', '', null],
            planilhaCelulasSeguras(['=HYPERLINK("http://x")', '+1+1', '@SUM(A1)', 'Ana', 12.5, '-3', '', null])
        );
        $this->assertStringContainsString('planilhaCelulasSeguras($cliente)', $this->fonte('application/controllers/Relatorios.php'));
    }

    public function testRelatorioMeiUsaArquivosTemporariosForaDoWebroot(): void
    {
        $fonte = $this->fonte('application/controllers/Relatorios.php');

        $this->assertStringNotContainsString("'temp.docx'", $fonte);
        $this->assertStringContainsString('sys_get_temp_dir()', $fonte);
    }

    public function testServidorNegaArquivosInternos(): void
    {
        foreach (['.htaccess', 'docker/etc/nginx/default.conf', 'docker/etc/nginx/default.template.conf'] as $arquivo) {
            $fonte = $this->fonte($arquivo);
            foreach (['application', 'tests', 'docs', 'node_modules'] as $pasta) {
                $this->assertStringContainsString($pasta, $fonte, "$arquivo nega $pasta");
            }
        }
        $this->assertStringNotContainsString('PMA_ARBITRARY', $this->fonte('docker/docker-compose.yml'));
        $this->assertStringContainsString('"127.0.0.1:${MYSQL_MAPOS_PORT}:3306"', $this->fonte('docker/docker-compose.yml'));
    }

    public function testReleaseValidaATagSemInterpolarNoScript(): void
    {
        $fonte = $this->fonte('.github/workflows/release.yml');

        $this->assertStringContainsString('Validate tag name', $fonte);
        $this->assertStringNotContainsString('git tag "${{ inputs.tag }}"', $fonte);
        $this->assertStringContainsString('--exclude \'./tests/*\'', $fonte);
    }

    public function testDownloadsFicamDentroDasPastasDeUpload(): void
    {
        $this->assertStringContainsString('osArquivosDosAnexos(', $this->metodo('application/controllers/Mine.php', 'downloadanexo'));
        $this->assertStringContainsString('str_starts_with($real, $raiz', $this->metodo('application/controllers/Arquivos.php', 'download'));
    }

    public function testEmailDaOsSoPorPostComPermissaoDeEdicao(): void
    {
        $this->assertStringContainsString("method() !== 'post'", $this->metodo('application/controllers/Os.php', 'enviar_email'));
        $this->assertStringContainsString("'eOs'", $this->metodo('application/controllers/Os.php', 'enviar_email'));
    }

    public function testValorDoEnvEscapaCifrao(): void
    {
        $this->assertSame('"a\\$b\'c"', configuracaoValorEnv("a\$b'c"));
    }
}
