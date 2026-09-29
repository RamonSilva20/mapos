<?php

namespace Tests\Support\ViewEscaping;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A guarda de "nenhuma view lida", exercitada pelo executável de verdade.
 *
 * A guarda é a única defesa do gate contra um ambiente quebrado, e ela só existe
 * porque o dano que previne é silencioso. Sem views lidas, a lista de achados fica
 * vazia, o caminho de escrita do baseline transforma isso em um arquivo VAZIO sem
 * erro e com código 0, e o gate passa a aprovar tudo a partir de então — porque
 * nenhuma decisão está mais registrada. As 234 entradas somem, e o sintoma
 * aparece semanas depois, quando alguém asks por que uma view deixou de ser
 * conferida.
 *
 * Não dá para provar isso testando `ViewScanner` num diretório vazio: o que
 * importa é a ORDEM das operações no executável, e a ordem é o defeito. A guarda
 * precisa vir ANTES do `--update-baseline` escrever, e um teste da biblioteca não
 * enxerga onde o `exit()` está.
 *
 * Por isso este arquivo executa o CLI num diretório temporário que imita a raiz do
 * projeto, com `application/views` vazio. Nenhuma porta dos fundos entra no código
 * de produção para isso: um caminho de views configurável por variável de ambiente
 * seria uma forma de apontar o gate para uma pasta vazia e obter um verde, que é
 * exatamente o que a guarda existe para impedir. A árvore temporária é isolada
 * porque o executável deriva a raiz de `dirname(__DIR__)`, e essa derivação é o
 * que permite testá-lo sem mexer no repositório.
 */
final class ZeroViewGuardTest extends TestCase
{
    private const SENTINEL = "views/mapos/login.php|<?= \$row->nome ?>\n# registro que precisa sobreviver\n";

    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/mapos-zero-view-guard-' . bin2hex(random_bytes(6));

        mkdir($this->root . '/application/views', 0700, true);
        mkdir($this->root . '/tools', 0700, true);

        // O autoload do Composer resolve `Tools\ViewEscaping\*` pelo mapa do
        // repositório real, então as classes carregadas aqui são as de produção.
        // Só o caminho do arquivo precisa existir, e um link serve.
        symlink(self::repositoryRoot() . '/application/vendor', $this->root . '/application/vendor');

        copy(
            self::repositoryRoot() . '/tools/check_view_escaping.php',
            $this->root . '/tools/check_view_escaping.php'
        );

        $this->copyDirectory(
            self::repositoryRoot() . '/tools/ViewEscaping',
            $this->root . '/tools/ViewEscaping'
        );

