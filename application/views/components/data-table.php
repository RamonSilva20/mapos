<?php
/**
 * Tabela de dados das telas de listagem (DESIGN.md data-table).
 *
 * columns aceita o formato curto ['chave' => 'Título'] ou uma lista de
 * colunas:
 *
 *     [
 *         ['key' => 'nome', 'label' => 'Nome'],
 *         ['key' => 'status', 'label' => 'Status', 'nowrap' => true, 'render' => fn ($l) => component('pill-status', [...])],
 *         ['key' => 'valor', 'label' => 'Valor', 'align' => 'right'],
 *         ['label' => 'Ações', 'align' => 'right', 'render' => fn ($linha) => component('button', [...])],
 *     ]
 *
 * O valor de cada célula vem de $linha[key] (array) ou $linha->key (objeto).
 * render recebe a linha e devolve texto (escapado) ou HtmlSeguro. Sem linhas,
 * a tabela mostra o componente empty-state com a mensagem de empty.
 *
 * Coluna com align right usa algarismos tabulares (valores, datas); nowrap
 * impede quebra (status, técnico, data, grupo de ações). Abaixo de 640px cada linha vira um
 * bloco com "Título: valor" por célula.
 *
 * @var array                  $columns
 * @var iterable               $rows
 * @var string|HtmlSeguro      $empty    Mensagem, ou um empty-state já renderizado
 * @var string|null            $caption  Legenda para leitores de tela
 * @var bool                   $striped
 * @var bool                   $dense
 * @var string|null            $id
 * @var string                 $class
 * @var array                  $attrs
 */
if (! is_array($columns)) {
    throw new InvalidArgumentException('A prop columns de data-table deve ser um array.');
}

$colunas = [];
foreach ($columns as $chave => $coluna) {
    if (! is_array($coluna)) {
        $coluna = ['key' => (string) $chave, 'label' => $coluna];
    }
    if (! array_key_exists('label', $coluna)) {
        throw new InvalidArgumentException('Cada coluna de data-table precisa de label.');
    }
    if (! isset($coluna['key']) && ! isset($coluna['render'])) {
        throw new InvalidArgumentException('Cada coluna de data-table precisa de key ou render.');
    }
    if (isset($coluna['render']) && ! is_callable($coluna['render'])) {
        throw new InvalidArgumentException('O render de uma coluna de data-table deve ser chamável.');
    }
    $coluna['align'] ??= 'left';
    if (! in_array($coluna['align'], ['left', 'center', 'right'], true)) {
        throw new InvalidArgumentException('align de coluna aceita left, center ou right.');
    }
    $colunas[] = $coluna;
}

$alinhamentos = ['left' => 'text-left', 'center' => 'text-center', 'right' => 'text-right tabular-nums'];
$celula = $dense ? 'px-3 py-2' : 'px-4 py-3.5';
// Abaixo de 640px: linha em bloco e o título da coluna antes do valor.
$celulaCelular = 'max-sm:flex max-sm:items-baseline max-sm:justify-between max-sm:gap-4 max-sm:py-1 max-sm:text-right max-sm:before:text-left max-sm:before:text-caption max-sm:before:text-muted max-sm:before:content-[attr(data-label)]';
$linhas = array_values(is_array($rows) ? $rows : iterator_to_array($rows, false));

$valorDaCelula = static function ($linha, array $coluna) {
    if (isset($coluna['render'])) {
        return ($coluna['render'])($linha);
    }
    if (is_array($linha)) {
        return $linha[$coluna['key']] ?? null;
    }
    if (is_object($linha)) {
        return $linha->{$coluna['key']} ?? null;
    }

    return null;
};

$atributos = [
    'id' => $id,
    'class' => componenteClasses('overflow-x-auto rounded-xl border border-border bg-surface', $class),
];
?>
<div<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <table class="w-full border-collapse text-body-md text-text max-sm:block">
        <?php if ($caption !== null) { ?><caption class="sr-only"><?= e($caption) ?></caption><?php } ?>
        <thead class="bg-surface-subtle text-xs font-semibold tracking-[0.35px] text-muted uppercase max-sm:sr-only">
            <tr>
                <?php foreach ($colunas as $coluna) { ?>
                    <th scope="col" class="<?= e(componenteClasses($dense ? 'px-3 py-2' : 'px-4 py-3', $alinhamentos[$coluna['align']], 'border-b border-border whitespace-nowrap')) ?>"><?= e($coluna['label']) ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody class="max-sm:block">
            <?php if ($linhas === []) { ?>
                <tr>
                    <td colspan="<?= e(count($colunas)) ?>" class="p-0">
                        <?= componenteConteudo($empty instanceof HtmlSeguro ? $empty : component('empty-state', ['title' => $empty, 'class' => 'border-0'])) ?>
                    </td>
                </tr>
            <?php } ?>
            <?php foreach ($linhas as $i => $linha) { ?>
                <tr class="<?= e(componenteClasses('border-b border-border last:border-b-0 hover:bg-surface-subtle max-sm:block max-sm:px-4 max-sm:py-3', ['bg-surface-subtle/60' => $striped && $i % 2 === 1])) ?>">
                    <?php foreach ($colunas as $coluna) { ?>
                        <td data-label="<?= e($coluna['label']) ?>" class="<?= e(componenteClasses($celula, $alinhamentos[$coluna['align']], ['whitespace-nowrap' => ! empty($coluna['nowrap'])], $celulaCelular, $coluna['class'] ?? null)) ?>"><?php $valor = $valorDaCelula($linha, $coluna); ?><?php if (is_array($valor)) { ?><span class="<?= e(componenteClasses('inline-flex items-center gap-1', empty($coluna['nowrap']) ? 'flex-wrap' : 'flex-nowrap')) ?>"><?= componenteConteudo($valor) ?></span><?php } else { ?><?= componenteConteudo($valor) ?><?php } ?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
