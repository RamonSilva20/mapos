<?php

namespace Tests\Support\ViewEscaping;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tools\ViewEscaping\PhpExpression;

/**
 * A gramática que o gate de escaping escreve à mão.
 *
 * Sem trait de transação e sem banco: PhpExpression é pura sobre string, sem IO e
 * sem política, e é por isso que o teste dela é uma lista de expressões e nada
 * mais. Os casos são sintéticos e pequenos de propósito — a função já rodou contra
 * 107 views sem nenhum teste, e o que ela erra é sempre um caso de borda de quoting
 * ou de operador, nunca um schema.
 *
 * O par `calleeName()` / `isMethodCall()` tem a sua própria seção porque é o par
 * que decide a diferença entre um escaper e um método qualquer com o nome do
 * escaper. Ver EscapingChecksTest para o efeito disso no gate.
 */
final class PhpExpressionTest extends TestCase
{
    /**
     * O nome do callable é o último pedaço do caminho, e o caminho inteiro diz se
     * ele é função ou método.
     *
     * Estes dois passos existem por causa de uma coisa só: `$this->load->view()`
     * precisa casar com o helper `view`. Por isso `calleeName()` devolve "view" e
     * não "$this->load->view". O preço é que o nome sozinho não distingue um
     * escaper de `$row->esc()`, e é por isso que a política olha os dois juntos.
     */
    #[Test]
    public function testTheNameIsTheLastSegmentAndTheShapeIsWhatSaysFunctionFromMethod(): void
    {
        $this->assertSame('view', PhpExpression::calleeName('$this->load->view'));
        $this->assertSame('esc', PhpExpression::calleeName('$row->esc'));
        $this->assertSame('esc', PhpExpression::calleeName('esc'));

        $this->assertTrue(PhpExpression::isMethodCall('$row->esc'));
        $this->assertTrue(PhpExpression::isMethodCall('$this->session->userdata'));
        $this->assertTrue(PhpExpression::isMethodCall('Foo::bar'));
        $this->assertFalse(PhpExpression::isMethodCall('esc'));
        $this->assertFalse(PhpExpression::isMethodCall('htmlspecialchars'));
    }

    /**
     * A forma da chamada é reconhecida só com o parêntese de abertura e o de
     * fechamento, e devolve o callable e os argumentos separados.
     *
     * O regex aceita uma cadeia `$a->b->c` ou um nome simples. Não aceitar `::` é
     * proposital: uma chamada estática não casa aqui e cai no fim de `unescaped()`
     * como caso não lido, que é fail-closed.
     */
    #[Test]
    public function testAFunctionCallIsSplitIntoCalleeAndArguments(): void
    {
        $this->assertSame(['esc', '$a'], PhpExpression::isFunctionCall('esc($a)'));
        $this->assertSame(
            ['$this->load->view', '$a, $b'],
            PhpExpression::isFunctionCall('$this->load->view($a, $b)')
        );
        $this->assertSame(
            ['date', "'d/m/Y', \$quando"],
            PhpExpression::isFunctionCall('date(\'d/m/Y\', $quando)')
        );

        // Sem parêntese não é chamada, e é o que separa `esc($a) . $y` de `esc($a)`.
        $this->assertNull(PhpExpression::isFunctionCall('$a'));
        $this->assertNull(PhpExpression::isFunctionCall('esc($a) . $y'));
    }

    /**
     * O corte no `;` de nível superior ignora o que está entre aspas e dentro de
     * parênteses ou colchetes.
     *
     * É o que impede que o `;` de um `date('d/m;Y')` corte a linha ao meio, e o
     * `;` de um `array(...)` idem. Sem isso a expressão chegava à conferência
     * partida em dois pedaços e o relatório apontava a linha errada.
     */
    #[Test]
    public function testTheTopLevelSemicolonCutIgnoresQuotesAndNesting(): void
    {
        $this->assertSame('$a', PhpExpression::cutAtTopLevelSemicolon('$a;'));
        $this->assertSame('$a . $b', PhpExpression::cutAtTopLevelSemicolon('$a . $b;'));
        $this->assertSame(
            "date('d/m;Y', \$quando)",
            PhpExpression::cutAtTopLevelSemicolon("date('d/m;Y', \$quando);")
        );
        $this->assertSame(
            'implode(";", $a)',
            PhpExpression::cutAtTopLevelSemicolon('implode(";", $a);')
        );
        $this->assertSame(
            "f(\$a, ['x' => 1;2])",
            PhpExpression::cutAtTopLevelSemicolon("f(\$a, ['x' => 1;2]);")
        );
    }

