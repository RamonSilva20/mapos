<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Layout do painel da v5 (#2835): menu, breadcrumb, assets do modo legado e
 * dados dos scripts legados. Ver application/helpers/layout_helper.php.
 */
final class LayoutTest extends MaposTestCase
{
    private static ?array $mapa = null;

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
     * Decide como o MY_Controller decidiria, para um usuário com as
     * permissões informadas, usando o mapa de permissões real.
     *
     * @param  list<string>  $permissoes
     * @return callable(string, string): bool
     */
    private function permite(array $permissoes): callable
    {
        $controller = $this->makeInstance(LayoutControllerTestavel::class);
        $controller->permission = new class($permissoes) {
            public function __construct(private array $permissoes)
            {
            }

            public function checkPermission($id, $atividade)
            {
                return in_array($atividade, $this->permissoes, true);
            }
        };
        $controller->session = new class() {
            public function userdata($chave)
            {
                return $chave === 'permissao' ? 1 : null;
            }
        };

        return fn (string $c, string $m): bool => $this->invokeMethod(
            $controller,
            'regraPermite',
            [$this->invokeMethod($controller, 'regraDaRota', [self::mapa(), $c, $m])]
        );
    }

    /**
     * Rótulos visíveis, com os filhos dos grupos como "Grupo > Item".
     *
     * @return list<string>
     */
    private static function rotulos(array $menu): array
    {
        $rotulos = [];
        foreach ($menu as $item) {
            if (isset($item['itens'])) {
                foreach ($item['itens'] as $filho) {
                    $rotulos[] = $item['label'] . ' > ' . $filho['label'];
                }
            } else {
                $rotulos[] = $item['label'];
            }
        }

        return $rotulos;
    }

    // ---------------------------------------------------------------------
    // Menu
    // ---------------------------------------------------------------------

    /**
     * Permissão que o tema/menu.php da v4 conferia em cada item da sidebar.
     * Início não tinha checagem.
     */
    public static function itensDaSidebarDaV4(): array
    {
        return [
            'clientes' => ['clientes', 'vCliente'],
            'produtos' => ['produtos', 'vProduto'],
            'servicos' => ['servicos', 'vServico'],
            'vendas' => ['vendas', 'vVenda'],
            'os' => ['os', 'vOs'],
            'garantias' => ['garantias', 'vGarantia'],
            'arquivos' => ['arquivos', 'vArquivo'],
            'lancamentos' => ['financeiro/lancamentos', 'vLancamento'],
            'cobrancas' => ['cobrancas/cobrancas', 'vCobranca'],
        ];
    }

    /**
     * O menu novo decide pelo mapa de permissões da rota de destino. Para os
     * itens que já existiam na sidebar da v4, a regra do mapa tem de ser a
     * mesma permissão que o menu antigo conferia.
     */
    #[DataProvider('itensDaSidebarDaV4')]
    public function testItemDaSidebarUsaAMesmaPermissaoDaV4(string $url, string $permissaoV4): void
    {
        $item = null;
        foreach (layoutMenu() as $candidato) {
            if (($candidato['url'] ?? null) === $url) {
                $item = $candidato;
            }
        }

        $this->assertNotNull($item, "Item $url sumiu do menu.");

        $regra = null;
        foreach (self::mapa() as $controller => $metodos) {
            if (strcasecmp($controller, $item['rota'][0]) === 0) {
                $regra = $metodos[$item['rota'][1]] ?? null;
            }
        }

        $this->assertSame($permissaoV4, $regra, "A rota de $url mudou de permissão em relação ao menu da v4.");
    }

    public function testInicioApareceParaQualquerUsuarioLogado(): void
    {
        $menu = layoutMenuVisivel(layoutMenu(), $this->permite([]), 'Mapos', 'index');

        $this->assertSame(['Início'], self::rotulos($menu));
    }

    /**
     * Para qualquer combinação de permissões, a sidebar nova mostra
     * exatamente os itens que a da v4 mostrava.
     */
    public function testSidebarMostraOsMesmosItensQueAV4(): void
    {
        $itens = self::itensDaSidebarDaV4();
        $permissoes = array_column($itens, 1);

        // Todos os subconjuntos seriam 512; os vizinhos de "nenhuma" e "todas"
        // e alguns cruzados cobrem cada item ligado e desligado.
        $conjuntos = [[], $permissoes];
        foreach ($permissoes as $p) {
            $conjuntos[] = [$p];
            $conjuntos[] = array_values(array_diff($permissoes, [$p]));
        }
        $conjuntos[] = ['vCliente', 'vOs', 'vLancamento'];
        $conjuntos[] = ['vProduto', 'vServico', 'vVenda', 'vCobranca'];

        $rotuloPorUrl = [];
        foreach (layoutMenu() as $item) {
            if (isset($item['url'])) {
                $rotuloPorUrl[$item['url']] = $item['label'];
            }
        }

        foreach ($conjuntos as $conjunto) {
            $esperado = ['Início'];
            foreach ($itens as [$url, $permissao]) {
                if (in_array($permissao, $conjunto, true)) {
                    $esperado[] = $rotuloPorUrl[$url];
                }
            }

            $menu = layoutMenuVisivel(layoutMenu(), $this->permite($conjunto), 'Mapos', 'index');
            $principais = array_values(array_filter(self::rotulos($menu), fn ($r) => ! str_contains($r, ' > ')));

            // A ordem segue os grupos da sidebar (#2917); a paridade é de itens.
            sort($esperado);
            sort($principais);
            $this->assertSame($esperado, $principais, 'Permissões: ' . implode(', ', $conjunto));
        }
    }

    /**
     * Relatórios e Configurações ficavam em menus da topbar sem checagem: o
     * link aparecia e a tela negava. Agora cada um segue a rota de destino.
     */
    public function testGruposSeguemAPermissaoDaRotaDeDestino(): void
    {
        $menu = layoutMenuVisivel(layoutMenu(), $this->permite(['rCliente', 'cUsuario', 'cBackup']), 'Mapos', 'index');

        $this->assertSame(
            ['Início', 'Relatórios > Clientes', 'Configurações > Usuários', 'Configurações > Backup'],
            self::rotulos($menu)
        );
    }

    public function testGrupoSemItemVisivelSome(): void
    {
        $menu = layoutMenuVisivel(layoutMenu(), $this->permite(['vCliente']), 'Mapos', 'index');

        $this->assertNotContains('Relatórios', array_column($menu, 'label'));
        $this->assertNotContains('Configurações', array_column($menu, 'label'));
    }

    /**
     * SKU exige rVenda e rOs juntos no mapa; o menu respeita o "todas".
     */
    public function testItemComRegraTodasExigeTodasAsPermissoes(): void
    {
        $so = layoutMenuVisivel(layoutMenu(), $this->permite(['rVenda']), 'Mapos', 'index');
        $ambas = layoutMenuVisivel(layoutMenu(), $this->permite(['rVenda', 'rOs']), 'Mapos', 'index');

        $this->assertNotContains('Relatórios > SKU', self::rotulos($so));
        $this->assertContains('Relatórios > SKU', self::rotulos($ambas));
    }

    public function testAdministradorVeTodosOsItens(): void
    {
        $todas = ['vCliente', 'vProduto', 'vServico', 'vVenda', 'vOs', 'vGarantia', 'vArquivo', 'vLancamento', 'vCobranca',
            'rCliente', 'rProduto', 'rServico', 'rOs', 'rVenda', 'rFinanceiro',
            'cSistema', 'cUsuario', 'cEmitente', 'cPermissao', 'cAuditoria', 'cEmail', 'cBackup'];

        $menu = layoutMenuVisivel(layoutMenu(), $this->permite($todas), 'Mapos', 'index');

        $this->assertCount(10 + 8 + 7, self::rotulos($menu));
    }

    /**
     * Todo item do menu aponta para uma rota que existe no mapa: um item
     * apontando para rota fora do mapa nunca apareceria.
     */
    public function testTodoItemApontaParaRotaDoMapa(): void
    {
        $rotas = [];
        foreach (layoutMenu() as $item) {
            foreach ($item['itens'] ?? [$item] as $folha) {
                $rotas[] = $folha['rota'];
            }
        }

        foreach ($rotas as [$controller, $metodo]) {
            $this->assertArrayHasKey($controller, self::mapa(), "Controller $controller fora do mapa.");
            $this->assertArrayHasKey($metodo, self::mapa()[$controller], "$controller::$metodo fora do mapa.");
            $this->assertNotFalse(self::mapa()[$controller][$metodo], "$controller::$metodo não é rota.");
        }
    }

    public function testMarcaAPaginaAtualEAbreOGrupo(): void
    {
        $todas = ['vCliente', 'rCliente', 'rOs', 'cSistema'];

        $clientes = layoutMenuVisivel(layoutMenu(), $this->permite($todas), 'clientes', 'editar');
        $atual = array_values(array_filter($clientes, fn ($i) => $i['atual']));
        $this->assertSame(['Cliente / Fornecedor'], array_column($atual, 'label'));

        $relatorio = layoutMenuVisivel(layoutMenu(), $this->permite($todas), 'Relatorios', 'osCustom');
        $grupo = array_values(array_filter($relatorio, fn ($i) => $i['label'] === 'Relatórios'))[0];
        $this->assertTrue($grupo['atual']);
        $this->assertSame(['Ordens de Serviço'], array_column(array_filter($grupo['itens'], fn ($i) => $i['atual']), 'label'));

        $configuracoes = array_values(array_filter($relatorio, fn ($i) => $i['label'] === 'Configurações'))[0];
        $this->assertFalse($configuracoes['atual']);
    }

    public function testInicioSoEhAtualNaPaginaInicial(): void
    {
        $this->assertTrue(layoutRotaAtiva(['Mapos/index'], 'mapos', 'index'));
        $this->assertFalse(layoutRotaAtiva(['Mapos/index'], 'Mapos', 'minhaConta'));
        $this->assertTrue(layoutRotaAtiva(['Clientes'], 'clientes', 'visualizar'));
        $this->assertFalse(layoutRotaAtiva(['Clientes'], 'Clientesx', 'index'));
    }

    // ---------------------------------------------------------------------
    // Breadcrumb, saudação e avatar
    // ---------------------------------------------------------------------

    public function testBreadcrumbSegueOsSegmentosDaUrl(): void
    {
        $site = fn ($c) => 'http://x/index.php/' . $c;

        $this->assertSame([['label' => 'Início', 'url' => 'http://x/index.php/']], layoutBreadcrumb([null, null, null], $site));
        $this->assertSame(
            [
                ['label' => 'Início', 'url' => 'http://x/index.php/'],
                ['label' => 'Clientes', 'url' => 'http://x/index.php/clientes'],
                ['label' => 'Editar', 'url' => 'http://x/index.php/clientes/editar/7'],
            ],
            layoutBreadcrumb(['clientes', 'editar', '7'], $site)
        );
        // A listagem (gerenciar/index), em qualquer página, fica só com o controller.
        $this->assertSame(['Início', 'Ordens de serviço'], array_column(layoutBreadcrumb(['os', 'gerenciar', null], $site), 'label'));
        $this->assertSame(['Início', 'Serviços', 'Editar'], array_column(layoutBreadcrumb(['servicos', 'editar', '3'], $site), 'label'));
        $this->assertSame(['Início', 'Clientes'], array_column(layoutBreadcrumb(['clientes', 'gerenciar', '10'], $site), 'label'));
    }

    public static function horas(): array
    {
        return [[0, 'Bom dia'], [11, 'Bom dia'], [12, 'Boa tarde'], [17, 'Boa tarde'], [18, 'Boa noite'], [23, 'Boa noite']];
    }

    #[DataProvider('horas')]
    public function testSaudacao(int $hora, string $esperado): void
    {
        $this->assertSame($esperado, layoutSaudacao($hora));
    }

    public function testAvatar(): void
    {
        $pasta = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mapos-avatar-' . uniqid();
        mkdir($pasta);
        touch($pasta . DIRECTORY_SEPARATOR . 'foto 1.png');

        try {
            $this->assertSame('http://x/assets/img/User.png', layoutAvatarUrl(null, $pasta, 'http://x/'));
            $this->assertSame('http://x/assets/img/User.png', layoutAvatarUrl('', $pasta, 'http://x/'));
            $this->assertSame('http://x/assets/img/User.png', layoutAvatarUrl('nao-existe.png', $pasta, 'http://x/'));
            $this->assertSame('http://x/assets/userImage/foto%201.png', layoutAvatarUrl('foto 1.png', $pasta, 'http://x/'));
            // Caminho com ../ não sai da pasta de fotos.
            $this->assertSame('http://x/assets/userImage/foto%201.png', layoutAvatarUrl('../../foto 1.png', $pasta, 'http://x/'));
        } finally {
            unlink($pasta . DIRECTORY_SEPARATOR . 'foto 1.png');
            rmdir($pasta);
        }
    }

    /**
     * A sidebar separa os itens em Operação, Financeiro e Sistema (#2917),
     * na ordem do mapa, e todo item de topo tem um desses grupos.
     */
    public function testMenuSeparadoEmGrupos(): void
    {
        foreach (layoutMenu() as $item) {
            $this->assertContains($item['grupo'] ?? null, ['Operação', 'Financeiro', 'Sistema'], "Item {$item['label']} sem grupo da sidebar.");
        }

        $todas = ['vCliente', 'vProduto', 'vServico', 'vVenda', 'vOs', 'vGarantia', 'vArquivo', 'vLancamento', 'vCobranca',
            'rCliente', 'rProduto', 'rServico', 'rOs', 'rVenda', 'rFinanceiro',
            'cSistema', 'cUsuario', 'cEmitente', 'cPermissao', 'cAuditoria', 'cEmail', 'cBackup'];
        $grupos = layoutMenuGrupos(layoutMenuVisivel(layoutMenu(), $this->permite($todas), 'Mapos', 'index'));

        $this->assertSame(['Operação', 'Financeiro', 'Sistema'], array_column($grupos, 'label'));
        $this->assertSame('Início', $grupos[0]['itens'][0]['label']);
        $this->assertSame('Configurações', end($grupos[2]['itens'])['label']);
    }

    public function testGrupoSemItemVisivelNaoApareceNaSidebar(): void
    {
        $grupos = layoutMenuGrupos(layoutMenuVisivel(layoutMenu(), $this->permite(['vCliente']), 'Mapos', 'index'));

        $this->assertSame(['Operação'], array_column($grupos, 'label'));
        $this->assertSame(['Início', 'Cliente / Fornecedor'], array_column($grupos[0]['itens'], 'label'));
    }

    public static function nomes(): array
    {
        return [
            ['Ramon da Silva', 'RS'],
            ['  ramon   silva ', 'RS'],
            ['Admin', 'AD'],
            ['ÉRICA', 'ÉR'],
            ['álvaro óliveira', 'ÁÓ'],
            ['', '?'],
            ['   ', '?'],
        ];
    }

    #[DataProvider('nomes')]
    public function testIniciaisDoAvatar(string $nome, string $esperado): void
    {
        $this->assertSame($esperado, layoutIniciais($nome));
    }

    // ---------------------------------------------------------------------
    // Assets e modo legado
    // ---------------------------------------------------------------------

    public function testModoLegadoCarregaOCssEJsDaV4NaMesmaOrdem(): void
    {
        $assets = layoutAssets(true, 'puredark');

        $this->assertSame(
            [
                'assets/css/bootstrap.min.css',
                'assets/css/bootstrap-responsive.min.css',
                'assets/css/matrix-style.css',
                'assets/css/matrix-media.css',
                'assets/font-awesome/css/font-awesome.css',
                'assets/css/fullcalendar.css',
                'assets/css/tema-pure-dark.css',
            ],
            array_slice($assets['css'], 0, 7)
        );
        $this->assertNotContains('assets/dist/app.css', $assets['css']);
        $this->assertContains('assets/vendor/boxicons/css/boxicons.min.css', $assets['css'], 'As telas legadas ainda usam Boxicons.');
        $this->assertContains('assets/js/jquery-1.12.4.min.js', $assets['js_cabecalho']);
        $this->assertContains('assets/js/csrf.js', $assets['js_cabecalho']);
        $this->assertSame('assets/js/legado/globais.js', $assets['js_cabecalho'][0], 'BaseUrl precisa existir antes dos scripts das views.');
        $this->assertSame(['assets/js/bootstrap.min.js', 'assets/js/matrix.js', 'assets/js/legado/datatables.js'], $assets['js_rodape']);
    }

    public function testTelaMigradaNaoCarregaNadaDoLegado(): void
    {
        $assets = layoutAssets(false, 'white');

        $this->assertContains('assets/dist/app.css', $assets['css']);
        $this->assertSame([], $assets['js_cabecalho']);
        $this->assertSame([], $assets['js_rodape']);
        foreach ($assets['css'] as $css) {
            $this->assertStringNotContainsString('bootstrap', $css);
            $this->assertStringNotContainsString('tema-', $css);
            $this->assertStringNotContainsString('boxicons', $css, 'Telas migradas usam o sprite Lucide (#2915).');
        }
    }

    /**
     * O layout.css tem regras sem camada que precisam vencer as do
     * matrix-style com a mesma especificidade: ele vem por último.
     */
    #[DataProvider('modos')]
    public function testLayoutCssVemPorUltimo(bool $legado): void
    {
        $css = layoutAssets($legado, 'white')['css'];

        $this->assertSame('assets/dist/layout.css', end($css));
        $this->assertSame(['assets/js/app.js'], layoutAssets($legado, 'white')['modulos']);
    }

    public static function modos(): array
    {
        return ['legado' => [true], 'migrada' => [false]];
    }

    public function testTemaDesconhecidoNaoCarregaTemaCss(): void
    {
        foreach (layoutAssets(true, 'default')['css'] as $css) {
            $this->assertStringNotContainsString('tema-', $css);
        }
    }

    public function testUrlDeAsset(): void
    {
        $this->assertSame('http://x/assets/a.css', layoutUrlAsset('assets/a.css', 'http://x/'));
        $this->assertSame('https://unpkg.com/a.css', layoutUrlAsset('https://unpkg.com/a.css', 'http://x/'));
    }

    public function testDadosDosScriptsLegados(): void
    {
        $dados = layoutDadosLegado('http://x/', fn ($c) => 'http://x/index.php/' . $c, ['control_datatable' => '0', 'per_page' => '25']);

        $this->assertSame('http://x/', $dados['baseUrl']);
        $this->assertSame('http://x/', $dados['atalhos']['Escape']);
        $this->assertSame('http://x/index.php/clientes', $dados['atalhos']['F1']);
        $this->assertSame('http://x/index.php/vendas/adicionar', $dados['atalhos']['F6']);
        $this->assertSame('http://x/index.php/financeiro/lancamentos', $dados['atalhos']['F7']);
        $this->assertSame(['F1', 'F2', 'F3', 'F4', 'F6', 'F7', 'Escape'], array_values(array_intersect(['F1', 'F2', 'F3', 'F4', 'F6', 'F7', 'Escape'], array_keys($dados['atalhos']))));
        $this->assertFalse($dados['dataTable']['ativo']);
        $this->assertSame(25, $dados['dataTable']['porPagina']);

        $padrao = layoutDadosLegado('http://x/', fn ($c) => $c, []);
        $this->assertTrue($padrao['dataTable']['ativo']);
        $this->assertSame(10, $padrao['dataTable']['porPagina']);
    }
}

/**
 * MY_Controller sem construtor, para chamar regraDaRota()/regraPermite().
 */
final class LayoutControllerTestavel extends MY_Controller
{
    public $permission;

    public $session;
}
