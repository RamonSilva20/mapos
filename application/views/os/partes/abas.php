<?php
/**
 * Abas da tela da OS (#2842), por link (?aba=), com o total de cada lista.
 * Volta renderizada pelos endpoints de itens para os contadores mudarem.
 *
 * @var object       $os
 * @var string       $aba
 * @var list<object> $produtos
 * @var list<object> $servicos
 * @var list<object> $anexos
 * @var list<object> $anotacoes
 */
$idOs = (int) $os->idOs;
$url = static fn (string $nome) => site_url('os/visualizar/' . $idOs) . ($nome === 'resumo' ? '' : listagemQuery(['aba' => $nome]));
?>
<?= component('tabs', [
    'label' => 'Seções da OS',
    'items' => [
        ['label' => 'Resumo', 'url' => $url('resumo'), 'active' => $aba === 'resumo', 'icon' => 'file-text'],
        ['label' => 'Produtos', 'url' => $url('produtos'), 'active' => $aba === 'produtos', 'icon' => 'package', 'count' => count($produtos)],
        ['label' => 'Serviços', 'url' => $url('servicos'), 'active' => $aba === 'servicos', 'icon' => 'wrench', 'count' => count($servicos)],
        ['label' => 'Anexos', 'url' => $url('anexos'), 'active' => $aba === 'anexos', 'icon' => 'paperclip', 'count' => count($anexos)],
        ['label' => 'Anotações', 'short' => 'Notas', 'url' => $url('anotacoes'), 'active' => $aba === 'anotacoes', 'icon' => 'pencil', 'count' => count($anotacoes)],
        ['label' => 'Histórico', 'url' => $url('historico'), 'active' => $aba === 'historico', 'icon' => 'history'],
    ],
]) ?>
