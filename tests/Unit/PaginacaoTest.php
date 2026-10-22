<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Paginação (#2836).
 *
 * Duas frentes:
 * - as telas legadas continuam com o markup do Bootstrap 2, que saiu do
 *   MY_Controller e foi para application/config/pagination.php. O teste de
 *   equivalência roda o CI_Pagination de verdade com a configuração antiga e
 *   com a nova e compara o HTML;
 * - as telas novas montam as props do componente pagination com
 *   paginacaoProps() / MY_Controller::paginacao().
 */
final class PaginacaoTest extends MaposTestCase
{
    /**
     * Configuração que o MY_Controller tinha antes da #2836, copiada como
     * estava. É a referência do teste de equivalência.
     */
    private const CONFIGURACAO_ANTIGA = [
        'per_page' => 10,
        'next_link' => 'Próxima',
        'prev_link' => 'Anterior',
        'full_tag_open' => '<div class="pagination alternate"><ul>',
        'full_tag_close' => '</ul></div>',
        'num_tag_open' => '<li>',
        'num_tag_close' => '</li>',
        'cur_tag_open' => '<li><a style="color: #2D335B"><b>',
        'cur_tag_close' => '</b></a></li>',
        'prev_tag_open' => '<li>',
        'prev_tag_close' => '</li>',
        'next_tag_open' => '<li>',
        'next_tag_close' => '</li>',
        'first_link' => 'Primeira',
        'last_link' => 'Última',
        'first_tag_open' => '<li>',
        'first_tag_close' => '</li>',
        'last_tag_open' => '<li>',
        'last_tag_close' => '</li>',
    ];

    public static function setUpBeforeClass(): void
    {
        require_once BASEPATH . 'libraries' . DIRECTORY_SEPARATOR . 'Pagination.php';
    }

    private static function configuracaoPadraoDoMyController(): array
    {
        return (new ReflectionClass(MY_Controller::class))->getDefaultProperties()['data']['configuration'];
    }

    private static function arquivoDeConfiguracao(): array
    {
        $config = [];
        include APPPATH . 'config' . DIRECTORY_SEPARATOR . 'pagination.php';

        return $config;
    }

    /**
     * Instância falsa do CodeIgniter com o que o CI_Pagination consulta.
     */
    private static function instanciaFalsa(?string $segmento): object
    {
        return new class($segmento) {
            public object $load;
            public object $lang;
            public object $config;
            public object $uri;
            public object $input;

            public function __construct(?string $segmento)
            {
                $this->load = new class() {
                    public function language($arquivo)
                    {
                    }
                };
                $this->lang = new class() {
                    public function line($linha)
                    {
                        return false;
                    }
                };
                $this->config = new class() {
                    public function item($item)
                    {
                        return null;
                    }
                };
                // URL /clientes/gerenciar[/offset], como o roteador entrega.
                $this->uri = new class($segmento) {
                    private array $segmentos;

                    public function __construct(?string $segmento)
                    {
                        $this->segmentos = array_filter([1 => 'clientes', 2 => 'gerenciar', 3 => $segmento], 'is_string');
                    }

                    public function segment($n)
                    {
                        return $this->segmentos[$n] ?? null;
                    }

                    public function segment_array()
                    {
                        return $this->segmentos;
                    }
                };
                $this->input = new class() {
                    public function get($chave = null)
                    {
                        return $chave === null ? [] : null;
                    }
                };
            }
        };
    }

    private static function linksLegados(array $parametrosDoConstrutor, array $initialize, ?string $segmento): string
    {
        $GLOBALS['__maposCiFalso'] = self::instanciaFalsa($segmento);

        $paginacao = new CI_Pagination($parametrosDoConstrutor);
        $paginacao->initialize($initialize);

        return $paginacao->create_links();
    }

    public static function offsets(): array
    {
        return [
            'primeira página' => [null, 195],
            'meio' => ['50', 195],
            'última' => ['190', 195],
            'poucos registros' => ['0', 25],
            'uma página só' => [null, 7],
        ];
    }

    /**
     * O fluxo antigo: library('pagination') sem config file, initialize()
     * com o array do MY_Controller. O fluxo novo: o Loader passa o
     * config/pagination.php ao construtor e o initialize() recebe a
     * configuração atual do MY_Controller. O HTML tem de ser idêntico.
     */
    #[DataProvider('offsets')]
    public function testTelasLegadasGeramOMesmoHtml(?string $segmento, int $total): void
    {
        $comum = ['base_url' => 'http://mapos.test/index.php/clientes/gerenciar/', 'total_rows' => $total];

        $antes = self::linksLegados([], self::CONFIGURACAO_ANTIGA + $comum, $segmento);
        $depois = self::linksLegados(self::arquivoDeConfiguracao(), self::configuracaoPadraoDoMyController() + $comum, $segmento);

        $this->assertSame($antes, $depois);
        if ($total > 10) {
            $this->assertStringContainsString('<div class="pagination alternate"><ul>', $depois);
            $paginaAtual = intdiv((int) $segmento, 10) + 1;
            $this->assertStringContainsString('<li><a style="color: #2D335B"><b>' . $paginaAtual . '</b></a></li>', $depois);
        } else {
            $this->assertSame('', $depois);
        }
    }

    public function testMarkupSaiuDoMyController(): void
    {
        $configuracao = self::configuracaoPadraoDoMyController();

        $this->assertSame(10, $configuracao['per_page']);
        foreach (array_keys(self::arquivoDeConfiguracao()) as $chave) {
            $this->assertArrayNotHasKey($chave, $configuracao, "$chave ainda está no MY_Controller.");
        }
    }

    public function testArquivoDeConfiguracaoTemOMarkupAntigo(): void
    {
        $esperado = self::CONFIGURACAO_ANTIGA;
        unset($esperado['per_page']);

        $atual = self::arquivoDeConfiguracao();
        ksort($esperado);
        ksort($atual);

        $this->assertSame($esperado, $atual);
    }

    // ------------------------------------------------------------ paginacaoProps

    public static function props(): array
    {
        $base = 'http://mapos.test/index.php/clientes/gerenciar';

        return [
            'meio' => [['base_url' => $base, 'total_rows' => 195, 'offset' => '50'], 20, 6, $base . '/{offset}'],
            'barra no fim' => [['base_url' => $base . '/', 'total_rows' => 195, 'offset' => 0], 20, 1, $base . '/{offset}'],
            'sem offset' => [['base_url' => $base, 'total_rows' => 30], 3, 1, $base . '/{offset}'],
            'offset não numérico' => [['base_url' => $base, 'total_rows' => 30, 'offset' => "1' OR 1=1"], 3, 1, $base . '/{offset}'],
            'offset negativo' => [['base_url' => $base, 'total_rows' => 30, 'offset' => -20], 3, 1, $base . '/{offset}'],
            'offset além do fim' => [['base_url' => $base, 'total_rows' => 30, 'offset' => 900], 3, 3, $base . '/{offset}'],
            'offset fora do múltiplo' => [['base_url' => $base, 'total_rows' => 30, 'offset' => 15], 3, 2, $base . '/{offset}'],
            'sem registros' => [['base_url' => $base, 'total_rows' => 0], 0, 1, $base . '/{offset}'],
            'per_page 25' => [['base_url' => $base, 'total_rows' => 51, 'per_page' => 25, 'offset' => 50], 3, 3, $base . '/{offset}'],
            'query sem ?' => [['base_url' => $base, 'total_rows' => 30, 'query_string' => 'per_page'], 3, 1, $base . '?per_page={offset}'],
            'query com filtros' => [['base_url' => $base . '?tipo=receita&status=1', 'total_rows' => 30, 'query_string' => 'per_page'], 3, 1, $base . '?tipo=receita&status=1&per_page={offset}'],
            'query terminando em ?' => [['base_url' => $base . '/?', 'total_rows' => 30, 'query_string' => 'per_page'], 3, 1, $base . '/?per_page={offset}'],
        ];
    }

    #[DataProvider('props')]
    public function testPaginacaoProps(array $opcoes, int $totalPaginas, int $atual, string $url): void
    {
        $props = paginacaoProps($opcoes);

        $this->assertSame($totalPaginas, $props['total_pages']);
        $this->assertSame($atual, $props['current']);
        $this->assertSame($url, $props['url']);
    }

    public static function opcoesInvalidas(): array
    {
        return [
            'sem base_url' => [['total_rows' => 10]],
            'per_page zero' => [['base_url' => '/x', 'total_rows' => 10, 'per_page' => 0]],
            'parâmetro com &' => [['base_url' => '/x', 'total_rows' => 10, 'query_string' => 'a&b=1']],
        ];
    }

    #[DataProvider('opcoesInvalidas')]
    public function testOpcoesInvalidas(array $opcoes): void
    {
        $this->expectException(InvalidArgumentException::class);
        paginacaoProps($opcoes);
    }

    public function testComponenteComPropsGeraOsMesmosOffsetsDoCiPagination(): void
    {
        $html = (string) component('pagination', paginacaoProps([
            'base_url' => 'http://mapos.test/index.php/clientes/gerenciar/',
            'total_rows' => 195,
            'offset' => 50,
        ]));

        $this->assertStringContainsString('href="http://mapos.test/index.php/clientes/gerenciar/50" aria-current="page"', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/clientes/gerenciar/40" rel="prev"', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/clientes/gerenciar/190"', $html);
        $this->assertStringNotContainsString('style=', $html);
    }

    public function testQueryStringEscapadaNoHref(): void
    {
        $html = (string) component('pagination', paginacaoProps([
            'base_url' => '/financeiro/lancamentos?cliente=a"b&tipo=receita',
            'total_rows' => 30,
            'query_string' => 'per_page',
        ]));

        $this->assertStringContainsString('href="/financeiro/lancamentos?cliente=a&quot;b&amp;tipo=receita&amp;per_page=10"', $html);
    }

    public function testMyControllerUsaOPerPageDaConfiguracao(): void
    {
        $controller = (new ReflectionClass(MY_Controller::class))->newInstanceWithoutConstructor();
        $controller->data['configuration']['per_page'] = 20;

        $props = (new ReflectionMethod($controller, 'paginacao'))->invoke($controller, '/clientes/gerenciar', 95, '40');

        $this->assertSame(['total_pages' => 5, 'current' => 3, 'url' => '/clientes/gerenciar/{offset}', 'per_page' => 20], $props);

        $comQuery = (new ReflectionMethod($controller, 'paginacao'))->invoke($controller, '/financeiro/lancamentos?x=1', 95, null, 'per_page');
        $this->assertSame('/financeiro/lancamentos?x=1&per_page={offset}', $comQuery['url']);
    }
}

if (! function_exists('get_instance')) {
    /**
     * Sob teste não existe a instância do CodeIgniter. O CI_Pagination a
     * consulta no construtor; o PaginacaoTest põe uma instância falsa aqui.
     */
    function &get_instance()
    {
        return $GLOBALS['__maposCiFalso'];
    }
}
