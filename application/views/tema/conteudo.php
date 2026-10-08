<?php
/**
 * Layout do painel: topbar (DESIGN.md `topbar`, #2917), breadcrumb e o
 * conteúdo da tela.
 *
 * Topbar: menu, busca global (Ctrl/⌘ K foca o campo, ver shell.js), modo de
 * cor, a ação principal da tela quando o controller define
 * $this->data['topbar_acao'] (o único button-primary) e o menu do usuário.
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
          'icon' => 'menu',
          'icon_only' => true,
          'variant' => 'ghost',
          'attrs' => ['data-sidebar-alternar' => true, 'aria-controls' => 'v5-sidebar', 'aria-expanded' => 'true'],
      ]) ?>

      <form action="<?= e(site_url('mapos/pesquisar')) ?>" method="get" role="search" class="relative hidden w-full max-w-md md:block">
        <?= component('input', ['name' => 'termo', 'id' => 'v5-pesquisa', 'label' => 'Pesquisar', 'hide_label' => true, 'type' => 'search', 'placeholder' => 'Buscar OS, cliente, produto…', 'class' => 'pr-16 pl-10', 'attrs' => ['aria-keyshortcuts' => 'Control+K Meta+K']]) ?>
        <?= icon('search', ['class' => 'pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-muted']) ?>
        <kbd class="pointer-events-none absolute top-1/2 right-2.5 -translate-y-1/2 rounded-xs border border-border px-1.5 font-sans text-xs leading-5 font-medium text-muted" aria-hidden="true">Ctrl K</kbd>
      </form>

      <div class="ml-auto flex items-center gap-2 sm:gap-3">
        <div class="inline-flex rounded-md border border-border bg-surface-subtle p-0.5" role="group" aria-label="Modo de cor">
          <?php foreach (['claro' => ['Modo claro', 'sun'], 'escuro' => ['Modo escuro', 'moon'], 'sistema' => ['Seguir o sistema', 'monitor']] as $modo => [$rotulo, $icone]) { ?>
            <?= component('button', [
                'label' => $rotulo,
                'icon' => $icone,
                'icon_only' => true,
                'variant' => 'ghost',
                'size' => 'sm',
                'class' => 'aria-pressed:bg-surface aria-pressed:font-semibold aria-pressed:text-text aria-pressed:shadow-elev-1',
                'attrs' => ['data-tema-escolher' => $modo, 'aria-pressed' => 'false'],
            ]) ?>
          <?php } ?>
        </div>

        <?php if ($layout['acao'] !== null) { ?><?= componenteConteudo($layout['acao']) ?><?php } ?>

        <details class="relative" data-menu-suspenso>
          <summary class="flex cursor-pointer list-none items-center gap-3 rounded-md p-1 hover:bg-surface-subtle focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring/50 [&::-webkit-details-marker]:hidden" aria-label="Menu do usuário">
            <span class="hidden text-right leading-tight sm:block">
              <span class="block text-xs text-muted"><?= e($layout['saudacao']) ?>,</span>
              <span class="block max-w-40 truncate text-sm font-medium text-text"><?= e($layout['usuario']) ?></span>
            </span>
            <?php if ($layout['tem_foto']) { ?>
              <img src="<?= e($layout['avatar']) ?>" alt="" class="size-9 rounded-full border border-border object-cover">
            <?php } else { ?>
              <span class="inline-flex size-9 items-center justify-center rounded-full bg-accent-violet-mid text-sm font-semibold text-on-dark" aria-hidden="true"><?= e($layout['iniciais']) ?></span>
            <?php } ?>
          </summary>
          <div class="absolute right-0 z-40 mt-2 w-56 rounded-xl border border-border bg-surface p-1.5 shadow-overlay">
            <p class="truncate px-3 py-2 text-sm font-medium text-text sm:hidden"><?= e($layout['usuario']) ?></p>
            <a href="<?= e(site_url('mine')) ?>" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-text hover:bg-surface-subtle focus-visible:outline-3 focus-visible:outline-ring/50"><?= icon('circle-user', ['class' => 'size-[18px] text-muted']) ?>Área do Cliente</a>
            <a href="<?= e(site_url('mapos/minhaConta')) ?>" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-text hover:bg-surface-subtle focus-visible:outline-3 focus-visible:outline-ring/50"><?= icon('id-card', ['class' => 'size-[18px] text-muted']) ?>Meu Perfil</a>
            <div class="my-1 border-t border-border" role="separator"></div>
            <a href="<?= e(site_url('login/sair')) ?>" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-text hover:bg-surface-subtle focus-visible:outline-3 focus-visible:outline-ring/50"><?= icon('log-out', ['class' => 'size-[18px] text-muted']) ?>Sair do Sistema</a>
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
