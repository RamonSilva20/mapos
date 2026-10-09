<?php
/**
 * Tela da venda (#2843), migrada para os componentes da v5, no mesmo desenho
 * da tela da OS (views/os/visualizar.php).
 *
 * - Cabeçalho com o número, o status (pill-status) e o cliente. "Editar venda"
 *   é a ação principal, na topbar; faturar, impressões, cobrança e excluir são
 *   secundárias.
 * - Uma página só: resumo dos valores, dados e observações, produtos,
 *   desconto e PIX. Os trechos que mudam ficam em [data-os-parte] e vêm de
 *   views/vendas/partes/, que os endpoints renderizam de novo.
 * - O módulo vendas/tela (o motor de os/tela) envia os formulários
 *   [data-os-acao] sem recarregar a página (lib/http.js) e troca os trechos
 *   devolvidos pelo servidor.
 *
 * @var object      $venda
 * @var array       $totais
 * @var list<object> $produtos
 * @var array<string, bool> $pode
 * @var object|null $cobranca
 * @var array|null  $whatsapp
 * @var string|null $garantia_ate
 * @var bool        $controle_estoque
 * @var array<string, string> $gateways
 */
$idVenda = (int) $venda->idVendas;
$cancelada = $venda->status === 'Cancelado';
$fechada = (int) $venda->faturado === 1 || $venda->status === 'Faturado' || $cancelada;
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$campos = static fn (array $itens) => array_filter($itens, static fn ($valor) => $valor !== null && $valor !== '');

$dados = $campos([
    'Cliente' => $pode['ver_cliente']
        ? component('link', ['label' => $venda->nomeCliente, 'href' => site_url('clientes/visualizar/' . (int) $venda->clientes_id)])
        : $venda->nomeCliente,
    'Contato' => trim(implode(' · ', array_filter([$venda->celular, $venda->telefone]))),
    'E-mail' => $venda->email,
    'Vendedor' => $venda->nome,
    'Data da venda' => dataBr($venda->dataVenda),
    'Garantia' => (int) $venda->garantia > 0 ? (int) $venda->garantia . ' dias' . ($garantia_ate ? ', até ' . dataBr($garantia_ate) : '') : null,
]);

