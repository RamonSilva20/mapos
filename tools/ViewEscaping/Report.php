<?php

namespace Tools\ViewEscaping;

/**
 * O texto que o gate imprime, e o texto que ele grava no topo do baseline.
 *
 * Estas duas coisas moravam no script de linha de comando, e é por isso que o
 * header do baseline era destruído: `--update-baseline` reescrevia o arquivo a
 * partir de `array_keys($findings)`, e o que estava acima da primeira entrada
 * — a nota datada que diz o que as 221 signify — não era parte de nenhuma
 * estrutura, era um literal no meio de um arquivo. Um literal sobrevive enquanto
 * ninguém reescreve o arquivo; a primeira vez que alguém reescreveu, foi embora.
 *
 * A promoção para cá tem a consequência prática de que o header passa a ser
 * gerado a partir dos próprios achados. As contagens que ele afirma não podem
 * divergir do que o gate encontrou, porque são as mesmas variáveis. O número que
 * já divergia — o header dizia 46 arquivos, e o número real era 48 — é
 * exatamente o tipo de coisa que um literal Written Once e mantido à mão faz.
 *
 * As seções do relatório também vivem aqui, e cada uma é identificada pelo
 * prefixo que `EscapingPolicy` define, e não por texto procurado dentro da chave.
 * A varredura sabe qual conferência reprovou cada entrada e devolve esse
 * agrupamento como dado (`ViewScanner::scan()`), de modo que nada no caminho
 * depois dela precisa ler a convenção de volta a partir da string.
 *
 * Aviso para quem editar os comentários deste arquivo, pelo mesmo motivo dos
 * demais arquivos de tools/ViewEscaping: o fechamento de tag do PHP não pode ser
 * escrito num comentário de linha. Ele encerra o modo PHP mesmo dentro do
 * comentário, e o resto do arquivo vira saída impressa. Nos textos de advice
 * abaixo a tag aparece, e ali é conteúdo de string, não comentário.
 */
final class Report
{
    /**
     * As seções do relatório, na ordem em que são impressas.
     *
     * A chave é o prefixo de `EscapingPolicy`, e a string vazia é a seção sem
     * prefixo: as expressões reprovadas pela varredura das formas de saída, que
     * são a maioria e não carregam prefixo na chave.
     *
     * `ReportTest` confere que todo prefixo de `EscapingPolicy` tem uma seção
     * aqui. Sem esse teste, acrescentar uma conferência nova produz um achado que
     * sai no relatório dentro da seção "resto" e com o texto de `esc()`, que é a
     * orientação errada para o defeito.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const SECTIONS = [
        '' => [
            'Unescaped output not present in the baseline:',
            "Fix by wrapping the value in esc() (or esc_json/esc_url/esc_img_src\n"
            . "JS/URL/CSS contexts). If the value is intentionally pre-rendered or\n"
            . 'literal markup, run: php tools/check_view_escaping.php --update-baseline',
        ],
        EscapingPolicy::JSON_PARSE_PREFIX => [
            'JSON.parse() fed a value that is not a string:',
            "esc_json() already emits a bare JSON value, and esc() emits\n"
            . "HTML entities, so JSON.parse() can never receive a usable string. Drop\n"
            . "the JSON.parse() call and assign the value directly:\n"
            . '    var config = <?= esc_json($arr) ?>;',
        ],
        EscapingPolicy::PRE_RENDERED_PREFIX => [
            'Pre-rendered markup escaped as if it were a plain value:',
            "These variables already hold finished markup, produced by\n"
            . "load->view(\$name, \$data, true) or built by the controller. Escaping one\n"
            . "turns the markup into visible text and neutralises any script nested\n"
            . 'inside it. Emit it raw instead: <?= $modalGerarPagamento ?>',
        ],
        EscapingPolicy::UNRECOGNIZED_OUTPUT_PREFIX => [
            'Output form the gate could not read:',
            "This is not a value that reached the page unescaped - it is an output\n"
            . "form none of PhpExpression::ECHO_SHAPES recognised, so no check ever\n"
            . "read it and nothing was approved. A token that is not PHP at all\n"
            . "(window.print(), @media print) also lands here, because the pattern is\n"
            . "deliberately wider than the forms. Look at the line: if it really emits a\n"
            . "value, escape it; if it is CSS, JS or a comment, add a line here saying\n"
            . "which. Do not delete the entry to make the report green - that is the\n"
            . 'one edit that turns this finding back into silence.',
        ],
    ];

    /**
     * A ordem em que as seções saem, que é a ordem de `SECTIONS`.
     *
     * @return list<string>
     */
    public static function prefixes(): array
    {
        return array_keys(self::SECTIONS);
    }

    /**
     * O título e a orientação de uma seção, ou null se o prefixo não tem uma.
     *
     * @return array{0: string, 1: string}|null
     */
    public static function section(string $prefix): ?array
    {
        return self::SECTIONS[$prefix] ?? null;
    }

