<?php

namespace Tests\Support\ViewEscaping;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tools\ViewEscaping\EscapingChecks;
use Tools\ViewEscaping\EscapingPolicy;
use Tools\ViewEscaping\PhpExpression;

/**
 * A propriedade que o gate inteiro depende e que nenhum teste de caso cobre: não
 * existe uma forma de produzir saída que o gate leia em silêncio.
 *
 * As outras seções da suíte provam decisões — que `esc()` passa, que `self::$y`
 * reprova, que `esc_json()` no `JSON.parse()` reprova. Provam o JULGAMENTO. Esta
 * prova a COBERTURA, e cobertura não se prova por casos: ela se prova percorrendo
 * um corpus e afirmando "todo token de saída está coberto por alguma forma OU vira
 * achado", que é a mesma que `EscapingChecks::unrecognizedOutput()` verifica em
 * produção, e é por isso que o teste chama a guarda e não reimplementa a conta.
 *
 * Sem isto, o gate é fail-closed por acidente, não por desenho. A camada de
 * EXTRAÇÃO é regex, e regex falha calada: `<?php if ($q) { echo self::$y; } ?>`
 * não casa com nenhuma das seis formas, `unescaped()` — que reprovaria
 * corretamente — nunca é chamado, e o relatório sai limpo. Um valor que o gate não
 * leu não é um valor que o gate aprovou, e a diferença entre as duas coisas é um XSS
 * que ninguém viu.
 *
 * A geração é determinística (`mt_srand` com semente fixa): uma propriedade que
 * muda de resultado a cada rodada não é uma propriedade, é um teste que às vezes
 * passa. A semente está em uma constante e não em um literal espalhado, porque um
 * número mágico repetido em dois lugares diverge na primeira edição de um deles.
 */
final class FailClosedTest extends TestCase
{
    private const SEED = 20260928;

    /**
     * Os átomos de onde as views são montadas, e as formas como eles se combinam.
     *
     * Metade são valores, metade são coisas que NÃO devem ser tratadas como valor:
     * um `print` de CSS, um `echo` de comentário, `?>` no meio da linha. É contra
     * estas que a extração erra, e um gerador que só produzisse `<?= $a ?>` passaria
     * com qualquer regex do mundo.
     */
    private const ATOMS = [
        '$a', '$r->idOs', '$this->view', 'self::$y', 'esc($a)', 'esc_url($a)',
        "'a'", '"a"', '1', '$topo', 'current_url()', "date('d/m/Y', \$r->d)",
        'f($a, $b)', 'ucfirst($r->tipo)', "number_format(\$v, 2, ',', '.')",
        '<!-- c -->', 'print {', '// print', '?>', ';', '.', ',',
    ];

    /**
     * Token de saída + o que vem depois dele, montado de duas maneiras: a que a
     * varredura real usa e a que a varredura real NÃO usa.
     *
     * A segunda coluna é a que interessa. Um token solto (`echo $a` sem `;`, sem
     * `?>`) não é um statement de PHP, e ainda assim é o que a guarda tem de
     * reportar: se ela calar aqui, cala em view quebrada, que é quando mais importa.
     */
    private const OUTPUTS = [
        '<?= %s ?>',
        '<?php echo %s ?>',
        '<?php print %s; ?>',
        '<?php print %s ?>',
        '<?php if ($q) { echo %s; }',
        '<?php if ($q) { echo %s ?>',
        '<?php if ($q) { echo %s',
        '<?php if ($q) { %s; }',
        '<?php if ($q) { echo %s . $b; }',
        'echo %s;',
        'print(%s);',
        'print %s;',
        '@media print { %s }',
        '<?php // echo %s; ?>',
        '<?php echo %s . $b . \'</td>\';',
    ];

