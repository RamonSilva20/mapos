<?php

namespace Tests\Support\ViewEscaping;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tools\ViewEscaping\EscapingPolicy;
use Tools\ViewEscaping\ViewScanner;

/**
 * A varredura de arquivos, que é a única parte do gate com IO.
 *
 * As outras três classes são funções puras sobre string, e a `ViewScanner` era a
 * única das quatro sem teste nenhum. É onde mora tudo que o resto não tem: a
 * contagem de arquivos, o formato da chave de baseline, o `ksort` do relatório, a
 * semeadura do `$seen` e o laço sobre as formas de saída. Cada um desses é um lugar
 * onde o gate pode responder "não achei nada" por um motivo que não é "não havia
 * nada", e o gate não tem stdout — a única saída dele é o relatório.
 *
 * Os casos usam arquivos de verdade, num diretório temporário, e não uma string
 * passada por parâmetro: uma versão desta classe que aceitasse conteúdo como
 * argumento deixaria de exercitar a travessia, que é metade do que a classe faz.
 *
 * Aviso para quem editar os comentários deste arquivo, pelo mesmo motivo do
 * PhpExpression: o fechamento de tag do PHP não pode ser escrito aqui, nem em forma
 * de parênteses. Num comentário de linha ele encerra o modo PHP e o resto do
 * arquivo vira saída impressa. O conteúdo das views aparece nos nowdoc, onde a tag
 * é texto; nos comentários, ela é descrita por extenso.
 */
final class ViewScannerTest extends TestCase
{
    private string $views;

    protected function setUp(): void
    {
        $this->views = sys_get_temp_dir() . '/mapos-view-scanner-' . bin2hex(random_bytes(6));

        if (! is_dir($this->views) && ! mkdir($this->views, 0o777, true)) {
            self::fail("Não consegui criar o diretório temporário {$this->views}.");
        }
    }

    protected function tearDown(): void
    {
        if (! is_dir($this->views)) {
            return;
        }

        $entries = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->views, \FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($entries as $entry) {
            /** @var SplFileInfo $entry */
            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }

        rmdir($this->views);
    }

    /**
     * O mesmo trecho duas vezes na mesma view conta uma vez.
     *
     * A chave é "caminho|trecho normalizado", e ela é semeada no mapa. Sem a
     * semeadura o relatório mostra a mesma linha duas vezes, uma por forma de saída
     * que casou — e isso não é raridade: a forma de abre-expressão e a forma
     * `echo ...;` casam o mesmo código, e qualquer view que use as duas repete o
     * achado.
     *
     * Duplicar não é grave por si, e é por isso que o defeito precisaria de um
     * segundo efeito para ser notado: o relatório é a única saída do gate, e um
     * relatório com a mesma linha duas vezes ensina quem lê a ignorar a coluna da
     * linha. A linha relatada tem de ser a PRIMEIRA ocorrência, e o offset do regex
     * já é a posição exata — procurar o texto de novo no conteúdo acharia a
     * primeira, que nem sempre é a mesma, e é por isso que a semeadura guarda o
     * primeiro achado e ignora os seguintes.
     */
    #[Test]
    public function testTheSameSnippetTwiceInOneFileCountsOnce(): void
    {
        $this->write('os.php', <<<'PHP'
        <td><?= $os->defeito ?></td>
        <td><?= $os->defeito ?></td>
        PHP);

        $scan = ViewScanner::scan($this->views, $this->views);

        $this->assertSame(1, $scan['files']);
        $this->assertCount(1, $scan['findings'], 'a segunda ocorrência não é um achado novo');
        $this->assertArrayHasKey('os.php|$os->defeito', $scan['findings']);
        $this->assertSame(
            '$os->defeito',
            $scan['findings']['os.php|$os->defeito']['snippet'],
            'a chave é caminho e trecho normalizado, e o snippet é o trecho'
        );
        $this->assertSame(1, $scan['findings']['os.php|$os->defeito']['line']);
    }

