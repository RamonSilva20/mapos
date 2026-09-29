<?php

namespace Tools\ViewEscaping;

/**
 * As três conferências, uma por arquivo, todas com a mesma assinatura de entrada.
 *
 * Cada uma devolve o trecho que a fez falhar, ou null. Nenhuma delas imprime,
 * nenhuma delas decide o código de saída, nenhuma delas conhece o baseline: quem
 * faz isso é o ViewScanner. O motivo é que as três são independentes por
 * construção — uma pode reprovar uma view que as outras duas aprovam — e o que
 * o relatório precisa é do trecho e da linha, não de uma opinião sobre a view.
 *
 * A política vem sempre por parâmetro, de EscapingPolicy, e é consultada por
 * parâmetro em todas as três: `unescaped()` entrega a mesma política a cada regra,
 * e `preRenderedEscaped()` e `jsonParseEscaper()` leem dela as listas de que
 * precisam. O que este arquivo não faz é escrever um nome de escaper, de helper ou
 * de função de JSON do teclado — a versão anterior de `jsonParseEscaper()` tinha
 * `esc_json` duas vezes dentro do regex e `json_encode(` numa segunda allowlist, e
 * acrescentar um décimo escaper não faria aquela conferência cobrir, em silêncio.
 *
 * `unescaped()` também não tem a ordem das decisões: ela percorre
 * `EscapingPolicy::rules()`, e cada regra decide se se aplica, aprova, reprova ou
 * desce para as partes. A classe tem a forma do gate, e não o julgamento.
 */
final class EscapingChecks
{
    /**
     * A expressão sai da view sem escaper, ou null se alguma regra a aprovou.
     *
     * A lista de regras é um laço, e a lista mora em `EscapingPolicy::RULES`. Antes
     * eram sete decisões sequenciais escritas aqui, e nenhuma classe era dona da
     * ordem — que é a decisão mais sensível do gate, porque trocar duas regras
     * consecutivas muda o resultado sem erro nenhum.
     *
     * Quatro saídas do laço, e só quatro:
     *
     *   - `null`: alguma regra aprovou, e a expressão é segura;
     *   - `descend`: as partes voltam ao começo da lista, cada uma por sua conta;
     *   - um veredito de reprovação, cujo trecho é o que o relatório mostra;
     *   - a expressão inteira, quando a lista inteira não decidiu.
     *
     * A última é a mais importante e não deveria existir: a última regra é
     * `unreadable`, que reprova sem condição, então sair do laço por cima dela é
     * impossível. Ela está aqui para o caso de alguém remover `unreadable` da lista
     * sem querer, e reprova em vez de aprovar — o erro de leitura de uma regra vira
     * um achado, e não uma linha em silêncio.
     */
    public static function unescaped(string $expr, EscapingPolicy $policy): ?string
    {
        $expr = PhpExpression::normalizeExpression($expr);

        if ($expr === '') {
            return null;
        }

        foreach ($policy->rules() as $rule) {
            $verdict = $rule->judge($expr, $policy);

            if ($verdict === null) {
                continue;
            }

            if ($verdict->isReport()) {
                return $verdict->snippet;
            }

            if ($verdict->isApproval()) {
                return null;
            }

            return self::mapHits($verdict->parts, $policy);
        }

        return $expr;
    }

    /**
     * Os trechos que reprovaram dentro de uma expressão composta, ou null.
     *
     * Três dos ramos de `unescaped()` — ternário, concatenação e argumentos de
     * chamada — conferiam a mesma coisa e só diferiam no nome da variável do laço.
     * A resposta é a mesma sempre: o relatório quer os trechos, separados por ' , ',
     * e nenhum deles quando todos passaram. Escrever a junção uma vez é o que impede
     * a diferença de aparecer num ramo e não nos outros.
     *
     * @param  list<string>  $parts
     */
    private static function mapHits(array $parts, EscapingPolicy $policy): ?string
    {
        $hits = [];

        foreach ($parts as $part) {
            $hit = self::unescaped($part, $policy);

            if ($hit !== null) {
                $hits[] = trim($hit);
            }
        }

        return $hits === [] ? null : implode(' , ', $hits);
    }

