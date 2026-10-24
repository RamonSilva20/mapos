<?php
/**
 * OS do cliente (#2848): substitui as telas visualizar_os e detalhes_os da
 * v4. Só leitura: status, valores em kpi-card, dados e textos da OS,
 * produtos e serviços (os/partes/*, sem as ações do painel), anexos e o PIX.
 *
 * @var object      $os
 * @var array       $totais
 * @var list<object> $produtos
 * @var list<object> $servicos
 * @var list<object> $anexos
 * @var string|null $garantia_ate
 * @var array|null  $pix
 */
$idOs = (int) $os->idOs;
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$campos = static fn (array $itens) => array_filter($itens, static fn ($valor) => $valor !== null && $valor !== '');

$dados = $campos([
    'Entrada' => dataBr($os->dataInicial),
    'Previsão de entrega' => dataBr($os->dataFinal),
    'Técnico responsável' => (string) $os->nome,
    'Garantia' => (int) $os->garantia > 0
        ? (int) $os->garantia . ' dias' . ($garantia_ate ? ($garantia_ate >= date('Y-m-d') ? ', até ' : ', venceu em ') . dataBr($garantia_ate) : ', a partir da entrega')
        : null,
]);

$textos = $campos([
    'Equipamento ou serviço' => trim(strip_tags((string) $os->descricaoProduto)) !== '' ? osTextoExibicao($os->descricaoProduto) : null,
    'Defeito informado' => trim(strip_tags((string) $os->defeito)) !== '' ? osTextoExibicao($os->defeito) : null,
    'Observações' => trim(strip_tags((string) $os->observacoes)) !== '' ? osTextoExibicao($os->observacoes) : null,
    'Laudo técnico' => trim(strip_tags((string) $os->laudoTecnico)) !== '' ? osTextoExibicao($os->laudoTecnico) : null,
]);
?>
<div class="flex flex-col gap-4">
    <div>
        <?= component('button', ['label' => 'Ordens de serviço', 'icon' => 'chevron-left', 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('mine/os'), 'class' => '-ml-3']) ?>
    </div>

    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display text-heading-xl text-text"><?= e('OS #' . $idOs) ?></h1>
                <?= component('pill-status', osStatusPill($os->status)) ?>
            </div>
            <p class="text-caption text-muted"><?= e('Aberta em ' . dataBr($os->dataInicial)) ?></p>
        </div>
        <?= component('button', ['label' => 'Imprimir', 'icon' => 'printer', 'variant' => 'outline', 'href' => site_url('mine/imprimirOs/' . $idOs), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) ?>
    </header>

    <?php // Uma coluna no celular: em duas, o valor em reais não cabe na linha do kpi-card.?>
    <div class="grid gap-4 sm:grid-cols-3">
        <?= component('kpi-card', ['label' => 'Produtos', 'value' => dinheiro($totais['produtos']), 'icon' => 'package']) ?>
        <?= component('kpi-card', ['label' => 'Serviços', 'value' => dinheiro($totais['servicos']), 'icon' => 'wrench']) ?>
        <?= component('kpi-card', ['label' => 'Total', 'value' => dinheiro($totais['total']), 'icon' => 'circle-dollar-sign', 'caption' => $totais['desconto'] > 0 ? 'Com ' . dinheiro($totais['desconto']) . ' de desconto' : null]) ?>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-dados">
            <h2 id="secao-dados" class="text-heading-sm text-text">Dados da OS</h2>
            <dl class="mt-3 flex flex-col gap-3">
                <?php foreach ($dados as $rotulo => $valor) { ?>
                    <div>
                        <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                        <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= e($valor) ?></dd>
                    </div>
                <?php } ?>
            </dl>
        </section>
        <section class="<?= e($caixa) ?> lg:col-span-2" aria-labelledby="secao-equipamento">
            <h2 id="secao-equipamento" class="text-heading-sm text-text">Equipamento e serviço</h2>
            <?php if ($textos === []) { ?>
                <p class="mt-3 text-caption text-muted">A assistência ainda não preencheu os detalhes desta OS.</p>
            <?php } else { ?>
                <dl class="mt-3 grid gap-4 sm:grid-cols-2">
                    <?php foreach ($textos as $rotulo => $valor) { ?>
                        <div>
                            <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                            <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= componenteConteudo($valor) ?></dd>
                        </div>
                    <?php } ?>
                </dl>
            <?php } ?>
        </section>
    </div>

    <section class="flex flex-col gap-3" aria-labelledby="secao-produtos">
        <h2 id="secao-produtos" class="text-heading-sm text-text">Produtos</h2>
        <?php $this->load->view('os/partes/produtos'); ?>
    </section>

    <section class="flex flex-col gap-3" aria-labelledby="secao-servicos">
        <h2 id="secao-servicos" class="text-heading-sm text-text">Serviços</h2>
        <?php $this->load->view('os/partes/servicos'); ?>
    </section>

    <?php $this->load->view('conecte/partes/anexos'); ?>

    <?php $this->load->view('conecte/partes/pix'); ?>
</div>
