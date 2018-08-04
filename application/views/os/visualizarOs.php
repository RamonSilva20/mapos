<?php $totalServico = 0; $totalProdutos = 0;?>
<?php $operador = $this->session->userdata('nome'); ?>
<?php
    $color = "default";
    $status = $result->status;

    if ($status == "Orçamento") {
        $color = "inverse";
    } else if ($status == "Aprovado") {
        $color = "info";
    } else if ($status == "Em Andamento") {
        $color = "info";
    } else if ($status == "Finalizado") {
        $color = "success";
    } else if ($status == "Faturado") {
        $color = "success";
    } else if ($status == "Aguardando Peça") {
        $color = "warning";
    } else if ($status == "Aguardando Cliente") {
        $color = "important";
    } else if ($status == "Cancelado") {
        $color = "default";
    } else {
        $color = "default";
    }
?>
<input id="sendOsId" type="hidden" name="sendOsId" value="<?php echo $result->idOs ?>"  />
<input id="sendEmailCliente" type="hidden" name="sendEmailCliente" value="<?php echo $result->email ?>"  />
<input id="sendNomeCliente" type="hidden" name="sendNomeCliente" value="<?php echo $result->nomeCliente?>"  />
<input id="urlAtual" type="hidden" name="urlAtual" value="<?php echo current_url() ?>"  />

