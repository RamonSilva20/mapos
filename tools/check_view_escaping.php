<?php

/**
 * check_view_escaping - guards application/views against unescaped output.
 *
 * Runs three independent checks over every view:
 *
 *   1. Escaping: value expressions that reach the page without going through an
 *      escaper, for the wrong output context.
 *   2. Shape: values whose *type* the chosen escaper changes, such as a
 *      JSON.parse() fed an escaper that emits a bare JSON value.
 *   3. Pre-rendered markup: values that already hold finished HTML being
 *      wrapped in an escaper, which breaks the page instead of protecting it.
 *
 * All three fail when they appear and are not in the baseline.
 *
 * Usage:
 *   php tools/check_view_escaping.php                  # check (exit 1 on new findings)
 *   php tools/check_view_escaping.php --update-baseline
 *   php tools/check_view_escaping.php --no-baseline    # report everything
 *
 * Baseline entries are "relative/path.php|snippet" so they survive unrelated
 * line shifts. Review every entry: each one is a deliberate decision to leave
 * a value unescaped.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script can only be run from the command line.\n");
}

$root = dirname(__DIR__);
$viewDir = $root . '/application/views';
$baselineFile = __DIR__ . '/xss-baseline.txt';
$updateBaseline = in_array('--update-baseline', $argv, true);
$ignoreBaseline = in_array('--no-baseline', $argv, true);

/**
 * Variables that carry finished, pre-rendered markup.
 *
 * These come from $this->load->view($name, $data, true) or from markup the
 * controller built by hand, so the view must emit them raw. Escaping one is
 * not merely redundant: htmlspecialchars() turns the markup into visible text
 * and neutralises any <script> nested inside it, which silently disables the
 * behaviour that view was included for.
 *
 * Kept as one list because both the skip rules below and the
 * preRenderedEscaped() check read it, so the two cannot drift apart.
 */
$preRendered = ['$topo', '$custom_error', '$modalGerarPagamento'];

// Expressions that are already safe or intentionally raw.
//
// This is an ALLOWLIST of function names, not a blocklist of substrings, and
// that distinction is the whole point of the tool. The previous version listed
// every string helper it had seen (strtoupper, ucfirst, nl2br, trim,
// str_replace, xss_clean, ...) and treated the mere presence of one as proof
// that the value was escaped. None of them escape: wrapping a column in
// strtoupper() passed the gate while being a working XSS, and so did
// xss_clean(), which AGENTS.md explicitly documents as an input filter and not
// an encoder.
//
// So a call is safe only when its function is listed here, because the listed
// ones either escape, or return a value that cannot carry markup (a numeric
// coercion, a format built from a format string), or are CI3 helpers that
// escape internally. Anything unlisted is walked into argument by argument,
// which makes the check fail closed: a new helper is a finding, not a silence.
//
// (Note for editors: never write a literal PHP close tag in a `//` comment
// here. It ends PHP mode even inside the comment, and the rest of this file
// silently becomes printed output.)
$allowedFunctions = [
    // Escapers. The only category that makes user data safe on its own.
    'esc', 'esc_json', 'esc_url', 'esc_img_src', 'esc_css', 'esc_msg',
    'html_escape', 'htmlspecialchars', 'printSafeHtml',

    // Numeric coercion: the return type is int/float/bool, so no markup survives.
    'abs', 'ceil', 'floor', 'intval', 'round', 'strtotime', 'count',
    'array_sum', 'array_column',

    // Formatting: the output comes from a format string, not from the value.
    'date', 'dateInterval', 'date_create', 'date_format', 'number_format', 'money_format',

    // Reflection: a class name, never a request value.
    'get_class',

    // URLs and config the application itself defines, never user input.
    'base_url', 'site_url', 'current_url', 'uri_string', 'index_page',
    'config_item', 'segment', 'get_csrf', 'get_csrf_hash', 'get_csrf_token_name',

    // CI3 form helpers: form_prep() escapes the value internally.
    'form_open', 'form_close', 'form_input', 'form_password', 'form_hidden',
    'form_dropdown', 'form_checkbox', 'form_submit', 'form_button', 'form_label',
    'set_value', 'set_select',

    // Produce markup or a count, not user text. create_links() is safe by
    // construction: the page numbers are (int)-cast and every query value goes
    // through http_build_query(), which percent-encodes the quotes.
    'view', 'count_all', 'create_links', 'create_nprev', 'create_next',
];

