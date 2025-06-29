<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Financeiro extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('financeiro_model');
        $this->load->helper('codegen_helper');
        $this->data['menuLancamentos'] = 'financeiro';
    }

    public function index()
    {
        $this->lancamentos();
    }

    public function lancamentos()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar lançamentos.');
            redirect(base_url());
        }

        $where = '';
        $vencimento_de = $this->input->get('vencimento_de') ?: date('d/m/Y');
        $vencimento_ate = $this->input->get('vencimento_ate') ?: date('d/m/Y');
        $cliente = $this->input->get('cliente');
        $tipo = $this->input->get('tipo');
        $status = $this->input->get('status');
        $valor_desconto = $this->input->get('valor_desconto');
        $desconto = $this->input->get('desconto');

        $periodo = $this->input->get('periodo');

        if (! empty($vencimento_de)) {
            $date = DateTime::createFromFormat('d/m/Y', $vencimento_de);

            if (empty($where)) {
                $dateString = $date->format('Y-m-d');
                $where = "data_vencimento >= '$dateString'";
            } else {
                $where .= " AND data_vencimento >= '$date'";
            }
        }

        if (! empty($vencimento_ate)) {
            $date = DateTime::createFromFormat('d/m/Y', $vencimento_ate)->format('Y-m-d');

            if (empty($where)) {
                $where = "data_vencimento <= '$date'";
            } else {
                $where .= " AND data_vencimento <= '$date'";
            }
        }

        if (isset($status) && $status != '') {
            if (empty($where)) {
                $where = "baixado = '$status'";
            } else {
                $where .= " AND baixado = '$status'";
            }
        }

        if (! empty($cliente)) {
            if (empty($where)) {
                $where = "cliente_fornecedor LIKE '%{$cliente}%'";
            } else {
                $where .= " AND cliente_fornecedor LIKE '%{$cliente}%'";
            }
        }

        if (! empty($tipo)) {
            if (empty($where)) {
                $where = "tipo = '$tipo'";
            } else {
                $where .= " AND tipo = '$tipo'";
            }
        }

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url("financeiro/lancamentos/?vencimento_de=$vencimento_de&vencimento_ate=$vencimento_ate&cliente=$cliente&tipo=$tipo&status=$status&periodo=$periodo");
        $this->data['configuration']['total_rows'] = $this->financeiro_model->count('lancamentos', $where);
        $this->data['configuration']['page_query_string'] = true;

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->financeiro_model->get('lancamentos', '*', $where, $this->data['configuration']['per_page'], $this->input->get('per_page'));
        $this->data['totals'] = $this->financeiro_model->getTotals($where);

        $this->data['estatisticas_financeiro'] = $this->financeiro_model->getEstatisticasFinanceiro2();

        $this->data['view'] = 'financeiro/lancamentos';

        return $this->layout();
    }

    public function adicionarReceita()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar lançamentos.');
            redirect(base_url());
        }
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        $urlAtual = $this->input->post('urlAtual');
        if ($this->form_validation->run('receita') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $vencimento = $this->input->post('vencimento');
            $recebimento = $this->input->post('recebimento');
            if ($recebimento != null) {
                $recebimento = explode('/', $recebimento);
                $recebimento = $recebimento[2] . '-' . $recebimento[1] . '-' . $recebimento[0];
            }
            if ($vencimento == null) {
                $vencimento = date('d/m/Y');
            }
            try {
                $vencimento = explode('/', $vencimento);
                $vencimento = $vencimento[2] . '-' . $vencimento[1] . '-' . $vencimento[0];
            } catch (Exception $e) {
                $vencimento = date('Y/m/d');
            }
            // Formatação correta dos valores
            $valor = str_replace(',', '.', $this->input->post('valor'));
            $valor_desconto = floatval(str_replace(',', '.', $this->input->post('valor_desconto')));   
            $desconto = $valor_desconto;
            $total_sem_desconto = $valor + $valor_desconto;
            $valor = $total_sem_desconto;
            $total_com_desconto = $valor - $valor_desconto;
            $valor_desconto = $total_com_desconto;
            // Verifica se o valor está em formato monetário
            if (!is_numeric($valor_desconto)) {
                $valor_desconto = str_replace([',', '.'], ['', ''], $valor_desconto);
            }
            if (!is_numeric($valor)) {
                $valor = str_replace([',', '.'], ['', ''], $valor);
            }
            // Criação do array de dados
            $data = [
                'descricao' => set_value('descricao'),
                'valor' => number_format($valor, 2, '.', ''), // Formatação para garantir 2 casas decimais
                'valor_desconto' => number_format($valor_desconto, 2, '.', ''), // Formatação para garantir 2 casas decimais
                'desconto' => $desconto,
                'tipo_desconto' => 'real',
                'data_vencimento' => $vencimento,
                'data_pagamento' => $recebimento != null ? $recebimento : date('Y-m-d'),
                'baixado' => $this->input->post('recebido') ?: 0,
                'cliente_fornecedor' => set_value('cliente'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => set_value('tipo'),
                'observacoes' => set_value('observacoes'),
                'usuarios_id' => $this->session->userdata('id_admin'),
            ];
            if (set_value('idFornecedor')) {
                $data['clientes_id'] = set_value('idFornecedor');
            }
            if (set_value('idCliente')) {
                $data['clientes_id'] = set_value('idCliente');
            }
            // Inserção dos dados no banco
            if ($this->financeiro_model->add('lancamentos', $data) == true) {
                $this->session->set_flashdata('success', 'Lançamento adicionado com sucesso!');
                log_info('Adicionou um lançamento em Financeiro');
                redirect($urlAtual);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }
        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar adicionar o lançamento.');
        redirect($urlAtual);
    }

    public function adicionarReceita_parc()
    {
        //$this->load->library('form_validation');
        //$this->data['custom_error'] = '';
        $urlAtual = $this->input->post('urlAtual');
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar lançamentos.');
            redirect(base_url());
        } else {

            $valor_desconto = $this->input->post('desconto_parc') ?: 0;
            $entrada = $this->input->post('entrada') ?: 0;
            $valor_desconto = str_replace(',', '.', $valor_desconto);

            $qtdparcelas_parc = $this->input->post('qtdparcelas_parc') ?: 1; //4x
            $valor_parc = $this->input->post('valor_parc'); //450
            $valorparcelas = ($valor_parc - $entrada) / $qtdparcelas_parc;

            $desconto_por_parcela = $valor_desconto > 0 ? ($valor_desconto / $qtdparcelas_parc) : 0;

            //para por na descrição, valor total sem desconto e sem parcelamento
            $descricao_parc_valor = $valor_parc + $valor_desconto;

            //cria variavel para pegar o valor total ja com o desconto e diminui com o desconto
            $total_com_desconto = $valorparcelas + $desconto_por_parcela;

            if ($entrada >= $valor_parc) {
                $this->session->set_flashdata('error', 'O valor da entrada não pode ser maior ou igual ao valor total da receita/Despesa!');
                redirect($urlAtual);
            }

            $dia_pgto = $this->input->post('dia_pgto');
            $dia_base_pgto = $this->input->post('dia_base_pgto');
            $recebimento = $this->input->post('recebimento');

            try {
                $dia_pgto = explode('/', $dia_pgto);
                $dia_pgto = $dia_pgto[2] . '-' . $dia_pgto[1] . '-' . $dia_pgto[0];

                $dia_base_pgto = explode('/', $dia_base_pgto);
                $dia_base_pgto = $dia_base_pgto[2] . '-' . $dia_base_pgto[1] . '-' . $dia_base_pgto[0];
            } catch (Exception $e) {
                $dia_pgto = date('Y/m/d');
                $dia_base_pgto = date('Y/m/d');
            }

            if ($recebimento) {
                try {

                    $recebimento = explode('/', $recebimento);
                    $recebimento = $recebimento[2] . '-' . $recebimento[1] . '-' . $recebimento[0];
                } catch (Exception) {
                }
            }

            $comissao = $this->input->post('comissao');

            if (! validate_money($comissao)) {
                $comissao = str_replace([',', '.'], ['', ''], $comissao);
            }

            if ($entrada == 0) {
                $loops = 1;
                while ($loops <= $qtdparcelas_parc) {
                    $myDateTimeISO = $dia_base_pgto;
                    $loopsmes = $loops - 1;
                    $addThese = $loopsmes;
                    $myDateTime = new DateTime($myDateTimeISO);
                    $myDayOfMonth = date_format($myDateTime, 'j');
                    date_modify($myDateTime, "+$addThese months");

                    //Descobre se o dia do mês caiu
                    $myNewDayOfMonth = date_format($myDateTime, 'j');
                    if ($myDayOfMonth > 28 && $myNewDayOfMonth < 4) {
                        //Em caso afirmativo, corrija voltando o número de dias que transbordaram
                        date_modify($myDateTime, "-$myNewDayOfMonth days");
                    }

                    $data = [
                        'descricao' => $this->input->post('descricao_parc') . ' - Parcelamento de R$' . $descricao_parc_valor . '  [' . $loops . '/' . $qtdparcelas_parc . ']',
                        'valor' => $total_com_desconto,
                        'desconto' => $desconto_por_parcela,
                        'tipo_desconto' => 'real',
                        'valor_desconto' => $valorparcelas,
                        'data_vencimento' => date_format($myDateTime, 'Y-m-d'),
                        'data_pagamento' => $recebimento ?: date_format($myDateTime, 'Y-m-d'),
                        'baixado' => 0,
                        'cliente_fornecedor' => $this->input->post('cliente_parc'),
                        'clientes_id' => $this->input->post('idCliente_parc'),
                        'observacoes' => $this->input->post('observacoes_parc'),
                        'forma_pgto' => $this->input->post('formaPgto_parc'),
                        'tipo' => $this->input->post('tipo_parc'),
                        'usuarios_id' => $this->session->userdata('id_admin'),
                    ];

                    if ($this->financeiro_model->add('lancamentos', $data) == true) {
                        $this->session->set_flashdata('success', 'Lançamento adicionado com sucesso!');
                        log_info('Adicionou um lançamento em Financeiro');
                    } else {
                        $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
                    }
                    $loops++;
                }

                redirect($urlAtual);
            } else {
                $desconto_entrada = '0';
                $data1 = [
                    'descricao' => $this->input->post('descricao_parc') . ' - Entrada do parc. de R$' . $descricao_parc_valor . ' ',
                    'valor' => $entrada,
                    'desconto' => $desconto_entrada,
                    'valor_desconto' => $entrada,
                    'tipo_desconto' => 'real',
                    'data_vencimento' => $dia_pgto,
                    'data_pagamento' => $dia_pgto != null ? $dia_pgto : date_format('Y-m-d'),
                    'baixado' => 1,
                    'cliente_fornecedor' => $this->input->post('cliente_parc'),
                    'clientes_id' => $this->input->post('idCliente_parc'),
                    'observacoes' => $this->input->post('observacoes_parc'),
                    'forma_pgto' => $this->input->post('formaPgto_parc'),
                    'tipo' => $this->input->post('tipo_parc'),
                    'usuarios_id' => $this->session->userdata('id_admin'),
                ];
                // if (empty($data['valor_desconto'])) {
                //     $data['valor_desconto'] =  "0";
                // }

                $this->financeiro_model->add1('lancamentos', $data1);

                $loops = 1;
                while ($loops <= $qtdparcelas_parc) {
                    $myDateTimeISO = $dia_base_pgto;
                    $loopsmes = $loops - 1;
                    $addThese = $loopsmes;
                    $myDateTime = new DateTime($myDateTimeISO);
                    $myDayOfMonth = date_format($myDateTime, 'j');
                    date_modify($myDateTime, "+$addThese months");

                    //Find out if the day-of-month has dropped
                    $myNewDayOfMonth = date_format($myDateTime, 'j');
                    if ($myDayOfMonth > 28 && $myNewDayOfMonth < 4) {
                        //If so, fix by going back the number of days that have spilled over
                        date_modify($myDateTime, "-$myNewDayOfMonth days");
                    }

                    $data = [
                        'descricao' => $this->input->post('descricao_parc') . ' - Parcelamento de R$' . $descricao_parc_valor . ' [' . $loops . '/' . $qtdparcelas_parc . ']',
                        'valor' => $total_com_desconto,
                        'desconto' => $desconto_por_parcela,
                        'tipo_desconto' => 'real',
                        'valor_desconto' => $valorparcelas,
                        'data_vencimento' => date_format($myDateTime, 'Y-m-d'),
                        'data_pagamento' => date_format($myDateTime, 'Y-m-d'),
                        'baixado' => 0,
                        'cliente_fornecedor' => $this->input->post('cliente_parc'),
                        'observacoes' => $this->input->post('observacoes_parc'),
                        'forma_pgto' => $this->input->post('formaPgto_parc'),
                        'tipo' => $this->input->post('tipo_parc'),
                        'usuarios_id' => $this->session->userdata('id_admin'),

                    ];

                    // if (empty($data['valor_desconto'])) {
                    //     $data['valor_desconto'] =  "0";
                    // }

                    if ($this->financeiro_model->add('lancamentos', $data) == true) {
                        $this->session->set_flashdata('success', 'Lançamento adicionado com sucesso!');
                        log_info('Adicionou um lançamento em Financeiro');
                    } else {
                        $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
                    }
                    $loops++;
                }

                redirect($urlAtual);
            }
        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar adicionar o lançamento');
        redirect($urlAtual);
    }

    public function adicionarDespesa()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar lançamentos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        $urlAtual = $this->input->post('urlAtual');
        if ($this->form_validation->run('despesa') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $vencimento = $this->input->post('vencimento');
            $pagamento = $this->input->post('pagamento');

            if ($pagamento != null) {
                $pagamento = explode('/', $pagamento);
                $pagamento = $pagamento[2] . '-' . $pagamento[1] . '-' . $pagamento[0];
            }

            if ($vencimento == null) {
                $vencimento = date('d/m/Y');
            }

            try {
                $vencimento = explode('/', $vencimento);
                $vencimento = $vencimento[2] . '-' . $vencimento[1] . '-' . $vencimento[0];
            } catch (Exception $e) {
                $vencimento = date('Y/m/d');
            }

            $valor = $this->input->post('valor');

            if (! validate_money($valor)) {
                $valor = str_replace([',', '.'], ['', ''], $valor);
            }

            $data = [
                'descricao' => set_value('descricao'),
                'valor' => $valor,
                'data_vencimento' => $vencimento,
                'data_pagamento' => $pagamento != null ? $pagamento : date('Y-m-d'),
                'baixado' => $this->input->post('pago') ?: 0,
                'cliente_fornecedor' => set_value('fornecedor'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => set_value('tipo'),
                'observacoes' => set_value('observacoes'),
                'usuarios_id' => $this->session->userdata('id_admin'),
            ];

            if (set_value('idFornecedor')) {
                $data['clientes_id'] = set_value('idFornecedor');
            }
            if (set_value('idCliente')) {
                $data['clientes_id'] = set_value('idCliente');
            }
            if ($this->financeiro_model->add('lancamentos', $data) == true) {
                $this->session->set_flashdata('success', 'Despesa adicionada com sucesso!');
                log_info('Adicionou uma despesa');
                redirect($urlAtual);
            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar adicionar despesa!');
                redirect($urlAtual);
            }
        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar adicionar despesa.');
        redirect($urlAtual);
    }

    public function editar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar lançamentos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        $urlAtual = $this->input->post('urlAtual');

        $this->form_validation->set_rules('descricao', '', 'trim|required');
        $this->form_validation->set_rules('fornecedor', '', 'trim|required');
        $this->form_validation->set_rules('valor', '', 'trim|required');
        $this->form_validation->set_rules('vencimento', '', 'trim|required');
        $this->form_validation->set_rules('pagamento', '', 'trim');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $vencimento = $this->input->post('vencimento');
            $pagamento = $this->input->post('pagamento');

            try {
                $vencimento = explode('/', $vencimento);
                $vencimento = $vencimento[2] . '-' . $vencimento[1] . '-' . $vencimento[0];

                if ($pagamento) {
                    $pagamento = explode('/', $pagamento);
                    $pagamento = $pagamento[2] . '-' . $pagamento[1] . '-' . $pagamento[0];
                }
            } catch (Exception $e) {
                $vencimento = date('Y/m/d');
            }

            $valor = floatval($this->input->post('valor'));
            //Se o valor_desconto for vázio, seta a variavel com valor 0, se não for vazio recebe o valor de desconto
            $valor_desconto = floatval($this->input->post('valor_desconto_editar')); // valor do total + desconto

            $valor_total = $valor + $valor_desconto; //90 + 10=100
            $valor_com_desconto = $valor_total - $valor_desconto;

            $data = [
                'descricao' => $this->input->post('descricao'),
                'data_vencimento' => $vencimento,
                'data_pagamento' => $pagamento,
                'valor' => $valor_total,
                'desconto' => $valor_desconto,
                'tipo_desconto' => 'real',
                'valor_desconto' => $valor_com_desconto,
                'baixado' => $this->input->post('pago') ?: 0,
                'cliente_fornecedor' => $this->input->post('fornecedor'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => $this->input->post('tipo'),
                'observacoes' => $this->input->post('observacoes'),
                'usuarios_id' => $this->session->userdata('id_admin'),
            ];

            if (set_value('idFornecedor')) {
                $data['clientes_id'] = set_value('idFornecedor');
            }
            if (empty($data['valor_desconto'])) {
                $data['valor_desconto'] = '0';
            }

            if (set_value('idCliente')) {
                $data['clientes_id'] = set_value('idCliente');
            }
            if ($this->financeiro_model->edit('lancamentos', $data, 'idLancamentos', $this->input->post('id')) == true) {
                $this->session->set_flashdata('success', 'lançamento editado com sucesso!');
                log_info('Alterou um lançamento no financeiro. ID' . $this->input->post('id'));
                redirect($urlAtual);
            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar editar lançamento!');
                redirect($urlAtual);
            }
        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar editar lançamento.');
        redirect($urlAtual);

        $data = [
            'descricao' => $this->input->post('descricao'),
            'data_vencimento' => $this->input->post('vencimento'),
            'data_pagamento' => $pagamento,
            'valor' => $this->input->post('valor'),
            'valor_desconto' => $this->input->post('valor_desconto_editar'),
            'tipo_desconto' => 'real',
            'baixado' => $this->input->post('pago'),
            'cliente_fornecedor' => set_value('fornecedor'),
            'forma_pgto' => $this->input->post('formaPgto'),
            'tipo' => $this->input->post('tipo'),
            'usuarios_id' => $this->session->userdata('id_admin'),
        ];
        if (set_value('idFornecedor')) {
            $data['clientes_id'] = set_value('idFornecedor');
        }
        if (empty($data['valor_desconto'])) {
            $data['valor_desconto'] = '0';
        }
        if (set_value('idCliente')) {
            $data['clientes_id'] = set_value('idCliente');
        }

        print_r($data);
    }

    public function excluirLancamento()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir lançamentos.');
            redirect(base_url());
        }

        $id = $this->input->post('id');

        if ($id == null || ! is_numeric($id)) {
            $json = ['result' => false, 'message' => 'ID inválido'];
            echo json_encode($json);
            exit();
        }

        // Começa a transação
        $this->db->trans_start();

        // Atualiza a tabela vendas, removendo o ID do lançamento e alterando o faturado e status
        $this->db->set('lancamentos_id', null);
        $this->db->set('faturado', 0);
        $this->db->set('status', 'Finalizado');
        $this->db->where('lancamentos_id', $id);
        $this->db->update('vendas');

        // Exclui o lançamento
        $result = $this->financeiro_model->delete('lancamentos', 'idLancamentos', $id);

        if ($result) {
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar excluir o lançamento.');
                $json = ['result' => false, 'message' => 'Erro na transação'];
            } else {
                log_info('Excluiu um lançamento. ID: ' . $id);
                $this->session->set_flashdata('success', 'Lançamento excluído com sucesso!');
                $json = ['result' => true];
            }
        } else {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar excluir o lançamento.');
            $json = ['result' => false, 'message' => 'Erro ao excluir lançamento'];
        }

        echo json_encode($json);
        exit();
    }
    
    public function autoCompleteClienteFornecedor()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->financeiro_model->autoCompleteClienteFornecedor($q);
        }
    }

    public function autoCompleteClienteAddReceita()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->financeiro_model->autoCompleteClienteReceita($q);
        }
    }

    protected function getThisYear()
    {
        $dias = date('z');
        $primeiro = date('Y-m-d', strtotime('-' . ($dias) . ' day'));
        $ultimo = date('Y-m-d', strtotime('+' . (364 - $dias) . ' day'));

        return [$primeiro, $ultimo];
    }

    protected function getThisWeek()
    {
        return [date('Y/m/d', strtotime('last sunday', strtotime('now'))), date('Y/m/d', strtotime('next saturday', strtotime('now')))];
    }

    protected function getLastSevenDays()
    {
        return [date('Y-m-d', strtotime('-7 day', strtotime('now'))), date('Y-m-d', strtotime('now'))];
    }

    protected function getThisMonth()
    {
        $mes = date('m');
        $ano = date('Y');
        $qtdDiasMes = date('t');
        $inicia = $ano . '-' . $mes . '-01';

        $ate = $ano . '-' . $mes . '-' . $qtdDiasMes;

        return [$inicia, $ate];
    }
}
