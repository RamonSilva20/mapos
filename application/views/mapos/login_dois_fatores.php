<?php
/**
 * Segundo passo do login do painel (#2876): o código de 6 dígitos do aplicativo
 * autenticador ou um código de recuperação. Superfície de entrada, como o
 * login (views/mapos/login.php). Formulário comum, com o token CSRF; o
 * servidor confere (Login::verificarDoisFatores) e volta aqui com o erro.
 *
 * @var string|null $erro Mensagem de erro (flashdata)
 */
$entrada = [
    'titulo' => 'Verificação em dois fatores',
    'secundaria' => ['label' => 'Voltar ao login', 'href' => site_url('login/sair'), 'icon' => 'log-in'],
];
include APPPATH . 'views/entrada/inicio.php';
?>
    <main class="mx-auto flex w-full max-w-md flex-1 items-center px-4 py-8">
        <section class="superficie-entrada w-full rounded-xxl border border-hairline-violet bg-night p-6 text-text sm:p-8" aria-labelledby="titulo-2fa">
            <h1 id="titulo-2fa" class="font-display text-heading-md text-text">Confirme que é você</h1>
            <p class="mt-2 mb-6 text-caption text-muted">Digite o código de 6 dígitos do aplicativo autenticador. Sem o celular, use um dos códigos de recuperação.</p>

            <form method="post" action="<?= e(site_url('login/verificarDoisFatores')) ?>" novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
                <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

                <?php if (! empty($erro)) { ?>
                    <?= component('alert', ['variant' => 'danger', 'message' => $erro]) ?>
                <?php } ?>

                <?= component('input', [
                    'name' => 'codigo',
                    'id' => 'codigo',
                    'label' => 'Código',
                    'required' => true,
                    'autocomplete' => 'one-time-code',
                    'help' => 'Seis dígitos, ou um código de recuperação (abcd-efgh-ijkl).',
                    'attrs' => ['autofocus' => true, 'inputmode' => 'text', 'maxlength' => 20, 'spellcheck' => 'false', 'autocapitalize' => 'none', 'data-msg-vazio' => 'Digite o código.'],
                ]) ?>

                <?= component('button', [
                    'label' => 'Verificar',
                    'type' => 'submit',
                    'size' => 'lg',
                    'icon' => 'shield-check',
                    'class' => 'mt-2 w-full shadow-elev-3',
                    'attrs' => ['data-rotulo-carregando' => 'Verificando…'],
                ]) ?>
            </form>
        </section>
    </main>
<?php include APPPATH . 'views/entrada/fim.php'; ?>
