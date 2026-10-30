<?php
/**
 * Lista de vendas de um status na seção do painel (#3036). Usada no primeiro
 * render (mapos/painel.php) e nas trocas de aba por AJAX (Mapos::painelVendas).
 *
 * @var list<object>                               $vendas_lista
 * @var array{ver: bool, editar: bool, imprimir: bool} $acoes_vendas
 */
?>
<?= component('data-table', [
    'caption' => 'Vendas do status selecionado, da mais recente',
    'dense' => true,
    'rows' => $vendas_lista,
    'columns' => [
        ['label' => 'Nº', 'align' => 'right', 'nowrap' => true, 'render' => fn ($v) => component('link', ['label' => (string) $v->idVendas, 'href' => site_url('vendas/visualizar/' . (int) $v->idVendas)])],
        ['label' => 'Cliente', 'class' => 'min-w-32 [overflow-wrap:anywhere]', 'render' => fn ($v) => (string) ($v->nomeCliente ?? 'Cliente removido')],
        ['label' => 'Data', 'align' => 'right', 'nowrap' => true, 'render' => fn ($v) => dataBr($v->dataVenda)],
        ['label' => 'Status', 'nowrap' => true, 'render' => fn ($v) => component('pill-status', osStatusPill($v->status))],
        ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($v) use ($acoes_vendas) {
            $acoes = [];
            if ($acoes_vendas['ver']) {
                $acoes[] = component('button', ['label' => 'Ver venda ' . $v->idVendas, 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('vendas/visualizar/' . (int) $v->idVendas)]);
            }
            if ($acoes_vendas['editar']) {
                $acoes[] = component('button', ['label' => 'Editar venda ' . $v->idVendas, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('vendas/editar/' . (int) $v->idVendas)]);
            }
            if ($acoes_vendas['imprimir']) {
                $acoes[] = component('button', ['label' => 'Imprimir venda ' . $v->idVendas, 'icon' => 'printer', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('vendas/imprimir/' . (int) $v->idVendas), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]);
            }

            return $acoes;
        }],
    ],
    'empty' => component('empty-state', ['title' => 'Nenhuma venda neste status', 'message' => 'Escolha outro status na faixa acima.', 'icon' => 'shopping-cart', 'class' => 'border-0']),
]) ?>