    /**
     * A concatenação parte em operandos, e a vírgula também é separador.
     *
     * A vírgula estar na lista não é detalhe: é o idioma dos templates de erro do
     * CI3, que escrevem a mensagem e a quebra de linha como dois operandos
     * separados por vírgula. Sem partir nela, a linha caía no fim de
     * `unescaped()` como "não consegui ler" e acusava uma linha já escapada.
     *
     * Os pedaços saem crus, com o espaço que os separava. Nenhum consumidor nota
     * porque `unescaped()` normaliza a expressão inteira antes e `EscapingChecks`
     * apara cada trecho reprovado — mas a primitiva não reescreve conteúdo, e é
     * isso que a distingue de `normalizeExpression()`.
     */
    #[Test]
    public function testTopLevelConcatenationSplitsOnBothDotAndComma(): void
    {
        $this->assertSame(['esc($a) ', ' $b'], PhpExpression::splitTopLevel('esc($a) . $b'));
        $this->assertSame(['esc($a)', ' $b', ' $c'], PhpExpression::splitTopLevel('esc($a), $b, $c'));
        $this->assertSame(
            ["'a;b' ", ' $c'],
            PhpExpression::splitTopLevel("'a;b' . \$c"),
            'o ponto e a vírgula dentro de aspas não separam'
        );
        $this->assertSame(
            ['f($a, $b) ', ' $c'],
            PhpExpression::splitTopLevel('f($a, $b) . $c'),
            'a vírgula dentro do parêntese não separa'
        );
    }

    /**
     * Um operador só, ou nada, não é uma divisão.
     *
     * `splitTopLevel()` devolve null para não-dividido, e o gate trata isso como
     * "não é concatenação" e segue para o próximo teste. Devolver um array de um
     * elemento faria a linha entrar no ramo de concatenação sem ser uma.
     */
    #[Test]
    public function testAnExpressionWithoutASeparatorDoesNotSplit(): void
    {
        $this->assertNull(PhpExpression::splitTopLevel('$a'));
        $this->assertNull(PhpExpression::splitTopLevel('esc($a)'));
        $this->assertNull(PhpExpression::splitTopLevel('f($a, $b)'), 'a vírgula está dentro do parêntese');
    }

    /**
     * O ternário devolve condição, braço verdadeiro e braço falso.
     *
     * O caso `??` está aqui porque é o que faz o `?` ser lido como ternário quando
     * não é: `$a ?? $b` tem dois `?` e nenhum `:`, e sem a guarda viraria um ternário
     * com a condição errada. O caso abreviado `?:` devolve a condição nos dois
     * braços, que é o que a linguagem quer dizer.
     */
    #[Test]
    public function testTheTernarySplitsIntoConditionAndBothBranches(): void
    {
        $this->assertSame(
            ['$c ', ' esc($y) ', ' $z'],
            PhpExpression::splitTernary('$c ? esc($y) : $z')
        );
        $this->assertSame(
            ['$c ', ' esc($y) ', ' esc($w)'],
            PhpExpression::splitTernary('$c ? esc($y) : esc($w)')
        );

        $this->assertNull(
            PhpExpression::splitTernary('$a ?? $b'),
            '?? não é ternário'
        );
        $this->assertSame(
            ['$c ', ' $a["x:y"] ', ' $z'],
            PhpExpression::splitTernary('$c ? $a["x:y"] : $z'),
            'o : dentro das aspas não fecha o ternário'
        );
    }

