<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>

<link rel="stylesheet" href="<?php echo base_url() ?>assets/trumbowyg/ui/trumbowyg.css">
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/trumbowyg.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/langs/pt_br.js"></script>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-book"></i>
                </span>
                <h5>Cadastrar Credencial de Pagamento</h5>
            </div>
            <div class="widget-content">

                <?php if ($custom_error == true) { ?>
                    <div class="span12 alert alert-danger" id="divInfo" style="padding: 1%;">Dados incompletos, verifique os campos com asterisco.</div>
                <?php
                } ?>
                <form action="<?php echo current_url(); ?>" method="post" id="formPagamento">
                    <div class="span12">
                        <div class="span3">
                            <label for="Nome">Nome<span class="required"></span></label>
                            <select name="nomePag" id="nomePag" class="span12">
                                <option value="MercadoPago" required>Mercado Pago</option>
                            </select>
                        </div>
                        <div class="span4">
                            <label for="clientId">Client Id<span class="required"></span></label>
                            <input type="text" class="span12" name="clientId" placeholder="Informe sua credencial: Client Id">
                        </div>
                        <div class="span5" >
                        <label for="clientSecret">Client Secret<span class="required"></span></label>
                            <input type="text" class="span12" name="clientSecret" placeholder="Informe sua credencial: Client Secret">
                        </div>
                        <div class="span4" style="margin-left: 0">
                            <label for="publicKey">Public Key<span class="required"></span></label>
                            <input type="text" class="span12" name="publicKey" placeholder="Informe sua credencial: Public Key">
                        </div>
                        <div class="span8">
                        <label for="accessToken">Access Token<span class="required"></span></label>
                            <input type="text" class="span12" name="accessToken" placeholder="Informe sua credencial: Access_Token">
                        </div>
                    <div class="span2" style="margin-left: 0">
                        <label for="default_pag">Tornar Padrão<span class="required"></span></label>
                            <input type="checkbox" class="span6" name="default_pag" id="default_pag">
                        </div>
                    </div>
                    <div class="span12" style="padding: 1%; margin-left: 0">
                        <div class="span6 offset3" style="text-align: center">
                            <button class="btn btn-success" id="btnContinuar"><i class="fas fa-plus"></i> Adicionar</button>
                            <a href="<?php echo base_url() ?>index.php/pagamentos" class="btn"><i class="fas fa-backward"></i> Voltar</a>
                        </div>
                    </div>
                </form>
                .
            </div>
        </div>
    </div>
</div>
