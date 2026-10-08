<?php

/**
 * Acusa saída sem escape nas views.
 *
 * Procura `<?= ... ?>`, `echo` e `print` que imprimem uma variável sem passar
 * por uma das funções de escape. Variável é qualquer `$algo`, inclusive
 * `$this->...`: o que vem do banco, do POST ou da sessão chega na view assim.
 *
 * As views antigas têm centenas de ocorrências, que estão registradas em
 * escape-baseline.json como contagem por arquivo. O CI só falha quando um
 * arquivo passa da contagem do baseline, ou seja, quando entra saída sem escape
 * nova. Corrigir ocorrências antigas nunca quebra o build.
 *
 * Uso:
 *   php scripts/check-escape.php                    verifica contra o baseline
 *   php scripts/check-escape.php --update-baseline  regrava o baseline
 *   php scripts/check-escape.php --list             lista todas as ocorrências
 */

/**
 * Funções cujo resultado é seguro para imprimir em HTML, não importa o que
 * recebam. Uma variável dentro dos parênteses delas não é acusada.
 *
 * Só entra aqui o que escapa ou o que só pode devolver número/data. site_url(),
 * base_url() e afins ficam de fora: eles devolvem a string recebida sem escapar.
 */
const ESCAPE_FUNCOES_SEGURAS = [
    'e',
    'html_escape',
    'htmlspecialchars',
    'printsafehtml',
    'intval',
    'floatval',
    'count',
    'number_format',
    'date',
    // Monta os atributos do <html> com htmlspecialchars (tema_helper.php).
    'temaatributoshtml',
];

/**
 * Casts que transformam o operando seguinte em número ou booleano.
 */
const ESCAPE_CASTS_SEGUROS = [T_INT_CAST, T_DOUBLE_CAST, T_BOOL_CAST];

/**
 * Devolve as ocorrências de saída sem escape em um trecho de código PHP.
 *
 * @return list<array{linha: int, trecho: string}>
 */
