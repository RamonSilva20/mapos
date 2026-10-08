<?php
/**
 * Layout do painel: sidebar (DESIGN.md `sidebar`, #2917).
 *
 * Desktop (lg+): fixa à esquerda, 252px, recolhível para um trilho de ícones
 * de 76px; o estado fica no navegador (ver assets/js/tema.js e
 * assets/js/modules/layout/shell.js). Celular e tablet: gaveta que abre pelo
 * botão de menu da topbar.
 *
 * Os itens vêm de layoutMenuVisivel(), separados em seções por
 * layoutMenuGrupos(): só aparece o que o mapa de permissões libera para o
 * usuário. Grupos (Relatórios, Configurações) são <details>, que abrem e
 * fecham sem JavaScript.
 *
 * Item ativo: barra laranja de 3px na borda, fundo primary-tint e texto ink
 * 600 — o laranja fino nunca marca o estado sozinho (DESIGN.md). Na sidebar
 * recolhida, o grupo do subitem ativo (data-atual) leva as mesmas pistas.
 * O selo da versão some na gaveta do celular, onde não cabe com o nome.
 *
 * @var array $layout
 * @var array $configuration
 */
$ativo = 'aria-[current=page]:bg-primary-tint aria-[current=page]:font-semibold aria-[current=page]:text-text'
    . ' aria-[current=page]:before:absolute aria-[current=page]:before:inset-y-2 aria-[current=page]:before:-left-3 aria-[current=page]:before:w-[3px] aria-[current=page]:before:rounded-r-[3px] aria-[current=page]:before:bg-primary';
$foco = 'focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring/50';
$classeLink = 'relative flex h-10 items-center gap-3 rounded-md px-2.5 text-[0.9375rem] font-medium text-muted transition-colors hover:bg-surface-subtle hover:text-text ' . $foco . ' ' . $ativo;
$classeSublink = 'relative flex min-h-9 items-center rounded-md py-1.5 pr-2.5 pl-10.5 text-sm text-muted transition-colors hover:bg-surface-subtle hover:text-text ' . $foco . ' ' . $ativo;
?>
<div class="v5-shell">
  <div class="v5-gaveta-fundo fixed inset-0 z-40 bg-backdrop lg:hidden" data-gaveta-fechar aria-hidden="true"></div>

  <aside id="v5-sidebar" class="v5-sidebar fixed inset-y-0 left-0 z-50 flex flex-col border-r border-border bg-surface" data-sidebar aria-label="Menu principal">
    <div class="v5-sidebar-topo flex h-topbar shrink-0 items-center gap-3 border-b border-border px-4">
      <a href="<?= e(base_url()) ?>" class="flex min-w-0 items-center gap-2.5 rounded-md <?= e($foco) ?>" title="Início">
        <span class="inline-flex size-[34px] shrink-0 items-center justify-center rounded-md bg-primary text-on-primary"><?= icon('wrench', ['class' => 'size-5']) ?></span>
        <span class="v5-rotulo flex min-w-0 items-center gap-2">
          <span class="truncate font-display text-[1.3125rem] leading-none font-bold tracking-[-0.2px] text-text"><?= e($layout['nome_sistema']) ?></span>
          <?php if ($layout['versao'] !== '') { ?>
            <span class="shrink-0 rounded-xs bg-surface-subtle px-1.5 text-micro-cap text-muted max-lg:hidden">v<?= e($layout['versao']) ?></span>
          <?php } ?>
        </span>
      </a>
      <?= component('button', ['label' => 'Fechar menu', 'icon' => 'x', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'ml-auto lg:hidden', 'attrs' => ['data-gaveta-fechar' => true]]) ?>
    </div>

    <form action="<?= e(site_url('mapos/pesquisar')) ?>" method="get" role="search" class="border-b border-border p-3 md:hidden">
      <?= component('input', ['name' => 'termo', 'id' => 'v5-pesquisa-gaveta', 'label' => 'Pesquisar', 'hide_label' => true, 'type' => 'search', 'placeholder' => 'Pesquisar...']) ?>
    </form>

    <nav class="flex-1 overflow-y-auto overscroll-contain px-3 py-4 [scrollbar-color:var(--color-input)_transparent] [scrollbar-width:thin]" aria-label="Navegação">
      <?php foreach ($layout['grupos'] as $grupo) { ?>
        <div class="v5-grupo mt-4.5 first:mt-0">
          <?php if ($grupo['label'] !== '') { ?>
            <p class="v5-rotulo px-2.5 pb-1.5 text-[0.6875rem] leading-[1.8] font-semibold tracking-[0.4px] text-muted uppercase"><?= e($grupo['label']) ?></p>
          <?php } ?>
          <ul class="flex flex-col gap-0.5">
            <?php foreach ($grupo['itens'] as $item) { ?>
              <?php if (isset($item['itens'])) { ?>
                <li>
                  <details class="group/grupo" <?= $item['atual'] ? 'open' : '' ?>>
                    <summary class="<?= e($classeLink) ?> cursor-pointer list-none [&::-webkit-details-marker]:hidden" title="<?= e($item['label']) ?>"<?= $item['atual'] ? ' data-atual' : '' ?>>
                      <?= icon($item['icon'], ['class' => 'size-5']) ?>
                      <span class="v5-rotulo flex-1 truncate"><?= e($item['label']) ?></span>
                      <?= icon('chevron-down', ['class' => 'v5-rotulo size-4 transition-transform group-open/grupo:rotate-180']) ?>
                    </summary>
                    <div class="v5-subitens">
                      <ul class="mt-0.5 flex flex-col gap-0.5">
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
                    <?= icon($item['icon'], ['class' => 'size-5']) ?>
                    <span class="v5-rotulo truncate"><?= e($item['label']) ?></span>
                  </a>
                </li>
              <?php } ?>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>
    </nav>

    <div class="shrink-0 border-t border-border p-3">
      <div class="v5-sidebar-usuario flex items-center gap-2.5 rounded-md p-1">
        <a href="<?= e(site_url('mapos/minhaConta')) ?>" class="shrink-0 rounded-full <?= e($foco) ?>" title="Meu perfil">
          <?php if ($layout['tem_foto']) { ?>
            <img src="<?= e($layout['avatar']) ?>" alt="" class="size-9 rounded-full object-cover">
          <?php } else { ?>
            <span class="inline-flex size-9 items-center justify-center rounded-full bg-accent-violet-mid text-sm font-semibold text-on-dark" aria-hidden="true"><?= e($layout['iniciais']) ?></span>
          <?php } ?>
          <span class="sr-only">Meu perfil</span>
        </a>
        <span class="v5-rotulo min-w-0 flex-1 leading-tight">
          <span class="block truncate text-sm font-semibold text-text"><?= e($layout['usuario']) ?></span>
          <a href="<?= e(site_url('mapos/minhaConta')) ?>" class="text-caption text-muted hover:text-text hover:underline">Meu perfil</a>
        </span>
        <a href="<?= e(site_url('login/sair')) ?>" class="v5-rotulo inline-flex size-9 shrink-0 items-center justify-center rounded-md text-muted hover:bg-surface-subtle hover:text-text <?= e($foco) ?>" title="Sair">
          <?= icon('log-out', ['class' => 'size-5']) ?>
          <span class="sr-only">Sair</span>
        </a>
      </div>
    </div>
  </aside>
</div>
