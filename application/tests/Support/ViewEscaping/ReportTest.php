<?php

namespace Tests\Support\ViewEscaping;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tools\ViewEscaping\EscapingPolicy;
use Tools\ViewEscaping\Report;

/**
 * O texto do relatório e o cabeçalho do baseline, que são as duas coisas que o
 * gate mostra para uma pessoa.
 *
 * Viviam no script de linha de comando, e é por isso que o cabeçalho do baseline
 * era destruído: `--update-baseline` reescrevia o arquivo a partir das chaves dos
 * achados, e a nota datada no topo não era parte de nenhuma estrutura. Aqui as
 * duas coisas são funções puras sobre os achados, e por isso podem ser conferidas
 * sem varrer diretório nenhum.
 *
 * Aviso para quem editar os comentários deste arquivo, pelo mesmo motivo dos
 * demais arquivos de tools/ViewEscaping: o fechamento de tag do PHP não pode ser
 * escrito num comentário de linha. Nos textos de advice, que é string, ele pode.
 */
final class ReportTest extends TestCase
{
    /**
     * Todo prefixo de conferência tem uma seção de relatório.
     *
     * Esta é a trava contra a divergência que a auditoria achou: o texto das
     * seções e os prefixos das chaves eram dois lugares, e acrescentar uma
     * conferência nova produzia um achado que saía no relatório dentro da seção de
     * escaping comum, com a orientação de envolver o valor em `esc()`. A
     * orientação errada para o defeito é pior que nenhuma, porque leva a pessoa a
     * mexer na coisa certa no lugar errado.
     *
     * A conferência de arquivo inteiro é o que produz prefixo; as formas de saída
     * não produzem, e por isso o prefixo vazio é uma seção legítima. O teste
     * confere as duas direções: nenhum prefixo sem seção, e nenhuma seção sem
     * prefixo — a segunda é o que impede a seção vazia de sobreviver quando o
     * prefixo correspondente é apagado.
     */
    #[Test]
    public function testEveryPolicyPrefixHasExactlyOneReportSection(): void
    {
        $policyPrefixes = [
            '',
            EscapingPolicy::JSON_PARSE_PREFIX,
            EscapingPolicy::PRE_RENDERED_PREFIX,
            EscapingPolicy::UNRECOGNIZED_OUTPUT_PREFIX,
        ];

        $reportPrefixes = Report::prefixes();

        sort($policyPrefixes);
        sort($reportPrefixes);

        $this->assertSame(
            $policyPrefixes,
            $reportPrefixes,
            'os prefixos de EscapingPolicy e as seções de Report são a mesma lista, '
            . 'e uma conferência nova sem seção sai com a orientação errada'
        );
    }

    /**
     * Toda seção tem um título e uma orientação, e nenhuma das duas é vazia.
     *
     * O título vazio sai como uma seção sem nome, e a orientação vazia sai como um
     * achado reprovado sem dizer o que fazer, que é o pior resultado possível
     * para quem está lendo a saída de um gate.
     */
    #[Test]
    public function testEverySectionHasATitleAndAdvice(): void
    {
        foreach (Report::prefixes() as $prefix) {
            $section = Report::section($prefix);

            $this->assertNotNull($section, "prefixo {$prefix} sem seção");
            [$title, $advice] = $section;

            $this->assertNotSame('', trim($title), "prefixo {$prefix} com título vazio");
            $this->assertNotSame('', trim($advice), "prefixo {$prefix} com orientação vazia");
        }
    }

    /**
     * As seções saem na ordem em que `prefixes()` declara, e uma seção sem achado
     * novo não aparece.
     *
     * A ordem é a de leitura: primeiro o que é XSS working, depois os três defeitos
     * de forma. Um título com nada embaixo é ruído que sugere que o gate achou
     * algo e não mostrou, e por isso a seção vazia some em vez de imprimir.
     */
    #[Test]
    public function testSectionsAreGroupedByCategoryAndEmptyOnesDisappear(): void
    {
        $findings = [
            'os.php|$os->defeito' => ['snippet' => '$os->defeito', 'line' => 4],
            'cfg.php|json-parse: JSON.parse("x")' => ['snippet' => 'JSON.parse("x")', 'line' => 9],
        ];

        $categories = [
            '' => ['os.php|$os->defeito'],
            EscapingPolicy::JSON_PARSE_PREFIX => ['cfg.php|json-parse: JSON.parse("x")'],
        ];

        $this->assertSame(
            ['', EscapingPolicy::JSON_PARSE_PREFIX],
            array_keys(Report::sections($findings, $categories)),
            'a ordem de leitura é a ordem das seções, e as vazias não saem'
        );
    }

