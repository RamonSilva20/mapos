<?php
/**
 * Cadastro pela área do cliente (#2848), na superfície de entrada (DESIGN.md,
 * views/entrada/): o formulário fica num card .superficie-entrada, com os
 * campos claros. Padrão dos formulários (#2851): POST comum, validação no
 * navegador e no servidor, erro em cada campo; o captcha vale para uma
 * tentativa e a imagem é nova a cada carga.
 *
 * @var array<string, string|bool> $valores
 * @var array<string, string>      $erros  Por campo; _geral para falha ao salvar
 */
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);

$entrada = [
    'titulo' => 'Criar conta',
    'secundaria' => ['label' => 'Já tenho conta', 'href' => site_url('mine'), 'icon' => 'log-in'],
];
include APPPATH . 'views/entrada/inicio.php';
?>
    <main class="mx-auto w-full max-w-3xl flex-1 px-4 py-6 lg:py-10" <?= js_module('clientes/formulario') ?>>
        <header class="mb-6">
            <h1 class="font-display text-display-hero-sm font-bold text-on-dark sm:text-display-large">Crie sua <span class="rounded-xs bg-accent-lime px-3 text-ink">conta</span>.</h1>
            <p class="mt-2 max-w-xl text-body-lg text-on-dark-muted">Acompanhe as ordens de serviço, as compras e as cobranças pela área do cliente.</p>
        </header>

        <div class="superficie-entrada flex flex-col gap-4 rounded-xxl border border-hairline-violet bg-night p-4 text-text sm:p-8">
            <?php if ($erroGeral !== null) { ?>
                <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
            <?php } elseif ($errosDeCampo !== []) { ?>
                <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
            <?php } ?>

            <form method="post" action="<?= e(site_url('mine/cadastrar')) ?>" novalidate class="flex flex-col gap-6" <?= js_module('formulario/padrao') ?>>
                <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

                <?php $this->load->view('conecte/partes/campos_cliente', ['erros' => $errosDeCampo, 'novo' => true, 'caixa' => 'border-t border-border pt-6', 'caixa_primeira' => '']); ?>

                <section class="border-t border-border pt-6" aria-labelledby="secao-captcha">
                    <h2 id="secao-captcha" class="text-heading-sm text-text">Confirme que você não é um robô</h2>
                    <div class="mt-4 grid items-start gap-4 sm:grid-cols-2">
                        <img src="<?= e(site_url('mine/captcha') . '?' . bin2hex(random_bytes(4))) ?>" alt="Imagem com os caracteres a digitar" width="180" height="50" class="h-[50px] w-[180px] rounded-md border border-border">
                        <?= component('input', [
                            'name' => 'captcha',
                            'label' => 'Caracteres da imagem',
                            'required' => true,
                            'autocomplete' => 'off',
                            'help' => 'Não diferencia maiúsculas de minúsculas.',
                            'error' => $errosDeCampo['captcha'] ?? null,
                            'attrs' => ['maxlength' => 10, 'autocapitalize' => 'off', 'spellcheck' => 'false', 'data-msg-vazio' => 'Digite os caracteres da imagem.'],
                        ]) ?>
                    </div>
                </section>

                <?= component('button', [
                    'label' => 'Criar conta',
                    'type' => 'submit',
                    'size' => 'lg',
                    'icon' => 'circle-user',
                    // DESIGN.md: na superfície de entrada o button-primary leva o halo nível 3.
                    'class' => 'w-full shadow-elev-3 sm:ml-auto sm:w-auto',
                    'attrs' => ['data-rotulo-carregando' => 'Criando…'],
                ]) ?>
            </form>
        </div>
    </main>
<?php include APPPATH . 'views/entrada/fim.php'; ?>
