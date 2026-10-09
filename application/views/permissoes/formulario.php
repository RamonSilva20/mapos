<?php
/**
 * Grupo de permissão, para criar e editar (#2846): nome, situação e as
 * permissões em três blocos.
 *
 * - Matriz dos módulos: uma linha por módulo e as colunas Ver, Adicionar,
 *   Editar e Excluir (permissoes[] com o código, ex. vCliente). Abaixo de
 *   640px a tabela rola na horizontal dentro do cartão.
 * - Relatórios e Sistema: caixas com o nome e uma linha de ajuda.
 * - O módulo permissoes/formulario marca "Ver" ao marcar outra ação do
 *   módulo e tem os botões de marcar e desmarcar tudo.
 *
 * @var object|null $grupo     null ao criar
 * @var array{nome: string, situacao: bool, marcadas: list<string>} $valores
 * @var array<string, string> $erros
 * @var bool        $do_logado Grupo do usuário logado (não pode ser desativado)
 */
$editando = $grupo !== null;
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);
$marcada = static fn (string $codigo) => in_array($codigo, $valores['marcadas'], true);
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
?>
<div class="flex max-w-4xl flex-col gap-4 pt-2 pb-8" <?= js_module('permissoes/formulario') ?>>
    <header>
        <h1 class="font-display text-heading-xl text-text"><?= e($editando ? 'Editar grupo' : 'Novo grupo de permissão') ?></h1>
        <p class="text-caption text-muted"><?= e($editando ? $grupo->nome : 'Marque o que os usuários deste grupo podem ver e fazer.') ?></p>
    </header>

    <?php if ($erroGeral !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
    <?php } elseif ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <?php if ($do_logado) { ?>
        <?= component('alert', ['variant' => 'info', 'message' => 'Este é o grupo do seu usuário: as mudanças valem também para você.']) ?>
    <?php } ?>

    <form method="post" action="<?= e(current_url()) ?>" novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-grupo">
            <h2 id="secao-grupo" class="text-heading-sm text-text">Grupo</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 sm:items-end">
                <?= component('input', ['name' => 'nome', 'label' => 'Nome do grupo', 'required' => true, 'value' => $valores['nome'] !== '' ? $valores['nome'] : null, 'error' => $errosDeCampo['nome'] ?? null, 'attrs' => ['maxlength' => 80, 'autofocus' => ! $editando, 'data-msg-vazio' => 'Informe o nome do grupo.']]) ?>
                <?php if ($editando) { ?>
                    <div class="sm:pb-2">
                        <?= component('switch', ['name' => 'situacao', 'value' => '1', 'label' => 'Grupo ativo', 'checked' => $valores['situacao'], 'disabled' => $do_logado, 'help' => $do_logado ? 'O grupo do seu usuário não pode ser desativado.' : 'Inativo: os usuários do grupo não entram no painel.']) ?>
                        <?php if ($do_logado) { ?><input type="hidden" name="situacao" value="1"><?php } ?>
                        <?php if (isset($errosDeCampo['situacao'])) { ?><p class="mt-1 text-caption text-danger-ink"><?= e($errosDeCampo['situacao']) ?></p><?php } ?>
                    </div>
                <?php } ?>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-modulos">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 id="secao-modulos" class="text-heading-sm text-text">Módulos</h2>
                    <p class="text-caption text-muted">Marcar adicionar, editar ou excluir marca também ver.</p>
                </div>
                <div class="flex gap-2">
                    <?= component('button', ['label' => 'Marcar tudo', 'variant' => 'ghost', 'size' => 'sm', 'attrs' => ['data-permissoes-todas' => '1']]) ?>
                    <?= component('button', ['label' => 'Desmarcar tudo', 'variant' => 'ghost', 'size' => 'sm', 'attrs' => ['data-permissoes-todas' => '0']]) ?>
                </div>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-120 text-body-md">
                    <caption class="sr-only">Permissões por módulo</caption>
                    <thead>
                        <tr class="border-b border-border text-left text-caption text-muted">
                            <th scope="col" class="py-2 pr-4 font-medium">Módulo</th>
                            <?php foreach (PERMISSOES_ACOES as $acao => $rotulo) { ?>
                                <th scope="col" class="px-2 py-2 text-center font-medium"><?= e($rotulo) ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (PERMISSOES_MODULOS as $modulo => $nomeModulo) { ?>
                            <tr class="border-b border-border last:border-0" data-permissoes-modulo>
                                <th scope="row" class="py-2.5 pr-4 text-left font-medium text-text"><?= e($nomeModulo) ?></th>
                                <?php foreach (PERMISSOES_ACOES as $acao => $rotulo) { ?>
                                    <?php $codigo = $acao . $modulo; ?>
                                    <td class="px-2 py-2.5">
                                        <div class="flex justify-center">
                                            <?= component('checkbox', [
                                                'name' => 'permissoes[]',
                                                'id' => 'permissao-' . $codigo,
                                                'value' => $codigo,
                                                'label' => $rotulo . ' — ' . $nomeModulo,
                                                'checked' => $marcada($codigo),
                                                'class' => '[&>span:last-child]:sr-only',
                                                'attrs' => ['data-permissao-acao' => $acao],
                                            ]) ?>
                                        </div>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-relatorios">
            <h2 id="secao-relatorios" class="text-heading-sm text-text">Relatórios</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <?php foreach (PERMISSOES_RELATORIOS as $codigo => $rotulo) { ?>
                    <?= component('checkbox', ['name' => 'permissoes[]', 'id' => 'permissao-' . $codigo, 'value' => $codigo, 'label' => $rotulo, 'checked' => $marcada($codigo)]) ?>
                <?php } ?>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-sistema">
            <h2 id="secao-sistema" class="text-heading-sm text-text">Sistema</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <?php foreach (PERMISSOES_SISTEMA as $codigo => [$rotulo, $ajuda]) { ?>
                    <?= component('checkbox', ['name' => 'permissoes[]', 'id' => 'permissao-' . $codigo, 'value' => $codigo, 'label' => $rotulo, 'help' => $ajuda, 'checked' => $marcada($codigo)]) ?>
                <?php } ?>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <?= component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'href' => site_url('permissoes')]) ?>
            <?= component('button', ['label' => $editando ? 'Salvar alterações' : 'Criar grupo', 'icon' => 'save', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
        </div>
    </form>
</div>
