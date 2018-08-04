<?php if($this->permission->checkPermission($this->session->userdata('permissao'),'aCompra')){ ?>
    <a href="<?php echo base_url()?>index.php/compras/adicionar" class="btn btn-success"><i class="icon-plus icon-white"></i> Adicionar Compra</a>
<?php } ?>

<?php

if(!$results){?>

    <div class="widget-box">
     <div class="widget-title">
        <span class="icon">
            <i class="icon-shopping-cart"></i>
         </span>
        <h5>Compras</h5>

     </div>

<div class="widget-content nopadding">


<table class="table table-bordered ">
    <thead>
        <tr style="backgroud-color: #2D335B">
            <th>#</th>
            <th>Fornecedor</th>
            <th>Descrição</th>
            <th>Preço</th>
            <th></th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td colspan="5">Nenhuma Compra Cadastrada</td>
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
        <h5>Compras</h5>

     </div>

<div class="widget-content nopadding">


<table class="table table-bordered ">
    <thead>
        <tr style="backgroud-color: #2D335B">
            <th>#</th>
            <th>OS</th>
            <th>Fornecedor</th>
            <th>Descrição</th>
            <th>Preço</th>
            <th>Rastreio</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $r) {
            echo '<tr>';
            echo '<td>'.$r->idCompras.'</td>';
            echo '<td style="text-align:center"><a style="cursor:pointer;text-decoration:underline;" href="' . base_url() . 'index.php/os/editar/' . $r->idOs . '" title="Editar OS">' . $r->idOs . '</a></td>'; 
            echo '<td>'.$r->fornecedor.'</td>';
            echo '<td>'.$r->descricao.'</td>';
            echo '<td>'.number_format($r->valor,2,',','.').'</td>';
            if($r->rastreio){
            echo '<td style="text-align:center"><a target="_blank" href="http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI='.$r->rastreio.'" class="tip-bottom" data-original-title="Pesquisar"><i class="icon-truck icon-white"></i></a></td>';
            } else {
                echo "<td></td>";
            }
            echo '<td>';
            if($this->permission->checkPermission($this->session->userdata('permissao'),'eCompra')){
                echo '<a style="margin-right: 1%" href="'.base_url().'index.php/compras/editar/'.$r->idCompras.'" class="btn btn-info tip-top" title="Editar Compra"><i class="icon-pencil icon-white"></i></a>'; 
            }
            if($this->permission->checkPermission($this->session->userdata('permissao'),'dCompra')){
                echo '<a href="#modal-excluir" role="button" data-toggle="modal" compra="'.$r->idCompras.'" class="btn btn-danger tip-top" title="Excluir Compra"><i class="icon-remove icon-white"></i></a>  '; 
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
  <form action="<?php echo base_url() ?>index.php/compras/excluir" method="post" >
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 id="myModalLabel">Excluir Compra</h5>
  </div>
  <div class="modal-body">
    <input type="hidden" id="idCompra" name="id" value="" />
    <h5 style="text-align: center">Deseja realmente excluir esta compra?</h5>
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
        
        var compra = $(this).attr('compra');
        $('#idCompra').val(compra);

    });

});

</script>