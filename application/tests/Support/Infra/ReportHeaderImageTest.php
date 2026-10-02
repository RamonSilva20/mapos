<?php

namespace Tests\Support\Infra;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A imagem do topo dos relatórios não pode ser escapada como se fosse uma URL.
 *
 * `imprimirTopo.php` é o único `<img src>` do projeto que recebe um caminho de
 * disco em vez de uma URL: `convertUrlToUploadsPath()` troca a URL gravada no banco
 * por `FCPATH . 'assets/uploads/' . basename($url)`, porque o mPDF lê o arquivo do
 * disco em vez de buscar a imagem pela rede. Os outros ~25 lugares que mostram
 * `emitente->url_logo` passam a URL que o `Mapos.php:315` gravou, e estão corretos.
 *
 * O caminho de disco quebra o `esc_img_src()` só no Windows, e em silêncio:
 * `parse_url('C:\laragon\...', PHP_URL_SCHEME)` devolve `C`, que não está entre os
 * esquemas de imagem aceitos, então o escaper devolve `""`. O `src` fica vazio, o
 * mPDF resolve o vazio contra a URL da página e estoura com "image type not
 * recognised" — e só lá, porque `showImageErrors` está ligado. No Linux o mesmo
 * código funciona, porque `/var/www/html/...` não tem esquema nenhum.
 *
 * Por isso este caso é de leitura de arquivo e não de render: a CI roda em Linux,
 * então renderizar a view passa antes e depois da correção, e um teste que não
 * consegue falhar não cobre nada. É a mesma razão de `ServedPathsTest` olhar o
 * conteúdo dos arquivos de configuração em vez de tentar exercitá-los.
 */
final class ReportHeaderImageTest extends TestCase
{
    /**
     * O logo do emitente não passa pelo escaper de URL.
     *
     * Escapar o caminho de disco com `esc_img_src()` volta a funcionar em Linux e
     * a quebrar em toda instalação Windows, que é o tipo de defeito que o CI não
     * vê e que ninguém encontra até um cliente gerar um PDF.
     */
    #[Test]
    public function testTheDiskPathIsNotEscapedAsIfItWereAUrl(): void
    {
        $this->assertStringNotContainsString(
            'esc_img_src(convertUrlToUploadsPath(',
            $this->contents($this->view()),
            'o topo dos relatórios escapa um caminho de disco com esc_img_src(): no Windows '
            . 'a letra de unidade vira esquema, o escaper devolve "" e o mPDF estoura ao '
            . 'resolver um src vazio. Use esc() — o caminho vem de FCPATH e nunca traz esquema.'
        );
    }

    /**
     * O caminho é montado uma vez e usado nas duas expressões que precisam dele.
     *
     * As duas usavam `convertUrlToUploadsPath($emitente->url_logo)` separadamente, o
     * que é a mesma string por construção. Se uma delas passar a escapar e a outra
     * não, o `file_exists()` decide sobre um caminho e o `src` aponta para outro, e
     * a imagem some sem erro — que é a mesma falha que este arquivo existe para
     * impedir, um passo antes dela.
     */
    #[Test]
    public function testThePathIsBuiltOnceAndSharedByBothUses(): void
    {
        $contents = $this->contents($this->view());

        $this->assertSame(
            1,
            substr_count($contents, 'convertUrlToUploadsPath('),
            'o caminho do logo precisa ser montado uma vez em uma variável: duas chamadas '
            . 'podem divergir, e o file_exists() passaria a validar um caminho diferente '
            . 'do que o src aponta'
        );

        $this->assertStringContainsString(
            'src="<?= esc($logoEmitente) ?>"',
            $contents,
            'o src do logo do topo dos relatórios tem que ser o caminho de disco escapado '
            . 'com esc(), não a URL'
        );
    }

    private function view(): string
    {
        return dirname(__DIR__, 4) . '/application/views/relatorios/imprimir/imprimirTopo.php';
    }

    private function contents(string $file): string
    {
        $contents = is_file($file) ? file_get_contents($file) : false;

        if ($contents === false) {
            self::fail("Não consegui ler {$file}.");
        }

        return $contents;
    }
}
