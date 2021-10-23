<!DOCTYPE html>
<html>

<head>
    <title>MAPOS</title>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/main.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/blue.css" class="skin-color" />
</head>

<body style="background-color: transparent">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                    <?= $topo ?>
                    <div class="widget-title">
                        <h4 style="text-align: center; font-size: 1.1em; padding: 5px;">
                            <?= ucfirst($title) ?>
                        </h4>
                    </div>
                        <div class="widget-content nopadding tab-content">
                        <table width="100%" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="480" style="font-size: 15px">Nome</th>
                                        <th width="170" style="font-size: 15px">Documento</th>
                                        <th width="150" style="font-size: 15px">Telefone</th>
                                        <th width="200" style="font-size: 15px">Email</th>
                                        <th width="120" style="font-size: 15px">Cadastro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes as $c) : ?>
                                    <?php $dataCadastro = date('d/m/Y', strtotime($c->dataCadastro)) ?>
                                    <tr>
                                        <td><?= $c->nomeCliente ?></td>
                                        <td align="center"><?= $c->documento ?></td>
                                        <td align="center"><?= $c->telefone ?></td>
                                        <td align="center"><?= $c->email ?></td>
                                        <td align="center"><?= $dataCadastro ?></td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>

                        </div>

                </div>
                <h5 style="text-align: right; font-size: 0.8em; padding: 5px;">Data do Relatório:
                    <?php echo date('d/m/Y'); ?>
                </h5>

            </div>
        </div>
    </div>
</body>

</html>
