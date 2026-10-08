<?php
/**
 * Layout do painel (#2835): rodapé, notificações e scripts do fim da página.
 *
 * As mensagens de flash (success/error da sessão) viram toasts do componente
 * da #2834, no lugar do sweetalert da v4. Algumas mensagens antigas trazem
 * <br> ou <b>; elas passam pelo HTMLPurifier (html_purificado()) em vez de
 * serem impressas cruas.
 *
 * @var array $configuration
 * @var array $layout
 */
?>
  <div class="v5-shell">
    <footer class="px-4 py-6 text-center text-xs text-muted lg:px-6">
      <a href="https://github.com/RamonSilva20/mapos" target="_blank" rel="noopener" class="rounded-sm hover:text-text focus-visible:outline-2 focus-visible:outline-ring">
        <?= e(date('Y')) ?> &copy; Ramon Silva - Map-OS - Versão: <?= e($this->config->item('app_version')) ?>
      </a>
    </footer>
  </div>
</div>

<div class="v5-shell">
  <div data-toast-region class="pointer-events-none fixed right-4 bottom-4 z-50 flex flex-col items-end gap-2">
    <?php foreach ($layout['flash'] as $mensagem) { ?>
      <?= component('toast', ['variant' => $mensagem['variant'], 'title' => $mensagem['title'], 'duration' => $mensagem['duration'], 'message' => html_purificado($mensagem['message'])]) ?>
    <?php } ?>
  </div>
</div>

<?php foreach ($layout['assets']['js_rodape'] as $arquivo) { ?>
<script src="<?= e(layoutUrlAsset($arquivo, base_url())) ?>"></script>
<?php } ?>
</body>

</html>
