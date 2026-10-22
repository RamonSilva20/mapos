<?php
/**
 * Moldura da área do cliente (#2848), para quem está logado.
 *
 * DESIGN.md:
 * - Top Nav escura (canvas-dark) com a marca, os links em on-dark-muted e a
 *   ação principal da tela em button-primary; os links aparecem a partir de
 *   1024px (lg), com os rótulos curtos, para caberem numa linha só ao lado da
 *   ação e do nome do cliente (este só a partir de xl).
 * - Abaixo de lg, a navegação vai para a tabbar-mobile: quatro itens (Início,
 *   Minhas OS, Compras, Conta) com ícone de 24px sobre o rótulo de 12px; o
 *   item ativo fica em ink 600 com o traço laranja de 28×3px no topo. As
 *   cobranças ficam no início e na conta.
 * - O conteúdo usa os tokens do painel e segue o modo de cor das
 *   Configurações (temaAtributosHtml() e assets/js/tema.js).
 *
 * As mensagens de flash viram toasts, como no painel.
 *
 * @var string      $view         View da tela (dentro de conecte/)
 * @var list<array> $menu         conecteMenu()
 * @var array|null  $acao         Props do button-primary da tela
 * @var list<array> $flash
 * @var array       $configuration
 * @var string      $cliente_nome
 */
// Mesmo nome das telas de entrada (views/entrada/inicio.php).
$nomeSistema = (string) ($this->config->item('app_name') ?: 'Map-OS');
$foco = 'focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring';
$atual = array_values(array_filter($menu, static fn ($item) => $item['atual']))[0]['label'] ?? 'Área do cliente';
?><!doctype html>
<html lang="pt-br"<?= temaAtributosHtml($configuration) ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token-name" content="<?= e($this->security->get_csrf_token_name()) ?>">
    <meta name="csrf-cookie-name" content="<?= e(config_item('csrf_cookie_name')) ?>">
    <title><?= e($atual . ' — Área do cliente — ' . $nomeSistema) ?></title>
    <link rel="icon" type="image/png" href="<?= e(base_url('assets/img/favicon.png')) ?>">
    <script src="<?= e(base_url('assets/js/tema.js')) ?>"></script>
    <link rel="stylesheet" href="<?= e(base_url('assets/dist/app.css')) ?>">
    <script type="module" src="<?= e(base_url('assets/js/app.js')) ?>"></script>
</head>
<body class="flex min-h-screen flex-col bg-bg font-sans text-text antialiased">
    <a href="#conteudo-principal" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:rounded-control focus:bg-surface focus:px-4 focus:py-2 focus:text-label-md focus:text-text focus:shadow-overlay">Pular para o conteúdo</a>

    <header class="bg-canvas-dark text-on-dark">
        <div class="mx-auto flex h-16 w-full max-w-6xl items-center gap-4 px-4 lg:px-8">
            <a href="<?= e(site_url('mine/painel')) ?>" class="flex min-w-0 items-center gap-2.5 rounded-md <?= e($foco) ?>">
                <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-md bg-primary text-on-primary"><?= icon('wrench', ['class' => 'size-5']) ?></span>
                <span class="min-w-0 leading-tight whitespace-nowrap">
                    <span class="block truncate font-display text-[1.25rem] leading-none font-bold tracking-[-0.2px]"><?= e($nomeSistema) ?></span>
                    <span class="block text-caption text-on-dark-muted">Área do cliente</span>
                </span>
            </a>

            <nav class="ml-2 hidden lg:block" aria-label="Área do cliente">
                <ul class="flex items-center gap-1">
                    <?php foreach ($menu as $item) { ?>
                        <li>
                            <a href="<?= e(site_url($item['url'])) ?>"
                               class="relative inline-flex h-10 items-center rounded-md px-3 whitespace-nowrap text-[0.9375rem] font-medium text-on-dark-muted transition-colors hover:bg-on-dark-faint hover:text-on-dark aria-[current=page]:bg-on-dark-faint aria-[current=page]:font-semibold aria-[current=page]:text-on-dark aria-[current=page]:after:absolute aria-[current=page]:after:inset-x-3 aria-[current=page]:after:-bottom-3 aria-[current=page]:after:h-[3px] aria-[current=page]:after:rounded-t-[3px] aria-[current=page]:after:bg-primary <?= e($foco) ?>"<?= $item['atual'] ? ' aria-current="page"' : '' ?>><?= e($item['curto']) ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>

            <div class="ml-auto flex shrink-0 items-center gap-2">
                <?php if ($acao !== null) { ?>
                    <?= component('button', $acao + ['class' => 'max-sm:hidden']) ?>
                    <?= component('button', $acao + ['icon_only' => true, 'class' => 'sm:hidden']) ?>
                <?php } ?>
                <span class="hidden max-w-48 truncate text-body-md text-on-dark-muted xl:inline" title="<?= e($cliente_nome) ?>"><?= e($cliente_nome) ?></span>
                <?= component('button', ['label' => 'Sair', 'icon' => 'log-out', 'variant' => 'ghost-on-dark', 'href' => site_url('mine/sair'), 'icon_only' => true]) ?>
            </div>
        </div>
    </header>

    <main id="conteudo-principal" tabindex="-1" class="mx-auto w-full max-w-6xl flex-1 px-4 pt-6 pb-28 lg:pb-10 lg:px-8">
        <?php $this->load->view($view); ?>
    </main>

    <footer class="hidden px-4 pb-6 text-center text-caption text-muted lg:block">
        <?= e(date('Y') . ' © ' . $nomeSistema) ?>
    </footer>

    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-surface pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Área do cliente">
        <ul class="grid grid-cols-4">
            <?php foreach ($menu as $item) { ?>
                <?php if ($item['tabbar']) { ?>
                    <li>
                        <a href="<?= e(site_url($item['url'])) ?>"
                           class="relative flex min-h-16 flex-col items-center justify-center gap-1 px-1 text-[0.75rem] leading-none font-medium text-muted hover:text-text aria-[current=page]:font-semibold aria-[current=page]:text-text aria-[current=page]:before:absolute aria-[current=page]:before:top-0 aria-[current=page]:before:left-1/2 aria-[current=page]:before:h-[3px] aria-[current=page]:before:w-7 aria-[current=page]:before:-translate-x-1/2 aria-[current=page]:before:rounded-b-[3px] aria-[current=page]:before:bg-primary focus-visible:outline-3 focus-visible:-outline-offset-3 focus-visible:outline-ring"<?= $item['atual'] ? ' aria-current="page"' : '' ?>>
                            <?= icon($item['icon'], ['class' => 'size-6']) ?>
                            <span><?= e($item['curto']) ?></span>
                        </a>
                    </li>
                <?php } ?>
            <?php } ?>
        </ul>
    </nav>

    <div data-toast-region class="pointer-events-none fixed right-4 bottom-20 z-50 flex flex-col items-end gap-2 lg:bottom-4">
        <?php foreach ($flash as $mensagem) { ?>
            <?= component('toast', ['variant' => $mensagem['variant'], 'title' => $mensagem['title'], 'duration' => $mensagem['duration'], 'message' => html_purificado($mensagem['message'])]) ?>
        <?php } ?>
    </div>
</body>
</html>
