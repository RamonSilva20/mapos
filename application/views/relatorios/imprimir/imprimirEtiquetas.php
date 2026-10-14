<?php
/**
 * Etiquetas com código de barras (mPDF). EAN13, QR e UPCA usam a etiqueta
 * estreita e o código um pouco menor; qtdEtiqueta repete a etiqueta pelo
 * estoque do produto.
 */
$tipo = (string) $this->input->get('etiquetaCode');
$estreita = in_array($tipo, ['EAN13', 'QR', 'UPCA'], true);
$classe = $estreita ? 'etiqueta-ean13' : 'etiqueta-padrao';
$tamanho = $estreita ? '0.62' : '0.7';
$repetir = isset($_GET['qtdEtiqueta']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <title>Etiquetas</title>
    <link rel="stylesheet" href="<?= base_url() ?>assets/dist/pdf.css" />
</head>

<body>
    <?php foreach ($produtos as $p) : ?>
        <?php for ($i = 0, $vezes = $repetir ? (int) $p->estoque : 1; $i < $vezes; $i++) : ?>
            <div class="etiqueta <?= e($classe) ?>">
                <div class="etiqueta-descricao">
                    <strong><?= e(limitarTexto(strtoupper($p->descricao), 23)) ?></strong>
                </div>
                <div class="etiqueta-texto">
                    Cod: <b><?= e($p->idProdutos) ?></b><br />
                    Preço: R$ <b><?= e(str_replace('.', ',', $p->precoVenda)) ?></b>
                </div>
                <div class="barcodecell">
                    <barcode code="<?= e($p->codDeBarra) ?>" text="0" type="<?= e($tipo) ?>" size="<?= e($tamanho) ?>" disableborder="0" class="barcode" />
                </div>
            </div>
        <?php endfor; ?>
    <?php endforeach; ?>
</body>

</html>