/**
 * Formas de saída do PHP que o gate precisa varrer.
 *
 * `terminated` diz se a captura já vem fechada pela tag de fim. Quando vem, o
 * `;` que o PHP interpretaria como separador de statements pode estar só ali
 * por construção e a expressão precisa ser cortada antes de ser analisada;
 * quando a forma é um statement solto, o `;` já é parte do match e cortar de
 * novo comeria o operando seguinte. As quatro primeiras linhas terminam a
 * tag; a quinta não.
 *
 * As quatro formas terminadas se mantêm separadas em vez de virar uma alternância
 * só porque a unificação muda a captura: `<?php echo $x ?>` absorveria o
 * `php echo` no grupo, e `<?= esc($x) ?>` perderia o parêntese final, que é o
 * que fecha a chamada. Medido, não suposto.
 *
 * A quinta é o `echo $x;` que NÃO abre o bloco — o caso `<?php if (...) { echo
 * $x; }`, o mais comum nos templates do CI3. Só a forma de valor único entra:
 * um `echo` que começa por literal é montagem de markup pré-renderizado (o
 * padrão `echo '<td>...' . $cor;`), em que o escape acontece onde o HTML é
 * montado e um regex não tem como distinguir de uma interpolação perigosa.
 * Incluir as duas formas custou 237 achados, quase todos falsos, e um gate que
 * grita lobo é ignorado.
 */
$echoShapes = [
    ['re' => '/<\?=\s*(.+?)\s*\?>/s', 'terminated' => true],
    ['re' => '/<\?php\s+echo\s+(.+?)\s*\?>/s', 'terminated' => true],
    ['re' => '/<\?php\s+print\(\s*(.+?)\s*\)\s*;?\s*\?>/s', 'terminated' => false],
    ['re' => '/<\?php\s+print\s+(.+?)\s*\?>/s', 'terminated' => true],
    ['re' => '/\b(?:echo|print)\s+(\$[A-Za-z_]\w*(?:(?:->|::)\w+|\[[^\]]*\])*)\s*;/', 'terminated' => false],
];

/**
 * Corta a expressao no primeiro `;` de nivel superior.
 *
 * PHP compila as chaves do arquivo inteiro, atravessando `?>`: um
 * `<?php if (...) { ?>` continua aberto enquanto um `<?= $x ?>` no meio roda
 * dentro dele. Entao `<?php echo $a; } ?>` e legitimo, e o `}` fecha o `if` —
 * nao faz parte da expressao ecoada. Sem este corte a captura trazia `; }` e
 * nenhuma regra casava, o que transformava uma linha segura em denuncia.
 */
function cutAtTopLevelSemicolon(string $expr): string
{
    $quote = null;
    $depth = 0;

    for ($i = 0, $n = strlen($expr); $i < $n; $i++) {
        $ch = $expr[$i];

        if ($quote !== null) {
            if ($ch === '\\') {
                $i++;
            } elseif ($ch === $quote) {
                $quote = null;
            }

            continue;
        }

        if ($ch === '"' || $ch === "'") {
            $quote = $ch;
        } elseif ($ch === '(' || $ch === '[') {
            $depth++;
        } elseif ($ch === ')' || $ch === ']') {
            $depth--;
        } elseif ($ch === ';' && $depth === 0) {
            return substr($expr, 0, $i);
        }
    }

    return $expr;
}

/**
 * Byte offset of a whitespace-normalized snippet inside the original content.
 *
 * The two functions that report by snippet return it normalized, because a
 * snippet spanning a line break is unreadable in a terminal report. `strpos()`
 * on that normalized text never finds it in the original: the indentation and
 * the newline are still there. The result is `false`, and `lineAt()` turns
 * `false` into line 0 — so the finding was reported against a line that does
 * not exist, which is the one number in the report a reader has to trust.
 *
 * Matching with `\s+` where the normalized text has a single space finds the
 * real offset instead. The literal parts are escaped, so a snippet whose own
 * text contains a space still only matches at its own position.
 */
function offsetOfNormalized(string $content, string $snippet): int|false
{
    $pattern = implode('\\s+', array_map(preg_quote(...), explode(' ', $snippet)));

    return preg_match('/' . $pattern . '/', $content, $m, PREG_OFFSET_CAPTURE) ? $m[0][1] : false;
}

