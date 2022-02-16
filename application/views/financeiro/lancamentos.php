<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css"/>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/dayjs.min.js"></script>

<?php $situacao = $this->input->get('situacao');
$periodo = $this->input->get('periodo');
?>

<style type="text/css">
    label.error {
        color: #b94a48;
    }

    input.error {
        border-color: #b94a48;
    }

    input.valid {
        border-color: #5bb75b;
    }

    textarea {
        resize: vertical;
    }
</style>

<div class="new122" style="margin-top: 0; min-height: 100vh">

<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) { ?>
    <div class="span5" style="margin-left: 0">
        <a href="#modalReceita" data-toggle="modal" role="button" class="button btn btn-mini btn-success">
          <span class="button__icon"><i class='bx bx-upvote' ></i></span><span class="button__text2" title="Cadastrar nova receita">Nova Receita</span></a>
        <a href="#modalDespesa" data-toggle="modal" role="button" class="button btn btn-mini btn-danger">
          <span class="button__icon"><i class='bx bx-downvote' ></i></span><span class="button__text2" title="Cadastrar nova despesa">Nova Despesa</span></a>
    </div>
<?php } ?>

<div class="span12" style="margin-left: 0;margin-top: 1rem;">
    <form action="<?php echo current_url(); ?>" method="get">
        <div class="span2" style="margin-left: 0">
            <label>Período</label>
            <select id="periodo" name="periodo" class="span12">
                <option value="dia" <?= $this->input->get('periodo') === 'dia' ? 'selected' : '' ?>>Dia</option>
                <option value="semana" <?= $this->input->get('periodo') === 'semana' ? 'selected' : '' ?>>Semana
                </option>
                <option value="mes" <?= $this->input->get('periodo') === 'mes' ? 'selected' : '' ?>>Mês</option>
                <option value="ano" <?= $this->input->get('periodo') === 'ano' ? 'selected' : '' ?>>Ano</option>
            </select>
        </div>

        <div class="span2">
            <label>Vencimento (de)</label>
            <input id="vencimento_de" type="text" class="span12 datepicker" name="vencimento_de"
                   value="<?= $this->input->get('vencimento_de') ? $this->input->get('vencimento_de') : date('d/m/Y') ?>">
        </div>

        <div class="span2">
            <label>Vencimento (até)</label>
            <input id="vencimento_ate" type="text" class="span12 datepicker" name="vencimento_ate"
                   value="<?= $this->input->get('vencimento_ate') ? $this->input->get('vencimento_ate') : date('d/m/Y') ?>">
        </div>

        <div class="span2">
            <label>Tipo</label>
            <select name="tipo" class="span12">
                <option value="">Todos</option>
                <option value="receita" <?= $this->input->get('tipo') === 'receita' ? 'selected' : '' ?>>Receita
                </option>
                <option value="despesa" <?= $this->input->get('tipo') === 'despesa' ? 'selected' : '' ?>>Despesa
                </option>
            </select>
        </div>

        <div class="span2">
            <label>Status</label>
            <select name="status" class="span12">
                <option value="">Todos</option>
                <option value="0" <?= $this->input->get('status') === '0' ? 'selected' : '' ?>>Pendente</option>
                <option value="1" <?= $this->input->get('status') === '1' ? 'selected' : '' ?>>Pago</option>
            </select>
        </div>

        <div class="span2">
            <label>Cliente/Fornecedor</label>
            <input id="cliente_fornecedor" type="text" class="span12" name="cliente"
                   value="<?= $this->input->get('cliente') ?>">
        </div>

        <div class="span2 pull-right">
            <button type="submit" class="button btn btn-primary btn-sm" style="min-width: 120px">
              <span class="button__icon"><i class='bx bx-filter-alt' ></i></span><span class="button__text2">Filtrar</span></a></button>
        </div>
    </form>
</div>

