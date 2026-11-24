<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e($titulo) ?></title>
    <link rel="icon" href="<?= e(assetUrl('assets/img/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(assetUrl('assets/dist/app.css')) ?>">
</head>
<body class="api-docs">
    <div id="api-docs" data-url="<?= e($especificacao) ?>" <?= js_module('api/docs') ?>>
        <p class="p-6 text-muted">Carregando a documentação da API…</p>
    </div>
    <script src="<?= e(assetUrl('assets/vendor/scalar/standalone.js')) ?>"></script>
    <script type="module" src="<?= e(assetUrl('assets/js/app.js')) ?>"></script>
</body>
</html>
