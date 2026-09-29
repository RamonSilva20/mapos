<?php

namespace Tests\Support\ViewEscaping;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tools\ViewEscaping\EscapingChecks;
use Tools\ViewEscaping\EscapingPolicy;
use Tools\ViewEscaping\PhpExpression;
use Tools\ViewEscaping\Rule;

/**
 * O gate que decide se um valor que chega à página foi escapado.
 *
 * Sem trait de transação e sem banco: `unescaped()` é pura, recebe uma expressão e
 * uma política e devolve o trecho que a reprovou, ou null. O teste é uma lista de
 * expressões e nada mais, e é essa forma que o próprio arquivo de produção pedia —
 * a política vem por parâmetro justamente para que um caso seja uma política menor
 * e não um monkey-patch num global.
 *
 * A primeira seção é a que este arquivo existe para travar. Uma allowlist única
 * permitia que QUALQUER método cujo nome colidisse com um escaper fosse tratado como
 * o escaper, porque `calleeName()` devolve o último pedaço do caminho para que
 * `$this->load->view()` case com o helper `view`. Com a lista única, `$row->esc($x)`
 * passava pelo gate sendo um método qualquer com o nome do escaper, e o gate é a
 * única barreira entre uma view e um XSS.
 *
 * Os casos do resto são fail-closed: a propriedade que importa é que nenhuma saída
 * alternativa exista. Um gate que grita lobo é ignorado, e um gate que fica calado
 * é o pior dos dois.
 */
final class EscapingChecksTest extends TestCase
{
    #[Test]
    public function testAnEscaperCalledAsAMethodIsNotAnEscaper(): void
    {
        $policy = EscapingPolicy::default();

        // A falha real: um método qualquer cujo nome por acaso colide com o de um
        // escaper era aprovado. `$row->esc()` não escapa nada — é um método do
        // objeto que a view tem em mãos.
        $this->assertSame('$x', EscapingChecks::unescaped('$row->esc($x)', $policy));
        $this->assertSame('$x', EscapingChecks::unescaped('$this->esc($x)', $policy));
        $this->assertSame('$x', EscapingChecks::unescaped('$obj->htmlspecialchars($x)', $policy));
        $this->assertSame('$x', EscapingChecks::unescaped('$o->printSafeHtml($x)', $policy));

        // E o mesmo nome chamado como função continua sendo o escaper. É a outra
        // metade da regra, e é ela que impede que o conserto vire Tools em que
        // ninguém mais escapa nada.
        $this->assertNull(EscapingChecks::unescaped('esc($x)', $policy));
        $this->assertNull(EscapingChecks::unescaped('htmlspecialchars($x, ENT_QUOTES)', $policy));
    }

    /**
     * Um helper do CI3 casa na forma de método, e é por isso que `calleeName()`
     * existe.
     *
     * Estes três são os únicos que precisam disso, e todos aparecem em views reais:
     * `create_links()` em 16, `view()` em 2 e `count_all()` em 1. Se este teste
     * falhar, a correção não é apertar a regra — é remover um helper da lista, e o
     * relatório de achados novos aponta a view.
     *
     * `segment()` saiu daqui: ele é o caminho da requisição, e é a razão de a
     * quarta lista existir. Ver `testARequestSourceIsReportedEvenWhenItsArgumentsAreLiterals()`.
     */
    #[Test]
    public function testCiHelpersStillMatchInTheirMethodForm(): void
    {
        $policy = EscapingPolicy::default();

        $this->assertNull(EscapingChecks::unescaped('$this->load->view($a, $b)', $policy));
        $this->assertNull(EscapingChecks::unescaped('$this->pagination->create_links()', $policy));
        $this->assertNull(EscapingChecks::unescaped('$this->db->count_all("os")', $policy));

        // E na forma de função, porque todos os três também são funções livres.
        $this->assertNull(EscapingChecks::unescaped('create_links()', $policy));
    }

