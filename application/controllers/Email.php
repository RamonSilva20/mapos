<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Email extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
        $this->data['menuConfiguracoes'] = 'Email';

    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmail')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar fila de e-mails');
            redirect(base_url());
        }

        $this->load->library('pagination');
        $this->load->model('email_model');

        $config['base_url'] = base_url() . 'index.php/email/gerenciar/';
        $config['total_rows'] = $this->email_model->count('email_queue');
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

        $this->data['results'] = $this->email_model->get('email_queue', 'id,to,status,date', '', $config['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'emails/emails';
        $this->load->view('tema/topo', $this->data);
    }

    public function excluir()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmail')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir e-mail da fila.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir e-mail da fila.');
            redirect(base_url() . 'index.php/email/gerenciar/');
        }
        
        $this->load->model('email_model');
        
        $this->email_model->delete('email_queue', 'id', $id);

        log_info('Removeu um e-mail da fila de envio. ID: ' . $id);

        $this->session->set_flashdata('success', 'E-mail removido da fila de envio!');
        redirect(base_url() . 'index.php/email/gerenciar/');
    }

    public function process()
    {
        if (!$this->input->is_cli_request()) {
            show_404();
        }

        $this->email->send_queue();
    }

    public function retry()
    {
        if (!$this->input->is_cli_request()) {
            show_404();
        }

        $this->email->retry_queue();
    }
}
