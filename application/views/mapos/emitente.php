
<?php if(!isset($dados) || $dados == null) {?>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-align-justify"></i>
                </span>
                <h5>Dados do Emitente</h5>
            </div>
            <div class="widget-content ">
                <div class="alert alert-danger">Nenhum dado foi cadastrado até o momento. Essas informações 
                estarão disponíveis na tela de impressão de OS.</div>
                <a href="#modalCadastrar" data-toggle="modal" role="button" class="btn btn-success">Cadastrar Dados</a>
            </div>
        </div>    
        
    </div>
</div>   


<div id="modalCadastrar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <form action="<?php echo base_url(); ?>index.php/mapos/cadastrarEmitente" id="formCadastrar" enctype="multipart/form-data" method="post" class="form-horizontal" >
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">MapOS - Cadastrar Dados do Emitente</h3>
  </div>
  <div class="modal-body">
        
        
                    <div class="control-group">
                        <label for="nome" class="control-label">Razão Social<span class="required">*</span></label>
                        <div class="controls">
                            <input id="nome" type="text" name="nome" value=""  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="cnpj" class="control-label"><span class="required">CNPJ*</span></label>
                        <div class="controls">
                            <input class="" type="text" name="cnpj" value=""  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">IE*</span></label>
                        <div class="controls">
                            <input type="text" name="ie" value=""  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">Logradouro*</span></label>
                        <div class="controls">
                            <input type="text" name="logradouro" value=""  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">Número*</span></label>
                        <div class="controls">
                            <input type="text" name="numero" value=""  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">Bairro*</span></label>
                        <div class="controls">
                            <input type="text" name="bairro" value=""  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">Cidade*</span></label>
                        <div class="controls">
                            <input type="text" name="cidade" value=""  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">UF*</span></label>
                        <div class="controls">
                            <input type="text" name="uf" value=""  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">Telefone*</span></label>
                        <div class="controls">
                            <input type="text" name="telefone" value=""  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span>Celular</span></label>
                        <div class="controls">
                            <input type="text" name="celular" value=""  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span>WhatsApp</span></label>
                        <div class="controls">
                            <input type="text" name="whatsapp" value=""  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">E-mail*</span></label>
                        <div class="controls">
                            <input type="text" name="email" value="" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="logo" class="control-label"><span class="required">Logomarca*</span></label>
                        <div class="controls">
                            <input type="file" name="userfile" value="" />
                        </div>
                    </div>
               

  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true" id="btnCancelExcluir">Cancelar</button>
    <button class="btn btn-success">Cadastrar</button>
  </div>
  </form>
</div>

<?php } else { ?>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-align-justify"></i>
                </span>
                <h5>Dados do Emitente</h5>
            </div>
            <div class="widget-content ">
            <div class="alert alert-info">Os dados abaixo serão utilizados no cabeçalho das telas de impressão.</div>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td style="width: 25%"><img src=" <?php echo $dados[0]->url_logo; ?> "></td>
                            <td> <span style="font-size: 20px; "> <?php echo $dados[0]->nome; ?> </span> </br><span><?php echo $dados[0]->cnpj; ?> </br> <?php echo $dados[0]->rua.', '.$dados[0]->numero.', '.$dados[0]->bairro.' - '.$dados[0]->cidade.' - '.$dados[0]->uf; ?> </span> </br> <span> E-mail: <?php echo $dados[0]->email.' - Fone: '.$dados[0]->telefone.' - Celular: '.$dados[0]->celular.' - WhatsApp: '.$dados[0]->whatsapp; ?></span></td>
                        </tr>
                    </tbody>
                </table>

                <a href="#modalAlterar" data-toggle="modal" role="button" class="btn btn-primary">Alterar Dados</a>
                <a href="#modalLogo" data-toggle="modal" role="button" class="btn btn-inverse">Alterar Logo</a>
            </div>
        </div>
    </div>
</div>




<div id="modalAlterar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <form action="<?php echo base_url(); ?>index.php/mapos/editarEmitente" id="formAlterar" enctype="multipart/form-data" method="post" class="form-horizontal" >
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="">MapOS - Editar Dados do Emitente</h3>
  </div>
  <div class="modal-body">
        
        
                    <div class="control-group">
                        <label for="nome" class="control-label">Razão Social<span class="required">*</span></label>
                        <div class="controls">
                            <input id="nome" type="text" name="nome" value="<?php echo $dados[0]->nome; ?>"  />
                            <input id="nome" type="hidden" name="id" value="<?php echo $dados[0]->id; ?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="cnpj" class="control-label"><span class="required">CNPJ*</span></label>
                        <div class="controls">
                            <input class="" type="text" name="cnpj" value="<?php echo $dados[0]->cnpj; ?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">IE*</span></label>
                        <div class="controls">
                            <input type="text" name="ie" value="<?php echo $dados[0]->ie; ?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">Logradouro*</span></label>
                        <div class="controls">
                            <input type="text" name="logradouro" value="<?php echo $dados[0]->rua; ?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">Número*</span></label>
                        <div class="controls">
                            <input type="text" name="numero" value="<?php echo $dados[0]->numero; ?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">Bairro*</span></label>
                        <div class="controls">
                            <input type="text" name="bairro" value="<?php echo $dados[0]->bairro; ?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">Cidade*</span></label>
                        <div class="controls">
                            <input type="text" name="cidade" value="<?php echo $dados[0]->cidade; ?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">UF*</span></label>
                        <div class="controls">
                            <input type="text" name="uf" value="<?php echo $dados[0]->uf; ?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">Telefone*</span></label>
                        <div class="controls">
                            <input type="text" name="telefone" value="<?php echo $dados[0]->telefone; ?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span>Celular</span></label>
                        <div class="controls">
                            <input type="text" name="celular" value="<?php echo $dados[0]->celular; ?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span>WhatsApp</span></label>
                        <div class="controls">
                            <input type="text" name="whatsapp" value="<?php echo $dados[0]->whatsapp; ?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label"><span class="required">E-mail*</span></label>
                        <div class="controls">
                            <input type="text" name="email" value="<?php echo $dados[0]->email; ?>" />
                        </div>
                    </div>

    
               

  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true" id="btnCancelExcluir">Cancelar</button>
    <button class="btn btn-primary">Alterar</button>
  </div>
  </form>
</div>


<div id="modalLogo" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <form action="<?php echo base_url(); ?>index.php/mapos/editarLogo" id="formLogo" enctype="multipart/form-data" method="post" class="form-horizontal" >
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="">MapOS - Alterar Logomarca</h3>
  </div>
  <div class="modal-body">
         <div class="span12 alert alert-info">Selecione uma nova imagem da logomarca. Tamanho indicado (130 X 130).</div>          
         <div class="control-group">
            <label for="logo" class="control-label"><span class="required">Logomarca*</span></label>
            <div class="controls">
                <input type="file" name="userfile" value="" />
                <input id="nome" type="hidden" name="id" value="<?php echo $dados[0]->id; ?>"  />
            </div>
        </div>

  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true" id="btnCancelExcluir">Cancelar</button>
    <button class="btn btn-primary">Alterar</button>
  </div>
  </form>
</div>



<?php } ?>