$textos = $campos([
    'Observações internas' => trim(strip_tags((string) $venda->observacoes)) !== '' ? osTextoExibicao($venda->observacoes) : null,
    'Observações ao cliente' => trim(strip_tags((string) $venda->observacoes_cliente)) !== '' ? osTextoExibicao($venda->observacoes_cliente) : null,
]);
?>
<div class="flex flex-col gap-4 pt-2 pb-8" <?= js_module('vendas/tela') ?>>
    <header class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display text-heading-xl text-text"><?= e('Venda #' . $idVenda) ?></h1>
                <?= component('pill-status', osStatusPill($venda->status)) ?>
            </div>
            <p class="text-caption [overflow-wrap:anywhere] text-muted"><?= e($venda->nomeCliente . ' · Vendedor: ' . $venda->nome) ?></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if ($pode['faturar']) { ?>
                <?= component('button', ['label' => 'Faturar', 'icon' => 'circle-dollar-sign', 'variant' => 'outline', 'attrs' => ['data-modal-abrir' => 'faturar-venda']]) ?>
            <?php } ?>
            <?= component('button', ['label' => 'Imprimir', 'icon' => 'printer', 'variant' => 'ghost', 'href' => site_url('vendas/imprimir/' . $idVenda), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) ?>
            <?= component('button', ['label' => 'Orçamento', 'icon' => 'file-text', 'variant' => 'ghost', 'href' => site_url('vendas/imprimirVendaOrcamento/' . $idVenda), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) ?>
            <?= component('button', ['label' => 'Cupom 80mm', 'icon' => 'receipt', 'variant' => 'ghost', 'href' => site_url('vendas/imprimirTermica/' . $idVenda), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) ?>
            <?php if ($pode['cobrar'] && $gateways !== []) { ?>
                <?= component('button', ['label' => 'Gerar cobrança', 'icon' => 'banknote', 'variant' => 'ghost', 'attrs' => ['data-modal-abrir' => 'gerar-cobranca']]) ?>
            <?php } elseif ($cobranca !== null && $pode['ver_cobranca']) { ?>
                <?= component('button', ['label' => 'Ver cobrança', 'icon' => 'banknote', 'variant' => 'ghost', 'href' => site_url('cobrancas/visualizar/' . (int) $cobranca->idCobranca)]) ?>
            <?php } ?>
            <?php if ($pode['excluir']) { ?>
                <?= component('button', ['label' => 'Excluir', 'icon' => 'trash-2', 'variant' => 'ghost', 'class' => 'hover:text-danger-ink', 'attrs' => ['data-modal-abrir' => 'excluir-venda']]) ?>
            <?php } ?>
        </div>
    </header>

    <?php if (empty($emitente)) { ?>
        <?= component('alert', ['variant' => 'warning', 'message' => 'Configure os dados do emitente para a venda sair completa nas impressões e no PIX (Configurações > Emitente).']) ?>
    <?php } ?>

    <?php if ($fechada && ! $pode['editar']) { ?>
        <?= component('alert', ['variant' => 'info', 'message' => 'Esta venda está ' . ($cancelada ? 'cancelada' : 'faturada') . ': produtos e desconto não podem mais ser alterados.']) ?>
    <?php } elseif ($cancelada && $pode['editar']) { ?>
        <?= component('alert', ['variant' => 'info', 'message' => 'Esta venda está cancelada e os produtos dela já voltaram ao estoque. Para mexer nos produtos, reabra a venda mudando o status em Editar venda.']) ?>
    <?php } ?>

    <div data-os-parte="totais"><?php $this->load->view('vendas/partes/totais'); ?></div>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-dados">
            <h2 id="secao-dados" class="text-heading-sm text-text">Dados da venda</h2>
            <dl class="mt-3 flex flex-col gap-3">
                <?php foreach ($dados as $rotulo => $valor) { ?>
                    <div>
                        <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                        <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= componenteConteudo($valor) ?></dd>
                    </div>
                <?php } ?>
            </dl>
        </section>
        <section class="<?= e($caixa) ?> lg:col-span-2" aria-labelledby="secao-observacoes">
            <h2 id="secao-observacoes" class="text-heading-sm text-text">Observações</h2>
            <?php if ($textos === []) { ?>
                <p class="mt-3 text-caption text-muted">Nada informado. As observações da venda são preenchidas em Editar venda.</p>
            <?php } else { ?>
                <dl class="mt-3 grid gap-4 sm:grid-cols-2">
                    <?php foreach ($textos as $rotulo => $valor) { ?>
                        <div>
                            <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                            <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= componenteConteudo($valor) ?></dd>
                        </div>
                    <?php } ?>
                </dl>
            <?php } ?>
        </section>
    </div>

    <?php if ($pode['itens']) { ?>
        <section id="secao-adicionar-produto" class="<?= e($caixa) ?>" aria-labelledby="titulo-adicionar-produto">
            <h2 id="titulo-adicionar-produto" class="text-heading-sm text-text">Adicionar produto</h2>
            <form method="post" action="<?= e(site_url('vendas/adicionarProduto')) ?>" novalidate
                  class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_7rem_9rem_auto] lg:items-start"
                  data-os-acao="produto" data-os-limpar>
                <input type="hidden" name="idVendas" value="<?= e($idVenda) ?>">
                <div class="relative sm:col-span-2 lg:col-span-1"<?= componenteAtributos([
                    'data-autocomplete' => site_url('os/autoCompleteProdutoSaida'),
                    'data-autocomplete-alvo' => 'item-id',
                    'data-os-preco' => 'item-preco',
                    'data-os-estoque' => $controle_estoque ? 'item-estoque' : null,
                ]) ?>>
                    <?= component('input', [
                        'name' => 'produto',
                        'id' => 'item-nome',
                        'label' => 'Produto',
                        'required' => true,
                        'autocomplete' => 'off',
                        'placeholder' => 'Descrição ou código de barras',
                        'help' => $controle_estoque ? 'O produto sai do estoque ao ser adicionado.' : null,
                        'attrs' => [
                            'data-msg-vazio' => 'Escolha o produto.',
                            'data-msg-invalido' => 'Escolha um produto da lista.',
                        ],
                    ]) ?>
                    <input type="hidden" id="item-id" name="idProduto" value="">
                    <p id="item-estoque" class="mt-1 text-caption text-muted" aria-live="polite"></p>
                </div>
                <?= component('input', [
                    'name' => 'quantidade',
                    'id' => 'item-quantidade',
                    'label' => 'Quantidade',
                    'type' => 'number',
                    'value' => '1',
                    'required' => true,
                    'attrs' => [
                        'min' => '1',
                        'step' => '1',
                        'inputmode' => 'numeric',
                        'data-msg-vazio' => 'Informe a quantidade.',
                        'data-msg-invalido' => 'Informe um número inteiro maior que zero.',
                    ],
                ]) ?>
                <?= component('input', [
                    'name' => 'preco',
                    'id' => 'item-preco',
                    'label' => 'Preço (R$)',
                    'required' => true,
                    'placeholder' => '0,00',
                    'help' => 'Sugerido pelo cadastro; pode ser alterado.',
                    'attrs' => [
                        'inputmode' => 'numeric',
                        'data-mascara' => 'dinheiro',
                        'data-msg-vazio' => 'Informe o preço.',
                    ],
                ]) ?>
                <div class="lg:pt-7">
                    <?= component('button', ['label' => 'Adicionar', 'icon' => 'plus', 'variant' => 'outline', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Adicionando…']]) ?>
                </div>
            </form>
        </section>
    <?php } ?>

    <section aria-labelledby="titulo-produtos">
        <h2 id="titulo-produtos" class="mb-2 text-heading-sm text-text">Produtos</h2>
        <div data-os-parte="produtos"><?php $this->load->view('vendas/partes/produtos'); ?></div>
    </section>

    <div data-os-parte="desconto"><?php $this->load->view('vendas/partes/desconto'); ?></div>
    <div data-os-parte="pix"><?php $this->load->view('vendas/partes/pix'); ?></div>
</div>

<?php
// Exclusão de produto: um modal-confirm preenchido pelo botão da linha
// (data-valor-id, data-valor-nome) e um formulário oculto que o módulo
// vendas/tela envia sem recarregar.
if ($pode['itens']) { ?>
    <form id="form-excluir-produto" method="post" action="<?= e(site_url('vendas/excluirProduto')) ?>" hidden data-os-acao="produto">
        <input type="hidden" name="idVendas" value="<?= e($idVenda) ?>">
        <input type="hidden" name="idItem" value="" data-modal-de="excluir-produto" data-modal-valor="id">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-produto',
        'title' => 'Excluir produto?',
        // Marcação estática; o nome entra por textContent (modules/componentes/modal.js).
        'message' => [new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="nome"></strong>'), ' sai da venda.' . ($controle_estoque ? ' A quantidade volta ao estoque.' : '') . ' O desconto da venda, se houver, é removido.'],
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-produto'],
    ]) ?>
<?php } ?>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-venda" method="post" action="<?= e(site_url('vendas/excluir')) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="<?= e($idVenda) ?>">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-venda',
        'title' => 'Excluir venda #' . $idVenda . '?',
        'message' => 'A venda e os produtos dela serão removidos' . ($controle_estoque && ! $cancelada ? ', e as quantidades voltam ao estoque' : '') . '.' . ((int) $venda->faturado === 1 ? ' O lançamento da fatura também sai do financeiro.' : '') . ' Essa ação não pode ser desfeita.',
        'confirm_label' => 'Excluir venda',
        'confirm_attrs' => ['form' => 'form-excluir-venda'],
    ]) ?>
<?php } ?>

