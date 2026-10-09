<?php

/*
 * Layout do painel da v5 (views/tema/topo|menu|conteudo|rodape).
 *
 * MY_Controller::layout() monta os dados com estas funções e as views só
 * imprimem. A lógica fica aqui para poder ser testada sem subir o framework.
 *
 * Modo legado
 * -----------
 * As telas ainda não migradas dependem do Bootstrap 2, do jQuery 1.x, do
 * matrix-style e dos tema-*.css. Com $this->data['legacy_assets'] = true (o
 * padrão), o layout carrega esses arquivos e o conteúdo da tela fica dentro de
 * #content, como antes. A moldura nova (sidebar, topbar, rodapé e toasts) usa
 * assets/dist/layout.css, cujas utilities só valem dentro de .v5-shell e por
 * isso não alteram o conteúdo legado. Ver assets/src/layout.css.
 *
 * Uma tela migrada desliga a flag e passa a receber o assets/dist/app.css
 * completo no lugar do CSS/JS legado. Quando todas estiverem migradas, a #2855
 * remove o modo legado.
 */

if (! function_exists('layoutSaudacao')) {
    /**
     * "Bom dia", "Boa tarde" ou "Boa noite" para a hora (0 a 23).
     */
    function layoutSaudacao(int $hora): string
    {
        if ($hora < 12) {
            return 'Bom dia';
        }

        return $hora < 18 ? 'Boa tarde' : 'Boa noite';
    }
}

if (! function_exists('layoutAvatarUrl')) {
    /**
     * URL da foto do usuário logado, ou a imagem padrão quando ele não tem foto
     * ou o arquivo não existe mais.
     *
     * @param  string|null  $arquivo     Nome gravado na sessão (url_image_user_admin)
     * @param  string       $pastaFotos  Caminho físico de assets/userImage/
     * @param  string       $baseUrl     base_url() com barra no fim
     */
    function layoutAvatarUrl($arquivo, string $pastaFotos, string $baseUrl): string
    {
        $arquivo = basename((string) $arquivo);

        if ($arquivo === '' || $arquivo === '.' || ! is_file(rtrim($pastaFotos, '/\\') . DIRECTORY_SEPARATOR . $arquivo)) {
            return $baseUrl . 'assets/img/User.png';
        }

        return $baseUrl . 'assets/userImage/' . rawurlencode($arquivo);
    }
}

