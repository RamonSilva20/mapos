<?php
/**
 * Fila de e-mails (#2846), no padrão das listagens (#2852): os e-mails que o
 * sistema gera (OS, cobranças, recuperação de senha) esperando o envio pelo
 * cron, com a situação em pill-status e a exclusão em modal-confirm.
 *
 * @var list<object>          $results
 * @var array<string, string> $filtros
 * @var int                   $total
 * @var array                 $paginacao
 */
$temFiltro = $filtros !== [];
$colunas = [
    ['key' => 'id', 'label' => 'Nº', 'align' => 'right', 'nowrap' => true],
    ['label' => 'Para', 'class' => 'min-w-56 [overflow-wrap:anywhere]', 'render' => fn ($m) => (string) $m->to],
    ['label' => 'Assunto', 'class' => 'min-w-40 [overflow-wrap:anywhere]', 'hide_until' => 'md', 'render' => fn ($m) => emailFilaAssunto($m->headers) ?: '—'],
    ['label' => 'Criado em', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'lg', 'render' => fn ($m) => $m->date ? date('d/m/Y H:i', strtotime((string) $m->date)) : '—'],
    ['label' => 'Situação', 'nowrap' => true, 'render' => fn ($m) => component('pill-status', emailFilaPill($m->status))],
    ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => fn ($m) => component('button', ['label' => 'Excluir e-mail ' . $m->id, 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
        'data-modal-abrir' => 'excluir-email',
        'data-valor-id' => (string) $m->id,
        'data-valor-para' => (string) $m->to,
    ]])],
];
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Fila de e-mails</h1>
        <p class="text-caption text-muted" aria-live="polite"><?= e(($total === 1 ? '1 e-mail' : number_format($total, 0, ',', '.') . ' e-mails') . ($temFiltro ? ' com o filtro' : '')) ?>. O envio é feito pelo cron (veja a documentação).</p>
    </header>

    <form method="get" action="<?= e(site_url('mapos/emails')) ?>" role="search" aria-label="Filtrar e-mails" class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-[14rem_auto]">
        <?= component('select', ['name' => 'status', 'label' => 'Situação', 'placeholder' => 'Todas', 'options' => ['pending' => 'Na fila', 'sending' => 'Enviando', 'sent' => 'Enviados', 'failed' => 'Falharam'], 'selected' => $filtros['status'] ?? null]) ?>
        <div class="flex gap-2">
            <?= component('button', ['label' => 'Filtrar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
            <?php if ($temFiltro) { ?>
                <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'href' => site_url('mapos/emails')]) ?>
            <?php } ?>
        </div>
    </form>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'caption' => 'Fila de e-mails',
        'empty' => component('empty-state', [
            'title' => $temFiltro ? 'Nenhum e-mail nesta situação' : 'A fila está vazia',
            'message' => $temFiltro ? 'Escolha outra situação ou limpe o filtro.' : 'Os e-mails gerados pelo sistema aparecem aqui até serem enviados.',
            'icon' => 'mail',
            'class' => 'border-0',
        ]),
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>

<form id="form-excluir-email" method="post" action="<?= e(site_url('mapos/excluirEmail') . listagemQuery($filtros)) ?>" hidden>
    <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
    <input type="hidden" name="id" value="" data-modal-de="excluir-email" data-modal-valor="id">
</form>
<?= component('modal-confirm', [
    'id' => 'excluir-email',
    'title' => 'Excluir e-mail da fila?',
    'message' => ['O e-mail para ', new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="para"></strong>'), ' sai da fila e não será enviado.'],
    'confirm_label' => 'Excluir',
    'confirm_attrs' => ['form' => 'form-excluir-email'],
]) ?>
