<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Servicos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('servicos_model');
        $this->data['menuServicos'] = 'Serviços';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! authorize('vServico', 'Você não tem permissão para visualizar serviços.')) {
            return;
        }

        $pesquisa = $this->input->get('pesquisa');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('servicos/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->servicos_model->count('servicos');
        if ($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/servicos")."\?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->servicos_model->get('servicos', '*', $pesquisa, $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'servicos/servicos';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! authorize('aServico', 'Você não tem permissão para adicionar serviços.')) {
            return;
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('servicos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $preco = $this->input->post('preco');
            $preco = str_replace(',', '', $preco);

            $data = [
                'nome' => set_value('nome'),
                'descricao' => set_value('descricao'),
                'preco' => $preco,
            ];

            if ($this->servicos_model->add('servicos', $data) == true) {
                $this->session->set_flashdata('success', 'Serviço adicionado com sucesso!');
                log_info('Adicionou um serviço');
                respond_redirect(site_url('servicos/adicionar/'));

                return;
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }
        $this->data['view'] = 'servicos/adicionarServico';

        return $this->layout();
    }

    public function editar()
    {
        $id = $this->uri->segment(3);

        if (! $id || ! is_numeric($id) || ! $this->servicos_model->getById($id)) {
            $this->session->set_flashdata('error', 'Serviço não encontrado ou parâmetro inválido.');
            respond_redirect(site_url('servicos/gerenciar'));

            return;
        }

        if (! authorize('eServico', 'Você não tem permissão para editar serviços.')) {
            return;
        }
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('servicos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $preco = $this->input->post('preco');
            $preco = str_replace(',', '', $preco);
            $data = [
                'nome' => $this->input->post('nome'),
                'descricao' => $this->input->post('descricao'),
                'preco' => $preco,
            ];

            if ($this->servicos_model->edit('servicos', $data, 'idServicos', $id) == true) {
                $this->session->set_flashdata('success', 'Serviço editado com sucesso!');
                log_info('Alterou um serviço. ID: ' . $id);
                respond_redirect(site_url('servicos/editar/') . $id);

                return;
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um errro.</p></div>';
            }
        }

        $this->data['result'] = $this->servicos_model->getById($id);

        $this->data['view'] = 'servicos/editarServico';

        return $this->layout();
    }

    public function excluir()
    {
        if (! authorize('dServico', 'Você não tem permissão para excluir serviços.')) {
            return;
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir serviço.');
            respond_redirect(site_url('servicos/gerenciar/'));

            return;
        }

        // As duas exclusões são uma operação só: o item de OS referencia o
        // serviço, então uma falha entre as duas deixaria o par pela metade. O
        // `trans_rollback()` do ramo de erro desfaz a exclusão dos filhos quando
        // o serviço não sai.
        $this->db->trans_start();

        $this->servicos_model->delete('servicos_os', 'servicos_id', $id);

        if ($this->servicos_model->delete('servicos', 'idServicos', $id) === false) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Erro ao tentar excluir serviço.');
            respond_redirect(site_url('servicos/gerenciar/'));

            return;
        }

        if (! $this->db->trans_complete()) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir serviço.');
            respond_redirect(site_url('servicos/gerenciar/'));

            return;
        }

        log_info('Removeu um serviço. ID: ' . $id);

        $this->session->set_flashdata('success', 'Serviço excluido com sucesso!');
        respond_redirect(site_url('servicos/gerenciar/'));
    }
}
