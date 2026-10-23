<?php

require_once MAPOS_ROOT . '/install/instalador.php';
require_once APPPATH . 'helpers/manutencao_helper.php';

/**
 * Instalação e atualização completas (#2858): o instalador roda as migrations,
 * confere requisitos e pasta dos anexos; o CLI de migrations não depende de
 * pacotes de desenvolvimento; o painel avisa de migrations pendentes e de
 * valores antigos e inseguros no .env.
 */
final class InstalacaoCompletaTest extends MaposTestCase
{
    private string $base;

    protected function setUp(): void
    {
        parent::setUp();

        $this->base = sys_get_temp_dir() . '/mapos-instalacao-' . bin2hex(random_bytes(4));
        mkdir($this->base . '/projeto/application', 0777, true);
        mkdir($this->base . '/projeto/assets', 0777, true);
        mkdir($this->base . '/projeto/install', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->apagar($this->base);

        parent::tearDown();
    }

    private function apagar(string $caminho): void
    {
        if (! file_exists($caminho) && ! is_link($caminho)) {
            return;
        }
        if (is_file($caminho) || is_link($caminho)) {
            unlink($caminho);

            return;
        }
        foreach (scandir($caminho) ?: [] as $item) {
            if ($item !== '.' && $item !== '..') {
                $this->apagar($caminho . '/' . $item);
            }
        }
        rmdir($caminho);
    }

    private function projeto(): string
    {
        return $this->base . '/projeto';
    }

    /** Roda um programa PHP em outro processo e devolve [código, stdout, stderr]. */
    private function executarPhp(array $argumentos, ?string $pasta = null): array
    {
        $processo = proc_open([PHP_BINARY, ...$argumentos], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $pasta);
        $this->assertIsResource($processo);
        $saida = (string) stream_get_contents($pipes[1]);
        $erro = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($processo), $saida, $erro];
    }

    // --- trava de instalação ---------------------------------------------------------------

    public function testTravaEsquecidaPorProcessoMortoNaoBloqueiaENovaTentativaPassa(): void
    {
        $arquivo = $this->base . '/instalacao.lock';
        $pegar = '$t = fopen($argv[1], "c"); exit(flock($t, LOCK_EX | LOCK_NB) ? 0 : 3);';

        // O arquivo da trava fica no disco depois de um processo que morreu sem apagá-lo.
        [$codigo] = $this->executarPhp(['-r', $pegar, $arquivo]);
        $this->assertSame(0, $codigo);
        $this->assertFileExists($arquivo);

        [$codigo] = $this->executarPhp(['-r', $pegar, $arquivo]);
        $this->assertSame(0, $codigo, 'uma trava esquecida não pode impedir nova instalação');
    }

    public function testTravaAtivaBloqueiaOutraInstalacaoSimultanea(): void
    {
        $arquivo = $this->base . '/instalacao.lock';
        $trava = fopen($arquivo, 'c');
        $this->assertTrue(flock($trava, LOCK_EX | LOCK_NB));

        [$codigo] = $this->executarPhp(['-r', '$t = fopen($argv[1], "c"); exit(flock($t, LOCK_EX | LOCK_NB) ? 0 : 3);', $arquivo]);
        $this->assertSame(3, $codigo);

        flock($trava, LOCK_UN);
        fclose($trava);
    }

    // --- pasta dos anexos (STORAGE_PATH) -------------------------------------------------

    public function testArmazenamentoVazioUsaOPadraoDoSistema(): void
    {
        $this->assertSame([null, null], instaladorPrepararArmazenamento('  ', $this->projeto()));
    }

    public function testArmazenamentoForaDaRaizEhCriadoEAceito(): void
    {
        [$caminho, $erro] = instaladorPrepararArmazenamento($this->base . '/mapos-storage', $this->projeto());

        $this->assertNull($erro);
        $this->assertSame(realpath($this->base . '/mapos-storage'), $caminho);
        $this->assertDirectoryExists($this->base . '/mapos-storage');
    }

    public function testArmazenamentoRelativoEhResolvidoNaRaiz(): void
    {
        [$caminho, $erro] = instaladorPrepararArmazenamento('application/dados', $this->projeto());

        $this->assertNull($erro);
        $this->assertSame(realpath($this->projeto() . '/application/dados'), $caminho);
    }

