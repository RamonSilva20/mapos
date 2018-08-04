<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Servicos extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        $this->load->helper(array('form', 'codegen_helper'));
        $this->load->model('servicos_model', '', true);
        $this->data['menuServicos'] = 'Serviços';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar serviços.');
            redirect(base_url());
        }

        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'index.php/servicos/gerenciar/';
        $config['total_rows'] = $this->servicos_model->count('servicos');
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

        $this->data['results'] = $this->servicos_model->get('servicos', 'idServicos,nome,descricao,preco', '', $config['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'servicos/servicos';
        $this->load->view('tema/topo', $this->data);

    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar serviços.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('servicos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $preco = $this->input->post('preco');
            $preco = str_replace(",", "", $preco);

            $data = array(
                'nome' => set_value('nome'),
                'descricao' => set_value('descricao'),
                'preco' => $preco,
            );

            if ($this->servicos_model->add('servicos', $data) == true) {
                $this->session->set_flashdata('success', 'Serviço adicionado com sucesso!');
                redirect(base_url() . 'index.php/servicos/adicionar/');
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }
        $this->data['view'] = 'servicos/adicionarServico';
        $this->load->view('tema/topo', $this->data);

    }

    public function editar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar serviços.');
            redirect(base_url());
        }
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('servicos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $preco = $this->input->post('preco');
            $preco = str_replace(",", "", $preco);
            $data = array(
                'nome' => $this->input->post('nome'),
                'descricao' => $this->input->post('descricao'),
                'preco' => $preco,
            );

            if ($this->servicos_model->edit('servicos', $data, 'idServicos', $this->input->post('idServicos')) == true) {
                $this->session->set_flashdata('success', 'Serviço editado com sucesso!');
                redirect(base_url() . 'index.php/servicos/editar/' . $this->input->post('idServicos'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um errro.</p></div>';
            }
        }

        $this->data['result'] = $this->servicos_model->getById($this->uri->segment(3));

        $this->data['view'] = 'servicos/editarServico';
        $this->load->view('tema/topo', $this->data);

    }

    public function excluir()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir serviços.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {

            $this->session->set_flashdata('error', 'Erro ao tentar excluir serviço.');
            redirect(base_url() . 'index.php/servicos/gerenciar/');
        }

        $this->db->where('servicos_id', $id);
        $this->db->delete('servicos_os');

        $this->servicos_model->delete('servicos', 'idServicos', $id);

        $this->session->set_flashdata('success', 'Serviço excluido com sucesso!');
        redirect(base_url() . 'index.php/servicos/gerenciar/');
    }
}