<div class="span12" style="margin-left: 0;">
    <div class="widget-box">
        <div class="widget-title">
      <span class="icon">
        <i class="fas fa-hand-holding-usd"></i>
      </span>
            <h5>Lançamentos Financeiros</h5>

        </div>

        <div class="widget-content nopadding tab-content">


            <table class="table table-bordered " id="divLancamentos">
                <thead>
                <tr style="backgroud-color: #2D335B">
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Cliente / Fornecedor</th>
                    <th>Descrição</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                    <th>Observações</th>
                    <th>Valor</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php

                if (!$results) {
                    echo '<tr>
              <td colspan="9" >Nenhum lançamento encontrado</td>
            </tr>';
                }
                foreach ($results as $r) {
                    $vencimento = date(('d/m/Y'), strtotime($r->data_vencimento));
                    if ($r->baixado == 0) {
                        $status = 'Pendente';
                    } else {
                        $status = 'Pago';
                    };
                    if ($r->tipo == 'receita') {
                        $label = 'success';
                    } else {
                        $label = 'important';
                    }
                    echo '<tr>';
                    echo '<td>' . $r->idLancamentos . '</td>';
                    echo '<td><span class="label label-' . $label . '">' . ucfirst($r->tipo) . '</span></td>';
                    echo '<td>' . $r->cliente_fornecedor . '</td>';
                    echo '<td>' . $r->descricao . '</td>';
                    echo '<td>' . $vencimento . '</td>';
                    echo '<td>' . $status . '</td>';
                    echo '<td>' . $r->observacoes . '</td>';
                    echo '<td> R$ ' . number_format($r->valor, 2, ',', '.') . '</td>';
                    echo '<td>';
                    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eLancamento')) {
                        echo '<a href="#modalEditar" style="margin-right: 1%" data-toggle="modal" role="button" idLancamento="' . $r->idLancamentos . '" descricao="' . $r->descricao . '" valor="' . $r->valor . '" vencimento="' . date('d/m/Y', strtotime($r->data_vencimento)) . '" pagamento="' . date('d/m/Y', strtotime($r->data_pagamento)) . '" baixado="' . $r->baixado . '" cliente="' . $r->cliente_fornecedor . '" formaPgto="' . $r->forma_pgto . '" tipo="' . $r->tipo . '" observacoes="' . $r->observacoes . '" usuario="' . $r->nome . '" class="btn-nwe3" title="Editar OS"><i class="bx bx-edit"></i></a>';

                    }
                    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dLancamento')) {
                        echo '<a href="#modalExcluir" data-toggle="modal" role="button" idLancamento="' . $r->idLancamentos . '" class="btn-nwe4" title="Excluir OS"><i class="bx bx-trash-alt"></i></a>';
                    }

                    echo '</td>';
                    echo '</tr>';
                } ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="6" style="text-align: right; color: green"><strong>Total Receitas:</strong></td>
                    <td colspan="3" style="text-align: left; color: green">
                        <strong>R$ <?php echo number_format($totals['receitas'], 2, ',', '.') ?></strong></td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align: right; color: red"><strong>Total Despesas:</strong></td>
                    <td colspan="3" style="text-align: left; color: red">
                        <strong>R$ <?php echo number_format($totals['despesas'], 2, ',', '.') ?></strong></td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align: right"><strong>Saldo:</strong></td>
                    <td colspan="3" style="text-align: left;">
                        <strong>R$ <?php echo number_format($totals['receitas'] - $totals['despesas'], 2, ',', '.') ?></strong>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php echo $this->pagination->create_links(); ?>
</div>

<!-- Modal nova receita -->
<div id="modalReceita" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <form id="formReceita" action="<?php echo base_url() ?>index.php/financeiro/adicionarReceita" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">MapOS - Adicionar Receita</h3>
        </div>
        <div class="modal-body">

            <div class="span12 alert alert-info" style="margin-left: 0"> Obrigatório o preenchimento dos campos com
                asterisco.
            </div>
            <div class="span12" style="margin-left: 0">
                <label for="descricao">Descrição*</label>
                <input class="span12" id="descricao" type="text" name="descricao"/>
                <input id="urlAtual" type="hidden" name="urlAtual" value="<?php echo current_url() ?>"/>
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span12" style="margin-left: 0">
                    <label for="cliente">Cliente*</label>
                    <input class="span12" id="cliente" type="text" name="cliente"/>
                    <input class="span12" id="idCliente" type="hidden" name="idCliente"/>
                </div>

                <div class="span12" style="margin-left: 0">
                    <label for="observacoes">Observações</label>
                    <textarea class="span12" id="observacoes" name="observacoes"></textarea>
                </div>

            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span4" style="margin-left: 0">
                    <label for="valor">Valor*</label>
                    <input type="hidden" id="tipo" name="tipo" value="receita"/>
                    <input class="span12 money" id="valor" type="text" name="valor" data-affixes-stay="true" data-thousands="" data-decimal="." />
                </div>
                <div class="span4">
                    <label for="vencimento">Data Vencimento*</label>
                    <input class="span12 datepicker" autocomplete="off" id="vencimento" type="text" name="vencimento"/>
                </div>

            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span4" style="margin-left: 0">
                    <label for="recebido">Recebido?</label>
                    &nbsp &nbsp &nbsp &nbsp<input id="recebido" type="checkbox" name="recebido" value="1"/>
                </div>
                <div id="divRecebimento" class="span8" style=" display: none">
                    <div class="span6">
                        <label for="recebimento">Data Recebimento</label>
                        <input class="span12 datepicker" autocomplete="off" id="recebimento" type="text" name="recebimento"/>
                    </div>
                    <div class="span6">
                        <label for="formaPgto">Forma Pgto</label>
                        <select name="formaPgto" id="formaPgto" class="span12">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Boleto">Boleto</option>
                            <option value="Depósito">Depósito</option>
                            <option value="Débito">Débito</option>
                            <option value="Pix">Pix</option>
                        </select>
                    </div>
                </div>

            </div>

        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-success" id="submitReceita">Adicionar Receita</button>
        </div>
    </form>