/** 1-based line number for a byte offset, for the human-readable report. */
function lineAt(string $content, int|false $offset): int
{
    if ($offset === false || $offset < 0) {
        return 0;
    }

    return substr_count($content, "\n", 0, $offset) + 1;
}

function isBareValue(string $e): bool
{
    return (bool) preg_match('/^\$[A-Za-z_][A-Za-z0-9_]*(\s*->\s*[A-Za-z_][A-Za-z0-9_]*|\s*\[[^\[\]]*\])*$/', trim($e));
}

function isSessionValue(string $e): bool
{
    return (bool) preg_match('/^\$this->session->userdata\(.*\)$/s', trim($e));
}

function splitTopLevel(string $expr): ?array
{
    $quote = null;
    $depth = 0;
    $operands = [];
    $current = '';

    for ($i = 0, $n = strlen($expr); $i < $n; $i++) {
        $ch = $expr[$i];

        if ($quote !== null) {
            $current .= $ch;
            if ($ch === '\\') {
                $i++;
                $current .= $expr[$i] ?? '';
            } elseif ($ch === $quote) {
                $quote = null;
            }

            continue;
        }

        if ($ch === "'" || $ch === '"') {
            $quote = $ch;
            $current .= $ch;

            continue;
        }

        if ($ch === '(' || $ch === '[') {
            $depth++;
        } elseif ($ch === ')' || $ch === ']') {
            $depth--;
        }

        // `.` concatena e `,` é o operador vírgula — os dois são separadores de
        // operando em nível superior. A vírgula importa porque é o idioma do
        // próprio CodeIgniter nos templates de erro, que escrevem a mensagem e
        // a quebra de linha como dois operandos separados por vírgula. Sem
        // partir nela, a linha caía no fim de `unescaped()` como "não consegui
        // ler", que é fail-closed e por isso acusava uma linha já escapada.
        //
        // (O fechamento de tag do PHP não pode aparecer neste comentário: em
        // um comentário de linha ele encerra o modo PHP e o resto do arquivo
        // vira saída. Ver o mesmo aviso no topo deste arquivo.)
        if ($depth === 0 && ($ch === '.' || $ch === ',')) {
            $operands[] = $current;
            $current = '';

            continue;
        }

        $current .= $ch;
    }

    if ($quote !== null || $depth !== 0) {
        return null;
    }

    $operands[] = $current;

    return count($operands) > 1 ? $operands : null;
}

/**
 * Splits a ternary into [condition, then, else], or null when there is none.
 *
 * Also handles the abbreviated form `$a ?: $b`, which is `$a ? $a : $b`. It is
 * reported as [cond, cond, else] so the caller analyses the same two branches
 * it would analyse in the long form — otherwise the condition is dropped and
 * `<?= $p->preco ?: $p->precoVenda ?>` reads as if `$p->precoVenda` were the
 * only value reaching the page.
 */
function splitTernary(string $expr): ?array
{
    $quote = null;
    $depth = 0;
    $q = null;
    $abbreviated = false;

    for ($i = 0, $n = strlen($expr); $i < $n; $i++) {
        $ch = $expr[$i];

        if ($quote !== null) {
            if ($ch === '\\') {
                $i++;
            } elseif ($ch === $quote) {
                $quote = null;
            }

            continue;
        }

        if ($ch === "'" || $ch === '"') {
            $quote = $ch;

            continue;
        }

        if ($ch === '(' || $ch === '[') {
            $depth++;

            continue;
        }

        if ($ch === ')' || $ch === ']') {
            $depth--;

            continue;
        }

        if ($depth !== 0) {
            continue;
        }

        // `?` can also be the start of `??`, which is not a ternary.
        if ($ch === '?' && ($i + 1 >= $n || $expr[$i + 1] !== '?')) {
            $q = $i;
            $abbreviated = $i + 1 < $n && $expr[$i + 1] === ':';
        } elseif ($ch === ':' && $q !== null && ($i + 1 >= $n || $expr[$i + 1] !== ':')) {
            $condition = substr($expr, 0, $q);

            return [
                $condition,
                $abbreviated ? $condition : substr($expr, $q + 1, $i - $q - 1),
                substr($expr, $i + 1),
            ];
        }
    }

    return null;
}