    public function testArmazenamentoDentroDaPastaPublicaEhRecusado(): void
    {
        foreach (['assets/anexos', 'install', 'uploads', '.'] as $relativo) {
            [$caminho, $erro] = instaladorPrepararArmazenamento($relativo, $this->projeto());

            $this->assertNull($caminho, $relativo);
            $this->assertStringContainsString('dentro da pasta pública', (string) $erro, $relativo);
        }
    }

    public function testArmazenamentoQueNaoPodeSerCriadoDaErroClaro(): void
    {
        // Um arquivo no lugar da pasta pai: mkdir falha em qualquer sistema e usuário (inclusive root).
        file_put_contents($this->base . '/arquivo', 'x');

        [$caminho, $erro] = instaladorPrepararArmazenamento($this->base . '/arquivo/anexos', $this->projeto());

        $this->assertNull($caminho);
        $this->assertStringContainsString('Não foi possível criar a pasta dos anexos', (string) $erro);
    }

    public function testValidacaoRecusaCaracteresPerigososNaPastaDosAnexos(): void
    {
        $base = [
            'host' => 'localhost', 'dbuser' => 'u', 'dbpassword' => 'p', 'dbname' => 'n', 'full_name' => 'Ana',
            'email' => 'ana@example.com', 'password' => 'senha-forte-1', 'base_url' => 'https://mapos.example.com/',
        ];

        foreach (['/dados/"x', '/dados/$HOME', "/dados/x\nAPI_ENABLED=true", '/dados\\x', "/dados/'x"] as $perigoso) {
            [$dados, $erro] = instaladorValidar($base + ['storage_path' => $perigoso]);

            $this->assertNull($dados, $perigoso);
            $this->assertStringContainsString('pasta dos anexos', (string) $erro, $perigoso);
        }

        [$dados, $erro] = instaladorValidar($base + ['storage_path' => ' /var/mapos-storage ']);
        $this->assertNull($erro);
        $this->assertSame('/var/mapos-storage', $dados['storage_path']);
    }

    public function testEnvGravaAPastaDosAnexosSemTratarCaminhoComoMarcador(): void
    {
        [$dados] = instaladorValidar([
            'host' => 'localhost', 'dbuser' => 'u', 'dbpassword' => 'p', 'dbname' => 'n', 'full_name' => 'Ana',
            'email' => 'ana@example.com', 'password' => 'senha-forte-1', 'base_url' => 'https://mapos.example.com/',
        ]);
        $modelo = "# STORAGE_PATH=comentario\nSTORAGE_PATH=\nDB_DATABASE=\"enter_db_name\"\n";

        $com = instaladorMontarEnv($modelo, $dados, 'chave', 'jwt', '/srv/enter_db_name/anexos');
        $this->assertStringContainsString("\nSTORAGE_PATH=\"/srv/enter_db_name/anexos\"\n", $com);
        $this->assertStringContainsString('# STORAGE_PATH=comentario', $com);
        $this->assertStringContainsString('DB_DATABASE="n"', $com);

        $sem = instaladorMontarEnv($modelo, $dados, 'chave', 'jwt');
        $this->assertStringContainsString("\nSTORAGE_PATH=\n", $sem);
    }

    // --- dependências e requisitos -------------------------------------------------------

    public function testSemVendorOInstaladorNaoTemDependencias(): void
    {
        $this->assertFalse(instaladorTemDependencias($this->projeto()));

        mkdir($this->projeto() . '/application/vendor', 0777, true);
        touch($this->projeto() . '/application/vendor/autoload.php');

        $this->assertTrue(instaladorTemDependencias($this->projeto()));
    }

