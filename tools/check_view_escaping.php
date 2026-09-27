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
$skipIfContains = [
    'esc(', 'esc_js(', 'esc_url(', 'esc_css(', 'esc_msg(', 'esc_json(',
    'html_escape(', 'htmlspecialchars(', 'printSafeHtml(', 'xss_clean(',
    'base_url(', 'site_url(', 'current_url(', 'uri_string(', 'index_page(',
    'date(', 'number_format(', 'intval(', 'str_pad(', 'str_replace(', 'ucfirst(',
    'strtoupper(', 'strtolower(', 'ucwords(', 'json_encode(', 'sprintf(',
    'round(', 'floor(', 'ceil(', 'abs(', 'trim(', 'nl2br(', 'wordwrap(',
    'limitarTexto(', 'money_format(', 'count(', 'implode(', 'dateInterval(',
    'form_hidden(', 'form_dropdown(', 'form_input(', 'form_password(',
    'form_checkbox(', 'form_submit(', 'form_button(', 'form_label(',
    'form_open(', 'form_close(', 'set_value(', 'config_item(',
    '$this->config->item(', '$this->uri->segment(',
    '$this->security->get_csrf', '$this->load->', '$this->db->count_all(',
    '$this->permission->', '$this->pagination->',
    // Ternaries that emit only literal keywords.
    "'selected'", '"selected"', "'disabled'", '"disabled"', "'checked'", '"checked"',
    ...$preRendered,
];

$echoShapes = [
    ['re' => '/<\?=\s*(.+?)\s*\?>/s', 'kind' => 'echo'],
    ['re' => '/<\?php\s+echo\s+(.+?);?\s*\?>/s', 'kind' => 'echo'],
    ['re' => '/<\?php\s+print\(\s*(.+?)\s*\);?\s*\?>/s', 'kind' => 'print'],
    ['re' => '/<\?php\s+print\s+(.+?);?\s*\?>/s', 'kind' => 'print'],
];

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

        if ($depth === 0 && $ch === '.') {
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

function splitTernary(string $expr): ?array
{
    $quote = null;
    $depth = 0;
    $q = null;

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

        if ($ch === '?') {
            $q = $i;
        } elseif ($ch === ':' && $q !== null) {
            return [
                substr($expr, 0, $q),
                substr($expr, $q + 1, $i - $q - 1),
                substr($expr, $i + 1),
            ];
        }
    }

    return null;
}

/**
 * Returns the escaped form of an expression, or null when the expression is
 * already safe. A non-null return means "this value reaches the page raw".
 */
function unescaped(string $expr, array $skipIfContains, int $depth = 0): ?string
{
    $expr = trim($expr);

    if ($expr === '' || strlen($expr) > 300 || $depth > 3) {
        return null;
    }

    foreach ($skipIfContains as $needle) {
        if (str_contains($expr, $needle)) {
            return null;
        }
    }

    if (preg_match('/\b(if|foreach|for|while|switch|function|return|echo|print|try|catch)\b/', $expr)) {
        return null;
    }

    if (str_contains($expr, ';') || str_contains($expr, '?>') || str_contains($expr, '<?')) {
        return null;
    }

    if (preg_match('/(?<!-)[<>]/', $expr)) {
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
            $hit = unescaped($branch, $skipIfContains, $depth + 1);
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
            $hit = unescaped($operand, $skipIfContains, $depth + 1);
            if ($hit !== null) {
                $hits[] = trim($hit);
            }
        }

        return $hits === [] ? null : implode(' , ', $hits);
    }

    return null;
}

/**
 * Detects JSON.parse() being fed a PHP echo of one of the escapers.
 *
 * None of the escapers can produce the string argument JSON.parse() expects:
 * esc_json() and esc_js() emit a bare JSON value with no surrounding quotes,
 * and esc() emits HTML entities that corrupt the payload. The config has to be
 * assigned directly instead, e.g. `var cfg = <?= esc_json($arr) ?>;`.
 *
 * esc_js(json_encode($x)) is the one legitimate spelling and is allowed.
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
            $hit = unescaped($m[0], $skipIfContains);
            if ($hit === null) {
                continue;
            }

            $snippet = (string) preg_replace('/\s+/', ' ', trim($hit));
            $key = $rel . '|' . $snippet;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $findings[$key] = $snippet;
        }
    }

    $snippet = jsonParseEscaper($content);
    if ($snippet !== null) {
        $key = $rel . '|' . JSON_PARSE_PREFIX . $snippet;
        $findings[$key] = $snippet;
    }

    $snippet = preRenderedEscaped($content, $preRendered);
    if ($snippet !== null) {
        $key = $rel . '|' . PRE_RENDERED_PREFIX . $snippet;
        $findings[$key] = $snippet;
    }
}

ksort($findings);

if ($updateBaseline) {
    $lines = [];
    foreach (array_keys($findings) as $key) {
        $lines[] = '# ' . $findings[$key] . PHP_EOL . $key . PHP_EOL;
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
    $unescaped = [];
    $badParse = [];
    $badPreRendered = [];

    foreach ($new as $key => $snippet) {
        if (str_contains($key, '|' . JSON_PARSE_PREFIX)) {
            $badParse[$key] = $snippet;
        } elseif (str_contains($key, '|' . PRE_RENDERED_PREFIX)) {
            $badPreRendered[$key] = $snippet;
        } else {
            $unescaped[$key] = $snippet;
        }
    }

    if ($unescaped) {
        echo "\nUnescaped output not present in the baseline:\n";
        foreach ($unescaped as $key => $snippet) {
            echo '  ', $key, "\n      raw: ", $snippet, "\n";
        }
        echo "\nFix by wrapping the value in esc() (or esc_js/esc_url/esc_css for\n";
        echo "JS/URL/CSS contexts). If the value is intentionally pre-rendered or\n";
        echo "literal markup, run: php tools/check_view_escaping.php --update-baseline\n";
    }

    if ($badParse) {
        echo "\nJSON.parse() fed a value that is not a string:\n";
        foreach ($badParse as $key => $snippet) {
            echo '  ', $key, "\n      raw: ", $snippet, "\n";
        }
        echo "\nesc_json() and esc_js() already emit a bare JSON value, and esc() emits\n";
        echo "HTML entities, so JSON.parse() can never receive a usable string. Drop\n";
        echo "the JSON.parse() call and assign the value directly:\n";
        echo '    var config = <?= esc_json($arr) ?>;' . "\n";
    }

    if ($badPreRendered) {
        echo "\nPre-rendered markup escaped as if it were a plain value:\n";
        foreach ($badPreRendered as $key => $snippet) {
            echo '  ', $key, "\n      raw: ", $snippet, "\n";
        }
        echo "\nThese variables already hold finished markup, produced by\n";
        echo "load->view(\$name, \$data, true) or built by the controller. Escaping one\n";
        echo "turns the markup into visible text and neutralises any <script> nested\n";
        echo "inside it. Emit it raw instead; the interpolations inside the view were\n";
        echo "already escaped where the view builds them:\n";
        echo '    <?= $modalGerarPagamento ?>' . "\n";
    }
}

if ($stale) {
    echo "\nBaseline entries that no longer match (safe to prune):\n";
    foreach (array_keys($stale) as $key) {
        echo '  ', $key, "\n";
    }
}

exit($new ? 1 : 0);
