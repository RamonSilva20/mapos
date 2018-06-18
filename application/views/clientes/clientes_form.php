<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-title">
                <h4>
                    <?= $button ?>
                        <?= ucfirst($this->lang->line('client')); ?>
                </h4>
                <hr>
            </div>
            <div class="card-body">
                <div class="form-body">
                    <form action="<?= $action; ?>" method="post">
                        <div class="form-group">
                            <label for="nomeCliente">
                                <?= ucfirst($this->lang->line('client_name')) ?>
                            </label>
                            <input type="text" class="form-control" name="nomeCliente" id="nomeCliente" value="<?= $nomeCliente; ?>" />
                            <?= form_error('nomeCliente') ?>
                        </div>
                        <!-- <div class="form-group">
                            <label for="sexo">
                                <?= ucfirst($this->lang->line('client_sex')) ?>
                            </label>
                            <input type="text" class="form-control" name="sexo" id="sexo" value="<?= $sexo; ?>" />
                            <?= form_error('sexo') ?>
                        </div>
                        <div class="form-group">
                            <label for="pessoa_fisica">
                                <?= ucfirst($this->lang->line('client_type')) ?>
                            </label>
                            <input type="text" class="form-control" name="pessoa_fisica" id="pessoa_fisica" value="<?= $pessoa_fisica; ?>" />
                            <?= form_error('pessoa_fisica') ?>
                        </div> -->
                        
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="documento">
                                        <?= ucfirst($this->lang->line('client_doc')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="documento" id="documento" value="<?= $documento; ?>" />
                                    <?= form_error('documento') ?>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="telefone">
                                        <?= ucfirst($this->lang->line('client_phone')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="telefone" id="telefone" value="<?= $telefone; ?>" />
                                    <?= form_error('telefone') ?>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="celular">
                                        <?= ucfirst($this->lang->line('client_cel')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="celular" id="celular" value="<?= $celular; ?>" />
                                    <?= form_error('celular') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <?= ucfirst($this->lang->line('client_mail')) ?>
                            </label>
                            <input type="email" class="form-control" name="email" id="email" value="<?= $email; ?>" />
                            <?= form_error('email') ?>
                        </div>

                        <div class="row">
                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label for="cep">
                                        <?= ucfirst($this->lang->line('client_zip')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="cep" id="cep" value="<?= $cep; ?>" />
                                    <?= form_error('cep') ?>
                                </div>
                            </div>
                            <div class="col-lg-9 col-md-9 col-sm-12">
                                <div class="form-group">
                                    <label for="rua">
                                        <?= ucfirst($this->lang->line('client_street')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="rua" id="rua" value="<?= $rua; ?>" />
                                    <?= form_error('rua') ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            
                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label for="numero">
                                        <?= ucfirst($this->lang->line('client_number')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="numero" id="numero" value="<?= $numero; ?>" />
                                    <?= form_error('numero') ?>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label for="bairro">
                                        <?= ucfirst($this->lang->line('client_district')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="bairro" id="bairro" value="<?= $bairro; ?>" />
                                    <?= form_error('bairro') ?>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label for="cidade">
                                        <?= ucfirst($this->lang->line('client_city')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="cidade" id="cidade" value="<?= $cidade; ?>" />
                                    <?= form_error('cidade') ?>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label for="estado">
                                        <?= ucfirst($this->lang->line('client_state')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="estado" id="estado" value="<?= $estado; ?>" />
                                    <?= form_error('estado') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="rua">
                                <?= ucfirst($this->lang->line('client_obs')) ?>
                            </label>
                            <textarea class="form-control" name="obs" id="obs" cols="30" rows="3"><?= $obs; ?></textarea>
                            <?= form_error('obs') ?>
                        </div>
                        
                        <input type="hidden" name="idClientes" value="<?= $idClientes; ?>" />
                        <button type="submit" class="btn btn-info">
                            <?= $button ?>
                        </button>
                        <a href="<?= site_url('clientes') ?>" class="btn btn-dark">
                            <i class="fa fa-reply"></i>
                            <?= $this->lang->line('app_cancel'); ?>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>

    $(document).ready(function(){

        function limpa_formulario_cep() {
			// Limpa valores do formulário de cep.
			$("#rua").val("");
			$("#bairro").val("");
			$("#cidade").val("");
			$("#estado").val("");
		}
            
		//Quando o campo cep perde o foco.
		$("#cep").blur(function() {
			//Nova variável "cep" somente com dígitos.
			var cep = $(this).val().replace(/\D/g, '');
			//Verifica se campo cep possui valor informado.
			if (cep != "") {
				//Expressão regular para validar o CEP.
				var validacep = /^[0-9]{8}$/;
				//Valida o formato do CEP.
				if(validacep.test(cep)) {
					//Preenche os campos com "..." enquanto consulta webservice.
					$("#rua").val("...");
					$("#bairro").val("...");
					$("#cidade").val("...");
					$("#estado").val("...");
					//Consulta o webservice viacep.com.br/
					$.getJSON("https://viacep.com.br/ws/"+ cep +"/json/?callback=?", function(dados) {
						if (!("erro" in dados)) {
							//Atualiza os campos com os valores da consulta.
							$("#rua").val(dados.logradouro);
							$("#bairro").val(dados.bairro);
							$("#cidade").val(dados.localidade);
							$("#estado").val(dados.uf);
							document.getElementById("numero").focus();
						} //end if.
						else {
							//CEP pesquisado não foi encontrado.
							limpa_formulario_cep();
							alert("CEP não encontrado.");
						}
					});
				} //end if.
				else {
					//cep é inválido.
					limpa_formulario_cep();
					alert("Formato de CEP inválido.");
				}
			} //end if.
			else {
				//cep sem valor, limpa formulário.
				limpa_formulario_cep();
			}
		});

    });

</script>