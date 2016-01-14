<link rel="stylesheet" href="<?php echo base_url();?>js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url()?>js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>

<div class="span12" style="margin-left: 0">
    <form method="get" action="<?php echo current_url(); ?>">
        <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'aArquivo')){ ?>
             <div class="span3">
                <a href="<?php echo base_url();?>index.php/arquivos/adicionar" class="btn btn-success span12"><i class="icon-plus icon-white"></i> Adicionar Arquivo</a>
            </div>  
        <?php } ?>
        
        <div class="span5">
            <input type="text" name="pesquisa"  id="pesquisa"  placeholder="Digite o nome do documento para pesquisar" class="span12" value="<?php echo $this->input->get('pesquisa'); ?>" >        
        </div>
        <div class="span3">
            <input type="text" name="data"  id="data"  placeholder="Data de" class="span6 datepicker" value="<?php echo $this->input->get('data'); ?>">
            <input type="text" name="data2"  id="data2"  placeholder="Data até" class="span6 datepicker" value="<?php echo $this->input->get('data2'); ?>" >                
        </div>
        <div class="span1">
            <button class="span12 btn"> <i class="icon-search"></i> </button>
        </div>
    </form>
</div>

<?php
if(!$results){?>

<div class="span12" style="margin-left: 0">
        <div class="widget-box">
        <div class="widget-title">
            <span class="icon">
                <i class="icon-hdd"></i>
            </span>
            <h5>Arquivos</h5>

        </div>

        <div class="widget-content nopadding">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Documento</th>
                        <th>Data</th>
                        <th>Tamanho</th>
                        <th>Extensão</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5">Nenhum Arquivo Encontrado</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php }else{ ?>

<div class="span12" style="margin-left: 0">
    <div class="widget-box">
         <div class="widget-title">
            <span class="icon">
                <i class="icon-hdd"></i>
             </span>
            <h5>Arquivos</h5>

         </div>

    <div class="widget-content nopadding">


    <table class="table table-bordered ">
        <thead>
            <tr>
                <th>#</th>
                <th>Documento</th>
                <th>Data</th>
                <th>Tamanho</th>
                <th>Extensão</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $r) {
                echo '<tr>';
                echo '<td>'.$r->idDocumentos.'</td>';
                echo '<td>'.$r->documento.'</td>';
                echo '<td>'.date('d/m/Y',strtotime($r->cadastro)).'</td>';
                echo '<td>'.$r->tamanho.' KB</td>';
                echo '<td>'.$r->tipo.'</td>';
                echo '<td>';
                    if($this->permission->checkPermission($this->session->userdata('permissao'),'vArquivo')){
                        echo '<a class="btn btn-inverse tip-top" style="margin-right: 1%" target="_blank" href="'.$r->url.'" class="btn tip-top" title="Imprimir"><i class="icon-print"></i></a>'; 
                    }
                    if($this->permission->checkPermission($this->session->userdata('permissao'),'vArquivo')){
                        echo '<a href="'.base_url().'index.php/arquivos/download/'.$r->idDocumentos.'" class="btn tip-top" style="margin-right: 1%" title="Download"><i class="icon-download-alt"></i></a>'; 
                    }
                    if($this->permission->checkPermission($this->session->userdata('permissao'),'eArquivo')){ 
                        echo  '<a href="'.base_url().'index.php/arquivos/editar/'.$r->idDocumentos.'" class="btn btn-info tip-top" style="margin-right: 1%" title="Editar"><i class="icon-pencil icon-white"></i></a>';
                    }
                    if($this->permission->checkPermission($this->session->userdata('permissao'),'dArquivo')){
                         echo '<a href="#modal-excluir" style="margin-right: 1%" role="button" data-toggle="modal" arquivo="'.$r->idDocumentos.'" class="btn btn-danger tip-top" title="Excluir Arquivo"><i class="icon-remove icon-white"></i></a>';
                    }
                echo  '</td>';
                echo '</tr>';
            }?>
            <tr>
                
            </tr>
        </tbody>
    </table>
    </div>
    </div>

</div>
<?php echo $this->pagination->create_links();}?>



 
<!-- Modal -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <form action="<?php echo base_url() ?>index.php/arquivos/excluir" method="post" >
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h5 id="myModalLabel">Excluir Arquivo</h5>
  </div>
  <div class="modal-body">
    <input type="hidden" id="idDocumento" name="id" value="" />
    <h5 style="text-align: center">Deseja realmente excluir este arquivo?</h5>
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
        
        var arquivo = $(this).attr('arquivo');
        $('#idDocumento').val(arquivo);

   });

   $(".datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });
});

</script>