    /**
     * A propriedade: nenhum token de saída escapa das duas conferências.
     *
     * Para cada view gerada, ou alguma forma de `ECHO_SHAPES` casou, ou a guarda
     * devolveu um achado. As duas respostas juntas cobrem o arquivo; a segunda é a
     * que fecha o buraco quando a primeira falha, e é por isso que a asserção
     * permite as duas. O que a asserção PROÍE é o terceiro estado, que é o
     * silencioso.
     */
    #[Test]
    public function testNoOutputShapeEscapesBothChecks(): void
    {
        $unreported = 0;
        $covered = 0;

        foreach ($this->generateViews() as $view) {
            $covered += $this->coveredBy($view);

            // A guarda devolve o PRIMEIRO token sem cobertura, e o arquivo inteiro
            // está coberto por ele — é o que o relatório faz também. Então o silêncio
            // da guarda é o único estado em que a cobertura precisa ser conferida
            // token por token, e é nele que a propriedade é decidível.
            if (EscapingChecks::unrecognizedOutput($view) === null) {
                $unreported += $this->uncoveredTokens($view);
            }
        }

        $this->assertGreaterThan(0, $covered, 'o corpus não exercita nenhuma forma de saída');
        $this->assertSame(
            0,
            $unreported,
            'há token de saída que nenhuma forma de ECHO_SHAPES cobriu e que '
            . 'unrecognizedOutput() não reportou: a cobertura falhou em silêncio'
        );
    }

    /**
     * A guarda cobre, sozinha, o que a forma de saída não cobre.
     *
     * Se a guarda calasse em tudo, a propriedade acima passaria por acidente: o
     * corpus inteiro cairia no primeiro braço e `unreported()` valeria 0 sem que
     * nada tivesse sido conferido. Este teste é o que impede essa leitura, e ele
     * falha se a guarda virar no-op.
     */
    #[Test]
    public function testTheGuardReportsWhatNoShapeCovers(): void
    {
        // O caso que o inventário achou nas views: um `echo` de statement solto,
        // sem ponto e vírgula e sem tag de fechamento, que nenhuma das seis
        // formas reconhece.
        $view = '<?php if ($q) { echo $r->nome';

        $this->assertSame(
            0,
            $this->coveredBy($view),
            'esta view de teste era para ser um buraco de cobertura, e uma forma '
            . 'passou a cobrir: o teste de cima deixaria de provar a guarda'
        );

        $this->assertNotNull(
            EscapingChecks::unrecognizedOutput($view),
            'a guarda calou diante de um token de saída que nenhuma forma cobre'
        );
    }

    /**
     * A guarda reporta também o que NÃO é PHP, e é por isso que ela existe.
     *
     * `OUTPUT_TOKENS` é largo de propósito, então o `print` do `@media print` e o
     * `print()` do JavaScript chegam nele. Um gate que escondesse esses casos para
     * parecer limpo estaria trocando cobertura comprovável por ausência de
     * evidência, e o relatório voltaria a ser o de antes: verde porque ninguém
     * perguntou. Cada um destes é um achado que alguém precisa decidir, e é
     * exatamente por isso que o arquivo de baseline existe.
     *
     * A asserção é a união, e não a guarda sozinha: um token que não é PHP pode
     * mesmo assim ter sido coberto por uma das formas, e nesse caso ele vira achado
     * da varredura comum em vez de achado da guarda. O que não pode é nenhum dos
     * dois caminhos calar. O `<?php // echo $a; ?>` é o exemplo do segundo: a sexta
     * forma casa nele, e ele sai como `unescaped` — o que é o resultado certo, e é
     * por isso que a forma existe.
     */
    #[Test]
    public function testANonPhpTokenDoesNotDisappearFromTheReport(): void
    {
        foreach ([
            '<style>@media print { width: 210mm }</style>',
            '<?php // echo $a; ?>',
            '<script>window.print();</script>',
        ] as $view) {
            $this->assertTrue(
                EscapingChecks::unrecognizedOutput($view) !== null
                    || $this->reportsUnescaped($view),
                "um token de saída sumiu do relatório: {$view}"
            );
        }
    }

    /**
     * O relatório da varredura comum, do jeito que a CLI o monta.
     *
     * @return list<string>
     */
    private function reportsUnescaped(string $view): array
    {
        $findings = [];

        foreach (PhpExpression::ECHO_SHAPES as $shape) {
            if (! preg_match_all($shape['re'], $view, $matches)) {
                continue;
            }

            foreach ($matches[1] as $expression) {
                $hit = EscapingChecks::unescaped(
                    $shape['terminated'] ? PhpExpression::cutAtTopLevelSemicolon($expression) : $expression,
                    EscapingPolicy::default()
                );

                if ($hit !== null) {
                    $findings[] = $hit;
                }
            }
        }

        return $findings;
    }

