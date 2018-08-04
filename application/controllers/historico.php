<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Historico extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        $this->load->helper(array('form', 'codegen_helper'));
        $this->load->model('historico_model', '', true);
        $this->data['menuHistorico'] = 'Historico';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar histórico.');
            redirect(base_url());
        }

        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'index.php/historico/gerenciar/';
        $config['total_rows'] = $this->historico_model->count('historico');
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

        $this->data['results'] = $this->historico_model->get('historico', 'idHistorico,idOs,data,responsavel,status,canal,comentarios', '', $config['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'historico/historico';
        $this->load->view('tema/topo', $this->data);

    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar histórico.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('historico') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $dataRegistro = $this->input->post('data');
            $dataRegistro = explode('/', $dataRegistro);
            $dataRegistro = $dataRegistro[2] . '-' . $dataRegistro[1] . '-' . $dataRegistro[0];

            $data = array(
                'idOs' => $this->input->post('idOs'),
                'data' => $dataRegistro,
                'responsavel' => $this->input->post('responsavel'),
                'status' => $this->input->post('status'),
                'canal' => $this->input->post('canal'),
                'comentarios' => $this->input->post('comentarios'),
            );

            if ($this->historico_model->add('historico', $data) == true) {
                $this->session->set_flashdata('success', 'Historico adicionado com sucesso!');
                redirect(base_url() . 'index.php/historico/');
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }
        $this->data['view'] = 'historico/adicionarHistorico';
        $this->load->view('tema/topo', $this->data);

    }

    public function editar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar histórico.');
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

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir histórico.');
            redirect(base_url());
        }

        $id = $this->input->post('idHistorico');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir histórico.');
            redirect(base_url() . 'index.php/historico/gerenciar/');
        }

        $this->historico_model->delete('historico', 'idHistorico', $id);

        $this->session->set_flashdata('success', 'Histórico excluido com sucesso!');
        redirect(base_url() . 'index.php/historico/gerenciar/');
    }

}
