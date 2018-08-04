<?php if($this->permission->checkPermission($this->session->userdata('permissao'),'aOs')){ ?>
    <a href="<?php echo base_url()?>index.php/historico/adicionar" class="btn btn-success"><i class="icon-plus icon-white"></i> Adicionar Histórico</a>
<?php } ?>

<?php

if(!$results){?>

    <div class="widget-box">
     <div class="widget-title">
        <span class="icon">
            <i class="icon-shopping-cart"></i>
         </span>
        <h5>Histórico</h5>

     </div>

<div class="widget-content nopadding">


<table class="table table-bordered ">
    <thead>
        <tr style="backgroud-color: #2D335B">
            <th>#</th>
            <th>Data</th>
            <th>Responsável</th>
            <th>Status</th>
            <th>Canal</th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td colspan="5">Nenhum Histórico Cadastrado</td>
        </tr>
    </tbody>
</table>
</div>
</div>



<?php }
else{ ?>

<div class="widget-box">
     <div class="widget-title">
        <span class="icon">
            <i class="icon-wrench"></i>
         </span>
        <h5>Histórico</h5>

     </div>

<div class="widget-content nopadding">


<table class="table table-bordered ">
    <thead>
        <tr style="backgroud-color: #2D335B">
            <th>#</th>
            <th>Data</th>
            <th>Responsável</th>
            <th>Status</th>
            <th>Canal</th>
            <th>Descrição</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $r) {
            $data = date(('d/m/Y'),strtotime($r->data));
            echo '<tr>';
            echo '<td style="text-align:center"><a style="cursor:pointer;text-decoration:underline;" href="' . base_url() . 'index.php/os/editar/' . $r->idOs . '" title="Editar OS">' . $r->idOs . '</a></td>'; 
            echo '<td>'.$data.'</td>';
            echo '<td>'.$r->responsavel.'</td>';
            echo '<td>'.$r->status.'</td>';
            echo '<td>'.$r->canal.'</td>';
            echo '<td>'.$r->comentarios.'</td>';
            echo '<td>';
            if($this->permission->checkPermission($this->session->userdata('permissao'),'eOs')){
                echo '<a style="margin-right: 1%" href="'.base_url().'index.php/historico/editar/'.$r->idHistorico.'" class="btn btn-info tip-top" title="Editar Histórico"><i class="icon-pencil icon-white"></i></a>'; 
            }
            if($this->permission->checkPermission($this->session->userdata('permissao'),'dOs')){
                echo '<a href="#modal-excluir" role="button" data-toggle="modal" historico="'.$r->idHistorico.'" class="btn btn-danger tip-top" title="Excluir Histórico"><i class="icon-remove icon-white"></i></a>  '; 
            }
            echo '</td>';
            echo '</tr>';
        }?>
        <tr>
            
        </tr>
    </tbody>
</table>
</div>
</div>
    
        



<?php echo $this->pagination->create_links();}?>


<!-- Modal -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <form action="<?php echo base_url() ?>index.php/historico/excluir" method="post" >
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 id="myModalLabel">Excluir Histórico</h5>
  </div>
  <div class="modal-body">
    <input type="hidden" id="idHistorico" name="idHistorico" value="" />
    <h5 style="text-align: center">Deseja realmente excluir este histórico?</h5>
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
        
        var historico = $(this).attr('historico');
        $('#idHistorico').val(historico);

    });

});

</script>