/**
 * Splits `f($a, $b)` into ['f', '$a, $b'], or null when the expression is not a
 * single call whose first parenthesis is the callee's.
 *
 * Deliberately anchored: the reference must be the *whole* head of the
 * expression, so `$this->uri->segment(1)` is a call on a method while
 * `$this->uri->segment(1) . $row->nome` is a concatenation the caller has
 * already split apart before asking.
 */
function isFunctionCall(string $e): ?array
{
    if (! preg_match('/^((?:\$[A-Za-z_][A-Za-z0-9_]*(?:\s*->\s*[A-Za-z_][A-Za-z0-9_]*)*)|[A-Za-z_][A-Za-z0-9_]*)\s*\((.*)\)$/s', trim($e), $m)) {
        return null;
    }

    return [$m[1], $m[2]];
}

/** `seg` of `$this->uri->seg` / `seg`, so methods and functions share one list. */
function calleeName(string $ref): string
{
    $parts = preg_split('/\s*->\s*/', trim($ref));

    return end($parts);
}

/** Splits a call's argument list on top-level commas; `f()` yields []. */
function splitCallArgs(string $args): array
{
    // `f()` tem zero argumentos, e nao um argumento vazio. Sem esta guarda o
    // `[]` final do acumulador produzia `['']`, e `unescaped()` recebia uma
    // string vazia no lugar de uma lista — o que fazia `foo()` passar como
    // seguro, ja que nao havia argumento para reportar.
    if (trim($args) === '') {
        return [];
    }

    $quote = null;
    $depth = 0;
    $out = [];
    $current = '';

    for ($i = 0, $n = strlen($args); $i < $n; $i++) {
        $ch = $args[$i];

        if ($quote !== null) {
            $current .= $ch;
            if ($ch === '\\') {
                $i++;
                $current .= $args[$i] ?? '';
            } elseif ($ch === $quote) {
                $quote = null;
            }

            continue;
        }

        if ($ch === "'" || $ch === '"') {
            $quote = $ch;
            $current .= $ch;

            continue;
        }

        if ($ch === '(' || $ch === '[') {
            $depth++;
        } elseif ($ch === ')' || $ch === ']') {
            $depth--;
        }

        if ($depth === 0 && $ch === ',') {
            $out[] = $current;
            $current = '';

            continue;
        }

        $current .= $ch;
    }

    $out[] = $current;

    return $out;
}

/**
 * Normaliza o que fica entre `<?=` e `?>`.
 *
 * O `;` final é legal e comum (`<?= $emitente->url_logo; ?>`), mas quebrava o
 * casamento de `isBareValue()`, que ancora em `$`. A expressão caía então no
 * fim de `unescaped()`, que devolvia `null`, e a linha era declarada segura.
 * Isto é o tipo de bug que o gate existe para achar, então o `;` é removido em
 * vez de ser motivo para desistir da análise.
 *
 * Parênteses externas e casts também saem, e não por conveniência do parser:
 * `($a ? esc($x) : esc($y))` e `(int) $config['n']` são seguros, e sem esta
 * etapa o gate os reportaria como valor cru. Um relatório cheio de falso
 * positivo treina o leitor a ignorar o relatório.
 */
function normalizeExpression(string $expr): string
{
    $expr = rtrim(trim($expr), ';');

    while (strlen($expr) > 1 && $expr[0] === '(' && $expr[strlen($expr) - 1] === ')') {
        $inner = substr($expr, 1, -1);
        $depth = 0;
        $quote = null;
        $balanced = true;

        for ($i = 0, $n = strlen($inner); $i < $n; $i++) {
            $ch = $inner[$i];

            if ($quote !== null) {
                if ($ch === '\\') {
                    $i++;
                } elseif ($ch === $quote) {
                    $quote = null;
                }

                continue;
            }

            if ($ch === '"' || $ch === "'") {
                $quote = $ch;
            } elseif ($ch === '(') {
                $depth++;
            } elseif ($ch === ')') {
                $depth--;

                if ($depth < 0) {
                    $balanced = false;

                    break;
                }
            }
        }

        // Só desembrulha quando o par de fora é mesmo o par de fora; caso
        // contrário a expressão era uma concatenação entre parênteses.
        if (! $balanced || $depth !== 0 || $quote !== null) {
            break;
        }

        $expr = trim($inner);
    }

    return $expr;
}

/**
 * Valores que não vêm de lugar nenhum: o próprio texto já é o valor final.
 *
 * Checado antes do corte de fluxo e de "não analysei isto", senão uma
 * concatenação de literal com variável — `<?= '<b>' . $row->nome ?>` — seria
 * reportada como valor cru por causa do `<` do literal.
 */
