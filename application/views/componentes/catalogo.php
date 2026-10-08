<?php
/**
 * Catálogo da biblioteca de componentes. Só em development (ver
 * controllers/Componentes.php).
 *
 * Tem layout próprio porque o app.css (com o preflight do Tailwind) ainda não
 * pode ser carregado junto do CSS legado; isso entra com o layout novo
 * (#2835).
 */
$secao = static function (string $titulo, string $descricao = ''): HtmlSeguro {
    return new HtmlSeguro(
        '<h2 class="mt-12 mb-1 text-heading-sm text-text">' . e($titulo) . '</h2>'
        . ($descricao !== '' ? '<p class="mb-4 text-caption text-muted">' . e($descricao) . '</p>' : '<div class="mb-4"></div>')
    );
};

$clientes = [
    ['id' => 1, 'nome' => 'Maria Souza', 'documento' => '123.456.789-09', 'status' => 'ativo', 'saldo' => 'R$ 1.250,00'],
    ['id' => 2, 'nome' => '<script>alert("xss")</script>', 'documento' => '987.654.321-00', 'status' => 'pendente', 'saldo' => 'R$ 0,00'],
    ['id' => 3, 'nome' => 'Oficina "Aspas" & Cia', 'documento' => '12.345.678/0001-90', 'status' => 'inativo', 'saldo' => 'R$ 89,90'],
];

$statusCliente = [
    'ativo' => ['label' => 'Ativo', 'variant' => 'success'],
    'pendente' => ['label' => 'Pendente', 'variant' => 'warning'],
    'inativo' => ['label' => 'Inativo', 'variant' => 'neutral'],
];

$statusOs = [
    ['label' => 'Aberta', 'variant' => 'info'],
    ['label' => 'Em andamento', 'variant' => 'progress'],
    ['label' => 'Aguardando peça', 'variant' => 'warning'],
    ['label' => 'Finalizada', 'variant' => 'success'],
    ['label' => 'Cancelada', 'variant' => 'danger'],
    ['label' => 'Orçamento enviado', 'variant' => 'neutral'],
];
?><!doctype html>
<html lang="pt-br"<?= temaAtributosHtml($configuration) ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Componentes — Map-OS</title>
    <link rel="icon" type="image/png" href="<?= e(base_url('assets/img/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('assets/dist/app.css')) ?>">
    <script src="<?= e(base_url('assets/js/tema.js')) ?>"></script>
    <script type="module" src="<?= e(base_url('assets/js/app.js')) ?>"></script>