</div>

<!-- Modal nova despesa -->
<div id="modalDespesa" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <form id="formDespesa" action="<?php echo base_url() ?>index.php/financeiro/adicionarDespesa" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">MapOS - Adicionar Despesa</h3>
        </div>
        <div class="modal-body">
            <div class="span12 alert alert-info" style="margin-left: 0"> Obrigatório o preenchimento dos campos com
                asterisco.
            </div>
            <div class="span12" style="margin-left: 0">
                <label for="descricao">Descrição*</label>
                <input class="span12" id="descricao" type="text" name="descricao"/>
                <input id="urlAtual" type="hidden" name="urlAtual" value="<?php echo current_url() ?>"/>
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span12" style="margin-left: 0">
                    <label for="fornecedor">Fornecedor / Empresa*</label>
                    <input class="span12" id="fornecedor" type="text" name="fornecedor"/>
                    <input class="span12" id="idFornecedor" type="hidden" name="idFornecedor"/>
                </div>

                <div class="span12" style="margin-left: 0">
                    <label for="observacoes">Observações</label>
                    <textarea class="span12" id="observacoes" name="observacoes"></textarea>
                </div>

            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span4" style="margin-left: 0">
                    <label for="valor">Valor*</label>
                    <input type="hidden" name="tipo" value="despesa"/>
                    <input class="span12 money" type="text" name="valor" data-affixes-stay="true" data-thousands="" data-decimal="." />
                </div>
                <div class="span4">
                    <label for="vencimento">Data Vencimento*</label>
                    <input class="span12 datepicker" autocomplete="off" type="text" name="vencimento"/>
                </div>

            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span4" style="margin-left: 0">
                    <label for="pago">Foi Pago?</label>
                    &nbsp &nbsp &nbsp &nbsp<input id="pago" type="checkbox" name="pago" value="1"/>
                </div>
                <div id="divPagamento" class="span8" style=" display: none">
                    <div class="span6">
                        <label for="pagamento">Data Pagamento</label>
                        <input class="span12 datepicker" autocomplete="off" id="pagamento" type="text" name="pagamento"/>
                    </div>

                    <div class="span6">
                        <label for="formaPgto">Forma Pgto</label>
                        <select name="formaPgto" class="span12">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Boleto">Boleto</option>
                            <option value="Depósito">Depósito</option>
                            <option value="Débito">Débito</option>
                            <option value="Pix">Pix</option>
                        </select>
                    </div>
                </div>

            </div>

        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-danger" id="submitDespesa">Adicionar Despesa</button>
        </div>
    </form>
</div>


