<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tema da v5: conversão do app_theme da v4 em modo + cor de destaque (#2833).
 */
final class TemaTest extends MaposTestCase
{
    private const MIGRATION = 'Migration_add_tema_modo_destaque_to_configuracoes';

    public static function setUpBeforeClass(): void
    {
        require_once APPPATH . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR
            . '20261008000000_add_tema_modo_destaque_to_configuracoes.php';
    }

    /**
     * Os 7 temas da v4 (os valores do select em mapos/configurar.php mais o
     * padrão do MY_Controller).
     */
    public static function temasDaV4(): array
    {
        return [
            'default' => ['default', 'claro', 'laranja'],
            'Claro' => ['white', 'claro', 'laranja'],
            'Pure dark' => ['puredark', 'escuro', 'azul'],
            'Dark orange' => ['darkorange', 'escuro', 'laranja'],
            'Dark violet' => ['darkviolet', 'escuro', 'violeta'],
            'White green' => ['whitegreen', 'claro', 'verde'],
            'White black' => ['whiteblack', 'claro', 'grafite'],
        ];
    }

    #[DataProvider('temasDaV4')]
    public function testConverteCadaTemaDaV4(string $appTheme, string $modo, string $destaque): void
    {
        $this->assertSame(['modo' => $modo, 'destaque' => $destaque], temaDeAppTheme($appTheme));
    }

    #[DataProvider('valoresDesconhecidos')]
    public function testValorDesconhecidoViraOPadrao($appTheme): void
    {
        $this->assertSame(['modo' => 'claro', 'destaque' => 'laranja'], temaDeAppTheme($appTheme));
    }

    public static function valoresDesconhecidos(): array
    {
        return [
            'null' => [null],
            'vazio' => [''],
            'tema que não existe' => ['solarized'],
        ];
    }

    public function testConversaoIgnoraCaixaEEspacos(): void
    {
        $this->assertSame(['modo' => 'escuro', 'destaque' => 'violeta'], temaDeAppTheme(' DarkViolet '));
    }

    /**
     * Configuração nova vale sobre a derivada de app_theme.
     */
    public function testConfiguracaoNovaTemPrioridade(): void
    {
        $tema = temaConfiguracao([
            'app_theme' => 'white',
            'app_tema_modo' => 'sistema',
            'app_tema_destaque' => 'verde',
        ]);

        $this->assertSame(['modo' => 'sistema', 'destaque' => 'verde'], $tema);
    }

    /**
     * Banco instalado pelo banco.sql antes de a migration rodar: só existe
     * app_theme, e o tema sai dele.
     */
    public function testSemConfiguracaoNovaDerivaDeAppTheme(): void
    {
        $this->assertSame(['modo' => 'escuro', 'destaque' => 'azul'], temaConfiguracao(['app_theme' => 'puredark']));
    }

    /**
     * Valor inválido gravado na configuração nova não pode chegar ao HTML: cai
     * no derivado de app_theme, campo a campo.
     */
    public function testValorInvalidoNaConfiguracaoNovaCaiNoDerivado(): void
    {
        $tema = temaConfiguracao([
            'app_theme' => 'darkorange',
            'app_tema_modo' => 'neon',
            'app_tema_destaque' => 'azul',
        ]);

        $this->assertSame(['modo' => 'escuro', 'destaque' => 'azul'], $tema);
    }

    public function testAtributosDoModoEscuro(): void
    {
        $this->assertSame(
            ' class="dark" data-tema-modo="escuro" data-accent="violeta"',
            temaAtributosHtml(['app_tema_modo' => 'escuro', 'app_tema_destaque' => 'violeta'])
        );
    }

    /**
     * Nos modos claro e sistema o servidor não põe .dark: no sistema quem
     * decide é o assets/js/tema.js, pelo prefers-color-scheme.
     */
    public function testAtributosDosModosClaroESistemaNaoTemClasseDark(): void
    {
        $this->assertSame(
            ' data-tema-modo="claro" data-accent="laranja"',
            temaAtributosHtml(['app_theme' => 'white'])
        );
        $this->assertSame(
            ' data-tema-modo="sistema" data-accent="grafite"',
            temaAtributosHtml(['app_tema_modo' => 'sistema', 'app_tema_destaque' => 'grafite'])
        );
    }

    public function testAtributosNaoDeixamPassarHtml(): void
    {
        $html = temaAtributosHtml([
            'app_tema_modo' => '"><script>alert(1)</script>',
            'app_tema_destaque' => '" onload="x',
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('onload', $html);
        $this->assertSame(' data-tema-modo="claro" data-accent="laranja"', $html);
    }

    #[DataProvider('temasDaV4')]
    public function testMigrationConverteOTemaAtual(string $appTheme, string $modo, string $destaque): void
    {
        $this->criarConfiguracoes(['app_theme' => $appTheme, 'per_page' => '10']);

        $this->migration()->up();

        $this->assertSame($modo, $this->valor('app_tema_modo'));
        $this->assertSame($destaque, $this->valor('app_tema_destaque'));
        $this->assertSame($appTheme, $this->valor('app_theme'), 'app_theme continua para as telas legadas.');
    }

    public function testMigrationSemAppThemeUsaOPadrao(): void
    {
        $this->criarConfiguracoes(['per_page' => '10']);

        $this->migration()->up();

        $this->assertSame('claro', $this->valor('app_tema_modo'));
        $this->assertSame('laranja', $this->valor('app_tema_destaque'));
    }

    /**
     * Rodar de novo não duplica nem sobrescreve uma escolha feita depois.
     */
    public function testMigrationEhIdempotente(): void
    {
        $this->criarConfiguracoes(['app_theme' => 'darkviolet']);
        $migration = $this->migration();

        $migration->up();
        $this->db->where('config', 'app_tema_destaque')->update('configuracoes', ['valor' => 'verde']);
        $migration->up();

        $this->assertSame(1, $this->db->where('config', 'app_tema_modo')->count_all_results('configuracoes'));
        $this->assertSame('verde', $this->valor('app_tema_destaque'));
    }

    public function testMigrationDownRemoveSoAsConfiguracoesNovas(): void
    {
        $this->criarConfiguracoes(['app_theme' => 'puredark', 'per_page' => '10']);
        $migration = $this->migration();

        $migration->up();
        $migration->down();

        $this->assertNull($this->valor('app_tema_modo'));
        $this->assertNull($this->valor('app_tema_destaque'));
        $this->assertSame('puredark', $this->valor('app_theme'));
        $this->assertSame('10', $this->valor('per_page'));
    }

    private function criarConfiguracoes(array $valores): void
    {
        $this->db->query('CREATE TABLE configuracoes (idConfig INTEGER PRIMARY KEY AUTOINCREMENT, config VARCHAR(20) NOT NULL UNIQUE, valor TEXT NULL)');

        foreach ($valores as $config => $valor) {
            $this->db->insert('configuracoes', ['config' => $config, 'valor' => $valor]);
        }
    }

    private function migration()
    {
        $class = self::MIGRATION;
        $migration = new $class();
        $migration->db = $this->db;

        return $migration;
    }

    private function valor(string $config): ?string
    {
        $linha = $this->db->select('valor')->where('config', $config)->get('configuracoes')->row();

        return $linha ? (string) $linha->valor : null;
    }
}