    /**
     * O `JSON.parse()` está sendo alimentado por um escaper que emite JSON como
     * STRING, e não o documento que ele quer ler.
     *
     * O defeito é uma propriedade do escaper, não do `JSON.parse()`: `esc_json()`
     * devolve o valor já entre aspas e escapado para dentro de um `<script>`, então
     * `JSON.parse()` recebe `'{"a":1}'` — uma string de oito caracteres — onde
     * queria o objeto. `json_encode()` devolve o documento, e por isso parsear o
     * resultado dele funciona. Os dois estão em `EscapingPolicy::JSON_ESCAPERS`, e
     * essa é a forma de a pergunta ser respondida: pelo que a função devolve, e não
     * por uma segunda lista de nomes escrita dentro do padrão.
     *
     * A forma de saída é casada por `PhpExpression::ECHO_SHAPES`, e não reescrita
     * aqui. A versão anterior deste método tinha `<?=` e `<?php echo` digitados de
     * novo dentro do regex, o que deixava de fora as outras três formas terminadas e
     * a quinta — o `echo $x;` que não abre bloco. Um check que só enxerga duas das
     * cinco formas não é um check com duas formas: é um check com cinco lacunas, e a
     * lista de nomes parecia maior do que a cobertura.
     *
     * O que o check reprova é o nome que devolve JSON sendo ESCAPER: `esc_json()`.
     * `json_encode` está na lista porque devolve JSON, e sai de graça por não ser
     * escaper — a exclusão que antes era um `str_contains` escrito à mão sobre o
     * texto capturado agora é a diferença entre dois conjuntos que a política já
     * expunha.
     */
    public static function jsonParseEscaper(string $content, EscapingPolicy $policy): ?string
    {
        if (! preg_match_all('/JSON\s*\.\s*parse\s*\(/', $content, $calls, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        foreach ($calls[0] as [$call, $offset]) {
            $argument = self::jsonParseArgument($content, $offset + strlen($call));

            if ($argument === null || ! self::isJsonEmittingEscaper($argument[0], $policy)) {
                continue;
            }

            // A chamada inteira, e não só a expressão: `esc_json($cfg)` aparece em
            // pelo menos cinco lugares da mesma view, e o que reprova é o uso dela
            // dentro daquele `JSON.parse()`. A linha sozinha não diz qual dos cinco é.
            return (string) preg_replace(
                '/\s+/',
                ' ',
                trim(substr($content, $offset, $argument[1] - $offset + 1))
            );
        }

        return null;
    }

    /**
     * O texto do primeiro argumento do `JSON.parse()` que começa em `$start`, e o
     * offset do `)` que fecha a chamada.
     *
     * Devolve null quando a chamada não fecha dentro do arquivo, e isso é um
     * reprovação silenciosa que vale nomear: um PHP sem fechamento no meio de um
     * `<script>` deixa o resto do arquivo como JavaScript, e é um caso em que a
     * varredura de expressões também não enxerga nada. A resposta honesta seria
     * reprovar, e ela está em `unreadable()` para o caminho do PHP; aqui o
     * argumento é procurado dentro de um `JSON.parse()` já encontrado, e um
     * parêntempo que não fecha significa que o arquivo está partido de um jeito que
     * nenhuma das duas varreduras consegue avaliar.
     *
     * @return array{0: string, 1: int}|null
     */
    private static function jsonParseArgument(string $content, int $start): ?array
    {
        $depth = 1;
        $quote = null;
        $escaped = false;

        for ($i = $start, $length = strlen($content); $i < $length; $i++) {
            $char = $content[$i];

            if ($quote !== null) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === $quote) {
                    $quote = null;
                }

                continue;
            }

            if ($char === '"' || $char === "'") {
                $quote = $char;
            } elseif ($char === '(') {
                $depth++;
            } elseif ($char === ')' && --$depth === 0) {
                return [substr($content, $start, $i - $start), $i];
            }
        }

        return null;
    }

