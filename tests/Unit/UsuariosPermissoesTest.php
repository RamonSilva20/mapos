<?php

require_once APPPATH . 'models/Usuarios_model.php';
require_once APPPATH . 'models/Permissoes_model.php';
require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'validation_helper.php';

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

if (! function_exists('current_url')) {
    function current_url()
    {
        return 'http://mapos.test/index.php/usuarios/adicionar';
    }
}

/**
 * Usuários e Permissões migrados (#2846): regras puras, consultas no SQLite,
 * views e checagens do controller.
 */
final class UsuariosPermissoesTest extends MaposTestCase
{
    // --- Permissões ---------------------------------------------------------

    public function testCodigosCobremAMatrizRelatoriosESistema(): void
    {
        $codigos = permissoesCodigos();

        $this->assertCount(count(PERMISSOES_MODULOS) * 4 + count(PERMISSOES_RELATORIOS) + count(PERMISSOES_SISTEMA), $codigos);
        $this->assertSame(['vCliente', 'aCliente', 'eCliente', 'dCliente'], array_slice($codigos, 0, 4));
        $this->assertContains('cPermissao', $codigos);
        $this->assertContains('rFinanceiro', $codigos);
        $this->assertNotContains('vPagamento', $codigos, 'A tabela de pagamentos saiu em 2021.');
    }

    /**
     * Toda permissão que alguma rota exige precisa poder ser marcada no
     * formulário, senão nenhum grupo novo chega à tela.
     */
    public function testTodaPermissaoDoMapaDeRotasEstaNoFormulario(): void
    {
        $mapa = (string) file_get_contents(APPPATH . 'config/permissions_map.php');
        preg_match_all("/'([vaedcr][A-Z][A-Za-z]+)'/", $mapa, $usadas);

        $faltando = array_diff(array_unique($usadas[1]), permissoesCodigos());
        $this->assertSame([], array_values($faltando));
    }

    public function testDoFormularioSoAceitaCodigosConhecidos(): void
    {
        $mapa = permissoesDoFormulario(['vCliente', 'aVenda', 'hack', 7, ['x']]);

        $this->assertSame(1, $mapa['vCliente']);
        $this->assertSame(1, $mapa['aVenda']);
        $this->assertSame(0, $mapa['dCliente']);
        $this->assertArrayNotHasKey('hack', $mapa);
        $this->assertSame(permissoesCodigos(), array_keys($mapa));
        $this->assertSame(0, array_sum(permissoesDoFormulario('vCliente')), 'Sem array, nada marcado.');
    }

    public function testMarcadasLeJsonEOSerializeDaV4(): void
    {
        $this->assertSame(['vCliente', 'cUsuario'], permissoesMarcadas('{"vCliente":"1","aCliente":"","cUsuario":1,"vPagamento":"1"}'));
        $this->assertSame(['vOs'], permissoesMarcadas(serialize(['vOs' => '1', 'aOs' => null])));
        $this->assertSame([], permissoesMarcadas(''));
        $this->assertSame([], permissoesMarcadas(null));
        $this->assertSame([], permissoesMarcadas('"texto"'));
    }

    public function testDadosDoGrupo(): void
    {
        [$dados, $erros] = permissaoDadosDoFormulario(['nome' => '  Vendedores ', 'permissoes' => ['vVenda']], true);
        $this->assertSame([], $erros);
        $this->assertSame('Vendedores', $dados['nome']);
        $this->assertSame(1, $dados['situacao'], 'Grupo novo nasce ativo.');
        $this->assertSame(['vVenda'], permissoesMarcadas($dados['permissoes']));

        [$dados] = permissaoDadosDoFormulario(['nome' => 'X'], false);
        $this->assertSame(0, $dados['situacao'], 'Ao editar, sem a caixa marcada, fica inativo.');

        [, $erros] = permissaoDadosDoFormulario(['nome' => str_repeat('a', 81)], true);
        $this->assertArrayHasKey('nome', $erros);
        [, $erros] = permissaoDadosDoFormulario(['nome' => ['x']], true);
        $this->assertArrayHasKey('nome', $erros);
    }

    // --- Usuários -----------------------------------------------------------

    private function post(array $mais = []): array
    {
        return $mais + [
            'nome' => 'Ana', 'cpf' => '529.982.247-25', 'email' => 'Ana@Example.com', 'telefone' => '11 3333-4444',
            'senha' => 'segredo123', 'situacao' => '1', 'permissoes_id' => '2', 'estado' => 'sp', 'dataExpiracao' => '',
        ];
    }