    /**
     * O relatório: cada seção é a lista de achados que a varredura atribuiu a ela.
     *
     * A atribuição vem do mapa de `ViewScanner`, e não de procurar o prefixo no
     * texto da chave. Uma chave de achado é
     * "caminho|prefixo trecho" para as conferências de arquivo inteiro e
     * "caminho|trecho" para as outras, e a posição do prefixo no meio da string
     * é o que torna `str_contains` necessário. Trazer o agrupamento junto dos
     * achados é o que permite que ninguém tenha de saber dessa posição.
     *
     * Uma seção sem achado novo não aparece no resultado, e é por isso que quem
     * imprime itera o resultado em vez das chaves: um título com nada embaixo é
     * ruído que sugere que o gate achou algo e não mostrou.
     *
     * @param  array<string, array{snippet: string, line: int}>  $new
     * @param  array<string, list<string>>  $categories
     * @return array<string, array<string, array{snippet: string, line: int}>>
     */
    public static function sections(array $new, array $categories): array
    {
        $sections = [];

        foreach (array_keys(self::SECTIONS) as $prefix) {
            $keys = array_values(array_filter(
                $categories[$prefix] ?? [],
                static fn (string $key): bool => isset($new[$key])
            ));

            if ($keys === []) {
                continue;
            }

            $section = [];

            foreach ($keys as $key) {
                $section[$key] = $new[$key];
            }

            $sections[$prefix] = $section;
        }

        return $sections;
    }

    /**
     * O cabeçalho do arquivo de baseline, com as contagens tiradas dos achados.
     *
     * `@param  array<string, array{snippet: string, line: int}>  $findings`
     * @param  array<string, list<string>>  $categories
     * @param  string|null  $previous  o conteúdo atual do baseline, ou null se ele não existe
     */
    public static function baselineHeader(array $findings, array $categories, ?string $previous): string
    {
        $unread = count($categories[EscapingPolicy::UNRECOGNIZED_OUTPUT_PREFIX] ?? []);
        $unescaped = count($categories[''] ?? []);
        $files = self::distinctFiles($findings);
        $on = self::recordedOn($previous) ?? date('Y-m-d');

        $debt = $unescaped === 0
            ? 'There are no unescaped entries recorded below.'
            : "The {$unescaped} \"unescaped\" entries below are NOT reviewed decisions.";

        $lines = [
            '# Baseline for tools/check_view_escaping.php',
            '#',
            '# Written by `php tools/check_view_escaping.php --update-baseline`. The counts',
            '# below are computed from the scan that wrote the file, so they cannot describe',
            '# a different state than the entries that follow them.',
            '#',
            '# Each entry is "application/views/relative/path.php|value expression", or',
            '# "application/views/relative/path.php|prefix value expression" for the checks',
            '# that work on the whole file. A comment line above an entry is the expression.',
            '#',
            '# ---------------------------------------------------------------------------',
            "# {$on} - {$debt}",
            '# They are a debt this file records, and the reason is a hole in the gate,',
            '# not a judgement about the views.',
            '#',
            '# The fifth output form only matched an echo whose value started with a dollar',
            '# sign, so a line that wrapped a database value in a string concatenation - the',
            '# most common way this project emits one - was never read by any check. The',
            '# fail-closed guard (EscapingChecks::unrecognizedOutput()) is what found them.',
            "# Fixing the views means choosing an escaper per context in {$files} files, which",
            '# is view work, not gate work, and it was explicitly deferred: the decision is',
            '# to record the debt and make it countable rather than to fix it inside a gate',
            '# change.',
            '#',
            '# Until someone does that work, "xss:check is green" means "no NEW unescaped',
            "# output\", and the {$unescaped} above are the known remainder. The count is the",
            '# thing to watch, not the colour.',
            '# ---------------------------------------------------------------------------',
            '#',
        ];

        if ($unread === 0) {
            $lines[] = '# There are no "unrecognized-output" entries: every output form the guard';
            $lines[] = '# saw was one it could read.';
        } else {
            $lines[] = "# The {$unread} \"unrecognized-output\" entries are the guard reporting";
            $lines[] = '# tokens it could not read. Most are the word "print" in CSS';
            $lines[] = '# (@media print) and JavaScript (window.print()) - not PHP at all, and';
            $lines[] = '# the pattern is deliberately wider than the forms. The rest are';
            $lines[] = '# views/errors/cli/*.php, whose multi-line echo of several values is a real';
            $lines[] = '# form no regex here recognises. Each is a line someone should look at,';
            $lines[] = '# which is why they are entries and not silence.';
        }

        $lines[] = '#';
        $lines[] = '# Run `php tools/check_view_escaping.php --update-baseline` after adding an';
        $lines[] = '# entry on purpose, and review the diff - each line is a decision to leave a';
        $lines[] = '# value unescaped.';
        $lines[] = '#';

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * Quantos arquivos distintos as entradas tocam.
     *
     * A chave começa no caminho relativo, e o caminho é a parte antes da primeira
     * barra vertical. Cortar na primeira barra é o que separa "views/os/editarOs.php"
     * em "os/editarOs.php" sem deixar o resto da chave, que é o trecho.
     *
     * @param  array<string, array{snippet: string, line: int}>  $findings
     */
    private static function distinctFiles(array $findings): int
    {
        $paths = [];

        foreach (array_keys($findings) as $key) {
            $paths[explode('|', $key, 2)[0]] = true;
        }

        return count($paths);
    }

    /**
     * A data que o header anterior carregava, ou null se ele não tinha uma.
     *
     * A data é preservada de propósito. Ela diz quando a dívida foi registrada, e
     * regravá-la a cada `--update-baseline` transformaria um marco em um contador
     * de execuções: alguém que acrescentasse uma entrada legítima por motivo hoje
     * faria o arquivo dizer que a dívida inteira foi revista hoje. As contagens
     * mudam a cada escrita e por isso são recalculadas; a data é a única coisa do
     * header que é registro, e registro não se reescreve.
     */
    private static function recordedOn(?string $previous): ?string
    {
        if ($previous === null) {
            return null;
        }

        return preg_match('/^# (\d{4}-\d{2}-\d{2}) /m', $previous, $match) === 1
            ? $match[1]
            : null;
    }
}
