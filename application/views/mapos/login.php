<?php
/**
 * Login do painel (v5), como superfície de entrada do DESIGN.md (#2918).
 *
 * Página avulsa, fora do layout do painel: o usuário ainda não está logado.
 * A moldura (fundo escuro com estrelas, marca, rodapé) vem de
 * views/entrada/inicio.php e fim.php; a área do cliente fica na top nav, em
 * button-ghost-on-dark.
 *
 * O envio é feito pelo módulo assets/js/modules/login/formulario.js, que
 * valida no navegador e posta em login/verificarLogin. Sem JavaScript o
 * formulário não envia nada: verificarLogin responde JSON, não uma página.
 *
 * @var string|null $erro  Mensagem de erro vinda de um redirect (flashdata)
 *
 * A mensagem de erro fica num alert com um <span data-login-texto>: o texto
 * entra escapado com e() e é ali que o módulo troca a mensagem depois.
 */
$saudacao = layoutSaudacao((int) date('H'));
$versao = (string) $this->config->item('app_version');
$subtitulo = (string) $this->config->item('app_subname');

$entrada = [
    'titulo' => 'Entrar',
    'secundaria' => ['label' => 'Área do cliente', 'href' => site_url('mine'), 'icon' => 'circle-user'],
];
include APPPATH . 'views/entrada/inicio.php';
?>
    <main class="mx-auto grid w-full max-w-6xl flex-1 items-center gap-8 px-4 py-6 lg:grid-cols-[1fr_26rem] lg:gap-16 lg:px-8 lg:py-12">
        <section class="flex flex-col items-start gap-4">
            <?php if ($versao !== '') { ?>
                <span class="rounded-xs bg-night px-2 py-1 text-caption text-on-dark max-sm:hidden"><?= e('Versão ' . $versao) ?></span>
            <?php } ?>
            <h1 class="font-display text-heading-xl font-bold text-on-dark sm:text-display-large xl:text-display-hero">
                Sua oficina em <span class="rounded-xs bg-accent-lime px-3 text-ink">ordem</span>.
            </h1>
            <?php if ($subtitulo !== '') { ?>
                <p class="max-w-xl text-body-lg text-on-dark-muted max-sm:hidden"><?= e($subtitulo) ?></p>
            <?php } ?>
        </section>

        <section class="superficie-entrada w-full rounded-xxl border border-hairline-violet bg-night p-6 text-text sm:p-8" aria-labelledby="titulo-login">
            <h2 id="titulo-login" class="mb-6 font-display text-heading-md text-text"><?= e($saudacao . '! Entre na sua conta') ?></h2>

            <form id="formLogin" method="post" action="<?= e(site_url('login/verificarLogin?ajax=true')) ?>" novalidate class="flex flex-col gap-4" <?= js_module('login/formulario') ?> data-destino="<?= e(site_url('mapos')) ?>">
                <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

                <div data-login-mensagem<?= empty($erro) ? ' hidden' : '' ?>>
                    <?= component('alert', ['message' => new HtmlSeguro('<span data-login-texto>' . e($erro ?? '') . '</span>'), 'variant' => 'danger']) ?>
                </div>

                <?= component('input', [
                    'name' => 'email',
                    'id' => 'email',
                    'label' => 'E-mail',
                    'type' => 'email',
                    'required' => true,
                    'autocomplete' => 'username',
                    'placeholder' => 'voce@empresa.com.br',
                    'attrs' => ['autofocus' => true, 'inputmode' => 'email', 'data-msg-vazio' => 'Informe o e-mail.', 'data-msg-invalido' => 'Informe um e-mail válido.'],
                ]) ?>

                <?= component('input', [
                    'name' => 'senha',
                    'id' => 'senha',
                    'label' => 'Senha',
                    'type' => 'password',
                    'required' => true,
                    'autocomplete' => 'current-password',
                    'attrs' => ['data-msg-vazio' => 'Informe a senha.'],
                ]) ?>

                <?= component('button', [
                    'label' => 'Entrar',
                    'type' => 'submit',
                    'size' => 'lg',
                    'icon' => 'log-in',
                    'id' => 'btn-acessar',
                    // DESIGN.md: na superfície de entrada o button-primary leva o halo nível 3.
                    'class' => 'mt-2 w-full shadow-elev-3',
                    'attrs' => ['data-rotulo-carregando' => 'Entrando…'],
                ]) ?>
            </form>
        </section>
    </main>
<?php include APPPATH . 'views/entrada/fim.php'; ?>
