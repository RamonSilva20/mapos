<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Conecte extends CI_Controller
{

    public function __construct()
    {

        parent::__construct();
        $this->load->model('Conecte_model');

    }

    public function index()
    {

        $this->load->view('conecte/login');

    }

    public function sair()
    {
        $this->session->sess_destroy();
        redirect('conecte');
    }

    public function login()
    {

        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'valid_email|required|xss_clean|trim');
        $this->form_validation->set_rules('telefone', 'Telefone', 'required|xss_clean|trim');
        $ajax = $this->input->get('ajax');
        if ($this->form_validation->run() == false) {

            if ($ajax == true) {
                $json = array('result' => false);
                echo json_encode($json);
            } else {
                $this->session->set_flashdata('error', 'Os dados de acesso estão incorretos.');
                redirect(base_url() . 'conecte');
            }
        } else {

            $email = $this->input->post('email');
            $telefone = $this->input->post('telefone');

            $this->db->where('email', $email);
            $this->db->where('telefone', $telefone);
            $this->db->limit(1);
            $cliente = $this->db->get('clientes')->row();

            if (count($cliente) > 0) {
                $dados = array('nome' => $cliente->nomeCliente, 'id' => $cliente->idClientes, 'token' => 20540658, 'conectado' => true);
                $this->session->set_userdata($dados);

                if ($ajax == true) {
                    $json = array('result' => true);
                    echo json_encode($json);
                } else {
                    redirect(base_url() . 'conecte');
                }

            } else {

                if ($ajax == true) {
                    $json = array('result' => false);
                    echo json_encode($json);
                } else {
                    $this->session->set_flashdata('error', 'Os dados de acesso estão incorretos.');
                    redirect(base_url() . 'conecte');
                }
            }

        }

    }

    public function painel()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('conectado') || ($this->session->userdata('token') != 20540658))) {
            redirect('conecte');
        }

        $data['menuPainel'] = 'painel';
        $data['compras'] = $this->Conecte_model->getLastCompras($this->session->userdata('id'));
        $data['os'] = $this->Conecte_model->getLastOs($this->session->userdata('id'));
        $data['output'] = 'conecte/painel';
        $this->load->view('conecte/template', $data);

    }

    public function conta()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('conectado') || ($this->session->userdata('token') != 20540658))) {
            redirect('conecte');
        }

        $data['menuConta'] = 'conta';
        $data['result'] = $this->Conecte_model->getDados();

        $data['output'] = 'conecte/conta';
        $this->load->view('conecte/template', $data);
    }

    public function editarDados($id = null)
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('conectado') || ($this->session->userdata('token') != 20540658))) {
            redirect('conecte');
        }

        $data['menuConta'] = 'conta';

        $this->load->library('form_validation');
        $data['custom_error'] = '';

        if ($this->form_validation->run('clientes') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = array(
                'nomeCliente' => $this->input->post('nomeCliente'),
                'documento' => $this->input->post('documento'),
                'telefone' => $this->input->post('telefone'),
                'celular' => $this->input->post('celular'),
                'email' => $this->input->post('email'),
                'rua' => $this->input->post('rua'),
                'numero' => $this->input->post('numero'),
                'bairro' => $this->input->post('bairro'),
                'cidade' => $this->input->post('cidade'),
                'estado' => $this->input->post('estado'),
                'cep' => $this->input->post('cep'),
            );

            if ($this->Conecte_model->edit('clientes', $data, 'idClientes', $this->input->post('idClientes')) == true) {
                $this->session->set_flashdata('success', 'Dados editados com sucesso!');
                redirect(base_url() . 'index.php/conecte/conta');
            } else {

            }
        }

        $data['result'] = $this->Conecte_model->getDados();

        $data['output'] = 'conecte/editar_dados';
        $this->load->view('conecte/template', $data);
    }

    public function compras()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('conectado') || ($this->session->userdata('token') != 20540658))) {
            redirect('conecte');
        }

        $data['menuCompras'] = 'compras';
        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'index.php/conecte/compras/';
        $config['total_rows'] = $this->Conecte_model->count('compras', $this->session->userdata('id'));
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

        $data['results'] = $this->Conecte_model->getCompras('compras', '*', '', $config['per_page'], $this->uri->segment(3), '', '', $this->session->userdata('id'));

        $data['output'] = 'conecte/compras';
        $this->load->view('conecte/template', $data);

    }

    public function os()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('conectado') || ($this->session->userdata('token') != 20540658))) {
            redirect('conecte');
        }

        $data['menuOs'] = 'os';
        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'index.php/conecte/os/';
        $config['total_rows'] = $this->Conecte_model->count('os', $this->session->userdata('id'));
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

        $data['results'] = $this->Conecte_model->getOs('os', '*', '', $config['per_page'], $this->uri->segment(3), '', '', $this->session->userdata('id'));

        $data['output'] = 'conecte/os';
        $this->load->view('conecte/template', $data);
    }

    public function visualizarOs($id = null)
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('conectado') || ($this->session->userdata('token') != 20540658))) {
            redirect('conecte');
        }

        $data['menuOs'] = 'os';
        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->load->model('os_model');
        $data['result'] = $this->os_model->getById($this->uri->segment(3));
        $data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $data['compras'] = $this->os_model->getCompras($this->uri->segment(3));
        $data['emitente'] = $this->mapos_model->getEmitente();

        $data['output'] = 'conecte/visualizar_os';
        $this->load->view('conecte/template', $data);

    }

    public function visualizarCompra($id = null)
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('conectado') || ($this->session->userdata('token') != 20540658))) {
            redirect('conecte');
        }

        $data['menuVendas'] = 'vendas';
        $data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->load->model('vendas_model');
        $data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $data['emitente'] = $this->mapos_model->getEmitente();

        $data['output'] = 'conecte/visualizar_compra';
        $this->load->view('conecte/template', $data);
    }

}

/* End of file conecte.php */
/* Location: ./application/controllers/conecte.php */