function isLiteral(string $e): bool
{
    $e = trim($e);

    if ($e === '') {
        return false;
    }

    if (preg_match("/^'(?:[^'\\\\]|\\\\.)*'$/", $e) === 1) {
        return true;
    }

    if (preg_match('/^"(?:[^"\\\\]|\\\\.)*"$/s', $e) === 1) {
        return true;
    }

    return preg_match('/^-?\d+(\.\d+)?$|^true$|^false$|^null$/i', $e) === 1;
}

/**
 * `(int) $x`, `(float) $y`: o cast converte para um tipo que não carrega
 * marcação, então o valor não pode chegar cru na página.
 */
function isScalarCast(string $e): bool
{
    return preg_match('/^\(\s*(?:int|integer|float|double|string|bool|boolean)\s*\)/i', trim($e)) === 1;
}

/**
 * Returns the escaped form of an expression, or null when the expression is
 * already safe. A non-null return means "this value reaches the page raw".
 *
 * O caminho feliz é curto e explícito. Tudo que sobra no fim — inclusive uma
 * expressão que esta função não entende — é achado, porque a
 * security gate that passes what it failed to parse is not a gate. The earlier
 * version returned null on five separate "I could not analyse this" paths
 * (>300 chars, depth >3, control-flow keyword, a literal `<`/`>`, any `;`), and
 * every one of them was a way to make a real finding disappear.
 */
function unescaped(string $expr, array $allowedFunctions, array $preRendered): ?string
{
    $expr = normalizeExpression($expr);

    if ($expr === '') {
        return null;
    }

    // Pre-rendered markup, emitted raw by design. Checked before isBareValue()
    // because these are plain variables; the same list drives
    // preRenderedEscaped(), so a site cannot be raw here and escaped there.
    if (in_array($expr, $preRendered, true)) {
        return null;
    }

    if (isLiteral($expr) || isScalarCast($expr)) {
        return null;
    }

    if (isBareValue($expr) || isSessionValue($expr)) {
        return $expr;
    }

    $ternary = splitTernary($expr);
    if ($ternary !== null) {
        [, $then, $else] = $ternary;
        $hits = [];

        foreach ([$then, $else] as $branch) {
            $hit = unescaped($branch, $allowedFunctions, $preRendered);
            if ($hit !== null) {
                $hits[] = trim($hit);
            }
        }

        return $hits === [] ? null : implode(' , ', $hits);
    }

    $operands = splitTopLevel($expr);
    if ($operands !== null) {
        $hits = [];
        foreach ($operands as $operand) {
            $hit = unescaped($operand, $allowedFunctions, $preRendered);
            if ($hit !== null) {
                $hits[] = trim($hit);
            }
        }

        return $hits === [] ? null : implode(' , ', $hits);
    }

    // Checked after the concatenation split on purpose: `esc($a) . $y` also
    // ends in a closing parenthesis, so testing the call first would let the
    // leading escaper vouch for the whole expression.
    $call = isFunctionCall($expr);
    if ($call !== null) {
        [$ref, $args] = $call;

        if (in_array(calleeName($ref), $allowedFunctions, true)) {
            return null;
        }

        $args = splitCallArgs($args);

        // `foo()` devolve conteúdo que não foi escapado e não tem argumento
        // para inspecionar, então não há nada que a approve.
        if ($args === []) {
            return $expr;
        }

        $hits = [];
        foreach ($args as $arg) {
            $hit = unescaped($arg, $allowedFunctions, $preRendered);
            if ($hit !== null) {
                $hits[] = trim($hit);
            }
        }

        return $hits === [] ? null : implode(' , ', $hits);
    }

    // Chega aqui o que este gate não sabe ler: expressão longa demais, aninhada
    // além do limite, com palavra de controle, com `<`/`>` ou com tag PHP
    // dentro. Nenhum desses é uma prova de que o valor está escapado — são
    // apenas casos que o analisador não deu conta. Reportar é a única leitura
    // honesta; a alternativa deixava a linha passar em silêncio.
    return $expr;
}

