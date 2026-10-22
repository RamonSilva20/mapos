<?php
/**
 * Modal para mudar o desconto individual de um item já adicionado (#2880).
 * Os botões "Desconto" das linhas abrem o modal "desconto-item-<tipo>" e
 * levam o item em data-valor-id, data-valor-nome, data-valor-tipo e
 * data-valor-desconto; o formulário oculto é enviado sem recarregar pelo
 * módulo os/tela. O servidor revalida e calcula o subtotal. Usado pelas
 * telas da OS e da venda.
 *
 * @var string     $tipo            produto ou servico
 * @var string     $rota            rota do endpoint (ex. os/descontoProduto)
 * @var string     $campoRegistro   nome do campo com o id do registro (idOs, idVendas)
 * @var int        $idRegistro
 * @var string     $rotuloRegistro  OS ou venda
 */
$idForm = 'form-desconto-item-' . $tipo;
?>
<form id="<?= e($idForm) ?>" method="post" action="<?= e(site_url($rota)) ?>" novalidate hidden data-os-acao="desconto-item">
    <input type="hidden" name="<?= e($campoRegistro) ?>" value="<?= e($idRegistro) ?>">
    <input type="hidden" name="idItem" value="" data-modal-de="<?= e('desconto-item-' . $tipo) ?>" data-modal-valor="id">
</form>
<?= component('modal', [
    'id' => 'desconto-item-' . $tipo,
    'title' => 'Desconto do item',
    'size' => 'sm',
    'body' => [
        new HtmlSeguro('<p class="mb-4 text-body-md text-text"><strong class="font-semibold" data-modal-valor="nome"></strong></p>'),
        new HtmlSeguro('<div class="grid gap-4">'),
        component('select', [
            'name' => 'tipoDescontoItem',
            'id' => 'desconto-item-tipo-' . $tipo,
            'label' => 'Tipo',
            'options' => ['real' => 'Em reais (R$)', 'porcento' => 'Em porcentagem (%)'],
            'selected' => 'real',
            'required' => true,
            'attrs' => ['form' => $idForm, 'data-modal-valor' => 'tipo'],
        ]),
        component('input', [
            'name' => 'descontoItem',
            'id' => 'desconto-item-valor-' . $tipo,
            'label' => 'Desconto',
            'placeholder' => '0,00',
            'required' => true,
            'help' => 'Informe 0 para tirar o desconto do item. O desconto geral da ' . $rotuloRegistro . ' é removido, porque o total muda.',
            'attrs' => [
                'form' => $idForm,
                'data-modal-valor' => 'desconto',
                'inputmode' => 'decimal',
                'pattern' => '^\d+([.,]\d{1,2})?$',
                'data-msg-vazio' => 'Informe o desconto, ou 0 para tirar.',
                'data-msg-invalido' => 'Use só números, como 10 ou 10,50.',
            ],
        ]),
        new HtmlSeguro('</div>'),
    ],
    'footer' => [
        component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'attrs' => ['data-modal-fechar' => true]]),
        component('button', ['label' => 'Aplicar', 'icon' => 'percent', 'type' => 'submit', 'attrs' => ['form' => $idForm, 'data-rotulo-carregando' => 'Aplicando…']]),
    ],
]) ?>