    public function testRequisitosListamTodasAsExtensoesQueOSistemaUsa(): void
    {
        $configuracao = json_decode((string) file_get_contents(MAPOS_ROOT . '/install/settings.json'), true);
        $extensoes = array_map('strtolower', $configuracao['extensions']);

        foreach (['curl', 'gd', 'mysqli', 'zip', 'mbstring', 'dom', 'xml', 'zlib', 'iconv', 'json', 'openssl', 'fileinfo'] as $extensao) {
            $this->assertContains($extensao, $extensoes);
        }

        // Os nomes têm que ser os que o PHP conhece, senão o instalador reprova um servidor correto.
        foreach (['curl', 'gd', 'mbstring', 'dom', 'xml', 'zlib', 'iconv', 'json', 'openssl', 'fileinfo'] as $extensao) {
            $this->assertTrue(extension_loaded($extensao), "{$extensao} é usada pela própria suíte e deve estar carregada");
        }
    }

    public function testSemDependenciasOSistemaAvisaSemMostrarCaminhos(): void
    {
        // index.php sozinho numa pasta sem application/vendor: nem o autoload nem o CodeIgniter existem.
        copy(MAPOS_ROOT . '/index.php', $this->projeto() . '/index.php');

        [$codigo, $saida, $erro] = $this->executarPhp(['index.php', 'tools', 'migrate'], $this->projeto());

        $this->assertSame(1, $codigo);
        $this->assertSame('', $saida);
        $this->assertStringContainsString('composer install --no-dev', $erro);
        $this->assertStringContainsString('application/vendor', $erro);
        $this->assertStringNotContainsString($this->base, $erro);
        $this->assertStringNotContainsString('Fatal error', $erro);
    }

    // --- CLI de migrations sem pacotes de desenvolvimento --------------------------------

    public function testToolsSobeSemOFakerQueSoExisteNoDesenvolvimento(): void
    {
        // Processo novo SEM o autoload do Composer: Faker\Factory não existe, como em "composer install --no-dev".
        $programa = <<<'PHP'
            define('BASEPATH', __DIR__);
            class CI_Controller
            {
                public $input;
                public $load;
                public function __construct()
                {
                    $this->input = new class { public function is_cli_request() { return true; } };
                    $this->load = new class { public function dbforge() {} public function library($nome) {} };
                }
            }
            class Seeder { public static function create() { return new self(); } }
            require $argv[1];
            $tools = new Tools();
            echo class_exists('Faker\Factory') ? 'faker-presente' : 'faker-ausente', '|', var_export($tools->faker, true);
            PHP;
        $arquivo = $this->base . '/tools-sem-faker.php';
        file_put_contents($arquivo, "<?php\n" . $programa);

        [$codigo, $saida, $erro] = $this->executarPhp(['-d', 'display_errors=stderr', $arquivo, APPPATH . 'controllers/Tools.php']);

        $this->assertSame(0, $codigo, $erro);
        $this->assertSame('faker-ausente|NULL', $saida);
    }

    // --- migrations rodadas pelo instalador ----------------------------------------------

    public function testInstaladorExecutaOComandoDeMigrationsEDevolveAFalha(): void
    {
        $ok = $this->base . '/ok';
        $falha = $this->base . '/falha';
        mkdir($ok);
        mkdir($falha);
        file_put_contents($ok . '/index.php', '<?php echo implode(" ", array_slice($argv, 1)), PHP_EOL, "Migrations run successfully";');
        file_put_contents($falha . '/index.php', '<?php fwrite(STDERR, "Erro: coluna duplicada"); exit(8);');

        [$migrou, $saida] = instaladorMigrar($ok, PHP_BINARY);
        $this->assertTrue($migrou);
        $this->assertStringContainsString('tools migrate', $saida);
        $this->assertStringContainsString('Migrations run successfully', $saida);

        [$migrou, $saida] = instaladorMigrar($falha, PHP_BINARY);
        $this->assertFalse($migrou);
        $this->assertStringContainsString('coluna duplicada', $saida);
    }

    public function testInstaladorAvisaQuandoNaoHaPhpDeLinhaDeComando(): void
    {
        $this->assertNull(instaladorLocalizarPhpCli(['/nao/existe/php', '/tmp']));
        $this->assertSame(PHP_BINARY, instaladorLocalizarPhpCli([PHP_BINARY]));

        [$migrou, $saida] = instaladorMigrar($this->projeto(), '/nao/existe/php');
        $this->assertNotTrue($migrou, 'um programa que não executa nunca conta como migrações aplicadas');
        $this->assertNotSame('', $saida);
    }

