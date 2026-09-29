<?php

namespace Tools\ViewEscaping;

/**
 * A gramática mínima de expressão PHP, escrita à mão.
 *
 * O gate precisa decidir se um valor que chega à página foi escapado, e para isso
 * tem que olhar para dentro de `esc($a . $b)`, `json_encode($x)`,
 * `strtoupper($y)` e `date('d/m', $quando)`. O token_get_all do PHP resolveria
 * isso com precisão, mas a decisão que importa aqui é do gate e não do PHP: a
 * forma de saída pode ser capturada por regex, e o que está dentro dela chega
 * como texto.
 *
 * São funções puras sobre string, sem IO e sem política. A política — o que é
 * seguro, o que é markup pronto — está em EscapingPolicy, e as três conferências
 * em EscapingChecks. Uma função aqui que precisasse ler arquivo, ou saber o que
 * é seguro, seria sinal de que o limite está no lugar errado.
 *
 * A exceção é `ECHO_SHAPES`, que vive aqui e não em EscapingPolicy: quais formas de
 * saída do PHP existem, e se a captura de cada uma já vem fechada pela tag de fim,
 * são fatos sobre a linguagem. O que é seguro dentro delas é julgamento, e quem
 * julga continua sendo a política. A lista estava na política, e lá dizia: um gate
 * que varre a quinta forma de saída é um gate configurável, e a configurable de
 * `echoShapes` nunca foi usada por ninguém — o teste que montava política
 * customized passava uma lista vazia, num campo que decide o que é varrido.
 *
 * Aviso para quem editar os comentários deste arquivo: o fechamento de tag do PHP
 * não pode ser escrito aqui, nem em forma de parênteses. Num comentário de linha
 * ele encerra o modo PHP e o resto do arquivo vira saída, o que quebra a
 * execução sem erro visível — o arquivo parece um arquivo de texto. Descreva a
 * tag por extenso.
 */
final class PhpExpression
{
    /**
     * As formas de saída do PHP que o gate varre, com a forma do regex.
     *
     * `terminated` diz se a captura já vem fechada pela tag de fim. Quando vem, o
     * `;` que o PHP interpretaria como separador de statements pode estar ali só
     * por construção, e a expressão precisa ser cortada antes de ser analisada;
     * quando a forma é um statement solto, o `;` já é parte do match, e cortar de
     * novo comeria o operando seguinte.
     *
     * As quatro formas terminadas ficam separadas em vez de virar uma alternância
     * só porque a unificação muda a captura: `<?php echo $x ?>` absorveria o
     * `php echo` no grupo, e `<?= esc($x) ?>` perderia o parêntese final, que é o
     * que fecha a chamada. Medido, não suposto.
     *
     * A quinta é o `echo ...;` que NÃO abre o bloco — o caso
     * `<?php if (...) { echo $x; }`, o mais comum nos templates do CI3. Ela
     * aceita QUALQUER expressão terminada em `;`, e não só a de valor único:
     * `echo '<td>' . $r->x . '</td>';` é a forma mais frequente de todo o
     * projeto, e restringi-la ao valor que começa em `$` deixava 290 saídas de
     * banco de dados sem escaper invisíveis para o gate. A nota anterior dizia
     * que essas eram "quase todas falsas"; medindo, são reais — `esc()` na view
     * é a correção, e ela é trabalho à parte do gate (ver `tools/xss-baseline.txt`).
     *
     * A forma é reluctant de propósito: a captura termina no `;` de nível 0, e o
     * corpo reconhece literais e até dois níveis de parêntese para que um `;` de
     * CSS dentro de `style="..."` não corte a expressão no meio. O
     * `lookahead` exige que o argumento comece como um valor (`$`, `'`, `"`,
     * dígito, `\`, `(`, chamada de função ou `self::`), o que impede que a
     * palavra `print` do `@media print` do CSS e um `echo` de comentário
     * virem statement. O que ainda escapar é o que `OUTPUT_TOKENS` + a guarda
     * fail-closed reportam; um regex não é um parser e não faz o papel dele.
     *
     * A sexta é o `echo ... ?>` que fecha o bloco sem `;` — o `echo
     * number_format($r->valor, 2, ',', '.') ?>` seguido de `</td>` que fecha
     * linha de tabela, um padrão dos templates de impressão. Sem ela, o `?>` era
     * uma saída sem forma de saída, e a guarda a reportava. Usa o MESMO
     * `VALUE_START` da quinta: sem ele, o `@media print {` do CSS casava aqui e
     * entrava na fila como se fosse um valor, o que é o defeito que o lookahead
     * existe para impedir.
     *
     * @var list<array{re: string, terminated: bool}>
     */
    public const ECHO_SHAPES = [
        ['re' => '/<\?=\s*(.+?)\s*\?>/s', 'terminated' => true],
        ['re' => '/<\?php\s+echo\s+(.+?)\s*\?>/s', 'terminated' => true],
        ['re' => '/<\?php\s+print\(\s*(.+?)\s*\)\s*;?\s*\?>/s', 'terminated' => false],
        ['re' => '/<\?php\s+print\s+(.+?)\s*\?>/s', 'terminated' => true],
        ['re' => '/\b(?:echo|print)\s+' . self::VALUE_START . '((?:[^;\x27\x22()\n]|\x27(?:[^\x27\\\\]|\\\\.)*\x27|\x22(?:[^\x22\\\\]|\\\\.)*\x22|\((?:(?:[^()\x27\x22]|\x27(?:[^\x27\\\\]|\\\\.)*\x27|\x22(?:[^\x22\\\\]|\\\\.)*\x22)|\((?:[^()\x27\x22]|\x27(?:[^\x27\\\\]|\\\\.)*\x27|\x22(?:[^\x22\\\\]|\\\\.)*\x22)*\))*\))+)\s*;/', 'terminated' => false],
        ['re' => '/\b(?:echo|print)\s+' . self::VALUE_START . '(.+?)\s*\?>/s', 'terminated' => true],
    ];

