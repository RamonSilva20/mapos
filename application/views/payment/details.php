<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Ordem de Serviço</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <img src="<?php echo $emitente->url_logo; ?>" alt="Logo" class="img-fluid" style="max-height: 50px;">
                <h3 class="h3 mb-0">Detalhes da Ordem de Serviço</h3>
                <span class="badge badge-info"><?php echo $result->status; ?></span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> <?php echo $result->nomeCliente; ?></p>
                    </div>
                    <div class="col-md-6">
                    <span><strong>Entrada:</strong> <?php echo (new DateTime($result->dataInicial))->format('d/m/Y'); ?></span><br>
                    <span><strong>Prev. saída:</strong> <?php echo (new DateTime($result->dataFinal))->format('d/m/Y'); ?></span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <?php if (count($produtos) > 0): ?>
                        <button class="btn btn-link" data-toggle="collapse" data-target="#produtosCollapse" aria-expanded="true">
                            <h2 class="h5">Produtos</h2>
                        </button>
                        <div class="collapse show" id="produtosCollapse">
                            <ul class="list-group">
                                <?php foreach ($produtos as $produto): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $produto->descricao; ?> - <?php echo $produto->quantidade; ?> x R$ <?php echo number_format($produto->preco, 2, ',', '.'); ?>
                                        <span class="badge badge-primary badge-pill">R$ <?php echo number_format($produto->subTotal, 2, ',', '.'); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <?php if (count($servicos) > 0): ?>
                        <button class="btn btn-link" data-toggle="collapse" data-target="#servicosCollapse" aria-expanded="true">
                            <h2 class="h5">Serviços</h2>
                        </button>
                        <div class="collapse show" id="servicosCollapse">
                            <ul class="list-group">
                                <?php foreach ($servicos as $servico): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $servico->nome; ?> - <?php echo $servico->quantidade; ?> x R$ <?php echo number_format($servico->preco, 2, ',', '.'); ?>
                                        <span class="badge badge-primary badge-pill">R$ <?php echo number_format($servico->subTotal, 2, ',', '.'); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-4">
                    <?php if (!empty($result->observacoes)) :?>
                    <div class="col-md-6">
                        <button class="btn btn-link" data-toggle="collapse" data-target="#observacoesCollapse" aria-expanded="true">
                            <h2 class="h5">Observações</h2>
                        </button>
                        <div class="collapse show" id="observacoesCollapse">
                            <p><?php echo $result->observacoes; ?></p>
                        </div>
                    </div>
                    <?php endif ?>
                    <?php if (!empty($result->laudoTecnico)) :?>
                    <div class="col-md-6">
                        <button class="btn btn-link" data-toggle="collapse" data-target="#laudoTecnicoCollapse" aria-expanded="true">
                            <h2 class="h5">Laudo Técnico</h2>
                        </button>
                        <div class="collapse show" id="laudoTecnicoCollapse">
                            <p><?php echo $result->laudoTecnico; ?></p>
                        </div>
                    </div>
                    <?php endif ?>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <div class="text-right">
                        <h2 class="h5">Valores</h2>
                        <p class="mb-1"><strong>Subtotal:</strong> R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></p>
                        <?php if ($desconto > 0) :?>
                        <p class="mb-1"><strong>Desconto:</strong> <?php echo $desconto; ?></p>
                        <?php endif; ?>
                        <p class="mb-1"><strong>Valor Final:</strong> R$ <?php echo number_format($valorFinal, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">&copy; 2024 <?php echo $emitente->nome; ?> - Todos os direitos reservados</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