    /**
     * Uma fonte de requisição reprova mesmo tendo argumentos literais.
     *
     * Este é o defeito que a lista de fontes conserta, e o teste o fixa pelo
     * mecanismo exato que o produzia: os argumentos de `$this->input->get('campo')`
     * são o nome do campo, um literal, então uma conferência que reprova a chamada
     * e depois inspeciona os argumentos aprova a linha sem olhar para dentro. Medido
     * nesta árvore antes do conserto: `input->get`, `input->post`, `uri->segment`,
     * `uri->uri_string`, `uri->current_url` e `request->getVar` saíam todos aprovados.
     *
     * A segunda metade é o que impede o conserto de virar "Tools em que ninguém mais
     * lê nada": um escaper em volta continua vencendo, porque o que protege a linha
     * é o escaper, e não o nome do que está dentro dele.
     */
    #[DataProvider('provideRequestSources')]
    #[Test]
    public function testARequestSourceIsReportedEvenWhenItsArgumentsAreLiterals(string $expr): void
    {
        $this->assertSame($expr, EscapingChecks::unescaped($expr, EscapingPolicy::default()));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideRequestSources(): iterable
    {
        yield 'input->get' => ['$this->input->get("pesquisa")'];
        yield 'input->post' => ['$this->input->post("campo")'];
        yield 'input->cookie' => ['$this->input->cookie("sessao")'];
        yield 'input->ip_address' => ['$this->input->ip_address()'];
        yield 'request->getVar' => ['$this->request->getVar("x")'];
        yield 'uri->segment' => ['$this->uri->segment(1)'];
        yield 'uri->uri_string' => ['$this->uri->uri_string()'];
        yield 'uri->current_url' => ['$this->uri->current_url()'];
        yield 'session->userdata' => ['$this->session->userdata("nome")'];
        yield 'session->flashdata' => ['$this->session->flashdata("error")'];
        yield 'segment() livre' => ['segment(1)'];
        yield 'current_url() livre' => ['current_url()'];
    }

    #[Test]
    public function testAnEscaperStillWinsOverASourceInsideIt(): void
    {
        $policy = EscapingPolicy::default();

        $this->assertNull(EscapingChecks::unescaped('esc($this->input->post("campo"))', $policy));
        $this->assertNull(EscapingChecks::unescaped('html_escape($this->input->get("pesquisa"))', $policy));
        $this->assertNull(EscapingChecks::unescaped('esc_url(current_url())', $policy));

        // E a fonte continua reprovando quando a linha só a embrulha num transformador
        // que não escapa, que era o caminho pelo qual ela voltava a passar. O
        // trecho reportado é o que reprovou, e não a chamada inteira — é o mesmo
        // que `strtoupper($a)` reporta como `$a`.
        $this->assertSame(
            '$this->uri->segment(1)',
            EscapingChecks::unescaped('ucfirst($this->uri->segment(1))', $policy)
        );
    }

    /**
     * Um helper não-escaper continua aceito na forma de método, por desenho.
     *
     * Este é o preço honesto da regra, e ele está escrito aqui para não ser
     * "descoberto" depois: `view` e `count_all` são helpers que produzem markup ou
     * uma contagem, e ambos precisam casar na forma de método. `$r->view($a, $b)`
     * passa pelo gate, e é o preço de `$this->load->view($a, $b)` passar. A
     * diferença em relação a um escaper é que nenhum dos dois devolve valor de
     * usuário como HTML escapado por dentro: eles produzem markup por construção.
     */
    #[Test]
    public function testANonEscapingHelperStillMatchesInItsMethodFormByDesign(): void
    {
        $policy = EscapingPolicy::default();

        $this->assertNull(EscapingChecks::unescaped('$r->view($a, $b)', $policy));
        $this->assertNull(EscapingChecks::unescaped('$m->count($x)', $policy));
        $this->assertNull(EscapingChecks::unescaped('$c->get_class($x)', $policy));
    }

    /**
     * Uma política menor roda sem tocar na global.
     *
     * Este é o motivo de `EscapingPolicy` ser um valor com construtor público, e é o
     * que a versão anterior das listas soltas não permitia: testar "só `esc` passa"
     * exigia um array montado à mão em cada chamada, e ninguém escrevia o teste.
     */
    #[Test]
    public function testTheCheckRunsAgainstAWhoMadePolicyWithoutTouchingTheDefault(): void
    {
        $permissive = new EscapingPolicy(
            escapers: ['esc'],
            helpers: ['strtoupper'],
            sources: ['post'],
            preRendered: [],
        );

        $this->assertNull(EscapingChecks::unescaped('esc($a)', $permissive));
        $this->assertNull(EscapingChecks::unescaped('strtoupper($a)', $permissive));
        $this->assertNull(
            EscapingChecks::unescaped('$row->strtoupper($a)', $permissive),
            'helper continua aceito na forma de método'
        );
        $this->assertSame(
            '$this->input->post("a")',
            EscapingChecks::unescaped('$this->input->post("a")', $permissive),
            'a lista de fontes também é a da política menor'
        );
        $this->assertSame(
            '$a',
            EscapingChecks::unescaped('esc_url($a)', $permissive),
            'o que não está na política menor continua reprovando'
        );
        $this->assertSame(
            '$topo',
            EscapingChecks::unescaped('$topo', $permissive),
            'a lista de pré-renderizados também é a da política'
        );

        // E a política padrão não foi tocada por nenhuma das conferências acima.
        $default = EscapingPolicy::default();

        $this->assertSame('$a', EscapingChecks::unescaped('strtoupper($a)', $default));
        $this->assertNull(EscapingChecks::unescaped('$topo', $default));
    }

    /**
     * O que reprova: valor cru chegando à página.
     *
     * O esperado é o TRECHO que reprovou, não um booleano, porque é o trecho que o
     * relatório mostra ao humano. E a ordem importa: `$a . esc($b)` reprova só em
     * `$a`, e reprovar a linha inteira seria dizer que `esc()` não escapa.
     */
    #[DataProvider('provideUnescapedExpressions')]
    #[Test]
    public function testAnUnescapedExpressionReportsTheOffendingSnippet(string $expr, ?string $expected): void
    {
        $this->assertSame($expected, EscapingChecks::unescaped($expr, EscapingPolicy::default()));
    }

    /**
     * @return iterable<string, array{string, string|null}>
     */
    public static function provideUnescapedExpressions(): iterable
    {
        yield 'variável crua' => ['$a', '$a'];
        yield 'propriedade crua' => ['$row->nome', '$row->nome'];
        yield 'leitura de sessão' => ['$this->session->userdata("nome")', '$this->session->userdata("nome")'];
        yield 'fonte de requisição num ternário' => ['$x ? $this->input->get("a") : esc($b)', '$this->input->get("a")'];
        yield 'fonte de requisição concatenada' => ['esc($a) . current_url()', 'current_url()'];
        yield 'concatenação de dois crus' => ['$a . $b', '$a , $b'];
        yield 'o primeiro de dois, um escapado' => ['$a . esc($b)', '$a'];
        yield 'o segundo de dois, um escapado' => ['esc($a) . $b', '$b'];
        yield 'os dois dentro do escaper' => ['esc($a . $b)', null];
        yield 'ternário com um braço escapado' => ['$x ? esc($y) : $z', '$z'];
        yield 'ternário com os dois crus' => ['$x ? $y : $z', '$y , $z'];
        yield 'ternário abreviado' => ['$x ?: $y', '$x , $y'];
        yield 'coalescência não é ternário' => ['$a ?? $b', '$a ?? $b'];
        yield 'função que não escapa' => ['strtoupper($a)', '$a'];
        yield 'função que não escapa, aninhada' => ['nl2br($a)', '$a'];
        yield 'argumento de uma função desconhecida' => ['implode(", ", $arr) . $b', '$arr , $b'];
        yield 'chamada sem argumento' => ['foo()', 'foo()'];
        yield 'parêntese que embrulha' => ['($a)', '$a'];
        yield 'parêntese que embrulha duas vezes' => ['(($a))', '$a'];
        yield 'índice com variável' => ['$a[$i]', '$a[$i]'];
        yield 'literal com variável' => ['$a . "<b>x</b>"', '$a'];
        yield 'marshal com vírgula' => ['$msg, $br', '$msg , $br'];
    }

    /**
     * O que passa, e por quê.
     *
     * Os literais e os casts entram porque não carregam valor de usuário; o
     * `<pre-renderizado>` porque é markup pronto por desenho; o `isBareValue()`
     * não aparece aqui porque reprova, e está nos casos de cima.
     */
    #[DataProvider('provideSafeExpressions')]
    #[Test]
    public function testASafeExpressionReportsNothing(string $expr): void
    {
        $this->assertNull(EscapingChecks::unescaped($expr, EscapingPolicy::default()));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideSafeExpressions(): iterable
    {
        yield 'string vazia' => [''];
        yield 'literal simples' => ["'texto'"];
        yield 'literal duplo' => ['"texto"'];
        yield 'número' => ['42'];
        yield 'float' => ['4.2'];
        yield 'booleano' => ['true'];
        yield 'nulo' => ['null'];
        yield 'cast para int' => ['(int) $a'];
        yield 'cast para string' => ['(string) $a'];
        yield 'escaper de html' => ['esc($a)'];
        yield 'escaper de json' => ['esc_json($a)'];
        yield 'escaper de url' => ['esc_url($a)'];
        yield 'escaper de src' => ['esc_img_src($a)'];
        yield 'escaper de css' => ['esc_css($a)'];
        yield 'escaper de mensagem' => ['esc_msg($a)'];
        yield 'html_escape' => ['html_escape($a)'];
        yield 'printSafeHtml' => ['printSafeHtml($a)'];
        yield 'coerção numérica' => ['intval($a)'];
        yield 'contagem' => ['count($arr)'];
        yield 'formatação' => ['number_format($a, 2, ",", ".")'];
        yield 'formatação com aspas' => ["date('d/m/Y', \$quando)"];
        yield 'reflexão' => ['get_class($a)'];
        yield 'csrf' => ['get_csrf_token_name()'];
        yield 'form helper' => ['form_input("nome")'];
        yield 'markup pré-renderizado' => ['$topo'];
        yield 'markup pré-renderizado com modal' => ['$modalGerarPagamento'];
        yield 'markup concatenado com um valor' => ['$topo . esc($a)'];
    }

    /**
     * O texto de uma linha de conteúdo é uma expressão, ou não é nada.
     *
     * Estas duas conferências são as que reprovam por padrão de arquivo inteiro, e
     * as duas dependem de a política estar com a lista certa: o padrão de
     * `preRenderedEscaped()` é montado a partir da mesma lista que a isenção usa. Se
     * as duas divergissem, um valor poderia sair cru num ponto e ser escapado noutro,
     * e nenhuma das duas acusaria nada.
     */
    #[Test]
    public function testTheTwoFileWideChecksReadTheSameListAsTheExemption(): void
    {
        $policy = EscapingPolicy::default();

        $this->assertNull(EscapingChecks::preRenderedEscaped('<?= $topo ?>', $policy));
        $this->assertSame(
            'esc($topo)',
            EscapingChecks::preRenderedEscaped('<?= esc($topo) ?>', $policy),
            'escapar markup pronto quebra a página em vez de protegê-la'
        );
        $this->assertNull(
            EscapingChecks::preRenderedEscaped('<?= esc($a) ?>', $policy),
            'escapar um valor normal é o certo'
        );
    }

    /**
     * A conferência de pronto-renderizado cobre TODOS os escapers da política.
     *
     * Este é o teste que impede a lista escrita à mão de voltar. A versão anterior
     * do padrão tinha quatro nomes — `esc`, `esc_html`, `html_escape`,
     * `htmlspecialchars` — e a política tem nove: acrescentar um escaper novo
     *cq não fazia aquela conferência reprovar, e ninguém veria isso, porque a
     * conferência é uma varredura de arquivo inteiro que só fala quando acha
     * alguma coisa. Um `esc_html` na lista era ainda o sintoma: um escaper que não
     * existe em lugar nenhum do código, e cujo lugar na lista era impossível de
     * justificar lendo o resto.
     *
     * O teste percorre a lista em vez de nomear casos, e é por isso que falha se
     * alguém voltar a escrever os nomes à mão: um quarto escaper coberto e um
     * décimo não fariam diferença para um teste que só citasse dois.
     */
    #[Test]
    public function testEveryEscaperInThePolicyIsCaughtWrappingPreRenderedMarkup(): void
    {
        $policy = EscapingPolicy::default();

        $this->assertNotEmpty($policy->escapers, 'uma lista vazia passaria este teste sem conferir nada');

        foreach ($policy->escapers as $escaper) {
            $this->assertSame(
                "{$escaper}(\$topo)",
                EscapingChecks::preRenderedEscaped("<?= {$escaper}(\$topo) ?>", $policy),
                "{$escaper}() em volta de markup pronto deveria ser reprovado, e a conferência tem nomes próprios"
            );
        }
    }

    /**
     * Um JSON.parse() alimentado por um escaper que emite JSON cru não é uma string.
     */
    #[Test]
    public function testAJsonParseFedAnEscaperIsReported(): void
    {
        $policy = EscapingPolicy::default();

        $this->assertSame(
            'JSON.parse(\'<?= esc_json($cfg) ?>\')',
            EscapingChecks::jsonParseEscaper('var c = JSON.parse(\'<?= esc_json($cfg) ?>\');', $policy)
        );

        $this->assertNull(
            EscapingChecks::jsonParseEscaper('var c = JSON.parse(\'<?= json_encode($cfg) ?>\');', $policy),
            'json_encode() devolve JSON, então o parse funciona'
        );
    }

    /**
     * A conferência do `JSON.parse()` cobre as formas de saída que podem carregar uma
     * chamada, e não duas delas.
     *
     * O método casava `<?=` e `<?php echo` digitados de novo dentro do regex, o que
     * deixava de fora as outras duas formas terminadas. O sintoma é o que importa:
     * `esc_json` aparecia duas vezes no padrão, ninguém escrevia a lista uma segunda
     * vez, e a cobertura real era menor do que o padrão parecia dizer.
     *
     * A quinta forma é a exceção, e a exclusão é uma propriedade do padrão e não uma
     * omissão: `ECHO_SHAPES[4]` captura só uma variável crua — `echo $x;` —, e
     * `esc_json()` é uma chamada. Ela não pode alimentar um `JSON.parse()` reprovável,
     * porque a forma inteira não comporta uma.
     */
    #[Test]
    public function testEveryEchoShapeThatCanCarryAJsonEscaperIsReported(): void
    {
        $policy = EscapingPolicy::default();

        $this->assertNotEmpty(PhpExpression::ECHO_SHAPES, 'uma lista vazia passaria sem conferir nada');

        $forms = [
            '<?= esc_json($cfg) ?>',
            '<?php echo esc_json($cfg) ?>',
            '<?php print(esc_json($cfg)) ?>',
            '<?php print esc_json($cfg) ?>',
            '<?php if ($q) { echo esc_json($cfg) ?>',
        ];

        // Cinco das seis formas estão na lista acima. A que falta é o `echo ...;`
        // de statement solto, e ela não pode ser testada aqui: o valor precisa
        // começar com `$`, então uma chamada de escaper nunca cabe nela. É o
        // motivo de a lista ter um item a menos que a de formas, e não um
        // buraco no teste.
        $this->assertCount(count($forms) + 1, PhpExpression::ECHO_SHAPES, 'a lista de formas mudou de tamanho');

        foreach ($forms as $index => $form) {
            $this->assertNotNull(
                EscapingChecks::jsonParseEscaper('var c = JSON.parse(\'' . $form . '\');', $policy),
                "a forma de saída {$index} alimenta um JSON.parse() e não foi conferida"
            );
        }
    }

    /**
     * A lista de quem devolve JSON é percorrida, não citada.
     *
     * O teste anterior citava `json_encode` e `esc_json` por nome, e por isso não
     * falharia se um terceiro nome devolvesse JSON. `jsonParseEscaper()` não reprova
     * quem devolve JSON — reprova o escaper que devolve JSON, porque `json_encode()`
     * parseia e `esc_json()` não —, então este teste percorre os dois lados: todo
     * escaper que devolve JSON reprova, e um escaper que não devolve JSON passa.
     */
    #[Test]
    public function testEveryJsonEmittingEscaperIsCaughtAndEveryOtherEscaperIsNot(): void
    {
        $policy = EscapingPolicy::default();

        $this->assertNotEmpty($policy->jsonEscapers);

        foreach ($policy->escapers as $escaper) {
            $snippet = EscapingChecks::jsonParseEscaper('var c = JSON.parse(\'<?= ' . $escaper . '($cfg) ?>\');', $policy);
            $expected = $policy->returnsJson($escaper) ? $snippet : null;

            $this->assertSame(
                $expected,
                $snippet,
                "{$escaper} devolve JSON: " . ($policy->returnsJson($escaper) ? 'deveria reprovar' : 'não deveria reprovar')
            );
        }
    }

    /**
     * Um nome nas duas listas de fonte e de escaper é um bug de escrita, e não uma
     * questão de quem ganha.
     *
     * A invariante estava afirmada em três comentários e aplicada em lugar nenhum.
     * Hoje ela é conferida na construção da política, e a falha é na construção: um
     * nome que está nos dois lados reprovaria ou passaria conforme a ordem das
     * regras, e a ordem muda quando alguém precisa mexer nela.
     */
    #[Test]
    public function testAPolicyWithTheSameNameAsAnEscaperAndASourceIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new EscapingPolicy(
            escapers: ['esc', 'segment'],
            helpers: [],
            sources: ['segment'],
            preRendered: [],
        );
    }

    #[Test]
    public function testAPolicyWithTheSameNameAsAHelperAndASourceIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new EscapingPolicy(
            escapers: [],
            helpers: ['count', 'segment'],
            sources: ['segment'],
            preRendered: [],
        );
    }

