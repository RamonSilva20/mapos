<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Ilustrações-adesivo (#2919): fontes em assets/src/stickers, saída do svgo
 * em assets/img/stickers (npm run build:stickers).
 */
final class StickerTest extends MaposTestCase
{
    /** Paleta do DESIGN.md para os adesivos: ink, branco do recorte, rosa, lima e violeta. */
    private const CORES = ['#1f1633', '#fff', '#ffffff', '#fa7faa', '#c2ef4e', '#79628c'];

    public static function adesivos(): array
    {
        $nomes = array_map(static fn ($f) => basename($f, '.svg'), glob(MAPOS_ROOT . '/assets/src/stickers/*.svg') ?: []);

        return array_combine($nomes, array_map(static fn ($n) => [$n], $nomes));
    }

    public function testTemOsCincoAdesivosDaIssue(): void
    {
        $this->assertEqualsCanonicalizing(['caixa', 'celular', 'chave', 'notebook', 'tecnico'], array_keys(self::adesivos()));
    }

    #[DataProvider('adesivos')]
    public function testSaidaOtimizadaPequenaESemLaranja(string $nome): void
    {
        $arquivo = MAPOS_ROOT . "/assets/img/stickers/{$nome}.svg";

        $this->assertFileExists($arquivo, 'Rode npm run build:stickers.');
        $this->assertLessThan(4096, filesize($arquivo), "{$nome}.svg passou de 4 KB.");

        $svg = strtolower((string) file_get_contents($arquivo));
        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringNotContainsString('<script', $svg);

        preg_match_all('/#[0-9a-f]{3,6}\b/', $svg, $cores);
        foreach (array_unique($cores[0]) as $cor) {
            $this->assertContains($cor, self::CORES, "{$nome}.svg usa {$cor}: adesivo só usa ink, branco, rosa, lima e violeta (nunca o laranja).");
        }
        $this->assertStringNotContainsString('#f37338', $svg);
    }

    /**
     * Onde aparece, o adesivo é decorativo: alt vazio, aria-hidden e oculto
     * no celular.
     */
    public function testUsoNasViewsEhDecorativo(): void
    {
        $usos = 0;
        foreach (['application/views/mapos/login.php', 'application/views/conecte/login.php'] as $view) {
            $codigo = (string) file_get_contents(MAPOS_ROOT . '/' . $view);
            // Uma <img> por linha: o PHP dentro do src fecha a tag antes do fim, então a busca é pela linha.
            preg_match_all('#^.*<img .*assets/img/stickers/.*$#m', $codigo, $imgs);

            $this->assertNotEmpty($imgs[0], "{$view} sem adesivo.");
            foreach ($imgs[0] as $img) {
                $usos++;
                $this->assertStringContainsString('alt=""', $img);
                $this->assertStringContainsString('aria-hidden="true"', $img);
                $this->assertMatchesRegularExpression('/max-(sm|lg):hidden/', $img);
            }
        }

        $this->assertSame(4, $usos);
    }
}