/**
 * Detects JSON.parse() being fed a PHP echo of one of the escapers.
 *
 * None of the escapers can produce the string argument JSON.parse() expects:
 * esc_json() emits a bare JSON value with no surrounding quotes,
 * and esc() emits HTML entities that corrupt the payload. The config has to be
 * assigned directly instead, e.g. `var cfg = <?= esc_json($arr) ?>;`.
 *
 * esc_json(json_encode($x)) is the one legitimate spelling and is allowed.
 *
 * Returns the offending snippet, or null when every call is well formed.
 */
function jsonParseEscaper(string $content): ?string
{
    $re = '/JSON\.parse\(\s*["\']?\s*<\?(?:=\s*esc(?:_js|_json)?\s*\(|php\s+echo\s+esc(?:_js|_json)?\s*\()(.*?)\?>(?:\s*["\'])?\s*\)?/s';

    if (! preg_match_all($re, $content, $matches, PREG_OFFSET_CAPTURE)) {
        return null;
    }

    foreach ($matches[0] as $i => $m) {
        if (str_contains($matches[1][$i][0], 'json_encode(')) {
            continue;
        }

        return (string) preg_replace('/\s+/', ' ', trim($m[0]));
    }

    return null;
}

const JSON_PARSE_PREFIX = 'json-parse: ';
const PRE_RENDERED_PREFIX = 'prerendered-esc: ';

/**
 * Detects a pre-rendered markup variable being wrapped in an HTML escaper.
 *
 * $preRendered values are already finished markup, so escaping them breaks the
 * page instead of protecting it. This is the inverse of the skip rule that
 * accepts them raw, and it catches the mistake of adding the escaper later.
 *
 * The match is anchored on the escaper wrapping the variable directly, so a
 * genuinely raw value such as esc($result->idOs) is not reported. The known
 * limitation is that it cannot see markup carried by a variable that is not
 * listed in $preRendered, nor one hidden behind a call such as trim().
 *
 * Returns the offending snippet, or null when nothing is mis-escaped.
 */
function preRenderedEscaped(string $content, array $preRendered): ?string
{
    $vars = implode('|', array_map(fn ($v) => preg_quote($v, '/'), $preRendered));
    $re = '/\b(?:esc|esc_html|html_escape|htmlspecialchars)\(\s*(' . $vars . ')\s*\)/';

    if (! preg_match($re, $content, $m)) {
        return null;
    }

    return (string) preg_replace('/\s+/', ' ', trim($m[0]));
}

$findings = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewDir));

foreach ($rii as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $rel = str_replace($root . '/', '', $path);
    $content = file_get_contents($path);
    $seen = [];

    foreach ($echoShapes as $shape) {
        if (! preg_match_all($shape['re'], $content, $matches, PREG_OFFSET_CAPTURE)) {
            continue;
        }

        foreach ($matches[1] as $m) {
            $expression = $shape['terminated'] ? cutAtTopLevelSemicolon($m[0]) : $m[0];
            $hit = unescaped($expression, $allowedFunctions, $preRendered);
            if ($hit === null) {
                continue;
            }

            // O trecho é normalizado só para ser legível, e a chave do baseline
            // usa essa forma de propósito: uma entrada sobrevive a uma mudança
            // que só mexe na indentação. A linha NÃO sai do texto normalizado —
            // o offset do regex (`$m[1]`) já é a posição exata da expressão, e
            // procurar o texto de novo acha a PRIMEIRA ocorrência da mesma
            // expressão no arquivo, que costuma ser outra linha. Foi
            // exatamente o que aconteceu com `esc_scalar($result->idOs)`: o
            // regex apontava 411, `strpos()` achou o `$result->idOs` da linha 8,
            // dentro de um echo de markup, e o gate apontou a linha errada.
            $snippet = (string) preg_replace('/\s+/', ' ', trim($hit));
            $key = $rel . '|' . $snippet;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            // The baseline key stays line-free so entries survive unrelated
            // line shifts, but the report needs the line to be actionable, and
            // `$m[1]` is the offset the match started at.
            $findings[$key] = ['snippet' => $snippet, 'line' => lineAt($content, $m[1])];
        }
    }

    $snippet = jsonParseEscaper($content);
    if ($snippet !== null) {
        $key = $rel . '|' . JSON_PARSE_PREFIX . $snippet;
        $findings[$key] = ['snippet' => $snippet, 'line' => lineAt($content, offsetOfNormalized($content, $snippet))];
    }

    $snippet = preRenderedEscaped($content, $preRendered);
    if ($snippet !== null) {
        $key = $rel . '|' . PRE_RENDERED_PREFIX . $snippet;
        $findings[$key] = ['snippet' => $snippet, 'line' => lineAt($content, offsetOfNormalized($content, $snippet))];
    }
}

