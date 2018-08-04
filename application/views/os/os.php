<?php if($this->permission->checkPermission($this->session->userdata('permissao'),'aOs')){ ?>

    <a href="<?php echo base_url();?>index.php/os/adicionar" class="btn btn-success"><i class="icon-plus icon-white"></i> Adicionar OS</a>

<?php } ?>



<?php



if(!$results){?>

	<div class="widget-box">

     <div class="widget-title">

        <span class="icon">

            <i class="icon-tags"></i>

         </span>

        <h5>Ordens de Serviço</h5>



     </div>



<div class="widget-content nopadding">





<table class="table table-bordered ">

    <thead>

        <tr style="backgroud-color: #2D335B">

            <th>#</th>

            <th>Cliente</th>

            <th>Data Inicial</th>

            <th>Data Final</th>

            <th>Status</th>

            <th></th>

        </tr>

    </thead>

    <tbody>



        <tr>

            <td colspan="6">Nenhuma OS Cadastrada</td>

        </tr>

    </tbody>

</table>

</div>

</div>

<?php } else{?>





<div class="widget-box">

    <div class="widget-title">
        <span class="icon">
            <i class="icon-tags"></i>
        </span>
        <h5>Ordens de Serviço</h5>
    </div>

    <div class="widget-content nopadding">

        <table class="table table-bordered ">

            <thead>
                <tr style="backgroud-color: #2D335B">
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Data Inicial</th>
                    <th>Data Final</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($results as $r) {

                    $dataInicial = date(('d/m/Y'),strtotime($r->dataInicial));
                    $dataFinal = date(('d/m/Y'),strtotime($r->dataFinal));
                    $dataAtual = date('d/m/Y');
                    $status = $r->status;

                    if ($status == "Faturado") {
                        echo '<tr class="success">';
                    } elseif ($dataFinal < $dataAtual && $status != "Faturado") {
                        echo '<tr class="danger">';
                    } else {
                        echo '<tr>';
                    }

                    echo '<td>'.$r->idOs.'</td>';
                    echo '<td>'.$r->nomeCliente.'</td>';
                    echo '<td>'.$dataInicial.'</td>';
                    echo '<td>'.$dataFinal.'</td>';
                    echo '<td>'.$status.'</td>';
                    echo '<td>';

                    if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){
                        echo '<a style="margin-right: 1%" href="'.base_url().'index.php/os/visualizar/'.$r->idOs.'" class="btn tip-top" title="Ver mais detalhes"><i class="icon-eye-open"></i></a>'; 
                    }

                    if($this->permission->checkPermission($this->session->userdata('permissao'),'eOs')){
                        echo '<a style="margin-right: 1%" href="'.base_url().'index.php/os/editar/'.$r->idOs.'" class="btn btn-info tip-top" title="Editar OS"><i class="icon-pencil icon-white"></i></a>'; 
                    }

                    if($this->permission->checkPermission($this->session->userdata('permissao'),'dOs')){
                        echo '<a href="#modal-excluir" role="button" data-toggle="modal" os="'.$r->idOs.'" class="btn btn-danger tip-top" title="Excluir OS"><i class="icon-remove icon-white"></i></a>  '; 
                    }

                    echo  '</td>';
                    echo '</tr>';

                }?>

            </tbody>
        </table>

    </div>
</div>

	

<?php echo $this->pagination->create_links();}?>





<!-- Modal -->

<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

  <form action="<?php echo base_url() ?>index.php/os/excluir" method="post" >

  <div class="modal-header">

    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

    <h5 id="myModalLabel">Excluir OS</h5>

  </div>

  <div class="modal-body">

    <input type="hidden" id="idOs" name="id" value="" />

    <h5 style="text-align: center">Deseja realmente excluir esta OS?</h5>

  </div>

  <div class="modal-footer">

    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>

    <button class="btn btn-danger">Excluir</button>

  </div>

  </form>

</div>













<script type="text/javascript">

$(document).ready(function(){





   $(document).on('click', 'a', function(event) {

        

        var os = $(this).attr('os');

        $('#idOs').val(os);



    });



});



</script>