function escapeOcorrencias(string $codigo): array
{
    $tokens = token_get_all($codigo);
    $ocorrencias = [];
    $total = count($tokens);

    for ($i = 0; $i < $total; $i++) {
        $token = $tokens[$i];

        if (! is_array($token) || ! in_array($token[0], [T_OPEN_TAG_WITH_ECHO, T_ECHO, T_PRINT], true)) {
            continue;
        }

        // A expressão vai até o ponto e vírgula ou a tag de fechamento do PHP.
        $expressao = [];
        $profundidade = 0;
        for ($j = $i + 1; $j < $total; $j++) {
            $t = $tokens[$j];

            if (is_array($t) && $t[0] === T_CLOSE_TAG) {
                break;
            }
            if ($t === ';' && $profundidade === 0) {
                break;
            }
            if (in_array($t, ['(', '[', '{'], true) || (is_array($t) && in_array($t[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                $profundidade++;
            } elseif (in_array($t, [')', ']', '}'], true)) {
                $profundidade--;
            }

            $expressao[] = $t;
        }

        if (escapeExpressaoInsegura($expressao)) {
            $ocorrencias[] = [
                'linha' => $token[2],
                'trecho' => escapeTrecho($token, $expressao),
            ];
        }

        $i = $j;
    }

    return $ocorrencias;
}

/**
 * Diz se a expressão impressa contém uma variável fora de uma função segura.
 *
 * @param list<array{0: int, 1: string, 2: int}|string> $tokens
 */
function escapeExpressaoInsegura(array $tokens): bool
{
    // Num ternário de topo, a condição não é impressa: `$ativo ? 'checked' : ''`
    // só imprime os ramos. Então a análise começa depois do primeiro ? de
    // profundidade zero. O ?? é outro token (T_COALESCE) e não entra aqui.
    $profundidade = 0;
    foreach ($tokens as $k => $t) {
        if (in_array($t, ['(', '['], true)) {
            $profundidade++;
        } elseif (in_array($t, [')', ']'], true)) {
            $profundidade--;
        } elseif ($t === '?' && $profundidade === 0) {
            $tokens = array_slice($tokens, $k + 1);

            break;
        }
    }

    // Pilha de parênteses abertos: true quando o parêntese é de uma função
    // segura. Uma variável é segura se houver pelo menos um true na pilha.
    $pilha = [];
    $proximaSegura = false;
    $ultimoSignificativo = null;

    foreach ($tokens as $t) {
        if (is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        if ($t === '(') {
            $nome = is_array($ultimoSignificativo) && in_array($ultimoSignificativo[0], [T_STRING, T_NAME_FULLY_QUALIFIED], true)
                ? strtolower(ltrim($ultimoSignificativo[1], '\\'))
                : null;
            $pilha[] = $nome !== null && in_array($nome, ESCAPE_FUNCOES_SEGURAS, true);
        } elseif ($t === ')') {
            array_pop($pilha);
        } elseif (is_array($t) && in_array($t[0], ESCAPE_CASTS_SEGUROS, true)) {
            $proximaSegura = true;
        } elseif (is_array($t) && $t[0] === T_VARIABLE) {
            if (! $proximaSegura && ! in_array(true, $pilha, true)) {
                return true;
            }
            $proximaSegura = false;
        }

        $ultimoSignificativo = $t;
    }

    return false;
}

/**
 * Reconstrói o comando em uma linha, para a mensagem de erro.
 *
 * @param array{0: int, 1: string, 2: int} $abertura
 * @param list<array{0: int, 1: string, 2: int}|string> $expressao
 */
function escapeTrecho(array $abertura, array $expressao): string
{
    $texto = $abertura[1];
    foreach ($expressao as $t) {
        $texto .= is_array($t) ? $t[1] : $t;
    }

    $texto = trim((string) preg_replace('/\s+/', ' ', $texto));

    return mb_strlen($texto) > 120 ? mb_substr($texto, 0, 117) . '...' : $texto;
}

/**
 * Varre as views e devolve as ocorrências agrupadas por arquivo, com caminho
 * relativo à raiz do projeto e em ordem alfabética.
 *
 * @return array<string, list<array{linha: int, trecho: string}>>
 */
function escapeVarrerViews(string $raiz, string $pastaViews = 'application/views'): array
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

        $ocorrencias = escapeOcorrencias((string) file_get_contents($arquivo->getPathname()));
        if ($ocorrencias !== []) {
            $relativo = substr($arquivo->getPathname(), strlen(rtrim($raiz, '/')) + 1);
            $resultado[$relativo] = $ocorrencias;
        }
    }

    ksort($resultado);

    return $resultado;
}

/**
 * Compara o que foi encontrado com o baseline e devolve só os arquivos que
 * passaram da contagem permitida.
 *
 * @param array<string, list<array{linha: int, trecho: string}>> $encontrado
 * @param array<string, int> $baseline
 * @return array<string, array{permitido: int, ocorrencias: list<array{linha: int, trecho: string}>}>
 */
function escapeExcedentes(array $encontrado, array $baseline): array
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
    $arquivoBaseline = $raiz . '/escape-baseline.json';
    $encontrado = escapeVarrerViews($raiz);

    if (in_array('--list', $argv, true)) {
        foreach ($encontrado as $arquivo => $ocorrencias) {
            foreach ($ocorrencias as $o) {
                echo "{$arquivo}:{$o['linha']}: {$o['trecho']}\n";
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
        echo 'Baseline regravado: ' . array_sum($contagem) . ' ocorrências em ' . count($contagem) . " arquivos.\n";
        exit(0);
    }

    $baseline = is_file($arquivoBaseline)
        ? (array) json_decode((string) file_get_contents($arquivoBaseline), true)
        : [];

    $excedentes = escapeExcedentes($encontrado, $baseline);

    if ($excedentes === []) {
        $total = array_sum(array_map('count', $encontrado));
        $permitido = array_sum($baseline);
        echo "Nenhuma saída sem escape nova ({$total} ocorrências antigas no baseline).\n";
        if ($total < $permitido) {
            echo 'O baseline permite ' . ($permitido - $total) . " ocorrências a mais do que existem. Rode com --update-baseline para baixar a contagem.\n";
        }
        exit(0);
    }

    foreach ($excedentes as $arquivo => $info) {
        $novas = count($info['ocorrencias']) - $info['permitido'];
        echo "::error file={$arquivo}::{$novas} saída(s) sem escape nova(s) em {$arquivo}\n";
        foreach ($info['ocorrencias'] as $o) {
            echo "  {$arquivo}:{$o['linha']}: {$o['trecho']}\n";
        }
    }

    echo "\nUse e() para texto e printSafeHtml() para HTML confiável. Se a saída for segura de fato\n";
    echo "(um número, por exemplo), use um cast (int) ou rode com --update-baseline e explique no PR.\n";
    exit(1);
}
