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
        $this->load->model('pagamentos_model');
        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['pagamento'] = $this->pagamentos_model->getPagamentos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        if ($this->data['pagamento']) {
            $this->load->library('Gateways/MercadoPago', null, 'MercadoPago');
        }

        $this->data['view'] = 'vendas/visualizarVenda';
        return $this->layout();
    }


    public function gerarPagamentoGerencianetBoleto()
    {
        $cobrancas_vendas = $this->vendas_model->getCobrancas($this->input->post('idVenda'));
        if ($cobrancas_vendas != null && count($cobrancas_vendas) >= 1) {
            $this->session->set_flashdata('error', 'Já existe cobrança vículadas neste pagamento, verifique em Financeiro/Cobranças');
            $json = ['code' => 4001, 'error' => 'server_error' , 'errorDescription' => 'Já existe uma cobrança desta venda'];

            print_r(json_encode($json));
            return;
        }

        $this->load->library('Gateways/GerencianetSdk', null, 'GerencianetSdk');
        $this->load->model('pagamentos_model');
        $pagamentoM = $this->pagamentos_model->getPagamentos($this->uri->segment(3));

        $pagamento = $this->GerencianetSdk->gerarBoleto(
            $pagamentoM->client_id,
            $pagamentoM->client_secret,
            $this->input->post('nomeCliente'),
            $this->input->post('emailCliente'),
            $this->input->post('documentoCliente'),
            $this->input->post('celular_cliente'),
            $this->input->post('ruaCliente'),
            $this->input->post('numeroCliente'),
            $this->input->post('bairroCliente'),
            $this->input->post('cidadeCliente'),
            $this->input->post('estadoCliente'),
            $this->input->post('cepCliente'),
            $this->input->post('idVenda'),
            $this->input->post('titleVenda'),
            $this->input->post('totalValor'),
            intval($this->input->post('quantidade'))
        );
        $vendas = $this->vendas_model->getById($this->input->post('idVenda'));
        $obj = json_decode($pagamento);
        if ($obj->code == 200) {
            $data = [
                'barcode' => $obj->data->barcode,
                'link' => $obj->data->link,
                'pdf' => $obj->data->pdf->charge,
                'expire_at' => $obj->data->expire_at,
                'charge_id' => $obj->data->charge_id,
                'status' => $obj->data->status,
                'total' =>  $this->input->post('totalValor'),
                'payment' => $obj->data->payment,
                'vendas_id' => $this->input->post('idVenda'),
                'clientes_id' => $vendas->idClientes,
            ];
            if ($this->vendas_model->add('cobrancas', $data) == true) {
                log_info('Cobrança criada com suceso. ID: ' . $obj->data->charge_id);
                $this->session->set_flashdata('success', 'Cobrança criada com sucesso!');
            }
        } else {
            $json = ['code' => $obj->code, 'error' => $obj->error , 'errorDescription' => $obj->errorDescription ];
            return print_r(json_encode($json));
        }
        print_r($pagamento);
    }

    public function gerarPagamentoGerencianetLink()
    {
        $cobrancas_vendas = $this->vendas_model->getCobrancas($this->input->post('idVenda'));
        if ($cobrancas_vendas != null && count($cobrancas_vendas) >= 1) {
            $this->session->set_flashdata('error', 'Já existe cobrança vículadas neste pagamento, verifique em Financeiro/Cobranças');
            $json = ['code' => 4001, 'error' => 'server_error' , 'errorDescription' => 'Já existe uma cobrança desta venda'];

            print_r(json_encode($json));
            return;
        }

        $this->load->library('Gateways/GerencianetSdk', null, 'GerencianetSdk');
        $this->load->model('pagamentos_model');
        $pagamentoM = $this->pagamentos_model->getPagamentos($this->uri->segment(3));

        $pagamento = $this->GerencianetSdk->gerarLink(
            $pagamentoM->client_id,
            $pagamentoM->client_secret,
            $this->input->post('idVenda'),
            $this->input->post('titleLink'),
            $this->input->post('totalValor'),
            intval($this->input->post('quantidade'))
        );
        $vendas = $this->vendas_model->getById($this->input->post('idVenda'));
        $obj = json_decode($pagamento);
        if ($obj->code == 200) {
            $data = [
                'charge_id' => $obj->data->charge_id,
                'status' => $obj->data->status,
                'total' =>  $this->input->post('totalValor'),
                'custom_id' => $obj->data->custom_id,
                'payment_url' => $obj->data->payment_url,
                'payment_method' => $obj->data->payment_method,
                'conditional_discount_date' => $obj->data->conditional_discount_date,
                'request_delivery_address' => $obj->data->request_delivery_address,
                'message' => $obj->data->message,
                'expire_at' => $obj->data->expire_at,
                'created_at' => $obj->data->created_at,
                'vendas_id' => $this->input->post('idVenda'),
                'clientes_id' => $vendas->idClientes,
            ];
            if ($this->vendas_model->add('cobrancas', $data) == true) {
                log_info('Cobrança criada com suceso. ID: ' . $obj->data->charge_id);
                $this->session->set_flashdata('success', 'Cobrança criada com sucesso!');
            }
        } else {
            $json = ['code' => $obj->code, 'error' => $obj->error , 'errorDescription' => $obj->errorDescription ];
            return print_r(json_encode($json));
        }
        print_r($pagamento);
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
        $venda = $this->vendas_model->getById($id);
        if ($venda == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir venda.');
            redirect(site_url('vendas/gerenciar/'));
        }

        $this->vendas_model->delete('cobrancas', 'vendas_id', $id);
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

        $ID = $this->input->post('idProduto');
        if ($this->vendas_model->delete('itens_de_vendas', 'idItens', $ID) == true) {
            $quantidade = $this->input->post('quantidade');
            $produto = $this->input->post('produto');

            $this->load->model('produtos_model');

            if ($this->data['configuration']['control_estoque']) {
                $this->produtos_model->updateEstoque($produto, $quantidade, '+');
            }

            log_info('Removeu produto de uma venda.');
            echo json_encode(['result' => true]);
        } else {
            echo json_encode(['result' => false]);
        }
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

            $data = [
                'vendas_id' => $venda_id,
                'descricao' => set_value('descricao'),
                'valor' => $this->input->post('valor'),
                'clientes_id' => $this->input->post('clientes_id'),
                'data_vencimento' => $vencimento,
                'data_pagamento' => $recebimento,
                'baixado' => $this->input->post('recebido'),
                'cliente_fornecedor' => set_value('cliente'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => $this->input->post('tipo'),
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
