<?php

/*
 * Layout do painel da v5 (views/tema/topo|menu|conteudo|rodape).
 *
 * MY_Controller::layout() monta os dados com estas funções e as views só
 * imprimem. A lógica fica aqui para poder ser testada sem subir o framework.
 *
 * Os estilos da moldura vêm de assets/dist/layout.css, cujas utilities só valem
 * dentro de .v5-shell; o conteúdo da tela recebe assets/dist/app.css. Ver
 * assets/src/layout.css.
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
        'sku' => 'SKU',
        'atualizacao' => 'Atualização',
        'usuarios' => 'Usuários',
        'permissoes' => 'Permissões',
        'mapos' => 'Sistema',
        'emails' => 'E-mails',
        'configurar' => 'Configurações',
        'lancamentos' => 'Lançamentos',
        'doisfatores' => 'Autenticação em dois fatores',
        'ativardoisfatores' => 'Autenticação em dois fatores',
        'novoscodigosdoisfatores' => 'Autenticação em dois fatores',
        'desativardoisfatores' => 'Autenticação em dois fatores',
        'minhaconta' => 'Minha conta',
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

        // /mapos (sem método) é o painel inicial, não o módulo "Sistema".
        $ehPainel = strtolower($controller) === 'mapos' && in_array(strtolower($metodo), ['', 'index'], true);

        if ($controller !== '' && ! $ehPainel) {
            $itens[] = ['label' => LAYOUT_ROTULOS[strtolower($controller)] ?? ucfirst($controller), 'url' => $siteUrl($controller)];
        }

        // gerenciar e index são a própria listagem do controller: a página 2
        // (clientes/gerenciar/10) tem o mesmo breadcrumb da página 1 (clientes).
        if ($controller !== '' && $metodo !== '' && ! $ehPainel && ! in_array(strtolower($metodo), ['gerenciar', 'index'], true)) {
            $itens[] = ['label' => LAYOUT_ROTULOS[strtolower($metodo)] ?? ucfirst($metodo), 'url' => $siteUrl(trim($controller . '/' . $metodo . '/' . $parametro, '/'))];
        }

        return $itens;
    }
}

if (! function_exists('layoutAssets')) {
    /**
     * CSS e módulos JS da página, como caminhos relativos a base_url() (ou
     * URLs absolutas, para os externos).
     *
     * O app.css (preflight e utilities, para o conteúdo da tela) vem antes do
     * layout.css (a moldura), que vai por último.
     *
     * @return array{css: list<string>, modulos: list<string>}
     */
    function layoutAssets(): array
    {
        return [
            'css' => ['assets/dist/app.css', 'assets/dist/layout.css'],
            'modulos' => ['assets/js/app.js'],
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

if (! function_exists('layoutAtalhos')) {
    /**
     * Atalhos de teclado do painel (F1 a F4, como na v4), só para as telas que
     * o usuário pode abrir: saem do mesmo menu já filtrado por permissão.
     *
     * @param  list<array<string, mixed>>  $menu  Saída de layoutMenuVisivel()
     * @return array<string, string> tecla => URL
     */
    function layoutAtalhos(array $menu, callable $siteUrl): array
    {
        $teclas = ['clientes' => 'F1', 'produtos' => 'F2', 'servicos' => 'F3', 'os' => 'F4'];
        $atalhos = [];

        foreach ($menu as $item) {
            if (isset($item['url'], $teclas[$item['url']])) {
                $atalhos[$teclas[$item['url']]] = $siteUrl($item['url']);
            }
        }

        ksort($atalhos);

        return $atalhos;
    }
}

if (! function_exists('layoutTitulo')) {
    /**
     * Título da aba do navegador (WCAG 2.4.2): a tela atual, o módulo e o nome
     * do sistema, do mais específico ao mais geral ("Adicionar — Clientes —
     * Map-OS"). Vem do breadcrumb, sem o "Início"; no painel inicial sobra só
     * "Início".
     *
     * @param  list<array{label: string, url?: string|null}>  $breadcrumb
     */
    function layoutTitulo(array $breadcrumb, string $sistema): string
    {
        $rotulos = array_values(array_filter(array_map(static fn (array $item): string => trim($item['label']), $breadcrumb), static fn (string $r): bool => $r !== ''));
        $especificos = array_slice($rotulos, 1);

        if ($especificos === [] && $rotulos !== []) {
            $especificos = [$rotulos[0]];
        }

        return implode(' — ', [...array_reverse(array_slice($especificos, -2)), $sistema]);
    }
}
