<?php
/**
 * Login do painel (v5).
 *
 * Página avulsa, fora do layout do painel: o usuário ainda não está logado.
 * O tema (modo e cor de destaque) vem da configuração do sistema, que o
 * Login::index() lê do banco.
 *
 * O envio é feito pelo módulo assets/js/modules/login/formulario.js, que
 * valida no navegador e posta em login/verificarLogin. Sem JavaScript o
 * formulário não envia nada: verificarLogin responde JSON, não uma página.
 *
 * @var array       $configuration  Configurações do tema (app_theme, app_tema_*)
 * @var string|null $erro           Mensagem de erro vinda de um redirect (flashdata)
 *
 * A mensagem de erro fica num alert com um <span data-login-texto>: o texto
 * entra escapado com e() e é ali que o módulo troca a mensagem depois.
 */
$hora = (int) date('H');
$saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
$nomeSistema = (string) $this->config->item('app_name');
$versao = (string) $this->config->item('app_version');
?><!doctype html>
<html lang="pt-br"<?= temaAtributosHtml($configuration ?? []) ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token-name" content="<?= e($this->security->get_csrf_token_name()) ?>">
    <meta name="csrf-cookie-name" content="<?= e(config_item('csrf_cookie_name')) ?>">
    <title><?= e('Entrar — ' . $nomeSistema) ?></title>
    <link rel="icon" type="image/png" href="<?= e(base_url('assets/img/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('assets/dist/app.css')) ?>">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css">
    <script src="<?= e(base_url('assets/js/tema.js')) ?>"></script>
    <script type="module" src="<?= e(base_url('assets/js/app.js')) ?>"></script>
</head>
<body class="min-h-screen bg-bg font-sans text-text antialiased">
    <main class="mx-auto grid min-h-screen max-w-6xl items-center gap-10 px-4 py-8 lg:grid-cols-2 lg:px-8">
        <section class="hidden flex-col items-start gap-3 lg:flex" aria-hidden="true">
            <p class="text-3xl font-semibold text-text"><?= e('Olá! ' . $saudacao . ', bem-vindo') ?></p>
            <p class="text-lg text-muted"><?= e('Ao ' . $this->config->item('app_subname')) ?></p>
            <img src="<?= e(base_url('assets/img/dashboard-animate.svg')) ?>" alt="" class="mt-4 w-full max-w-lg">
        </section>

        <section class="mx-auto w-full max-w-sm">
            <div class="rounded-card border border-border bg-surface p-6 shadow-card sm:p-8">
                <div class="mb-6 flex flex-col items-center gap-2 text-center">
                    <img src="<?= e(base_url('assets/img/logo-mapos-laranja-grande.png')) ?>" alt="<?= e($nomeSistema) ?>" class="h-9 w-auto dark:hidden">
                    <img src="<?= e(base_url('assets/img/logo-mapos-branco-laranja-grande.png')) ?>" alt="<?= e($nomeSistema) ?>" class="hidden h-9 w-auto dark:block">
                    <p class="text-xs text-muted"><?= e('Versão: ' . $versao) ?></p>
                    <h1 class="mt-2 text-lg font-semibold text-text lg:sr-only"><?= e($saudacao . '! Entre na sua conta') ?></h1>
                </div>

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
                        'label' => 'Acessar',
                        'type' => 'submit',
                        'size' => 'lg',
                        'icon' => 'bx-log-in',
                        'id' => 'btn-acessar',
                        'class' => 'w-full',
                        'attrs' => ['data-rotulo-carregando' => 'Entrando…'],
                    ]) ?>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-muted">
                <a href="https://github.com/RamonSilva20/mapos" class="hover:text-text hover:underline"><?= e(date('Y') . ' © Ramon Silva') ?></a>
            </p>
        </section>
    </main>
</body>
</html>