    /**
     * Os argumentos de uma chamada partem na vírgula de nível superior.
     *
     * O caso de zero argumentos é o que a guarda no topo de `splitCallArgs()`
     * protege: sem ela o acumulador devolvia `['']`, e `unescaped()` recebia uma
     * string vazia no lugar de uma lista — o que fazia `foo()` passar como seguro,
     * já que não havia argumento para reportar.
     */
    #[Test]
    public function testCallArgumentsSplitOnTopLevelCommas(): void
    {
        $this->assertSame(['$a', ' $b'], PhpExpression::splitCallArgs('$a, $b'));
        $this->assertSame(["'d/m/Y'", ' $quando'], PhpExpression::splitCallArgs("'d/m/Y', \$quando"));
        $this->assertSame(['g($a, $b)', ' $c'], PhpExpression::splitCallArgs('g($a, $b), $c'));

        $this->assertSame([], PhpExpression::splitCallArgs(''));
        $this->assertSame([], PhpExpression::splitCallArgs('   '));
    }

    /**
     * O parêntese que embrulha a expressão inteira é desembrulhado, o que está
     * dentro dele não.
     *
     * `($a)` e `($a . $b)` são a mesma expressão para quem lê, e desembrulhar as
     * duas é o que permite que o gate as reconheça. O par que não é o par de fora é
     * uma concatenação entre parênteses, e desembrulhar esse sim mudaria o
     * significado: `(1 + 2) . $b` viraria `1 + 2 . $b`.
     */
    #[Test]
    public function testTheWrappingParenthesisIsStrippedButAnInnerOneIsNot(): void
    {
        $this->assertSame('$a', PhpExpression::normalizeExpression('($a)'));
        $this->assertSame('$a', PhpExpression::normalizeExpression('(($a))'));
        $this->assertSame('$a . $b', PhpExpression::normalizeExpression('($a . $b)'));
        $this->assertSame('$a', PhpExpression::normalizeExpression('$a;'));
        $this->assertSame('$a', PhpExpression::normalizeExpression('  $a  ; '));

        $this->assertSame(
            '(1 + 2) . $b',
            PhpExpression::normalizeExpression('(1 + 2) . $b'),
            'o par de fora não é o par que embrulha'
        );
    }

    /**
     * Literais e casts não são valor de usuário, e por isso passam.
     */
    #[Test]
    public function testLiteralsAndScalarCastsAreRecognisedAsSafe(): void
    {
        $this->assertTrue(PhpExpression::isLiteral("'texto'"));
        $this->assertTrue(PhpExpression::isLiteral('"texto"'));
        $this->assertTrue(PhpExpression::isLiteral('42'));
        $this->assertTrue(PhpExpression::isLiteral('4.2'));
        $this->assertTrue(PhpExpression::isLiteral('true'));
        $this->assertTrue(PhpExpression::isLiteral('null'));
        $this->assertFalse(PhpExpression::isLiteral(''));
        $this->assertFalse(PhpExpression::isLiteral('$a'));

        $this->assertTrue(PhpExpression::isScalarCast('(int) $a'));
        $this->assertTrue(PhpExpression::isScalarCast('(float)$a'));
        $this->assertFalse(PhpExpression::isScalarCast('(array) $a'));
    }

    /**
     * A forma de valor puro é a que o gate reprova sem olhar dentro.
     *
     * A cadeia de acesso conta como valor puro: `$row->nome` é o caso mais comum de
     * achado do gate. Não há caso especial para o CI3 aqui — a leitura de sessão é
     * uma FONTE, e fonte é política, em `EscapingPolicy::isSource()`. Quando essa
     * leitura morava neste arquivo, ela era o único acréscimo de CI3 na classe, e
     * ela tinha que ser aplicada acessor por acessor, um por um, com o resto
     * compartilhando o defeito.
     */
    #[Test]
    public function testBareValuesAreRecognised(): void
    {
        $this->assertTrue(PhpExpression::isBareValue('$a'));
        $this->assertTrue(PhpExpression::isBareValue('$row->nome'));
        $this->assertTrue(PhpExpression::isBareValue('$row["nome"]'));
        $this->assertTrue(PhpExpression::isBareValue('$_SESSION["nome"]'));
        $this->assertFalse(PhpExpression::isBareValue('esc($a)'));
        $this->assertFalse(PhpExpression::isBareValue('$this->session->userdata("nome")'));
    }

