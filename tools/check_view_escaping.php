<?php

/**
 * check_view_escaping - guarda as views de application/views contra saída sem escape.
 *
 * Roda quatro conferências independentes sobre cada view:
 *
 *   1. Escaping: expressões de valor que chegam à página sem passar por um
 *      escaper, para o contexto de saída errado.
 *   2. Forma: valores cujo TIPO muda por causa do escaper escolhido, como um
 *      JSON.parse() alimentado por um escaper que emite um JSON cru.
 *   3. Markup pronto: valores que já carregam HTML finalizado e estão sendo
 *      passados por um escaper, o que quebra a página em vez de protegê-la.
 *   4. Forma de saída ilegível: tokens de saída que nenhuma conferência soube ler,
 *      reprovados sem consultar as outras três, porque um valor que o gate não leu
 *      não é um valor que o gate aprovou.
 *
 * As quatro reprovam quando aparecem e não estão no baseline.
 *
 * A varredura, as regras e o texto do relatório estão em tools/ViewEscaping/; este
 * arquivo é a entrada de linha de comando: argumentos, a guarda de cobertura, o
 * baseline e a impressão.
 *
 * Uso:
 *   php tools/check_view_escaping.php                  # confere (sai 1 em achado novo)
 *   php tools/check_view_escaping.php --update-baseline
 *   php tools/check_view_escaping.php --no-baseline    # reporta tudo
 *
 * Sai 1 em achado novo, 2 quando não leu nenhuma view, e 0 quando está em ordem.
 *
 * As entradas do baseline são "caminho/relativo.php|trecho", mais o prefixo da
 * conferência nas quatro entradas que vêm de arquivo inteiro, para sobreviver a
 * mudanças de linha que não têm relação. Cada entrada precisa ser revisada: uma
 * delas é a decisão de deixar um valor sem escape.
 */

use Tools\ViewEscaping\Report;
use Tools\ViewEscaping\ViewScanner;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script can only be run from the command line.\n");
}

require_once dirname(__DIR__) . '/application/vendor/autoload.php';

$root = dirname(__DIR__);
$baselineFile = __DIR__ . '/xss-baseline.txt';
$viewsDirectory = $root . '/application/views';

$options = getopt('', ['update-baseline', 'no-baseline']);
$updateBaseline = array_key_exists('update-baseline', $options);
$ignoreBaseline = array_key_exists('no-baseline', $options);

$scan = ViewScanner::scan($viewsDirectory, $root);
$findings = $scan['findings'];
$categories = $scan['categories'];

// A guarda vem ANTES de `--update-baseline`, e essa ordem é o ponto inteiro dela.
//
// Sem views lidas, `$findings` fica vazio, e o caminho de escrita que vem abaixo
// transforma isso em um arquivo de baseline VAZIO, sem erro e com código 0: as
// 234 entradas somem e o gate passa a aprovar tudo, porque nada mais está
// registrado. Uma pasta de views renomeada, um volume de Docker que não montou, um
// erro de digitação no caminho, qualquer um dos três produz um gate verde com o
// registro de decisões apagado. Por isso a checagem é de contagem de arquivos, e
// não de "há achados": "não achei nada" e "não havia nada" são respostas
// diferentes, e só a primeira é um erro.
//
// O código sai 2, e não 1, para que "o gate não conseguiu rodar" não se confunda
// com "o gate achou algo novo": o 1 pede correção em uma view, o 2 pede olhar o
// ambiente. Quem só trata de 1 lê o 2 como verde.
if ($scan['files'] === 0) {
    fwrite(
        STDERR,
        "Nenhuma view lida em {$viewsDirectory}.\n"
        . "O diretório não existe, está vazio, ou não pôde ser lido, e por isso o gate não\n"
        . "conferiu nada. Nada foi escrito no baseline. Corrija o caminho e rode de novo.\n"
    );

    exit(2);
}

if ($updateBaseline) {
    $previous = is_file($baselineFile) ? (string) file_get_contents($baselineFile) : null;
    $lines = [Report::baselineHeader($findings, $categories, $previous)];

    foreach ($findings as $key => $finding) {
        $lines[] = '# ' . $finding['snippet'] . PHP_EOL . $key;
    }

    file_put_contents($baselineFile, implode(PHP_EOL, $lines) . PHP_EOL);
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

echo 'Views scanned      : ', $scan['files'], "\n";
echo 'Findings           : ', count($findings), "\n";
echo 'Baseline entries   : ', count($baseline), "\n";
echo 'New (unsuppressed) : ', count($new), "\n";
echo 'Stale baseline     : ', count($stale), "\n";

foreach (Report::sections($new, $categories) as $prefix => $group) {
    [$title, $advice] = Report::section($prefix);

    echo "\n", $title, "\n";

    foreach ($group as $key => $finding) {
        echo '  ', $key, ':', $finding['line'], "\n      raw: ", $finding['snippet'], "\n";
    }

    echo "\n", $advice, "\n";
}

if ($stale) {
    echo "\nBaseline entries that no longer match (safe to prune):\n";

    foreach (array_keys($stale) as $key) {
        echo '  ', $key, "\n";
    }
}

exit($new ? 1 : 0);
