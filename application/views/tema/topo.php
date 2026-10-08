<?php
/**
 * Layout do painel (#2835): <head> e abertura do <body>.
 *
 * Montado por MY_Controller::layout(), que carrega, nesta ordem, tema/topo,
 * tema/menu, tema/conteudo e tema/rodape. Os dados vêm prontos em $layout
 * (ver application/helpers/layout_helper.php):
 *
 * - legado: a tela ainda usa Bootstrap 2/jQuery (legacy_assets, o padrão);
 * - assets: CSS e JS da página (layoutAssets());
 * - dados_legado: URLs dos atalhos e configuração do DataTables, lidos pelos
 *   scripts de assets/js/legado/ no lugar do JavaScript inline da v4.
 *
 * @var array $configuration
 * @var array $layout
 */
?><!DOCTYPE html>
<html lang="pt-br"<?= temaAtributosHtml($configuration) ?><?= $layout['legado'] ? ' data-layout-legado' : '' ?>>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($configuration['app_name'] ?: 'Map-OS') ?></title>
  <meta name="csrf-token-name" content="<?= e(config_item('csrf_token_name')) ?>">
  <meta name="csrf-cookie-name" content="<?= e(config_item('csrf_cookie_name')) ?>">
  <meta name="base-url" content="<?= e(base_url()) ?>">
  <link rel="shortcut icon" type="image/png" href="<?= e(base_url('assets/img/favicon.png')) ?>">
  <script src="<?= e(base_url('assets/js/tema.js')) ?>"></script>
  <?php foreach ($layout['assets']['css'] as $arquivo) { ?>
  <link rel="stylesheet" href="<?= e(layoutUrlAsset($arquivo, base_url())) ?>">
  <?php } ?>
  <?php if ($layout['legado']) { ?>
  <?= page_data('layout-legado', $layout['dados_legado']) ?>
  <?php } ?>
  <?php foreach ($layout['assets']['js_cabecalho'] as $arquivo) { ?>
  <script src="<?= e(layoutUrlAsset($arquivo, base_url())) ?>"></script>
  <?php } ?>
  <?php foreach ($layout['assets']['modulos'] as $arquivo) { ?>
  <script type="module" src="<?= e(layoutUrlAsset($arquivo, base_url())) ?>"></script>
  <?php } ?>
</head>

<body class="v5-layout" <?= js_module('layout/shell') ?>>
  <div class="v5-shell">
    <a href="#conteudo-principal" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:rounded-control focus:bg-surface focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-text focus:shadow-overlay">Pular para o conteúdo</a>
  </div>
