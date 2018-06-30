<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-title">
                <h4>
                    <?= $button ?>
                        <?= ucfirst($this->lang->line('perm')); ?>
                </h4>
                <hr>
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form action="<?= $action; ?>" method="post">

                        <div class="row">

                            <div class="form-group col-md-8 col-sm-12">
                                <label for="nome">
                                    <?= ucfirst($this->lang->line('perm_name')) ?>
                                </label>
                                <input type="text" class="form-control" name="nome" id="nome" value="<?= $nome; ?>" />
                                <?= form_error('nome') ?>
                            </div>

                            <div class="form-group col-md-4 col-sm-12">
                                <label for="situacao">
                                    <?= ucfirst($this->lang->line('perm_status')) ?>
                                </label>
                                <?=  form_dropdown('situacao', array('1' => $this->lang->line('app_active'), '0' => $this->lang->line('app_inactive')), $situacao, array('class' => 'form-control') ); ?> 
                                <?= form_error('situacao') ?>
                            </div>
                        </div>

                        <div class="form-group">

                            <table class="table table-bordered">
                                <thead>
               
                                    <tr>
                                        <th>
                                            <label>
                                                <input name="" type="checkbox" value="1" id="marcarTodos" />
                                                <span class="lbl"> <?= $this->lang->line('app_check_all'); ?></span>
                                            </label>
                                        </th>
                                        <th><?= strtoupper($this->lang->line('app_view')); ?></th>
                                        <th><?= strtoupper($this->lang->line('app_create')); ?></th>
                                        <th><?= strtoupper($this->lang->line('app_edit')); ?></th>
                                        <th><?= strtoupper($this->lang->line('app_delete')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                                <?= ucfirst($this->lang->line('client')); ?>
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['vCliente']) == '1'? 'checked' : '' ?> name="vCliente" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['aCliente']) == '1' ? 'checked' : '' ?> name="aCliente" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['eCliente']) =='1'? 'checked' : '' ?> name="eCliente" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['dCliente']) == '1'? 'checked' : '' ?> name="dCliente" class="marcar" type="checkbox" value="1" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                                <?= ucfirst($this->lang->line('product')); ?>
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['vProduto']) == '1' ? 'checked' : '' ?> name="vProduto" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes[ 'aProduto']) == '1' ? 'checked' : '' ?> name="aProduto" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['eProduto']) == '1' ? 'checked' : '' ?> name="eProduto" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['dProduto']) == '1' ? 'checked' : '' ?> name="dProduto" class="marcar" type="checkbox" value="1" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                                <?= ucfirst($this->lang->line('service')); ?>
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['vServico']) == '1' ? 'checked' : '' ?> name="vServico" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['aServico']) == '1' ? 'checked' : '' ?> name="aServico" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['eServico']) == '1' ? 'checked' : '' ?> name="eServico" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['dServico']) == '1' ? 'checked' : '' ?> name="dServico" class="marcar" type="checkbox" value="1" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                                <?= ucfirst($this->lang->line('os')); ?>
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['vOs']) == '1' ? 'checked' : '' ?> name="vOs" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['aOs']) == '1' ? 'checked' : '' ?> name="aOs" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['eOs']) == '1' ? 'checked' : '' ?> name="eOs" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['dOs']) == '1' ? 'checked' : '' ?> name="dOs" class="marcar" type="checkbox" value="1" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                                <?= ucfirst($this->lang->line('sale')); ?>
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes[ 'vVenda']) == '1' ? 'checked' : '' ?> name="vVenda" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['aVenda']) == '1' ? 'checked' : '' ?> name="aVenda" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['eVenda']) == '1' ? 'checked' : '' ?> name="eVenda" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes[ 'dVenda']) == '1' ? 'checked' : '' ?> name="dVenda" class="marcar" type="checkbox" value="1" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                                <?= ucfirst($this->lang->line('file')); ?>
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['vArquivo']) == '1' ? 'checked' : '' ?> name="vArquivo" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['aArquivo']) == '1' ? 'checked' : '' ?> name="aArquivo" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['eArquivo']) == '1' ? 'checked' : '' ?> name="eArquivo" class="marcar" type="checkbox" value="1" />
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['dArquivo']) == '1' ? 'checked' : '' ?> name="dArquivo" class="marcar" type="checkbox" value="1" />
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>
                                                <?= ucfirst($this->lang->line('payment')); ?>
                                        </td>
                                        <td>
                                                <input <?= isset($permissoes['vLancamento']) =='1' ? 'checked' : '' ?> name="vLancamento" class="marcar" type="checkbox" value="1" />

                                        </td>

                                        <td>
                                                <input <?= isset($permissoes['aLancamento']) =='1' ? 'checked' : '' ?> name="aLancamento" class="marcar" type="checkbox" value="1" />

                                        </td>

                                        <td>
                                                <input <?= isset($permissoes['eLancamento']) =='1' ? 'checked' : '' ?> name="eLancamento" class="marcar" type="checkbox" value="1" />

                                        </td>

                                        <td>
                                                <input <?= isset($permissoes['dLancamento']) =='1' ? 'checked' : '' ?> name="dLancamento" class="marcar" type="checkbox" value="1" />
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center" colspan="4">
                                            <?= strtoupper($this->lang->line('reports')); ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                                <input <?= isset($permissoes[ 'rCliente']) == '1' ? 'checked' : '' ?> name="rCliente" class="marcar" type="checkbox" value="1" />
                                                <?= ucfirst($this->lang->line('report')) ?> <?= ucfirst($this->lang->line('client')); ?>
                                        </td>

                                        <td>
                                                <input <?= isset($permissoes[ 'rServico']) == '1' ? 'checked' : '' ?> name="rServico" class="marcar" type="checkbox" value="1" />
                                                <?= ucfirst($this->lang->line('report')) ?> <?= ucfirst($this->lang->line('service')); ?>
                                        </td>

                                        <td>
                                                <input <?= isset($permissoes[ 'rOs']) == '1' ? 'checked' : '' ?> name="rOs" class="marcar" type="checkbox" value="1" />
                                                <?= ucfirst($this->lang->line('report')) ?> <?= ucfirst($this->lang->line('os')); ?>
                                        </td>

                                        <td>
                                                <input <?= isset($permissoes[ 'rProduto']) == '1' ? 'checked' : '' ?> name="rProduto" class="marcar" type="checkbox" value="1" />
                                                <?= ucfirst($this->lang->line('report')) ?> <?= ucfirst($this->lang->line('product')); ?>
                                        </td>

                                    </tr>

                                    <tr>
                                        <td>
                                                <input <?= isset($permissoes[ 'rVenda']) == '1' ? 'checked' : '' ?> name="rVenda" class="marcar" type="checkbox" value="1" />
                                                <?= ucfirst($this->lang->line('report')) ?> <?= ucfirst($this->lang->line('sale')); ?>
                                        </td>

                                        <td>
                                                <input <?= isset($permissoes[ 'rFinanceiro']) == '1' ? 'checked' : '' ?> name="rFinanceiro" class="marcar" type="checkbox" value="1" />
                                                <?= ucfirst($this->lang->line('report')) ?> <?= ucfirst($this->lang->line('financial')); ?>
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center" colspan="4">
                                            <?= strtoupper($this->lang->line('app_configs')); ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>

                                        <td>
                                                <input <?= isset($permissoes[ 'cUsuario']) == '1' ? 'checked' : '' ?> name="cUsuario" class="marcar" type="checkbox" value="1" />
                                                <?= ucfirst($this->lang->line('app_config')); ?> <?= ucfirst($this->lang->line('user')); ?>
                                        </td>

                                        <td>
                                                <input <?= isset($permissoes[ 'cEmitente']) == '1' ? 'checked' : '' ?> name="cEmitente" class="marcar" type="checkbox" value="1" />
                                                <?= ucfirst($this->lang->line('app_config')); ?> <?= ucfirst($this->lang->line('company')); ?>
                                        </td>

                                        <td>
                                                <input <?= isset($permissoes[ 'cPermissao']) == '1' ? 'checked' : '' ?> name="cPermissao" class="marcar" type="checkbox" value="1" />
                                                <?= ucfirst($this->lang->line('app_config')); ?> <?= ucfirst($this->lang->line('perm')); ?>
                                        </td>

                                        <td>
                                                <input <?= isset($permissoes[ 'cBackup']) == '1' ? 'checked' : '' ?> name="cBackup" class="marcar" type="checkbox" value="1" />
                                                <?= ucfirst($this->lang->line('app_backup')); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>

                        <input type="hidden" name="idPermissao" value="<?= $idPermissao; ?>" />
                        <button type="submit" class="btn btn-info">
                            <?= $button ?>
                        </button>
                        <a href="<?= site_url('permissoes') ?>" class="btn btn-dark">
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

        $("#marcarTodos").change(function () {
            $("input:checkbox").prop('checked', $(this).prop("checked"));
        });

    });
</script>