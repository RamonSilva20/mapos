<?php
/**
 * Finalização: sucesso da instalação, ou o aviso de que o Map-OS já está
 * instalado. Com installed = 1 esta é a única tela aberta.
 */
?>
<div class="flex flex-col items-center gap-4 p-6 text-center sm:p-10">
  <?php if (! $installed) { ?>
    <span class="text-success"><?= instalador_icone('circulo-ok', 'size-12') ?></span>
    <p>Parabéns! Você instalou o <strong><?= htmlspecialchars($settings['title'], ENT_QUOTES) ?></strong> com sucesso!</p>
    <p class="text-danger-ink">Não esqueça de apagar a pasta <code>install</code> do servidor.</p>
  <?php } else { ?>
    <span class="text-danger"><?= instalador_icone('circulo-erro', 'size-12') ?></span>
    <p class="text-danger-ink">Parece que este aplicativo já está instalado! Você não pode reinstalá-lo novamente.</p>
  <?php } ?>

  <a href="<?= htmlspecialchars($dashboard_url, ENT_QUOTES) ?>" class="inline-flex items-center gap-3 rounded-md bg-primary px-6 py-3 font-bold text-on-primary hover:bg-primary-hover">
    <?= instalador_icone('monitor', 'size-6') ?> Ir para a página de login
  </a>
</div>
