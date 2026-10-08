<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Mapa de permissões das rotas do painel (#2866).
 *
 * O MY_Controller nega todo método público que não esteja em
 * application/config/permissions_map.php. Estes testes garantem as três
 * coisas que tornam isso seguro de manter:
 *
 * 1. todo método público de todo controller do painel está no mapa, então
 *    uma rota nova sem mapeamento quebra o teste em vez de ficar negada em
 *    produção sem ninguém perceber;
 * 2. a regra do mapa é a mesma que o método já verifica no próprio corpo,
 *    então o mapa não libera nem bloqueia nada além do que já era bloqueado;
 * 3. a negação responde do jeito combinado, em HTML e em AJAX.
 *
 * Os controllers são lidos como tokens e não carregados: incluí-los exigiria
 * o framework inteiro, e Os.php e Vendas.php ainda disparam deprecations do
 * PHP 8.2 na compilação.
 */
final class PermissionsMapTest extends MaposTestCase
{
    /**
     * Métodos cuja checagem no corpo não é uma barreira de entrada, e sim um
     * filtro do que aparece na tela. O método é aberto a qualquer usuário
     * logado.
     */
    private const FILTRAM_EM_VEZ_DE_BARRAR = [
        'Mapos::pesquisar',
    ];

    private static ?array $mapa = null;

    private static ?array $controllers = null;

    /**
     * Nenhum teste aqui usa banco, então não abre o SQLite da classe base.
     */
    protected function setUp(): void
    {
    }

    private static function mapa(): array
    {
        if (self::$mapa === null) {
            $config = [];
            include APPPATH . 'config' . DIRECTORY_SEPARATOR . 'permissions_map.php';
            self::$mapa = $config['permissions_map'];
        }

        return self::$mapa;
    }

    /**
     * Controllers que estendem MY_Controller, com os métodos públicos e o
     * corpo de cada um.
     *
     * @return array<string, array<string, string>> controller => [método => corpo]
     */
    private static function controllers(): array
    {
        if (self::$controllers !== null) {
            return self::$controllers;
        }

        self::$controllers = [];
        foreach (glob(APPPATH . 'controllers' . DIRECTORY_SEPARATOR . '*.php') as $arquivo) {
            $codigo = file_get_contents($arquivo);
            if (! preg_match('/class\s+(\w+)\s+extends\s+MY_Controller\b/', $codigo, $m)) {
                continue;
            }
            self::$controllers[$m[1]] = self::metodosPublicos($codigo);
        }

        return self::$controllers;
    }

    /**
     * Métodos declarados direto na classe (closures ficam de fora), com o
     * corpo. O construtor entra com a chave __construct.
     *
     * @return array<string, string>
     */
    private static function metodosPublicos(string $codigo): array
    {
        $tokens = token_get_all($codigo);
        $total = count($tokens);
        $metodos = [];
        $profundidade = 0;

        for ($i = 0; $i < $total; $i++) {
            $token = $tokens[$i];

            if ($token === '{' || (is_array($token) && in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                $profundidade++;

                continue;
            }
            if ($token === '}') {
                $profundidade--;

                continue;
            }

            // Métodos da classe ficam na profundidade 1; closures, mais fundo.
            if (! is_array($token) || $token[0] !== T_FUNCTION || $profundidade !== 1) {
                continue;
            }

            $visibilidade = 'public';
            $estatico = false;
            for ($j = $i - 1; $j >= 0; $j--) {
                if (! is_array($tokens[$j])) {
                    break;
                }
                if (in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_FINAL, T_ABSTRACT], true)) {
                    continue;
                }
                if ($tokens[$j][0] === T_STATIC) {
                    $estatico = true;

                    continue;
                }
                if (in_array($tokens[$j][0], [T_PUBLIC, T_PROTECTED, T_PRIVATE], true)) {
                    $visibilidade = strtolower($tokens[$j][1]);
                }
                break;
            }

            $k = $i + 1;
            while (! is_array($tokens[$k]) || $tokens[$k][0] !== T_STRING) {
                $k++;
            }
            $nome = $tokens[$k][1];

            while ($tokens[$k] !== '{') {
                $k++;
            }
            $corpo = '';
            $nivel = 0;
            for (; $k < $total; $k++) {
                $t = $tokens[$k];
                $corpo .= is_array($t) ? $t[1] : $t;
                if ($t === '{' || (is_array($t) && in_array($t[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                    $nivel++;
                } elseif ($t === '}') {
                    $nivel--;
                    if ($nivel === 0) {
                        break;
                    }
                }
            }
            // O laço externo retoma depois do corpo, que já foi consumido.
            $i = $k;

            if ($visibilidade === 'public' && ! $estatico) {
                $metodos[$nome] = $corpo;
            }
        }

        return $metodos;
    }

    /**
     * Permissões exigidas pelo corpo: cada checkPermission literal vira um
     * grupo de uma permissão, e cada hasAnyPermission literal vira um grupo
     * com as permissões da lista. Todos os grupos precisam ser satisfeitos.
     *
     * @return list<list<string>>
     */
    private static function gruposNoCorpo(string $corpo): array
    {
        $grupos = [];

        preg_match_all("/checkPermission\\(\\s*[^,]+,\\s*'(\\w+)'\\s*\\)/", $corpo, $m);
        foreach ($m[1] as $permissao) {
            $grupos[] = [$permissao];
        }

        preg_match_all('/hasAnyPermission\(\s*\[([^\]]*)\]\s*\)/', $corpo, $m);
        foreach ($m[1] as $lista) {
            preg_match_all("/'(\\w+)'/", $lista, $p);
            $grupos[] = $p[1];
        }

        return self::normalizarGrupos($grupos);
    }

    /**
     * Converte uma regra do mapa para o mesmo formato de grupos.
     *
     * @return list<list<string>>|string '*' e 'nao-e-rota' para os casos sem permissão
     */
    private static function gruposDaRegra($regra)
    {
        if ($regra === '*') {
            return '*';
        }
        if ($regra === false) {
            return 'nao-e-rota';
        }
        if (is_string($regra)) {
            return [[$regra]];
        }
        if (isset($regra['todas'])) {
            return self::normalizarGrupos(array_map(fn ($p) => [$p], $regra['todas']));
        }

        return self::normalizarGrupos([array_values($regra)]);
    }

    private static function normalizarGrupos(array $grupos): array
    {
        $grupos = array_map(function ($g) {
            $g = array_values(array_unique($g));
            sort($g);

            return $g;
        }, $grupos);
        $grupos = array_values(array_unique($grupos, SORT_REGULAR));
        usort($grupos, fn ($a, $b) => strcmp(implode(',', $a), implode(',', $b)));

        return $grupos;
    }

    public static function rotas(): array
    {
        $casos = [];
        foreach (self::controllers() as $controller => $metodos) {
            foreach (array_keys($metodos) as $metodo) {
                if ($metodo === '__construct') {
                    continue;
                }
                $casos["$controller::$metodo"] = [$controller, $metodo];
            }
        }

        return $casos;
    }

    public function testEncontrouOsControllersDoPainel(): void
    {
        // Proteção contra o próprio teste: se a leitura dos tokens quebrar e
        // não achar nada, todos os outros testes passariam sem conferir nada.
        $this->assertGreaterThanOrEqual(14, count(self::controllers()));
        $this->assertArrayHasKey('gerenciar', self::controllers()['Clientes']);
    }

    #[DataProvider('rotas')]
    public function testTodoMetodoPublicoEstaNoMapa(string $controller, string $metodo): void
    {
        $this->assertArrayHasKey($controller, self::mapa(), "Controller $controller fora do mapa de permissões.");
        $this->assertArrayHasKey(
            $metodo,
            self::mapa()[$controller],
            "$controller::$metodo é público e não está em application/config/permissions_map.php, "
                . 'então seria negado para todos. Declare a permissão da rota no mapa.'
        );
    }

    /**
     * A regra do mapa tem de ser a mesma que o método já verifica.
     *
     * A checagem considerada é a do próprio método; se ele não tem nenhuma e
     * só delega para outro método público (como index() chamando
     * gerenciar()), vale a do método delegado; se ainda assim não houver,
     * vale a do construtor.
     */
    #[DataProvider('rotas')]
    public function testRegraDoMapaBateComAChecagemDoMetodo(string $controller, string $metodo): void
    {
        $metodos = self::controllers()[$controller];
        $regra = self::mapa()[$controller][$metodo] ?? null;
        $this->assertNotNull($regra, "$controller::$metodo fora do mapa.");

        $grupos = self::gruposNoCorpo($metodos[$metodo]);

        if ($grupos === [] && preg_match_all('/\$this->(\w+)\(/', $metodos[$metodo], $m)) {
            foreach ($m[1] as $chamado) {
                if ($chamado !== $metodo && isset($metodos[$chamado])) {
                    $grupos = self::gruposNoCorpo($metodos[$chamado]);
                    if ($grupos !== []) {
                        break;
                    }
                }
            }
        }

        if ($grupos === [] && isset($metodos['__construct'])) {
            $grupos = self::gruposNoCorpo($metodos['__construct']);
        }

        if (in_array("$controller::$metodo", self::FILTRAM_EM_VEZ_DE_BARRAR, true)) {
            $this->assertSame('*', $regra, "$controller::$metodo filtra o conteúdo pela permissão; a rota é aberta.");

            return;
        }

        if ($grupos === []) {
            $this->assertContains(
                self::gruposDaRegra($regra),
                ['*', 'nao-e-rota'],
                "$controller::$metodo não verifica permissão hoje; o mapa deve dizer '*' ou false, "
                    . 'e uma permissão nova merece PR próprio.'
            );

            return;
        }

        $this->assertSame(
            $grupos,
            self::gruposDaRegra($regra),
            "A regra de $controller::$metodo no mapa não bate com a checagem do método."
        );
    }

    public function testMapaNaoTemRotaInexistente(): void
    {
        foreach (self::mapa() as $controller => $metodos) {
            $this->assertArrayHasKey($controller, self::controllers(), "$controller está no mapa mas não existe.");
            foreach (array_keys($metodos) as $metodo) {
                $this->assertArrayHasKey(
                    $metodo,
                    self::controllers()[$controller],
                    "$controller::$metodo está no mapa mas não é método público do controller."
                );
            }
        }
    }

    // ---------------------------------------------------------------------
    // Decisão e resposta do MY_Controller
    // ---------------------------------------------------------------------

    /**
     * @param list<string> $permissoesDoUsuario
     */
    private function controller(array $permissoesDoUsuario, bool $ajax = false): PermissionsMapControllerTestavel
    {
        $controller = $this->makeInstance(PermissionsMapControllerTestavel::class);
        $controller->permission = new class($permissoesDoUsuario) {
            public function __construct(private array $permissoes)
            {
            }

            public function checkPermission($id, $atividade)
            {
                return in_array($atividade, $this->permissoes, true);
            }
        };
        $controller->session = new class() {
            public array $flash = [];

            public function userdata($chave)
            {
                return $chave === 'permissao' ? 1 : null;
            }

            public function set_flashdata($chave, $valor)
            {
                $this->flash[$chave] = $valor;
            }
        };
        $controller->input = new class($ajax) {
            public function __construct(private bool $ajax)
            {
            }

            public function is_ajax_request()
            {
                return $this->ajax;
            }
        };
        $controller->output = new class() {
            public ?int $status = null;

            public ?string $tipo = null;

            public string $corpo = '';

            public bool $enviado = false;

            public function set_status_header($status)
            {
                $this->status = $status;

                return $this;
            }

            public function set_content_type($tipo, $charset = null)
            {
                $this->tipo = $tipo;

                return $this;
            }

            public function set_output($corpo)
            {
                $this->corpo = $corpo;

                return $this;
            }

            public function _display()
            {
                $this->enviado = true;
            }
        };

        return $controller;
    }

    public static function regras(): array
    {
        return [
            'permissao unica, tem' => ['vOs', ['vOs'], true],
            'permissao unica, nao tem' => ['vOs', ['aOs'], false],
            'qualquer uma, tem uma' => [['aOs', 'eOs'], ['eOs'], true],
            'qualquer uma, nao tem nenhuma' => [['aOs', 'eOs'], ['vOs'], false],
            'todas, tem todas' => [['todas' => ['rVenda', 'rOs']], ['rOs', 'rVenda'], true],
            'todas, falta uma' => [['todas' => ['rVenda', 'rOs']], ['rOs'], false],
            'todas vazia' => [['todas' => []], ['rOs'], false],
            'logado' => ['*', [], true],
            'nao e rota' => [false, ['vOs', 'aOs'], false],
            'fora do mapa' => [null, ['vOs'], false],
            'lista vazia' => [[], ['vOs'], false],
            'string vazia' => ['', ['vOs'], false],
        ];
    }

    #[DataProvider('regras')]
    public function testRegraPermite($regra, array $permissoesDoUsuario, bool $esperado): void
    {
        $controller = $this->controller($permissoesDoUsuario);

        $this->assertSame($esperado, $this->invokeMethod($controller, 'regraPermite', [$regra]));
    }

    public function testRegraDaRotaIgnoraMaiusculas(): void
    {
        $controller = $this->controller([]);
        $mapa = ['Clientes' => ['autoCompleteX' => 'vCliente']];

        $this->assertSame('vCliente', $this->invokeMethod($controller, 'regraDaRota', [$mapa, 'clientes', 'autocompletex']));
        $this->assertNull($this->invokeMethod($controller, 'regraDaRota', [$mapa, 'clientes', 'outro']));
        $this->assertNull($this->invokeMethod($controller, 'regraDaRota', [$mapa, 'produtos', 'autoCompleteX']));
    }

    public function testSoMetodoPublicoProprioEhRota(): void
    {
        $controller = $this->controller([]);

        $this->assertTrue($this->invokeMethod($controller, 'ehRota', ['acaoPublica']));
        $this->assertFalse($this->invokeMethod($controller, 'ehRota', ['acaoProtegida']), 'Protegido dá 404 no CodeIgniter.');
        $this->assertFalse($this->invokeMethod($controller, 'ehRota', ['_interno']), 'Prefixo "_" dá 404 no CodeIgniter.');
        $this->assertFalse($this->invokeMethod($controller, 'ehRota', ['naoExiste']), 'Método inexistente dá 404.');
        $this->assertFalse($this->invokeMethod($controller, 'ehRota', ['']));
    }

    public function testNegacaoHtmlAvisaERedirecionaParaOPainel(): void
    {
        $controller = $this->controller([], false);

        try {
            $this->invokeMethod($controller, 'negarAcesso');
            $this->fail('A negação deveria interromper a requisição.');
        } catch (PermissionsMapInterrompido $e) {
            $this->assertSame('redirect', $e->getMessage());
        }

        $this->assertSame('Você não tem permissão para acessar esta página.', $controller->session->flash['error'] ?? null);
        $this->assertNull($controller->output->status, 'HTML não deve responder 403, e sim redirecionar.');
    }

    public function testNegacaoAjaxRespondeJson403(): void
    {
        $controller = $this->controller([], true);

        try {
            $this->invokeMethod($controller, 'negarAcesso');
            $this->fail('A negação deveria interromper a requisição.');
        } catch (PermissionsMapInterrompido $e) {
            $this->assertSame('exit', $e->getMessage());
        }

        $this->assertSame(403, $controller->output->status);
        $this->assertSame('application/json', $controller->output->tipo);
        $this->assertTrue($controller->output->enviado);
        $this->assertSame(
            ['result' => false, 'message' => 'Você não tem permissão para acessar esta página.'],
            json_decode($controller->output->corpo, true)
        );
        $this->assertSame([], $controller->session->flash, 'AJAX não deixa aviso pendente para a próxima página.');
    }

    public function testRotaForaDoMapaEhNegada(): void
    {
        $controller = $this->controller(['vOs', 'aOs', 'eOs', 'cSistema'], true);
        $controller->router = (object) ['class' => 'clientes', 'method' => 'acaoPublica'];
        $controller->mapaFalso = ['Clientes' => ['outraAcao' => '*']];

        $this->expectException(PermissionsMapInterrompido::class);
        $this->invokeMethod($controller, 'verificarPermissaoDaRota');
    }

    public function testRotaPermitidaSegue(): void
    {
        $controller = $this->controller(['vCliente']);
        $controller->router = (object) ['class' => 'clientes', 'method' => 'acaoPublica'];
        $controller->mapaFalso = ['Clientes' => ['acaoPublica' => 'vCliente']];

        $this->invokeMethod($controller, 'verificarPermissaoDaRota');

        $this->assertSame([], $controller->session->flash);
    }

    public function testRotaInexistenteFicaParaO404DoFramework(): void
    {
        $controller = $this->controller([]);
        $controller->router = (object) ['class' => 'clientes', 'method' => 'naoExiste'];
        $controller->mapaFalso = [];

        $this->invokeMethod($controller, 'verificarPermissaoDaRota');

        $this->assertSame([], $controller->session->flash);
    }
}

final class PermissionsMapInterrompido extends RuntimeException
{
}

/**
 * MY_Controller com as saídas que encerram o PHP trocadas por exceção, e
 * com o carregamento do mapa vindo de uma propriedade.
 */
final class PermissionsMapControllerTestavel extends MY_Controller
{
    public $permission;

    public $session;

    public $input;

    public $output;

    public $router;

    public $config;

    public ?array $mapaFalso = null;

    public function acaoPublica()
    {
    }

    public function _interno()
    {
    }

    protected function acaoProtegida()
    {
    }

    protected function redirecionarParaOPainel()
    {
        throw new PermissionsMapInterrompido('redirect');
    }

    protected function encerrar()
    {
        throw new PermissionsMapInterrompido('exit');
    }

    protected function verificarPermissaoDaRota()
    {
        $teste = $this;
        $this->config = new class($teste) {
            public function __construct(private $teste)
            {
            }

            public function load($arquivo)
            {
            }

            public function item($chave)
            {
                return $this->teste->mapaFalso;
            }
        };

        parent::verificarPermissaoDaRota();
    }
}
