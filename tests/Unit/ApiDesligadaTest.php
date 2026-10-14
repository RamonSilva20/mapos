<?php

/**
 * API desligada (#2879): com API_ENABLED falso nenhum controller de api/
 * responde, nem por URL direta de controller (o roteamento padrão do CI
 * ignora as rotas condicionais de routes.php).
 */
final class ApiDesligadaTest extends MaposTestCase
{
    private mixed $anterior;

    protected function setUp(): void
    {
        parent::setUp();
        $this->anterior = $_ENV['API_ENABLED'] ?? null;
        require_once APPPATH . 'helpers/general_helper.php';
    }

    protected function tearDown(): void
    {
        if ($this->anterior === null) {
            unset($_ENV['API_ENABLED']);
        } else {
            $_ENV['API_ENABLED'] = $this->anterior;
        }
        parent::tearDown();
    }

    public function testApiHabilitadaSegueAFlag(): void
    {
        unset($_ENV['API_ENABLED']);
        $this->assertFalse(apiHabilitada());

        foreach (['false', '0', '', 'enter_api_enabled', 'nao'] as $valor) {
            $_ENV['API_ENABLED'] = $valor;
            $this->assertFalse(apiHabilitada(), "valor: {$valor}");
        }
        foreach (['true', '1', 'on', 'yes'] as $valor) {
            $_ENV['API_ENABLED'] = $valor;
            $this->assertTrue(apiHabilitada(), "valor: {$valor}");
        }
    }

    public function testRestControllerBarraAntesDeQualquerCoisa(): void
    {
        $fonte = (string) file_get_contents(APPPATH . 'libraries/REST_Controller.php');

        $this->assertMatchesRegularExpression(
            '/function __construct\(.*?parent::__construct\(\);\s*(\/\/[^\n]*\s*)?if \(! apiHabilitada\(\)\) \{\s*show_404\(\);\s*\}\s*\$this->preflight_checks\(\);/s',
            $fonte
        );
    }

    public function testTodoControllerDaApiHerdaDoRestController(): void
    {
        $arquivos = array_merge(
            glob(APPPATH . 'controllers/api/*/*.php') ?: [],
            glob(APPPATH . 'controllers/api/*/*/*.php') ?: []
        );
        $this->assertNotEmpty($arquivos);

        foreach ($arquivos as $arquivo) {
            $fonte = (string) file_get_contents($arquivo);
            $this->assertMatchesRegularExpression(
                '/^class \w+ extends REST_Controller\b/m',
                $fonte,
                basename($arquivo) . ' precisa herdar de REST_Controller para respeitar API_ENABLED'
            );
            if (preg_match('/function __construct\([^)]*\)\s*\{\s*(.*?);/s', $fonte, $m)) {
                $this->assertStringStartsWith('parent::__construct', trim($m[1]), basename($arquivo) . ' deve chamar parent::__construct() primeiro');
            }
        }
    }

    public function testPainelEAreaDoClienteNaoDependemDasClassesDaApi(): void
    {
        $achou = [];
        foreach (['controllers', 'models', 'views', 'helpers'] as $pasta) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APPPATH . $pasta, FilesystemIterator::SKIP_DOTS));
            foreach ($it as $arquivo) {
                if ($arquivo->getExtension() !== 'php' || str_contains($arquivo->getPathname(), '/controllers/api/')) {
                    continue;
                }
                if (preg_match('/ApiController|UsuariosController|ClientesController|ClientLoginController|REST_Controller/', (string) file_get_contents($arquivo->getPathname()))) {
                    $achou[] = $arquivo->getPathname();
                }
            }
        }
        $this->assertSame([], $achou);
    }
}
