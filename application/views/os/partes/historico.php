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
<ol class="flex flex-col gap-5 border-l-2 border-border pl-6" aria-label="Histórico de status">
    <?php foreach ($historico as $mudanca) { ?>
        <?php
        $autor = $mudanca->autor ?? ($mudanca->usuarios_id === null ? 'Cliente, pela área do cliente' : 'Usuário removido');
        $quando = date('d/m/Y \à\s H:i', strtotime((string) $mudanca->data_hora));
        ?>
        <li class="relative">
            <span class="absolute top-1.5 -left-[1.9rem] size-3 rounded-full border-2 border-surface bg-muted" aria-hidden="true"></span>
            <div class="flex flex-wrap items-center gap-2">
                <?= component('pill-status', osStatusPill($mudanca->status_novo)) ?>
                <span class="text-body-md text-text">
                    <?= e($mudanca->status_anterior === null ? 'OS aberta' : 'Antes: ' . $mudanca->status_anterior) ?>
                </span>
            </div>
            <p class="mt-1 text-caption text-muted"><?= e($autor . ' · ' . $quando) ?></p>
        </li>
    <?php } ?>
</ol>
