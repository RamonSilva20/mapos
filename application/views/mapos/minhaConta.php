<?php
/**
 * Minha conta (#2855): dados do usuário logado, a foto (modal de envio) e a
 * troca de senha, no padrão dos formulários (#2851): POST comum, validação no
 * navegador e no servidor, erro em cada campo.
 *
 * @var object                $usuario
 * @var string                $avatar    URL da foto, ou da imagem padrão
 * @var bool                  $tem_foto
 * @var bool                  $pode_foto Permissão cUsuario (a mesma da ação de enviar)
 * @var array<string, string> $erros     oldSenha, novaSenha, confirmarSenha
 */
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$dados = array_filter([
    'Nome' => (string) $usuario->nome,
    'E-mail' => (string) $usuario->email,
    'Telefone' => (string) $usuario->telefone,
    'Nível de acesso' => (string) $usuario->permissao,
    'Acesso expira em' => dataBr($usuario->dataExpiracao) ?: 'Sem data de expiração',
], static fn ($valor) => trim($valor) !== '');
$errosDeCampo = $erros;
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Minha conta</h1>
        <p class="text-caption text-muted">Seus dados, a foto e a senha de acesso ao sistema.</p>
    </header>

    <?php if ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-dados">
            <h2 id="secao-dados" class="text-heading-sm text-text">Meus dados</h2>

            <div class="mt-4 flex items-center gap-4">
                <?php if ($tem_foto) { ?>
                    <img src="<?= e($avatar) ?>" alt="Sua foto" class="size-24 shrink-0 rounded-full border border-border object-cover">
                <?php } else { ?>
                    <span class="inline-flex size-24 shrink-0 items-center justify-center rounded-full bg-accent-violet-mid font-display text-heading-lg text-on-dark" aria-hidden="true"><?= e(layoutIniciais((string) $usuario->nome)) ?></span>
                <?php } ?>
                <?php if ($pode_foto) { ?>
                    <?= component('button', ['label' => $tem_foto ? 'Trocar foto' : 'Enviar foto', 'icon' => 'image', 'variant' => 'outline', 'attrs' => ['data-modal-abrir' => 'trocar-foto']]) ?>
                <?php } ?>
            </div>

            <dl class="mt-5 flex flex-col gap-3">
                <?php foreach ($dados as $rotulo => $valor) { ?>
                    <div>
                        <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                        <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= e($valor) ?></dd>
                    </div>
                <?php } ?>
            </dl>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-2fa">
            <div class="flex flex-wrap items-center gap-2">
                <h2 id="secao-2fa" class="text-heading-sm text-text">Autenticação em dois fatores</h2>
                <?= component('pill-status', ['label' => $dois_fatores_ativo ? 'Ativada' : 'Desativada', 'variant' => $dois_fatores_ativo ? 'success' : 'neutral']) ?>
            </div>
            <p class="mt-0.5 text-caption text-muted">Além da senha, o login pede um código do aplicativo autenticador no seu celular.</p>
            <div class="mt-4"><?= component('button', ['label' => $dois_fatores_ativo ? 'Gerenciar' : 'Configurar', 'icon' => 'shield-check', 'variant' => 'outline', 'href' => site_url('mapos/doisFatores')]) ?></div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-senha">
            <h2 id="secao-senha" class="text-heading-sm text-text">Alterar minha senha</h2>
            <p class="mt-0.5 text-caption text-muted">A nova senha precisa de pelo menos 8 caracteres.</p>

            <form method="post" action="<?= e(site_url('mapos/alterarSenha')) ?>" novalidate class="mt-4 flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
                <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
                <?= component('input', [
                    'name' => 'oldSenha',
                    'label' => 'Senha atual',
                    'type' => 'password',
                    'required' => true,
                    'autocomplete' => 'current-password',
                    'error' => $errosDeCampo['oldSenha'] ?? null,
                    'attrs' => ['maxlength' => 72, 'data-msg-vazio' => 'Informe a senha atual.'],
                ]) ?>
                <?= component('input', [
                    'name' => 'novaSenha',
                    'label' => 'Nova senha',
                    'type' => 'password',
                    'required' => true,
                    'autocomplete' => 'new-password',
                    'error' => $errosDeCampo['novaSenha'] ?? null,
                    'attrs' => ['minlength' => 8, 'maxlength' => 72, 'data-msg-vazio' => 'Crie uma senha nova.', 'data-msg-invalido' => 'A senha precisa de pelo menos 8 caracteres.'],
                ]) ?>
                <?= component('input', [
                    'name' => 'confirmarSenha',
                    'label' => 'Repita a nova senha',
                    'type' => 'password',
                    'required' => true,
                    'autocomplete' => 'new-password',
                    'error' => $errosDeCampo['confirmarSenha'] ?? null,
                    'attrs' => ['maxlength' => 72, 'data-msg-vazio' => 'Repita a senha nova para confirmar.'],
                ]) ?>
                <div class="flex sm:justify-end">
                    <?= component('button', ['label' => 'Alterar senha', 'icon' => 'key-round', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
                </div>
            </form>
        </section>
    </div>
</div>

<?php if ($pode_foto) { ?>
    <form id="form-foto" method="post" action="<?= e(site_url('mapos/uploadUserImage')) ?>" enctype="multipart/form-data" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
    </form>
    <?= component('modal', [
        'id' => 'trocar-foto',
        'title' => 'Foto do usuário',
        'size' => 'sm',
        'body' => [
            new HtmlSeguro('<div class="flex flex-col gap-1.5">'),
            new HtmlSeguro('<label for="foto-arquivo" class="text-label-md text-text">Imagem <span class="text-danger-ink" aria-hidden="true">*</span></label>'),
            new HtmlSeguro('<input type="file" id="foto-arquivo" name="userfile" form="form-foto" required accept=".png,.jpg,.jpeg,.bmp" aria-describedby="foto-ajuda" class="block w-full rounded-sm border border-input bg-field px-3 py-2 text-body-md text-text file:mr-3 file:rounded-sm file:border-0 file:bg-surface-subtle file:px-3 file:py-1 file:text-label-md file:text-text focus:outline-3 focus:outline-offset-0 focus:outline-ring">'),
            new HtmlSeguro('<p id="foto-ajuda" class="text-caption text-muted">PNG, JPG ou BMP, até 2 MB. Tamanho indicado: 130 × 130 px.</p>'),
            new HtmlSeguro('</div>'),
        ],
        'footer' => [
            component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'attrs' => ['data-modal-fechar' => true]]),
            component('button', ['label' => 'Enviar foto', 'icon' => 'upload', 'type' => 'submit', 'attrs' => ['form' => 'form-foto', 'data-rotulo-carregando' => 'Enviando…']]),
        ],
    ]) ?>
<?php } ?>
