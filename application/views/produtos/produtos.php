<?php if($this->permission->checkPermission($this->session->userdata('permissao'),'aProduto')){ ?>
    <a href="<?php echo base_url();?>index.php/produtos/adicionar" class="btn btn-success"><i class="icon-plus icon-white"></i> Adicionar Produto</a>
<?php } ?>

<?php

if(!$results){?>
	<div class="widget-box">
     <div class="widget-title">
        <span class="icon">
            <i class="icon-barcode"></i>
         </span>
        <h5>Produtos</h5>

     </div>

<div class="widget-content nopadding">


<table class="table table-bordered ">
    <thead>
        <tr>
            <th>#</th>
            <th>Nome</th>
            <th>Estoque</th>
            <th>Preço</th>
            <th></th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td colspan="5">Nenhum Produto Cadastrado</td>
        </tr>
    </tbody>
</table>
</div>
</div>

<?php } else{?>

<div class="widget-box">
     <div class="widget-title">
        <span class="icon">
            <i class="icon-barcode"></i>
         </span>
        <h5>Produtos</h5>

     </div>

<div class="widget-content nopadding">


<table class="table table-bordered ">
    <thead>
        <tr style="backgroud-color: #2D335B">
            <th>#</th>
            <th>Nome</th>
            <th>Estoque</th>
            <th>Preço</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $r) {
            echo '<tr>';
            echo '<td>'.$r->idProdutos.'</td>';
            echo '<td>'.$r->descricao.'</td>';
            echo '<td>'.$r->estoque.'</td>';
            echo '<td>'.number_format($r->precoVenda,2,',','.').'</td>';
            
            echo '<td>';
            if($this->permission->checkPermission($this->session->userdata('permissao'),'vProduto')){
                echo '<a style="margin-right: 1%" href="'.base_url().'index.php/produtos/visualizar/'.$r->idProdutos.'" class="btn tip-top" title="Visualizar Produto"><i class="icon-eye-open"></i></a>  '; 
            }
            if($this->permission->checkPermission($this->session->userdata('permissao'),'eProduto')){
                echo '<a style="margin-right: 1%" href="'.base_url().'index.php/produtos/editar/'.$r->idProdutos.'" class="btn btn-info tip-top" title="Editar Produto"><i class="icon-pencil icon-white"></i></a>'; 
            }
            if($this->permission->checkPermission($this->session->userdata('permissao'),'dProduto')){
                echo '<a href="#modal-excluir" role="button" data-toggle="modal" produto="'.$r->idProdutos.'" class="btn btn-danger tip-top" title="Excluir Produto"><i class="icon-remove icon-white"></i></a>'; 
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
  <form action="<?php echo base_url() ?>index.php/produtos/excluir" method="post" >
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 id="myModalLabel">Excluir Produto</h5>
  </div>
  <div class="modal-body">
    <input type="hidden" id="idProduto" name="id" value="" />
    <h5 style="text-align: center">Deseja realmente excluir este produto?</h5>
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
        
        var produto = $(this).attr('produto');
        $('#idProduto').val(produto);

    });

});

</script>