    /**
     * A ordem das regras é um dado, e o teste a fixa inteira.
     *
     * A lista é conferida como lista, e não caso a caso, porque o que este arquivo
     * trava é a ordem. Um gate com as mesmas sete regras em outra sequência aprova
     * exatamente o que a sequência errada aprova, e nenhum teste de desfecho pega
     * isso: os desfechos são os mesmos na maioria das expressões. O que muda é o
     * trecho do relatório e a aprovação que não deveria existir, e é para isso que
     * servem os casos de paragem abaixo.
     */
    #[Test]
    public function testTheRulesAreReadInTheOrderThePolicyDeclares(): void
    {
        $this->assertSame(
            ['preRendered', 'literalOrCast', 'bareValue', 'ternary', 'concatenation', 'functionCall', 'unreadable'],
            EscapingPolicy::default()->ruleOrder
        );
    }

    /**
     * `unreadable` é a última, e reprova sem condição.
     *
     * A regra é a rede do gate: ela cobre o que nenhuma das outras sabe ler. Rede
     * que pode não se aplicar não é rede, e por isso ela não devolve null nunca — o
     * que também quer dizer que ela tem de vir depois de todas as outras, senão
     * reprovaria a primeira expressão que aparecesse e o gate deixaria de examinar
     * qualquer coisa.
     */
    #[Test]
    public function testTheUnreadableRuleIsLastAndAlwaysReports(): void
    {
        $order = EscapingPolicy::default()->ruleOrder;
        $this->assertSame('unreadable', end($order));

        $last = $this->ruleNamed($order[count($order) - 1], EscapingPolicy::default());
        $verdict = $last->judge('$x < 5 ? $a : $b', EscapingPolicy::default());

        $this->assertNotNull($verdict, 'a rede não pode recusar a expressão');
        $this->assertTrue($verdict->isReport());
    }

