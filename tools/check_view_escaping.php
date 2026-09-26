<?php

/**
 * check_view_escaping - guards application/views against unescaped output.
 *
 * Scans every view for value expressions that reach the page without going
 * through an escaper, and fails if any appear that are not in the baseline.
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
    // Variables that intentionally carry pre-rendered, already-escaped markup.
    '$topo', '$custom_error',
    // Ternaries that emit only literal keywords.
    "'selected'", '"selected"', "'disabled'", '"disabled"', "'checked'", '"checked"',
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
    echo "\nUnescaped output not present in the baseline:\n";
    foreach ($new as $key => $snippet) {
        echo '  ', $key, "\n      raw: ", $snippet, "\n";
    }
    echo "\nFix by wrapping the value in esc() (or esc_js/esc_url/esc_css for\n";
    echo "JS/URL/CSS contexts). If the value is intentionally pre-rendered or\n";
    echo "literal markup, run: php tools/check_view_escaping.php --update-baseline\n";
}

if ($stale) {
    echo "\nBaseline entries that no longer match (safe to prune):\n";
    foreach (array_keys($stale) as $key) {
        echo '  ', $key, "\n";
    }
}

exit($new ? 1 : 0);
