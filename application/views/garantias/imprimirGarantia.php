<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Map OS</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/dist/imprimir.css">
</head>

<body>
    <div class="doc-folha" id="viaCliente">
        <?php if ($emitente == null) { ?>
            <div class="doc-cabecalho bg-warning-soft text-warning-ink">
                Você precisa configurar os dados do emitente. &gt;&gt;&gt;<a class="underline" href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>&lt;&lt;&lt;
            </div>
        <?php } else { ?>
            <div class="doc-cabecalho">
                <div class="w-1/4"><img src="<?php echo e($emitente->url_logo); ?>" alt=""></div>
                <div class="flex-1">
                    <span class="text-xl"><?php echo e($emitente->nome); ?></span><br>
                    <span>
                        <?php echo e($emitente->cnpj); ?><br>
                        <?php echo e($emitente->rua . ', nº:' . $emitente->numero . ', ' . $emitente->bairro . ' - ' . $emitente->cidade . ' - ' . $emitente->uf); ?>
                    </span><br>
                    <span>E-mail: <?php echo e($emitente->email . ' - Fone: ' . $emitente->telefone); ?></span>
                </div>
                <div class="flex w-1/5 flex-col text-center">
                    <span>Emissão: <?php echo date('d/m/Y'); ?></span>
                </div>
            </div>
        <?php } ?>

        <h1 class="mt-4 text-center text-base font-bold">Termo de Garantia</h1>

        <div class="mt-4">
            <?php echo printSafeHtml($result->textoGarantia) ?>
        </div>

        <div class="mt-4 grid grid-cols-3 border border-hairline-cool">
            <div class="border-r border-hairline-cool p-2 pb-8">Data</div>
            <div class="border-r border-hairline-cool p-2 pb-8">Assinatura do Cliente</div>
            <div class="p-2 pb-8">Assinatura do Técnico Responsável</div>
        </div>
    </div>
<script src="<?= e(base_url('assets/js/imprimir.js')) ?>"></script>
</body>

</html>