        file_put_contents($this->root . '/tools/xss-baseline.txt', self::SENTINEL);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);
    }

    /**
     * `--update-baseline` não escreve nada quando não leu view nenhuma.
     *
     * Este é o caso destrutivo, e é o que a ordem da guarda protege. O
     * executável sem views produz `$findings` vazio; o caminho de escrita que o
     * segue transforma isso em um baseline de zero entradas e sai com 0, que é um
     * gate verde com o registro de decisões apagado. A asserção que segura é a do
     * conteúdo: mesmo que o código de saída mudasse, um arquivo diferente do que
     * estava ali é o dano, e ele tem que aparecer.
     */
    #[Test]
    public function testTheUpdateRunLeavesTheBaselineUntouched(): void
    {
        $result = $this->runCli(['--update-baseline']);

        $this->assertSame(
            2,
            $result['code'],
            "O gate não conseguiu ler nenhuma view, e isso precisa ser o 2 — o código que pede "
                . "olhar o ambiente, não o 1 que pede corrigir uma view.\n"
                . $result['output']
        );

        $this->assertSame(
            self::SENTINEL,
            file_get_contents($this->root . '/tools/xss-baseline.txt'),
            'O baseline foi reescrito sem ter lido nada. A partir daqui o gate aprova tudo, '
                . 'porque nenhuma decisão está mais registrada.'
        );
    }

    /**
     * A conferência simples também sai com 2, e não com um verde de "nada achado".
     *
     * Sem este caso, a guarda poderia existir só no caminho de escrita e o gate do
     * dia a dia continuaria reportando 0 achados e 0 arquivos com código 0 — a
     * mesma mentira, com outro uniforme. `assertNotSame(0, ...)` é o que prende
     * os dois caminhos ao mesmo código.
     */
    #[Test]
    public function testTheCheckRunAlsoReportsThatItReadNothing(): void
    {
        $result = $this->runCli([]);

        $this->assertSame(
            2,
            $result['code'],
            "Uma pasta de views vazia é um erro de ambiente, e não um gate em ordem. O código 0 "
                . "aqui diria que as 234 entradas conferem, que é o oposto do que aconteceu.\n"
                . $result['output']
        );

        $this->assertStringNotContainsString(
            'New (unsuppressed) : 0',
            $result['output'],
            'O relatório de uma execução que não leu nada não pode parecer o de uma execução em ordem.'
        );
    }

    /**
     * A mensagem diz onde olhou, porque o erro quase sempre é um caminho.
     *
     * A causa de um diretório vazio é quase sempre um: volume que não montou, pasta
     * renomeada, erro de digitação. Os três se distinguem pelo caminho, e uma
     * mensagem sem ele deixa quem recebe o 2 sem nada para corrigir. A asserção é
     * frouxa de propósito — o que precisa estar presente é o diretório, não uma
     * frase exata que alguém vai reescrever.
     */
    #[Test]
    public function testTheMessageNamesTheDirectoryItTriedToRead(): void
    {
        $result = $this->runCli(['--update-baseline']);

        $this->assertStringContainsString(
            $this->root . '/application/views',
            $result['output'],
            'A mensagem precisa dizer qual diretório foi lido. "Nenhuma view lida" sem o caminho '
                . 'não distingue um volume que não montou de uma pasta renomeada.'
        );
    }

    /**
     * Com uma view de verdade, o gate roda e o baseline passa a ser reescrito.
     *
     * É o contra-teste dos três acima, e ele é o que impede que a guarda seja
     * confundida com "o script nunca faz nada". Se a guarda disparasse com views
     * presentes, os três casos passariam e o gate estaria quebrado de um jeito bem
     * mais silencioso: verde permanente, sem jamais conferir nada.
     */
    #[Test]
    public function testARealViewLetsTheUpdateRunWriteTheBaseline(): void
    {
        file_put_contents(
            $this->root . '/application/views/qualquer.php',
            "<p><?= \$row->nome ?></p>\n"
        );

        $result = $this->runCli(['--update-baseline']);

        $this->assertSame(
            0,
            $result['code'],
            "Com uma view legível o caminho de escrita tem que funcionar, e é isso que os outros "
                . "três casos supõem. Se a guarda disparasse aqui, o gate ficaria verde para sempre.\n"
                . $result['output']
        );

        $written = (string) file_get_contents($this->root . '/tools/xss-baseline.txt');

        $this->assertStringNotContainsString(
            self::SENTINEL,
            $written,
            'O baseline foi reescrito, e o registro anterior tem que ter ido com ele.'
        );

        $this->assertStringContainsString(
            'qualquer.php',
            $written,
            'A entrada nova precisa estar no arquivo, que é o que o gate passa a comparar.'
        );
    }

    /**
     * @param  list<string> $arguments
     * @return array{code: int, output: string}
     */
    private function runCli(array $arguments): array
    {
        $command = escapeshellarg(PHP_BINARY)
            . ' ' . escapeshellarg($this->root . '/tools/check_view_escaping.php');

        foreach ($arguments as $argument) {
            $command .= ' ' . escapeshellarg($argument);
        }

        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open($command, $descriptors, $pipes, $this->root);

        $this->assertIsResource($process, 'Não foi possível executar o gate de escape.');

        $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        return ['code' => proc_close($process), 'output' => $output];
    }

    /**
     * A raiz do repositório, para não repetir a contagem de `dirname` em cada uso.
     *
     * `__DIR__` é `application/tests/Support/ViewEscaping`, e subir quatro níveis
     * chega na raiz. A contagem está escrita uma vez porque espalhada é o jeito de
     * errar: três níveis resolve para `application/`, e o erro aparece como um
     * `copy()` reclamando de um arquivo que existe.
     */
    private static function repositoryRoot(): string
    {
        return dirname(__DIR__, 4);
    }

    private function copyDirectory(string $from, string $to): void
    {
        mkdir($to, 0700, true);

        foreach (scandir($from) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $source = $from . '/' . $entry;
            $destination = $to . '/' . $entry;

            is_dir($source) ? $this->copyDirectory($source, $destination) : copy($source, $destination);
        }
    }

    /**
     * Apaga a árvore, incluindo o link de `vendor`, que `unlink` e não `rmdir`
     * resolve — e é por isso que o `is_link()` vem antes do `is_dir()`.
     *
     * Sem essa distinção, o `scandir` desce no autoload do Composer e apaga o
     * `vendor/` do repositório. O `rmdir` num link também falharia, então as duas
     * ordens importam: link primeiro, e um `is_dir` que só vale para diretório
     * verdade.
     */
    private function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $child = $path . '/' . $entry;

            if (is_link($child)) {
                unlink($child);
            } elseif (is_dir($child)) {
                $this->removeDirectory($child);
            } else {
                unlink($child);
            }
        }

        rmdir($path);
    }
}