    /**
     * A linha de um trecho é a do offset, e offset inexistente é 0 e não 1.
     *
     * O `false` virando 0 é o que faz o relatório dizer "linha 0" quando o trecho
     * não foi encontrado, em vez de apontar para a primeira linha do arquivo. Uma
     * posição inventada manda o leitor para o lugar errado com a confiança de quem
     * estava certo.
     */
    #[Test]
    public function testTheLineComesFromTheOffsetAndAMissingOffsetIsZero(): void
    {
        $content = "linha 1\nlinha 2\nlinha 3";

        $this->assertSame(1, PhpExpression::lineAt($content, 0));
        $this->assertSame(2, PhpExpression::lineAt($content, 8));
        $this->assertSame(3, PhpExpression::lineAt($content, 16));
        $this->assertSame(0, PhpExpression::lineAt($content, false));
        $this->assertSame(0, PhpExpression::lineAt($content, -1));
    }

    /**
     * `locate()` aponta a primeira ocorrência do trecho normalizado no arquivo.
     *
     * A primeira é a resposta, e não uma limitação: o único caller é a varredura
     * das duas conferências de arquivo inteiro, que casam um padrão no conteúdo
     * todo e por isso não têm grupo de captura que aponte a posição. O achado delas
     * é "isto acontece neste arquivo", e a primeira ocorrência é a que o leitor
     * encontra primeiro. A conferência das formas de saída, que sim tem o offset
     * exato do regex, não passa por aqui.
     */
    #[Test]
    public function testLocateReportsTheFirstOccurrenceOfTheSnippet(): void
    {
        $content = "esc(\$a);\npreenchido\n" . str_repeat("x\n", 400) . "esc(\$a);\n";

        [$offset, $line] = PhpExpression::locate($content, 'esc($a)');

        $this->assertSame(1, $line);
        $this->assertSame(0, $offset);
    }

    /**
     * Um trecho com barra é localizável, e o delimitador do padrão é escapado.
     *
     * O padrão de `offsetOfNormalized()` é montado entre barras, e o `preg_quote`
     * precisa receber esse delimitador para escapar a barra de dentro do trecho.
     * Sem ele, a primeira barra do trecho fecha o padrão e o resto é lido como
     * modificador: `preg_match()` devolvia `false` com "Unknown modifier", o que em
     * produção é um erro fatal que derruba o gate.
     *
     * O trecho que dispara não é exótico, e a razão está na guarda de saída
     * ilegível: ela devolve do token até o fim da linha, e numa view de uma linha
     * só isso inclui o resto do documento. Uma folha de estilo embutida é
     * suficiente — `print { a { color: red } }</style>` tem duas barras, e a
     * primeira delas é a que fechava o padrão.
     */
    #[Test]
    public function testASnippetContainingASlashIsStillFound(): void
    {
        $content = '<style>@media print { a { color: red } }</style>';

        [$offset, $line] = PhpExpression::locate($content, 'print { a { color: red } }</style>');

        $this->assertSame(1, $line);
        $this->assertSame(
            strpos($content, 'print {'),
            $offset,
            'o offset é onde o TRECHO começa, e não onde a linha começa'
        );
    }

    /**
     * O pedaço de um achado normalizado é procurado tolerando a indentação que ele
     * teve no arquivo.
     *
     * A chave do baseline é o trecho normalizado, e é por isso que a busca também é:
     * uma entrada sobrevive a uma mudança que só mexeu em espaços.
     */
    #[Test]
    public function testTheNormalizedSnippetIsFoundAcrossDifferingWhitespace(): void
    {
        $content = "<?= esc(\$a)\n    . \$b ?>";

        [$offset, $line] = PhpExpression::locate($content, 'esc($a) . $b');

        $this->assertSame(1, $line);
        $this->assertIsInt($offset);
    }