    /**
     * `terminated` decide se a captura é cortada, e as duas metadas erram ao contrário.
     *
     * Os dois arquivos deste caso têm o MESMO texto capturado, `$a; $b`, e produzem
     * chaves diferentes. É esse o ponto: o texto é o mesmo, o que muda é se a forma
     * de saída já traz a tag de fim na captura.
     *
     * Na forma terminada — a abre-expressão curta — o `;` está ali só por
     * construção, e a expressão precisa ser cortada ANTES de ser analisada. Sem o
     * corte, o que é reprovado é o pedaço errado, e o relatório aponta a linha
     * inteira em vez da linha do valor.
     *
     * Na forma não terminada — `print( ... )` — o `;` já é parte do match, e cortar
     * de novo comeria o operando seguinte. É o mesmo `;` produzindo o defeito
     * oposto, e é por isso que `terminated` é um dado da forma e não um detalhe do
     * regex: as duas metades no mesmo caso porque um teste de uma só deixaria a
     * outra sem cobertura, e a forma errada é sempre a que ninguém exercita.
     */
    #[Test]
    public function testTheTerminatedFlagDecidesWhetherTheCaptureIsCut(): void
    {
        $this->write('terminada.php', '<?= $a; $b ?>');
        $this->write('solta.php', '<?php print($a; $b) ?>');

        $scan = ViewScanner::scan($this->views, $this->views);

        $this->assertArrayHasKey(
            'terminada.php|$a',
            $scan['findings'],
            'forma terminada: o ponto-e-vírgula é construção do regex e precisa ser cortado'
        );
        $this->assertArrayNotHasKey(
            'terminada.php|$a; $b',
            $scan['findings'],
            'sem o corte, a linha relatada seria a do arquivo inteiro em vez de a do valor'
        );

        $this->assertArrayHasKey(
            'solta.php|$a; $b',
            $scan['findings'],
            'forma não terminada: o ponto-e-vírgula é parte do statement, e cortar comeria o operando'
        );
        $this->assertArrayNotHasKey(
            'solta.php|$a',
            $scan['findings'],
            'cortar aqui é o defeito espelhado, e produz um achado que aponta a linha errada'
        );
    }

    /**
     * A política injetada decide o que é achado, e é este o caso que faltava.
     *
     * A costura existia — `scan()` já tinha um parâmetro para isso — e não servia
     * para nada, porque `scanFile()` montava a política padrão por conta própria e a
     * ignorava. O único teste que exercitava a costura provava o avesso: montava uma
     * política menor e conferia a conferência, ou seja, a camada de baixo, nunca a
     * varredura. O campo que a costura controlava era justamente o que o teste não
     * alcançava.
     *
     * Aqui a prova é a inversa: a MESMA view, com a política padrão e com uma
     * política que aprova aquele valor, precisa dar resultados diferentes. Se a
     * costura voltar a ser decorativa, as duas chamadas dão o mesmo mapa e este
     * teste falha — que é o que um parâmetro ignorado faria.
     *
     * A lista de isenções é a do campo que decide o que é reprovado, e a contagem
     * de arquivos é a mesma nas duas: a travessia não depende da política, e um
     * teste que só conferisse o mapa vazio passaria também com um `scan()` que não
     * tivesse aberto arquivo nenhum.
     */
    #[Test]
    public function testAnInjectedPolicyDecidesWhatTheScanFinds(): void
    {
        $this->write('os.php', '<td><?= $os->defeito ?></td>');

        $default = ViewScanner::scan($this->views, $this->views);
        $this->assertNotSame([], $default['findings'], 'a política padrão reprova o valor solto');

        $aprovadora = new EscapingPolicy(
            escapers: ['esc'],
            helpers: ['strtoupper'],
            sources: [],
            preRendered: ['$os->defeito'],
        );

        $permissive = ViewScanner::scan($this->views, $this->views, $aprovadora);

        $this->assertSame(
            1,
            $permissive['files'],
            'a varredura percorreu o mesmo arquivo: a contagem não depende da política'
        );
        $this->assertSame(
            [],
            $permissive['findings'],
            'a mesma view é limpa para a política injetada, e achada para a padrão'
        );
    }

