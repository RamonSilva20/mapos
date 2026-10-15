<?php

require_once APPPATH . 'models/Clientes_model.php';
require_once APPPATH . 'controllers/Clientes.php';

// O audit_helper (log_info) e o url_helper não são carregados nos testes.
if (! function_exists('log_info')) {
    function log_info($task)
    {
    }
}

if (! function_exists('base_url')) {
    function base_url($uri = '')
    {
        return 'http://mapos.test/' . ltrim((string) $uri, '/');
    }
}

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

/**
 * Cadastro rápido de cliente no modal da OS e da venda (#3003): o endpoint
 * JSON executado de verdade (controller, model, helper e SQLite), com as
 * dependências do CodeIgniter trocadas por fakes. A validação do
 * form_validation é trocada por uma que repete as regras sem banco
 * (nome obrigatório, e-mail válido); o e-mail duplicado é conferido pelo
 * emailExists() real do model.
 */
final class ClientesCadastroRapidoTest extends MaposTestCase
{
    private const POST = [
        'fornecedor' => '0',
        'documento' => '123.456.789-09',
        'nomeCliente' => ' Maria da Silva ',
        'contato' => '',
        'telefone' => '',
        'celular' => '(11) 98888-7777',
        'email' => 'maria@x.com',
        'cep' => '',
        'rua' => '',
        'numero' => '',
        'complemento' => '',
        'bairro' => '',
        'cidade' => '',
        'estado' => '',
    ];