    public function testDadosDoUsuario(): void
    {
        [$dados, $erros, $senha] = usuarioDadosDoFormulario($this->post(['idUsuarios' => '1', 'senha_hash' => 'x']), true);

        $this->assertSame([], $erros);
        $this->assertSame('segredo123', $senha);
        $this->assertSame('ana@example.com', $dados['email']);
        $this->assertSame('SP', $dados['estado']);
        $this->assertNull($dados['dataExpiracao']);
        $this->assertSame(1, $dados['situacao']);
        $this->assertSame(2, $dados['permissoes_id']);
        $this->assertArrayNotHasKey('senha', $dados, 'O hash é do controller.');
        $this->assertArrayNotHasKey('idUsuarios', $dados, 'Só os campos do formulário.');
    }

    public function testSenhaObrigatoriaSoAoCadastrarEComMinimo(): void
    {
        [, $erros, $senha] = usuarioDadosDoFormulario($this->post(['senha' => '']), false);
        $this->assertSame([], $erros, 'Editar sem senha mantém a atual.');
        $this->assertNull($senha);

        [, $erros] = usuarioDadosDoFormulario($this->post(['senha' => '']), true);
        $this->assertArrayHasKey('senha', $erros);

        [, $erros] = usuarioDadosDoFormulario($this->post(['senha' => '1234567']), false);
        $this->assertSame('A senha tem pelo menos 8 caracteres.', $erros['senha']);
    }

    public function testCamposInvalidos(): void
    {
        [, $erros] = usuarioDadosDoFormulario($this->post(['estado' => 'XX', 'dataExpiracao' => '31/12/2026', 'situacao' => '2', 'permissoes_id' => '0']), true);

        $this->assertSame(['estado', 'dataExpiracao', 'situacao', 'permissoes_id'], array_keys($erros));

        [$dados, $erros] = usuarioDadosDoFormulario($this->post(['dataExpiracao' => '2026-12-31', 'estado' => '']), true);
        $this->assertSame([], $erros);
        $this->assertSame('2026-12-31', $dados['dataExpiracao']);
        $this->assertSame('', $dados['estado']);
    }

    public function testValoresDaTelaNuncaTrazemASenha(): void
    {
        $usuario = (object) ['nome' => 'Ana', 'email' => 'a@b.c', 'situacao' => 1, 'permissoes_id' => 3, 'dataExpiracao' => '2026-12-31 00:00:00', 'senha' => 'hash'];

        $valores = usuarioValoresDoFormulario(null, $usuario);
        $this->assertSame('Ana', $valores['nome']);
        $this->assertSame('3', $valores['permissoes_id']);
        $this->assertSame('2026-12-31', $valores['dataExpiracao']);
        $this->assertArrayNotHasKey('senha', $valores);

        $this->assertArrayNotHasKey('senha', usuarioValoresDoFormulario(['senha' => 'x', 'nome' => 'B'], null));
        $this->assertSame('1', usuarioValoresDoFormulario(null, null)['situacao'], 'Usuário novo nasce ativo.');
    }

    public function testSituacao(): void
    {
        $this->assertSame('Inativo', usuarioSituacaoPill((object) ['situacao' => 0, 'dataExpiracao' => null], '2026-10-09')['label']);
        $this->assertSame(['label' => 'Expirado', 'variant' => 'warning'], usuarioSituacaoPill((object) ['situacao' => 1, 'dataExpiracao' => '2026-10-08'], '2026-10-09'));
        $this->assertSame('Ativo', usuarioSituacaoPill((object) ['situacao' => 1, 'dataExpiracao' => '2026-10-09'], '2026-10-09')['label'], 'No último dia ainda entra.');
        $this->assertSame('Ativo', usuarioSituacaoPill((object) ['situacao' => '1', 'dataExpiracao' => '0000-00-00'], '2026-10-09')['label']);
    }

    public function testQuemNaoPodeSerRemovido(): void
    {
        $this->assertNotNull(usuarioPodeSerRemovido(1, 5), 'Super admin.');
        $this->assertNotNull(usuarioPodeSerRemovido(5, 5), 'O próprio usuário.');
        $this->assertNull(usuarioPodeSerRemovido(6, 5));
    }

    // --- Consultas ----------------------------------------------------------

    private function banco(): void
    {
        $this->db->query('CREATE TABLE permissoes (idPermissao INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, permissoes TEXT, situacao INTEGER, data TEXT)');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, rg TEXT, cpf TEXT, cep TEXT, rua TEXT, numero TEXT, bairro TEXT, cidade TEXT,
            estado TEXT, email TEXT, telefone TEXT, celular TEXT, dataCadastro TEXT, dataExpiracao TEXT, situacao INTEGER, permissoes_id INTEGER, url_image_user TEXT, senha TEXT)');
        foreach (['os', 'vendas', 'lancamentos', 'garantias'] as $tabela) {
            $this->db->query("CREATE TABLE {$tabela} (id INTEGER PRIMARY KEY AUTOINCREMENT, usuarios_id INTEGER)");
        }

