<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Veiculos extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('veiculos_model');
        $this->data['menuVeiculos'] = 'Veiculos';
    }

    public function index() {
        $this->gerenciar();
    }

    public function gerenciar() {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vVeiculo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar veículos.');
            redirect(base_url());
        }

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('veiculos/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->veiculos_model->count('produtos');

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->veiculos_model->get('veiculos', '*', '', $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'veiculos/veiculos';
        return $this->layout();
    }

    public function adicionar() {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aVeiculo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar veículos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('veiculos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = array(
                'clientes_id' => $this->input->post('clientes_id'),
                'placa' => $this->input->post('placa'),
                'anoModelo' => $this->input->post('anoModelo'),
                'anoFabricacao' => $this->input->post('anoFabricacao'),
                'marcaFabricante' => $this->input->post('marcaFabricante'),
                'modelo' => $this->input->post('modelo'),
                'chassi' => $this->input->post('chassi'),
            );

            if ($this->veiculos_model->add('veiculos', $data) == true) {
                $this->session->set_flashdata('success', 'Veículo adicionado com sucesso!');
                log_info('Adicionou um veículo');
                redirect(site_url('veiculos/adicionar/'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Um erro ocorreu.</p></div>';
            }
        }
        $this->data['view'] = 'veiculos/adicionarVeiculo';
        return $this->layout();
    }

    public function editar() {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eVeiculo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar veículos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('veiculos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = array(
                'placa' => $this->input->post('placa'),
                'anoModelo' => $this->input->post('anoModelo'),
                'anoFabricacao' => $this->input->post('anoFabricacao'),
                'marcaFabricante' => $this->input->post('marcaFabricante'),
                'modelo' => $this->input->post('modelo'),
                'chassi' => $this->input->post('chassi'),
                'clientes_id' => $this->input->post('clientes_id'),
            );

            if ($this->veiculos_model->edit('veiculos', $data, 'idVeiculos', $this->input->post('idVeiculos')) == true) {
                $this->session->set_flashdata('success', 'Veículo editado com sucesso!');
                log_info('Alterou um veículo. ID: ' . $this->input->post('idVeiculos'));
                redirect(site_url('veiculos/editar/') . $this->input->post('idVeiculos'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Um erro ocorreu</p></div>';
            }
        }

        $this->data['result'] = $this->veiculos_model->getById($this->uri->segment(3));
        $this->data['view'] = 'veiculos/editarVeiculo';
        return $this->layout();
    }

    public function visualizar() {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vVeiculo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar veículos.');
            redirect(base_url());
        }

        $this->data['result'] = $this->veiculos_model->getById($this->uri->segment(3));

        if ($this->data['result'] == null) {
            $this->session->set_flashdata('error', 'Veículo não encontrado.');
            redirect(site_url('veiculos/editar/') . $this->input->post('idVeiculos'));
        }

        $this->data['view'] = 'veiculos/vizualizarVeiculo';
        return $this->layout();
    }

    public function excluir() {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dVeiculo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir veículos.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir veículo.');
            redirect(base_url() . 'index.php/veiculos/gerenciar/');
        }

        $this->veiculos_model->delete('veiculos', 'idVeiculos', $id);

        log_info('Removeu um veículo. ID: ' . $id);

        $this->session->set_flashdata('success', 'Veículo excluido com sucesso!');
        redirect(site_url('veiculos/gerenciar/'));
    }

}