<?php if ($pode['faturar']) { ?>
    <form id="form-faturar" method="post" action="<?= e(site_url('vendas/faturar')) ?>" novalidate hidden data-os-acao="faturar">
        <input type="hidden" name="idVendas" value="<?= e($idVenda) ?>">
    </form>
    <?= component('modal', [
        'id' => 'faturar-venda',
        'title' => 'Faturar venda #' . $idVenda,
        'body' => [
            component('alert', [
                'variant' => $totais['total'] > 0 ? 'info' : 'warning',
                'message' => $totais['total'] > 0
                    ? 'Será lançada uma receita de ' . dinheiro($totais['total']) . ($totais['desconto'] > 0 ? ' (' . dinheiro($totais['bruto']) . ' menos ' . dinheiro($totais['desconto']) . ' de desconto)' : '') . ' para ' . $venda->nomeCliente . '. A venda passa a Faturado e não pode mais ser alterada.'
                    : 'Adicione produtos antes de faturar.',
            ]),
            new HtmlSeguro('<div class="mt-4 grid gap-4 sm:grid-cols-2">'),
            new HtmlSeguro('<div class="sm:col-span-2">'),
            component('input', ['name' => 'descricao', 'id' => 'fatura-descricao', 'label' => 'Descrição', 'value' => 'Fatura de Venda Nº: ' . $idVenda, 'required' => true, 'attrs' => ['form' => 'form-faturar', 'maxlength' => '255', 'data-msg-vazio' => 'Informe a descrição do lançamento.']]),
            new HtmlSeguro('</div>'),
            component('input', ['name' => 'vencimento', 'id' => 'fatura-vencimento', 'label' => 'Vencimento', 'type' => 'date', 'value' => date('Y-m-d'), 'required' => true, 'attrs' => ['form' => 'form-faturar', 'data-msg-vazio' => 'Informe o vencimento.']]),
            component('select', ['name' => 'formaPgto', 'id' => 'fatura-forma', 'label' => 'Forma de pagamento', 'options' => array_combine(OS_FORMAS_PAGAMENTO, OS_FORMAS_PAGAMENTO), 'placeholder' => 'Escolha', 'required' => true, 'attrs' => ['form' => 'form-faturar', 'data-msg-vazio' => 'Escolha a forma de pagamento.']]),
            component('checkbox', ['name' => 'recebido', 'id' => 'fatura-recebido', 'label' => 'Já foi recebido', 'help' => 'Marca o lançamento como pago.', 'attrs' => ['form' => 'form-faturar', 'data-os-recebido' => 'fatura-recebimento']]),
            component('input', ['name' => 'recebimento', 'id' => 'fatura-recebimento', 'label' => 'Data do recebimento', 'type' => 'date', 'value' => date('Y-m-d'), 'disabled' => true, 'help' => 'Habilitada ao marcar "Já foi recebido".', 'attrs' => ['form' => 'form-faturar', 'data-msg-vazio' => 'Informe a data do recebimento.']]),
            new HtmlSeguro('<div class="sm:col-span-2">'),
            component('textarea', ['name' => 'observacoes', 'id' => 'fatura-observacoes', 'label' => 'Observações', 'rows' => 2, 'attrs' => ['form' => 'form-faturar']]),
            new HtmlSeguro('</div></div>'),
        ],
        'footer' => [
            component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'attrs' => ['data-modal-fechar' => true]]),
            component('button', ['label' => 'Faturar', 'icon' => 'circle-dollar-sign', 'type' => 'submit', 'disabled' => $totais['total'] <= 0, 'attrs' => ['form' => 'form-faturar', 'data-rotulo-carregando' => 'Faturando…']]),
        ],
    ]) ?>
