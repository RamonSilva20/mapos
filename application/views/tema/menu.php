<?php
/**
 * Layout do painel (#2835): sidebar.
 *
 * Desktop (lg+): fixa à esquerda, recolhível para só os ícones; o estado fica
 * no navegador (ver assets/js/tema.js e assets/js/modules/layout/shell.js).
 * Celular e tablet: gaveta que abre pelo botão de menu da topbar.
 *
 * Os itens vêm de layoutMenuVisivel(): só aparece o que o mapa de permissões
 * libera para o usuário. Grupos (Relatórios, Configurações) são <details>,
 * que abrem e fecham sem JavaScript.
 *
 * @var array $layout
 */
$classeLink = 'flex h-10 items-center gap-3 rounded-control px-3 text-sm font-medium text-muted transition-colors hover:bg-surface-2 hover:text-text focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring aria-[current=page]:bg-accent-50 aria-[current=page]:text-accent-700 dark:aria-[current=page]:bg-surface-2 dark:aria-[current=page]:text-accent';
$classeSublink = 'flex min-h-9 items-center rounded-control py-1.5 pr-3 pl-11 text-sm text-muted transition-colors hover:bg-surface-2 hover:text-text focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring aria-[current=page]:font-medium aria-[current=page]:text-accent-700 dark:aria-[current=page]:text-accent';
?>
<div class="v5-shell">
  <div class="v5-gaveta-fundo fixed inset-0 z-40 bg-black/50 lg:hidden" data-gaveta-fechar aria-hidden="true"></div>

  <aside id="v5-sidebar" class="v5-sidebar fixed inset-y-0 left-0 z-50 flex flex-col border-r border-border bg-surface" data-sidebar aria-label="Menu principal">
    <div class="flex h-topbar shrink-0 items-center gap-3 border-b border-border px-4">
      <a href="<?= e(base_url()) ?>" class="flex min-w-0 items-center gap-3 rounded-control focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring" title="Início">
        <img src="<?= e(base_url('assets/img/logo-two.png')) ?>" alt="" class="size-9 shrink-0">
        <span class="v5-rotulo">
          <img src="<?= e(base_url('assets/img/logo-mapos.png')) ?>" alt="<?= e($configuration['app_name'] ?: 'Map-OS') ?>" class="h-7 w-auto dark:hidden">
          <img src="<?= e(base_url('assets/img/logo-mapos-branco.png')) ?>" alt="<?= e($configuration['app_name'] ?: 'Map-OS') ?>" class="hidden h-7 w-auto dark:block">
        </span>
      </a>
      <?= component('button', ['label' => 'Fechar menu', 'icon' => 'bx-x', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'ml-auto lg:hidden', 'attrs' => ['data-gaveta-fechar' => true]]) ?>
    </div>

    <form action="<?= e(site_url('mapos/pesquisar')) ?>" method="get" role="search" class="border-b border-border p-3 md:hidden">
      <?= component('input', ['name' => 'termo', 'id' => 'v5-pesquisa-gaveta', 'label' => 'Pesquisar', 'hide_label' => true, 'type' => 'search', 'placeholder' => 'Pesquisar...']) ?>
    </form>

    <nav class="flex-1 overflow-y-auto overscroll-contain px-3 py-4" aria-label="Navegação">
      <ul class="flex flex-col gap-1">
        <?php foreach ($layout['menu'] as $item) { ?>
          <?php if (isset($item['itens'])) { ?>
            <li>
              <details class="group/grupo" <?= $item['atual'] ? 'open' : '' ?>>
                <summary class="<?= e($classeLink) ?> cursor-pointer list-none [&::-webkit-details-marker]:hidden" title="<?= e($item['label']) ?>">
                  <i class="<?= e('bx ' . $item['icon'] . ' shrink-0 text-xl') ?>" aria-hidden="true"></i>
                  <span class="v5-rotulo flex-1 truncate"><?= e($item['label']) ?></span>
                  <i class="v5-rotulo bx bx-chevron-down text-lg transition-transform group-open/grupo:rotate-180" aria-hidden="true"></i>
                </summary>
                <div class="v5-subitens">
                  <ul class="mt-1 flex flex-col gap-0.5">
                    <?php foreach ($item['itens'] as $filho) { ?>
                      <li>
                        <a href="<?= e(site_url($filho['url'])) ?>" class="<?= e($classeSublink) ?>"<?= $filho['atual'] ? ' aria-current="page"' : '' ?>><?= e($filho['label']) ?></a>
                      </li>
                    <?php } ?>
                  </ul>
                </div>
              </details>
            </li>
          <?php } else { ?>
            <li>
              <a href="<?= e(site_url($item['url'])) ?>" class="<?= e($classeLink) ?>" title="<?= e($item['label']) ?>"<?= $item['atual'] ? ' aria-current="page"' : '' ?>>
                <i class="<?= e('bx ' . $item['icon'] . ' shrink-0 text-xl') ?>" aria-hidden="true"></i>
                <span class="v5-rotulo truncate"><?= e($item['label']) ?></span>
              </a>
            </li>
          <?php } ?>
        <?php } ?>
      </ul>
    </nav>

    <div class="shrink-0 border-t border-border px-3 py-3">
      <a href="<?= e(site_url('login/sair')) ?>" class="<?= e($classeLink) ?>" title="Sair">
        <i class="bx bx-log-out-circle shrink-0 text-xl" aria-hidden="true"></i>
        <span class="v5-rotulo truncate">Sair</span>
      </a>
    </div>
  </aside>
</div>
