<?php
/**
 * Abertura dos relatórios em PDF (mPDF): cabeçalho HTML, dados do emitente e
 * título. O CSS é o assets/dist/pdf.css (Tailwind achatado, ver
 * scripts/build-pdf-css.mjs); o mPDF não lê colunas flexíveis nem grade, então o layout
 * usa tabelas.
 *
 * @var string $tituloDoc Título do relatório
 */
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <title>Map-OS</title>
    <link rel="stylesheet" href="<?= base_url() ?>assets/dist/pdf.css" />
</head>

<body>
    <?php $this->load->view('relatorios/imprimir/imprimirTopo'); ?>
    <h1 class="rel-titulo"><?= e($tituloDoc) ?></h1>
