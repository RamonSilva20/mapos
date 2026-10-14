<?php

require_once MAPOS_ROOT . '/install/instalador.php';

/**
 * Instalador bloqueado depois da instalação (#2874).
 */
final class InstaladorTest extends MaposTestCase
{
    private string $raiz;

    protected function setUp(): void
    {
        parent::setUp();

        $this->raiz = sys_get_temp_dir() . '/mapos-instalador-' . bin2hex(random_bytes(4));
        mkdir($this->raiz . '/application', 0777, true);
        mkdir($this->raiz . '/install', 0777, true);
    }

    protected function tearDown(): void
    {
        foreach ([$this->raiz . '/application/.env', $this->raiz . '/install/instalado.lock'] as $arquivo) {
            if (file_exists($arquivo)) {
                unlink($arquivo);
            }
        }
        rmdir($this->raiz . '/application');
        rmdir($this->raiz . '/install');
        rmdir($this->raiz);

        parent::tearDown();
    }

    private function post(array $mudancas = []): array
    {
        return $mudancas + [
            'host' => 'localhost',
            'dbuser' => 'mapos',
            'dbpassword' => 's3nha',
            'dbname' => 'mapos',
            'full_name' => 'Ana Admin',
            'email' => 'ana@example.com',
            'password' => 'senha-forte-1',
            'base_url' => 'https://mapos.example.com/',
            'enter_api_enabled' => 'false',
            'enter_token_expire_time' => '86400',
        ];
    }

    public function testSoEstaInstaladoComEnvOuComLock(): void
    {
        $this->assertFalse(instaladorInstalado($this->raiz));

        touch($this->raiz . '/install/instalado.lock');
        $this->assertTrue(instaladorInstalado($this->raiz));

        unlink($this->raiz . '/install/instalado.lock');
        touch($this->raiz . '/application/.env');
        $this->assertTrue(instaladorInstalado($this->raiz));
    }

    public function testValidacaoAceitaOFormularioCompleto(): void
    {
        [$dados, $erro] = instaladorValidar($this->post());

        $this->assertNull($erro);
        $this->assertSame('86400', $dados['enter_token_expire_time']);
    }

    public static function formulariosInvalidos(): array
    {
        return [
            'campo vazio' => [['host' => ''], 'insira todos os campos'],
            'campo em array' => [['dbname' => ['x']], 'insira todos os campos'],
            'e-mail inválido' => [['email' => 'ana'], 'email válido'],
            'senha curta' => [['password' => '1234567'], 'pelo menos 8'],
            'quebra de linha no host' => [['host' => "localhost\nAPP_ENVIRONMENT=development"], 'quebra de linha'],
            'quebra de linha na expiração' => [['enter_token_expire_time' => "60\nX=1"], 'API inválida'],
            'expiração fora da lista' => [['enter_token_expire_time' => '5'], 'API inválida'],
            'api inválida' => [['enter_api_enabled' => 'talvez'], 'API inválida'],
            'URL sem esquema' => [['base_url' => 'mapos.example.com'], 'URL completa'],
            'URL com aspas' => [['base_url' => 'https://x.com/"'], 'URL completa'],
        ];
    }

    #[PHPUnit\Framework\Attributes\DataProvider('formulariosInvalidos')]
    public function testValidacaoRecusa(array $mudancas, string $trecho): void
    {
        [$dados, $erro] = instaladorValidar($this->post($mudancas));

        $this->assertNull($dados);
        $this->assertStringContainsString($trecho, (string) $erro);
    }

    public function testEnvEscapaAspasBarraECifrao(): void
    {
        [$dados] = instaladorValidar($this->post(['dbpassword' => 'a"b\\c$d${HOME}']));
        $env = instaladorMontarEnv('DB_PASSWORD="enter_db_password"' . "\n" . 'API_ENABLED=enter_api_enabled', $dados, 'chave', 'jwt');

        $this->assertStringContainsString('DB_PASSWORD="a\\"b\\\\c\\$d\\${HOME}"', $env);
        $this->assertStringContainsString('API_ENABLED=false', $env);
    }