    /**
     * O `;` de uma forma `echo $x;` não faz parte da expressão conferida.
     *
     * A quinta forma de saída não vem terminada pela tag de fim, e o `;` já é parte
     * do match. Cortar de novo comeria o operando seguinte, que é o que
     * `cutAtTopLevelSemicolon()` existe para não fazer.
     */
    #[Test]
    public function testATrailingSemicolonIsNotPartOfTheCheckedExpression(): void
    {
        $this->assertSame('$a', PhpExpression::normalizeExpression(PhpExpression::cutAtTopLevelSemicolon('$a;')));
    }

    /**
     * @param  string  $expr
     */
    #[DataProvider('provideBareValues')]
    #[Test]
    public function testBareValueDetectionIsNotConfusedByConcatenation(string $expr, bool $expected): void
    {
        $this->assertSame($expected, PhpExpression::isBareValue($expr));
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function provideBareValues(): iterable
    {
        yield 'variável simples' => ['$a', true];
        yield 'propriedade' => ['$os->defeito', true];
        yield 'cadeia de propriedades' => ['$this->session->userdata', true];
        yield 'índice' => ['$arr[0]', true];
        yield 'índice com variável' => ['$arr[$i]', true];
        yield 'concatenação' => ['$a . $b', false];
        yield 'chamada' => ['esc($a)', false];
        yield 'ternário' => ['$a ? $b : $c', false];
    }

    /**
     * O `\` dentro de uma aspa come o caractere seguinte, e o par chega ao
     * callback em duas chamadas separadas: a do `\` e a do que ele protege.
     *
     * São os dois casos que nenhum dos quatro callbacks de produção alcança — os
     * quatro só param com `$quote === null`, e o escape só existe com ela aberta.
     * São eles que torneiam o contrato de parada, e o teste entra por reflexão
     * porque nenhum caminho público chega lá.
     */
    #[Test]
    public function testTheCallbackSeesTheBackslashAndTheCharacterItEscapes(): void
    {
        $visited = $this->walkWithLogger('"a\\bcd"');

        $this->assertSame(['"', 'a', '\\', 'b', 'c', 'd', '"'], $visited);
    }

    #[Test]
    public function testAStopRequestOnTheEscapedCharacterEndsTheWalk(): void
    {
        // O `b` é o caractere escapado: ele só chega ao callback pela segunda
        // chamada, a que acontece dentro do `if ($ch === '\\')`. Parar nele tem de
        // parar a varredura. Com os dois retornos compostos por `||`, o `false`
        // desta chamada era engolido pelo `true` da anterior e o `walk()` seguia
        // até o fim da expressão.
        $visited = $this->walkUntil('"a\\bcd"', 'b');

        $this->assertSame(['"', 'a', '\\', 'b'], $visited);
    }

    #[Test]
    public function testAStopRequestOnTheBackslashItselfEndsTheWalk(): void
    {
        $visited = $this->walkUntil('"a\\bcd"', '\\');

        $this->assertSame(['"', 'a', '\\'], $visited);
    }

    #[Test]
    public function testAStopRequestAfterAnEscapeStillEndsTheWalk(): void
    {
        // O caso que o `||` não quebrava, fixado para que a correção não seja
        // feita removendo a segunda chamada: parar em `c` precisa parar em `c`.
        $visited = $this->walkUntil('"a\\bcd"', 'c');

        $this->assertSame(['"', 'a', '\\', 'b', 'c'], $visited);
    }

    /**
     * Os caracteres que o `walk()`visitou, na ordem.
     *
     * @return list<string>
     */
    private function walkWithLogger(string $expr): array
    {
        return $this->walk($expr, static fn (string $ch): bool => true);
    }

    /**
     * Os caracteres que o `walk()`visitou até pedir parada em `$stopAt`.
     *
     * @return list<string>
     */
    private function walkUntil(string $expr, string $stopAt): array
    {
        return $this->walk($expr, static fn (string $ch): bool => $ch !== $stopAt);
    }

    /**
     * @param  callable(string): bool  $visit
     * @return list<string>
     */
    private function walk(string $expr, callable $visit): array
    {
        $method = new ReflectionMethod(PhpExpression::class, 'walk');
        $visited = [];
        $method->invoke(null, $expr, static function (string $ch) use ($visit, &$visited): bool {
            $visited[] = $ch;

            return $visit($ch);
        });

        return $visited;
    }
}
