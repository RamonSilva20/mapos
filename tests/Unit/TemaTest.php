<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tema da v5: modo de cor derivado do app_theme da v4 (#2833) e remoção da
 * cor de destaque (#2913).
 */
final class TemaTest extends MaposTestCase
{
    private const MIGRATION = 'Migration_add_tema_modo_destaque_to_configuracoes';

    private const MIGRATION_REMOVE_DESTAQUE = 'Migration_remove_tema_destaque_from_configuracoes';

    public static function setUpBeforeClass(): void
    {
        $pasta = APPPATH . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR;

        require_once $pasta . '20261008000000_add_tema_modo_destaque_to_configuracoes.php';
        require_once $pasta . '20261008120000_remove_tema_destaque_from_configuracoes.php';
    }

    /**
     * Os 7 temas da v4 (os valores do select em mapos/configurar.php mais o
     * padrão do MY_Controller), com o modo da v5 e o destaque que a migration
     * de 2026-10-08 gravava antes da #2913.
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

    /**
     * O destaque do provider vale só para as migrations; o helper trata só o
     * modo.
     */
    #[DataProvider('temasDaV4')]
    public function testConverteCadaTemaDaV4(string $appTheme, string $modo, string $destaque): void
    {
        $this->assertSame(['modo' => $modo], temaDeAppTheme($appTheme));
    }

    #[DataProvider('valoresDesconhecidos')]
    public function testValorDesconhecidoViraOPadrao($appTheme): void
    {
        $this->assertSame(['modo' => 'claro'], temaDeAppTheme($appTheme));
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
        $this->assertSame(['modo' => 'escuro'], temaDeAppTheme(' DarkViolet '));
    }

    /**
     * O modo gravado vale sobre o derivado de app_theme. Uma app_tema_destaque
     * que ainda exista (banco onde a migration da #2913 não rodou) é ignorada.
     */
    public function testConfiguracaoNovaTemPrioridade(): void
    {
        $tema = temaConfiguracao([
            'app_theme' => 'white',
            'app_tema_modo' => 'sistema',
            'app_tema_destaque' => 'verde',
        ]);

        $this->assertSame(['modo' => 'sistema'], $tema);
    }

    /**
     * Banco instalado pelo banco.sql antes de a migration rodar: só existe
     * app_theme, e o modo sai dele.
     */
    public function testSemConfiguracaoNovaDerivaDeAppTheme(): void
    {
        $this->assertSame(['modo' => 'escuro'], temaConfiguracao(['app_theme' => 'puredark']));
    }

    /**
     * Valor inválido gravado na configuração não pode chegar ao HTML: cai no
     * derivado de app_theme.
     */
    public function testValorInvalidoNaConfiguracaoNovaCaiNoDerivado(): void
    {
        $tema = temaConfiguracao([
            'app_theme' => 'darkorange',
            'app_tema_modo' => 'neon',
        ]);

        $this->assertSame(['modo' => 'escuro'], $tema);
    }

    public function testAtributosDoModoEscuro(): void
    {
        $this->assertSame(
            ' class="dark" data-tema-modo="escuro"',
            temaAtributosHtml(['app_tema_modo' => 'escuro', 'app_tema_destaque' => 'violeta'])
        );
    }

    /**
     * Nos modos claro e sistema o servidor não põe .dark: no sistema quem
     * decide é o assets/js/tema.js, pelo prefers-color-scheme.
     */
    public function testAtributosDosModosClaroESistemaNaoTemClasseDark(): void
    {
        $this->assertSame(' data-tema-modo="claro"', temaAtributosHtml(['app_theme' => 'white']));
        $this->assertSame(' data-tema-modo="sistema"', temaAtributosHtml(['app_tema_modo' => 'sistema']));
    }

    /**
     * Sem cor de destaque (#2913): o <html> não recebe mais data-accent.
     */
    public function testAtributosNaoTemMaisCorDeDestaque(): void
    {
        $html = temaAtributosHtml(['app_tema_modo' => 'claro', 'app_tema_destaque' => 'azul']);

        $this->assertStringNotContainsString('data-accent', $html);
        $this->assertFalse(defined('TEMA_DESTAQUES'));
    }

    public function testAtributosNaoDeixamPassarHtml(): void
    {
        $html = temaAtributosHtml([
            'app_tema_modo' => '"><script>alert(1)</script>',
            'app_tema_destaque' => '" onload="x',
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('onload', $html);
        $this->assertSame(' data-tema-modo="claro"', $html);
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

    public function testRemoveDestaqueApagaSoACorDeDestaque(): void
    {
        $this->criarConfiguracoes([
            'app_theme' => 'darkviolet',
            'app_tema_modo' => 'escuro',
            'app_tema_destaque' => 'violeta',
            'per_page' => '10',
        ]);

        $this->migration(self::MIGRATION_REMOVE_DESTAQUE)->up();

        $this->assertNull($this->valor('app_tema_destaque'));
        $this->assertSame('escuro', $this->valor('app_tema_modo'));
        $this->assertSame('darkviolet', $this->valor('app_theme'));
        $this->assertSame('10', $this->valor('per_page'));
    }

    /**
     * Banco que já não tem a configuração (instalação nova ou up repetido).
     */
    public function testRemoveDestaqueSemAConfiguracaoNaoFalha(): void
    {
        $this->criarConfiguracoes(['app_tema_modo' => 'claro']);
        $migration = $this->migration(self::MIGRATION_REMOVE_DESTAQUE);

        $migration->up();
        $migration->up();

        $this->assertNull($this->valor('app_tema_destaque'));
        $this->assertSame('claro', $this->valor('app_tema_modo'));
    }

    #[DataProvider('temasDaV4')]
    public function testRemoveDestaqueDownRecriaAPartirDoAppTheme(string $appTheme, string $modo, string $destaque): void
    {
        $this->criarConfiguracoes(['app_theme' => $appTheme, 'app_tema_modo' => $modo]);

        $this->migration(self::MIGRATION_REMOVE_DESTAQUE)->down();

        $this->assertSame($destaque, $this->valor('app_tema_destaque'));
    }

    public function testRemoveDestaqueDownNaoSobrescreveNemDuplica(): void
    {
        $this->criarConfiguracoes(['app_theme' => 'puredark', 'app_tema_destaque' => 'verde']);

        $this->migration(self::MIGRATION_REMOVE_DESTAQUE)->down();

        $this->assertSame(1, $this->db->where('config', 'app_tema_destaque')->count_all_results('configuracoes'));
        $this->assertSame('verde', $this->valor('app_tema_destaque'));
    }

    public function testRemoveDestaqueDownSemAppThemeUsaLaranja(): void
    {
        $this->criarConfiguracoes(['per_page' => '10']);

        $this->migration(self::MIGRATION_REMOVE_DESTAQUE)->down();

        $this->assertSame('laranja', $this->valor('app_tema_destaque'));
    }

    /**
     * Atualização de uma base da 4.x: as duas migrations em sequência deixam
     * só o modo, derivado do app_theme.
     */
    public function testAtualizacaoDaV4TerminaSoComOModo(): void
    {
        $this->criarConfiguracoes(['app_theme' => 'darkorange']);

        $this->migration()->up();
        $this->migration(self::MIGRATION_REMOVE_DESTAQUE)->up();

        $this->assertSame('escuro', $this->valor('app_tema_modo'));
        $this->assertNull($this->valor('app_tema_destaque'));
        $this->assertSame(['modo' => 'escuro'], temaConfiguracao(['app_theme' => 'darkorange', 'app_tema_modo' => $this->valor('app_tema_modo')]));
    }

    private function criarConfiguracoes(array $valores): void
    {
        $this->db->query('CREATE TABLE configuracoes (idConfig INTEGER PRIMARY KEY AUTOINCREMENT, config VARCHAR(20) NOT NULL UNIQUE, valor TEXT NULL)');

        foreach ($valores as $config => $valor) {
            $this->db->insert('configuracoes', ['config' => $config, 'valor' => $valor]);
        }
    }

    private function migration(string $class = self::MIGRATION)
    {
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