    /**
     * A travessia desce em subdiretórios, e a contagem é de arquivos PHP.
     *
     * Um subdiretório é a forma como as views do projeto estão organizadas: `tema/`,
     * `os/`, `financeiro/`. Um teste que só escrevesse na raiz passaria com um
     * `RecursiveDirectoryIterator` trocado por `scandir`, que é a troca que a
     * contagem de arquivos denuncia.
     */
    #[Test]
    public function testSubdirectoriesAreWalkedAndNonPhpFilesAreNotCounted(): void
    {
        mkdir($this->views . '/os');
        $this->write('os.php', '<td><?= $os->defeito ?></td>');
        $this->write('os/editarOs.php', '<td><?= $os->defeito ?></td>');
        $this->write('estilo.css', 'td { color: red }');

        $scan = ViewScanner::scan($this->views, $this->views);

        $this->assertSame(2, $scan['files'], 'o CSS não é view, e o subdiretório é');
        $this->assertArrayHasKey('os.php|$os->defeito', $scan['findings']);
        $this->assertArrayHasKey('os/editarOs.php|$os->defeito', $scan['findings']);
    }

    /**
     * O agrupamento por conferência viaja junto dos achados, e não é lido de volta
     * a partir da chave.
     *
     * O prefixo fica DENTRO da chave, depois do caminho, e é por isso que quem só
     * recebe o mapa de achados teria de procurá-lo com `str_contains`. A varredura
     * sabe qual conferência reprovou cada entrada — está no laço, linha a linha — e
     * por isso devolve esse agrupamento como dado.
     *
     * A consequência que este caso trava é a Coverage, não a convenção: toda chave
     * de `findings` tem que aparecer em exatamente um dos grupos. Um achado que
     * existisse em `findings` e em nenhum grupo sumiria do relatório sem erro, e um
     * que aparecesse em dois grupos sairia duas vezes.
     */
    #[Test]
    public function testEveryFindingIsFiledUnderExactlyOneCategory(): void
    {
        $this->write('os.php', '<td><?= $os->defeito ?></td>');
        $this->write(
            'config.php',
            '<script>var c = JSON.parse("<?= esc_json($cfg) ?>");</script>'
        );
        $this->write('css.php', '<style>@media print { a { color: red } }</style>');

        $scan = ViewScanner::scan($this->views, $this->views);

        $grouped = array_merge(...array_values($scan['categories']));

        $this->assertSame(
            [],
            array_values(array_diff(array_keys($scan['findings']), $grouped)),
            'um achado que não está em nenhum grupo desaparece do relatório sem erro'
        );

        $this->assertSame(
            [],
            array_values(array_diff_assoc($grouped, array_unique($grouped))),
            'um achado em dois grupos sai duas vezes no relatório'
        );

        $this->assertCount(
            1,
            $scan['categories'][''],
            'o achado de escaping comum é o que não tem prefixo'
        );

        $this->assertCount(
            1,
            $scan['categories'][EscapingPolicy::JSON_PARSE_PREFIX],
            'o achado de JSON.parse() é agrupado pelo prefixo da conferência, não pela chave'
        );

        $this->assertCount(
            1,
            $scan['categories'][EscapingPolicy::UNRECOGNIZED_OUTPUT_PREFIX],
            'a guarda de saída ilegível tem grupo próprio'
        );
    }

    /**
     * A varredura de um diretório inexistente devolve a mesma forma, com o mapa de
     * categorias presente e vazio.
     *
     * A forma é a mesma para o script de entrada ler `categories` sem `isset`, e
     * o vazio é o que permite a guarda de cobertura do CLI ler `files` e recusar
     * uma varredura que não conferiu nada.
     */
    #[Test]
    public function testAMissingDirectoryHasTheSameShapeAndNoCategories(): void
    {
        $scan = ViewScanner::scan($this->views . '/nao-existe', $this->views);

        $this->assertSame(0, $scan['files']);
        $this->assertSame([], $scan['findings']);
        $this->assertSame([], $scan['categories']);
    }

    private function write(string $name, string $content): void
    {
        file_put_contents($this->views . '/' . $name, $content);
    }
}