    /**
     * `preRendered` antes de `bareValue`: `$topo` é uma variável crua.
     *
     * Invertidas as duas, o achado de markup pronto vira achado de XSS, e o relatório
     * passa a dizer que há uma falha onde o valor é markup pronto por desenho. O
     * conserto errado para esse sintoma é apagar `$topo` da lista de pré-renderizados,
     * que é o que transforma um bug de configuração em XSS de verdade.
     *
     * As duas regras não são vizinhas na lista — `literalOrCast` está no meio —, e
     * por isso a mutação aqui é a lista inteira com as duas trocadas de lugar, e não
     * a troca de um par vizinho. Um par não vizinho ainda pode decidir: basta que a
     * regra do meio não se aplique, e `(int)` não se aplica a `$topo`.
     */
    #[Test]
    public function testBareValueMustNotComeBeforePreRendered(): void
    {
        $order = EscapingPolicy::default()->ruleOrder;

        $this->assertNull($this->judgeWith($order, '$topo'));
        $this->assertSame(
            '$topo',
            $this->judgeWith(
                ['bareValue', 'preRendered', 'literalOrCast', 'ternary', 'concatenation', 'functionCall', 'unreadable'],
                '$topo'
            )
        );
    }

    /**
     * `ternary` antes de `concatenation`: o `?` e o `:` de um ternário não são
     * operadores de concatenação.
     *
     * Invertidas as duas, `$a ? $b . $c : $d` é cortado no ponto, e o corte devolve
     * `$a ? $b` e `$c : $d` — o reprovado passa a ser a condição do ternário, e o
     * relatório entrega ao autor uma linha que ele não consegue corrigir.
     *
     * `$a ? esc($b) : $c`, o exemplo da versão anterior deste arquivo, também não
     * prova nada: o `?` de um ternário sem concatenação dentro não faz
     * `splitTopLevel()` dividir, e as duas ordens devolvem `$c`.
     */
    #[Test]
    public function testConcatenationMustNotComeBeforeTernary(): void
    {
        $order = EscapingPolicy::default()->ruleOrder;

        $this->assertSame('$b , $c , $d', $this->judgeWith($order, '$a ? $b . $c : $d'));
        $this->assertSame(
            '$a ? $b , $c : $d',
            $this->judgeWith($this->movedUp('concatenation'), '$a ? $b . $c : $d')
        );
    }