    // --- aviso de migrations pendentes e de .env antigo no painel -------------------------

    private function pastaDeMigrations(): string
    {
        $pasta = $this->base . '/migrations';
        mkdir($pasta);
        foreach (['20240503170400_a', '20261009000000_b', '20261014000000_c', 'index.html', '20261014010000_sem_classe.txt'] as $nome) {
            file_put_contents($pasta . '/' . $nome . (str_contains($nome, '.') ? '' : '.php'), '<?php');
        }

        return $pasta;
    }

    public function testMigracoesPendentesSaoAsMaioresQueAAplicada(): void
    {
        $this->db->query('CREATE TABLE migrations (version TEXT)');
        $this->db->query("INSERT INTO migrations (version) VALUES ('20240503170400')");

        $pasta = $this->pastaDeMigrations();
        $this->assertSame(['20261009000000_b', '20261014000000_c'], migracoesPendentes($this->db, $pasta));

        $this->db->query("UPDATE migrations SET version = '20261014000000'");
        $this->assertSame([], migracoesPendentes($this->db, $pasta));
    }

    public function testBancoSemTabelaDeMigrationsTemTudoPendente(): void
    {
        $this->assertCount(3, migracoesPendentes($this->db, $this->pastaDeMigrations()));
    }

    public function testBancoQueSoTemAMigrationDaV4AvisaDasPendentes(): void
    {
        // O banco.sql grava 20240503170400: é o estado de uma instalação nova que não rodou as migrations.
        preg_match("/INSERT IGNORE INTO `migrations`\\(`version`\\) VALUES \\('(\\d+)'\\)/", (string) file_get_contents(MAPOS_ROOT . '/banco.sql'), $versao);
        $this->db->query('CREATE TABLE migrations (version TEXT)');
        $this->db->query('INSERT INTO migrations (version) VALUES (?)', [$versao[1]]);

        $pendentes = migracoesPendentes($this->db);

        $this->assertNotEmpty($pendentes);
        $this->assertContains('20261014000000_create_api_tokens', $pendentes);

        $avisos = manutencaoAvisos($this->db, [], false);
        $this->assertCount(1, $avisos);
        $this->assertStringContainsString((string) count($pendentes), $avisos[0]);
        $this->assertStringContainsString('limite de tentativas de login', $avisos[0]);
        $this->assertStringContainsString('Atualizar banco de dados', $avisos[0]);
    }

    public function testSemPendenciasNemEnvAntigoNaoHaAviso(): void
    {
        $this->db->query('CREATE TABLE migrations (version TEXT)');
        $this->db->query("INSERT INTO migrations (version) VALUES ('99999999999999')");

        $this->assertSame([], manutencaoAvisos($this->db, ['APP_COOKIE_HTTPONLY' => 'true', 'APP_COOKIE_SECURE' => 'auto'], true));
        $this->assertSame([], manutencaoAvisos($this->db, [], true), 'variáveis ausentes valem o padrão seguro');
    }

    public function testEnvAntigoDa4xGeraAviso(): void
    {
        $antigo = ['APP_COOKIE_SECURE' => 'false', 'APP_COOKIE_HTTPONLY' => 'false', 'APP_SESS_REGENERATE_DESTROY' => 'false'];

        $this->assertSame(['APP_COOKIE_HTTPONLY', 'APP_SESS_REGENERATE_DESTROY', 'APP_COOKIE_SECURE'], envCookiesInseguros($antigo, true));
        $this->assertSame(['APP_COOKIE_HTTPONLY', 'APP_SESS_REGENERATE_DESTROY'], envCookiesInseguros($antigo, false), 'Secure só importa em HTTPS');
        $this->assertSame([], envCookiesInseguros(['APP_COOKIE_SECURE' => 'auto', 'APP_COOKIE_HTTPONLY' => '1', 'APP_SESS_REGENERATE_DESTROY' => 'true'], true));
        $this->assertSame([], envCookiesInseguros(['APP_COOKIE_HTTPONLY' => 'talvez'], true), 'valor não reconhecido usa o padrão, como no config.php');
    }
}
