<?php

// A view usa os helpers de URL do CodeIgniter, que o harness não carrega.
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
 * Telas de login do painel (views/mapos/login.php) e da área do cliente
 * (views/conecte/login.php), renderizadas fora do framework com $this falso.
 * As duas são superfícies de entrada do DESIGN.md (#2918).
 */
final class LoginViewTest extends MaposTestCase
{
    private function renderizar(array $dados = [], array $config = [], string $view = 'mapos/login'): string
    {
        $config += ['app_name' => 'Map-OS', 'app_version' => '5.0.0-alpha', 'app_subname' => 'Sistema de Controle de Ordens de Serviço'];

        $contexto = new class($config) {
            public object $config;
            public object $security;

            public function __construct(array $itens)
            {
                $this->config = new class($itens) {
                    public function __construct(private array $itens)
                    {
                    }

                    public function item($nome)
                    {
                        return $this->itens[$nome] ?? null;
                    }
                };

                $this->security = new class() {
                    public function get_csrf_token_name()
                    {
                        return 'MAPOS_CSRF_TOKEN';
                    }

                    public function get_csrf_hash()
                    {
                        return 'hash"<x>';
                    }
                };
            }
        };

        $render = function (array $__dados, string $__view) {
            extract($__dados);
            ob_start();
            include APPPATH . 'views/' . $__view . '.php';

            return ob_get_clean();
        };

        return Closure::bind($render, $contexto, $contexto::class)($dados + ['erro' => null, 'sucesso' => null, 'email' => ''], $view);
    }

    public function testCamposComLabelAutocompleteEObrigatoriedade(): void
    {
        $html = $this->renderizar();

        $this->assertMatchesRegularExpression('/<label for="email"[^>]*>\s*E-mail/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="email"[^>]*id="email"[^>]*name="email"[^>]*autocomplete="username"[^>]*required/', $html);
        $this->assertMatchesRegularExpression('/<label for="senha"[^>]*>\s*Senha/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="password"[^>]*id="senha"[^>]*name="senha"[^>]*autocomplete="current-password"[^>]*required/', $html);
        $this->assertMatchesRegularExpression('/<button[^>]*id="btn-acessar"[^>]*type="submit"/', $html);
    }

    public function testFormularioUsaOModuloENaoTemScriptInline(): void
    {
        $html = $this->renderizar();

        $this->assertStringContainsString('data-module="login/formulario"', $html);
        $this->assertStringContainsString('action="http://mapos.test/index.php/login/verificarLogin?ajax=true"', $html);
        $this->assertStringContainsString('data-destino="http://mapos.test/index.php/mapos"', $html);
        $this->assertSame(0, preg_match('/<script(?![^>]*\bsrc=)[^>]*>/', $html), 'Nenhum <script> sem src.');
        $this->assertStringNotContainsString('jquery', strtolower($html));
        $this->assertStringNotContainsString('matrix-login', $html);
        $this->assertStringNotContainsString('validate.js', $html);
    }

    public function testCsrfEscapadoNoCampoENasMetas(): void
    {
        $html = $this->renderizar();

        $this->assertStringContainsString('name="MAPOS_CSRF_TOKEN" value="hash&quot;&lt;x&gt;"', $html);
        $this->assertStringContainsString('<meta name="csrf-token-name" content="MAPOS_CSRF_TOKEN">', $html);
        $this->assertStringContainsString('<meta name="csrf-cookie-name"', $html);
    }

    public function testSemErroOAvisoFicaEscondido(): void
    {
        $this->assertMatchesRegularExpression('/<div data-login-mensagem hidden>/', $this->renderizar());
    }

    public function testErroDoFlashApareceEscapadoNumAlert(): void
    {
        $html = $this->renderizar(['erro' => 'Sessão <b>encerrada</b>']);

        $this->assertMatchesRegularExpression('/<div data-login-mensagem>/', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('<span data-login-texto>Sessão &lt;b&gt;encerrada&lt;/b&gt;</span>', $html);
    }

    public function testNomeDoSistemaEVersaoEscapados(): void
    {
        $html = $this->renderizar([], ['app_name' => 'Oficina "<Zé>"', 'app_version' => '5<1']);

        $this->assertStringContainsString('<title>Entrar — Oficina &quot;&lt;Zé&gt;&quot;</title>', $html);
        $this->assertMatchesRegularExpression('#<span class="[^"]*font-display[^"]*">Oficina &quot;&lt;Zé&gt;&quot;</span>#', $html);
        $this->assertStringContainsString('Versão 5&lt;1', $html);
        $this->assertStringNotContainsString('<Zé>', $html);
    }

    /**
     * A superfície de entrada é sempre escura: não segue o modo de cor do
     * painel (sem .dark, sem tema.js) e o card usa o escopo de tokens
     * .superficie-entrada, com os campos claros.
     */
    public function testSuperficieDeEntradaNaoSegueOModoDoPainel(): void
    {
        foreach (['mapos/login', 'conecte/login'] as $view) {
            $html = $this->renderizar([], [], $view);

            $this->assertStringContainsString('<html lang="pt-br" class="scheme-dark">', $html, $view);
            $this->assertStringNotContainsString('tema.js', $html, $view);
            $this->assertStringContainsString('bg-canvas-dark bg-[url(../img/entrada/estrelas.svg)]', $html, $view);
            $this->assertMatchesRegularExpression('/<section class="superficie-entrada [^"]*bg-night/', $html, $view);
            $this->assertMatchesRegularExpression('/<button[^>]*class="[^"]*bg-primary[^"]*shadow-elev-3/', $html, $view);
            $this->assertStringContainsString('class="rounded-xs bg-accent-lime px-3 text-ink"', $html, $view);
        }
    }

    public function testCadaLoginApontaParaOOutroNaTopNav(): void
    {
        $painel = $this->renderizar();
        $cliente = $this->renderizar([], [], 'conecte/login');

        $this->assertMatchesRegularExpression('#<a(?=[^>]*href="http://mapos.test/index.php/mine")(?=[^>]*bg-on-dark-faint)#', $painel);
        $this->assertMatchesRegularExpression('#<a(?=[^>]*href="http://mapos.test/index.php/login")(?=[^>]*bg-on-dark-faint)#', $cliente);

        // No celular o botão vira só ícone, com 44px (max-sm:size-11).
        $this->assertMatchesRegularExpression('#<a(?=[^>]*href="http://mapos.test/index.php/mine")(?=[^>]*max-sm:hidden)#', $painel);
        $this->assertMatchesRegularExpression('#<a(?=[^>]*href="http://mapos.test/index.php/mine")(?=[^>]*max-sm:size-11)(?=[^>]*sm:hidden)(?=[^>]*title="Área do cliente")#', $painel);
    }

    public function testLoginDoClienteUsaOMesmoModulo(): void
    {
        $html = $this->renderizar([], [], 'conecte/login');

        $this->assertStringContainsString('data-module="login/formulario"', $html);
        $this->assertStringContainsString('action="http://mapos.test/index.php/mine/login?ajax=true"', $html);
        $this->assertStringContainsString('data-destino="http://mapos.test/index.php/mine/painel"', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/mine/resetarSenha"', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/mine/cadastrar"', $html);
        $this->assertSame(0, preg_match('/<script(?![^>]*\bsrc=)[^>]*>/', $html), 'Nenhum <script> sem src.');
        $this->assertStringNotContainsString('jquery', strtolower($html));
        $this->assertStringNotContainsString('bx-', $html);
    }

    public function testLoginDoClienteMostraMensagensEPreencheOEmail(): void
    {
        $html = $this->renderizar(['erro' => 'Falhou <b>', 'sucesso' => 'Cadastro <i>feito</i>', 'email' => 'zé@x.com"'], [], 'conecte/login');

        $this->assertStringContainsString('<span data-login-texto>Falhou &lt;b&gt;</span>', $html);
        $this->assertStringContainsString('Cadastro &lt;i&gt;feito&lt;/i&gt;', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*id="email"[^>]*value="zé@x.com&quot;"/', $html);
        // Com o e-mail preenchido, o foco vai para a senha.
        $this->assertMatchesRegularExpression('/<input[^>]*id="senha"[^>]*autofocus/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*id="email"[^>]*autofocus/', $html);
    }
}
