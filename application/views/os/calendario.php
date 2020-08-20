<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/dist/excanvas.min.js"></script><![endif]-->

<div class="row-fluid" style="margin-top: 0">
    <div class="span12" style="margin-left: 0">
            <label class="checkbox inline" style="color: white;">
                <input id="atrasadas" checked="" name="atrasadas" class="marcar" type="checkbox" value="1">
                <span class="lbl" style="background-color: rgb(255, 51, 0); border-radius: 3px; font-size: .85em; padding: 1px 5px;"> Atrasadas <i></i></span>
            </label>
            <label class="checkbox inline" style="color: white;">
                <input id="aguardandoCliente" checked="" name="aguardandoCliente" class="marcar" type="checkbox" value="1">
                <span class="lbl" style="background-color: rgb(255, 153, 0); border-radius: 3px; font-size: .85em; padding: 1px 5px;"> Aguardando Cliente <i></i></span>
            </label>
            <label class="checkbox inline" style="color: white;">
                <input id="aguardandoPeca" checked="" name="aguardandoPeca" class="marcar" type="checkbox" value="1">
                <span class="lbl" style="background-color: rgb(255, 219, 21); border-radius: 3px; font-size: .85em; padding: 1px 5px;"> Aguardando Peça <i></i></span>
            </label>
            <label class="checkbox inline" style="color: white;">
                <input id="orcamento" checked="" name="orcamento" class="marcar" type="checkbox" value="1">
                <span class="lbl" style="background-color: rgb(102, 0, 204); border-radius: 3px; font-size: .85em; padding: 1px 5px;"> Orçamento <i></i></span>
            </label>
            <label class="checkbox inline" style="color: white;">
                <input id="aprovado" checked="" name="aprovado" class="marcar" type="checkbox" value="1">
                <span class="lbl" style="background-color: rgb(153, 204, 0); border-radius: 3px; font-size: .85em; padding: 1px 5px;"> Aprovado <i></i></span>
            </label>
            <label class="checkbox inline" style="color: white;">
                <input id="andamento" checked="" name="andamento" class="marcar" type="checkbox" value="1">
                <span class="lbl" style="background-color: rgb(51, 204, 51); border-radius: 3px; font-size: .85em; padding: 1px 5px;"> Em Andamento <i></i></span>
            </label>
            <label class="checkbox inline" style="color: white;">
                <input id="finalizado" checked="" name="finalizado" class="marcar" type="checkbox" value="1">
                <span class="lbl" style="background-color: rgb(51, 102, 255); border-radius: 3px; font-size: .85em; padding: 1px 5px;"> Finalizado <i></i></span>
            </label>
    </div>
    <div class="span12" style="margin-left: 0">
        <div class="widget-box">
            <div id='calendar'></div>
        </div>
    </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {    

    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,basicWeek,basicDay'
        },
        defaultView: 'month',
        editable: true,
        events: [
        <?php
        $i = 0;
        $len = count($ordensVencendo);

        $now = new DateTime();
        $now = $now->format('Y-m-d');

        foreach ($ordensVencendo as $o) {
            if ($o->dataFinal <= $now && $o->status != "Finalizado" && $o->status != "Aguardando Cliente" && $o->status != "Aguardando Peça") {
                $color = " rgb(255, 51, 0)"; //vermelho
            } elseif ($o->status == "Aguardando Cliente") {
                $color = " rgb(255, 153, 0)"; //laranja
            } elseif ($o->status == "Aguardando Peça") {
                $color = " rgb(255, 219, 21)"; //amarelo
            } elseif ($o->status == "Orçamento") {
                $color = " rgb(102, 0, 204)"; //roxo
            } elseif ($o->status == "Aprovado") {
                $color = " rgb(153, 204, 0)"; //verde pastel
            } elseif ($o->status == "Em Andamento") {
                $color = " rgb(51, 204, 51)"; //verde
            } elseif ($o->status == "Finalizado") {
                $color = " rgb(51, 102, 255)"; //azul
            } elseif ($o->status == "Cancelado") {
                $color = " rgb(204, 204, 204)"; //cinza
            } else {
                $color = "#3366ff"; //azul
            }

            //$color = "#ff3300"; //vermelho rgb(255, 51, 0)
            //$color = "#e6e600"; //amarelo
            //$color = "#33cc33"; //verde
            //$color = "#3366ff"; //azul
            //$color = "#ffcc00"; //laranja
            //$color = "#cccccc"; //cinza

            if ($i >= 0) {
            echo "{color:'".$color."', title:'".$o->idOs." - ".$o->nomeCliente."', status:'".$o->status."', start:'".$o->dataFinal."', url:'".base_url().'index.php/os/visualizar/'.$o->idOs."'},";
            } else if ($i == $len - 1) {
            echo "{color:'".$color."', title:'".$o->idOs." - ".$o->nomeCliente."', status:'".$o->status."', start:'".$o->dataFinal."', url:'".base_url().'index.php/os/visualizar/'.$o->idOs."'}";
            }
        }
        ?>
        ],
        eventRender: function(event, element) {
            element.qtip({
                content: event.status
            });
        }

    });

    function qtyOs(){
        var qtyAtrasadas = $("#calendar a[style*='background-color: rgb(255, 51, 0)']").length;
        var qtyAguardandoCliente = $("#calendar a[style*='background-color: rgb(255, 153, 0)']").length;
        var qtyAguardandoPeca = $("#calendar a[style*='background-color: rgb(255, 219, 21)']").length;
        var qtyOrcamento = $("#calendar a[style*='background-color: rgb(102, 0, 204)']").length;
        var qtyAprovado = $("#calendar a[style*='background-color: rgb(153, 204, 0)']").length;
        var qtyAndamento = $("#calendar a[style*='background-color: rgb(51, 204, 51)']").length;
        var qtyFinalizado = $("#calendar a[style*='background-color: rgb(51, 102, 255)']").length;

        $("#atrasadas").next("span").children("i").text("("+qtyAtrasadas+")");
        $("#aguardandoCliente").next("span").children("i").text("("+qtyAguardandoCliente+")");
        $("#aguardandoPeca").next("span").children("i").text("("+qtyAguardandoPeca+")");
        $("#orcamento").next("span").children("i").text("("+qtyOrcamento+")");
        $("#aprovado").next("span").children("i").text("("+qtyAprovado+")");
        $("#andamento").next("span").children("i").text("("+qtyAndamento+")");
        $("#finalizado").next("span").children("i").text("("+qtyFinalizado+")");
    }

    qtyOs();

    $('#calendar').bind("DOMSubtreeModified", function(){
        qtyOs();
    });

    $('#atrasadas').change(function(){
      if($(this).prop("checked")) {
        $("#calendar a[style*='background-color: rgb(255, 51, 0)']").show();
      } else {
        $("#calendar a[style*='background-color: rgb(255, 51, 0)']").hide();
      }
    });
    $('#aguardandoCliente').change(function(){
      if($(this).prop("checked")) {
        $("#calendar a[style*='background-color: rgb(255, 153, 0)']").show();
      } else {
        $("#calendar a[style*='background-color: rgb(255, 153, 0)']").hide();
      }
    });
    $('#aguardandoPeca').change(function(){
      if($(this).prop("checked")) {
        $("#calendar a[style*='background-color: rgb(255, 219, 21)']").show();
      } else {
        $("#calendar a[style*='background-color: rgb(255, 219, 21)']").hide();
      }
    });
    $('#orcamento').change(function(){
      if($(this).prop("checked")) {
        $("#calendar a[style*='background-color: rgb(102, 0, 204)']").show();
      } else {
        $("#calendar a[style*='background-color: rgb(102, 0, 204)']").hide();
      }
    });
    $('#aprovado').change(function(){
      if($(this).prop("checked")) {
        $("#calendar a[style*='background-color: rgb(153, 204, 0)']").show();
      } else {
        $("#calendar a[style*='background-color: rgb(153, 204, 0)']").hide();
      }
    });
    $('#andamento').change(function(){
      if($(this).prop("checked")) {
        $("#calendar a[style*='background-color: rgb(51, 204, 51)']").show();
      } else {
        $("#calendar a[style*='background-color: rgb(51, 204, 51)']").hide();
      }
    });
    $('#finalizado').change(function(){
      if($(this).prop("checked")) {
        $("#calendar a[style*='background-color: rgb(51, 102, 255)']").show();
      } else {
        $("#calendar a[style*='background-color: rgb(51, 102, 255)']").hide();
      }
    });
    $('#cancelado').change(function(){
      if($(this).prop("checked")) {
        $("#calendar a[style*='background-color: rgb(204, 204, 204)']").show();
      } else {
        $("#calendar a[style*='background-color: rgb(204, 204, 204)']").hide();
      }
    });
});
</script>