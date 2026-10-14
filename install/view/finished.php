<?php
defined('INSTALADOR') || exit;
/**
 * Finalização: mostrada depois que do_install.php responde sucesso. Com o
 * sistema já instalado o instalador nem chega aqui (index.php responde 403).
 */
?>
<div class="flex flex-col items-center gap-4 p-6 text-center sm:p-10">
  <span class="text-success"><?= instalador_icone('circulo-ok', 'size-12') ?></span>
  <p>Parabéns! Você instalou o <strong><?= htmlspecialchars($settings['title'], ENT_QUOTES) ?></strong> com sucesso!</p>
  <p data-aviso-final hidden class="rounded-md border border-border bg-warning-soft p-3 text-left text-warning-ink whitespace-pre-line"></p>
  <p class="text-danger-ink">Apague a pasta <code>install</code> do servidor.</p>

  <a href="<?= htmlspecialchars($dashboard_url, ENT_QUOTES) ?>" class="inline-flex items-center gap-3 rounded-md bg-primary px-6 py-3 font-bold text-on-primary hover:bg-primary-hover">
    <?= instalador_icone('monitor', 'size-6') ?> Ir para a página de login
  </a>
</div>
