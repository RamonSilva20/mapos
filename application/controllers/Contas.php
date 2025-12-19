<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Contas extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('contas_model');
        $this->load->model('financeiro_model');
        $this->data['menuContas'] = 'financeiro';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar contas.');
            redirect(base_url());
        }

        $pesquisa = $this->input->get('pesquisa');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('contas/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->contas_model->count('contas', $pesquisa);
        if($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/contas")."?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $results = $this->contas_model->get('contas', '*', $pesquisa, $this->data['configuration']['per_page'], $this->uri->segment(3));
        
        // Atualizar saldo de cada conta
        foreach ($results as $conta) {
            $this->contas_model->atualizarSaldo($conta->idContas);
        }
        
        // Buscar novamente para pegar saldos atualizados
        $this->data['results'] = $this->contas_model->get('contas', '*', $pesquisa, $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'contas/contas';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar contas.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('contas') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            // Converter formato de moeda brasileiro (0,00) para formato numérico (0.00)
            $saldoPost = $this->input->post('saldo');
            $saldoInicial = 0;
            if (!empty($saldoPost) && $saldoPost !== '0,00') {
                // Remove pontos de milhar e substitui vírgula por ponto
                $saldoLimpo = str_replace('.', '', $saldoPost);
                $saldoLimpo = str_replace(',', '.', $saldoLimpo);
                if (is_numeric($saldoLimpo)) {
                    $saldoInicial = floatval($saldoLimpo);
                }
            }

            $data = [
                'conta' => set_value('conta'),
                'banco' => set_value('banco'),
                'numero' => set_value('numero'),
                'tipo' => set_value('tipo'),
                'saldo' => $saldoInicial,
                'status' => 1,
                'cadastro' => date('Y-m-d'),
            ];

            if ($this->contas_model->add('contas', $data) == true) {
                $this->session->set_flashdata('success', 'Conta adicionada com sucesso!');
                log_info('Adicionou uma conta.');
                redirect(site_url('contas/'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->data['view'] = 'contas/adicionarConta';

        return $this->layout();
    }

    public function editar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3)) || ! $this->contas_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Conta não encontrada ou parâmetro inválido.');
            redirect('contas/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar contas.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('contas') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            // Atualizar saldo baseado nos lançamentos
            $idConta = $this->input->post('idContas');
            $this->contas_model->atualizarSaldo($idConta);

            $data = [
                'conta' => $this->input->post('conta'),
                'banco' => $this->input->post('banco'),
                'numero' => $this->input->post('numero'),
                'tipo' => $this->input->post('tipo'),
                'status' => $this->input->post('status') ? 1 : 0,
            ];

            if ($this->contas_model->edit('contas', $data, 'idContas', $idConta) == true) {
                $this->session->set_flashdata('success', 'Conta editada com sucesso!');
                log_info('Alterou uma conta. ID' . $idConta);
                redirect(site_url('contas/editar/') . $idConta);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
            }
        }

        $this->data['result'] = $this->contas_model->getById($this->uri->segment(3));
        // Atualizar saldo antes de exibir
        $this->contas_model->atualizarSaldo($this->data['result']->idContas);
        $this->data['result'] = $this->contas_model->getById($this->uri->segment(3));
        $this->data['view'] = 'contas/editarConta';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir contas.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir conta.');
            redirect(site_url('contas/gerenciar/'));
        }

        // Verificar se há lançamentos vinculados
        if ($this->contas_model->hasLancamentos($id)) {
            $this->session->set_flashdata('error', 'Não é possível excluir esta conta pois existem lançamentos vinculados a ela.');
            redirect(site_url('contas/gerenciar/'));
        }

        $this->contas_model->delete('contas', 'idContas', $id);
        log_info('Removeu uma conta. ID' . $id);

        $this->session->set_flashdata('success', 'Conta excluída com sucesso!');
        redirect(site_url('contas/gerenciar/'));
    }

    public function extrato()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Conta não encontrada ou parâmetro inválido.');
            redirect('contas/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar extratos.');
            redirect(base_url());
        }

        $idConta = $this->uri->segment(3);
        $conta = $this->contas_model->getById($idConta);
        
        if (!$conta) {
            $this->session->set_flashdata('error', 'Conta não encontrada.');
            redirect('contas/gerenciar');
        }

        // Atualizar saldo
        $this->contas_model->atualizarSaldo($idConta);
        $conta = $this->contas_model->getById($idConta);

        // Filtros de data
        $dataInicio = $this->input->get('data_inicio') ?: date('Y-m-01'); // Primeiro dia do mês
        $dataFim = $this->input->get('data_fim') ?: date('Y-m-t'); // Último dia do mês

        // Converter datas para formato do banco
        if ($dataInicio) {
            $dataInicio = date('Y-m-d', strtotime(str_replace('/', '-', $dataInicio)));
        }
        if ($dataFim) {
            $dataFim = date('Y-m-d', strtotime(str_replace('/', '-', $dataFim)));
        }

        $this->data['conta'] = $conta;
        $this->data['extrato'] = $this->contas_model->getExtrato($idConta, $dataInicio, $dataFim);
        $this->data['data_inicio'] = $dataInicio;
        $this->data['data_fim'] = $dataFim;

        $this->data['view'] = 'contas/extrato';

        return $this->layout();
    }

    public function transferir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para realizar transferências.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        // Regras de validação
        $this->form_validation->set_rules('conta_origem', 'Conta de Origem', 'required|numeric');
        $this->form_validation->set_rules('conta_destino', 'Conta de Destino', 'required|numeric|differs[conta_origem]');
        $this->form_validation->set_rules('valor', 'Valor', 'required');
        $this->form_validation->set_rules('data', 'Data', 'required');
        $this->form_validation->set_rules('observacoes', 'Observações', 'trim');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $contaOrigem = $this->input->post('conta_origem');
            $contaDestino = $this->input->post('conta_destino');
            $valor = str_replace(',', '.', $this->input->post('valor'));
            $data = $this->input->post('data');
            $observacoes = $this->input->post('observacoes');

            // Converter data
            $data = explode('/', $data);
            $data = $data[2] . '-' . $data[1] . '-' . $data[0];

            // Criar lançamento de saída (despesa) na conta origem
            $dataSaida = [
                'descricao' => 'Transferência para ' . $this->contas_model->getById($contaDestino)->conta,
                'valor' => $valor,
                'valor_desconto' => $valor,
                'desconto' => 0,
                'tipo_desconto' => 'real',
                'data_vencimento' => $data,
                'data_pagamento' => $data,
                'baixado' => 1,
                'cliente_fornecedor' => 'Transferência entre contas',
                'forma_pgto' => 'Transferência',
                'tipo' => 'despesa',
                'observacoes' => $observacoes ?: 'Transferência entre contas',
                'contas_id' => $contaOrigem,
                'usuarios_id' => $this->session->userdata('id_admin'),
            ];

            // Criar lançamento de entrada (receita) na conta destino
            $dataEntrada = [
                'descricao' => 'Transferência de ' . $this->contas_model->getById($contaOrigem)->conta,
                'valor' => $valor,
                'valor_desconto' => $valor,
                'desconto' => 0,
                'tipo_desconto' => 'real',
                'data_vencimento' => $data,
                'data_pagamento' => $data,
                'baixado' => 1,
                'cliente_fornecedor' => 'Transferência entre contas',
                'forma_pgto' => 'Transferência',
                'tipo' => 'receita',
                'observacoes' => $observacoes ?: 'Transferência entre contas',
                'contas_id' => $contaDestino,
                'usuarios_id' => $this->session->userdata('id_admin'),
            ];

            // Inserir ambos os lançamentos
            if ($this->financeiro_model->add('lancamentos', $dataSaida) && 
                $this->financeiro_model->add('lancamentos', $dataEntrada)) {
                
                // Atualizar saldos
                $this->contas_model->atualizarSaldo($contaOrigem);
                $this->contas_model->atualizarSaldo($contaDestino);

                $this->session->set_flashdata('success', 'Transferência realizada com sucesso!');
                log_info('Realizou transferência entre contas.');
                redirect(site_url('contas/gerenciar/'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao realizar a transferência.</p></div>';
            }
        }

        $this->data['contas'] = $this->contas_model->getAll();
        $this->data['view'] = 'contas/transferir';

        return $this->layout();
    }

    public function getAll()
    {
        $contas = $this->contas_model->getAll();
        header('Content-Type: application/json');
        echo json_encode($contas);
    }
}

