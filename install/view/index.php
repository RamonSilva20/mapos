<?php
defined('INSTALADOR') || exit;
require_once __DIR__ . '/icones.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= htmlspecialchars($settings['title'], ENT_QUOTES) ?>">
  <link rel="shortcut icon" href="<?= htmlspecialchars($dashboard_url, ENT_QUOTES) ?>install/assets/images/favicon.ico" type="image/x-icon" />
  <title><?= htmlspecialchars($settings['title'], ENT_QUOTES) ?></title>
  <link rel="stylesheet" href="<?= htmlspecialchars($dashboard_url, ENT_QUOTES) ?>install/assets/css/instalador.css" />
</head>

<body class="min-h-screen px-4 py-8">
  <main class="mx-auto flex max-w-3xl flex-col gap-4">
    <header class="flex flex-col items-center gap-2 text-center">
      <img class="h-16 w-auto" src="<?= htmlspecialchars($dashboard_url, ENT_QUOTES) ?>assets/img/logo.png" alt="Map-OS" />
      <h1 class="text-xl font-medium text-text"><?= htmlspecialchars($settings['title'], ENT_QUOTES) ?></h1>
    </header>

    <div class="overflow-hidden rounded-xl border border-border bg-surface">
        <ol class="grid grid-cols-3 border-b border-border text-center text-sm" aria-label="Etapas da instalação">
          <li id="etapa-pre-instalacao" data-etapa class="flex items-center justify-center gap-2 border-b-2 border-transparent p-3 text-muted data-[atual]:border-primary data-[atual]:bg-primary-tint data-[atual]:font-semibold data-[atual]:text-text">
            <?= instalador_icone('circulo', 'size-4 shrink-0') ?> <span class="max-sm:text-xs">Pré-instalação</span>
          </li>
          <li id="etapa-configuracao" data-etapa class="flex items-center justify-center gap-2 border-b-2 border-transparent p-3 text-muted data-[atual]:border-primary data-[atual]:bg-primary-tint data-[atual]:font-semibold data-[atual]:text-text">
            <?= instalador_icone('circulo', 'size-4 shrink-0') ?> <span class="max-sm:text-xs">Configuração</span>
          </li>
          <li id="etapa-finalizacao" data-etapa class="flex items-center justify-center gap-2 border-b-2 border-transparent p-3 text-muted data-[atual]:border-primary data-[atual]:bg-primary-tint data-[atual]:font-semibold data-[atual]:text-text">
            <?= instalador_icone('circulo', 'size-4 shrink-0') ?> <span class="max-sm:text-xs">Finalização</span>
          </li>
        </ol>

      <div id="avisos" role="alert" class="empty:hidden"></div>

      <section id="painel-pre-instalacao" data-painel>
        <?php include __DIR__ . '/pre-installation.php'; ?>
      </section>
      <section id="painel-configuracao" data-painel hidden>
        <?php include __DIR__ . '/configuration.php'; ?>
      </section>
      <section id="painel-finalizacao" data-painel hidden>
        <?php include __DIR__ . '/finished.php'; ?>
      </section>
    </div>
  </main>

  <script src="<?= htmlspecialchars($dashboard_url, ENT_QUOTES) ?>install/assets/js/main.js"></script>
</body>

</html>