        $this->db->query("INSERT INTO permissoes (nome, situacao, data) VALUES ('Administrador', 1, '2026-01-01'), ('Vendas', 1, '2026-02-01'), ('Antigo', 0, '2025-01-01')");
        $this->db->query("INSERT INTO usuarios (nome, cpf, email, telefone, situacao, permissoes_id, senha) VALUES
            ('Admin', '1', 'admin@x', '11', 1, 1, 'h'), ('Bruno', '2', 'bruno@x', '22', 1, 2, 'h'), ('Carla 100%', '3', 'carla@x', '33', 0, 2, 'h'), ('Davi', '4', 'davi@x', '44', 1, 9, 'h')");
        $this->db->query('INSERT INTO vendas (usuarios_id) VALUES (2), (2)');
        $this->db->query('INSERT INTO garantias (usuarios_id) VALUES (2)');
    }

    private function usuarios(): Usuarios_model
    {
        $model = $this->makeInstance(Usuarios_model::class);
        $model->db = $this->db;

        return $model;
    }

    public function testListagemDeUsuarios(): void
    {
        $this->banco();
        $model = $this->usuarios();

        $todos = $model->listar([], 10, 0);
        $this->assertSame(['Admin', 'Bruno', 'Carla 100%', 'Davi'], array_column($todos, 'nome'));
        $this->assertSame('Vendas', $todos[1]->permissao);
        $this->assertNull($todos[3]->permissao, 'Grupo removido não some com o usuário.');
        $this->assertObjectNotHasProperty('senha', $todos[0], 'O hash da senha nunca sai na listagem.');

        $this->assertSame(['Carla 100%'], array_column($model->listar(['situacao' => 'inativo'], 10, 0), 'nome'));
        $this->assertSame(3, $model->contar(['situacao' => 'ativo']));
        $this->assertSame(['Carla 100%'], array_column($model->listar(['pesquisa' => '100%'], 10, 0), 'nome'));
        $this->assertSame(['Bruno'], array_column($model->listar(['pesquisa' => 'bruno@'], 10, 0), 'nome'));
        $this->assertSame(['Bruno'], array_column($model->listar([], 1, 1), 'nome'));
    }

    public function testReferenciasImpedemAExclusao(): void
    {
        $this->banco();
        $model = $this->usuarios();

        $this->assertSame(3, $model->referencias(2));
        $this->assertSame(0, $model->referencias(3));
    }

    public function testGruposComQuantosUsuarios(): void
    {
        $this->banco();
        $model = $this->makeInstance(Permissoes_model::class);
        $model->db = $this->db;

        $grupos = $model->listarComUsuarios();
        $this->assertSame(['Administrador', 'Antigo', 'Vendas'], array_column($grupos, 'nome'));
        $this->assertSame([1, 0, 2], array_map('intval', array_column($grupos, 'usuarios')));
    }

    // --- Views --------------------------------------------------------------

    private function renderizar(string $view, array $variaveis): string
    {
        $carregador = new class() {
            public object $security;

            public function __construct()
            {
                $this->security = new class() {
                    public function get_csrf_token_name()
                    {
                        return 'MAPOS_TOKEN';
                    }

                    public function get_csrf_hash()
                    {
                        return 'hash-csrf';
                    }
                };
            }

            public function render(string $view, array $variaveis): string
            {
                extract($variaveis);
                ob_start();
                include APPPATH . 'views/' . $view . '.php';

                return (string) ob_get_clean();
            }
        };

        return $carregador->render($view, $variaveis);
    }

