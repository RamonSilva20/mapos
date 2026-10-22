<?php

/**
 * Páginas de erro da v5 (#2849): 404, erro geral, banco e 500 (exceção não
 * tratada em produção). Os templates rodam no CodeIgniter dentro de um método,
 * com variáveis soltas; aqui cada um é incluído do mesmo jeito, em um processo
 * à parte para poder escolher o ENVIRONMENT.
 */
final class PaginasDeErroTest extends MaposTestCase
{
    /**
     * @param  array<string, mixed>  $variaveis
     */
    private function renderizar(string $template, array $variaveis, string $ambiente): string
    {
        $codigo = <<<'PHP'
            define('BASEPATH', '/x/');
            define('ENVIRONMENT', $argv[2]);
            $_SERVER['SCRIPT_NAME'] = '/oficina/index.php';
            $dados = json_decode($argv[3], true);
            if (isset($dados['exception'])) {
                $dados['exception'] = new RuntimeException($dados['exception']);
            }
            extract($dados);
            include $argv[1];
            PHP;

        $saida = shell_exec(sprintf('php -r %s %s %s %s 2>&1', escapeshellarg($codigo), escapeshellarg(APPPATH . 'views/errors/html/' . $template . '.php'), escapeshellarg($ambiente), escapeshellarg((string) json_encode($variaveis))));

        return (string) $saida;
    }

    public function testPaginaDe404NoVisualDaV5(): void
    {
        $html = $this->renderizar('error_404', ['heading' => '404 Page Not Found', 'message' => '<p>The page you requested was not found.</p>'], 'production');

        $this->assertStringContainsString('<html lang="pt-br" data-tema-modo="sistema">', $html);
        $this->assertStringContainsString('<title>Página não encontrada — Map-OS</title>', $html);
        $this->assertStringContainsString('404', $html);
        $this->assertStringContainsString('href="/oficina/assets/dist/app.css"', $html, 'CSS pelo caminho da raiz do site.');
        $this->assertStringContainsString('src="/oficina/assets/js/tema.js"', $html);
        $this->assertStringContainsString('href="/oficina/" class=', $html, 'Voltar ao início.');
        $this->assertStringNotContainsString('The page you requested', $html, 'Texto em português, não o da biblioteca.');
        $this->assertStringNotContainsString('<style', $html);
        $this->assertStringNotContainsString(' style=', $html);
        $this->assertSame(1, substr_count($html, ' bg-primary '), 'Um único button-primary.');
        $this->assertStringContainsString('noindex', $html);
    }

    public function testErroDeBancoNaoMostraSqlEmProducao(): void
    {
        $dados = ['heading' => 'Database error', 'message' => '<p>Table \'mapos.clientes\' doesn\'t exist</p><p>SELECT * FROM clientes WHERE senha = 1</p>'];

        $producao = $this->renderizar('error_db', $dados, 'production');
        $this->assertStringContainsString('Banco de dados indisponível', $producao);
        $this->assertStringNotContainsString('SELECT', $producao);
        $this->assertStringNotContainsString('mapos.clientes', $producao);
        $this->assertStringNotContainsString('<pre', $producao);

        $dev = $this->renderizar('error_db', $dados, 'development');
        $this->assertStringContainsString('SELECT * FROM clientes', $dev, 'Em development o detalhe ajuda a corrigir.');
        $this->assertStringContainsString('<pre', $dev);
    }

    public function testErroGeralMostraAMensagemSoParaErros4xx(): void
    {
        $quatrocentos = $this->renderizar('error_general', ['heading' => 'Acesso negado', 'message' => '<p>Você não pode ver isto.</p>', 'status_code' => 403], 'production');
        $this->assertStringContainsString('Acesso negado', $quatrocentos);
        $this->assertStringContainsString('Você não pode ver isto.', $quatrocentos);
        $this->assertStringContainsString('403', $quatrocentos);

        $quinhentos = $this->renderizar('error_general', ['heading' => 'An Error Was Encountered', 'message' => '<p>Disk /var/www/secret full</p>', 'status_code' => 500], 'production');
        $this->assertStringContainsString('Algo deu errado', $quinhentos);
        $this->assertStringNotContainsString('/var/www/secret', $quinhentos);

        $this->assertStringContainsString('/var/www/secret', $this->renderizar('error_general', ['heading' => 'x', 'message' => '<p>Disk /var/www/secret full</p>', 'status_code' => 500], 'development'));
    }

