<div class="col-lg-12 col-md-12 col-sm-12">
    <div class="card">
        <div class="card-title">
            <h4>
                <i class="fa fa-eye"></i>
                <?= $this->lang->line('app_view').' '.ucfirst($this->lang->line('user')); ?>
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <tr>
                        <td width="30%">
                            <?= ucfirst($this->lang->line('user_name')) ?>
                        </td>
                        <td class="text-left">
                            <?= $nome; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_rg')) ?>
                        </td>
                        <td class="text-left">
                            <?= $rg; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_cpf')) ?>
                        </td>
                        <td class="text-left">
                            <?= $cpf; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_street')) ?>
                        </td>
                        <td class="text-left">
                            <?= $rua; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_number')) ?>
                        </td>
                        <td class="text-left">
                            <?= $numero; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_district')) ?>
                        </td>
                        <td class="text-left">
                            <?= $bairro; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_city')) ?>
                        </td>
                        <td class="text-left">
                            <?= $cidade; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_state')) ?>
                        </td>
                        <td class="text-left">
                            <?= $estado; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_email')) ?>
                        </td>
                        <td class="text-left">
                            <?= $email; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_phone')) ?>
                        </td>
                        <td class="text-left">
                            <?= $telefone; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_cel')) ?>
                        </td>
                        <td class="text-left">
                            <?= $celular; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_status')) ?>
                        </td>
                        <td class="text-left">
                            <?= $situacao ? $this->lang->line('app_active') : $this->lang->line('app_inactive'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_created')) ?>
                        </td>
                        <td class="text-left">
                            <?= date('d/m/Y', strtotime($dataCadastro)); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= ucfirst($this->lang->line('user_group')) ?>
                        </td>
                        <td class="text-left">
                            <?= $permissao; ?>
                        </td>
                    </tr>
                </table>

            </div>
            <hr>
            <a href="<?= site_url('usuarios/create') ?>" class="btn btn-success">
                <i class="fa fa-plus"></i>
                <?= $this->lang->line('app_create'); ?>
            </a>
            <a href="<?= site_url('usuarios/update/'.$idUsuarios) ?>" class="btn btn-info">
                <i class="fa fa-edit"></i>
                <?= $this->lang->line('app_edit'); ?>
            </a>
            <a href="<?= site_url('usuarios') ?>" class="btn btn-dark">
                <i class="fa fa-reply"></i>
                <?= $this->lang->line('app_back'); ?>
            </a>

        </div>
    </div>
</div>