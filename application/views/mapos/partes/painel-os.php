<?php
/**
 * Lista de OS de um status na seção do painel (#3036). Usada no primeiro
 * render (mapos/painel.php) e nas trocas de aba por AJAX (Mapos::painelOs),
 * por isso é só a tabela, sem a faixa de abas em volta.
 *
 * @var list<object>                               $os_lista
 * @var string                                     $hoje
 * @var array{ver: bool, editar: bool, imprimir: bool} $acoes_os
 */
?>
<?= component('data-table', [
    'caption' => 'Ordens de serviço do status selecionado, pela data de entrega',
    'dense' => true,
    'rows' => $os_lista,
    'columns' => [
        ['label' => 'Nº', 'align' => 'right', 'nowrap' => true, 'render' => fn ($o) => component('link', ['label' => (string) $o->idOs, 'href' => site_url('os/visualizar/' . (int) $o->idOs)])],
        ['label' => 'Cliente', 'class' => 'min-w-32 [overflow-wrap:anywhere]', 'render' => fn ($o) => (string) ($o->nomeCliente ?? 'Cliente removido')],
        ['label' => 'Entrega', 'align' => 'right', 'nowrap' => true, 'render' => fn ($o) => $o->dataFinal !== null && substr((string) $o->dataFinal, 0, 10) < $hoje
            ? component('pill-status', ['label' => 'Atrasada · ' . dataBr($o->dataFinal), 'variant' => 'warning'])
            : ($o->dataFinal !== null ? dataBr($o->dataFinal) : '—')],
        ['label' => 'Status', 'nowrap' => true, 'render' => fn ($o) => component('pill-status', osStatusPill($o->status))],
        ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($o) use ($acoes_os) {
            $acoes = [];
            if ($acoes_os['ver']) {
                $acoes[] = component('button', ['label' => 'Ver OS ' . $o->idOs, 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('os/visualizar/' . (int) $o->idOs)]);
            }
            if ($acoes_os['editar']) {
                $acoes[] = component('button', ['label' => 'Editar OS ' . $o->idOs, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('os/editar/' . (int) $o->idOs)]);
            }
            if ($acoes_os['imprimir']) {
                $acoes[] = component('button', ['label' => 'Imprimir OS ' . $o->idOs, 'icon' => 'printer', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('os/imprimir/' . (int) $o->idOs), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]);
            }

            return $acoes;
        }],
    ],
    'empty' => component('empty-state', ['title' => 'Nenhuma OS neste status', 'message' => 'Escolha outro status na faixa acima.', 'icon' => 'wrench', 'class' => 'border-0']),
]) ?>
