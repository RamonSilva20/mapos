<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Garantias extends CI_Controller
{

    /**
     * author: Wilmerson Felipe
     * email: will.phelipe@gmail.com
     *
     */

    public function __construct()
    {
        parent::__construct();

        if ((!session_id()) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        $this->load->helper(array('form', 'codegen_helper'));
        $this->load->model('garantias_model', '', true);
        $this->data['menuGarantia'] = 'Garantias';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar Termo de Garantia.');
            redirect(base_url());
        }

        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'index.php/garantias/gerenciar/';
        $config['total_rows'] = $this->garantias_model->count('garantias');
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

        $this->data['results'] = $this->garantias_model->get('garantias', '*', '', $config['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'garantias/garantias';
        $this->load->view('tema/topo', $this->data);
    }

    public function adicionar()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar Termo de Garantia.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('garantias') == false) {
            $this->data['custom_error'] = (validation_errors() ? true : false);
        } else {

            $data = array(
                'dataGarantia' => date('Y/m/d'),
                'refGarantia' => $this->input->post('refGarantia'),
                'textoGarantia' => $this->input->post('textoGarantia'),
                'usuarios_id' => $this->session->userdata('id'),
            );

            if (is_numeric($id = $this->garantias_model->add('garantias', $data, true))) {
                log_info('Adicionou uma garantia');
                $this->session->set_flashdata('success', 'Termo de Garantia adicionado com sucesso.');
                redirect('garantias/editar/' . $id);
            } else {

                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->data['view'] = 'garantias/adicionarGarantia';
        $this->load->view('tema/topo', $this->data);
    }

    public function editar()
    {

        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar termo de garantia');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('garantias') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {

            $data = array(
                'textoGarantia' => $this->input->post('textoGarantia'),
                'refGarantia' => $this->input->post('refGarantia'),
            );

            if ($this->garantias_model->edit('garantias', $data, 'idGarantias', $this->input->post('idGarantias')) == true) {
                $this->session->set_flashdata('success', 'Termo de garantia editada com sucesso!');
                log_info('Alterou uma garantia. ID: ' . $this->input->post('idGarantias'));
                redirect(base_url() . 'index.php/garantias/editar/' . $this->input->post('idGarantias'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
            }
        }

        $this->data['result'] = $this->garantias_model->getById($this->uri->segment(3));
        $this->data['view'] = 'garantias/editarGarantia';
        $this->load->view('tema/topo', $this->data);
    }

    public function visualizar()
    {

        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar o termo de garantia.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->garantias_model->getById($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->data['view'] = 'garantias/visualizarGarantia';
        $this->load->view('tema/topo', $this->data);
    }

    public function imprimir()
    {

        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para imprimir o Termo de Garantia.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->garantias_model->getById($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->load->view('garantias/imprimirGarantia', $this->data);
    }

    public function excluir()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir termo de garantia');
            redirect(base_url());
        }

        $ID = $this->input->post('idGarantias');
        if ($ID == null) {

            $this->session->set_flashdata('error', 'Erro ao tentar excluir termo de garantia.');
            redirect(base_url() . 'index.php/garantias/gerenciar/');
        }

        if ($this->garantias_model->delete('garantias', 'idGarantias', $ID) == true) {

            $this->garantias_model->delete('garantias', 'idGarantias', $ID);
            $this->session->set_flashdata('success', 'Termo de garantia excluída com sucesso!');
            log_info('Removeu uma garantia. ID: ' . $ID);

        } else {

            $this->session->set_flashdata('error', 'Você não pode excluir esse termo de garantia.<br />Verifique se tem alguma OS vinculada a esse termo e remova antes de tentar excluir novamente.');

        }

        redirect(base_url() . 'index.php/garantias/gerenciar/');

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

}
