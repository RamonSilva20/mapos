<?php
/**
 * Abertura das telas de entrada (DESIGN.md: login do painel e da área do
 * cliente), até o <main>. Fecha com entrada/fim.php.
 *
 *     $entrada = ['titulo' => 'Entrar', 'secundaria' => ['label' => 'Área do cliente', 'href' => site_url('mine'), 'icon' => 'circle-user']];
 *     include APPPATH . 'views/entrada/inicio.php';
 *     ...
 *     include APPPATH . 'views/entrada/fim.php';
 *
 * Superfície de entrada: fundo canvas-dark com a textura de estrelas
 * (imagem, não CSS), top nav escura com a marca e a ação secundária em
 * button-ghost-on-dark. É sempre escura, qualquer que seja o modo de cor do
 * painel, por isso não carrega o tema.js nem a classe .dark (só o
 * color-scheme escuro, para a barra de rolagem; os campos voltam ao claro no
 * escopo .superficie-entrada).
 *
 * @var array{titulo: string, secundaria?: array} $entrada
 */
$nomeSistema = (string) ($this->config->item('app_name') ?: 'Map-OS');
?><!doctype html>
<html lang="pt-br" class="scheme-dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token-name" content="<?= e($this->security->get_csrf_token_name()) ?>">
    <meta name="csrf-cookie-name" content="<?= e(config_item('csrf_cookie_name')) ?>">
    <title><?= e($entrada['titulo'] . ' — ' . $nomeSistema) ?></title>
    <link rel="icon" type="image/png" href="<?= e(base_url('assets/img/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('assets/dist/app.css')) ?>">
    <script type="module" src="<?= e(base_url('assets/js/app.js')) ?>"></script>
</head>
<body class="flex min-h-screen flex-col bg-canvas-dark bg-[url(../img/entrada/estrelas.svg)] font-sans text-on-dark antialiased">
    <header class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-4 py-5 lg:px-8">
        <a href="<?= e(base_url()) ?>" class="flex min-w-0 items-center gap-2.5 rounded-md focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring/50">
            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-md bg-primary text-on-primary"><?= icon('wrench', ['class' => 'size-5']) ?></span>
            <span class="truncate font-display text-[1.375rem] leading-none font-bold tracking-[-0.2px]"><?= e($nomeSistema) ?></span>
        </a>
        <?php if (! empty($entrada['secundaria'])) { ?>
            <?php // 44px no celular (DESIGN.md); abaixo de sm só o ícone, para a marca caber em 320px. ?>
            <?= component('button', $entrada['secundaria'] + ['variant' => 'ghost-on-dark', 'class' => 'max-sm:hidden']) ?>
            <?= component('button', $entrada['secundaria'] + ['variant' => 'ghost-on-dark', 'icon_only' => true, 'class' => 'sm:hidden']) ?>
        <?php } ?>
    </header>
