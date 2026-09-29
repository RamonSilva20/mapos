<?php

namespace Tools\ViewEscaping;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

/**
 * A allowlist de função segura, a lista de fontes de requisição, as variáveis de
 * markup pronto, a lista de quem devolve JSON, e a ORDEM das regras.
 *
 * É o conteúdo do julgamento, e a frase é exata desde que a ordem esteja aqui: um
 * valor só passa se a função estiver na allowlist, um markup pronto só é seguro se a
 * variável estiver na lista de pré-renderizados, e um dos dois lados de uma expressão
 * composta só é seguro se a regra que o examina vier antes da que o engoliria. O
 * motivo delas é consultável: quem quiser saber por que `strtoupper()` passou a ser
 * achado tem um lugar para ler.
 *
 * A forma como a ordem deixa de ser o corpo de sete `if` consecutivos dentro de
 * `EscapingChecks::unescaped()` está em `RULES`, e a razão de cada paragem está nos
 * comentários dela. É a parte dessa classe que mais mudou, e a que mais aparece no
 * relatório quando dá errado: uma ordem trocada não dá erro, dá um veredito
 * diferente.
 *
 * As formas de saída do PHP que o gate varre NÃO estão aqui, e a exclusão é o
 * ponto: quais formas existem, e se a captura de cada uma já vem fechada pela tag
 * de fim, são fatos sobre a linguagem, e ficam em `PhpExpression::ECHO_SHAPES`. O
 * que está dentro da forma é julgamento, e é isso que esta classe guarda. A lista
 * morava aqui, e aqui ela parecia configurável — a tal ponto que um teste montava
 * uma política com `echoShapes: []`, num campo que decide o que é varrido, e a
 * provava vazio.
 *
 * São três decisões, e elas não se misturam. Um TRANSFORMADOR devolve algo que já
 * era seguro — a saída de `date('d/m', $quando)` vem da string de formato, não do
 * valor, e por isso os argumentos são conferidos um a um. Uma FONTE devolve a
 * requisição, e isso é verdade do retorno, não dos argumentos: os argumentos de
 * `$this->input->get('campo')` são literais, então inspecioná-los aprova
 * exatamente a expressão que a fonte existe para reprovar. Por isso a fonte é
 * conferida antes da allowlist e reprova a expressão inteira, e por isso ela é
 * uma lista só sua em vez de uma ausência na allowlist.
 *
 * É um valor, e não um punhado de constantes, por causa da regra que separa
 * escapers de helpers. Uma lista só permitiria que QUALQUER método cujo nome
 * colidisse com um escaper fosse tratado como o escaper: `PhpExpression::calleeName()`
 * devolve o último pedaço do caminho, porque é isso que faz `$this->load->view()`
 * casar com o helper `view`. Com uma lista única, `$row->esc($x)` casava com `esc`
 * e passava pelo gate, sendo um método qualquer cujo nome por acaso colide. Medido
 * antes de virar código, com o gate real: `$row->esc($x)`, `$this->esc($x)` e
 * `$obj->htmlspecialchars($x)` saíam todos como aprovados.
 *
 * Os prefixos das chaves de achado continuam em constante. A chave de baseline é
 * "caminho|trecho" para as três conferências que varrem as formas de saída, e
 * "caminho|prefixo trecho" para as de arquivo inteiro, e o prefixo é o que separa
 * as conferências dentro dela; se ele vivesse no gerador do relatório, um achado
 * novo poderia nascer com um prefixo que o relatório não conhece. Constante, e não
 * propriedade, porque não faz parte do julgamento e não tem o que ser injetado.
 *
 * O texto de cada seção mora em `Report`, e não aqui: o prefixo é o que liga os
 * dois, e `ReportTest` confere que todo prefixo desta classe tem uma seção lá. É
 * a mesma divisão de antes, entre o que é julgamento e o que é texto, e ela
 * também não é negociável num gate em que a orientação errada manda a pessoa
 * corrigir a coisa certa no lugar errado.
 *
 * (Nota para quem editar: nunca escreva uma tag de fechamento do PHP num
 * comentário `//` aqui. Ela encerra o modo PHP mesmo dentro do comentário, e o
 * resto do arquivo vira saída impressa.)
 */