<?php } ?>

<?php if ($pode['cobrar'] && $gateways !== []) { ?>
    <form id="form-cobranca" method="post" action="<?= e(site_url('cobrancas/adicionar')) ?>" novalidate hidden data-os-acao="cobranca">
        <input type="hidden" name="id" value="<?= e($idVenda) ?>">
        <input type="hidden" name="tipo" value="venda">
        <input type="hidden" name="gateway_de_pagamento" value="">
        <input type="hidden" name="forma_pagamento" value="">
    </form>
    <?= component('modal', [
        'id' => 'gerar-cobranca',
        'title' => 'Gerar cobrança da venda #' . $idVenda,
        'size' => 'sm',
        'body' => [
            component('select', [
                'name' => 'cobranca',
                'id' => 'cobranca-forma',
                'label' => 'Gateway e forma de pagamento',
                'options' => $gateways,
                'placeholder' => 'Escolha',
                'required' => true,
                'help' => 'A cobrança de ' . dinheiro($totais['total']) . ' é criada no gateway escolhido.',
                'attrs' => ['form' => 'form-cobranca', 'data-msg-vazio' => 'Escolha o gateway e a forma de pagamento.'],
            ]),
        ],
        'footer' => [
            component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'attrs' => ['data-modal-fechar' => true]]),
            component('button', ['label' => 'Gerar cobrança', 'icon' => 'banknote', 'type' => 'submit', 'attrs' => ['form' => 'form-cobranca', 'data-rotulo-carregando' => 'Gerando…']]),
        ],
    ]) ?>
<?php } ?>
