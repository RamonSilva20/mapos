<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Financeiro extends MY_Controller
{

    /**
     * author: Ramon Silva
     * email: silva018-mg@yahoo.com.br
     *
     */

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
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar lançamentos.');
            redirect(base_url());
        }

        $where = '';
        $vencimento_de = $this->input->get('vencimento_de') ?: date('d/m/Y');
        $vencimento_ate = $this->input->get('vencimento_ate') ?: date('d/m/Y');
        $cliente = $this->input->get('cliente');
        $tipo = $this->input->get('tipo');
        $status = $this->input->get('status');
        $periodo = $this->input->get('periodo');

        if (! empty($vencimento_de)) {
            $date = DateTime::createFromFormat('d/m/Y', $vencimento_de)->format('Y-m-d');

            if (empty($where)) {
                $where = "data_vencimento >= '$date'";
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
                $where = "cliente_fornecedor LIKE '%${cliente}%'";
            } else {
                $where .= " AND cliente_fornecedor LIKE '%${cliente}%'";
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

            $valor = $this->input->post('valor');

            if (!validate_money($valor)) {
                $valor = str_replace([',', '.'], ['', ''], $valor);
            }

            $data = [
                'descricao' => set_value('descricao'),
                'valor' => $valor,
                'data_vencimento' => $vencimento,
                'data_pagamento' => $recebimento != null ? $recebimento : date('Y-m-d'),
                'baixado' => $this->input->post('recebido') ?: 0,
                'cliente_fornecedor' => set_value('cliente'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => set_value('tipo'),
                'observacoes' => set_value('observacoes'),
                'usuarios_id' => $this->session->userdata('id'),
            ];

            if (set_value('idFornecedor')) {
                $data['clientes_id'] =  set_value('idFornecedor');
            }
            if (set_value('idCliente')) {
                $data['clientes_id'] =  set_value('idCliente');
            }
            if ($this->financeiro_model->add('lancamentos', $data) == true) {
                $this->session->set_flashdata('success', 'Receita adicionada com sucesso!');
                log_info('Adicionou uma receita');
                redirect($urlAtual);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar adicionar receita.');
        redirect($urlAtual);
    }

    public function adicionarDespesa()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) {
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

            if (!validate_money($valor)) {
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
                'usuarios_id' => $this->session->userdata('id'),
            ];

            if (set_value('idFornecedor')) {
                $data['clientes_id'] =  set_value('idFornecedor');
            }
            if (set_value('idCliente')) {
                $data['clientes_id'] =  set_value('idCliente');
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
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eLancamento')) {
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

                $pagamento = explode('/', $pagamento);
                $pagamento = $pagamento[2] . '-' . $pagamento[1] . '-' . $pagamento[0];
            } catch (Exception $e) {
                $vencimento = date('Y/m/d');
            }

            $data = [
                'descricao' => $this->input->post('descricao'),
                'data_vencimento' => $vencimento,
                'data_pagamento' => $pagamento,
                'valor' => $this->input->post('valor'),
                'baixado' => $this->input->post('pago') ?: 0,
                'cliente_fornecedor' => $this->input->post('fornecedor'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => $this->input->post('tipo'),
                'observacoes' => $this->input->post('observacoes'),
                'usuarios_id' => $this->session->userdata('id'),
            ];

            if (set_value('idFornecedor')) {
                $data['clientes_id'] =  set_value('idFornecedor');
            }
            if (set_value('idCliente')) {
                $data['clientes_id'] =  set_value('idCliente');
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
            'baixado' => $this->input->post('pago'),
            'cliente_fornecedor' => set_value('fornecedor'),
            'forma_pgto' => $this->input->post('formaPgto'),
            'tipo' => $this->input->post('tipo'),
            'usuarios_id' => $this->session->userdata('id'),
        ];
        if (set_value('idFornecedor')) {
            $data['clientes_id'] =  set_value('idFornecedor');
        }
        if (set_value('idCliente')) {
            $data['clientes_id'] =  set_value('idCliente');
        }

        print_r($data);
    }

    public function excluirLancamento()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir lançamentos.');
            redirect(base_url());
        }

        $id = $this->input->post('id');

        if ($id == null || !is_numeric($id)) {
            $json = ['result' => false];
            echo json_encode($json);
        } else {
            $result = $this->financeiro_model->delete('lancamentos', 'idLancamentos', $id);
            if ($result) {
                log_info('Removeu um lançamento. ID: ' . $id);
                $json = ['result' => true];
                echo json_encode($json);
            } else {
                $json = ['result' => false];
                echo json_encode($json);
            }
        }
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
        $dias = date("z");
        $primeiro = date("Y-m-d", strtotime("-" . ($dias) . " day"));
        $ultimo = date("Y-m-d", strtotime("+" . (364 - $dias) . " day"));
        return [$primeiro, $ultimo];
    }

    protected function getThisWeek()
    {
        return [date("Y/m/d", strtotime("last sunday", strtotime("now"))), date("Y/m/d", strtotime("next saturday", strtotime("now")))];
    }

    protected function getLastSevenDays()
    {
        return [date("Y-m-d", strtotime("-7 day", strtotime("now"))), date("Y-m-d", strtotime("now"))];
    }

    protected function getThisMonth()
    {
        $mes = date('m');
        $ano = date('Y');
        $qtdDiasMes = date('t');
        $inicia = $ano . "-" . $mes . "-01";

        $ate = $ano . "-" . $mes . "-" . $qtdDiasMes;
        return [$inicia, $ate];
    }
}