    private function criarBanco(): void
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, contato TEXT, documento TEXT, telefone TEXT, celular TEXT, email TEXT,
            cep TEXT, rua TEXT, numero TEXT, complemento TEXT, bairro TEXT, cidade TEXT, estado TEXT, pessoa_fisica INTEGER, fornecedor INTEGER, senha TEXT, dataCadastro TEXT)');
    }

    private function controller(array $post, bool $permitido = true, string $metodo = 'post'): object
    {
        $model = $this->makeInstance(Clientes_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        $controller = new class() extends Clientes {
            public $clientes_model;

            public $input;

            public $output;

            public $permission;

            public $session;

            public function __construct()
            {
            }

            protected function validarFormulario(string $regras): array
            {
                $erros = [];
                if (trim((string) $this->input->post('nomeCliente')) === '') {
                    $erros['nomeCliente'] = 'O campo Nome ou razão social é obrigatório.';
                }
                $email = trim((string) $this->input->post('email'));
                if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $erros['email'] = 'O campo E-mail deve conter um e-mail válido.';
                }

                return $erros;
            }
        };

        $controller->clientes_model = $model;
        $controller->input = new class($post, $metodo) {
            public function __construct(private array $post, private string $metodo)
            {
            }

            public function method()
            {
                return $this->metodo;
            }

            public function post($chave = null)
            {
                return $chave === null ? $this->post : ($this->post[$chave] ?? null);
            }
        };
        $controller->output = new class() {
            public int $status = 200;

            public string $corpo = '';

            public function set_status_header($status)
            {
                $this->status = $status;

                return $this;
            }

            public function set_content_type($tipo, $charset = null)
            {
                return $this;
            }

            public function set_output($corpo)
            {
                $this->corpo = $corpo;

                return $this;
            }
        };
        $controller->permission = new class($permitido) {
            public array $pedidas = [];

            public function __construct(private bool $permitido)
            {
            }

            public function checkPermission($grupo, $permissao)
            {
                $this->pedidas[] = $permissao;

                return $this->permitido;
            }
        };
        $controller->session = new class() {
            public function userdata($chave)
            {
                return ['permissao' => '1'][$chave] ?? null;
            }
        };

        return $controller;
    }

    private function resposta(object $controller): array
    {
        return [$controller->output->status, json_decode($controller->output->corpo, true)];
    }

    private function total(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) AS n FROM clientes')->row()->n;
    }

    public function testCadastraEDevolveOClienteNoFormatoDoAutocomplete(): void
    {
        $this->criarBanco();
        $controller = $this->controller(self::POST);

        $controller->cadastroRapido();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(201, $status);
        $this->assertTrue($corpo['result']);
        $this->assertSame(['id', 'valor', 'detalhe'], array_keys($corpo['cliente']));
        $this->assertSame('1', $corpo['cliente']['id']);
        $this->assertSame('Maria da Silva', $corpo['cliente']['valor']);
        $this->assertSame('123.456.789-09 · (11) 98888-7777', $corpo['cliente']['detalhe']);
        $this->assertStringNotContainsString('senha', $controller->output->corpo, 'A resposta não leva a senha nem o hash.');
        $this->assertSame(['aCliente'], $controller->permission->pedidas);

        $gravado = $this->db->query('SELECT * FROM clientes WHERE idClientes = 1')->row();
        $this->assertSame('Maria da Silva', $gravado->nomeCliente);
        $this->assertSame(date('Y-m-d'), $gravado->dataCadastro);
        $this->assertSame(1, (int) $gravado->pessoa_fisica);
        // Sem senha no modal: a senha é a do cadastro normal, o documento só com números.
        $this->assertTrue(password_verify('12345678909', $gravado->senha));
    }

    public function testSenhaDoPostEhIgnorada(): void
    {
        $this->criarBanco();
        $controller = $this->controller(['senha' => 'hackeada'] + self::POST);

        $controller->cadastroRapido();

        $this->assertSame(201, $this->resposta($controller)[0]);
        $gravado = $this->db->query('SELECT senha FROM clientes')->row();
        $this->assertFalse(password_verify('hackeada', $gravado->senha), 'A senha do POST é ignorada.');
        $this->assertTrue(password_verify('12345678909', $gravado->senha));
    }

    public function testSemNomeDa422ComOErroNoCampoENaoGrava(): void
    {
        $this->criarBanco();
        $controller = $this->controller(['nomeCliente' => '   '] + self::POST);

        $controller->cadastroRapido();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertFalse($corpo['result']);
        $this->assertSame(['nomeCliente'], array_keys($corpo['erros']));
        $this->assertSame(0, $this->total());
    }

    public function testEmailDuplicadoDa422ComOErroNoCampo(): void
    {
        $this->criarBanco();
        $this->db->query("INSERT INTO clientes (nomeCliente, email) VALUES ('Outra', 'maria@x.com')");
        $controller = $this->controller(self::POST);

        $controller->cadastroRapido();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertSame(['email' => 'Este e-mail já está sendo usado por outro cliente.'], $corpo['erros']);
        $this->assertSame(1, $this->total(), 'Nada foi gravado.');
    }

    public function testSemAClienteDa403ESemGravarNada(): void
    {
        $this->criarBanco();
        $controller = $this->controller(self::POST, false);

        $controller->cadastroRapido();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(403, $status);
        $this->assertFalse($corpo['result']);
        $this->assertArrayNotHasKey('cliente', $corpo);
        $this->assertSame(0, $this->total());
    }

    public function testSoAceitaPost(): void
    {
        $this->criarBanco();
        $controller = $this->controller(self::POST, true, 'get');

        $controller->cadastroRapido();

        $this->assertSame(405, $this->resposta($controller)[0]);
        $this->assertSame(0, $this->total());
    }

    // --- View ----------------------------------------------------------

    private function renderizar(string $view): string
    {
        $carregador = new class() {
            public object $load;

            public object $security;

            public function __construct()
            {
                $this->load = $this;
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

            public function render(string $nome): string
            {
                return (function () use ($nome) {
                    ob_start();
                    include APPPATH . 'views' . DIRECTORY_SEPARATOR . $nome . '.php';

                    return (string) ob_get_clean();
                })();
            }
        };

        return $carregador->render($view);
    }

    public function testModalTemOsCamposDoCadastroSemASenhaEComOsLinks(): void
    {
        $html = $this->renderizar('clientes/partes/cadastro-rapido');

        $this->assertStringContainsString('data-module="clientes/cadastro-rapido"', $html);
        $this->assertStringContainsString('id="cliente-rapido"', $html);
        $this->assertStringContainsString('clientes/cadastroRapido', $html);
        $this->assertStringContainsString('hash-csrf', $html);
        foreach (['fornecedor', 'documento', 'nomeCliente', 'contato', 'telefone', 'celular', 'email', 'cep', 'rua', 'numero', 'complemento', 'bairro', 'cidade', 'estado'] as $campo) {
            $this->assertStringContainsString('name="' . $campo . '"', $html);
        }
        $this->assertStringNotContainsString('name="senha"', $html);
        $this->assertStringContainsString('data-buscar-cnpj', $html);
        $this->assertStringContainsString('Cadastro completo', $html);
        $this->assertStringContainsString('clientes/adicionar', $html);
        $this->assertStringContainsString('Cadastrar cliente', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    public function testOsEVendaSoIncluemOModalComAClientesDepoisDoFormulario(): void
    {
        foreach (['os', 'vendas'] as $tela) {
            $codigo = (string) file_get_contents(APPPATH . 'views/' . $tela . '/formulario.php');

            $this->assertStringContainsString("'data-autocomplete-novo-modal' => 'cliente-rapido'", $codigo);
            $inclusao = strpos($codigo, "view('clientes/partes/cadastro-rapido')");
            $this->assertNotFalse($inclusao);
            $this->assertStringContainsString("if (\$pode['cadastrar_cliente'])", substr($codigo, $inclusao - 120, 120));
            // Depois do </form> da tela, para não aninhar formulários.
            $this->assertGreaterThan((int) strrpos($codigo, '</form>'), $inclusao);
        }
    }
}