if (! function_exists('layoutMenu')) {
    /**
     * Itens do menu do painel.
     *
     * Cada item aponta para uma rota (controller/método) e só aparece quando o
     * mapa de permissões (config/permissions_map.php) libera essa rota para o
     * usuário. Assim o menu nunca mostra um link que vai dar "sem permissão" e
     * nunca esconde um que funcionaria.
     *
     * Os itens principais têm a mesma permissão que o menu da v4 verificava
     * (tests/Unit/LayoutTest.php confere). Os de Relatórios e Configurações
     * ficavam em menus da topbar sem verificação nenhuma: agora seguem a
     * permissão da rota de destino.
     *
     * - grupo: seção da sidebar (Operação, Financeiro, Sistema), ver
     *   layoutMenuGrupos()
     * - url: caminho para site_url() ('' é a página inicial)
     * - rota: [controller, método] conferido no mapa de permissões
     * - ativo: controllers ("Clientes") ou rotas ("Mapos/configurar") que
     *   marcam o item como a página atual
     *
     * @return list<array<string, mixed>>
     */
    function layoutMenu(): array
    {
        return [
            ['grupo' => 'Operação', 'label' => 'Início', 'icon' => 'house', 'url' => '', 'rota' => ['Mapos', 'index'], 'ativo' => ['Mapos/index']],
            ['grupo' => 'Operação', 'label' => 'Cliente / Fornecedor', 'icon' => 'user', 'url' => 'clientes', 'rota' => ['Clientes', 'index'], 'ativo' => ['Clientes']],
            ['grupo' => 'Operação', 'label' => 'Produtos', 'icon' => 'shopping-basket', 'url' => 'produtos', 'rota' => ['Produtos', 'index'], 'ativo' => ['Produtos']],
            ['grupo' => 'Operação', 'label' => 'Serviços', 'icon' => 'wrench', 'url' => 'servicos', 'rota' => ['Servicos', 'index'], 'ativo' => ['Servicos']],
            ['grupo' => 'Operação', 'label' => 'Vendas', 'icon' => 'shopping-cart', 'url' => 'vendas', 'rota' => ['Vendas', 'index'], 'ativo' => ['Vendas']],
            ['grupo' => 'Operação', 'label' => 'Ordens de Serviço', 'icon' => 'file-text', 'url' => 'os', 'rota' => ['Os', 'index'], 'ativo' => ['Os']],
            ['grupo' => 'Operação', 'label' => 'Termos de Garantias', 'icon' => 'receipt', 'url' => 'garantias', 'rota' => ['Garantias', 'index'], 'ativo' => ['Garantias']],
            ['grupo' => 'Financeiro', 'label' => 'Lançamentos', 'icon' => 'chart-column', 'url' => 'financeiro/lancamentos', 'rota' => ['Financeiro', 'lancamentos'], 'ativo' => ['Financeiro']],
            ['grupo' => 'Financeiro', 'label' => 'Cobranças', 'icon' => 'circle-dollar-sign', 'url' => 'cobrancas/cobrancas', 'rota' => ['Cobrancas', 'cobrancas'], 'ativo' => ['Cobrancas']],
            [
                'grupo' => 'Financeiro',
                'label' => 'Relatórios',
                'icon' => 'chart-pie',
                'itens' => [
                    ['label' => 'Clientes', 'url' => 'relatorios/clientes', 'rota' => ['Relatorios', 'clientes'], 'ativo' => ['Relatorios/clientes', 'Relatorios/clientesCustom', 'Relatorios/clientesRapid']],
                    ['label' => 'Produtos', 'url' => 'relatorios/produtos', 'rota' => ['Relatorios', 'produtos'], 'ativo' => ['Relatorios/produtos', 'Relatorios/produtosCustom', 'Relatorios/produtosRapid', 'Relatorios/produtosRapidMin', 'Relatorios/produtosEtiquetas']],
                    ['label' => 'Serviços', 'url' => 'relatorios/servicos', 'rota' => ['Relatorios', 'servicos'], 'ativo' => ['Relatorios/servicos', 'Relatorios/servicosCustom', 'Relatorios/servicosRapid']],
                    ['label' => 'Ordens de Serviço', 'url' => 'relatorios/os', 'rota' => ['Relatorios', 'os'], 'ativo' => ['Relatorios/os', 'Relatorios/osCustom', 'Relatorios/osRapid']],
                    ['label' => 'Vendas', 'url' => 'relatorios/vendas', 'rota' => ['Relatorios', 'vendas'], 'ativo' => ['Relatorios/vendas', 'Relatorios/vendasCustom', 'Relatorios/vendasRapid']],
                    ['label' => 'Financeiro', 'url' => 'relatorios/financeiro', 'rota' => ['Relatorios', 'financeiro'], 'ativo' => ['Relatorios/financeiro', 'Relatorios/financeiroCustom', 'Relatorios/financeiroRapid']],
                    ['label' => 'SKU', 'url' => 'relatorios/sku', 'rota' => ['Relatorios', 'sku'], 'ativo' => ['Relatorios/sku', 'Relatorios/skuCustom', 'Relatorios/skuRapid']],
                    ['label' => 'Receitas Brutas - MEI', 'url' => 'relatorios/receitasBrutasMei', 'rota' => ['Relatorios', 'receitasBrutasMei'], 'ativo' => ['Relatorios/receitasBrutasMei', 'Relatorios/receitasBrutasCustom', 'Relatorios/receitasBrutasRapid']],
                ],
            ],
            ['grupo' => 'Sistema', 'label' => 'Arquivos', 'icon' => 'archive', 'url' => 'arquivos', 'rota' => ['Arquivos', 'index'], 'ativo' => ['Arquivos']],
            [
                'grupo' => 'Sistema',
                'label' => 'Configurações',
                'icon' => 'settings',
                'itens' => [
                    ['label' => 'Sistema', 'url' => 'mapos/configurar', 'rota' => ['Mapos', 'configurar'], 'ativo' => ['Mapos/configurar']],
                    ['label' => 'Usuários', 'url' => 'usuarios', 'rota' => ['Usuarios', 'index'], 'ativo' => ['Usuarios']],
                    ['label' => 'Emitente', 'url' => 'mapos/emitente', 'rota' => ['Mapos', 'emitente'], 'ativo' => ['Mapos/emitente']],
                    ['label' => 'Permissões', 'url' => 'permissoes', 'rota' => ['Permissoes', 'index'], 'ativo' => ['Permissoes']],
                    ['label' => 'Auditoria', 'url' => 'auditoria', 'rota' => ['Auditoria', 'index'], 'ativo' => ['Auditoria']],
                    ['label' => 'E-mails', 'url' => 'mapos/emails', 'rota' => ['Mapos', 'emails'], 'ativo' => ['Mapos/emails']],
                    ['label' => 'Backup', 'url' => 'mapos/backup', 'rota' => ['Mapos', 'backup'], 'ativo' => ['Mapos/backup']],
                ],
            ],
        ];
    }
}