    public function testEnvNaoSubstituiMarcadorQueVemDentroDeUmValor(): void
    {
        [$dados] = instaladorValidar($this->post(['dbpassword' => 'enter_db_name', 'dbname' => 'banco']));
        $env = instaladorMontarEnv("DB_PASSWORD=\"enter_db_password\"\nDB_DATABASE=\"enter_db_name\"", $dados, 'chave', 'jwt');

        $this->assertStringContainsString('DB_PASSWORD="enter_db_name"', $env);
        $this->assertStringContainsString('DB_DATABASE="banco"', $env);
    }

    public function testSqlEscapaONomeEHasheiaASenha(): void
    {
        [$dados] = instaladorValidar($this->post(['full_name' => "O'Brien"]));
        $sql = instaladorMontarSql("VALUES ('admin_name', 'admin_email', 'admin_password', 'admin_created_at')", $dados, '2026-10-14 08:00:00', 'addslashes');

        $this->assertStringContainsString("'O\\'Brien'", $sql);
        $this->assertStringNotContainsString('senha-forte-1', $sql);
        $this->assertStringContainsString('$2y$', $sql);
    }

    public function testDoInstallChecaATravaAntesDeLerOFormularioOuConectarNoBanco(): void
    {
        $codigo = (string) file_get_contents(MAPOS_ROOT . '/install/do_install.php');

        $trava = strpos($codigo, 'instaladorInstalado()');
        $this->assertNotFalse($trava);
        $this->assertLessThan(strpos($codigo, '$_POST'), $trava);
        $this->assertLessThan(strpos($codigo, 'new mysqli'), $trava);
    }

    public function testIndexBloqueiaAntesDeMostrarRequisitos(): void
    {
        $codigo = (string) file_get_contents(MAPOS_ROOT . '/install/index.php');

        $this->assertLessThan(strpos($codigo, 'settings.json'), strpos($codigo, 'instaladorBloquear()'));
    }

    public function testViewsNaoExecutamSemOInstalador(): void
    {
        foreach (glob(MAPOS_ROOT . '/install/view/*.php') as $view) {
            $this->assertStringContainsString("defined('INSTALADOR') || exit", (string) file_get_contents($view), basename($view));
        }
    }

    public function testServidorWebNegaOsArquivosInternos(): void
    {
        $htaccess = (string) file_get_contents(MAPOS_ROOT . '/install/.htaccess');
        $this->assertStringContainsString('settings\\.json', $htaccess);
        $this->assertStringContainsString('instalador\\.php', $htaccess);
        $this->assertStringContainsString('.*\\.lock', $htaccess);
        $this->assertStringContainsString('Require all denied', (string) file_get_contents(MAPOS_ROOT . '/install/view/.htaccess'));

        foreach (['default.conf', 'default.template.conf'] as $arquivo) {
            $nginx = (string) file_get_contents(MAPOS_ROOT . '/docker/etc/nginx/' . $arquivo);
            $this->assertStringContainsString('settings\\.json|instalador\\.php', $nginx, $arquivo);
            $this->assertStringContainsString('location ^~ /install/view/', $nginx, $arquivo);
        }
    }

    public function testHtaccessDeInstaladoDeixaSoOIndex(): void
    {
        $regra = instaladorHtaccessInstalado();

        $this->assertStringContainsString('^(?!index\\.php$)', $regra);
        $this->assertSame(1, preg_match('/^(?!index\.php$)/', 'do_install.php'));
        $this->assertSame(0, preg_match('/^(?!index\.php$)/', 'index.php'));
    }

    public function testAtualizadorNaoTrazOInstaladorDeVolta(): void
    {
        require_once APPPATH . 'libraries/Github_updater.php';

        $this->assertContains('install/', Github_updater::PROTEGIDOS);
    }

    public function testPainelAvisaOAdminQueAPastaInstallAindaExiste(): void
    {
        $controller = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');
        $this->assertStringContainsString("'instalador_no_servidor' => \$this->permite('cSistema') && is_dir(FCPATH . 'install')", $controller);

        $view = (string) file_get_contents(APPPATH . 'views/mapos/painel.php');
        $this->assertStringContainsString('A pasta install ainda está no servidor', $view);
    }
}