    /**
     * `concatenation` antes de `functionCall`: `esc($a) . foo($b)` termina em
     * parêntese, e o nome da chamada é `esc`.
     *
     * Invertidas as duas, o escaper da frente responde pelo lado nu, e o gate aprova a
     * linha inteira com `$b` e `$c` saindo crus. É o XSS mais fácil de escrever e o
     * mais difícil de ver, porque a linha tem um escaper nela.
     *
     * O exemplo que `Rules` usava antes, `esc($a) . $y`, foi medido e não prova nada:
     * `$y` faz a expressão não terminar em parêntese, `isFunctionCall()` devolve null
     * nas duas ordens, e o resultado é `$y` nos dois casos.
     */
    #[Test]
    public function testFunctionCallMustNotComeBeforeConcatenation(): void
    {
        $order = EscapingPolicy::default()->ruleOrder;

        $this->assertSame('$b', $this->judgeWith($order, 'esc($a) . foo($b)'));
        $this->assertNull($this->judgeWith($this->movedUp('functionCall'), 'esc($a) . foo($b)'));
        $this->assertSame('$b , $c', $this->judgeWith($order, 'esc($a) . $b . foo($c)'));
    }

    /**
     * A lista de regras tem exatamente três posições que decidem alguma coisa, e este
     * teste é o que as nomeia.
     *
     * Puxar cada regra uma posição para cima e medir o que muda no corpus dá três
     * respostas, e as três são a documentação de `EscapingPolicy::RULES` com prova
     * anexa. A primeira troca o trecho reportado, a segunda aprova uma linha que tem
     * um escaper nela, e a terceira troca o trecho de um caso de fallback.
     *
     * O valor deste teste é o contrapositivo: uma regra nova, ou uma movida, entra na
     * lista sem que ninguém decida onde, e a lista é lida sem teste de nada. Aqui
     * qualquer posição nova que passe a decidir aparece como uma linha a mais no
     * relatório do teste, e a decisão deixa de ser uma escolha invisível.
     */
    #[Test]
    public function testOnlyThreeRulePositionsDecideAnything(): void
    {
        $order = EscapingPolicy::default()->ruleOrder;
        $deciding = [];

        for ($i = 1, $total = count($order); $i < $total; $i++) {
            foreach (self::PRECEDENCE_CORPUS as $expr) {
                $declared = $this->judgeWith($order, $expr);
                $moved = $this->judgeWith($this->movedUp($order[$i]), $expr);

                if ($declared !== $moved) {
                    $deciding[] = "{$order[$i]} antes de {$order[$i - 1]}: "
                        . var_export($declared, true) . ' vira ' . var_export($moved, true);
                    break;
                }
            }
        }

        $this->assertSame([
            'concatenation antes de ternary: \'$b , $c , $d\' vira \'$a ? $b , $c : $d\'',
            'functionCall antes de concatenation: \'$b\' vira NULL',
            'unreadable antes de functionCall: \'$c\' vira \'esc($b) , $c\'',
        ], $deciding);
    }

