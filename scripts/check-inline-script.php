<?php

/**
 * Acusa <script> inline novo nas views.
 *
 * O padrão da v5 é JavaScript em assets/js/modules, ligado à view por
 * js_module() e alimentado por page_data() ou atributos data-*. Script inline
 * impede uma CSP sem 'unsafe-inline' (#2878).
 *
 * Conta como inline todo <script> sem src cujo type é executável. Ficam de
 * fora os blocos de dados (application/json, application/ld+json), que o
 * navegador não executa.
 *
 * As views antigas têm centenas de blocos, registrados em
 * inline-script-baseline.json como contagem por arquivo. Só falha o arquivo
 * que passar da contagem; remover script inline nunca quebra o build.
 *
 * Uso:
 *   php scripts/check-inline-script.php                    verifica contra o baseline
 *   php scripts/check-inline-script.php --update-baseline  regrava o baseline
 *   php scripts/check-inline-script.php --list             lista todas as ocorrências
 */

/**
 * Tipos de <script> que carregam dados, não código.
 */
const INLINE_SCRIPT_TIPOS_DE_DADOS = ['application/json', 'application/ld+json'];

/**
 * Devolve os <script> inline executáveis de um trecho de view.
 *
 * @return list<array{linha: int, tag: string}>
 */
function inlineScriptOcorrencias(string $codigo): array
{
    // O PHP dentro da tag pode ter ">", a começar pela própria tag de
    // fechamento do PHP. Trocar cada bloco por um marcador com o mesmo número
    // de quebras de linha mantém a numeração e deixa só o HTML para a regex.
    $html = (string) preg_replace_callback(
        '/<\?(?:php|=)?.*?(?:\?>|$)/s',
        static fn (array $m) => str_repeat("\n", substr_count($m[0], "\n")) . 'PHP',
        $codigo
    );

    preg_match_all('/<script\b([^>]*)>/i', $html, $tags, PREG_OFFSET_CAPTURE);

    $ocorrencias = [];
    foreach ($tags[0] as $i => [$tag, $posicao]) {
        $atributos = $tags[1][$i][0];

        if (preg_match('/(?<![\w-])src\s*=/i', $atributos)) {
            continue;
        }

        if (preg_match('/(?<![\w-])type\s*=\s*["\']?([^"\'\s>]+)/i', $atributos, $tipo)
            && in_array(strtolower($tipo[1]), INLINE_SCRIPT_TIPOS_DE_DADOS, true)) {
            continue;
        }

        $ocorrencias[] = [
            'linha' => substr_count($html, "\n", 0, $posicao) + 1,
            'tag' => trim((string) preg_replace('/\s+/', ' ', $tag)),
        ];
    }

    return $ocorrencias;
}

/**
 * Varre as views e devolve as ocorrências por arquivo, com caminho relativo à
 * raiz do projeto e em ordem alfabética.
 *
 * @return array<string, list<array{linha: int, tag: string}>>
 */
function inlineScriptVarrerViews(string $raiz, string $pastaViews = 'application/views'): array
{
    $resultado = [];
    $base = rtrim($raiz, '/') . '/' . $pastaViews;

    $arquivos = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($arquivos as $arquivo) {
        if ($arquivo->getExtension() !== 'php') {
            continue;
        }

        $ocorrencias = inlineScriptOcorrencias((string) file_get_contents($arquivo->getPathname()));
        if ($ocorrencias !== []) {
            $relativo = substr($arquivo->getPathname(), strlen(rtrim($raiz, '/')) + 1);
            $resultado[$relativo] = $ocorrencias;
        }
    }

    ksort($resultado);

    return $resultado;
}

/**
 * Arquivos que passaram da contagem permitida pelo baseline.
 *
 * @param array<string, list<array{linha: int, tag: string}>> $encontrado
 * @param array<string, int> $baseline
 * @return array<string, array{permitido: int, ocorrencias: list<array{linha: int, tag: string}>}>
 */
function inlineScriptExcedentes(array $encontrado, array $baseline): array
{
    $excedentes = [];

    foreach ($encontrado as $arquivo => $ocorrencias) {
        $permitido = $baseline[$arquivo] ?? 0;
        if (count($ocorrencias) > $permitido) {
            $excedentes[$arquivo] = ['permitido' => $permitido, 'ocorrencias' => $ocorrencias];
        }
    }

    return $excedentes;
}

if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    $raiz = dirname(__DIR__);
    $arquivoBaseline = $raiz . '/inline-script-baseline.json';
    $encontrado = inlineScriptVarrerViews($raiz);

    if (in_array('--list', $argv, true)) {
        foreach ($encontrado as $arquivo => $ocorrencias) {
            foreach ($ocorrencias as $o) {
                echo "{$arquivo}:{$o['linha']}: {$o['tag']}\n";
            }
        }
        exit(0);
    }

    if (in_array('--update-baseline', $argv, true)) {
        $contagem = array_map('count', $encontrado);
        file_put_contents(
            $arquivoBaseline,
            json_encode($contagem, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
        );
        echo 'Baseline regravado: ' . array_sum($contagem) . ' blocos em ' . count($contagem) . " arquivos.\n";
        exit(0);
    }

    $baseline = is_file($arquivoBaseline)
        ? (array) json_decode((string) file_get_contents($arquivoBaseline), true)
        : [];

    $excedentes = inlineScriptExcedentes($encontrado, $baseline);

    if ($excedentes === []) {
        $total = array_sum(array_map('count', $encontrado));
        $permitido = array_sum($baseline);
        echo "Nenhum <script> inline novo ({$total} blocos antigos no baseline).\n";
        if ($total < $permitido) {
            echo 'O baseline permite ' . ($permitido - $total) . " blocos a mais do que existem. Rode com --update-baseline para baixar a contagem.\n";
        }
        exit(0);
    }

    foreach ($excedentes as $arquivo => $info) {
        $novos = count($info['ocorrencias']) - $info['permitido'];
        echo "::error file={$arquivo}::{$novos} <script> inline novo(s) em {$arquivo}\n";
        foreach ($info['ocorrencias'] as $o) {
            echo "  {$arquivo}:{$o['linha']}: {$o['tag']}\n";
        }
    }

    echo "\nMova o código para assets/js/modules/<pagina>.js, ligue com js_module() e passe os dados\n";
    echo "por page_data() ou atributos data-*. Ver \"JavaScript nas views\" no CONTRIBUTING.md.\n";
    exit(1);
}
