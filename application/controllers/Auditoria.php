<?php defined('BASEPATH') or exit('No direct script access allowed');

class Auditoria extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if ((!session_id()) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cAuditoria')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar logs do sistema.');
            redirect(base_url());
        }

        $this->load->model('Audit_model');
        $this->data['menuConfiguracoes'] = 'Auditoria';

    }

    public function index()
    {

        $this->load->library('pagination');

        $config['base_url'] = site_url('auditoria/index/');
        $config['total_rows'] = $this->Audit_model->count('logs');
        $config['per_page'] = 20;
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

        $this->data['results'] = $this->Audit_model->get('logs', '*', '', $config['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'auditoria/logs';
        $this->load->view('tema/topo', $this->data);

    }

    public function clean()
    {

        if ($this->Audit_model->clean()) {
            log_info('Efetuou limpeza de logs');
            $this->session->set_flashdata('success', 'Limpeza de logs realizada com sucesso.');
        } else {
            $this->session->set_flashdata('error', 'Nenhum log com mais de 30 dias encontrado.');
        }
        redirect(site_url('auditoria'));
    }

}

/* End of file Controllername.php */