    /**
     * A seção recebe os achados que a varredura marcar como novos, e nada mais.
     *
     * A distinção é a que o `array_diff_key` do CLI faz, e ela precisa acontecer
     * dentro de `Report`: a lista de categorias vem da varredura completa, e o
     * relatório só imprime o que ainda não está no baseline. Uma entrada já
     * registrada que aparecesse aqui sairia reprovada sem ser nova.
     */
    #[Test]
    public function testOnlyNewFindingsReachASection(): void
    {
        $categories = [
            '' => ['velha.php|$a', 'nova.php|$b'],
        ];

        $new = [
            'nova.php|$b' => ['snippet' => '$b', 'line' => 2],
        ];

        $sections = Report::sections($new, $categories);

        $this->assertSame(['nova.php|$b'], array_keys($sections['']));
    }

    /**
     * O cabeçalho recounts o que a varredura encontrou, e não o que alguém escreveu.
     *
     * A contagem que já divergiu é a de arquivos: o header dizia 46 e o número
     * real era 48, porque o header era um literal mantido à mão. Aqui as três
     * contagens saem dos arrays, e o caso falha se alguma delas deixar de
     * bater com o que foi passado.
     */
    #[Test]
    public function testTheHeaderCountsWhatWasScanned(): void
    {
        $findings = [
            'application/views/os/a.php|$a' => ['snippet' => '$a', 'line' => 1],
            'application/views/os/b.php|$b' => ['snippet' => '$b', 'line' => 1],
            'application/views/os/b.php|unrecognized-output: print();' => ['snippet' => 'print();', 'line' => 3],
        ];

        $categories = [
            '' => ['application/views/os/a.php|$a', 'application/views/os/b.php|$b'],
            EscapingPolicy::UNRECOGNIZED_OUTPUT_PREFIX => ['application/views/os/b.php|unrecognized-output: print();'],
        ];

        $header = Report::baselineHeader($findings, $categories, null);

        $this->assertStringContainsString('The 2 "unescaped" entries', $header);
        $this->assertStringContainsString('The 1 "unrecognized-output" entries', $header);
        $this->assertStringContainsString('per context in 2 files', $header, 'arquivos distintos, não entradas');
    }

    /**
     * O cabeçalho sobrevive a uma reescrita, e é por isso que ele existe.
     *
     * O defeito que este caso cobre é o `--update-baseline` do script de entrada
     * apagando as 234 entradas porque a pasta de views não estava lá: sem a
     * guarda de cobertura, uma escrita com zero achados produz um arquivo vazio e
     * código 0. O cabeçalho gerado é a parte do arquivo que a reescrita sempre
     * repõe, e o registro das entradas nunca depende de ele.
     *
     * A data é preservada de propósito: ela diz quando a dívida foi registrada, e
     * regravá-la a cada escrita faria alguém que acrescentou uma entrada legítima
     * parecer que revisou as 221.
     */
    #[Test]
    public function testTheHeaderIsRegeneratedAndKeepsTheRecordedDate(): void
    {
        $findings = ['application/views/os/a.php|$a' => ['snippet' => '$a', 'line' => 1]];
        $categories = ['' => ['application/views/os/a.php|$a']];

        $previous = "# Baseline for tools/check_view_escaping.php\n"
            . "#\n"
            . "# 2026-09-28 - The 221 \"unescaped\" entries below are NOT reviewed decisions.\n"
            . "# Older wording that should be replaced.\n";

        $header = Report::baselineHeader($findings, $categories, $previous);

        $this->assertStringContainsString('# 2026-09-28 - The 1 "unescaped" entries', $header);
        $this->assertStringNotContainsString('Older wording', $header, 'o header antigo foi regravado, não copiado');
    }

    /**
     * Sem header anterior, a data de hoje é a data do registro.
     *
     * É a primeira escrita, e não há marco anterior para preservar. O que não pode
     * acontecer é a ausência de data: um header sem quando é um header que não
     * responde "as entradas valem até quando".
     */
    #[Test]
    public function testAFreshHeaderIsDatedToday(): void
    {
        $header = Report::baselineHeader([], [], null);

        $this->assertStringContainsString(
            '# ' . date('Y-m-d') . ' - ',
            $header
        );

        $this->assertStringContainsString('no unescaped entries', $header);
        $this->assertStringContainsString(
            'no "unrecognized-output" entries',
            $header,
            'o caso de zero legível é escrito por extenso, e não como um número'
        );
    }
}
