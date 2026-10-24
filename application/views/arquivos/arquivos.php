<?php
/**
 * Listagem de arquivos (#2845), no padrão das listagens (#2852): busca e
 * período na URL, data-table, estados vazios, ações por linha e exclusão em
 * modal-confirm. "Novo arquivo" vem pela topbar.
 *
 * @var list<object>          $results
 * @var array<string, string> $filtros  pesquisa, de e ate (AAAA-MM-DD)
 * @var int                   $total
 * @var array                 $paginacao
 * @var array{adicionar: bool, editar: bool, excluir: bool} $pode
 */
$temFiltro = $filtros !== [];
$imagem = static fn (object $a): bool => in_array(strtolower(ltrim((string) $a->tipo, '.')), ['jpg', 'jpeg', 'gif', 'png'], true);

$colunas = [
    ['key' => 'idDocumentos', 'label' => 'Cód.', 'align' => 'right', 'nowrap' => true],
    ['label' => 'Prévia', 'nowrap' => true, 'hide_until' => 'md', 'render' => fn ($a) => $imagem($a)
        ? html_purificado('<img src="' . e(componenteUrl($a->url)) . '" alt="" width="40" height="40" loading="lazy" class="size-10 rounded-md border border-border object-cover">')
        : icon('file', ['class' => 'size-5 text-muted'])],
    ['label' => 'Nome', 'class' => 'min-w-40 [overflow-wrap:anywhere]', 'render' => fn ($a) => component('link', ['label' => (string) $a->documento, 'href' => site_url('arquivos/download/' . (int) $a->idDocumentos), 'class' => 'font-medium'])],
    ['label' => 'Descrição', 'class' => 'min-w-40 [overflow-wrap:anywhere]', 'hide_until' => 'xl', 'render' => fn ($a) => mb_strimwidth((string) $a->descricao, 0, 80, '…', 'UTF-8')],
    ['label' => 'Data', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'md', 'render' => fn ($a) => dataBr($a->cadastro)],
    ['label' => 'Tamanho', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'lg', 'render' => fn ($a) => tamanhoDeArquivo($a->tamanho)],
    ['label' => 'Tipo', 'nowrap' => true, 'hide_until' => 'lg', 'render' => fn ($a) => component('pill-status', ['label' => strtoupper(ltrim((string) $a->tipo, '.')) ?: '—', 'variant' => 'neutral'])],
    ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($a) use ($pode) {
        $acoes = [component('button', ['label' => 'Baixar ' . $a->documento, 'icon' => 'download', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('arquivos/download/' . (int) $a->idDocumentos)])];
        if ($pode['editar']) {
            $acoes[] = component('button', ['label' => 'Editar ' . $a->documento, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('arquivos/editar/' . (int) $a->idDocumentos)]);
        }
        if ($pode['excluir']) {
            $acoes[] = component('button', ['label' => 'Excluir ' . $a->documento, 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
                'data-modal-abrir' => 'excluir-arquivo',
                'data-valor-id' => (string) $a->idDocumentos,
                'data-valor-nome' => $a->documento,
            ]]);
        }

        return $acoes;
    }],
];

$vazio = $temFiltro
    ? component('empty-state', [
        'title' => 'Nenhum arquivo encontrado',
        'message' => 'Nada corresponde à busca. Confira o texto e o período, ou limpe os filtros.',
        'icon' => 'search-x',
        'action' => component('button', ['label' => 'Limpar filtros', 'variant' => 'outline', 'href' => site_url('arquivos')]),
        'class' => 'border-0',
    ])
    : component('empty-state', [
        'title' => 'Nenhum arquivo enviado',
        'message' => 'Documentos, fotos e PDFs guardados aqui ficam disponíveis para baixar.',
        'icon' => 'archive',
        'action' => $pode['adicionar'] ? component('button', ['label' => 'Enviar arquivo', 'icon' => 'upload', 'variant' => 'outline', 'href' => site_url('arquivos/adicionar')]) : null,
        'class' => 'border-0',
    ]);
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Arquivos</h1>
        <p class="text-caption text-muted" aria-live="polite">
            <?= e(($total === 1 ? '1 arquivo' : number_format($total, 0, ',', '.') . ' arquivos') . ($temFiltro ? ($total === 1 ? ' encontrado' : ' encontrados') . ' com os filtros' : '')) ?>
        </p>
    </header>

    <form method="get" action="<?= e(site_url('arquivos')) ?>" role="search" aria-label="Filtrar arquivos" class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-[minmax(0,1fr)_11.5rem_11.5rem_auto]">
        <?= component('input', [
            'name' => 'pesquisa',
            'label' => 'Buscar',
            'type' => 'search',
            'value' => $filtros['pesquisa'] ?? null,
            'placeholder' => 'Nome ou descrição',
            'attrs' => ['maxlength' => 100],
        ]) ?>
        <?= component('input', ['name' => 'de', 'label' => 'De', 'type' => 'date', 'value' => $filtros['de'] ?? null]) ?>
        <?= component('input', ['name' => 'ate', 'label' => 'Até', 'type' => 'date', 'value' => $filtros['ate'] ?? null]) ?>
        <div class="flex gap-2">
            <?= component('button', ['label' => 'Filtrar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
            <?php if ($temFiltro) { ?>
                <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'href' => site_url('arquivos')]) ?>
            <?php } ?>
        </div>
    </form>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'empty' => $vazio,
        'caption' => 'Arquivos',
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-arquivo" method="post" action="<?= e(site_url('arquivos/excluir') . listagemQuery($filtros)) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="" data-modal-de="excluir-arquivo" data-modal-valor="id">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-arquivo',
        'title' => 'Excluir arquivo?',
        // O nome entra pelo modal.js (textContent); aqui só o marcador vazio.
        'message' => [
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="nome"></strong>'),
            ' será apagado do servidor. Essa ação não pode ser desfeita.',
        ],
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-arquivo'],
    ]) ?>
<?php } ?>
