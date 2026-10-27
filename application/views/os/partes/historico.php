<?php
/**
 * Linha do tempo dos status da OS (#2842), da mudança mais recente à mais
 * antiga (tabela os_historico). OS anteriores a esta versão não têm
 * histórico.
 *
 * @var list<object> $historico
 * @var bool         $historico_disponivel
 */
if (! $historico_disponivel) { ?>
    <?= component('alert', [
        'variant' => 'warning',
        'title' => 'Histórico ainda não disponível',
        'message' => 'O banco de dados precisa ser atualizado para registrar o histórico de status. Peça ao administrador para usar Configurações > Atualizar banco de dados.',
    ]) ?>
<?php return;
}

if ($historico === []) { ?>
    <?= component('empty-state', [
        'title' => 'Histórico disponível a partir desta versão',
        'message' => 'As próximas mudanças de status desta OS aparecem aqui, com quem mudou e quando.',
        'icon' => 'history',
    ]) ?>
<?php return;
}
?>
<?php
// Cor da bolinha por status (nomes completos, para o Tailwind detectar). A
// mudança atual (primeira) ganha anel e tamanho maior.
$timelineDot = [
    'success' => 'bg-success-ink',
    'warning' => 'bg-warning-ink',
    'danger' => 'bg-danger-ink',
    'info' => 'bg-info-ink',
    'progress' => 'bg-progress-ink',
    'neutral' => 'bg-neutral-ink',
];
$timelineAnel = [
    'success' => 'ring-4 ring-success-ink/20',
    'warning' => 'ring-4 ring-warning-ink/20',
    'danger' => 'ring-4 ring-danger-ink/20',
    'info' => 'ring-4 ring-info-ink/20',
    'progress' => 'ring-4 ring-progress-ink/20',
    'neutral' => 'ring-4 ring-neutral-ink/20',
];
?>
<ol class="flex flex-col gap-6 border-l-2 border-border pl-6" aria-label="Histórico de status">
    <?php foreach ($historico as $i => $mudanca) {
        $pill = osStatusPill($mudanca->status_novo);
        $variante = $pill['variant'] ?? 'neutral';
        $atual = $i === 0;
        $autor = $mudanca->autor ?? ($mudanca->usuarios_id === null ? 'Cliente, pela área do cliente' : 'Usuário removido');
        $quando = date('d/m/Y \à\s H:i', strtotime((string) $mudanca->data_hora));
        ?>
        <li class="relative">
            <span class="absolute top-1 <?= e($atual ? '-left-[2.05rem] size-4 ' . ($timelineAnel[$variante] ?? '') : '-left-[1.9rem] size-3') ?> rounded-full border-2 border-surface <?= e($timelineDot[$variante] ?? 'bg-neutral-ink') ?>" aria-hidden="true"></span>
            <div class="flex flex-wrap items-center gap-2">
                <?= component('pill-status', $pill) ?>
                <?php if ($atual) { ?>
                    <span class="text-caption font-semibold text-text">Status atual</span>
                <?php } elseif ($mudanca->status_anterior !== null) { ?>
                    <span class="text-caption text-muted">veio de <?= e($mudanca->status_anterior) ?></span>
                <?php } else { ?>
                    <span class="text-caption text-muted">OS aberta</span>
                <?php } ?>
            </div>
            <p class="mt-1 text-caption text-muted"><?= e($autor . ' · ' . $quando) ?></p>
        </li>
    <?php } ?>
</ol>
