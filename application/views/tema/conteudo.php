<?php
/**
 * Layout do painel (#2835): topbar, breadcrumb e o conteúdo da tela.
 *
 * O conteúdo continua dentro de #content > .container-flu > .row-fluid >
 * .span12, como na v4, porque o CSS e o JavaScript das telas legadas contam
 * com essa estrutura. O CSS da moldura (assets/dist/layout.css) só vale dentro
 * de .v5-shell, e #content fica fora dele.
 *
 * @var array $configuration
 * @var array $layout
 * @var string|null $view
 */
?>
<div class="v5-main">
  <div class="v5-shell">
    <header class="sticky top-0 z-30 flex h-topbar items-center gap-2 border-b border-border bg-surface px-3 sm:gap-3 lg:px-6">
      <?= component('button', [
          'label' => 'Menu',
          'icon' => 'bx-menu',
          'icon_only' => true,
          'variant' => 'ghost',
          'attrs' => ['data-sidebar-alternar' => true, 'aria-controls' => 'v5-sidebar', 'aria-expanded' => 'true'],
      ]) ?>

      <form action="<?= e(site_url('mapos/pesquisar')) ?>" method="get" role="search" class="hidden w-full max-w-md md:block">
        <?= component('input', ['name' => 'termo', 'id' => 'v5-pesquisa', 'label' => 'Pesquisar', 'hide_label' => true, 'type' => 'search', 'placeholder' => 'Pesquisar clientes, produtos, serviços, OS...']) ?>
      </form>

      <div class="ml-auto flex items-center gap-2 sm:gap-3">
        <div class="inline-flex rounded-control border border-border bg-surface-2 p-0.5" role="group" aria-label="Modo de cor">
          <?php foreach (['claro' => ['Modo claro', 'bx-sun'], 'escuro' => ['Modo escuro', 'bx-moon'], 'sistema' => ['Seguir o sistema', 'bx-desktop']] as $modo => [$rotulo, $icone]) { ?>
            <?= component('button', [
                'label' => $rotulo,
                'icon' => $icone,
                'icon_only' => true,
                'variant' => 'ghost',
                'size' => 'sm',
                'class' => 'aria-pressed:bg-surface aria-pressed:text-accent-700 aria-pressed:shadow-card dark:aria-pressed:text-accent',
                'attrs' => ['data-tema-escolher' => $modo, 'aria-pressed' => 'false'],
            ]) ?>
          <?php } ?>
        </div>

        <details class="relative" data-menu-suspenso>
          <summary class="flex cursor-pointer list-none items-center gap-3 rounded-control p-1 hover:bg-surface-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring [&::-webkit-details-marker]:hidden" aria-label="Menu do usuário">
            <span class="hidden text-right leading-tight sm:block">
              <span class="block text-xs text-muted"><?= e($layout['saudacao']) ?>,</span>
              <span class="block max-w-40 truncate text-sm font-medium text-text"><?= e($layout['usuario']) ?></span>
            </span>
            <img src="<?= e($layout['avatar']) ?>" alt="" class="size-9 rounded-pill border border-border object-cover">
          </summary>
          <div class="absolute right-0 z-40 mt-2 w-56 rounded-card border border-border bg-surface p-1.5 shadow-overlay">
            <p class="truncate px-3 py-2 text-sm font-medium text-text sm:hidden"><?= e($layout['usuario']) ?></p>
            <a href="<?= e(site_url('mine')) ?>" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-control px-3 py-2 text-sm text-text hover:bg-surface-2 focus-visible:outline-2 focus-visible:outline-ring"><i class="bx bx-user-circle text-lg text-muted" aria-hidden="true"></i>Área do Cliente</a>
            <a href="<?= e(site_url('mapos/minhaConta')) ?>" class="flex items-center gap-2 rounded-control px-3 py-2 text-sm text-text hover:bg-surface-2 focus-visible:outline-2 focus-visible:outline-ring"><i class="bx bx-id-card text-lg text-muted" aria-hidden="true"></i>Meu Perfil</a>
            <div class="my-1 border-t border-border" role="separator"></div>
            <a href="<?= e(site_url('login/sair')) ?>" class="flex items-center gap-2 rounded-control px-3 py-2 text-sm text-text hover:bg-surface-2 focus-visible:outline-2 focus-visible:outline-ring"><i class="bx bx-log-out-circle text-lg text-muted" aria-hidden="true"></i>Sair do Sistema</a>
          </div>
        </details>
      </div>
    </header>

    <div class="px-4 pt-4 lg:px-6">
      <?= component('breadcrumb', ['items' => $layout['breadcrumb']]) ?>
    </div>
  </div>

  <main id="conteudo-principal" tabindex="-1">
    <div id="content">
      <div class="container-flu">
        <div class="row-fluid">
          <div class="span12">
            <?php if (isset($view)) {
                echo $this->load->view($view, null, true);
            } ?>
          </div>
        </div>
      </div>
    </div>
  </main>
