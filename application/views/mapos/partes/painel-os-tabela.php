<?php
/**
 * Tabela "Ver como tabela" do gráfico de OS por status (#3037). Usada no
 * primeiro render (mapos/painel.php) e quando o filtro de mês troca a série
 * por AJAX (Mapos::painelGraficoOs). O Valor é sensível ao botão de olho.
 *
 * @var list<array{status: string, total: int, valor: float, variante: string}> $os_grafico
 */
?>
<table class="w-full text-caption">
    <caption class="sr-only">OS por status</caption>
    <thead>
        <tr class="text-left text-muted">
            <th scope="col" class="py-1 pr-2 font-medium">Status</th>
            <th scope="col" class="px-2 py-1 text-right font-medium">Qtd.</th>
            <th scope="col" class="py-1 pl-2 text-right font-medium">Valor</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($os_grafico as $fatia) { ?>
            <tr class="border-t border-border">
                <td class="py-1 pr-2 text-text"><?= e($fatia['status']) ?></td>
                <td class="px-2 py-1 text-right tabular-nums text-text"><?= e($fatia['total']) ?></td>
                <td class="py-1 pl-2 text-right tabular-nums text-text"><?= component('valor-sensivel', ['value' => dinheiro($fatia['valor'])]) ?></td>
            </tr>
        <?php } ?>
        <?php if ($os_grafico === []) { ?>
            <tr class="border-t border-border"><td colspan="3" class="py-2 text-center text-muted">Nenhuma OS neste mês.</td></tr>
        <?php } ?>
    </tbody>
</table>
