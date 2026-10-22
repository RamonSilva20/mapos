<?php
/**
 * Conta do cliente (#2848): os dados do cadastro, "Editar dados" como ação
 * principal e, abaixo de lg (com a tabbar), os atalhos que não cabem na tabbar (cobranças e
 * sair).
 *
 * @var object $cliente
 */
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$endereco = trim(implode(', ', array_filter([
    trim(implode(', ', array_filter([$cliente->rua, $cliente->numero, $cliente->complemento]))),
    $cliente->bairro,
    trim(implode(' / ', array_filter([$cliente->cidade, $cliente->estado]))),
    $cliente->cep,
])));

$secoes = [
    'Identificação' => ['Nome' => $cliente->nomeCliente, 'CPF ou CNPJ' => $cliente->documento, 'Pessoa de contato' => $cliente->contato, 'Cliente desde' => dataBr($cliente->dataCadastro)],
    'Contato' => ['E-mail (login)' => $cliente->email, 'Celular' => $cliente->celular, 'Telefone' => $cliente->telefone],
    'Endereço' => ['Endereço' => $endereco],
];
$classeAtalho = 'flex min-h-12 items-center gap-3 rounded-md px-3 text-body-md text-text hover:bg-surface-subtle focus-visible:outline-3 focus-visible:outline-ring';
?>
<div class="flex flex-col gap-4">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="font-display text-heading-xl text-text">Minha conta</h1>
            <p class="text-caption [overflow-wrap:anywhere] text-muted"><?= e($cliente->email) ?></p>
        </div>
        <?= component('button', ['label' => 'Editar dados', 'icon' => 'pencil', 'href' => site_url('mine/editarDados')]) ?>
    </header>

    <div class="grid gap-4 lg:grid-cols-3">
        <?php foreach ($secoes as $titulo => $campos) { ?>
            <section class="<?= e($caixa) ?>" aria-label="<?= e($titulo) ?>">
                <h2 class="text-heading-sm text-text"><?= e($titulo) ?></h2>
                <dl class="mt-3 flex flex-col gap-3">
                    <?php foreach ($campos as $rotulo => $valor) { ?>
                        <div>
                            <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                            <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= e(trim((string) $valor) !== '' ? $valor : '—') ?></dd>
                        </div>
                    <?php } ?>
                </dl>
            </section>
        <?php } ?>
    </div>

    <nav class="rounded-xl border border-border bg-surface p-2 lg:hidden" aria-label="Atalhos da conta">
        <a href="<?= e(site_url('mine/cobrancas')) ?>" class="<?= e($classeAtalho) ?>"><?= icon('receipt', ['class' => 'size-5 text-muted']) ?><span class="flex-1">Cobranças</span><?= icon('chevron-right', ['class' => 'size-4 text-muted']) ?></a>
        <a href="<?= e(site_url('mine/sair')) ?>" class="<?= e($classeAtalho) ?>"><?= icon('log-out', ['class' => 'size-5 text-muted']) ?><span class="flex-1">Sair</span></a>
    </nav>
</div>
