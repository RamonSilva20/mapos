<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Checagem de <script> inline nas views (scripts/check-inline-script.php).
 */
final class CheckInlineScriptTest extends MaposTestCase
{
    #[DataProvider('scriptsInline')]
    public function testAcusaScriptInline(string $view): void
    {
        $this->assertCount(1, inlineScriptOcorrencias($view));
    }

    public static function scriptsInline(): array
    {
        return [
            'sem atributos' => ['<script>alert(1)</script>'],
            'text/javascript' => ['<script type="text/javascript">$(function () {});</script>'],
            'module inline' => ['<script type="module">import x from "./x.js";</script>'],
            'maiúsculas' => ['<SCRIPT>alert(1)</SCRIPT>'],
            'php no corpo' => ["<script>\nvar url = '<?= base_url() ?>';\n</script>"],
            'php com > num atributo' => ['<script nonce="<?= $a > 1 ? \'x\' : \'y\' ?>">go()</script>'],
            'atributo data-src não é src' => ['<script data-src="x.js">go()</script>'],
        ];
    }

    #[DataProvider('scriptsPermitidos')]
    public function testNaoAcusaScriptExternoOuDeDados(string $view): void
    {
        $this->assertSame([], inlineScriptOcorrencias($view));
    }

    public static function scriptsPermitidos(): array
    {
        return [
            'externo' => ['<script src="/assets/js/app.js"></script>'],
            'externo com php no src' => ['<script type="text/javascript" src="<?= base_url(); ?>assets/js/csrf.js"></script>'],
            'module externo' => ['<script type="module" src="<?= base_url() ?>assets/js/app.js"></script>'],
            'json de dados' => ['<script type="application/json" id="dados">{"a":1}</script>'],
            'json-ld' => ["<script type='application/ld+json'>{}</script>"],
            'page_data()' => ['<?= page_data(\'dados\', $dados) ?>'],
            'palavra script em texto' => ['<p>Use scripts com cuidado: <code>&lt;script&gt;</code></p>'],
            'tag dentro de string php' => ['<?php $x = "<script>alert(1)</script>"; ?>'],
        ];
    }

    public function testInformaALinhaDaTag(): void
    {
        $view = "<div>\n<?php if (\$a) {\n    echo 1;\n} ?>\n<script>\nx();\n</script>";

        $this->assertSame([5], array_column(inlineScriptOcorrencias($view), 'linha'));
    }

    public function testContaCadaBloco(): void
    {
        $view = '<script>a()</script><script src="b.js"></script><script>c()</script>';

        $this->assertCount(2, inlineScriptOcorrencias($view));
    }

    public function testArquivoAcimaDoBaselineEhAcusado(): void
    {
        $encontrado = [
            'application/views/a.php' => [['linha' => 1, 'tag' => '<script>'], ['linha' => 9, 'tag' => '<script>']],
            'application/views/b.php' => [['linha' => 1, 'tag' => '<script>']],
        ];

        $excedentes = inlineScriptExcedentes($encontrado, [
            'application/views/a.php' => 1,
            'application/views/b.php' => 1,
        ]);

        $this->assertSame(['application/views/a.php'], array_keys($excedentes));
    }

    public function testViewNovaComScriptInlineEhAcusada(): void
    {
        $excedentes = inlineScriptExcedentes(
            ['application/views/nova.php' => [['linha' => 3, 'tag' => '<script>']]],
            []
        );

        $this->assertSame(0, $excedentes['application/views/nova.php']['permitido']);
    }

    /**
     * Remover script inline nunca quebra o build.
     */
    public function testArquivoAbaixoDoBaselinePassa(): void
    {
        $this->assertSame([], inlineScriptExcedentes(
            ['application/views/a.php' => [['linha' => 1, 'tag' => '<script>']]],
            ['application/views/a.php' => 4]
        ));
    }

    /**
     * As views do repositório têm de bater com o baseline commitado. É o que
     * leva a checagem para o CI, pelo job do PHPUnit.
     */
    public function testViewsDoProjetoNaoPassamDoBaseline(): void
    {
        $baseline = json_decode((string) file_get_contents(MAPOS_ROOT . '/inline-script-baseline.json'), true);

        $excedentes = inlineScriptExcedentes(inlineScriptVarrerViews(MAPOS_ROOT), $baseline);

        $this->assertSame([], array_keys($excedentes), 'Há <script> inline novo nas views. Rode php scripts/check-inline-script.php.');
    }

    /**
     * A tela convertida como prova do padrão (#2838) não pode voltar a ter
     * script inline.
     */
    public function testListagemDeServicosNaoTemScriptInline(): void
    {
        $view = (string) file_get_contents(APPPATH . 'views/servicos/servicos.php');

        $this->assertSame([], inlineScriptOcorrencias($view));
        $this->assertStringContainsString("js_module('servicos/listagem')", $view);
        $this->assertFileExists(MAPOS_ROOT . '/assets/js/modules/servicos/listagem.js');
    }
}