<script type="text/javascript" src="<?php echo base_url()?>js/jquery.validate.js"></script>
<script type="text/javascript">
    
$(document).ready(function(){

    $("#formLogo").validate({
      rules:{
         userfile: {required:true}
      },
      messages:{
         userfile: {required: 'Campo Requerido.'}
      },

        errorClass: "help-inline",
        errorElement: "span",
        highlight:function(element, errorClass, validClass) {
            $(element).parents('.control-group').addClass('error');
            $(element).parents('.control-group').removeClass('success');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).parents('.control-group').removeClass('error');
            $(element).parents('.control-group').addClass('success');
        }
   });


    $("#formCadastrar").validate({
      rules:{
         userfile: {required:true},
         nome: {required:true},
         cnpj: {required:true},
         ie: {required:true},
         logradouro: {required:true},
         numero: {required:true},
         bairro: {required:true},
         cidade: {required:true},
         uf: {required:true},
         telefone: {required:true},
         email: {required:true}
      },
      messages:{
         userfile: {required: 'Campo Requerido.'},
         nome: {required: 'Campo Requerido.'},
         cnpj: {required: 'Campo Requerido.'},
         ie: {required: 'Campo Requerido.'},
         logradouro: {required: 'Campo Requerido.'},
         numero: {required:'Campo Requerido.'},
         bairro: {required:'Campo Requerido.'},
         cidade: {required:'Campo Requerido.'},
         uf: {required:'Campo Requerido.'},
         telefone: {required:'Campo Requerido.'},
         email: {required:'Campo Requerido.'}
      },

        errorClass: "help-inline",
        errorElement: "span",
        highlight:function(element, errorClass, validClass) {
            $(element).parents('.control-group').addClass('error');
            $(element).parents('.control-group').removeClass('success');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).parents('.control-group').removeClass('error');
            $(element).parents('.control-group').addClass('success');
        }
   });


    $("#formAlterar").validate({
      rules:{
         userfile: {required:true},
         nome: {required:true},
         cnpj: {required:true},
         ie: {required:true},
         logradouro: {required:true},
         numero: {required:true},
         bairro: {required:true},
         cidade: {required:true},
         uf: {required:true},
         telefone: {required:true},
         email: {required:true}
      },
      messages:{
         userfile: {required: 'Campo Requerido.'},
         nome: {required: 'Campo Requerido.'},
         cnpj: {required: 'Campo Requerido.'},
         ie: {required: 'Campo Requerido.'},
         logradouro: {required: 'Campo Requerido.'},
         numero: {required:'Campo Requerido.'},
         bairro: {required:'Campo Requerido.'},
         cidade: {required:'Campo Requerido.'},
         uf: {required:'Campo Requerido.'},
         telefone: {required:'Campo Requerido.'},
         email: {required:'Campo Requerido.'}
      },

        errorClass: "help-inline",
        errorElement: "span",
        highlight:function(element, errorClass, validClass) {
            $(element).parents('.control-group').addClass('error');
            $(element).parents('.control-group').removeClass('success');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).parents('.control-group').removeClass('error');
            $(element).parents('.control-group').addClass('success');
        }
   });

});
    
</script>