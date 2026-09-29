<?php

namespace Tools\ViewEscaping;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * A varredura: percorre as views e devolve o mapa de achados.
 *
 * Sai daqui um array associativo com a chave de baseline como chave e o trecho e
 * a linha como valor, mais o número de arquivos lidos e o agrupamento dos achados
 * por conferência. Quem decide o que fazer com eles — comparar com o baseline,
 * imprimir, sair com 1 — é o script de entrada, e não esta classe, para que a
 * varredura possa ser rodada por um teste sem stdout nem arquivo.
 *
 * A contagem de arquivos vem da própria varredura, e não de um
 * RecursiveDirectoryIterator criado só para contar. A versão anterior abria um
 * segundo percurso da árvore das views atrás do `Views scanned: N` do relatório:
 * duas travessias do mesmo diretório para um número que a primeira já sabia.
 * E o script de entrada precisa dela para recusar uma varredura vazia, que é a
 * única forma de o gate passar sem ter conferido nada.
 */
final class ViewScanner
{
    /**
     * Varre um diretório de views e devolve os achados, já semeados por chave.
     *
     * A chave é "caminho relativo|trecho normalizado" para as três conferências
     * normais e "caminho relativo|prefixo trecho" para as de arquivo inteiro, e ela
     * é semeada no próprio mapa para que dois trechos iguais na mesma view contem uma
     * vez. Sem isso o relatório mostra a mesma linha duas vezes, uma por forma de
     * saída que a casou — o que é o caso comum, e não uma raridade.
     *
     * A política é parâmetro, e não um `EscapingPolicy::default()` montado aqui
     * dentro, porque este é o único ponto da cadeia que decide o que é varrido e o
     * que é achado. Com a costura fechada, a única prova de que ela existia era um
     * teste que montava política própria para o `ViewScanner` não a usar — o
     * que prova que a costura existe, e não que ela funciona. Com ela aberta, um
     * teste injeta a política que quiser e vê o resultado, e a varredura deixa de ser
     * a única parte do gate que só se exercita por efeito colateral.
     *
     * `categories` acompanha os achados porque o prefixo fica DENTRO da chave, e
     * quem sabe qual conferência produziu qual chave é este laço — não o relatório.
     * Sem o mapa, o script de entrada só poderia regroupar os achados procurando o
     * prefixo no texto da chave com `str_contains`, que é a leitura da convenção que
     * o mapa torna desnecessária: a chave continua uma string para o arquivo em
     * disco, e a categoria viaja como dado. Nenhuma das 234 entradas do baseline
     * muda de forma por causa disto.
     *
     * @return array{findings: array<string, array{snippet: string, line: int}>, files: int, categories: array<string, list<string>>}
     */
    public static function scan(string $directory, string $root, ?EscapingPolicy $policy = null): array
    {
        $policy ??= EscapingPolicy::default();

        $findings = [];
        $categories = [];
        $files = 0;

        if (! is_dir($directory)) {
            return ['findings' => $findings, 'files' => 0, 'categories' => $categories];
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $files++;
            $path = $file->getPathname();
            $relative = str_replace($root . '/', '', $path);
            $content = (string) file_get_contents($path);

            $findings += self::scanFile($content, $relative, $policy, $categories);
        }

        ksort($findings);

        return ['findings' => $findings, 'files' => $files, 'categories' => $categories];
    }

    /**
     * As três conferências de um arquivo, unidas pelo mesmo mapa de chave.
     *
     * `$categories` é preenchido por referência e nunca é lido aqui: a conferência
     * que reprovou é ela mesma quem sabe em que seção o achado entra, e a chave
     * completa é semeada nos dois mapas no mesmo instante, para que nenhum dos dois
     * possa citar uma entrada que o outro não tem.
     *
     * @param  array<string, list<string>>  $categories
     * @return array<string, array{snippet: string, line: int}>
     */
    private static function scanFile(string $content, string $relative, EscapingPolicy $policy, array &$categories): array
    {
        $findings = [];
        $seen = [];

        $categories[''] ??= [];

        foreach (PhpExpression::ECHO_SHAPES as $shape) {
            if (! preg_match_all($shape['re'], $content, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            foreach ($matches[1] as $match) {
                $expression = $shape['terminated']
                    ? PhpExpression::cutAtTopLevelSemicolon($match[0])
                    : $match[0];

                $hit = EscapingChecks::unescaped($expression, $policy);

                if ($hit === null) {
                    continue;
                }

                // O trecho é normalizado só para ser legível, e a chave do
                // baseline usa essa forma de propósito: uma entrada sobrevive a
                // uma mudança que só mexe na indentação. A linha NÃO sai do
                // texto normalizado — o offset do regex (`$match[1]`) já é a
                // posição exata da expressão, e procurar o texto de novo acha a
                // PRIMEIRA ocorrência da mesma expressão no arquivo, que
                // costuma ser outra linha.
                $snippet = (string) preg_replace('/\s+/', ' ', trim($hit));
                $key = $relative . '|' . $snippet;

                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $categories[''][] = $key;
                $findings[$key] = [
                    'snippet' => $snippet,
                    'line' => substr_count($content, "\n", 0, $match[1]) + 1,
                ];
            }
        }

        // As três conferências de arquivo inteiro não têm forma de saída para
        // varrer: elas procuram um padrão no conteúdo todo, e a linha do achado
        // vem do offset do trecho normalizado, não de um grupo de captura.
        foreach ([
            EscapingPolicy::JSON_PARSE_PREFIX => EscapingChecks::jsonParseEscaper($content, $policy),
            EscapingPolicy::PRE_RENDERED_PREFIX => EscapingChecks::preRenderedEscaped($content, $policy),
            EscapingPolicy::UNRECOGNIZED_OUTPUT_PREFIX => EscapingChecks::unrecognizedOutput($content),
        ] as $prefix => $snippet) {
            if ($snippet === null) {
                continue;
            }

            [, $line] = PhpExpression::locate($content, $snippet);
            $key = $relative . '|' . $prefix . $snippet;
            $categories[$prefix][] = $key;
            $findings[$key] = [
                'snippet' => $snippet,
                'line' => $line,
            ];
        }

        return $findings;
    }
}
