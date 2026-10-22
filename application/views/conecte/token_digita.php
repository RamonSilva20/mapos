<?php
/**
 * Digitar o código de recuperação de senha (#2848), para quem não consegue
 * abrir o link do e-mail. Código válido leva à tela da senha nova; inválido,
 * expirado ou usado volta ao login com a mensagem genérica.
 *
 * @var string|null $erro
 */
$entrada = [
    'titulo' => 'Código de recuperação',
    'secundaria' => ['label' => 'Voltar ao login', 'href' => site_url('mine'), 'icon' => 'log-in'],
];
include APPPATH . 'views/entrada/inicio.php';
?>
    <main class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-4 py-6 lg:py-12">
        <section class="superficie-entrada w-full rounded-xxl border border-hairline-violet bg-night p-6 text-text sm:p-8" aria-labelledby="titulo-codigo">
            <h1 id="titulo-codigo" class="font-display text-heading-md text-text">Digite o código</h1>
            <p class="mt-2 text-body-md text-muted">Cole o código que chegou no e-mail de recuperação de senha.</p>

            <form method="post" action="<?= e(site_url('mine/tokenManual')) ?>" novalidate class="mt-6 flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
                <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
                <?= component('input', [
                    'name' => 'token',
                    'label' => 'Código',
                    'required' => true,
                    'autocomplete' => 'one-time-code',
                    'error' => $erro ?? null,
                    'class' => 'font-mono',
                    'attrs' => ['autofocus' => true, 'maxlength' => 128, 'autocapitalize' => 'off', 'spellcheck' => 'false', 'data-msg-vazio' => 'Cole o código que chegou no seu e-mail.'],
                ]) ?>
                <?= component('button', [
                    'label' => 'Continuar',
                    'type' => 'submit',
                    'size' => 'lg',
                    'icon' => 'key-round',
                    'class' => 'mt-2 w-full shadow-elev-3',
                    'attrs' => ['data-rotulo-carregando' => 'Conferindo…'],
                ]) ?>
            </form>

            <p class="mt-6 text-body-md">
                <a href="<?= e(site_url('mine/resetarSenha')) ?>" class="rounded-xs text-on-dark underline underline-offset-4 hover:decoration-2 focus-visible:outline-3 focus-visible:outline-ring max-sm:inline-flex max-sm:min-h-11 max-sm:items-center">Pedir um código novo</a>
            </p>
        </section>
    </main>
<?php include APPPATH . 'views/entrada/fim.php'; ?>
