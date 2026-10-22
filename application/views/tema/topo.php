<?php
/**
 * Layout do painel (#2835): <head> e abertura do <body>.
 *
 * Montado por MY_Controller::layout(), que carrega, nesta ordem, tema/topo,
 * tema/menu, tema/conteudo e tema/rodape. Os dados vêm prontos em $layout
 * (ver application/helpers/layout_helper.php):
 *
 * - assets: CSS e módulos JS da página (layoutAssets()).
 *
 * @var array $configuration
 * @var array $layout
 */
?><!DOCTYPE html>
<html lang="pt-br"<?= temaAtributosHtml($configuration) ?>>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e(layoutTitulo($layout['breadcrumb'], (string) ($configuration['app_name'] ?: 'Map-OS'))) ?></title>
  <meta name="csrf-token-name" content="<?= e(config_item('csrf_token_name')) ?>">
  <meta name="csrf-cookie-name" content="<?= e(config_item('csrf_cookie_name')) ?>">
  <meta name="base-url" content="<?= e(base_url()) ?>">
  <?php if (function_exists('segurancaNonceAtual') && segurancaNonceAtual() !== '') { ?>
  <meta name="csp-nonce" content="<?= e(segurancaNonceAtual()) ?>">
  <?php } ?>
  <link rel="shortcut icon" type="image/png" href="<?= e(base_url('assets/img/favicon.png')) ?>">
  <script src="<?= e(base_url('assets/js/tema.js')) ?>"></script>
  <?php foreach ($layout['assets']['css'] as $arquivo) { ?>
  <link rel="stylesheet" href="<?= e(layoutUrlAsset($arquivo, base_url())) ?>">
  <?php } ?>
  <?php foreach ($layout['assets']['modulos'] as $arquivo) { ?>
  <script type="module" src="<?= e(layoutUrlAsset($arquivo, base_url())) ?>"></script>
  <?php } ?>
</head>

<body class="v5-layout" <?= js_module('layout/shell') ?> data-tema-url="<?= e(site_url('mapos/salvarTemaModo')) ?>">
  <div class="v5-shell">
    <a href="#conteudo-principal" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:rounded-control focus:bg-surface focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-text focus:shadow-overlay">Pular para o conteúdo</a>
  </div>