    /**
     * Há, dentro deste argumento de `JSON.parse()`, um escaper cujo retorno é JSON?
     *
     * Percorre `ECHO_SHAPES` em vez de casar um formato só, e corta o ponto e vírgula
     * das formas não terminadas com o mesmo método que a varredura de expressões
     * usa. A lista de formas é uma, e a varredura de expressões é a dona dela.
     */
    private static function isJsonEmittingEscaper(string $text, EscapingPolicy $policy): bool
    {
        foreach (PhpExpression::ECHO_SHAPES as $shape) {
            if (! preg_match_all($shape['re'], $text, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            foreach ($matches[1] as $match) {
                $expression = $shape['terminated']
                    ? PhpExpression::cutAtTopLevelSemicolon($match[0])
                    : $match[0];

                $call = PhpExpression::isFunctionCall($expression);

                if ($call === null) {
                    continue;
                }

                [$ref] = $call;
                $name = PhpExpression::calleeName($ref);

                if (in_array($name, $policy->escapers, true) && $policy->returnsJson($name)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Uma forma de saída que nenhuma das formas de `PhpExpression::ECHO_SHAPES`
     * reconheceu, e que por isso nunca chegou a ser conferida.
     *
     * Esta é a única conferência do gate que reprova por motivo de NÃO SABER, e
     * não de ter visto algo errado. A diferença é o ponto: todo o resto do gate lê
     * a expressão e decide, e pode errar decidindo; aqui a leitura é que falhou, e
     * um valor que o gate não leu não é um valor que o gate aprovou.
     *
     * O token vem de `PhpExpression::OUTPUT_TOKENS`, que é deliberadamente mais
     * largo que as cinco formas, e a cobertura é a união dos intervalos que elas
     * casaram no arquivo. A escolha de reprovar sem consultar `unescaped()` é
     * deliberada e é o oposto de parecer prudente: para montar a expressão a
     * passar a `unescaped()` é justamente o passo que falhou, e reaproveitar um
     * trecho mal cortado aqui devolveria um "aprovado" que ninguém conferiu — o
     * buraco fail-open que esta função existe para fechar.
     *
     * O snippet começa no token e vai até o fim da linha, e é um pedaço contíguo do
     * conteúdo de propósito: `PhpExpression::locate()` o procura no texto para
     * deduzir a linha, e um trecho remontado (a linha inteira com o token colado no
     * fim) não está em lugar nenhum do arquivo e reportava linha 0 em todo achado.
     * Começar no token também é o que o leitor precisa: é o que não foi reconhecido.
     */
    public static function unrecognizedOutput(string $content): ?string
    {
        if (! preg_match_all(PhpExpression::OUTPUT_TOKENS, $content, $tokens, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $covered = self::coveredOutputRanges($content);

        foreach ($tokens[0] as [$token, $offset]) {
            foreach ($covered as [$start, $end]) {
                if ($offset >= $start && $offset < $end) {
                    continue 2;
                }
            }

            $end = strpos($content, "\n", $offset);

            return (string) preg_replace(
                '/\s+/',
                ' ',
                trim($end === false ? substr($content, $offset) : substr($content, $offset, $end - $offset))
            );
        }

        return null;
    }

    /**
     * Os intervalos de `PhpExpression::ECHO_SHAPES` casados no conteúdo, unidos.
     *
     * É o mesmo laço de formas que o `ViewScanner` roda, reduzido ao que a guarda
     * precisa: onde cada forma casou, e nada sobre o que casou. A versão anterior
     * media cobertura comparando o offset do token com o fim de cada match, o que
     * erra nas formas em que o token NÃO é o começo do match — `<?= $x ?>` casa
     * em `<?=` e o `echo` nem existe no texto. Por isso o intervalo é o match
     * inteiro, e não a posição do token dentro dele.
     *
     * @return list<array{0: int, 1: int}>
     */
    private static function coveredOutputRanges(string $content): array
    {
        $ranges = [];

        foreach (PhpExpression::ECHO_SHAPES as $shape) {
            if (! preg_match_all($shape['re'], $content, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            foreach ($matches[0] as [$match, $offset]) {
                $ranges[] = [$offset, $offset + strlen($match)];
            }
        }

        return $ranges;
    }

    public static function preRenderedEscaped(string $content, EscapingPolicy $policy): ?string
    {
        $re = '/\b(?:' . $policy->escaperPattern() . ')\(\s*(' . $policy->preRenderedPattern() . ')\s*\)/';

        if (! preg_match($re, $content, $m)) {
            return null;
        }

        return (string) preg_replace('/\s+/', ' ', trim($m[0]));
    }
}
