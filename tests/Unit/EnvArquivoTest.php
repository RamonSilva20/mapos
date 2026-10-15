<?php

require_once MAPOS_ROOT . '/application/env_arquivo.php';

/**
 * Localização do .env fora da pasta pública (#2879).
 */
final class EnvArquivoTest extends MaposTestCase
{
    private string $base;

    private string $raiz;

    protected function setUp(): void
    {
        parent::setUp();

        $this->base = sys_get_temp_dir() . '/mapos-env-' . bin2hex(random_bytes(4));
        $this->raiz = $this->base . '/www';
        mkdir($this->raiz . '/application', 0777, true);
        putenv('MAPOS_ENV_FILE');
        unset($_SERVER['MAPOS_ENV_FILE']);
    }

    protected function tearDown(): void
    {
        putenv('MAPOS_ENV_FILE');
        unset($_SERVER['MAPOS_ENV_FILE']);
        foreach ([$this->raiz . '/application/.env', $this->base . '/.mapos.env', $this->base . '/fora.env'] as $arquivo) {
            if (file_exists($arquivo)) {
                unlink($arquivo);
            }
        }
        rmdir($this->raiz . '/application');
        rmdir($this->raiz);
        rmdir($this->base);

        parent::tearDown();
    }

    public function testSemNenhumArquivoUsaOLocalDeSempre(): void
    {
        $this->assertSame($this->raiz . '/application/.env', maposEnvArquivo($this->raiz));
    }

    public function testEnvDentroDeApplicationContinuaFuncionando(): void
    {
        touch($this->raiz . '/application/.env');

        $this->assertSame($this->raiz . '/application/.env', maposEnvArquivo($this->raiz . '/'));
    }

    public function testArquivoNaPastaAcimaTemPrioridade(): void
    {
        touch($this->raiz . '/application/.env');
        touch($this->base . '/.mapos.env');

        $this->assertSame($this->base . '/.mapos.env', maposEnvArquivo($this->raiz));
    }

    public function testVariavelDeAmbienteTemPrioridadeSobreTodos(): void
    {
        touch($this->base . '/.mapos.env');
        touch($this->base . '/fora.env');
        putenv('MAPOS_ENV_FILE=' . $this->base . '/fora.env');

        $this->assertSame($this->base . '/fora.env', maposEnvArquivo($this->raiz));
    }

    public function testVariavelViaServerEhAceita(): void
    {
        touch($this->base . '/fora.env');
        $_SERVER['MAPOS_ENV_FILE'] = $this->base . '/fora.env';

        $this->assertSame($this->base . '/fora.env', maposEnvArquivo($this->raiz));
    }

    public function testInstalacaoNovaGravaOndeAVariavelMandar(): void
    {
        putenv('MAPOS_ENV_FILE=' . $this->base . '/fora.env');

        $this->assertSame($this->base . '/fora.env', maposEnvArquivo($this->raiz));
    }

    public function testCaminhoRelativoEhIgnorado(): void
    {
        putenv('MAPOS_ENV_FILE=../fora.env');

        $this->assertSame($this->raiz . '/application/.env', maposEnvArquivo($this->raiz));
    }

    public function testInstaladorTravaComEnvForaDaRaiz(): void
    {
        require_once MAPOS_ROOT . '/install/instalador.php';
        $this->assertFalse(instaladorInstalado($this->raiz));

        touch($this->base . '/.mapos.env');
        $this->assertTrue(instaladorInstalado($this->raiz));
    }

    public function testIndexCarregaOEnvDeForaDaRaiz(): void
    {
        file_put_contents($this->base . '/.mapos.env', "APP_ENVIRONMENT=pre_installation\nSO_NO_ENV_FORA=sim\n");
        $codigo = '$_SERVER["HTTP_HOST"]="x"; $_SERVER["SCRIPT_NAME"]="/index.php"; require ' . var_export(MAPOS_ROOT . '/application/env_arquivo.php', true)
            . '; require ' . var_export(MAPOS_ROOT . '/application/vendor/autoload.php', true)
            . '; $f = maposEnvArquivo(' . var_export($this->raiz, true) . '); Dotenv\Dotenv::createImmutable(dirname($f), basename($f))->load(); echo $_ENV["SO_NO_ENV_FORA"] ?? "nao";';

        $this->assertSame('sim', shell_exec(escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($codigo)));
    }
}
