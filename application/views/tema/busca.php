<?php
/**
 * Paleta de busca global (#2850): abre com Ctrl/⌘ K, com o clique no campo de
 * busca da topbar ou no botão de busca do celular (shell.js). O módulo
 * layout/busca consulta mapos/buscaRapida enquanto o usuário digita e mostra os
 * resultados por grupo; ↑ ↓ escolhem, Enter abre e Esc fecha. Sem escolha, o
 * Enter vai para a página de resultados completa (mapos/pesquisar).
 *
 * Os atalhos de teclado do painel (F1 a F4) vêm do JSON abaixo, lido pelo
 * shell.js, só com as telas que o usuário pode abrir.
 *
 * @var array $layout
 */
?>
<div class="v5-shell">
  <?= page_data('layout-atalhos', $layout['atalhos']) ?>
  <dialog id="v5-busca" <?= js_module('layout/busca') ?> data-url="<?= e(site_url('mapos/buscaRapida')) ?>" aria-label="Busca global" class="m-auto mt-[12vh] w-[calc(100%-2rem)] max-w-xl rounded-xl border border-border bg-surface p-0 text-text shadow-overlay backdrop:bg-backdrop backdrop:backdrop-blur-[1px]">
    <form action="<?= e(site_url('mapos/pesquisar')) ?>" method="get" role="search" class="flex flex-col" data-busca-form>
      <div class="relative border-b border-border">
        <?= icon('search', ['class' => 'pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-muted']) ?>
        <input type="text" enterkeyhint="search" name="termo" autocomplete="off" autofocus maxlength="100" data-busca-campo role="combobox" aria-expanded="false" aria-controls="v5-busca-lista" aria-autocomplete="list" placeholder="Buscar OS, cliente, produto ou serviço…" aria-label="Buscar" class="h-14 w-full rounded-t-xl bg-transparent pr-16 pl-12 text-base text-text placeholder:text-muted focus-visible:outline-none">
        <kbd class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 rounded-xs border border-border px-1.5 font-sans text-xs leading-5 font-medium text-muted" aria-hidden="true">Esc</kbd>
      </div>
      <div id="v5-busca-lista" tabindex="-1" role="listbox" aria-label="Resultados" data-busca-resultados class="max-h-[50vh] overflow-y-auto"></div>
      <p data-busca-estado class="px-4 py-6 text-center text-caption text-muted" aria-live="polite">Digite pelo menos 2 letras para buscar.</p>
      <div class="flex items-center justify-between gap-3 border-t border-border bg-surface-subtle px-4 py-2 text-xs text-muted">
        <span class="max-sm:hidden"><kbd class="font-sans font-medium">↑</kbd> <kbd class="font-sans font-medium">↓</kbd> escolhem · <kbd class="font-sans font-medium">Enter</kbd> abre</span>
        <a href="<?= e(site_url('mapos/pesquisar')) ?>" data-busca-todos class="font-medium text-primary-strong hover:underline focus-visible:outline-3 focus-visible:outline-ring">Ver todos os resultados</a>
      </div>
    </form>
  </dialog>
</div>
