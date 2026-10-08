<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Helpers que ligam as views aos módulos JS (application/helpers/js_helper.php).
 */
final class JsHelperTest extends MaposTestCase
{
    public function testJsModuleGeraOAtributo(): void
    {
        $this->assertSame('data-module="servicos/listagem"', js_module('servicos/listagem'));
    }

    #[DataProvider('nomesInvalidos')]
    public function testJsModuleRecusaNomeInvalido(string $nome): void
    {
        $this->expectException(InvalidArgumentException::class);

        js_module($nome);
    }

    public static function nomesInvalidos(): array
    {
        return [
            'vazio' => [''],
            'subida de pasta' => ['../segredo'],
            'subida no meio' => ['servicos/../../x'],
            'barra inicial' => ['/servicos'],
            'barra final' => ['servicos/'],
            'barra dupla' => ['servicos//lista'],
            'extensão' => ['servicos/lista.js'],
            'aspas' => ['x" onmouseover="alert(1)'],
            'maiúsculas' => ['Servicos'],
            'url' => ['https://evil.test/x'],
        ];
    }

    public function testPageDataGeraBlocoJson(): void
    {
        $html = page_data('dados-servicos', ['porPagina' => 10, 'ativo' => true]);

        $this->assertSame(
            '<script type="application/json" id="dados-servicos">{"porPagina":10,"ativo":true}</script>',
            $html
        );
    }

    /**
     * Um valor vindo do banco com </script> não pode fechar a tag e injetar
     * HTML ou script na página.
     */
    public function testPageDataNaoDeixaODadoFecharATag(): void
    {
        $html = page_data('dados', ['nome' => '</script><script>alert(1)</script>']);

        $this->assertSame(1, substr_count(strtolower($html), '</script>'));
        $this->assertSame(1, substr_count(strtolower($html), '<script'));
        $this->assertStringEndsWith('</script>', $html);
    }

    #[DataProvider('caracteresPerigosos')]
    public function testPageDataEscapaCaracteresDeHtml(string $caractere): void
    {
        $html = page_data('d', ['v' => "a{$caractere}b"]);
        $json = substr($html, strlen('<script type="application/json" id="d">'), -strlen('</script>'));

        $this->assertStringNotContainsString($caractere, $json);
        $this->assertSame(['v' => "a{$caractere}b"], json_decode($json, true), 'O JSON tem de continuar decodificando no mesmo valor.');
    }

    public static function caracteresPerigosos(): array
    {
        return [
            'menor' => ['<'],
            'maior' => ['>'],
            'e comercial' => ['&'],
            'apóstrofo' => ["'"],
            'comentário html' => ['<!--'],
        ];
    }

    public function testPageDataMantemAcentos(): void
    {
        $this->assertStringContainsString('"Manutenção"', page_data('d', ['s' => 'Manutenção']));
    }

    public function testPageDataEscapaOId(): void
    {
        $html = page_data('x" onload="alert(1)', []);

        $this->assertStringContainsString('id="x&quot; onload=&quot;alert(1)"', $html);
    }

    public function testPageDataRecusaValorQueNaoViraJson(): void
    {
        $this->expectException(JsonException::class);

        page_data('d', ['v' => "\xB1\x31"]);
    }
}