</head>
<body class="min-h-screen bg-bg font-sans text-text antialiased">
    <header class="sticky top-0 z-40 border-b border-border bg-surface/95 backdrop-blur">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3" <?= js_module('componentes/catalogo') ?>>
            <div>
                <p class="text-base font-semibold">Biblioteca de componentes</p>
                <p class="text-xs text-muted">application/views/components · visível só em development</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-control border border-border bg-surface-subtle p-0.5" role="group" aria-label="Modo de cor">
                    <?php foreach (['claro' => 'Claro', 'escuro' => 'Escuro', 'sistema' => 'Sistema'] as $modo => $rotulo) { ?>
                        <?= component('button', ['label' => $rotulo, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'aria-pressed:bg-surface aria-pressed:shadow-card', 'attrs' => ['data-tema-modo' => $modo, 'aria-pressed' => 'false']]) ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 pb-24">
        <?= componenteConteudo($secao('Breadcrumb')) ?>
        <?= component('breadcrumb', ['items' => [
            ['label' => 'Início', 'url' => site_url('mapos')],
            ['label' => 'Clientes', 'url' => site_url('clientes')],
            ['label' => 'Editar cliente'],
        ]]) ?>

        <?= componenteConteudo($secao('Botões', 'Um primary (laranja, rótulo ink) por tela; outline e ghost para o resto; danger só para ação destrutiva. Três tamanhos, com ícone, só ícone, link e desabilitado.')) ?>
        <div class="flex flex-col gap-3">
            <?php foreach (['sm', 'md', 'lg'] as $tamanho) { ?>
                <div class="flex flex-wrap items-center gap-2">
                    <?php foreach (['primary', 'outline', 'ghost', 'danger', 'link'] as $variante) { ?>
                        <?= component('button', ['label' => ucfirst($variante), 'variant' => $variante, 'size' => $tamanho]) ?>
                    <?php } ?>
                </div>
            <?php } ?>
            <div class="flex flex-wrap items-center gap-2">
                <?= component('button', ['label' => 'Novo cliente', 'icon' => 'plus']) ?>
                <?= component('button', ['label' => 'Editar', 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'outline']) ?>
                <?= component('button', ['label' => 'Excluir', 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'danger']) ?>
                <?= component('button', ['label' => 'Abrir clientes (link)', 'href' => site_url('clientes'), 'variant' => 'outline', 'icon' => 'external-link']) ?>
                <?= component('button', ['label' => 'Desabilitado', 'disabled' => true]) ?>
                <?= component('button', ['label' => 'Link desabilitado', 'href' => site_url('clientes'), 'disabled' => true, 'variant' => 'outline']) ?>
            </div>
            <div class="flex flex-wrap items-center gap-2 rounded-xl bg-canvas-dark p-5">
                <?= component('button', ['label' => 'Entrar', 'icon' => 'log-in', 'class' => 'shadow-elev-3']) ?>
                <?= component('button', ['label' => 'Ver demonstração', 'variant' => 'inverted']) ?>
                <?= component('button', ['label' => 'Área do cliente', 'variant' => 'ghost-on-dark']) ?>
                <span class="text-caption text-on-dark-muted">inverted e ghost-on-dark só sobre fundo escuro (telas de entrada)</span>
            </div>
        </div>

        <?= componenteConteudo($secao('Campos de formulário', 'Label associado, ajuda e erro ligados por aria-describedby, aria-invalid no erro.')) ?>
        <form class="grid gap-4 md:grid-cols-2" action="#" method="post">
            <?= component('input', ['name' => 'nome', 'label' => 'Nome', 'required' => true, 'placeholder' => 'Nome completo']) ?>
            <?= component('input', ['name' => 'email', 'type' => 'email', 'label' => 'E-mail', 'help' => 'Usado para enviar a OS ao cliente.']) ?>
            <?= component('input', ['name' => 'documento', 'label' => 'CPF/CNPJ', 'value' => '111.111.111-11', 'error' => 'O campo CPF/CNPJ não é um CPF ou CNPJ válido.']) ?>
            <?= component('input', ['name' => 'codigo', 'label' => 'Código', 'value' => 'CLI-0001', 'readonly' => true]) ?>
            <?= component('input', ['name' => 'total', 'label' => 'Valor total', 'value' => 'R$ 480,00', 'disabled' => true, 'help' => 'Calculado pelos produtos e serviços.']) ?>
            <?= component('input', ['name' => 'busca', 'type' => 'search', 'label' => 'Buscar', 'placeholder' => 'Nº da OS, cliente ou equipamento']) ?>
            <?= component('select', [
                'name' => 'tipo',
                'label' => 'Tipo de cliente',
                'placeholder' => 'Selecione...',
                'options' => ['pf' => 'Pessoa física', 'pj' => 'Pessoa jurídica'],
                'required' => true,
            ]) ?>
            <?= component('select', [
                'name' => 'tags',
                'label' => 'Etiquetas',
                'multiple' => true,
                'options' => [['value' => 'vip', 'label' => 'VIP'], ['value' => 'atraso', 'label' => 'Em atraso'], ['value' => 'novo', 'label' => 'Novo', 'disabled' => true]],
                'selected' => ['vip'],
                'help' => 'Ctrl + clique para marcar mais de uma.',
            ]) ?>
            <div class="md:col-span-2">
                <?= component('textarea', ['name' => 'observacoes', 'label' => 'Observações', 'value' => 'Texto com <b>tags</b> aparece escapado.', 'help' => 'Até 500 caracteres.']) ?>
            </div>
            <div class="flex flex-wrap items-start gap-x-8 gap-y-4 md:col-span-2">
                <?= component('checkbox', ['name' => 'enviar_email', 'label' => 'Enviar e-mail ao cliente', 'checked' => true, 'help' => 'O cliente recebe o link da OS.']) ?>
                <?= component('checkbox', ['name' => 'termos', 'label' => 'Aceito os termos', 'required' => true, 'error' => 'Marque para continuar.']) ?>
                <?= component('checkbox', ['name' => 'bloqueado', 'label' => 'Desabilitado', 'disabled' => true]) ?>
                <fieldset class="flex flex-col gap-2">
                    <legend class="mb-2 text-label-md">Prioridade</legend>
                    <?= component('radio', ['name' => 'prioridade', 'value' => 'normal', 'label' => 'Normal', 'checked' => true]) ?>
                    <?= component('radio', ['name' => 'prioridade', 'value' => 'urgente', 'label' => 'Urgente']) ?>
                </fieldset>
                <?= component('switch', ['name' => 'aprovado', 'label' => 'Orçamento aprovado', 'checked' => true]) ?>
                <?= component('switch', ['name' => 'garantia', 'label' => 'Em garantia', 'help' => 'Desligado por padrão.']) ?>
            </div>
            <div class="flex gap-2 md:col-span-2">
                <?= component('button', ['label' => 'Salvar', 'type' => 'submit', 'icon' => 'save', 'attrs' => ['data-modal-abrir' => 'modal-salvo']]) ?>
                <?= component('button', ['label' => 'Cancelar', 'variant' => 'outline']) ?>
            </div>
        </form>

        <?= componenteConteudo($secao('Cartão')) ?>
        <div class="grid gap-4 md:grid-cols-2">
            <?= component('card', [
                'id' => 'card-resumo',
                'title' => 'Resumo do cliente',
                'subtitle' => 'Atualizado hoje',
                'actions' => component('button', ['label' => 'Editar', 'size' => 'sm', 'variant' => 'outline', 'icon' => 'pencil']),
                'body' => 'O corpo aceita texto (escapado) ou a saída de outros componentes.',
                'footer' => [
                    component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'size' => 'sm']),
                    component('button', ['label' => 'Confirmar', 'size' => 'sm']),
                ],
            ]) ?>
            <?= component('card', [
                'title' => 'Com componentes no corpo',
                'body' => [
                    component('alert', ['variant' => 'info', 'message' => 'Um alert dentro de um card.']),
                    html_purificado('<p class="mt-3 text-sm">HTML vindo do usuário passa por <strong>html_purificado()</strong>; <em>script</em> é removido.<script>alert(1)</script></p>'),
                ],
            ]) ?>
        </div>

        <?= componenteConteudo($secao('Cartões de resumo (kpi-card)')) ?>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <?= component('kpi-card', ['label' => 'Abertas', 'value' => 18, 'icon' => 'wrench', 'caption' => html_purificado('<span class="font-semibold text-success-ink">+3</span> hoje')]) ?>
            <?= component('kpi-card', ['label' => 'Em andamento', 'value' => 7, 'icon' => 'file-text', 'caption' => 'tempo médio 2,4 dias']) ?>
            <?= component('kpi-card', ['label' => 'Aguardando peça', 'value' => 4, 'icon' => 'archive', 'caption' => html_purificado('<span class="font-semibold text-warning-ink">2 há mais de 5 dias</span>')]) ?>
            <?= component('kpi-card', ['label' => 'Finalizadas no mês', 'value' => 52, 'icon' => 'circle-check', 'caption' => 'R$ 18.430,00 faturados']) ?>
        </div>

        <?= componenteConteudo($secao('Abas (tabs)', 'Aba ativa com texto ink 600 e sublinhado laranja: o laranja fino nunca marca o estado sozinho.')) ?>
        <?= component('tabs', ['label' => 'Seções da OS', 'items' => [
            ['label' => 'Detalhes', 'url' => site_url('componentes'), 'active' => true],
            ['label' => 'Produtos', 'url' => site_url('componentes'), 'count' => 2],
            ['label' => 'Serviços', 'url' => site_url('componentes'), 'count' => 1],
            ['label' => 'Anexos', 'url' => site_url('componentes'), 'icon' => 'folder-open'],
        ]]) ?>

        <?= componenteConteudo($secao('Status (pill-status)', 'Sempre com a palavra; texto -ink sobre -soft, conferido nos dois modos. O laranja nunca indica status.')) ?>
        <div class="flex flex-wrap items-center gap-2">
            <?php foreach ($statusOs as $status) { ?>
                <?= component('pill-status', $status) ?>
            <?php } ?>
            <?= component('pill-status', ['label' => 'Sem ponto', 'variant' => 'info', 'dot' => false]) ?>
        </div>

        <?= componenteConteudo($secao('Tabela (data-table)', 'Células escapadas; render devolve texto ou componentes. O segundo cliente tem um <script> no nome. Abaixo de 640px cada linha vira um bloco.')) ?>
        <?= component('data-table', [
            'caption' => 'Clientes de exemplo',
            'striped' => true,
            'columns' => [
                ['key' => 'id', 'label' => '#'],
                ['key' => 'nome', 'label' => 'Nome'],
                ['key' => 'documento', 'label' => 'Documento'],
                ['label' => 'Status', 'nowrap' => true, 'render' => static fn ($linha) => component('pill-status', $statusCliente[$linha['status']])],
                ['key' => 'saldo', 'label' => 'Saldo', 'align' => 'right'],
                ['label' => 'Ações', 'align' => 'right', 'render' => static fn ($linha) => [
                    component('button', ['label' => 'Editar ' . $linha['nome'], 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('clientes/editar/' . $linha['id'])]),
                    component('button', ['label' => 'Excluir ' . $linha['nome'], 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'attrs' => ['data-modal-abrir' => 'modal-excluir']]),
                ]],
            ],
            'rows' => $clientes,
        ]) ?>
        <div class="mt-4">
            <?= component('data-table', [
                'columns' => ['nome' => 'Nome', 'documento' => 'Documento'],
                'rows' => [],
                'empty' => 'Nenhum cliente encontrado para "silva".',
                'dense' => true,
            ]) ?>
        </div>

        <?= componenteConteudo($secao('Paginação', 'Props montadas por paginacaoProps() a partir de total e offset, como faz MY_Controller::paginacao(). 195 registros, 10 por página, offset 50 (página 6); e o mesmo com o offset na query string, como no financeiro.')) ?>
        <div class="flex flex-col gap-3">
            <?= component('pagination', paginacaoProps(['base_url' => site_url('componentes/index'), 'total_rows' => 195, 'per_page' => 10, 'offset' => 50])) ?>
            <?= component('pagination', paginacaoProps(['base_url' => site_url('componentes?status=1'), 'total_rows' => 45, 'per_page' => 10, 'offset' => 0, 'query_string' => 'per_page'])) ?>
        </div>

        <?= componenteConteudo($secao('Modal', 'Abre com data-modal-abrir. Foco preso, Esc fecha, clique no fundo fecha e o foco volta ao botão.')) ?>
        <div class="flex flex-wrap gap-2">
            <?= component('button', ['label' => 'Abrir modal de exclusão', 'variant' => 'danger', 'attrs' => ['data-modal-abrir' => 'modal-excluir']]) ?>
            <?= component('button', ['label' => 'Abrir modal com formulário', 'variant' => 'outline', 'attrs' => ['data-modal-abrir' => 'modal-form']]) ?>
        </div>
        <?= component('modal-confirm', [
            'id' => 'modal-excluir',
            'title' => 'Excluir cliente?',
            'message' => 'Esta ação não pode ser desfeita. As OS do cliente continuam no sistema.',
            'confirm_attrs' => ['form' => 'form-excluir-exemplo'],
        ]) ?>
        <?php /* O catálogo não exclui nada: o formulário de exemplo só fecha o modal. */ ?>
        <form id="form-excluir-exemplo" method="dialog" class="hidden"></form>
        <?= component('modal', [
            'id' => 'modal-form',
            'title' => 'Novo serviço',
            'body' => [
                component('input', ['name' => 'servico_nome', 'label' => 'Nome do serviço', 'required' => true]),
                component('textarea', ['name' => 'servico_descricao', 'label' => 'Descrição', 'rows' => 3, 'class' => 'mt-1']),
            ],
            'footer' => [
                component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'attrs' => ['data-modal-fechar' => true]]),
                component('button', ['label' => 'Salvar', 'attrs' => ['data-modal-fechar' => true]]),
            ],
        ]) ?>
        <?= component('modal', ['id' => 'modal-salvo', 'title' => 'Formulário de exemplo', 'body' => 'O catálogo não envia nada.']) ?>

        <?= componenteConteudo($secao('Avisos (alert)', 'warning e danger usam role="alert"; info e success, role="status".')) ?>
        <div class="flex flex-col gap-3">
            <?= component('alert', ['variant' => 'info', 'message' => 'Há 3 OS aguardando aprovação do cliente.']) ?>
            <?= component('alert', ['variant' => 'success', 'title' => 'Cliente salvo', 'message' => 'As alterações foram gravadas.', 'dismissible' => true]) ?>
            <?= component('alert', ['variant' => 'warning', 'message' => 'O estoque do produto está abaixo do mínimo.', 'dismissible' => true]) ?>
            <?= component('alert', ['variant' => 'danger', 'title' => 'Não foi possível salvar', 'message' => 'Mensagem com <b>HTML</b> é escapada.']) ?>
        </div>

        <?= componenteConteudo($secao('Toasts', 'Renderizados no servidor (abaixo, sem sumir) ou criados por mostrarToast() no JS (botões).')) ?>
        <div class="flex flex-wrap items-start gap-3">
            <?= component('toast', ['variant' => 'success', 'title' => 'Pagamento confirmado', 'message' => 'Cobrança #123 baixada.', 'duration' => 0]) ?>
            <?= component('toast', ['variant' => 'danger', 'message' => 'Falha ao enviar o e-mail.', 'duration' => 0]) ?>
        </div>
        <div class="mt-3 flex flex-wrap gap-2" <?= js_module('componentes/catalogo') ?>>
            <?php foreach (['info', 'success', 'warning', 'danger'] as $variante) { ?>
                <?= component('button', ['label' => 'Toast ' . $variante, 'variant' => 'outline', 'size' => 'sm', 'attrs' => ['data-toast-variante' => $variante]]) ?>
            <?php } ?>
        </div>

        <?= componenteConteudo($secao('Contadores (badge)', 'Para quantidades e categorias; estados usam pill-status.')) ?>
        <div class="flex flex-wrap items-center gap-2">
            <?= component('badge', ['label' => '18']) ?>
            <?= component('badge', ['label' => '3', 'size' => 'sm']) ?>
            <?= component('badge', ['label' => 'Novo', 'variant' => 'accent']) ?>
        </div>

        <?= componenteConteudo($secao('Estado vazio')) ?>
        <?= component('empty-state', [
            'title' => 'Nenhuma ordem de serviço ainda',
            'message' => 'Cadastre a primeira OS para acompanhar os serviços do cliente.',
            'icon' => 'wrench',
            'action' => component('button', ['label' => 'Nova OS', 'icon' => 'plus']),
        ]) ?>
    </main>

    <div data-toast-region class="pointer-events-none fixed right-4 bottom-4 z-50 flex flex-col items-end gap-2"></div>
</body>
</html>
