<?php $totalServico = 0; $totalProdutos = 0;?>
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-tags"></i>
                </span>
                <h5>Ordem de Serviço</h5>
                <div class="buttons">
                    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'eOs')){
                        echo '<a title="Icon Title" class="btn btn-mini btn-info" href="'.site_url('os/editar/').$result->idOs.'"><i class="icon-pencil icon-white"></i> Editar</a>';
                    } ?>

                    <a id="imprimir" title="Imprimir" class="btn btn-mini btn-inverse" href=""><i class="icon-print icon-white"></i> Imprimir</a>
                </div>
            </div>
            <div class="widget-content" id="printOs">
                <div class="invoice-content">
                    <div class="invoice-head" style="margin-bottom: 0">

                        <table class="table">
                            <tbody>
                                <?php if($emitente == null) {?>

                                <tr>
                                    <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?=site_url('index.php/mapos/emitente')?>">Configurar</a><<<</td>
                                </tr>
                                <?php } else {?>
                                <tr>
                                    <td style="width: 25%"><img src=" <?=$emitente[0]->url_logo?> "></td>
                                    <td> <span style="font-size: 20px; "> <?=$emitente[0]->nome?></span> </br><span><?=$emitente[0]->cnpj?> </br>
                                      <?=$emitente[0]->rua.', nº:'.$emitente[0]->numero.', '.$emitente[0]->bairro.' - '.$emitente[0]->cidade.' - '.$emitente[0]->uf?> </span> </br> <span> E-mail: <?=$emitente[0]->email.' - Fone: '.$emitente[0]->telefone?></span></td>
                                    <td style="width: 18%; text-align: center">#Protocolo: <span ><?=$result->idOs?></span></br> </br> <span>Emissão: <?=date('d/m/Y')?></span></td>
                                </tr>

                                <?php } ?>
                            </tbody>
                        </table>


                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span><h5>Cliente</h5>
                                                <span><?=$result->nomeCliente?></span><br/>
                                                <span><?=$result->rua?>, <?=$result->numero?>, <?=$result->bairro?></span><br/>
                                                <span><?=$result->cidade?> - <?=$result->estado?></span>
                                            </li>
                                        </ul>
                                    </td>
                                    <td style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span><h5>Responsável</h5></span>
                                                <span><?=$result->nome?></span> <br/>
                                                <span>Telefone: <?=$result->telefone?></span><br/>
                                                <span>Email: <?=$result->email?></span>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                    <div style="margin-top: 0; padding-top: 0">

                    <?php if($result->descricaoProduto != null){?>
                    <hr style="margin-top: 0">
                    <h5>Descrição</h5>
                    <p>
                        <?=$result->descricaoProduto?>

                    </p>
                    <?php }?>

                    <?php if($result->defeito != null){?>
                    <hr style="margin-top: 0">
                    <h5>Defeito</h5>
                    <p>
                        <?=$result->defeito?>
                    </p>
                    <?php }?>
                    <?php if($result->laudoTecnico != null){?>
                    <hr style="margin-top: 0">
                    <h5>Laudo Técnico</h5>
                    <p>
                        <?=$result->laudoTecnico?>
                    </p>
                    <?php }?>
                    <?php if($result->observacoes != null){?>
                    <hr style="margin-top: 0">
                    <h5>Observações</h5>
                    <p>
                        <?=$result->observacoes?>
                    </p>
                    <?php }?>

                        <?php if($produtos != null){?>
                        <br />
                        <table class="table table-bordered" id="tblProdutos">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Quantidade</th>
                                            <th>Sub-total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        foreach ($produtos as $p) {

                                            $totalProdutos = $totalProdutos + $p->subTotal;
                                            echo '<tr>';
                                            echo '<td>'.$p->descricao.'</td>';
                                            echo '<td>'.$p->quantidade.'</td>';

                                            echo '<td>R$ '.number_format($p->subTotal,2,',','.').'</td>';
                                            echo '</tr>';
                                        }?>

                                        <tr>
                                            <td colspan="2" style="text-align: right"><strong>Total:</strong></td>
                                            <td><strong>R$ <?=number_format($totalProdutos,2,',','.')?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                               <?php }?>

                        <?php if($servicos != null){?>
                        <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Serviço</th>
                                                <th>Sub-total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                        setlocale(LC_MONETARY, 'en_US');
                                        foreach ($servicos as $s) {
                                            $preco = $s->preco;
                                            $totalServico = $totalServico + $preco;
                                            echo '<tr>';
                                            echo '<td>'.$s->nome.'</td>';
                                            echo '<td>R$ '.number_format($s->preco, 2, ',', '.').'</td>';
                                            echo '</tr>';
                                        }?>

                                        <tr>
                                            <td colspan="1" style="text-align: right"><strong>Total:</strong></td>
                                            <td><strong>R$ <?=number_format($totalServico, 2, ',', '.')?></strong></td>
                                        </tr>
                                        </tbody>
                                    </table>
                        <?php }?>
                        <hr />
                        <h4 style="text-align: right">Valor Total: R$ <?=number_format($totalProdutos + $totalServico,2,',','.')?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $("#imprimir").click(function(){
            PrintElem('#printOs');
        })

        function PrintElem(elem)
        {
            Popup($(elem).html());
        }

        function Popup(data)
        {
            var mywindow = window.open('', 'MapOs', 'height=600,width=800');
            mywindow.document.write('<html><head><title>Map Os</title>');
            mywindow.document.write("<link rel='stylesheet' href='<?=base_url('assets/css/bootstrap.min.css')?>' />");
            mywindow.document.write("<link rel='stylesheet' href='<?=base_url('assets/css/bootstrap-responsive.min.css')?>' />");
            mywindow.document.write("<link rel='stylesheet' href='<?=base_url('assets/css/matrix-style.css')?>' />");
            mywindow.document.write("<link rel='stylesheet' href='<?=base_url('assets/css/matrix-media.css')?>' />");


            mywindow.document.write("</head><body >");
            mywindow.document.write(data);
            mywindow.document.write("</body></html>");

            setTimeout(function(){
                mywindow.print();
            }, 50);

            return true;
        }

    });
</script>