    public function testListagemEFormularioDeUsuarioEscapamEProtegemOAdmin(): void
    {
        $xss = '<script>alert(1)</script>';
        $linhas = [
            (object) ['idUsuarios' => 1, 'nome' => 'Admin', 'email' => 'a@x', 'telefone' => '1', 'permissao' => 'Administrador', 'situacao' => 1, 'dataExpiracao' => null],
            (object) ['idUsuarios' => 7, 'nome' => 'Bia ' . $xss, 'email' => 'b@x', 'telefone' => '2', 'permissao' => null, 'situacao' => 1, 'dataExpiracao' => '2020-01-01'],
        ];

        $html = $this->renderizar('usuarios/usuarios', ['results' => $linhas, 'filtros' => [], 'total' => 2, 'logado' => 1, 'hoje' => '2026-10-09', 'paginacao' => paginacaoProps(['base_url' => 'u', 'total_rows' => 2, 'per_page' => 10, 'offset' => 0])]);
        $this->assertStringNotContainsString($xss, $html);
        $this->assertStringContainsString('Bia &lt;script&gt;', $html);
        $this->assertStringContainsString('Expirado', $html);
        $this->assertStringContainsString('Grupo removido', $html);
        $this->assertStringContainsString('data-valor-id="7"', $html);
        $this->assertStringNotContainsString('data-valor-id="1"', $html, 'O admin (e o logado) não tem o botão de excluir.');
        $this->assertStringContainsString('action="http://mapos.test/index.php/usuarios/excluir"', $html);
        $this->assertStringNotContainsString('<script', $html);

        $form = $this->renderizar('usuarios/formulario', [
            'usuario' => (object) ['idUsuarios' => 1, 'nome' => 'Admin ' . $xss],
            'valores' => usuarioValoresDoFormulario(null, (object) ['nome' => 'Admin ' . $xss, 'situacao' => 1, 'permissoes_id' => 1]),
            'erros' => [],
            'grupos' => ['1' => 'Administrador'],
            'protegido' => true,
        ]);
        $this->assertStringNotContainsString($xss, $form);
        $this->assertStringContainsString('Este usuário não pode ser desativado.', $form);
        $this->assertStringContainsString('<input type="hidden" name="situacao" value="1">', $form);
        $this->assertStringContainsString('Deixe em branco para manter a senha atual', $form);
        $this->assertStringContainsString('data-module="usuarios/formulario"', $form);
        $this->assertStringNotContainsString('<script', $form);
    }

    public function testFormularioDePermissoesMarcaOGravado(): void
    {
        $html = $this->renderizar('permissoes/formulario', [
            'grupo' => (object) ['idPermissao' => 2, 'nome' => 'Vendas <b>'],
            'valores' => ['nome' => 'Vendas <b>', 'situacao' => true, 'marcadas' => ['vVenda', 'cEmail']],
            'erros' => [],
            'do_logado' => false,
        ]);

        $this->assertStringNotContainsString('Vendas <b>', $html);
        $this->assertSame(count(permissoesCodigos()), substr_count($html, 'name="permissoes[]"'));
        $this->assertMatchesRegularExpression('/id="permissao-vVenda"[^>]*\\schecked(?=[\\s>])/', $html);
        $this->assertMatchesRegularExpression('/id="permissao-cEmail"[^>]*\\schecked(?=[\\s>])/', $html);
        $this->assertDoesNotMatchRegularExpression('/id="permissao-aVenda"[^>]*\\schecked(?=[\\s>])/', $html);
        $this->assertStringContainsString('Ver — Clientes e fornecedores', $html, 'Cada caixa da matriz tem nome acessível.');
        $this->assertStringContainsString('data-module="permissoes/formulario"', $html);

        $lista = $this->renderizar('permissoes/permissoes', ['results' => [
            (object) ['idPermissao' => 1, 'nome' => 'Admin', 'data' => '2026-01-01', 'situacao' => 1, 'usuarios' => 1],
            (object) ['idPermissao' => 2, 'nome' => 'Vendas', 'data' => '2026-01-01', 'situacao' => 1, 'usuarios' => 3],
            (object) ['idPermissao' => 3, 'nome' => 'Antigo', 'data' => '2026-01-01', 'situacao' => 0, 'usuarios' => 0],
        ], 'grupo_logado' => 1]);
        $this->assertStringContainsString('data-valor-id="2"', $lista);
        $this->assertStringNotContainsString('data-valor-id="1"', $lista, 'O grupo do logado não pode ser desativado.');
        $this->assertStringNotContainsString('data-valor-id="3"', $lista, 'Grupo inativo não tem o botão de desativar.');
    }

    public function testControllersUsamOIdDaUrlEExcluemSoPorPost(): void
    {
        $usuarios = (string) file_get_contents(APPPATH . 'controllers/Usuarios.php');
        $permissoes = (string) file_get_contents(APPPATH . 'controllers/Permissoes.php');

        $this->assertStringNotContainsString("post('idUsuarios')", $usuarios);
        $this->assertStringNotContainsString("post('idPermissao')", $permissoes);
        $this->assertStringContainsString("\$this->input->method() !== 'post'", $usuarios);
        $this->assertStringContainsString("validarFormulario('usuarios_formulario')", $usuarios);
        $this->assertStringContainsString('password_hash($senha, PASSWORD_DEFAULT)', $usuarios);
        $this->assertStringNotContainsString('uri->segment(3);' . "\n" . '        $this->usuarios_model->delete', $usuarios);
    }
}