if (! function_exists('layoutRotaAtiva')) {
    /**
     * Diz se a página atual (controller/método) casa com a lista "ativo" de um
     * item. A comparação ignora maiúsculas e minúsculas, como o roteador.
     *
     * @param  list<string>  $ativo
     */
    function layoutRotaAtiva(array $ativo, string $controller, string $metodo): bool
    {
        foreach ($ativo as $padrao) {
            $partes = explode('/', $padrao, 2);

            if (strcasecmp($partes[0], $controller) !== 0) {
                continue;
            }

            if (! isset($partes[1]) || strcasecmp($partes[1], $metodo) === 0) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('layoutMenuVisivel')) {
    /**
     * Filtra o menu pelo que o usuário pode acessar e marca a página atual.
     *
     * Grupo sem nenhum item visível some. Grupo com o item atual vem aberto.
     *
     * @param  list<array<string, mixed>>   $menu     Normalmente layoutMenu()
     * @param  callable(string, string): bool  $permite  Recebe controller e método da rota
     * @return list<array<string, mixed>>
     */
    function layoutMenuVisivel(array $menu, callable $permite, string $controller, string $metodo): array
    {
        $visivel = [];

        foreach ($menu as $item) {
            if (isset($item['itens'])) {
                $filhos = layoutMenuVisivel($item['itens'], $permite, $controller, $metodo);

                if ($filhos === []) {
                    continue;
                }

                $item['itens'] = $filhos;
                $item['atual'] = in_array(true, array_column($filhos, 'atual'), true);
                $visivel[] = $item;

                continue;
            }

            if (! $permite($item['rota'][0], $item['rota'][1])) {
                continue;
            }

            $item['atual'] = layoutRotaAtiva($item['ativo'], $controller, $metodo);
            $visivel[] = $item;
        }

        return $visivel;
    }
}

if (! function_exists('layoutMenuGrupos')) {
    /**
     * Separa o menu já filtrado (layoutMenuVisivel()) nas seções da sidebar
     * (DESIGN.md: Operação, Financeiro, Sistema), na ordem em que aparecem.
     * Seção sem item visível não aparece.
     *
     * @param  list<array<string, mixed>>  $menu
     * @return list<array{label: string, itens: list<array<string, mixed>>}>
     */
    function layoutMenuGrupos(array $menu): array
    {
        $grupos = [];

        foreach ($menu as $item) {
            $grupo = (string) ($item['grupo'] ?? '');
            $grupos[$grupo] ??= ['label' => $grupo, 'itens' => []];
            $grupos[$grupo]['itens'][] = $item;
        }

        return array_values($grupos);
    }
}

if (! function_exists('layoutIniciais')) {
    /**
     * Iniciais do usuário para o avatar sem foto: primeira letra do primeiro
     * e do último nome ("Ramon da Silva" → "RS"); com um nome só, as duas
     * primeiras letras.
     */
    function layoutIniciais(string $nome): string
    {
        $partes = preg_split('/\s+/u', trim($nome), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($partes === []) {
            return '?';
        }

        $iniciais = count($partes) === 1
            ? mb_substr($partes[0], 0, 2)
            : mb_substr($partes[0], 0, 1) . mb_substr(end($partes), 0, 1);

        return mb_strtoupper($iniciais);
    }
}

if (! defined('LAYOUT_ROTULOS')) {
    // Nome dos módulos no breadcrumb, com acento (o segmento da URL não tem).
    define('LAYOUT_ROTULOS', [
        'servicos' => 'Serviços',
        'os' => 'Ordens de serviço',
        'cobrancas' => 'Cobranças',
        'relatorios' => 'Relatórios',
        'usuarios' => 'Usuários',
        'permissoes' => 'Permissões',
        'mapos' => 'Sistema',
    ]);
}

if (! function_exists('layoutBreadcrumb')) {
    /**
     * Itens do breadcrumb a partir dos segmentos da URL, com os mesmos rótulos
     * e links do breadcrumb da v4: Início, o controller e o método.
     *
     * @param  list<string|null>          $segmentos  $this->uri->segment(1..3)
     * @param  callable(string): string   $siteUrl
     * @return list<array{label: string, url?: string}>
     */
    function layoutBreadcrumb(array $segmentos, callable $siteUrl): array
    {
        [$controller, $metodo, $parametro] = array_pad(array_map(static fn ($s) => (string) $s, $segmentos), 3, '');

        $itens = [['label' => 'Início', 'url' => $siteUrl('')]];

        if ($controller !== '') {
            $itens[] = ['label' => LAYOUT_ROTULOS[strtolower($controller)] ?? ucfirst($controller), 'url' => $siteUrl($controller)];
        }

        // gerenciar e index são a própria listagem do controller: a página 2
        // (clientes/gerenciar/10) tem o mesmo breadcrumb da página 1 (clientes).
        if ($controller !== '' && $metodo !== '' && ! in_array(strtolower($metodo), ['gerenciar', 'index'], true)) {
            $itens[] = ['label' => ucfirst($metodo), 'url' => $siteUrl(trim($controller . '/' . $metodo . '/' . $parametro, '/'))];
        }

        return $itens;
    }
}

if (! function_exists('layoutAssets')) {
    /**
     * CSS e JS da página, como caminhos relativos a base_url() (ou URLs
     * absolutas, para os externos).
     *
     * - shell: sempre. A moldura nova e o tema (tokens, modo, sidebar).
     * - legado: só com legacy_assets, na mesma ordem do tema/topo.php da v4,
     *   para que a cascata do CSS legado não mude.
     * - app.css: só nas telas migradas (legacy_assets = false).
     *
     * @return array{css: list<string>, js_cabecalho: list<string>, modulos: list<string>, js_rodape: list<string>}
     */
    function layoutAssets(bool $legado, string $appTheme): array
    {
        $temas = [
            'white' => 'assets/css/tema-white.css',
            'puredark' => 'assets/css/tema-pure-dark.css',
            'darkviolet' => 'assets/css/tema-dark-violet.css',
            'darkorange' => 'assets/css/tema-dark-orange.css',
            'whitegreen' => 'assets/css/tema-white-green.css',
            'whiteblack' => 'assets/css/tema-white-black.css',
        ];

        $css = [];
        $jsCabecalho = [];
        $jsRodape = [];

        if ($legado) {
            $css = [
                'assets/css/bootstrap.min.css',
                'assets/css/bootstrap-responsive.min.css',
                'assets/css/matrix-style.css',
                'assets/css/matrix-media.css',
                'assets/font-awesome/css/font-awesome.css',
                'assets/css/fullcalendar.css',
            ];

            if (isset($temas[$appTheme])) {
                $css[] = $temas[$appTheme];
            }

            $css[] = 'https://fonts.googleapis.com/css?family=Open+Sans:400,700,800';
            $css[] = 'https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;500;700&display=swap';

            $jsCabecalho = [
                'assets/js/legado/globais.js',
                'assets/js/jquery-1.12.4.min.js',
                'assets/js/shortcut.js',
                'assets/js/funcoesGlobal.js',
                'assets/js/datatables.min.js',
                'assets/js/sweetalert.min.js',
                'assets/js/csrf.js',
                'assets/js/legado/atalhos.js',
            ];

            $jsRodape = [
                'assets/js/bootstrap.min.js',
                'assets/js/matrix.js',
                'assets/js/legado/datatables.js',
            ];
            // As telas legadas ainda usam ícones do Boxicons (<i class="bx ...">).
            // A moldura e as telas novas usam o sprite Lucide (icon(), #2915).
            $css[] = 'assets/vendor/boxicons/css/boxicons.min.css';
        } else {
            $css[] = 'assets/dist/app.css';
        }

        // Depois do CSS legado: as regras estruturais do layout.css (sem
        // camada) precisam vencer as do matrix-style com a mesma especificidade.
        $css[] = 'assets/dist/layout.css';

        return [
            'css' => $css,
            'js_cabecalho' => $jsCabecalho,
            'modulos' => ['assets/js/app.js'],
            'js_rodape' => $jsRodape,
        ];
    }
}

if (! function_exists('layoutUrlAsset')) {
    /**
     * URL final de um asset de layoutAssets().
     */
    function layoutUrlAsset(string $caminho, string $baseUrl): string
    {
        return preg_match('#^https?://#', $caminho) ? $caminho : $baseUrl . $caminho;
    }
}

if (! function_exists('layoutDadosLegado')) {
    /**
     * Dados que os scripts legados (assets/js/legado/) leem da página, no lugar
     * do JavaScript que o tema/topo.php e o tema/rodape.php da v4 escreviam
     * inline.
     *
     * @param  callable(string): string  $siteUrl
     * @return array<string, mixed>
     */
    function layoutDadosLegado(string $baseUrl, callable $siteUrl, array $configuration): array
    {
        return [
            'baseUrl' => $baseUrl,
            'atalhos' => [
                'Escape' => $baseUrl,
                'F1' => $siteUrl('clientes'),
                'F2' => $siteUrl('produtos'),
                'F3' => $siteUrl('servicos'),
                'F4' => $siteUrl('os'),
                'F6' => $siteUrl('vendas/adicionar'),
                'F7' => $siteUrl('financeiro/lancamentos'),
            ],
            // Teclas que a v4 registrava sem ação, só para bloquear o
            // comportamento padrão do navegador.
            'atalhosBloqueados' => ['F8', 'F9', 'F10', 'F12'],
            'dataTable' => [
                'ativo' => (string) ($configuration['control_datatable'] ?? '1') === '1',
                'porPagina' => (int) ($configuration['per_page'] ?? 10),
                'idioma' => $baseUrl . 'assets/js/dataTable_pt-br.json',
            ],
        ];
    }
}
