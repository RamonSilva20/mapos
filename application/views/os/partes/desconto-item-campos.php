<?php
/**
 * Desconto individual do item (#2880) no formulário de adicionar produto ou
 * serviço, em R$ ou %. Opcional: vazio ou 0 é sem desconto. O servidor
 * calcula o subtotal do item (osDescontoDoItem()); estes campos só enviam o
 * que foi digitado. Usado pelas telas da OS e da venda.
 */
?>
<div class="grid gap-4 sm:col-span-2 sm:grid-cols-[9rem_minmax(0,12rem)] lg:col-span-4">
    <?= component('select', [
        'name' => 'tipoDescontoItem',
        'id' => 'item-desconto-tipo',
        'label' => 'Tipo do desconto',
        'options' => ['real' => 'Em reais (R$)', 'porcento' => 'Em porcentagem (%)'],
        'selected' => 'real',
    ]) ?>
    <?= component('input', [
        'name' => 'descontoItem',
        'id' => 'item-desconto-valor',
        'label' => 'Desconto do item',
        'placeholder' => '0,00',
        'help' => 'Opcional. Vale só para este item.',
        'attrs' => [
            'inputmode' => 'decimal',
            'pattern' => '^\d+([.,]\d{1,2})?$',
            'data-msg-invalido' => 'Use só números, como 10 ou 10,50.',
        ],
    ]) ?>
</div>
