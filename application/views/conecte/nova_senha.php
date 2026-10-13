<?php
/**
 * Senha nova da área do cliente (#2848), depois do link ou do código de
 * recuperação. POST comum para mine/senhaSalvar, com o token em campo oculto;
 * erro de campo volta para esta tela, token inválido volta para o login.
 *
 * @var string                $token  Token em claro (do link ou digitado)
 * @var array<string, string> $erros  Por campo; _geral para falha ao salvar
 */
$erros ??= [];
$entrada = [
    'titulo' => 'Nova senha',
    'secundaria' => ['label' => 'Voltar ao login', 'href' => site_url('mine'), 'icon' => 'log-in'],
];
include APPPATH . 'views/entrada/inicio.php';
?>
    <main class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-4 py-6 lg:py-12">
        <section class="superficie-entrada w-full rounded-xxl border border-hairline-violet bg-night p-6 text-text sm:p-8" aria-labelledby="titulo-nova-senha">
            <h1 id="titulo-nova-senha" class="font-display text-heading-md text-text">Crie uma senha nova</h1>
            <p class="mt-2 text-body-md text-muted">Pelo menos 8 caracteres. Depois, entre com ela na área do cliente.</p>

            <?php if (isset($erros['_geral'])) { ?>
                <div class="mt-4"><?= component('alert', ['message' => $erros['_geral'], 'variant' => 'danger']) ?></div>
            <?php } ?>

            <form method="post" action="<?= e(site_url('mine/senhaSalvar')) ?>" novalidate class="mt-6 flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
                <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-2">
                    <?= component('input', [
                        'name' => 'senha',
                        'label' => 'Senha nova',
                        'type' => 'password',
                        'required' => true,
                        'autocomplete' => 'new-password',
                        'error' => $erros['senha'] ?? null,
                        'attrs' => ['autofocus' => true, 'minlength' => 8, 'maxlength' => 72, 'data-msg-vazio' => 'Crie uma senha.', 'data-msg-invalido' => 'A senha precisa de pelo menos 8 caracteres.'],
                    ]) ?>
                    <?= component('button', ['label' => 'Mostrar senha', 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'class' => 'mt-7', 'attrs' => ['data-mostrar-senha' => 'campo-senha', 'aria-controls' => 'campo-senha', 'aria-pressed' => 'false']]) ?>
                </div>
                <?= component('input', [
                    'name' => 'senha_confirmacao',
                    'label' => 'Repita a senha',
                    'type' => 'password',
                    'required' => true,
                    'autocomplete' => 'new-password',
                    'error' => $erros['senha_confirmacao'] ?? null,
                    'attrs' => ['maxlength' => 72, 'data-msg-vazio' => 'Repita a senha para confirmar.'],
                ]) ?>
                <?= component('button', [
                    'label' => 'Salvar senha',
                    'type' => 'submit',
                    'size' => 'lg',
                    'icon' => 'save',
                    'class' => 'mt-2 w-full shadow-elev-3',
                    'attrs' => ['data-rotulo-carregando' => 'Salvando…'],
                ]) ?>
            </form>
        </section>
    </main>
<?php include APPPATH . 'views/entrada/fim.php'; ?>
