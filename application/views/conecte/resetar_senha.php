<?php
/**
 * Recuperar a senha da área do cliente (#2848), na superfície de entrada. O
 * e-mail recebe o link e o código (mine/gerarTokenResetarSenha); a resposta é
 * a mesma para e-mail com ou sem cadastro.
 *
 * @var string|null $erro
 */
$entrada = [
    'titulo' => 'Recuperar senha',
    'secundaria' => ['label' => 'Voltar ao login', 'href' => site_url('mine'), 'icon' => 'log-in'],
];
include APPPATH . 'views/entrada/inicio.php';
?>
    <main class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-4 py-6 lg:py-12">
        <section class="superficie-entrada w-full rounded-xxl border border-hairline-violet bg-night p-6 text-text sm:p-8" aria-labelledby="titulo-recuperar">
            <h1 id="titulo-recuperar" class="font-display text-heading-md text-text">Esqueceu a senha?</h1>
            <p class="mt-2 text-body-md text-muted">Informe o e-mail do cadastro. Enviamos um link e um código para criar uma senha nova, válidos por 1 hora.</p>

            <?php if (! empty($erro)) { ?>
                <div class="mt-4"><?= component('alert', ['message' => (string) $erro, 'variant' => 'danger']) ?></div>
            <?php } ?>

            <form method="post" action="<?= e(site_url('mine/gerarTokenResetarSenha')) ?>" novalidate class="mt-6 flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
                <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
                <?= component('input', [
                    'name' => 'email',
                    'label' => 'E-mail',
                    'type' => 'email',
                    'required' => true,
                    'autocomplete' => 'email',
                    'placeholder' => 'voce@email.com.br',
                    'attrs' => ['autofocus' => true, 'inputmode' => 'email', 'maxlength' => 100, 'data-msg-vazio' => 'Informe o e-mail.', 'data-msg-invalido' => 'Informe um e-mail válido.'],
                ]) ?>
                <?= component('button', [
                    'label' => 'Enviar instruções',
                    'type' => 'submit',
                    'size' => 'lg',
                    'icon' => 'mail',
                    'class' => 'mt-2 w-full shadow-elev-3',
                    'attrs' => ['data-rotulo-carregando' => 'Enviando…'],
                ]) ?>
            </form>

            <p class="mt-6 text-body-md">
                <a href="<?= e(site_url('mine/tokenManual')) ?>" class="rounded-xs text-on-dark underline underline-offset-4 hover:decoration-2 focus-visible:outline-3 focus-visible:outline-ring max-sm:inline-flex max-sm:min-h-11 max-sm:items-center">Já tenho o código</a>
            </p>
        </section>
    </main>
<?php include APPPATH . 'views/entrada/fim.php'; ?>