<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">

            <div class="widget-title">
                <span class=<?php echo '"icon label label-'.$color.'"'?> style="padding-bottom: 13px; margin-top: 0;"><?php echo $status?></span>
                <h5>Ordem de Serviço</h5>
                <div class="buttons">
                    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'eOs')){
                        echo '<a title="Icon Title" class="btn btn-mini btn-info" href="'.base_url().'index.php/os/editar/'.$result->idOs.'"><i class="icon-pencil icon-white"></i> Editar</a>'; 
                    } ?>
                    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'eOs')){
                        $zapnumber = preg_replace("/[^0-9]/", "", $result->celular );
                        echo '<a title="Enviar WhatsApp" class="btn btn-mini btn-inverse" id="enviarWhatsApp" target="_blank" href="https://wa.me/55'.$zapnumber.'?text=Prezado%20'.$result->nomeCliente.'%20. Sua%20OS'.$result->idOs.'%20foi%20atualizada.%20Entre%20em%20contato%20para%20saber%20mais%20detalhes."><i class="icon-phone icon-white"></i> WhatsApp</a>'; 
                    } ?>
                    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'eOs')){
                        echo '<a title="Enviar Email" class="btn btn-mini btn-inverse" id="enviarEmail"><i class="icon-envelope icon-white"></i> Email</a>'; 
                    } ?>
                    <!-- <a id="imprimir" title="Imprimir" class="btn btn-mini btn-inverse" href=""><i class="icon-print icon-white"></i> Imprimir OS Antiga</a> -->
                    <a id="imprimir2" title="Imprimir" class="btn btn-mini btn-inverse" href=""><i class="icon-print icon-white"></i> Imprimir</a>
                    <input id="urlCliente" type="hidden" value=<?php echo '"http://www.computadorpc.com/os/index.php/clientes/visualizar/'.$result->idClientes.'"' ?></input>
                </div>
            </div>

            
            <div class="widget-content" id="printOs2">
                <!-- OS PRINT - FRENTE -->
                <page size="A4">
                    <!-- via loja -->
                    <page size="A2" id="osLoja">
                        <div class="invoice-content">
                            <div class="invoice-head" style="margin-bottom: 0">
                                <table class="table table-bordered" style="margin-bottom: 10px;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 15%; font-size: 13px; text-align: center; vertical-align: middle;">#<?php echo $result->idOs?></td>
                                            <td style="width: 30%; font-size: 11px; text-align: center; vertical-align: middle; padding: 2px;"><span class="linkCliente" style="cursor: pointer;"><?php echo $result->nomeCliente?></span> - <?php if ($result->celular != ""){echo $result->celular;}else{echo $result->telefone;}?> <?php if ($result->tipoCelular != ""){echo "- "; echo $result->tipoCelular;}else{echo "- "; echo $result->tipoTelefone;} ?></td>
                                            <td style="width: 25%; font-size: 11px; text-align: center; vertical-align: middle; padding: 2px;">Entrada <?php echo date('d/m/Y', strtotime($result->dataInicial)); ?> - Previsão <?php echo date('d/m/Y', strtotime($result->dataFinal)); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table" style="margin-bottom: 0">
                                    <tbody>
                                            <?php if($emitente == null) {?>        
                                            <tr>
                                                <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a><<<</td>
                                            </tr>
                                            <?php } else {?>
                                            <tr>
                                                <td style="width: 100px; padding: 8px;"><img src=" <?php echo $emitente[0]->url_logo; ?> "></td>
                                                <td style="line-height: 17px;"> <span style="font-size: 14px; "> <?php echo $emitente[0]->nome; ?></span> </br><span style="font-size: 10px;"><?php echo $emitente[0]->rua.', '.$emitente[0]->numero.', '.$emitente[0]->bairro.' - '.$emitente[0]->cidade.' - '.$emitente[0]->uf; ?> </br> <?php if ($emitente[0]->telefone != ""){echo "Fone: "; echo $emitente[0]->telefone;} if($emitente[0]->celular != "") { echo ' Celular: '; echo $emitente[0]->celular; } if($emitente[0]->whatsapp != "") { echo ' WhatsApp: '; echo $emitente[0]->whatsapp; } ?></br> E-mail: <?php echo $emitente[0]->email; ?> </span></td>
                                                <td style="width: 18%; text-align: center"><span style="font-size: 12px">#Protocolo: <?php echo $result->idOs?></span></br><span style="font-size: 12px">Emissão: <?php echo date('d/m/Y')?></span><?php if($result->garantia == 'selecione'){echo '';} else echo "</br><span style='font-size: 12px'>Garantia: ".$result->garantia."</span>"?></td>
                                            </tr>
                                            <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <hr style="margin-top: 0; margin-bottom: 10px;">
                            <table class="table table-bordered" id="tblProdutos">
                              <tr>
                                <th>Equipamento</th>
                                <td><span style="font-size: 10px;">
                                <?php if($result->equipamento == 'selecione'){echo '';} else echo $result->equipamento?> 
                                <?php if($result->marca == 'selecione'){echo '';} else echo $result->marca?> 
                                Modelo <?php echo $result->modelo?> 
                                - Nro. Série: <?php echo $result->nronumber?>
                                </span></td>
                              </tr>
                            </table>

                            <table class="table">
                                <hr style="margin: 0">
                                <span style="font-size: 10px;"><strong>Detalhes:</strong> 
                                <?php if($result->senha != null && $result->senha != "selecione") echo "Senha: ".$result->senha." | "?> 
                                <?php if($result->backup != null && $result->backup != "selecione") echo "Backup: ".$result->backup." | "?>
                                <?php if($result->cabos != null && $result->cabos != "selecione" && $result->cabos != "Sim") {echo "Acessórios: ".$result->cabos;} else {echo "Acessórios: ".$result->descricaoCabos;}?>
                                </span>
                            <?php if($result->defeito != null){?>
                                <hr style="margin: 0">
                                <span style="font-size: 10px;"><strong>Defeito relatado:</strong> <?php echo $result->defeito?></span>
                            <?php }?>

                            <?php if($result->descricaoProduto != null){?>
                                <hr style="margin: 0">
                                <span style="font-size: 10px;"><strong>Descrição do serviço:</strong> <?php echo $result->descricaoProduto?></span>
                            <?php }?>

                            <?php if($result->laudoTecnico != null){?>
                                <hr style="margin: 0">
                                <span style="font-size: 10px;"><strong>Laudo Técnico:</strong> <?php echo $result->laudoTecnico?></span>
                            <?php }?>

                            <?php if($result->observacoes != null){?>
                                <hr style="margin: 0">
                                <span style="font-size: 10px;"><strong>Observações:</strong> <?php echo $result->observacoes?></span>
                            <?php }?>
                                <hr style="margin: 0">
                            </table>

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
                                            echo '<td><span style="font-size: 10px;">'.$p->descricao.'<span></td>';
                                            echo '<td><span style="font-size: 10px;">'.$p->quantidade.'<span></td>';
                                            
                                            echo '<td><span style="font-size: 10px;">R$ '.number_format($p->subTotal,2,',','.').'</span></td>';
                                            echo '</tr>';
                                        }?>

                                        <tr>
                                            <td colspan="2" style="text-align: right"><span style="font-size: 10px;"><strong>Total:</strong></span></td>
                                            <td><span style="font-size: 10px;"><strong>R$ <?php echo number_format($totalProdutos,2,',','.');?></strong></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php }?>
                            
                            <?php if($servicos != null){?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; width: 80%;">Descrição</th>
                                            <th>Sub-total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    setlocale(LC_MONETARY, 'en_US');
                                    foreach ($servicos as $s) {
                                        $nome = $s->nome;
                                        $preco = $s->preco;
                                        $totalServico = $totalServico + $preco;
                                        echo '<tr>';
                                        echo '<td style="padding: 2px 10px;"><span style="font-size: 10px;">'.$nome.'</span></td>';
                                        echo '<td style="padding: 2px 10px; text-align: right"><span style="font-size: 10px;">R$ '.number_format($preco, 2, ',', '.').'</span></td>';
                                        echo '</tr>';
                                    }?>

                                    <tr>
                                        <td colspan="1" style="text-align: right; padding: 2px 10px;"><span style="font-size: 11px;"><strong>Total:</strong></span></td>
                                        <td style="text-align: right; padding: 2px 10px;"><span style="font-size: 11px;"><strong>R$ <?php  echo number_format($totalServico, 2, ',', '.');?></strong></span></td>
                                    </tr>
                                    </tbody>
                                </table>
                            <?php }?>
                            <table class="table" style="margin-bottom: 0;">
                                <br>
                                <tr>
                                    <td style="width: 50%; padding: 0 5px 0 0; text-align: center; border-top: 0;">
                                        <hr style="border-width: 2px; margin-bottom: 5px;">
                                        <span style="font-size: 10px;"><strong><?php echo $result->nome ?></strong></span>
                                    </td>
                                    <td style="width: 50%; padding: 0 0 0 5px; text-align: center; border-top: 0;">
                                        <hr style="border-width: 2px; margin-bottom: 5px;">
                                        <span style="font-size: 10px;"><strong><?php echo $result->nomeCliente?></strong></span>
                                    </td>
                                </tr>
                            </table>
                            <div>
                                <p style="font-size: 10px; line-height: 12px;"><small><b>Obs:</b>&nbsp;PAGAMENTO: Somente em dinheiro. ORÇAMENTO: Em caso de desistência após a realização do diagnóstico será cobrado a taxa de R$ 50,00. CUSTÓDIA: Na ausência de comunicação por um prazo superior à 30 dias os equipamentos serão enviados para nosso depósito sendo necessário agendar data para a retirada dos mesmos.</small></p>
                            </div>
                        </div><!-- invoice-content -->
                    </page>
                    <hr style="margin-top: 5px; margin-bottom: 5px; border-style: dashed;">
                    <!-- via cliente -->
                    <page size="A2" id="osCliente">
                    </page>
                </page>
            </div><!-- widget-content - printOs2 end-->

        </div><!-- class="widget-box" -->
    </div><!-- class="span12" -->