    public function testTudoQueVemDoErroSaiEscapado(): void
    {
        $xss = '<img src=x onerror=alert(1)>';

        $geral = $this->renderizar('error_general', ['heading' => $xss, 'message' => '<p>' . $xss . '</p>', 'status_code' => 400], 'production');
        $this->assertStringNotContainsString('<img src=x', $geral);

        // Tags são removidas (strip_tags) e o que sobra sai escapado.
        $db = $this->renderizar('error_db', ['heading' => 'x', 'message' => $xss . '<p>a < b & "c"</p>'], 'development');
        $this->assertStringNotContainsString('<img src=x', $db);
        $this->assertStringNotContainsString('onerror', $db);
        $this->assertStringContainsString('a &lt; b &amp; &quot;c&quot;', $db);
    }

    public function testPagina500DaExcecaoNaoTratada(): void
    {
        $producao = $this->renderizar('error_500', ['exception' => 'segredo interno da exceção'], 'production');
        $this->assertStringContainsString('<title>Algo deu errado — Map-OS</title>', $producao);
        $this->assertStringNotContainsString('segredo interno', $producao);
        $this->assertStringContainsString('O erro foi registrado', $producao);

        $dev = $this->renderizar('error_500', ['exception' => 'segredo interno da exceção'], 'development');
        $this->assertStringContainsString('RuntimeException: segredo interno da exceção', $dev);
    }

    public function testBlocosInlineEscapamEUsamTokens(): void
    {
        $php = $this->renderizar('error_php', ['severity' => 'Warning', 'message' => '<b>x</b>', 'filepath' => '/a/b.php', 'line' => 10], 'development');
        $this->assertStringContainsString('&lt;b&gt;x&lt;/b&gt;', $php);
        $this->assertStringNotContainsString(' style=', $php);
        $this->assertStringContainsString('text-danger-ink', $php);

        $excecao = $this->renderizar('error_exception', ['message' => '<i>y</i>', 'exception' => 'boom'], 'development');
        $this->assertStringContainsString('&lt;i&gt;y&lt;/i&gt;', $excecao);
        $this->assertStringContainsString('RuntimeException', $excecao);
    }

    public function testDisplayErrorsDesligadoEmProducaoLeva500ENaoOHandlerPadrao(): void
    {
        require_once APPPATH . 'hooks/pagina_de_erro.php';
        $original = ini_get('display_errors');

        try {
            foreach (['0', '', 'off', 'Off', 'none', 'false', 'no', 'null'] as $desligado) {
                ini_set('display_errors', $desligado);
                $this->assertFalse(paginaDeErroExibeErros(), "display_errors={$desligado} é desligado (o index.php de produção usa 0).");
            }
            foreach (['1', 'On', 'stdout', 'true'] as $ligado) {
                ini_set('display_errors', $ligado);
                $this->assertTrue(paginaDeErroExibeErros(), "display_errors={$ligado} é ligado.");
            }
        } finally {
            ini_set('display_errors', (string) $original);
        }
    }

    public function testMensagemDeBancoSeparaOsParagrafos(): void
    {
        $html = $this->renderizar('error_db', ['heading' => 'x', 'message' => '<p>Erro um</p><p>SELECT 1</p>'], 'development');

        $this->assertStringContainsString("Erro um\nSELECT 1", $html);
        $this->assertStringContainsString('rel="icon"', $html);
    }

    public function testHookRegistraOHandlerQueDevolveAoPadraoComDisplayErrors(): void
    {
        $hook = (string) file_get_contents(APPPATH . 'hooks/pagina_de_erro.php');

        $this->assertStringContainsString('_exception_handler($exception)', $hook, 'Com display_errors ligado ou CLI, o handler do CodeIgniter.');
        $this->assertStringContainsString("log_exception('error'", $hook, 'Continua registrando no log.');
        $this->assertStringContainsString('set_status_header(500)', $hook);
        $this->assertStringContainsString("errors/html/error_500.php", $hook);

        $config = (string) file_get_contents(APPPATH . 'config/hooks.php');
        $this->assertLessThan(strpos($config, 'bootWhoops'), strpos($config, 'registrarPaginaDeErro'), 'O Whoops registra depois e vence em development.');
    }
}
