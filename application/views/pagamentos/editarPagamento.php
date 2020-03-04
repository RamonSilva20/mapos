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
                <h5>Editar Credencias de Pagamentos</h5>
            </div>
            <div class="widget-content">

                <?php if ($custom_error) { ?>
                    <div class="span12 alert alert-danger" id="divInfo" style="padding: 1%;">Dados incompletos, verifique os campos com asterisco ou se selecionou corretamente cliente e responsável.</div>
                <?php  } ?>

                <form action="<?php echo current_url(); ?>" method="post" id="formPagamento">

                <div class="span12">
                        <div class="span2">
                            <label for="Nome">Nome</label>
                            <?php echo form_hidden('idPag', $result->idPag) ?>
                            <select name="nomePag" id="nomePag" class="span12">
                                <option value="MercadoPago" required>Mercado Pago</option>
                            </select>
                        </div>
                        <div class="span5">
                            <label for="clientId">Client Id</label>
                            <input id="clientId" class="span12" type="text" name="clientId" value="<?php echo $result->client_id ?>" />
                        </div>

                        <div class="span5">
                            <label for="clientSecret">Client Secret</label>
                            <input id="clientSecret" class="span12" type="text" name="clientSecret" value="<?php echo $result->client_secret ?>" />
                        </div>
                        <div class="span12" style="margin-left: 0">
                            <label for="publicKey">Public Key<span class="required"></span></label>
                            <input id="publicKey" type="text" class="span12" name="publicKey" value="<?php echo $result->public_key ?>">
                        </div>
                        <div class="span12" style="margin-left: 0">
                            <label for="accessToken">Access Token<span class="required"></span></label>
                            <input id="accessToken" class="span12" type="text" name="accessToken" value="<?php echo $result->access_token ?>" />
                        </div>
                        <div class="span4" style="margin-left: 0">
                            <label for="default_pag">Tornar Padrão<span class="required"></span></label>
                            <input name="default_pag" id="default_pag" class="span12" type="checkbox" <?php echo $result->default_pag == 1 ? "checked" : null; ?> />
                         </div>
                    </div>

                    <div class="span12" style="padding: 1%; margin-left: 0">
                        <div class="span6 offset5">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt"></i> Atualizar</button>
                            <a href="<?php echo base_url() ?>index.php/pagamentos" id="" class="btn"><i class="fas fa-backward"></i> Voltar</a>
                        </div>
                    </div>
                </form>
                .
            </div>
        </div>
    </div>
</div>
