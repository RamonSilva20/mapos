<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * author: Ramon Silva
 * email: silva018-mg@yahoo.com.br
 *
 */

class Produtos extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if ((!session_id()) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        $this->load->model('Produtos_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'vProduto')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view') . ' ' . $this->lang->line('products'));
            redirect(base_url());
        }

        $data['view'] = 'produtos/produtos_list';
        $this->load->view('tema/topo', $data, false);
    }

    public function datatable()
    {

        if (!$this->permission->check($this->session->userdata('permissao'), 'vProduto')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view') . ' ' . $this->lang->line('products'));
            redirect(base_url());
        }

        $this->load->model('Produtos_model');
        $result_data = $this->Produtos_model->get_datatables();
        $data = array();

        foreach ($result_data as $row) {
            $line = array();
            $line[] = '<input type="checkbox" class="remove" name="item_id[]" value="' . $row->idProdutos . '">';

            $line[] = $row->idProdutos;
            $line[] = $row->descricao;
            $line[] = $row->unidade;
            $line[] = $row->precoCompra;
            $line[] = $row->precoVenda;
            $line[] = $row->estoque;
            $line[] = $row->estoqueMinimo;

            $line[] = '<a href="' . site_url('produtos/read/' . $row->idProdutos) . '" class="btn btn-dark" title="' . $this->lang->line('app_view') . '"><i class="fa fa-eye"></i> </a>
                       <a href="' . site_url('produtos/update/' . $row->idProdutos) . '" class="btn btn-info" title="' . $this->lang->line('app_edit') . '"><i class="fa fa-edit"></i></a>
                       <a href="' . site_url('produtos/delete/' . $row->idProdutos) . '" class="btn btn-danger delete" title="' . $this->lang->line('app_delete') . '"><i class="fa fa-remove"></i></a>';
            $data[] = $line;
        }

        $output = array(
            'draw' => intval($this->input->post('draw')),
            'recordsTotal' => $this->Produtos_model->get_all_data(),
            'recordsFiltered' => $this->Produtos_model->get_filtered_data(),
            'data' => $data,
        );
        echo json_encode($output);
    }

    public function read($id)
    {

        if (!is_numeric($id)) {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('produtos');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'vProduto')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view') . ' ' . $this->lang->line('products'));
            redirect(base_url());
        }

        $row = $this->Produtos_model->get($id);
        if ($row) {
            $data = array(
                'idProdutos' => $row->idProdutos,
                'descricao' => $row->descricao,
                'unidade' => $row->unidade,
                'precoCompra' => $row->precoCompra,
                'precoVenda' => $row->precoVenda,
                'estoque' => $row->estoque,
                'estoqueMinimo' => $row->estoqueMinimo,
                'saida' => $row->saida,
                'entrada' => $row->entrada,
            );

            $data['view'] = 'produtos/produtos_read';
            $this->load->view('tema/topo', $data, false);
        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('produtos'));
        }
    }

    public function create()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'aProduto')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_add') . ' ' . $this->lang->line('products'));
            redirect(base_url());
        }

        $data = array(
            'button' => '<i class="fa fa-plus"></i> ' . $this->lang->line('app_create'),
            'action' => site_url('produtos/create_action'),
            'idProdutos' => set_value('idProdutos'),
            'descricao' => set_value('descricao'),
            'unidade' => set_value('unidade'),
            'precoCompra' => set_value('precoCompra'),
            'precoVenda' => set_value('precoVenda'),
            'estoque' => set_value('estoque'),
            'estoqueMinimo' => set_value('estoqueMinimo'),
            'saida' => set_value('saida'),
            'entrada' => set_value('entrada'),
        );

        $data['view'] = 'produtos/produtos_form';
        $this->load->view('tema/topo', $data, false);

    }

    public function create_action()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'aProduto')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_add') . ' ' . $this->lang->line('products'));
            redirect(base_url());
        }

        $this->_rules();

        if ($this->form_validation->run() == false) {
            $this->create();
        } else {
            $data = array(
                'descricao' => $this->input->post('descricao', true),
                'unidade' => $this->input->post('unidade', true),
                'precoCompra' => $this->input->post('precoCompra', true),
                'precoVenda' => $this->input->post('precoVenda', true),
                'estoque' => $this->input->post('estoque', true),
                'estoqueMinimo' => $this->input->post('estoqueMinimo', true),
                'saida' => $this->input->post('saida', true) ? 1 : 0,
                'entrada' => $this->input->post('entrada', true) ? 1 : 0,
            );

            $this->Produtos_model->insert($data);
            $this->session->set_flashdata('success', $this->lang->line('app_add_message'));
            redirect(site_url('produtos'));
        }
    }

    public function update($id)
    {
        if (!is_numeric($id)) {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('produtos');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'eProduto')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_edit') . ' ' . $this->lang->line('products'));
            redirect(base_url());
        }

        $row = $this->Produtos_model->get($id);

        if ($row) {
            $data = array(
                'button' => '<i class="fa fa-edit"></i> ' . $this->lang->line('app_edit'),
                'action' => site_url('produtos/update_action'),
                'idProdutos' => set_value('idProdutos', $row->idProdutos),
                'descricao' => set_value('descricao', $row->descricao),
                'unidade' => set_value('unidade', $row->unidade),
                'precoCompra' => set_value('precoCompra', $row->precoCompra),
                'precoVenda' => set_value('precoVenda', $row->precoVenda),
                'estoque' => set_value('estoque', $row->estoque),
                'estoqueMinimo' => set_value('estoqueMinimo', $row->estoqueMinimo),
                'saida' => set_value('saida', $row->saida),
                'entrada' => set_value('entrada', $row->entrada),
            );
            $data['view'] = 'produtos/produtos_form';
            $this->load->view('tema/topo', $data, false);

        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('produtos'));
        }
    }

    public function update_action()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'eProduto')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_edit') . ' ' . $this->lang->line('products'));
            redirect(base_url());
        }

        $this->_rules();

        if ($this->form_validation->run() == false) {
            $this->update($this->input->post('idProdutos', true));
        } else {
            $data = array(
                'descricao' => $this->input->post('descricao', true),
                'unidade' => $this->input->post('unidade', true),
                'precoCompra' => $this->input->post('precoCompra', true),
                'precoVenda' => $this->input->post('precoVenda', true),
                'estoque' => $this->input->post('estoque', true),
                'estoqueMinimo' => $this->input->post('estoqueMinimo', true),
                'saida' => $this->input->post('saida', true) ? 1 : 0,
                'entrada' => $this->input->post('entrada', true) ? 1 : 0,
            );

            $this->Produtos_model->update($data, $this->input->post('idProdutos', true));
            $this->session->set_flashdata('success', $this->lang->line('app_edit_message'));
            redirect(site_url('produtos'));
        }
    }

    public function delete($idProdutos)
    {
        if (!is_numeric($idProdutos)) {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('produtos');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'dProduto')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_delete') . ' ' . $this->lang->line('products'));
            redirect(base_url());
        }

        $row = $this->Produtos_model->get($idProdutos);
        $ajax = $this->input->get('ajax');

        if ($row) {

            $this->Produtos_model->delete_linked($idProdutos);

            if ($this->Produtos_model->delete($idProdutos)) {

                if ($ajax) {
                    echo json_encode(array('result' => true, 'message' => $this->lang->line('app_delete_message')));die();
                }
                $this->session->set_flashdata('success', $this->lang->line('app_delete_message'));
                redirect(site_url('produtos'));
            } else {

                if ($ajax) {
                    echo json_encode(array('result' => false, 'message' => $this->lang->line('app_error')));die();
                }

                $this->session->set_flashdata('error', $this->lang->line('app_error'));
                redirect(site_url('produtos'));
            }

        } else {

            if ($ajax) {
                echo json_encode(array('result' => false, 'message' => $this->lang->line('app_not_found')));die();
            }
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('produtos'));
        }

    }

    public function delete_many()
    {

        if (!$this->permission->check($this->session->userdata('permissao'), 'dProduto')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_delete') . ' ' . $this->lang->line('products'));
            redirect(base_url());
        }

        $items = $this->input->post('item_id[]');

        if ($items) {

            $verify = implode('', $items);
            if (is_numeric($verify)) {

                $this->Produtos_model->delete_linked($items);

                $result = $this->Produtos_model->delete_many($items);
                if ($result) {
                    echo json_encode(array('result' => true, 'message' => $this->lang->line('app_delete_message_many')));die();
                } else {
                    echo json_encode(array('result' => false, 'message' => $this->lang->line('app_error')));die();
                }

            } else {
                echo json_encode(array('result' => false, 'message' => $this->lang->line('app_data_not_supported')));die();
            }
        }

        echo json_encode(array('result' => false, 'message' => $this->lang->line('app_empty_data')));die();

    }

    public function _rules()
    {
        $this->form_validation->set_rules('descricao', '<b>' . $this->lang->line('product_name') . '</b>', 'trim|required');
        $this->form_validation->set_rules('unidade', '<b>' . $this->lang->line('product_unity') . '</b>', 'trim|required');
        $this->form_validation->set_rules('precoCompra', '<b>' . $this->lang->line('product_buy_price') . '</b>', 'trim|required|numeric');
        $this->form_validation->set_rules('precoVenda', '<b>' . $this->lang->line('product_sell_price') . '</b>', 'trim|required|numeric');
        $this->form_validation->set_rules('estoque', '<b>' . $this->lang->line('product_stock') . '</b>', 'trim|required');
        $this->form_validation->set_rules('estoqueMinimo', '<b>' . $this->lang->line('product_min_stock') . '</b>', 'trim|required');
        $this->form_validation->set_rules('saida', '<b>' . $this->lang->line('product_out') . '</b>', 'trim');
        $this->form_validation->set_rules('entrada', '<b>' . $this->lang->line('product_in') . '</b>', 'trim');

        $this->form_validation->set_rules('idProdutos', 'idProdutos', 'trim');
        $this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file Produtos.php */
/* Location: ./application/controllers/Produtos.php */