</div><!-- class="row-fluid" -->

<script type="text/javascript">
    $(document).ready(function(){
        $( "#osLoja" ).clone().appendTo( "#osCliente" );

        $(".linkCliente").click(function(){
            window.location.href = $("#urlCliente").val();
        })
        $("#imprimir").click(function(e){
            e.preventDefault();
            PrintElem('#printOs');
        })
        $("#imprimir2").click(function(e){
            e.preventDefault();
            PrintElem('#printOs2');
        })

        function PrintElem(elem)
        {
            var html = $(elem).html();
            setTimeout(Popup, 100, html);
        }

        function Popup(data)
        {
            var mywindow = window.open('', 'ComputadorOS', 'height=600,width=800');
            mywindow.document.write('<html><head><title>Computador.com - OS</title>');
            mywindow.document.write("<link rel='stylesheet' href='<?php echo base_url();?>assets/css/bootstrap.min.css' />");
            mywindow.document.write("<link rel='stylesheet' href='<?php echo base_url();?>assets/css/bootstrap-responsive.min.css' />");
            mywindow.document.write("<link rel='stylesheet' href='<?php echo base_url();?>assets/css/matrix-style.css' />");
            mywindow.document.write("<link rel='stylesheet' href='<?php echo base_url();?>assets/css/matrix-media.css' />");
            mywindow.document.write("<link rel='stylesheet' href='<?php echo base_url();?>assets/css/ui-print.css' media='print' />");
            mywindow.document.write("</head><body style='background-color: white;'>");
            mywindow.document.write(data);
            mywindow.document.write("</body></html>");
            setTimeout(delayPrint, 1000, mywindow);
        }

        function delayPrint(mywindow){
            mywindow.print();
            mywindow.close();
            return true;
        }

        if ( $("#cabosValue").val() == "Sim" || $("#cabosValue").val() == "Não" ) {
          $( ".descricaoCabos" ).show();
        } else {
          $( ".descricaoCabos" ).hide();
        }

        if ( $("#chkTotalProdutos").val() != "0" || $("#chkTotalServico").val() != "0" ) {
          $( "#totalOs" ).show();
        } else {
          $( "#totalOs" ).hide();
        }

        $(document).on('click', '#enviarWhatsApp', function(event) {
           if ( $(this).attr("disabled") ) {
            console.log("bt off");
            return false;
           } else {
            var urlAtual = $("#urlAtual").val();
            var id = $("#sendOsId").val();
            var nome = $("#sendNomeCliente").val();

            $.ajax({
                type: "POST",
                url: "<?php echo base_url();?>index.php/os/enviarWhatsApp",
                data:{id:id, nome:nome},
                dataType: 'json',
                ContentType:"application/json",
                success: function(data){
                    if(data.result == true){
                        $("#enviarWhatsApp").css("background-color","green").attr("disabled","disabled");
                    }else{
                        $("#enviarWhatsApp").css("background-color","red");
                    }
                }
            });
           };

        });

        $(document).on('click', '#enviarEmail', function(event) {
           event.preventDefault();

           if ( $(this).attr("disabled") ) {
            console.log("bt off");
            return false;
           } else {
            var urlAtual = $("#urlAtual").val();
            var id = $("#sendOsId").val();
            var email = $("#sendEmailCliente").val();
            var nome = $("#sendNomeCliente").val();
            var subject = "[#"+id+"] - Protocolo de Atendimento Atualizado";
            var emailBody = "Prezado(a) "+nome+"<br> Informamos que houve uma alteração de status no seu atendimento.<br>Entre em contato para maiores informações. <br><br> Atenciosamente, <br> Computadorpc.com<br>(85) 3268.1107<br>(85) 98901.4810<br>(85) 99664.3815";
            var emailBody = decodeURIComponent(emailBody);

            $.ajax({
                type: "POST",
                url: "<?php echo base_url();?>index.php/os/enviarEmail",
                data:{id:id, email:email, nome:nome, subject:subject, emailBody:emailBody},
                dataType: 'json',
                ContentType:"application/json",
                success: function(data){
                    if(data.result == true){
                        $("#enviarEmail").css("background-color","green").attr("disabled","disabled");
                    }else{
                        $("#enviarEmail").css("background-color","red");
                    }
                }
            });
           };

        });
    });
</script>