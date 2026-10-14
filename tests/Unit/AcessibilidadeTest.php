<?php

/**
 * Acessibilidade AA (#2853): título por tela, anel de foco com contraste,
 * menos movimento, nome do menu do usuário e topbar sem estourar em 320 px.
 */
final class AcessibilidadeTest extends MaposTestCase
{
    public function testTituloDaPaginaTrazATelaEOSistema(): void
    {
        $itens = [['label' => 'Início', 'url' => '/'], ['label' => 'Clientes', 'url' => '/clientes'], ['label' => 'Adicionar', 'url' => '/clientes/adicionar']];

        $this->assertSame('Adicionar — Clientes — Map-OS', layoutTitulo($itens, 'Map-OS'));
        $this->assertSame('Clientes — Map-OS', layoutTitulo(array_slice($itens, 0, 2), 'Map-OS'));
        $this->assertSame('Início — Map-OS', layoutTitulo([['label' => 'Início', 'url' => '/']], 'Map-OS'));
        $this->assertSame('Map-OS', layoutTitulo([], 'Map-OS'));
        $this->assertSame('Map-OS', layoutTitulo([['label' => '  ', 'url' => null]], 'Map-OS'));
    }

    public function testPainelInicialEAcentosNoTitulo(): void
    {
        $url = static fn ($c) => 'http://x/' . $c;

        $this->assertSame([['label' => 'Início', 'url' => 'http://x/']], layoutBreadcrumb(['mapos', '', ''], $url), '/mapos é o painel, não o módulo Sistema.');
        $this->assertSame([['label' => 'Início', 'url' => 'http://x/']], layoutBreadcrumb(['mapos', 'index', ''], $url));
        $this->assertSame('Lançamentos — Financeiro — Map-OS', layoutTitulo(layoutBreadcrumb(['financeiro', 'lancamentos', ''], $url), 'Map-OS'));
        $this->assertSame('Minha conta — Sistema — Map-OS', layoutTitulo(layoutBreadcrumb(['mapos', 'minhaConta', ''], $url), 'Map-OS'));
        $this->assertSame('Clientes — Relatórios — Map-OS', layoutTitulo(layoutBreadcrumb(['relatorios', 'clientes', ''], $url), 'Map-OS'));
    }

    public function testMenosMovimentoVenceAsUtilitiesDaMoldura(): void
    {
        $layout = (string) file_get_contents(MAPOS_ROOT . '/assets/src/layout.css');

        $this->assertMatchesRegularExpression('/@layer base \{\s+@media \(prefers-reduced-motion: reduce\)/', $layout);
    }

    public function testGridDosRelatoriosNaoEmpurraAPaginaEm320px(): void
    {
        $this->assertStringContainsString('grid-cols-[minmax(0,1fr)]', (string) file_get_contents(APPPATH . 'views/relatorios/relatorio.php'));
    }

    public function testTopoUsaOTituloDaTela(): void
    {
        $topo = (string) file_get_contents(APPPATH . 'views/tema/topo.php');

        $this->assertStringContainsString("layoutTitulo(\$layout['breadcrumb']", $topo);
    }

    public function testAnelDeFocoNuncaETranslucido(): void
    {
        $arquivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APPPATH . 'views', FilesystemIterator::SKIP_DOTS));
        foreach ($arquivos as $arquivo) {
            if ($arquivo->getExtension() === 'php') {
                $this->assertStringNotContainsString('outline-ring/', (string) file_get_contents($arquivo->getPathname()), $arquivo->getPathname());
            }
        }

        foreach (['assets/src/layout.css', 'assets/src/app.css'] as $css) {
            $this->assertStringNotContainsString('var(--color-ring) 50%', (string) file_get_contents(MAPOS_ROOT . '/' . $css), $css);
        }
    }

    public function testRazaoDeContrasteDoAnelDeFocoSobreAsSuperficies(): void
    {
        $luz = static function (string $hex): float {
            $canais = array_map(static fn (string $c): float => (static fn (float $v): float => $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4)(hexdec($c) / 255), str_split(ltrim($hex, '#'), 2));

            return 0.2126 * $canais[0] + 0.7152 * $canais[1] + 0.0722 * $canais[2];
        };
        $razao = static fn (string $a, string $b): float => (max($luz($a), $luz($b)) + 0.05) / (min($luz($a), $luz($b)) + 0.05);

        $this->assertGreaterThanOrEqual(3.0, $razao('#3b82f6', '#ffffff'));
        $this->assertGreaterThanOrEqual(3.0, $razao('#3b82f6', '#f6f5f9'));
        $this->assertGreaterThanOrEqual(3.0, $razao('#3b82f6', '#150f23'));
        $this->assertLessThan(3.0, $razao('#9dc0fa', '#ffffff'), 'O anel a 50% de alfa sobre branco (~#9DC0FA) reprovava.');
        $this->assertStringContainsString('#3B82F6', (string) file_get_contents(MAPOS_ROOT . '/DESIGN.md'));
    }

    public function testMenosMovimentoNosDoisCss(): void
    {
        foreach (['assets/src/app.css', 'assets/src/layout.css'] as $css) {
            $codigo = (string) file_get_contents(MAPOS_ROOT . '/' . $css);
            $this->assertStringContainsString('prefers-reduced-motion: reduce', $codigo, $css);
            $this->assertStringContainsString('transition-duration: 0.01ms !important', $codigo, $css);
        }
    }

    public function testMenuDoUsuarioTemNomeQueContemOTextoVisivel(): void
    {
        $conteudo = (string) file_get_contents(APPPATH . 'views/tema/conteudo.php');

        $this->assertStringNotContainsString('aria-label="Menu do usuário"', $conteudo, 'aria-label diferente do texto visível (WCAG 2.5.3).');
        $this->assertStringContainsString('<span class="sr-only">Menu do usuário</span>', $conteudo);
    }

    public function testTopbarCabeEm320pxComOModoDeCorNoMenuDoUsuario(): void
    {
        $conteudo = (string) file_get_contents(APPPATH . 'views/tema/conteudo.php');

        $this->assertStringContainsString('max-sm:hidden inline-flex', $conteudo, 'O seletor de modo de cor some da topbar no celular.');
        $this->assertSame(2, substr_count($conteudo, 'data-tema-escolher'), 'Nos dois lugares: topbar (sm+) e menu do usuário (celular).');
        $this->assertStringContainsString('max-w-[calc(100vw-1rem)]', $conteudo, 'O menu do usuário não passa da largura da tela.');
    }

    public function testCalendarioDaAgendaNaoTemIconeSemTextoNemDiaApagado(): void
    {
        $js = (string) file_get_contents(MAPOS_ROOT . '/assets/js/modules/painel/painel.js');
        $this->assertStringContainsString("setAttribute('aria-hidden', 'true')", $js);

        $css = (string) file_get_contents(MAPOS_ROOT . '/assets/src/app.css');
        $this->assertStringContainsString('.fc-day-other .fc-daygrid-day-number', $css);
    }

    public function testListaDaPaletaNaoEParadaExtraDoTab(): void
    {
        $this->assertStringContainsString('id="v5-busca-lista" tabindex="-1"', (string) file_get_contents(APPPATH . 'views/tema/busca.php'));
    }
}
