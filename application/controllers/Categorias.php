<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Categorias extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('categorias_model');
        $this->data['menuCategorias'] = 'financeiro';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar categorias.');
            redirect(base_url());
        }

        $pesquisa = $this->input->get('pesquisa');
        $tipo = $this->input->get('tipo'); // receita ou despesa

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('categorias/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->categorias_model->count('categorias', $pesquisa);
        if($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/categorias")."?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->categorias_model->get('categorias', '*', $pesquisa, $this->data['configuration']['per_page'], $this->uri->segment(3));
        $this->data['tipo'] = $tipo;

        $this->data['view'] = 'categorias/categorias';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar categorias.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('categorias') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'categoria' => set_value('categoria'),
                'tipo' => set_value('tipo'),
                'status' => 1,
                'cadastro' => date('Y-m-d'),
            ];

            if ($this->categorias_model->add('categorias', $data) == true) {
                $this->session->set_flashdata('success', 'Categoria adicionada com sucesso!');
                log_info('Adicionou uma categoria.');
                redirect(site_url('categorias/'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->data['view'] = 'categorias/adicionarCategoria';

        return $this->layout();
    }

    public function editar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3)) || ! $this->categorias_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Categoria não encontrada ou parâmetro inválido.');
            redirect('categorias/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar categorias.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('categorias') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'categoria' => $this->input->post('categoria'),
                'tipo' => $this->input->post('tipo'),
                'status' => $this->input->post('status') ? 1 : 0,
            ];

            if ($this->categorias_model->edit('categorias', $data, 'idCategorias', $this->input->post('idCategorias')) == true) {
                $this->session->set_flashdata('success', 'Categoria editada com sucesso!');
                log_info('Alterou uma categoria. ID' . $this->input->post('idCategorias'));
                redirect(site_url('categorias/editar/') . $this->input->post('idCategorias'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
            }
        }

        $this->data['result'] = $this->categorias_model->getById($this->uri->segment(3));
        $this->data['view'] = 'categorias/editarCategoria';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir categorias.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir categoria.');
            redirect(site_url('categorias/gerenciar/'));
        }

        // Verificar se há lançamentos vinculados
        if ($this->categorias_model->hasLancamentos($id)) {
            $this->session->set_flashdata('error', 'Não é possível excluir esta categoria pois existem lançamentos vinculados a ela.');
            redirect(site_url('categorias/gerenciar/'));
        }

        $this->categorias_model->delete('categorias', 'idCategorias', $id);
        log_info('Removeu uma categoria. ID' . $id);

        $this->session->set_flashdata('success', 'Categoria excluída com sucesso!');
        redirect(site_url('categorias/gerenciar/'));
    }

    public function getAll()
    {
        $categorias = $this->categorias_model->getAll();
        header('Content-Type: application/json');
        echo json_encode($categorias);
    }

    public function getByTipo()
    {
        $tipo = $this->input->get('tipo');
        if ($tipo) {
            $categorias = $this->categorias_model->getByTipo($tipo);
        } else {
            $categorias = $this->categorias_model->getAll();
        }
        header('Content-Type: application/json');
        echo json_encode($categorias);
    }
}

