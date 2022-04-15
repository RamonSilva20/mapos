<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vendas extends MY_Controller
{

    /**
     * author: Ramon Silva
     * email: silva018-mg@yahoo.com.br
     *
     */

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('vendas_model');
        $this->data['menuVendas'] = 'Vendas';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('vendas/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->vendas_model->count('vendas');

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->vendas_model->get('vendas', '*', '', $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'vendas/vendas';
        return $this->layout();
    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar Vendas.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('vendas') == false) {
            $this->data['custom_error'] = (validation_errors() ? true : false);
        } else {
            $dataVenda = $this->input->post('dataVenda');

            try {
                $dataVenda = explode('/', $dataVenda);
                $dataVenda = $dataVenda[2] . '-' . $dataVenda[1] . '-' . $dataVenda[0];
            } catch (Exception $e) {
                $dataVenda = date('Y/m/d');
            }

            $data = [
                'dataVenda' => $dataVenda,
                'observacoes' => $this->input->post('observacoes'),
                'observacoes_cliente' => $this->input->post('observacoes_cliente'),
                'clientes_id' => $this->input->post('clientes_id'),
                'usuarios_id' => $this->input->post('usuarios_id'),
                'faturado' => 0,
            ];

            if (is_numeric($id = $this->vendas_model->add('vendas', $data, true))) {
                $this->session->set_flashdata('success', 'Venda iniciada com sucesso, adicione os produtos.');
                log_info('Adicionou uma venda.');
                redirect(site_url('vendas/editar/') . $id);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->data['view'] = 'vendas/adicionarVenda';
        return $this->layout();
    }

    public function editar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar vendas');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('vendas') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $dataVenda = $this->input->post('dataVenda');

            try {
                $dataVenda = explode('/', $dataVenda);
                $dataVenda = $dataVenda[2] . '-' . $dataVenda[1] . '-' . $dataVenda[0];
            } catch (Exception $e) {
                $dataVenda = date('Y/m/d');
            }

            $data = [
                'dataVenda' => $dataVenda,
                'observacoes' => $this->input->post('observacoes'),
                'observacoes_cliente' => $this->input->post('observacoes_cliente'),
                'usuarios_id' => $this->input->post('usuarios_id'),
                'clientes_id' => $this->input->post('clientes_id'),
            ];

            if ($this->vendas_model->edit('vendas', $data, 'idVendas', $this->input->post('idVendas')) == true) {
                $this->session->set_flashdata('success', 'Venda editada com sucesso!');
                log_info('Alterou uma venda. ID: ' . $this->input->post('idVendas'));
                redirect(site_url('vendas/editar/') . $this->input->post('idVendas'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
            }
        }

        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['view'] = 'vendas/editarVenda';
        return $this->layout();
    }

    public function visualizar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['modalGerarPagamento'] = $this->load->view(
            'cobrancas/modalGerarPagamento',
            [
                'id' => $this->uri->segment(3),
                'tipo' => 'venda',
            ],
            true
        );

        $this->data['view'] = 'vendas/visualizarVenda';

        return $this->layout();
    }


    public function imprimir()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['qrCode'] = $this->vendas_model->getQrCode(
            $this->uri->segment(3),
            $this->data['configuration']['pix_key'],
            $this->data['emitente'][0]
        );

        $this->load->view('vendas/imprimirVenda', $this->data);
    }

    public function imprimirTermica()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->load->view('vendas/imprimirVendaTermica', $this->data);
    }

    public function excluir()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir vendas');
            redirect(base_url());
        }

        $this->load->model('vendas_model');

        $id = $this->input->post('id');

        $editavel = $this->vendas_model->isEditable($id);
        if (!$editavel) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir. Venda já faturada');
            redirect(site_url('vendas/gerenciar/'));
        }

        $venda = $this->vendas_model->getByIdCobrancas($id);
        if ($venda == null) {
            $venda = $this->vendas_model->getById($id);
            if ($venda == null) {
                $this->session->set_flashdata('error', 'Erro ao tentar excluir venda.');
                redirect(site_url('vendas/gerenciar/'));
            }
        }

        if ($venda->idCobranca != null) {
            if ($venda->status == "canceled") {
                $this->vendas_model->delete('cobrancas', 'vendas_id', $id);
            } else {
                $this->session->set_flashdata('error', 'Existe uma cobrança associada a esta venda, deve cancelar e/ou excluir a cobrança primeiro!');
                redirect(site_url('vendas/gerenciar/'));
            }
        }

        $this->vendas_model->delete('itens_de_vendas', 'vendas_id', $id);
        $this->vendas_model->delete('vendas', 'idVendas', $id);
        if ((int) $venda->faturado === 1) {
            $this->vendas_model->delete('lancamentos', 'descricao', "Fatura de Venda - #${id}");
        }

        log_info('Removeu uma venda. ID: ' . $id);

        $this->session->set_flashdata('success', 'Venda excluída com sucesso!');
        redirect(site_url('vendas/gerenciar/'));
    }

    public function autoCompleteProduto()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->vendas_model->autoCompleteProduto($q);
        }
    }

    public function autoCompleteCliente()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->vendas_model->autoCompleteCliente($q);
        }
    }

    public function autoCompleteUsuario()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->vendas_model->autoCompleteUsuario($q);
        }
    }

    public function adicionarProduto()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar vendas.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('quantidade', 'Quantidade', 'trim|required');
        $this->form_validation->set_rules('idProduto', 'Produto', 'trim|required');
        $this->form_validation->set_rules('idVendasProduto', 'Vendas', 'trim|required');

        $editavel = $this->vendas_model->isEditable($this->input->post('idVendasProduto'));
        if (!$editavel) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode(['result' => false, 'messages' => '<br /><br /> <strong>Motivo:</strong> Venda já faturada']));
        }

        if ($this->form_validation->run() == false) {
            echo json_encode(['result' => false]);
        } else {
            $preco = $this->input->post('preco');
            $quantidade = $this->input->post('quantidade');
            $subtotal = $preco * $quantidade;
            $produto = $this->input->post('idProduto');
            $data = [
                'quantidade' => $quantidade,
                'subTotal' => $subtotal,
                'produtos_id' => $produto,
                'preco' => $preco,
                'vendas_id' => $this->input->post('idVendasProduto'),
            ];

            if ($this->vendas_model->add('itens_de_vendas', $data) == true) {
                $this->load->model('produtos_model');

                if ($this->data['configuration']['control_estoque']) {
                    $this->produtos_model->updateEstoque($produto, $quantidade, '-');
                }

                $this->db->set('desconto', 0.00);
                $this->db->set('valor_desconto', 0.00);
                $this->db->where('idVendas', $this->input->post('idVendasProduto'));
                $this->db->update('vendas');

                log_info('Adicionou produto a uma venda.');

                echo json_encode(['result' => true]);
            } else {
                echo json_encode(['result' => false]);
            }
        }
    }

    public function excluirProduto()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar Vendas');
            redirect(base_url());
        }

        $editavel = $this->vendas_model->isEditable($this->input->post('idVendas'));
        if (!$editavel) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(422)
                ->set_output(json_encode(['result' => false, 'messages' => '<br /><br /> <strong>Motivo:</strong> Venda já faturada']));
        }

        $ID = $this->input->post('idProduto');
        if ($this->vendas_model->delete('itens_de_vendas', 'idItens', $ID) == true) {
            $quantidade = $this->input->post('quantidade');
            $produto = $this->input->post('produto');

            $this->load->model('produtos_model');

            if ($this->data['configuration']['control_estoque']) {
                $this->produtos_model->updateEstoque($produto, $quantidade, '+');
            }

            $this->db->set('desconto', 0.00);
            $this->db->set('valor_desconto', 0.00);
            $this->db->where('idVendas', $this->input->post('idVendas'));
            $this->db->update('vendas');

            log_info('Removeu produto de uma venda.');
            echo json_encode(['result' => true]);
        } else {
            echo json_encode(['result' => false]);
        }
    }

    public function adicionarDesconto()
    {
        if ($this->input->post('desconto') == "") {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['messages' => 'Campo desconto vazio']));
        } else {
            $idVendas = $this->input->post('idVendas');
            $data = [
                'desconto' => $this->input->post('desconto'),
                'valor_desconto' => $this->input->post('resultado')
            ];
            $editavel = $this->vendas_model->isEditable($idVendas);
            if (!$editavel) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['result' => false, 'messages', 'Desconto não pode ser adiciona. Venda não ja Faturada/Cancelada']));
            }
            if ($this->vendas_model->edit('vendas', $data, 'idVendas', $idVendas) == true) {
                log_info('Adicionou um desconto na Venda. ID: ' . $idVendas);
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(201)
                    ->set_output(json_encode(['result' => true, 'messages' => 'Desconto adicionado com sucesso!']));
            } else {
                log_info('Ocorreu um erro ao tentar adiciona desconto a Venda: ' . $idVendas);
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['result' => false, 'messages', 'Ocorreu um erro ao tentar adiciona desconto a Venda.']));
            }
        }
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode(['result' => false, 'messages', 'Ocorreu um erro ao tentar adiciona desconto a OS.']));
    }

    public function faturar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar Vendas');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('receita') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $venda_id = $this->input->post('vendas_id');
            $vencimento = $this->input->post('vencimento');
            $recebimento = $this->input->post('recebimento');

            try {
                $vencimento = explode('/', $vencimento);
                $vencimento = $vencimento[2] . '-' . $vencimento[1] . '-' . $vencimento[0];

                if ($recebimento != null) {
                    $recebimento = explode('/', $recebimento);
                    $recebimento = $recebimento[2] . '-' . $recebimento[1] . '-' . $recebimento[0];
                }
            } catch (Exception $e) {
                $vencimento = date('Y/m/d');
            }
            $vendas = $this->vendas_model->getById($venda_id);
            $data = [
                'vendas_id' => $venda_id,
                'descricao' => set_value('descricao'),
                'valor' => $this->input->post('valor'),
                'desconto' => $vendas->desconto,
                'valor_desconto' => $vendas->valor_desconto,
                'clientes_id' => $this->input->post('clientes_id'),
                'data_vencimento' => $vencimento,
                'data_pagamento' => $recebimento,
                'baixado' => $this->input->post('recebido') == 1 ? true : false,
                'cliente_fornecedor' => set_value('cliente'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => $this->input->post('tipo'),
                'usuarios_id' => $this->session->userdata('id'),
            ];

            if ($this->vendas_model->add('lancamentos', $data) == true) {
                $venda = $this->input->post('vendas_id');

                $this->db->set('faturado', 1);
                $this->db->set('valorTotal', $this->input->post('valor'));
                $this->db->where('idVendas', $venda);
                $this->db->update('vendas');

                log_info('Faturou uma venda.');

                $this->session->set_flashdata('success', 'Venda faturada com sucesso!');
                $json = ['result' => true];
                echo json_encode($json);
                die();
            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar faturar venda.');
                $json = ['result' => false];
                echo json_encode($json);
                die();
            }
        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar faturar venda.');
        $json = ['result' => false];
        echo json_encode($json);
    }
}
