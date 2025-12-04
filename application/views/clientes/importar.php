<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-upload"></i>
                </span>
                <h5>Importar Clientes em Massa</h5>
            </div>
            <div class="widget-content nopadding">
                <div style="padding: 20px;">
                    <div class="alert alert-info">
                        <strong>Instruções:</strong>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>Faça o download do arquivo modelo CSV</li>
                            <li>Preencha o arquivo com os dados dos clientes</li>
                            <li>Mantenha a primeira linha (cabeçalho) intacta</li>
                            <li>Campos obrigatórios: Nome/Razão Social e CPF/CNPJ</li>
                            <li>Email duplicado será ignorado automaticamente</li>
                            <li>Senha será gerada automaticamente se não informada</li>
                        </ul>
                    </div>

                    <div style="margin: 20px 0;">
                        <a href="<?php echo base_url(); ?>index.php/clientes/downloadModelo" 
                           class="button btn btn-info" 
                           style="margin-bottom: 20px;">
                            <span class="button__icon"><i class="bx bx-download"></i></span>
                            <span class="button__text2">Baixar Arquivo Modelo CSV</span>
                        </a>
                    </div>

                    <?php if ($this->session->flashdata("error")): ?>
                        <div class="alert alert-danger">
                            <?php echo $this->session->flashdata("error"); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata("success")): ?>
                        <div class="alert alert-success">
                            <?php echo $this->session->flashdata("success"); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo base_url(); ?>index.php/clientes/processarImportacao" 
                          method="post" 
                          enctype="multipart/form-data" 
                          class="form-horizontal">
                        
                        <div class="control-group">
                            <label for="arquivo_csv" class="control-label">
                                Selecione o arquivo CSV <span class="required">*</span>
                            </label>
                            <div class="controls">
                                <input type="file" 
                                       name="arquivo_csv" 
                                       id="arquivo_csv" 
                                       accept=".csv" 
                                       required />
                                <span class="help-inline">Apenas arquivos CSV (máx. 5MB)</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="span12">
                                <div class="span6 offset3" style="display:flex;justify-content: center">
                                    <button type="submit" class="button btn btn-mini btn-success">
                                        <span class="button__icon"><i class="bx bx-upload"></i></span>
                                        <span class="button__text2">Importar Clientes</span>
                                    </button>
                                    <a title="Voltar" class="button btn btn-warning" href="<?php echo site_url(); ?>/clientes">
                                        <span class="button__icon"><i class="bx bx-undo"></i></span>
                                        <span class="button__text2">Voltar</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="alert alert-warning" style="margin-top: 20px;">
                        <strong>Atenção:</strong>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>O arquivo será processado e removido após a importação</li>
                            <li>Clientes com email duplicado serão ignorados</li>
                            <li>Verifique os resultados após a importação</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