    /**
     * O `lookahead` que exige que o argumento de um `echo`/`print` comece como um
     * valor: `$`, `'`, `"`, dígito, `\`, `(`, chamada de função ou `self::`.
     *
     * É a diferença entre "esta palavra `print` abre um statement" e "a palavra
     * `print` aparece num lugar que não é PHP": `@media print {` no CSS de
     * impressão, `window.print()` no JavaScript e um `echo` comentado passam as
     * três. Sem ele, a forma pega markup e joga na fila como se fosse valor, e o
     * gate perde a credibilidade que a guarda fail-closed existe para dar.
     *
     * Fica num const só, e as duas formas o usam, porque as duas precisam da
     * mesma resposta e duas cópias divergem na primeira edição de uma delas.
     */
    private const VALUE_START = '(?=[($\x27\x22\d\x5C]|\w+\s*\(|\w+::)';

    /**
     * Todo token que pode produzir saída, deliberadamente mais largo que
     * `ECHO_SHAPES` e sem qualquer noção de parêntese, literal ou statement.
     *
     * Serve a um único propósito: a guarda fail-closed. Um token aqui que nenhuma
     * forma de `ECHO_SHAPES` cobriu é uma saída que o gate não consegue julgar,
     * e o gate não pode passar por cima dela em silêncio. A guarda é quem
     * transforma isso em achado (`EscapingChecks::unrecognizedOutput()`).
     *
     * É largo de propósito, então produz falsos positivos de vez: o `echo` de um
     * comentário, um `<?php` de string, o `print` do `@media print`. Cada um
     * deles é um achado que alguém precisa decidir, e é por isso que o arquivo de
     * baseline existe. Estreitar isto para "não gritar" é trocar uma cobertura
     * comprovável por uma ausência de evidência.
     */
    public const OUTPUT_TOKENS = '/<\?=|\\b(?:echo|print)\\s+(?=\\S)|\\bprint\\s*[(]/';

