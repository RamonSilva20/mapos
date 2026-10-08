<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Helper e(), o escape padrão das views da v5.
 */
final class EscapeHelperTest extends MaposTestCase
{
    #[DataProvider('valoresEscapados')]
    public function testEscapaParaHtml($entrada, string $esperado): void
    {
        $this->assertSame($esperado, e($entrada));
    }

    public static function valoresEscapados(): array
    {
        return [
            'script' => ['<script>alert(1)</script>', '&lt;script&gt;alert(1)&lt;/script&gt;'],
            'aspas duplas' => ['" onmouseover="x', '&quot; onmouseover=&quot;x'],
            'aspas simples' => ["' onmouseover='x", '&#039; onmouseover=&#039;x'],
            'e comercial' => ['Silva & Filhos', 'Silva &amp; Filhos'],
            'texto comum' => ['Cliente Teste', 'Cliente Teste'],
            'acentos' => ['Ordem de Serviço nº 1', 'Ordem de Serviço nº 1'],
        ];
    }

    /**
     * Mesmo comportamento do html_escape() com double_encode: o & de uma
     * entidade já escapada é escapado de novo. Escapar duas vezes é bug de
     * quem chamou, e o HTML mostra isso em vez de esconder.
     */
    public function testEscapaDeNovoUmaEntidadeJaEscapada(): void
    {
        $this->assertSame('&amp;lt;b&amp;gt;', e('&lt;b&gt;'));
    }

    /**
     * O html_escape() devolve valores "vazios" como vieram. O e() sempre
     * devolve string, para que `<?= e($x) ?>` nunca imprima algo inesperado.
     */
    #[DataProvider('valoresVazios')]
    public function testValorVazioViraStringVazia($entrada): void
    {
        $this->assertSame('', e($entrada));
    }

    public static function valoresVazios(): array
    {
        return [
            'null' => [null],
            'false' => [false],
            'string vazia' => [''],
        ];
    }

    /**
     * Zero é um valor legítimo, não "vazio": tem que aparecer na tela.
     */
    #[DataProvider('escalares')]
    public function testEscalaresViramString($entrada, string $esperado): void
    {
        $this->assertSame($esperado, e($entrada));
    }

    public static function escalares(): array
    {
        return [
            'zero inteiro' => [0, '0'],
            'zero string' => ['0', '0'],
            'inteiro' => [42, '42'],
            'float' => [10.5, '10.5'],
            'true' => [true, '1'],
        ];
    }

    public function testAceitaObjetoStringable(): void
    {
        $objeto = new class() {
            public function __toString(): string
            {
                return '<b>nome</b>';
            }
        };

        $this->assertSame('&lt;b&gt;nome&lt;/b&gt;', e($objeto));
    }

    /**
     * Imprimir um array numa view é bug. O TypeError aparece na hora em vez de
     * um "Array" no HTML.
     */
    public function testArrayNaoEhAceito(): void
    {
        $this->expectException(TypeError::class);

        e(['<b>']);
    }
}