    /**
     * O corpus que o teste de precedência percorre.
     *
     * Cada linha existe por um motivo, e a lista é curta de propósito: um corpus grande
     * faria toda troca parecer importante, que é o oposto de informar. A primeira linha
     * de cada grupo é a expressão em que duas regras se aplicam ao mesmo tempo; as outras
     * são o caso comum, que nenhuma troca pode quebrar.
     */
    private const PRECEDENCE_CORPUS = [
        // preRendered x bareValue
        '$topo',
        '$os->defeito',
        // ternary x concatenation
        '$a ? $b . $c : $d',
        '$a ? $b : $c . $d',
        '$a ? esc($b) : $c',
        // concatenation x functionCall
        'esc($a) . foo($b)',
        'esc($a) . $y',
        'esc($a)',
        'foo()',
        '(int) $x',
        'esc($a) . esc($b)',
        '$a . $b',
    ];

    /**
     * A lista de regras com uma delas puxada uma posição para cima.
     *
     * É a mutação que um autor faz sem querer ao inserir uma regra no lugar "que
     * parecia certo", e ela é direcional por natureza: puxar `functionCall` para cima
     * põe a chamada antes da concatenação, e é isso que o gate precisa proibir.
     *
     * Uma troca de posições seria simétrica e não diria nada — `swapped('a', 'b')` e
     * `swapped('b', 'a')` são a mesma ordem, e o teste passaria a provar que o injetor
     * funciona em vez de que a ordem importa.
     *
     * @return list<string>
     */
    private function movedUp(string $name): array
    {
        $order = EscapingPolicy::default()->ruleOrder;
        $at = array_search($name, $order, true);

        $this->assertIsInt($at, "{$name} não está na lista de regras");
        $this->assertGreaterThan(0, $at, "{$name} já é a primeira regra, e não há para cima");

        return array_values(array_merge(
            array_slice($order, 0, $at - 1),
            [$name, $order[$at - 1]],
            array_slice($order, $at + 1)
        ));
    }

    /**
     * A política do gate com a lista de regras trocada, e nada mais mudado.
     */
    private function policyWithOrder(array $order): EscapingPolicy
    {
        $policy = EscapingPolicy::default();

        return new EscapingPolicy(
            escapers: $policy->escapers,
            helpers: $policy->helpers,
            sources: $policy->sources,
            preRendered: $policy->preRendered,
            jsonEscapers: $policy->jsonEscapers,
            ruleOrder: $order,
        );
    }

    /**
     * O veredito de `unescaped()` sob uma lista de regras específica.
     */
    private function judgeWith(array $order, string $expr): ?string
    {
        return EscapingChecks::unescaped($expr, $this->policyWithOrder($order));
    }

    /**
     * A regra de um nome, pela lista da política.
     */
    private function ruleNamed(string $name, EscapingPolicy $policy): Rule
    {
        foreach ($policy->rules() as $rule) {
            if ($rule->name === $name) {
                return $rule;
            }
        }

        $this->fail("{$name} não está na lista de regras");
    }
}