    /**
     * Percorre a expressão, com o estado de aninhamento já resolvido, e devolve o estado final.
     *
     * É a única resposta da classe para "o que é aninhamento". As quatro varreduras que
     * existiam antes repetiam o mesmo par de inicialização e a mesma aritmética de
     * profundidade, e elas já discordavam: `normalizeExpression()` contava `(` e `)`
     * mas não `[` e `]`, enquanto as outras três contavam os dois. Não foi possível
     * montar entrada onde isso mudasse o resultado, o que faz dela divergência
     * latente e não bug — e divergência latente em um parser é a pior hora de
     * descobrir, porque ela sobrevive a uma bateria de testes que não tem o caso.
     *
     * O callback recebe cada caractere UMA vez, já com o índice, a profundidade e a
     * aspa aberta no momento em que ele aparece, e devolve `false` para parar. Os
     * caracteres escapados por `\` dentro de uma aspa chegam individualmente, e
     * quem precisa de texto precisa dos dois: é o que mantém `"a\".b"` do outro lado
     * de uma concatenação.
     *
     * Três estados chegam ao callback, e a distinção entre eles é o contrato inteiro:
     *
     *   - a aspa que ABRE aparece com `$quote` já igual a ela, o conteúdo e a aspa
     *     que FECHA aparecem com `$quote` igual a elas, e o que está entre elas
     *     aparece com `$quote` igual à que está aberta. Quem reconstrói o texto
     *     acumulado não distingue os três e concatena tudo; quem decide se o
     *     caractere é estrutura sabe que nenhum dos três é.
     *   - a profundidade é a de DEPOIS do caractere, então um `(` chega valendo 1 e
     *     o `)` que o fecha chega valendo 0. A aritmética fica em um lugar só.
     *   - `$quote` nunca é null e a profundidade ser 0 nunca significa "fora de
     *     aspa": uma string aberta no fim da expressão é `$quote` não nulo com
     *     profundidade 0, e por isso o estado final é devolvido em vez de inferido.
     *
     * @param  callable(string, int, int, ?string): bool  $visit
     * @return array{quote: ?string, depth: int}
     */
    private static function walk(string $expr, callable $visit): array
    {
        $quote = null;
        $depth = 0;

        for ($i = 0, $n = strlen($expr); $i < $n; $i++) {
            $ch = $expr[$i];

            if ($quote !== null) {
                if ($visit($ch, $i, $depth, $quote) === false) {
                    return ['quote' => $quote, 'depth' => $depth];
                }

                // O `\` come o caractere seguinte, que não é estrutura e não fecha
                // aspa nenhuma. Ele chega ao callback à parte porque quem reconstrói
                // o texto precisa dele: `splitOnTopLevel()` e `splitTernary()`
                // remontam a expressão caractere a caractere, e sem este segundo
                // `$visit` elas perdiam o caractere escapado no meio.
                //
                // Cada `$visit` tem o seu próprio retorno. Compor os dois com `||`
                // faz o primeiro `false` ser engolido pelo `true` do segundo, e a
                // parada que o callback pediu não acontece — o que é o contrato
                // inteiro deste método.
                if ($ch === '\\') {
                    $i++;

                    if ($visit($expr[$i] ?? '', $i, $depth, $quote) === false) {
                        return ['quote' => $quote, 'depth' => $depth];
                    }
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
            }

            if ($visit($ch, $i, $depth, $quote) === false) {
                return ['quote' => $quote, 'depth' => $depth];
            }
        }

        return ['quote' => $quote, 'depth' => $depth];
    }

    public static function cutAtTopLevelSemicolon(string $expr): string
    {
        $cut = null;

        self::walk($expr, function (string $ch, int $i, int $depth, ?string $quote) use (&$cut): bool {
            if ($quote === null && $ch === ';' && $depth === 0) {
                $cut = $i;

                return false;
            }

            return true;
        });

        return $cut === null ? $expr : substr($expr, 0, $cut);
    }

    public static function isBareValue(string $e): bool
    {
        return (bool) preg_match('/^\$[A-Za-z_][A-Za-z0-9_]*(\s*->\s*[A-Za-z_][A-Za-z0-9_]*|\s*\[[^\[\]]*\])*$/', trim($e));
    }

    /**
     * A expressão partida nos separadores de nível superior, ou null.
     *
     * Uma varredura só para as duas coisas que partem: a concatenação e os
     * argumentos de uma chamada. Elas eram dois laços quase idênticos, e a diferença
     * entre eles era o conjunto de separadores — a mesma varredura, com `.` a mais.
     *
     * `null` quer dizer duas coisas ao mesmo tempo, e as duas interessam a quem
     * chama: a expressão não está equilibrada (uma aspa ou um parêntese ficou
     * aberto), ou ela não tem separador nenhum. Nos dois casos não há o que
     * conferir, e a resposta honesta é não ter resposta.
     *
     * O `\` dentro de uma aspas pula o caractere seguinte E o escreve no
     * acumulador, porque quem constrói o resultado precisa dele: é o que mantém
     * `"a\".b"` do outro lado de uma concatenação.
     *
     * @param  string  $separators  caracteres que separam em nível superior
     * @return list<string>|null
     */
    private static function splitOnTopLevel(string $expr, string $separators): ?array
    {
        $parts = [];
        $current = '';

        $end = self::walk(
            $expr,
            function (string $ch, int $i, int $depth, ?string $quote) use (&$parts, &$current, $separators): bool {
                if ($quote === null && $depth === 0 && str_contains($separators, $ch)) {
                    $parts[] = $current;
                    $current = '';

                    return true;
                }

                $current .= $ch;

                return true;
            }
        );

        if ($end['quote'] !== null || $end['depth'] !== 0) {
            return null;
        }

        $parts[] = $current;

        return count($parts) > 1 ? $parts : null;
    }

    /**
     * A concatenação de nível superior, ou null se a expressão não é uma.
     *
     * A vírgula também separa porque é o idioma do próprio CodeIgniter nos
     * templates de erro, que escrevem a mensagem e a quebra de linha como dois
     * operandos separados por vírgula. Sem partir nela, a linha caía no fim de
     * `unescaped()` como "não consegui ler", que é fail-closed e por isso acusava
     * uma linha já escapada.
     *
     * `null` tem duas causas, e o gate as trata igual: não é concatenação, siga
     * para a próxima regra.
     *
     * @return list<string>|null
     */
    public static function splitTopLevel(string $expr): ?array
    {
        return self::splitOnTopLevel($expr, '.,');
    }

    public static function splitTernary(string $expr): ?array
    {
        $q = null;
        $abbreviated = false;
        $split = null;

        self::walk(
            $expr,
            function (string $ch, int $i, int $depth, ?string $quote) use ($expr, &$q, &$abbreviated, &$split): bool {
                if ($quote !== null || $depth !== 0) {
                    return true;
                }

                $next = $expr[$i + 1] ?? null;

                // `?` can also be the start of `??`, which is not a ternary.
                if ($ch === '?' && $next !== '?') {
                    $q = $i;
                    $abbreviated = $next === ':';
                } elseif ($ch === ':' && $q !== null && $next !== ':') {
                    $split = [
                        substr($expr, 0, $q),
                        $abbreviated ? substr($expr, 0, $q) : substr($expr, $q + 1, $i - $q - 1),
                        substr($expr, $i + 1),
                    ];

                    return false;
                }

                return true;
            }
        );

        return $split;
    }

    public static function isFunctionCall(string $e): ?array
    {
        if (! preg_match('/^((?:\$[A-Za-z_][A-Za-z0-9_]*(?:\s*->\s*[A-Za-z_][A-Za-z0-9_]*)*)|[A-Za-z_][A-Za-z0-9_]*)\s*\((.*)\)$/s', trim($e), $m)) {
            return null;
        }

        return [$m[1], $m[2]];
    }

    public static function calleeName(string $ref): string
    {
        $parts = preg_split('/\s*->\s*/', trim($ref));

        return end($parts);
    }

    /**
     * A chamada é um método, e não uma função livre?
     *
     * `calleeName()` devolve o último pedaço do caminho porque é isso que faz
     * `$this->load->view()` casar com o helper `view`. O preço é que o nome, sozinho,
     * não distingue um escaper de um método qualquer com o nome do escaper: é por
     * isso que a política pergunta isto antes de aceitar pelo nome.
     *
     * `::` entra junto com `->` porque também é um método. Uma chamada estática não
     * passa por `isFunctionCall()` hoje — o regex não aceita `Foo::bar(...)` e ela
     * cai no fim de `unescaped()` como caso não lido, que é fail-closed — mas
     * responder "não" aqui seria um erro silencioso no dia em que o regex mudar.
     */
    public static function isMethodCall(string $ref): bool
    {
        return str_contains($ref, '->') || str_contains($ref, '::');
    }

    /**
     * Os argumentos de uma chamada, na ordem, sempre como lista.
     *
     * Ao contrário de `splitTopLevel()`, aqui não existe "não é uma lista": quem
     * chega a esta função é porque a expressão É uma chamada, e uma chamada sem
     * separador tem um argumento só. Por isso o `?? [$args]` em vez de null — um
     * único argumento não é motivo para o gate desistir de olhar dentro dele.
     *
     * `f()` tem zero argumentos, e não um argumento vazio. Sem a guarda o
     * acumulador devolveria `['']`, e `unescaped()` receberia uma string vazia no
     * lugar de uma lista — o que fazia `foo()` passar como seguro, já que não
     * havia argumento para reportar.
     *
     * Uma lista de argumentos desequilibrada volta inteira, e não partida. A
     * varredura devolve null nesse caso, e devolver a lista meio partida daria ao
     * relatório uma linha que não existe no arquivo; a expression inteira é
     * reprovada do mesmo jeito, o que é fail-closed do mesmo modo.
     *
     * @return list<string>
     */
    public static function splitCallArgs(string $args): array
    {
        if (trim($args) === '') {
            return [];
        }

        return self::splitOnTopLevel($args, ',') ?? [$args];
    }

    public static function normalizeExpression(string $expr): string
    {
        // A lista de caracteres, e não só o `;`, porque o espaço entre a expressão e
        // o `;` é parte do que precisa ir embora: `rtrim($expr, ';')` deixava
        // "  $a  ; " como "  $a  ", e a função se chamava normalize.
        $expr = trim(rtrim($expr, '; '));

        while (strlen($expr) > 1 && $expr[0] === '(' && $expr[strlen($expr) - 1] === ')') {
            $inner = substr($expr, 1, -1);
            $unbalanced = false;

            $end = self::walk(
                $inner,
                function (string $ch, int $i, int $depth, ?string $quote) use (&$unbalanced): bool {
                    // A profundidade chega já decrementada, então o fecha que não
                    // tinha quem abrir é o primeiro momento em que ela fica negativa.
                    if ($depth < 0) {
                        $unbalanced = true;

                        return false;
                    }

                    return true;
                }
            );

            // Só desembrulha quando o par de fora é mesmo o par de fora; caso
            // contrário a expressão era uma concatenação entre parênteses.
            if ($unbalanced || $end['depth'] !== 0 || $end['quote'] !== null) {
                break;
            }

            $expr = trim($inner);
        }

        return $expr;
    }

    public static function isLiteral(string $e): bool
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

    public static function isScalarCast(string $e): bool
    {
        return preg_match('/^\(\s*(?:int|integer|float|double|string|bool|boolean)\s*\)/i', trim($e)) === 1;
    }

    /**
     * Onde um trecho normalizado aparece no conteúdo: o offset e a linha 1-based.
     *
     * Existe para as duas conferências que casam um padrão no arquivo inteiro, que
     * não têm grupo de captura que aponte a posição — `jsonParseEscaper()` e
     * `preRenderedEscaped()` devolvem o trecho que casou, não onde. Para elas a
     * única forma de chegar numa linha é procurar o texto, e a busca é feita
     * tolerando a indentação que o trecho normalizado perdeu.
     *
     * A PRIMEIRA ocorrência é a resposta certa aqui, e é por isso que a função não
     * se esforça para achar outra: quem chama são conferências de arquivo inteiro,
     * cujo achado é "isto acontece neste arquivo", e a primeira ocorrência é a que o
     * leitor encontra primeiro. O caso que motivou a função — o gate reportando
     * `esc_scalar($result->idOs)` na linha 8 em vez da 411 — era da outra
     * conferência, a das formas de saída, e ela não passa por aqui: usa o offset do
     * próprio regex, que já é a posição exata.
     *
     * @return array{0: int, 1: int}
     */
    public static function locate(string $content, string $snippet): array
    {
        $offset = self::offsetOfNormalized($content, $snippet);

        return [$offset, self::lineAt($content, $offset)];
    }

    public static function offsetOfNormalized(string $content, string $snippet): int|false
    {
        // O delimitador vai para o `preg_quote` porque o padrão é montado entre
        // barras. Sem ele, uma barra dentro do trecho NÃO é escapada, e a primeira
        // delas fecha o padrão: `preg_match()` passa a ler o resto do trecho como
        // modificador e devolve `false` com "Unknown modifier", que em ambiente de
        // produção vira erro fatal e derruba o gate inteiro.
        //
        // Não é um trecho exótico. A guarda de saída ilegível devolve do token até
        // o fim da linha, e numa view de uma linha só isso inclui o resto do
        // documento — `print { a { color: red } }</style>` tem duas barras, e a
        // primeira delas é a que fechava o padrão.
        $parts = array_map(
            static fn (string $part): string => preg_quote($part, '/'),
            explode(' ', $snippet)
        );

        $pattern = implode('\\s+', $parts);

        return preg_match('/' . $pattern . '/', $content, $m, PREG_OFFSET_CAPTURE) ? $m[0][1] : false;
    }

    /**
     * A linha 1-based de um offset, ou 0 se o offset não existe.
     *
     * `substr_count()` conta as quebras de linha do início até o offset, e a linha
     * é uma a mais que isso: a linha 1 é a que não tem quebra antes dela. O 0 para
     * um offset falso é o que faz o relatório dizer "0" em vez de "1" quando o
     * trecho não foi encontrado no arquivo, que é uma resposta honesta e não uma
     * posição inventada.
     */
    public static function lineAt(string $content, int|false $offset): int
    {
        if ($offset === false || $offset < 0) {
            return 0;
        }

        return substr_count($content, "\n", 0, $offset) + 1;
    }
}