final readonly class EscapingPolicy
{
    public const JSON_PARSE_PREFIX = 'json-parse: ';

    public const PRE_RENDERED_PREFIX = 'prerendered-esc: ';

    public const UNRECOGNIZED_OUTPUT_PREFIX = 'unrecognized-output: ';

    /**
     * A ordem das regras, resolvida na construção.
     *
     * Não é um parâmetro promovido porque a promotion já conta como inicialização, e
     * uma propriedade readonly só pode ser inicializada uma vez — `$this->ruleOrder ??=
     * self::RULES` em cima de um parâmetro promoted é um `Error` em tempo de
     * execução, e não uma decisão sobre o default.
     *
     * @var list<string>
     */
    public array $ruleOrder;

    /**
     * @param  list<string>  $escapers  nomes que só são o escaper quando chamados como função
     * @param  list<string>  $helpers  nomes de função segura, na forma de função ou de método
     * @param  list<string>  $sources  nomes cujo retorno É a requisição, e não algo derivado dela
     * @param  list<string>  $preRendered  variáveis que carregam markup pronto
     * @param  list<string>  $jsonEscapers  nomes cujo retorno é JSON, e por isso são os únicos que alguém escreveria dentro de um `JSON.parse()`
     * @param  list<string>|null  $ruleOrder  a ordem das regras, ou null para a de `RULES`
     */
    public function __construct(
        public array $escapers,
        public array $helpers,
        public array $sources,
        public array $preRendered,
        public array $jsonEscapers = self::JSON_ESCAPERS,
        ?array $ruleOrder = null,
    ) {
        $this->ruleOrder = $ruleOrder ?? self::RULES;

        // Um nome nas duas listas seria aprovado e reprovado ao mesmo tempo,
        // dependendo de qual regra rodasse primeiro. Três comentários diferentes
        // nesta classe diziam que isso não acontecia; nenhum deles conferia. Aqui ele
        // é conferido, e a falha é na construção da política — onde o erro de escrita
        // está — em vez de no relatório, onde ele apareceria como um `segment()`
        // aprovado num caso e reprovado no outro.
        $this->refuseOverlap($this->escapers, $this->sources, 'Escaper e fonte');
        $this->refuseOverlap($this->helpers, $this->sources, 'Helper e fonte');

        // Uma regra na lista sem corpo é uma regra que reprovaria tudo, ou nada, e
        // uma regra com corpo fora da lista é código morto. As duas direções falham
        // aqui, na construção, e não no meio de uma varredura de 107 views.
        foreach ($this->ruleOrder as $name) {
            if (! is_callable([Rules::class, $name])) {
                throw new InvalidArgumentException("Regra \"{$name}\" na lista não existe em Rules.");
            }
        }

        $orphans = array_diff(
            array_map(
                fn (ReflectionMethod $method): string => $method->getName(),
                (new ReflectionClass(Rules::class))->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC)
            ),
            $this->ruleOrder
        );

        if ($orphans !== []) {
            throw new InvalidArgumentException(
                'Regra em Rules fora da lista, que nunca seria lida: ' . implode(', ', $orphans)
            );
        }
    }

    /**
     * Recusa dois conjuntos com um nome em comum, e diz qual era o par.
     *
     * @param  list<string>  $one
     * @param  list<string>  $other
     */
    private function refuseOverlap(array $one, array $other, string $what): void
    {
        $both = array_intersect($one, $other);

        if ($both !== []) {
            throw new InvalidArgumentException(
                "{$what} com o mesmo nome, decidido por ordem de verificação: " . implode(', ', $both)
            );
        }
    }

    /**
     * A política que o `composer xss:check` roda.
     *
     * O construtor é público para que um teste possa montar uma política menor sem
     * editar o arquivo: o gate inteiro é função pura sobre string, e o jeito de
     * exercitar um caso é uma lista, não um monkey-patch num global.
     */
    public static function default(): self
    {
        return new self(
            escapers: self::ESCAPERS,
            helpers: self::HELPERS,
            sources: self::SOURCES,
            preRendered: self::PRE_RENDERED,
            jsonEscapers: self::JSON_ESCAPERS,
        );
    }

    /**
     * Os nomes cujo retorno é JSON.
     *
     * Entra no `JSON.parse()` e sai como string, que é o tipo que o `JSON.parse()`
     * recusa — daí a conferência separada. O que importa aqui é a PROPRIEDADE, e
     * não o nome: `esc_json()` é o único escaper do projeto que devolve JSON, e
     * `json_encode()` é a única função livre que devolve JSON. A lista estava
     * escrita à mão dentro de uma regex em `EscapingChecks::jsonParseEscaper()`, o
     * que fazia duas coisas erradas de uma vez: o nome do escaper era repetido
     * literal em dois lugares do padrão, e a lista de bypass ficava fora de onde as
     * outras listas vivem.
     */
    private const JSON_ESCAPERS = ['esc_json', 'json_encode'];

    /**
     * A ORDEM das regras, que é a decisão mais sensível do gate.
     *
     * Antes esta ordem eram sete `if` seguidos dentro de `unescaped()`, e nenhuma
     * classe era dona dela. Uma ordem que ninguém possui é uma ordem que cada autor
     * reescreve do jeito que entende, e o sintoma disso é um par de regras trocadas
     * que só se nota num caso que ninguém tinha visto. Por isso aqui: uma lista de
     * NOMES, lida de cima para baixo por `EscapingChecks::unescaped()`.
     *
     * A lista é um construtor com default, e não só uma constante, porque o teste de
     * precedência precisa montar a política com a ordem trocada. Com a ordem presa em
     * constante, esse teste teria de reescrever os `if` — ou seja, exercitar um código
     * que não é o do gate, que é como toda mutação manual falha em silêncio.
     *
     * Das vinte e uma ordens possíveis, três pares colidem de verdade — isto é, existem
     * expressões em que as duas regras se aplicam ao mesmo tempo e o resultado depende
     * de qual vem primeiro. Os três, e a expressão que prova cada um:
     *
     *   1. `preRendered` antes de `bareValue` — `$topo` é uma variável crua, então
     *      invertidas as duas o markup pronto vira achado de XSS.
     *   2. `ternary` antes de `concatenation` — em `$a ? $b . $c : $d` o ponto e
     *      vírgula está no ramo do ternário, e o corte por concatenação partiria a
     *      linha no meio de uma escolha, reprovando o `$a` e perdendo os ramos.
     *   3. `concatenation` antes de `functionCall` — `esc($a) . foo($b)` termina em
     *      parêntese, e invertidas as duas o escaper da frente responderia pelo lado
     *      nu: a linha inteira passaria aprovada.
     *
     * As outras posições não colidem com nada, e estão onde estão por leitura, não por
     * prova. Dizer isso aqui é melhor que inventar um exemplo para cada uma: um
     * comentário de precedência com um caso que não existe é pior do que um comentário
     * a menos, porque o próximo autor procura um caso que não aparece.
     */
    private const RULES = [
        'preRendered',
        'literalOrCast',
        'bareValue',
        'ternary',
        'concatenation',
        'functionCall',
        'unreadable',
    ];

    /**
     * A lista de regras, na ordem em que devem ser lidas.
     *
     * Só a ordem mora nesta classe; os corpos ficam em `Rules`, e cada item da lista
     * aponta para o método de mesmo nome. `Rules` não sabe a ordem, que é o inverso da
     * divisão que existia antes, quando a ordem e o corpo estavam no mesmo bloco de
     * sete `if`.
     *
     * @return list<Rule>
     */
    public function rules(): array
    {
        return array_map(
            fn (string $name): Rule => new Rule($name, Closure::fromCallable([Rules::class, $name])),
            $this->ruleOrder
        );
    }

    /**
     * O nome devolve JSON, e por isso pode aparecer dentro de um `JSON.parse()`?
     *
     * Esta é a única leitura de `jsonEscapers`. Havia também um
     * `jsonEscaperPattern()`, que compunha a mesma lista numa alternância de
     * regex, e ele foi removido por não ter chamador: a conferência de
     * `JSON.parse()` em `EscapingChecks` pergunta nome a nome se o escaper
     * devolve JSON, justamente para não precisar de regex, e nenhuma das
     * conferências restantes compõe padrão a partir desta lista.
     */
    public function returnsJson(string $name): bool
    {
        return in_array($name, $this->jsonEscapers, true);
    }

    /**
     * A chamada devolve a requisição, e não algo derivado dela?
     *
     * A resposta reprova a EXPRESSÃO, e não apenas a chamada, e a diferença é o
     * ponto inteiro do método: uma fonte tem argumentos literais — o nome do
     * campo — e uma conferência que aprova argumentos literais aprovaria
     * `<?= $this->input->post('campo') ?>` sem olhar para dentro uma vez sequer.
     *
     * Por isso esta lista é conferida antes de `approvesCall()`, e não como uma
     * ausência silenciosa dentro dela. A ausência seria indistinguishable de
     * "ninguém pensou nesse nome": um helper novo na allowlist e uma fonte nova
     * aqui divergiriam só no relatório, que é o pior lugar para uma divergência
     * aparecer.
     */
    public function isSource(string $ref): bool
    {
        return in_array(PhpExpression::calleeName($ref), $this->sources, true);
    }

    /**
     * A chamada é de uma função que a política considera segura?
     *
     * A resposta é o ponto inteiro da ferramenta, então ela mora num lugar só e
     * ninguém mais reconstrói a regra. É uma ALLOWLIST: a versão anterior listava
     * todo helper de string que já tinha visto (strtoupper, ucfirst, nl2br, trim,
     * str_replace, xss_clean, ...) e tratava a simples presença de um deles como
     * prova de que o valor estava escapado. Nenhum deles escapa: envolver uma
     * coluna em strtoupper() passava o gate sendo um XSS funcionando, e o mesmo
     * acontecia com xss_clean(), que o AGENTS.md documenta como filtro de entrada e
     * não como encoder.
     *
     * O que não está na lista é percorrido argumento por argumento, e isso faz a
     * conferência falhar fechada: um helper novo é um achado, não um silêncio.
     *
     * Nenhuma fonte chega aqui: `isSource()` já reprovou a expressão. A lista de
     * fontes é o que impede que um nome esteja nos dois lados ao mesmo tempo.
     */
    public function approvesCall(string $ref): bool
    {
        $name = PhpExpression::calleeName($ref);

        // Um escaper na forma de método é um método qualquer com o nome do escaper.
        // `PhpExpression::isMethodCall()` é o que separa `esc($x)` de `$row->esc($x)`.
        if (in_array($name, $this->escapers, true)) {
            return ! PhpExpression::isMethodCall($ref);
        }

        return in_array($name, $this->helpers, true);
    }

    /**
     * A variável é markup pronto e pode sair crua?
     */
    public function isPreRendered(string $expr): bool
    {
        return in_array($expr, $this->preRendered, true);
    }

    /**
     * Alternância de regex com os nomes de escaper.
     *
     * Vem da mesma lista que `approvesCall()` usa, e não de uma segunda escrita,
     * pelo mesmo motivo de `preRenderedPattern()`: a conferência de
     * pronto-renderizado-escapado precisa saber o que é escaper, e a lista que
     * estava escrita à mão ali omitia cinco dos nove nomes — sem erro, sem aviso e
     * sem teste, que é a forma de divergência que só aparece quando alguém
     * acrescenta um décimo escaper.
     *
     * A alternância sai sem agrupamento; quem compõe o regex é quem decide onde
     * ela entra. O mesmo contrato de `preRenderedPattern()`.
     */
    public function escaperPattern(): string
    {
        return implode('|', array_map(fn ($name) => preg_quote($name, '/'), $this->escapers));
    }

    /**
     * Alternância de regex com as variáveis de markup pronto.
     *
     * Vem da mesma lista que `isPreRendered()` usa, e não de uma segunda escrita,
     * porque as duas regras precisam concordar: uma entrada só é segura se for
     * emitida crua E não for escapada por engano, e duas listas que divergem não
     * dão erro.
     */
    public function preRenderedPattern(): string
    {
        return implode('|', array_map(fn ($v) => preg_quote($v, '/'), $this->preRendered));
    }

    /**
     * Variáveis que carregam markup pronto, para emitir cru.
     *
     * Vêm de $this->load->view($name, $data, true) ou de markup montado à mão
     * pelo controller. Escapar uma delas não é redundante, é destrutivo:
     * htmlspecialchars() transforma o markup em texto visível e neutraliza
     * qualquer script aninhado, desligando sem erro nenhum o que aquela view foi
     * incluir para fazer.
     */
    private const PRE_RENDERED = ['$topo', '$custom_error', '$modalGerarPagamento'];

    /**
     * Nomes que tornam dado de usuário seguro sozinhos.
     *
     * Todos são funções livres do projeto ou do PHP, e nenhum deles é um método, e é
     * por isso que a forma da chamada é testada antes do nome. A lista é curta de
     * propósito: um escaper que não escapa seria pior do que nenhum escaper.
     */
    private const ESCAPERS = [
        'esc', 'esc_json', 'esc_url', 'esc_img_src', 'esc_css', 'esc_msg',
        'html_escape', 'htmlspecialchars', 'printSafeHtml',
    ];

    /**
     * Nomes cujo retorno é a requisição, e não algo derivado dela.
     *
     * Toda esta lista devolve texto que entrou no servidor por outra porta que não
     * fosse a view, e por isso nenhuma delas pode devolver HTML já escapado: a
     * página que o atacante pede é a página que devolve o valor.
     *
     * A lista é de NOMES, e não de caminhos, porque é assim que `calleeName()`
     * funciona — o mesmo mecanismo que faz `$this->load->view()` casar com o helper
     * `view`. O preço é que um nome genérico aqui reprova um homônimo que não seja
     * fonte; esse erro é um achado a mais, e não um silêncio, que é a direção
     * certa para um gate de segurança.
     *
     * Fica de fora o que a requisição alimenta mas não pode carregar markup: um
     * `request_method()` devolve só um token de um conjunto fixo, e tratá-lo como
     * fonte é o mesmo erro de tratar `intval()` como transformador em vez de
     * coerção.
     */
    private const SOURCES = [
        // CI_Input, os getters que devolvem texto que veio na requisição.
        'get', 'get_post', 'post', 'post_get', 'get_cookie', 'cookie', 'cookie_userdata',
        'get_browser', 'get_query_string', 'get_primary_uri_string', 'server', 'ip_address',

        // CI_URI. Um segmento de URI É o caminho da requisição, e é por isso que
        // `segment` saiu daqui: ele estava na allowlist com o comentário de que
        // URLs são "configuração que a própria aplicação define", e isso é falso
        // para o caminho. `current_url()` anexa a query string crua, que é o
        // ataque mais direto dos três.
        'segment', 'slash_segment', 'uri_string', 'ruri_string', 'current_url',

        // CI_Session. Flashdata é o mesmo caso de userdata com outra porta de
        // entrada, e ficá-lo de fora seria o mesmo defeito que esta lista conserta.
        'userdata', 'flashdata', 'flashdata_old',

        // CI4 / PSR-7, para o dia em que a base trocar de framework.
        'getVar', 'getHeader',
    ];

    /**
     * Nomes cujo retorno já é seguro, ou é markup por desenho, na forma de função
     * ou de método.
     *
     * Estar aqui é o motivo de `calleeName()` existir. Quatro destes são chamadas de
     * método do CI3 nos templates — create_links() em 16 views, view() em 2,
     * count_all() em 1 — e é por isso que o nome sozinho não basta para reprovar:
     * casar o último pedaço do caminho é o que faz `$this->load->view()` encontrar
     * o helper `view`.
     *
     * Um nome daqui nunca pode estar também em `SOURCES`. Os dois lados decidindo o
     * mesmo nome é o que faria `segment()` ser ao mesmo tempo aprovado e
     * reprovado, dependendo de qual lista o próximo autor lembrar de ler.
     */
    private const HELPERS = [
        // Coerção numérica: o retorno é int/float/bool, então nenhum markup sobrevive.
        'abs', 'ceil', 'floor', 'intval', 'round', 'strtotime', 'count',
        'array_sum', 'array_column',

        // Formatação: a saída vem da format string, não do valor.
        'date', 'dateInterval', 'date_create', 'date_format', 'number_format', 'money_format',

        // Reflexão: um nome de classe, nunca um valor de requisição.
        'get_class',

        // URLs e configuração que a própria aplicação define. Só a base: o
        // caminho e a query string saíram para SOURCES, porque ambos vêm de fora.
        'base_url', 'site_url', 'index_page',
        'config_item', 'get_csrf', 'get_csrf_hash', 'get_csrf_token_name',

        // Helpers de form do CI3: form_prep() escapa o valor por dentro.
        'form_open', 'form_close', 'form_input', 'form_password', 'form_hidden',
        'form_dropdown', 'form_checkbox', 'form_submit', 'form_button', 'form_label',
        'set_value', 'set_select',

        // Produzem markup ou uma contagem, não texto de usuário. create_links() é
        // seguro por construção: os números de página são (int)-castados e todo
        // valor de query passa por http_build_query(), que percent-encode as aspas.
        'view', 'count_all', 'create_links', 'create_nprev', 'create_next',
    ];
}
