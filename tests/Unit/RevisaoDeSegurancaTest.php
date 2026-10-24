<?php



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

    public function testBackupNaoLevaSessoesNemSegredosTemporarios(): void
    {
        $backup = $this->metodo('application/controllers/Mapos.php', 'backup');

        foreach (['ci_sessions', 'login_attempts', 'api_refresh_tokens', 'api_rate_limit', 'resets_de_senha'] as $tabela) {
            $this->assertStringContainsString("'$tabela'", $backup, $tabela);
        }
        $this->assertStringContainsString("'ignore' =>", $backup);
    }

    public function testClienteTrocaEmailOuSenhaSoComASenhaAtual(): void
    {
        $mine = $this->metodo('application/controllers/Mine.php', 'editarDados');
        $this->assertStringContainsString("senhaConfere((string) \$this->input->post('senha_atual')", $mine);
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
}
