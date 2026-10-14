<?php
/**
 * Autenticação em dois fatores da própria conta (#2876).
 *
 * - Desativada: QR code para o aplicativo autenticador, o segredo para digitar
 *   à mão e o campo do código de confirmação (button-primary "Ativar").
 * - Recém-ativada (ou com novos códigos): os códigos de recuperação, mostrados
 *   uma única vez.
 * - Ativa: quantos códigos restam, "Gerar novos códigos" e "Desativar" (os
 *   dois pedem a senha; um grupo que exige o 2FA não deixa desativar).
 *
 * @var bool                  $ativo
 * @var bool                  $exigido    O grupo de permissão exige o 2FA
 * @var int                   $restantes  Códigos de recuperação ainda não usados
 * @var list<string>|null     $codigos    Códigos recém-gerados
 * @var array<string, string> $erros      codigo, senha_ativar, senha_codigos, senha_desativar, _geral
 * @var string|null           $qr         Data URI do QR code (só desativado)
 * @var string|null           $segredo    Segredo em grupos de 4 letras
 */
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
?>
<div class="flex max-w-3xl flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Autenticação em dois fatores</h1>
        <p class="text-caption text-muted">Além da senha, o login pede um código de 6 dígitos gerado no seu celular.</p>
    </header>

    <?php if ($exigido && ! $ativo) { ?>
        <?= component('alert', ['variant' => 'warning', 'message' => 'O seu grupo de permissão exige a autenticação em dois fatores. Ative abaixo para usar o sistema.']) ?>
    <?php } ?>
    <?php if (isset($erros['_geral'])) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erros['_geral']]) ?>
    <?php } ?>

    <?php if ($codigos !== null) { ?>
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-codigos">
            <h2 id="secao-codigos" class="text-heading-sm text-text">Códigos de recuperação</h2>
            <p class="mt-1 text-caption text-muted">Guarde estes códigos em um lugar seguro. Cada um vale uma vez e substitui o aplicativo se você perder o celular. <strong class="font-medium text-text">Eles não aparecem de novo.</strong></p>
            <ul class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3" data-codigos>
                <?php foreach ($codigos as $codigo) { ?>
                    <li class="rounded-md border border-border bg-surface-subtle px-3 py-2 text-center font-mono text-sm text-text"><?= e($codigo) ?></li>
                <?php } ?>
            </ul>
            <div class="mt-4 flex justify-end">
                <?= component('button', ['label' => 'Concluir', 'href' => site_url('mapos'), 'icon' => 'check']) ?>
            </div>
        </section>
    <?php } elseif (! $ativo) { ?>
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-ativar">
            <h2 id="secao-ativar" class="text-heading-sm text-text">Ativar</h2>
            <ol class="mt-3 list-decimal space-y-1 pl-5 text-caption text-muted">
                <li>Instale um aplicativo autenticador (Google Authenticator, Microsoft Authenticator, Authy, 1Password...).</li>
                <li>Leia o QR code, ou digite a chave abaixo no aplicativo.</li>
                <li>Digite o código de 6 dígitos que o aplicativo mostrar.</li>
            </ol>
            <div class="mt-4 flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                <img src="<?= e($qr) ?>" alt="QR code para o aplicativo autenticador" width="200" height="200" class="size-[200px] rounded-md border border-border">
                <div class="min-w-0 flex-1">
                    <p class="text-label-md text-text">Chave para digitar</p>
                    <p class="mt-1 rounded-md border border-border bg-surface-subtle px-3 py-2 font-mono text-sm text-text" data-segredo><?php foreach (explode(' ', $segredo) as $grupo) { ?><span class="inline-block whitespace-nowrap"><?= e($grupo) ?></span> <?php } ?></p>
                    <form method="post" action="<?= e(site_url('mapos/ativarDoisFatores')) ?>" novalidate class="mt-4 flex flex-col gap-3" <?= js_module('formulario/padrao') ?>>
                        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
                        <?= component('input', ['name' => 'senha', 'label' => 'Sua senha', 'type' => 'password', 'required' => true, 'autocomplete' => 'current-password', 'error' => $erros['senha_ativar'] ?? null, 'attrs' => ['data-msg-vazio' => 'Informe a senha.']]) ?>
                        <?= component('input', ['name' => 'codigo', 'label' => 'Código do aplicativo', 'required' => true, 'autocomplete' => 'one-time-code', 'error' => $erros['codigo'] ?? null, 'attrs' => ['inputmode' => 'numeric', 'maxlength' => 7, 'pattern' => '[0-9 ]{6,7}', 'data-msg-vazio' => 'Digite o código de 6 dígitos.']]) ?>
                        <div><?= component('button', ['label' => 'Ativar', 'type' => 'submit', 'icon' => 'shield-check', 'attrs' => ['data-rotulo-carregando' => 'Verificando…']]) ?></div>
                    </form>
                </div>
            </div>
        </section>
    <?php } else { ?>
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-estado">
            <div class="flex flex-wrap items-center gap-2">
                <h2 id="secao-estado" class="text-heading-sm text-text">Ativada</h2>
                <?= component('pill-status', ['label' => 'Protegida', 'variant' => 'success']) ?>
            </div>
            <p class="mt-1 text-caption text-muted">Códigos de recuperação restantes: <strong class="font-medium text-text"><?= e((string) $restantes) ?></strong>. <?= $restantes <= 2 ? 'Gere novos códigos em breve.' : '' ?></p>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-novos">
            <h2 id="secao-novos" class="text-heading-sm text-text">Novos códigos de recuperação</h2>
            <p class="mt-1 text-caption text-muted">Gera 10 códigos novos; os anteriores deixam de valer. Confirme com a sua senha.</p>
            <form method="post" action="<?= e(site_url('mapos/novosCodigosDoisFatores')) ?>" novalidate class="mt-4 flex flex-col gap-3 sm:max-w-sm" <?= js_module('formulario/padrao') ?>>
                <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
                <?= component('input', ['name' => 'senha', 'label' => 'Sua senha', 'type' => 'password', 'required' => true, 'autocomplete' => 'current-password', 'error' => $erros['senha_codigos'] ?? null, 'attrs' => ['data-msg-vazio' => 'Informe a senha.']]) ?>
                <div><?= component('button', ['label' => 'Gerar novos códigos', 'type' => 'submit', 'variant' => 'outline', 'icon' => 'key-round']) ?></div>
            </form>
        </section>

        <?php if (! $exigido) { ?>
            <section class="<?= e($caixa) ?>" aria-labelledby="secao-desativar">
                <h2 id="secao-desativar" class="text-heading-sm text-text">Desativar</h2>
                <p class="mt-1 text-caption text-muted">O login volta a pedir só e-mail e senha. Confirme com a sua senha.</p>
                <form method="post" action="<?= e(site_url('mapos/desativarDoisFatores')) ?>" novalidate class="mt-4 flex flex-col gap-3 sm:max-w-sm" <?= js_module('formulario/padrao') ?>>
                    <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
                    <?= component('input', ['name' => 'senha', 'label' => 'Sua senha', 'type' => 'password', 'required' => true, 'autocomplete' => 'current-password', 'error' => $erros['senha_desativar'] ?? null, 'attrs' => ['data-msg-vazio' => 'Informe a senha.']]) ?>
                    <div><?= component('button', ['label' => 'Desativar o 2FA', 'type' => 'submit', 'variant' => 'outline', 'icon' => 'shield-off']) ?></div>
                </form>
            </section>
        <?php } ?>
    <?php } ?>
</div>
