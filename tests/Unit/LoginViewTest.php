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
 * Tela de login do painel (views/mapos/login.php), renderizada fora do
 * framework com $this falso.
 */
final class LoginViewTest extends MaposTestCase
{
    private function renderizar(array $dados = [], array $config = []): string
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

        $render = function (array $__dados) {
            extract($__dados);
            ob_start();
            include APPPATH . 'views/mapos/login.php';

            return ob_get_clean();
        };

        return Closure::bind($render, $contexto, $contexto::class)($dados + ['configuration' => [], 'erro' => null]);
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
        $this->assertStringContainsString('alt="Oficina &quot;&lt;Zé&gt;&quot;"', $html);
        $this->assertStringContainsString('Versão: 5&lt;1', $html);
        $this->assertStringNotContainsString('<Zé>', $html);
    }

    public function testTemaDaConfiguracaoVaiParaOHtml(): void
    {
        $html = $this->renderizar(['configuration' => ['app_tema_modo' => 'escuro', 'app_tema_destaque' => 'verde']]);

        $this->assertStringContainsString('<html lang="pt-br" class="dark" data-tema-modo="escuro" data-accent="verde">', $html);
        $this->assertStringContainsString('assets/js/tema.js', $html);
    }

    public function testSemConfiguracaoUsaOTemaPadrao(): void
    {
        $this->assertStringContainsString('data-tema-modo="claro" data-accent="laranja"', $this->renderizar());
    }
}
