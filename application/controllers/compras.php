<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Compras extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        $this->load->helper(array('form', 'codegen_helper'));
        $this->load->model('compras_model', '', true);
        $this->data['menuCompras'] = 'Compras';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar compras.');
            redirect(base_url());
        }

        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'index.php/compras/gerenciar/';
        $config['total_rows'] = $this->compras_model->count('compras');
        $config['per_page'] = 10;
        $config['next_link'] = 'Próxima';
        $config['prev_link'] = 'Anterior';
        $config['full_tag_open'] = '<div class="pagination alternate"><ul>';
        $config['full_tag_close'] = '</ul></div>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li><a style="color: #2D335B"><b>';
        $config['cur_tag_close'] = '</b></a></li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['first_link'] = 'Primeira';
        $config['last_link'] = 'Última';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';

        $this->pagination->initialize($config);

        $this->data['results'] = $this->compras_model->get('compras', 'idCompras,idOs,solicitante,comprador,fornecedor,descricao,valor,dataPedido,dataPrevista,rastreio', '', $config['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'compras/compras';
        $this->load->view('tema/topo', $this->data);

    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar compras.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('compras') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $valor = $this->input->post('valor');
            $valor = str_replace(",", "", $valor);
            $faturado = '1';

            $dataPedido = $this->input->post('dataPedido');
            $dataPrevista = $this->input->post('dataPrevista');

            if ($dataPedido != null) {
                $dataPedido = explode('/', $dataPedido);
                $dataPedido = $dataPedido[2] . '-' . $dataPedido[1] . '-' . $dataPedido[0];
            } else {
                $dataPedido = date('Y/m/d');
            }

            if ($dataPrevista != null) {
                $dataPrevista = explode('/', $dataPrevista);
                $dataPrevista = $dataPrevista[2] . '-' . $dataPrevista[1] . '-' . $dataPrevista[0];
            } else {
                $dataPrevista = date('Y/m/d');
            }

            $data = array(
                'idOs' => $this->input->post('idOs'),
                'solicitante' => $this->input->post('solicitante'),
                'comprador' => $this->input->post('comprador'),
                'fornecedor' => $this->input->post('fornecedor'),
                'descricao' => $this->input->post('descricao'),
                'valor' => $valor,
                'dataPedido' => $dataPedido,
                'dataPrevista' => $dataPrevista,
                'rastreio' => $this->input->post('rastreio'),
                'status' => $this->input->post('status'),
                'faturado' => $faturado,
            );

            if ($this->compras_model->add('compras', $data) == true) {
                $this->session->set_flashdata('success', 'Compra adicionada com sucesso!');
                redirect(base_url() . 'index.php/compras/adicionar/');
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }
        $this->data['view'] = 'compras/adicionarCompra';
        $this->load->view('tema/topo', $this->data);

    }

    public function editar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar compras.');
            redirect(base_url());
        }
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('compras') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $valor = $this->input->post('valor');
            $valor = str_replace(",", "", $valor);
            $faturado = $this->input->post('faturado');

            $dataPedido = $this->input->post('dataPedido');
            $dataPrevista = $this->input->post('dataPrevista');

            try {

                $dataPedido = explode('/', $dataPedido);
                $dataPedido = $dataPedido[2] . '-' . $dataPedido[1] . '-' . $dataPedido[0];

                $dataPrevista = explode('/', $dataPrevista);
                $dataPrevista = $dataPrevista[2] . '-' . $dataPrevista[1] . '-' . $dataPrevista[0];

            } catch (Exception $e) {
                $dataPedido = date('Y/m/d');
            }

            $data = array(
                'idOs' => $this->input->post('idOs'),
                'solicitante' => $this->input->post('solicitante'),
                'comprador' => $this->input->post('comprador'),
                'fornecedor' => $this->input->post('fornecedor'),
                'descricao' => $this->input->post('descricao'),
                'valor' => $valor,
                'dataPedido' => $dataPedido,
                'dataPrevista' => $dataPrevista,
                'rastreio' => $this->input->post('rastreio'),
                'status' => $this->input->post('status'),
                'faturado' => $faturado,
            );

            if ($this->compras_model->edit('compras', $data, 'idCompras', $this->input->post('idCompras')) == true) {
                $this->session->set_flashdata('success', 'Compra editada com sucesso!');
                redirect(base_url() . 'index.php/compras/editar/' . $this->input->post('idCompras'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->data['result'] = $this->compras_model->getById($this->uri->segment(3));

        $this->data['view'] = 'compras/editarCompra';
        $this->load->view('tema/topo', $this->data);

    }

    public function excluir()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir compras.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {

            $this->session->set_flashdata('error', 'Erro ao tentar excluir compra.');
            redirect(base_url() . 'index.php/compras/gerenciar/');
        }

        $this->db->where('compra_id', $id);
        $this->db->delete('compras_os');

        $this->compras_model->delete('compras', 'idCompras', $id);

        $this->session->set_flashdata('success', 'Compra excluida com sucesso!');
        redirect(base_url() . 'index.php/compras/gerenciar/');
    }

    public function faturar()
    {

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('comprasDespesa') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {

            $vencimento = $this->input->post('pedido');
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

            $data = array(
                'descricao' => set_value('descricaoFaturar'),
                'valor' => $this->input->post('valorFaturar'),
                'data_pagamento' => $vencimento, // as datas estão invertidas
                'data_vencimento' => $pagamento != null ? $pagamento : date('Y-m-d'),
                'baixado' => $this->input->post('pago'),
                'cliente_fornecedor' => set_value('fornecedorFaturar'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => $this->input->post('tipo'),
            );

            if ($this->compras_model->add('lancamentos', $data) == true) {

                $os = $this->input->post('idCompras');

                $this->db->set('faturado', 1);
                $this->db->set('valor', $this->input->post('valorFaturar'));
                $this->db->where('idCompras', $os);
                $this->db->update('compras');

                $this->session->set_flashdata('success', 'Compra faturada com sucesso!');
                $json = array('result' => true);
                echo json_encode($json);
                die();
            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar faturar a Compra 1.');
                $json = array('result' => false);
                echo json_encode($json);
                die();
            }
        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar faturar a Compra 2.');
        $json = array('result' => false);
        echo json_encode($json);

    }
}