<!-- Modal editar lançamento -->
<div id="modalEditar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <form id="formEditar" action="<?php echo base_url() ?>index.php/financeiro/editar" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">MapOS - Editar Lançamento</h3>
        </div>
        <div class="modal-body">
            <div class="span12 alert alert-info" style="margin-left: 0"> Obrigatório o preenchimento dos campos com
                asterisco.
            </div>
            <div class="span12" style="margin-left: 0">
                <label for="descricao">Descrição*</label>
                <input class="span12" id="descricaoEditar" type="text" name="descricao"/>
                <input id="urlAtualEditar" type="hidden" name="urlAtual" value=""/>
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span12" style="margin-left: 0">
                    <label for="fornecedor">Fornecedor / Empresa*</label>
                    <input class="span12" id="fornecedorEditar" type="text" name="fornecedor"/>
                </div>

                <div class="span12" style="margin-left: 0">
                    <label for="observacoes">Observações</label>
                    <textarea class="span12" id="observacoes_edit" name="observacoes"></textarea>
                </div>
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span4" style="margin-left: 0">
                    <label for="valor">Valor*</label>
                    <input type="hidden" name="tipo" value="despesa"/>
                    <input type="hidden" id="idEditar" name="id" value=""/>
                    <input class="span12 money" type="text" name="valor" id="valorEditar" data-affixes-stay="true" data-thousands="" data-decimal="." />
                </div>
                <div class="span4">
                    <label for="vencimento">Data Vencimento*</label>
                    <input class="span12 datepicker2" type="text" name="vencimento" id="vencimentoEditar"/>
                </div>
                <div class="span4">
                    <label for="vencimento">Tipo*</label>
                    <select class="span12" name="tipo" id="tipoEditar">
                        <option value="receita">Receita</option>
                        <option value="despesa">Despesa</option>
                    </select>
                </div>

            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span4" style="margin-left: 0">
                    <label for="pago">Foi Pago?</label>
                    &nbsp &nbsp &nbsp &nbsp<input id="pagoEditar" type="checkbox" name="pago" value="1"/>
                </div>
                <div id="divPagamentoEditar" class="span8" style=" display: none">
                    <div class="span6">
                        <label for="pagamento">Data Pagamento</label>
                        <input class="span12 datepicker2" id="pagamentoEditar" type="text" name="pagamento"/>
                    </div>

                    <div class="span6">
                        <label for="formaPgto">Forma Pgto</label>
                        <select name="formaPgto" id="formaPgtoEditar" class="span12">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Boleto">Boleto</option>
                            <option value="Depósito">Depósito</option>
                            <option value="Débito">Débito</option>
                            <option value="Pix">Pix</option>
                        </select>
                    </div>
                </div>

            </div>

        </div>
        <div class="modal-footer">
            Modificado:<input disabled id="usuarioEditar" value=""/>
            <button class="btn" data-dismiss="modal" aria-hidden="true" id="btnCancelarEditar">Cancelar</button>
            <button class="btn btn-primary">Salvar Alterações</button>
        </div>
    </form>
</div>

<!-- Modal Excluir lançamento-->
<div id="modalExcluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">MapOS - Excluir Lançamento</h3>
    </div>
    <div class="modal-body">
        <h5 style="text-align: center">Deseja realmente excluir esse lançamento?</h5>
        <input name="id" id="idExcluir" type="hidden" value=""/>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true" id="btnCancelExcluir">Cancelar</button>
        <button class="btn btn-danger" id="btnExcluir">Excluir Lançamento</button>
    </div>
</div>

