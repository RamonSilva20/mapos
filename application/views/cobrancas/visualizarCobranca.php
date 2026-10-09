<?php
/**
 * Detalhe da cobrança (#2844): cliente e dados do gateway em listas de
 * definição, com o status em pill-status e as ações por POST (atualizar, enviar
 * por e-mail, cancelar e excluir), que voltam para esta tela (campo voltar).
 *
 * Link, PDF e código de barras vêm do gateway: só URL http(s) vira link
 * (cobrancaUrlSegura()).
 *
 * @var object $result
 * @var array  $gateways
 * @var array{editar: bool, excluir: bool, ver_os: bool, ver_venda: bool, ver_cliente: bool} $pode
 */
$id = (int) $result->idCobranca;
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$campos = static fn (array $itens) => array_filter($itens, static fn ($valor) => $valor !== null && $valor !== '');

$referencia = null;
if (! empty($result->os_id)) {
    $referencia = $pode['ver_os']
        ? component('link', ['label' => 'Ordem de serviço #' . (int) $result->os_id, 'href' => site_url('os/visualizar/' . (int) $result->os_id)])
        : 'Ordem de serviço #' . (int) $result->os_id;
} elseif (! empty($result->vendas_id)) {
    $referencia = $pode['ver_venda']
        ? component('link', ['label' => 'Venda #' . (int) $result->vendas_id, 'href' => site_url('vendas/visualizar/' . (int) $result->vendas_id)])
        : 'Venda #' . (int) $result->vendas_id;
}

$link = cobrancaUrlSegura($result->link);
$pdf = cobrancaUrlSegura($result->pdf);
$pagamento = cobrancaUrlSegura($result->payment_url);

$cliente = $campos([
    'Nome' => $pode['ver_cliente'] && $result->clientes_id
        ? component('link', ['label' => (string) $result->nomeCliente, 'href' => site_url('clientes/visualizar/' . (int) $result->clientes_id)])
        : $result->nomeCliente,
    'Documento' => $result->documento,
    'Telefone' => $result->telefone,
    'Celular' => $result->celular,
    'E-mail' => $result->email,
]);

$dados = $campos([
    'Referência' => $referencia,
    'Gateway' => $result->payment_gateway,
    'Método de pagamento' => $result->payment_method,
    'Valor' => dinheiro((float) $result->total / 100),
    'Vencimento' => dataBr($result->expire_at),
    'Id no gateway' => $result->charge_id,
    'Código de barras' => $result->barcode,
    'Link de pagamento' => $pagamento !== null ? component('link', ['label' => 'Abrir link', 'href' => $pagamento, 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) : null,
    'Boleto' => $link !== null ? component('link', ['label' => 'Abrir em nova aba', 'href' => $link, 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) : null,
    'PDF' => $pdf !== null ? component('link', ['label' => 'Abrir em nova aba', 'href' => $pdf, 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) : null,
    'Mensagem' => $result->message,
]);
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display text-heading-xl text-text"><?= e('Cobrança #' . $id) ?></h1>
                <?= component('pill-status', cobrancaStatusPill($result->status)) ?>
            </div>
            <p class="text-caption [overflow-wrap:anywhere] text-muted"><?= e(cobrancaDescricaoDoStatus($gateways, $result->payment_gateway, $result->status)) ?></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?= component('button', ['label' => 'Voltar', 'icon' => 'chevron-left', 'variant' => 'ghost', 'href' => site_url('cobrancas/cobrancas')]) ?>
            <?php if ($pode['editar']) { ?>
                <?= component('button', ['label' => 'E-mail', 'icon' => 'mail', 'variant' => 'outline', 'type' => 'submit', 'attrs' => ['form' => 'form-acao-cobranca', 'formaction' => site_url('cobrancas/enviarEmail/' . $id)]]) ?>
                <?= component('button', ['label' => 'Atualizar status', 'icon' => 'history', 'variant' => 'outline', 'type' => 'submit', 'attrs' => ['form' => 'form-acao-cobranca', 'formaction' => site_url('cobrancas/atualizar/' . $id)]]) ?>
                <?= component('button', ['label' => 'Cancelar cobrança', 'icon' => 'x', 'variant' => 'ghost', 'class' => 'hover:text-danger-ink', 'attrs' => ['data-modal-abrir' => 'cancelar-cobranca']]) ?>
            <?php } ?>
            <?php if ($pode['excluir']) { ?>
                <?= component('button', ['label' => 'Excluir', 'icon' => 'trash-2', 'variant' => 'ghost', 'class' => 'hover:text-danger-ink', 'attrs' => ['data-modal-abrir' => 'excluir-cobranca']]) ?>
            <?php } ?>
        </div>
    </header>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-cliente">
            <h2 id="secao-cliente" class="text-heading-sm text-text">Cliente</h2>
            <?php if ($cliente === []) { ?>
                <p class="mt-3 text-caption text-muted">Sem cliente vinculado a esta cobrança.</p>
            <?php } ?>
            <dl class="mt-3 flex flex-col gap-3">
                <?php foreach ($cliente as $rotulo => $valor) { ?>
                    <div>
                        <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                        <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= componenteConteudo($valor) ?></dd>
                    </div>
                <?php } ?>
            </dl>
        </section>
        <section class="<?= e($caixa) ?> lg:col-span-2" aria-labelledby="secao-cobranca">
            <h2 id="secao-cobranca" class="text-heading-sm text-text">Dados da cobrança</h2>
            <dl class="mt-3 grid gap-4 sm:grid-cols-2">
                <?php foreach ($dados as $rotulo => $valor) { ?>
                    <div>
                        <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                        <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= componenteConteudo($valor) ?></dd>
                    </div>
                <?php } ?>
            </dl>
        </section>
    </div>
</div>

<form id="form-acao-cobranca" method="post" action="<?= e(site_url('cobrancas/visualizar/' . $id)) ?>" hidden>
    <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
    <input type="hidden" name="voltar" value="detalhe">
</form>

<?php if ($pode['editar']) { ?>
    <form id="form-cancelar-cobranca" method="post" action="<?= e(site_url('cobrancas/cancelar')) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="<?= e($id) ?>">
        <input type="hidden" name="voltar" value="detalhe">
    </form>
    <?= component('modal-confirm', [
        'id' => 'cancelar-cobranca',
        'title' => 'Cancelar cobrança #' . $id . '?',
        'message' => 'A cobrança será cancelada no gateway e o cliente não poderá mais pagá-la.',
        'confirm_label' => 'Cancelar cobrança',
        'cancel_label' => 'Voltar',
        'confirm_attrs' => ['form' => 'form-cancelar-cobranca'],
    ]) ?>
<?php } ?>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-cobranca" method="post" action="<?= e(site_url('cobrancas/excluir')) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="<?= e($id) ?>">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-cobranca',
        'title' => 'Excluir cobrança #' . $id . '?',
        'message' => 'A cobrança será cancelada no gateway e removida do Map-OS. Essa ação não pode ser desfeita.',
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-cobranca'],
    ]) ?>
<?php } ?>
