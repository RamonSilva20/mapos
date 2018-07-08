<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-title">
                <h4>
                    <?= $button ?>
                        <?= ucfirst($this->lang->line('user')); ?>
                </h4>
                <hr>
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form action="<?= $action; ?>" method="post">
                        <div class="form-group">
                            <label for="nome">
                                <?= ucfirst($this->lang->line('user_name')) ?>
                            </label>
                            <input type="text" class="form-control" name="nome" id="nome" value="<?= $nome; ?>" />
                            <?= form_error('nome') ?>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="rg">
                                        <?= ucfirst($this->lang->line('user_rg')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="rg" id="rg" value="<?= $rg; ?>" />
                                    <?= form_error('rg') ?>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="cpf">
                                        <?= ucfirst($this->lang->line('user_cpf')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="cpf" id="cpf" value="<?= $cpf; ?>" />
                                    <?= form_error('cpf') ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="telefone">
                                        <?= ucfirst($this->lang->line('user_phone')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="telefone" id="telefone" value="<?= $telefone; ?>" />
                                    <?= form_error('telefone') ?>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="celular">
                                        <?= ucfirst($this->lang->line('user_cel')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="celular" id="celular" value="<?= $celular; ?>" />
                                    <?= form_error('celular') ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">
                                <?= ucfirst($this->lang->line('user_email')) ?>
                            </label>
                            <input type="text" class="form-control" name="email" id="email" value="<?= $email; ?>" />
                            <?= form_error('email') ?>
                        </div>
                        <div class="form-group">
                            <label for="senha">
                                <?= ucfirst($this->lang->line('user_password')) ?>
                            </label>
                            <input type="password" class="form-control" name="senha" id="senha" value="<?= $senha; ?>" placeholder="<?= $this->uri->segment(2) == 'update' ? $this->lang->line('user_change_password') : ''; ?>" />
                            <?= form_error('senha') ?>
                        </div>
                        <div class="form-group">
                            <label for="rua">
                                <?= ucfirst($this->lang->line('user_street')) ?>
                            </label>
                            <input type="text" class="form-control" name="rua" id="rua" value="<?= $rua; ?>" />
                            <?= form_error('rua') ?>
                        </div>
                        <div class="row">
                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label for="numero">
                                        <?= ucfirst($this->lang->line('user_number')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="numero" id="numero" value="<?= $numero; ?>" />
                                    <?= form_error('numero') ?>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label for="bairro">
                                        <?= ucfirst($this->lang->line('user_district')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="bairro" id="bairro" value="<?= $bairro; ?>" />
                                    <?= form_error('bairro') ?>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label for="cidade">
                                        <?= ucfirst($this->lang->line('user_city')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="cidade" id="cidade" value="<?= $cidade; ?>" />
                                    <?= form_error('cidade') ?>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label for="estado">
                                        <?= ucfirst($this->lang->line('user_state')) ?>
                                    </label>
                                    <input type="text" class="form-control" name="estado" id="estado" value="<?= $estado; ?>" />
                                    <?= form_error('estado') ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="situacao">
                                        <?= ucfirst($this->lang->line('user_status')) ?>
                                    </label>
                                    <?php 
                                        $options = array(
                                            '1' => $this->lang->line('app_active'),
                                            '0' => $this->lang->line('app_inactive')
                                        );
                                        echo form_dropdown('situacao', $options, $situacao, array('class' => 'form-control'));
                                        echo form_error('situacao');

                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="permissoes_id">
                                        <?= ucfirst($this->lang->line('user_group')) ?>
                                    </label>
                                    <?= form_dropdown('permissoes_id', $permissoes, $permissoes_id, array('class' => 'form-control')); ?>
                                    <?= form_error('permissoes_id') ?>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="idUsuarios" value="<?= $idUsuarios; ?>" />
                        <button type="submit" class="btn btn-info">
                            <?= $button ?>
                        </button>
                        <a href="<?= site_url('usuarios') ?>" class="btn btn-dark">
                            <i class="fa fa-reply"></i>
                            <?= $this->lang->line('app_cancel'); ?>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>