ksort($findings);

if ($updateBaseline) {
    $lines = [];
    foreach (array_keys($findings) as $key) {
        $lines[] = '# ' . $findings[$key]['snippet'] . PHP_EOL . $key . PHP_EOL;
    }
    file_put_contents($baselineFile, implode(PHP_EOL, $lines));
    echo 'Baseline written with ', count($findings), " entries.\n";
    exit(0);
}

$baseline = [];
if (! $ignoreBaseline && is_file($baselineFile)) {
    foreach (file($baselineFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line !== '' && ! str_starts_with($line, '#')) {
            $baseline[$line] = true;
        }
    }
}

$new = array_diff_key($findings, $baseline);
$stale = array_diff_key($baseline, $findings);

echo 'Views scanned      : ', iterator_count(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewDir))), "\n";
echo 'Findings           : ', count($findings), "\n";
echo 'Baseline entries   : ', count($baseline), "\n";
echo 'New (unsuppressed) : ', count($new), "\n";
echo 'Stale baseline     : ', count($stale), "\n";

if ($new) {
    // Uma tabela por tipo de achado, e um laço só. As três seções tinham o
    // mesmo cabeçalho e o mesmo "para cada achado, imprima chave:linha e o
    // trecho"; só o título e a Advice diferiam. Escrever isso três vezes
    // significava que Consertar a finding: mexer no segundo bloco e esquecer o
    // terceiro não dava erro nenhum.
    $reports = [
        '' => [
            'Unescaped output not present in the baseline:',
            "Fix by wrapping the value in esc() (or esc_json/esc_url/esc_img_src\n"
            . "JS/URL/CSS contexts). If the value is intentionally pre-rendered or\n"
            . "literal markup, run: php tools/check_view_escaping.php --update-baseline",
        ],
        JSON_PARSE_PREFIX => [
            'JSON.parse() fed a value that is not a string:',
            "esc_json() already emits a bare JSON value, and esc() emits\n"
            . "HTML entities, so JSON.parse() can never receive a usable string. Drop\n"
            . "the JSON.parse() call and assign the value directly:\n"
            . '    var config = <?= esc_json($arr) ?>;',
        ],
        PRE_RENDERED_PREFIX => [
            'Pre-rendered markup escaped as if it were a plain value:',
            "These variables already hold finished markup, produced by\n"
            . "load->view(\$name, \$data, true) or built by the controller. Escaping one\n"
            . "turns the markup into visible text and neutralises any <script> nested\n"
            . "inside it. Emit it raw instead; the interpolations inside the view were\n"
            . "already escaped where the view builds them:\n"
            . '    <?= $modalGerarPagamento ?>',
        ],
    ];

    $tagged = static fn (array $findings, string $prefix): array
        => array_filter($findings, fn ($k) => str_contains($k, '|' . $prefix), ARRAY_FILTER_USE_KEY);

    // A seção sem prefixo é o resto: os achados de escaping não-load prefixo,
    // porque quem gera a chave deles é a varredura das formas de echo. Filtrar
    // pelas duas tags prefixadas primeiro deixa o "resto" ser o resto, em vez
    // de repetir a lista de prefixos no predicado.
    $sections = [
        '' => array_diff_key($new, $tagged($new, JSON_PARSE_PREFIX), $tagged($new, PRE_RENDERED_PREFIX)),
        JSON_PARSE_PREFIX => $tagged($new, JSON_PARSE_PREFIX),
        PRE_RENDERED_PREFIX => $tagged($new, PRE_RENDERED_PREFIX),
    ];

    foreach ($reports as $prefix => [$title, $advice]) {
        $group = $sections[$prefix];

        if (! $group) {
            continue;
        }

        echo "\n", $title, "\n";
        foreach ($group as $key => $finding) {
            echo '  ', $key, ':', $finding['line'], "\n      raw: ", $finding['snippet'], "\n";
        }
        echo "\n", $advice, "\n";
    }
}

if ($stale) {
    echo "\nBaseline entries that no longer match (safe to prune):\n";
    foreach (array_keys($stale) as $key) {
        echo '  ', $key, "\n";
    }
}

exit($new ? 1 : 0);
