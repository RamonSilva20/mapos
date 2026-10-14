<?php
/**
 * Página de erro da v5 (#2849), usada pelos templates 404, geral, banco e 500.
 *
 * É uma página avulsa e não depende de helpers nem de banco: o erro pode ter
 * vindo de qualquer ponto do boot. O CSS e o tema.js vêm de assets/, pelo
 * caminho relativo à raiz do site (derivado do SCRIPT_NAME, sem base_url()).
 * Segue o modo de cor do sistema (data-tema-modo="sistema"). Detalhes técnicos
 * só aparecem em desenvolvimento.
 *
 * Entradas (definidas pelo template que inclui este arquivo):
 *
 * @var string      $codigo    "404", "500"...
 * @var string      $titulo    Título curto
 * @var string      $texto     Explicação em uma ou duas frases
 * @var string|null $detalhes  Mensagem técnica (já em texto puro); só em development
 */
$raiz = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'))), '/') . '/';
$desenvolvimento = defined('ENVIRONMENT') && ENVIRONMENT === 'development';
$detalhes = $desenvolvimento ? ($detalhes ?? null) : null;
?><!DOCTYPE html>
<html lang="pt-br" data-tema-modo="sistema">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= htmlspecialchars($titulo . ' — Map-OS', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($raiz . 'assets/img/favicon.png', ENT_QUOTES, 'UTF-8') ?>">
    <script src="<?= htmlspecialchars($raiz . 'assets/js/tema.js', ENT_QUOTES, 'UTF-8') ?>"></script>
    <link rel="stylesheet" href="<?= htmlspecialchars($raiz . 'assets/dist/app.css', ENT_QUOTES, 'UTF-8') ?>">
</head>

<body class="min-h-screen bg-bg text-text">
    <main class="mx-auto flex min-h-screen w-full max-w-xl flex-col items-start justify-center gap-4 px-6 py-12">
        <p class="font-display text-display-large font-medium text-primary-strong" aria-hidden="true"><?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?></p>
        <h1 class="font-display text-heading-xl text-text"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-body-lg text-muted"><?= htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') ?></p>

        <?php if ($detalhes !== null && $detalhes !== '') { ?>
            <pre class="max-h-72 w-full overflow-auto rounded-md border border-border bg-surface-subtle p-3 text-caption whitespace-pre-wrap text-text"><?= htmlspecialchars($detalhes, ENT_QUOTES, 'UTF-8') ?></pre>
        <?php } ?>

        <a href="<?= htmlspecialchars($raiz, ENT_QUOTES, 'UTF-8') ?>" class="mt-2 inline-flex h-10 items-center justify-center gap-2 rounded-md border border-transparent bg-primary px-4 text-button-cap whitespace-nowrap text-on-primary uppercase hover:bg-primary-hover focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring max-sm:h-11">Voltar ao início</a>
    </main>
</body>

</html>
