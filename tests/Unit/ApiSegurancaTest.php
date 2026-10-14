<?php

require_once APPPATH . 'models/Usuarios_model.php';
require_once APPPATH . 'helpers/usuarios_helper.php';
require_once APPPATH . 'helpers/api_helper.php';

/**
 * Hardening da API (#2879): o corpo do PUT de usuários não escala privilégio,
 * a senha é gravada com hash e nenhuma resposta devolve o hash.
 */
final class ApiSegurancaTest extends MaposTestCase
{
    private const SENHA = 'senha-atual-123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, email TEXT, senha TEXT)');
        $hash = password_hash(self::SENHA, PASSWORD_DEFAULT);
        $this->db->query('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?), (?, ?, ?), (?, ?, ?)', [
            'Admin', 'admin@x.com', $hash,
            'Tecnico', 'tecnico@x.com', $hash,
            'Outro', 'outro@x.com', $hash,
        ]);
    }

    private function usuarios(): Usuarios_model
    {
        $model = $this->makeInstance(Usuarios_model::class);
        $model->db = $this->db;

        return $model;
    }

    /** @return array<string, string> */
    private function corpo(array $extra = []): array
    {
        return $extra + [
            'nome' => 'Tecnico Novo', 'rg' => '1', 'cpf' => '529.982.247-25', 'cep' => '01000-000', 'rua' => 'Rua A',
            'numero' => '10', 'bairro' => 'Centro', 'cidade' => 'SP', 'estado' => 'SP', 'email' => 'tecnico@x.com',
            'telefone' => '1199999999',
        ];
    }

    public function testNoProprioUsuarioGrupoSituacaoEExpiracaoSaoIgnorados(): void
    {
        $plano = usuarioApiPlanoEdicao($this->usuarios(), 2, 2, false, $this->corpo([
            'permissoes_id' => '1', 'situacao' => '1', 'dataExpiracao' => '2099-01-01',
        ]));

        $this->assertTrue($plano['ok']);
        $this->assertArrayNotHasKey('permissoes_id', $plano['dados']);
        $this->assertArrayNotHasKey('situacao', $plano['dados']);
        $this->assertArrayNotHasKey('dataExpiracao', $plano['dados']);
        $this->assertSame('Tecnico Novo', $plano['dados']['nome']);
    }

    public function testQuemTemCUsuarioTambemNaoMudaOProprioGrupo(): void
    {
        $plano = usuarioApiPlanoEdicao($this->usuarios(), 1, 1, true, $this->corpo(['email' => 'admin@x.com', 'permissoes_id' => '9', 'situacao' => '0']));

        $this->assertTrue($plano['ok']);
        $this->assertArrayNotHasKey('permissoes_id', $plano['dados']);
        $this->assertArrayNotHasKey('situacao', $plano['dados']);
    }

    public function testSemCUsuarioNaoEditaOutroUsuario(): void
    {
        $plano = usuarioApiPlanoEdicao($this->usuarios(), 2, 3, false, $this->corpo(['permissoes_id' => '1', 'situacao' => '1']));

        $this->assertFalse($plano['ok']);
        $this->assertSame(401, $plano['status']);
    }

    public function testComCUsuarioMudaGrupoSituacaoEExpiracaoDeOutro(): void
    {
        $plano = usuarioApiPlanoEdicao($this->usuarios(), 1, 3, true, $this->corpo(['email' => 'outro@x.com', 'permissoes_id' => '2', 'situacao' => '0', 'dataExpiracao' => '2030-12-31']));

        $this->assertTrue($plano['ok']);
        $this->assertSame(2, $plano['dados']['permissoes_id']);
        $this->assertSame(0, $plano['dados']['situacao'], 'Desativar com situacao=0 funciona.');
        $this->assertSame('2030-12-31', $plano['dados']['dataExpiracao']);
    }

    public function testGerenciarExigeSituacaoEGrupoValidos(): void
    {
        $semGrupo = usuarioApiPlanoEdicao($this->usuarios(), 1, 3, true, $this->corpo(['email' => 'outro@x.com', 'situacao' => '1']));
        $grupoRuim = usuarioApiPlanoEdicao($this->usuarios(), 1, 3, true, $this->corpo(['email' => 'outro@x.com', 'situacao' => '1', 'permissoes_id' => '1 OR 1=1']));
        $situacaoRuim = usuarioApiPlanoEdicao($this->usuarios(), 1, 3, true, $this->corpo(['email' => 'outro@x.com', 'situacao' => '7', 'permissoes_id' => '2']));

        $this->assertFalse($semGrupo['ok']);
        $this->assertFalse($grupoRuim['ok']);
        $this->assertFalse($situacaoRuim['ok']);
    }

    public function testSuperAdminNaoSaiDoGrupoNemEDesativado(): void
    {
        $desativar = usuarioApiPlanoEdicao($this->usuarios(), 2, 1, true, $this->corpo(['email' => 'admin@x.com', 'permissoes_id' => '1', 'situacao' => '0']));
        $rebaixar = usuarioApiPlanoEdicao($this->usuarios(), 2, 1, true, $this->corpo(['email' => 'admin@x.com', 'permissoes_id' => '2', 'situacao' => '1']));

        $this->assertFalse($desativar['ok']);
        $this->assertFalse($rebaixar['ok']);
    }

    public function testEmailJaUsadoPorOutroUsuarioEhRecusado(): void
    {
        $outro = usuarioApiPlanoEdicao($this->usuarios(), 2, 2, false, $this->corpo(['email' => 'ADMIN@x.com']));
        $proprio = usuarioApiPlanoEdicao($this->usuarios(), 2, 2, false, $this->corpo(['email' => 'tecnico@x.com']));
        $invalido = usuarioApiPlanoEdicao($this->usuarios(), 2, 2, false, $this->corpo(['email' => 'nao-e-email']));

        $this->assertFalse($outro['ok']);
        $this->assertTrue($proprio['ok'], 'Manter o próprio e-mail é permitido.');
        $this->assertFalse($invalido['ok']);
    }

    public function testSenhaPropriaExigeASenhaAtualEFicaComHash(): void
    {
        $semAtual = usuarioApiPlanoEdicao($this->usuarios(), 2, 2, false, $this->corpo(['senha' => 'nova-senha-456']));
        $atualErrada = usuarioApiPlanoEdicao($this->usuarios(), 2, 2, false, $this->corpo(['senha' => 'nova-senha-456', 'senha_atual' => 'errada']));
        $ok = usuarioApiPlanoEdicao($this->usuarios(), 2, 2, false, $this->corpo(['senha' => 'nova-senha-456', 'senha_atual' => self::SENHA]));

        $this->assertFalse($semAtual['ok']);
        $this->assertSame(403, $atualErrada['status']);
        $this->assertTrue($ok['ok']);
        $this->assertNotSame('nova-senha-456', $ok['dados']['senha']);
        $this->assertTrue(password_verify('nova-senha-456', $ok['dados']['senha']));
    }

    public function testSenhaDeOutroUsuarioComCUsuarioTambemVaiComHash(): void
    {
        $plano = usuarioApiPlanoEdicao($this->usuarios(), 1, 3, true, $this->corpo(['email' => 'outro@x.com', 'permissoes_id' => '2', 'situacao' => '1', 'senha' => 'definida-pelo-admin']));

        $this->assertTrue($plano['ok']);
        $this->assertTrue(password_verify('definida-pelo-admin', $plano['dados']['senha']));
    }

    public function testSenhaCurtaEhRecusada(): void
    {
        $plano = usuarioApiPlanoEdicao($this->usuarios(), 2, 2, false, $this->corpo(['senha' => 'curta', 'senha_atual' => self::SENHA]));

        $this->assertFalse($plano['ok']);
    }

    public function testCamposObrigatoriosFaltandoEhRecusado(): void
    {
        $corpo = $this->corpo();
        unset($corpo['nome']);

        $this->assertFalse(usuarioApiPlanoEdicao($this->usuarios(), 2, 2, false, $corpo)['ok']);
    }

    public function testSenhaConfereLeOHashSemExpor(): void
    {
        $model = $this->usuarios();

        $this->assertTrue($model->senhaConfere(2, self::SENHA));
        $this->assertFalse($model->senhaConfere(2, 'outra'));
        $this->assertFalse($model->senhaConfere(99, self::SENHA));
    }

    public function testApiSemSenhaLimpaRegistroListaEAninhados(): void
    {
        $registro = (object) ['idClientes' => 1, 'nome' => 'A', 'senha' => '$2y$hash'];
        $lista = [(object) ['nome' => 'B', 'senha' => 'x'], (object) ['nome' => 'C']];
        $os = (object) ['idOs' => 1, 'senha' => 'h', 'cliente' => (object) ['senha' => 'h2', 'nome' => 'D'], 'itens' => [['senha' => 'h3', 'id' => 1]]];

        $this->assertObjectNotHasProperty('senha', apiSemSenha($registro));
        $this->assertSame('A', $registro->nome);
        foreach (apiSemSenha($lista) as $linha) {
            $this->assertObjectNotHasProperty('senha', $linha);
        }
        $limpo = apiSemSenha($os);
        $this->assertObjectNotHasProperty('senha', $limpo);
        $this->assertObjectNotHasProperty('senha', $limpo->cliente);
        $this->assertArrayNotHasKey('senha', $limpo->itens[0]);
        $this->assertSame(1, $limpo->itens[0]['id']);
        $this->assertNull(apiSemSenha(null));
        $this->assertSame(['a' => 1], apiSemSenha(['a' => 1, 'senha' => 'x']));
    }
}