    /**
     * Uma view sem saída nenhuma é aprovada, e é o outro lado da propriedade.
     *
     * Sem este caso, uma guarda que reprovasse tudo também passaria no teste
     * principal — ela nunca calaria. Fail-closed que reprova view sem valor é
     * fail-closed que ninguém vai olhar em uma semana.
     */
    #[Test]
    public function testAViewWithoutOutputIsNotAFinding(): void
    {
        $this->assertNull(EscapingChecks::unrecognizedOutput('<p><?= esc($a) ?></p>'));
        $this->assertNull(EscapingChecks::unrecognizedOutput('<style>@media screen { }</style>'));
    }

    /**
     * O snippet da guarda é um pedaço do arquivo, e é por isso que a linha sai certa.
     *
     * A varredura descobre a linha procurando o snippet no texto. A primeira
     * versão da guarda devolvia a linha inteira com o token colado no fim, que não
     * está em lugar nenhum do arquivo: toda linha saía 0, e um relatório com
     * linha 0 é um relatório que ninguém pode usar para ir ver o caso.
     */
    #[Test]
    public function testTheGuardSnippetIsInTheFile(): void
    {
        $view = "<p>texto</p>\n<?php if (\$q) { echo \$r->nome\n</p>\n";
        $snippet = EscapingChecks::unrecognizedOutput($view);

        $this->assertNotNull($snippet);

        [, $line] = PhpExpression::locate($view, $snippet);

        $this->assertSame(2, $line, "o snippet {$snippet} não foi localizado na linha do token");
    }

    /**
     * Quantas formas de saída a varredura real encontrou nesta view.
     */
    private function coveredBy(string $view): int
    {
        $count = 0;

        foreach (PhpExpression::ECHO_SHAPES as $shape) {
            $count += (int) preg_match_all($shape['re'], $view);
        }

        return $count;
    }

    /**
     * Quantos tokens de saída nenhuma das formas de `ECHO_SHAPES` cobriu.
     *
     * É a mesma conta que `EscapingChecks::coveredOutputRanges()` faz, reescrita
     * aqui de propósito. Um teste que chamasse o método privado da guarda provaria
     * que a guarda é consistente consigo mesma, e a propriedade que importa é outra:
     * que a guarda e a extração concordam sobre o que é cobertura. Escrevendo a
     * conta uma segunda vez, as duas implementasi divergem no dia em que uma delas
     * regredir, e é esse o dia que o teste existe para pegar.
     */
    private function uncoveredTokens(string $view): int
    {
        if (! preg_match_all(PhpExpression::OUTPUT_TOKENS, $view, $tokens, PREG_OFFSET_CAPTURE)) {
            return 0;
        }

        $ranges = [];

        foreach (PhpExpression::ECHO_SHAPES as $shape) {
            if (! preg_match_all($shape['re'], $view, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            foreach ($matches[0] as [$match, $offset]) {
                $ranges[] = [$offset, $offset + strlen($match)];
            }
        }

        $uncovered = 0;

        foreach ($tokens[0] as [, $offset]) {
            foreach ($ranges as [$start, $end]) {
                if ($offset >= $start && $offset < $end) {
                    continue 2;
                }
            }

            $uncovered++;
        }

        return $uncovered;
    }

    /**
     * O corpus de views, gerado com semente fixa.
     *
     * @return list<string>
     */
    private function generateViews(): array
    {
        $atoms = self::ATOMS;
        $views = [];

        foreach (self::OUTPUTS as $output) {
            foreach ($atoms as $a) {
                foreach ($atoms as $b) {
                    $views[] = '<p>ok</p>' . sprintf($output, $a) . '<p>' . $b . '</p>';
                }
            }
        }

        mt_srand(self::SEED);

        for ($i = 0; $i < 2000; $i++) {
            $parts = [];

            for ($j = 0, $n = mt_rand(1, 5); $j < $n; $j++) {
                $parts[] = $atoms[mt_rand(0, count($atoms) - 1)];
            }

            $joiners = [' . ', ' ? ', ', ', ' '];
            $body = implode($joiners[mt_rand(0, 3)], $parts);
            $views[] = sprintf(self::OUTPUTS[mt_rand(0, count(self::OUTPUTS) - 1)], $body);
        }

        return $views;
    }
}