<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url(); ?>assets/js/maskmoney.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {

        $(".money").maskMoney();

        $('#pago').click(function (event) {
            var flag = $(this).is(':checked');
            if (flag == true) {
                $('#divPagamento').show();
            } else {
                $('#divPagamento').hide();
            }
        });


        $('#recebido').click(function (event) {
            var flag = $(this).is(':checked');
            if (flag == true) {
                $('#divRecebimento').show();
            } else {
                $('#divRecebimento').hide();
            }
        });

        $('#pagoEditar').click(function (event) {
            var flag = $(this).is(':checked');
            if (flag == true) {
                $('#divPagamentoEditar').show();
            } else {
                $('#divPagamentoEditar').hide();
            }
        });


        $("#formReceita").validate({
            rules: {
                descricao: {
                    required: true
                },
                cliente: {
                    required: true
                },
                valor: {
                    required: true
                },
                vencimento: {
                    required: true
                }

            },
            messages: {
                descricao: {
                    required: 'Campo Requerido.'
                },
                cliente: {
                    required: 'Campo Requerido.'
                },
                valor: {
                    required: 'Campo Requerido.'
                },
                vencimento: {
                    required: 'Campo Requerido.'
                }
            },
            submitHandler: function(form) {
                $("#submitReceita").attr("disabled", true);
                form.submit();
            }
        });


        $("#formDespesa").validate({
            rules: {
                descricao: {
                    required: true
                },
                fornecedor: {
                    required: true
                },
                valor: {
                    required: true
                },
                vencimento: {
                    required: true
                }

            },
            messages: {
                descricao: {
                    required: 'Campo Requerido.'
                },
                fornecedor: {
                    required: 'Campo Requerido.'
                },
                valor: {
                    required: 'Campo Requerido.'
                },
                vencimento: {
                    required: 'Campo Requerido.'
                }
            },
            submitHandler: function(form) {
                $("#submitDespesa").attr("disabled", true);
                form.submit();
            }
        });


        $(document).on('click', '.excluir', function (event) {
            $("#idExcluir").val($(this).attr('idLancamento'));
        });


        $(document).on('click', '.editar', function (event) {
            $("#idEditar").val($(this).attr('idLancamento'));
            $("#descricaoEditar").val($(this).attr('descricao'));
            $("#usuarioEditar").val($(this).attr('usuario'));
            $("#fornecedorEditar").val($(this).attr('cliente'));
            $("#observacoes_edit").val($(this).attr('observacoes'));
            $("#valorEditar").val($(this).attr('valor'));
            $("#vencimentoEditar").val($(this).attr('vencimento'));
            $("#pagamentoEditar").val($(this).attr('pagamento'));
            $("#formaPgtoEditar").val($(this).attr('formaPgto'));
            $("#tipoEditar").val($(this).attr('tipo'));
            $("#urlAtualEditar").val($(location).attr('href'));
            var baixado = $(this).attr('baixado');
            if (baixado == 1) {
                $("#pagoEditar").prop('checked', true);
                $("#divPagamentoEditar").show();
            } else {
                $("#pagoEditar").prop('checked', false);
                $("#divPagamentoEditar").hide();
            }


        });

        $(document).on('click', '#btnExcluir', function (event) {
            var id = $("#idExcluir").val();

            $.ajax({
                type: "POST",
                url: "<?php echo base_url(); ?>index.php/financeiro/excluirLancamento",
                data: "id=" + id,
                dataType: 'json',
                success: function (data) {
                    if (data.result == true) {
                        $("#btnCancelExcluir").trigger('click');
                        $("#divLancamentos").html('<div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div>');
                        $("#divLancamentos").load($(location).attr('href') + " #divLancamentos");

                    } else {
                        $("#btnCancelExcluir").trigger('click');
                        Swal.fire({
                            type: "error",
                            title: "Atenção",
                            text: "Ocorreu um erro ao tentar excluir produto."
                        });
                    }
                }
            });
            return false;
        });
        let controlBaixa = "<?php echo $configuration['control_baixa']; ?>";
        let datePickerOptions = {
            dateFormat: 'dd/mm/yy',
        };
        if (controlBaixa === '1') {
            datePickerOptions.minDate = 0;
            datePickerOptions.maxDate = 0;
        }
        $(".datepicker2").datepicker(
            datePickerOptions
        );
        $(".datepicker").datepicker();
        $('#periodo').on('change', function (event) {
            const period = $('#periodo').val();

            switch (period) {
                case 'dia':
                    $('#vencimento_de').val(dayjs().locale('pt-br').format('DD/MM/YYYY'));
                    $('#vencimento_ate').val(dayjs().locale('pt-br').format('DD/MM/YYYY'));
                    break;
                case 'semana':
                    $('#vencimento_de').val(dayjs().startOf('week').locale('pt-br').format('DD/MM/YYYY'));
                    $('#vencimento_ate').val(dayjs().endOf('week').locale('pt-br').format('DD/MM/YYYY'));
                    break;
                case 'mes':
                    $('#vencimento_de').val(dayjs().startOf('month').locale('pt-br').format('DD/MM/YYYY'));
                    $('#vencimento_ate').val(dayjs().endOf('month').locale('pt-br').format('DD/MM/YYYY'));
                    break;
                case 'ano':
                    $('#vencimento_de').val(dayjs().startOf('year').locale('pt-br').format('DD/MM/YYYY'));
                    $('#vencimento_ate').val(dayjs().endOf('year').locale('pt-br').format('DD/MM/YYYY'));
                    break;
            }
        });

        $("#cliente_fornecedor").autocomplete({
            source: "<?php echo base_url(); ?>index.php/financeiro/autoCompleteClienteFornecedor",
            minLength: 1,
            select: function (event, ui) {
                $("#cliente_fornecedor").val(ui.item.value);
                $("#idFornecedor").val(ui.item.id);
            }
        });
        $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/financeiro/autoCompleteClienteAddReceita",
            minLength: 1,
            select: function (event, ui) {
                $("#cliente").val(ui.item.label);
                $("#idCliente").val(ui.item.id);
            }
        });
        $("#fornecedor").autocomplete({
            source: "<?php echo base_url(); ?>index.php/financeiro/autoCompleteClienteAddReceita",
            minLength: 1,
            select: function (event, ui) {
                $("#fornecedor").val(ui.item.label);
                $("#idFornecedor").val(ui.item.id);
            }
        });
    });
</script>
