<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Permissoes extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cPermissao')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar as permissões no sistema.');
            redirect(base_url());
        }

        $this->load->helper(array('form', 'codegen_helper'));
        $this->load->model('permissoes_model', '', true);
        $this->data['menuConfiguracoes'] = 'Permissões';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {

        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'index.php/permissoes/gerenciar/';
        $config['total_rows'] = $this->permissoes_model->count('permissoes');
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

        $this->data['results'] = $this->permissoes_model->get('permissoes', 'idPermissao,nome,data,situacao', '', $config['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'permissoes/permissoes';
        $this->load->view('tema/topo', $this->data);

    }

    public function adicionar()
    {
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('nome', 'Nome', 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {

            $nomePermissao = $this->input->post('nome');
            $cadastro = date('Y-m-d');
            $situacao = 1;

            $permissoes = array(

                'aCliente' => $this->input->post('aCliente'),
                'eCliente' => $this->input->post('eCliente'),
                'dCliente' => $this->input->post('dCliente'),
                'vCliente' => $this->input->post('vCliente'),

                'aProduto' => $this->input->post('aProduto'),
                'eProduto' => $this->input->post('eProduto'),
                'dProduto' => $this->input->post('dProduto'),
                'vProduto' => $this->input->post('vProduto'),

                'aServico' => $this->input->post('aServico'),
                'eServico' => $this->input->post('eServico'),
                'dServico' => $this->input->post('dServico'),
                'vServico' => $this->input->post('vServico'),

                'aOs' => $this->input->post('aOs'),
                'eOs' => $this->input->post('eOs'),
                'dOs' => $this->input->post('dOs'),
                'vOs' => $this->input->post('vOs'),

                'aCompra' => $this->input->post('aCompra'),
                'eCompra' => $this->input->post('eCompra'),
                'dCompra' => $this->input->post('dCompra'),
                'vCompra' => $this->input->post('vCompra'),

                'aVenda' => $this->input->post('aVenda'),
                'eVenda' => $this->input->post('eVenda'),
                'dVenda' => $this->input->post('dVenda'),
                'vVenda' => $this->input->post('vVenda'),

                'aArquivo' => $this->input->post('aArquivo'),
                'eArquivo' => $this->input->post('eArquivo'),
                'dArquivo' => $this->input->post('dArquivo'),
                'vArquivo' => $this->input->post('vArquivo'),

                'aLancamento' => $this->input->post('aLancamento'),
                'eLancamento' => $this->input->post('eLancamento'),
                'dLancamento' => $this->input->post('dLancamento'),
                'vLancamento' => $this->input->post('vLancamento'),

                'cUsuario' => $this->input->post('cUsuario'),
                'cEmitente' => $this->input->post('cEmitente'),
                'cPermissao' => $this->input->post('cPermissao'),
                'cBackup' => $this->input->post('cBackup'),

                'rCliente' => $this->input->post('rCliente'),
                'rProduto' => $this->input->post('rProduto'),
                'rServico' => $this->input->post('rServico'),
                'rOs' => $this->input->post('rOs'),
                'rCompra' => $this->input->post('rCompra'),
                'rVenda' => $this->input->post('rVenda'),
                'rFinanceiro' => $this->input->post('rFinanceiro'),

            );
            $permissoes = serialize($permissoes);

            $data = array(
                'nome' => $nomePermissao,
                'data' => $cadastro,
                'permissoes' => $permissoes,
                'situacao' => $situacao,
            );

            if ($this->permissoes_model->add('permissoes', $data) == true) {

                $this->session->set_flashdata('success', 'Permissão adicionada com sucesso!');
                redirect(base_url() . 'index.php/permissoes/adicionar/');
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->data['view'] = 'permissoes/adicionarPermissao';
        $this->load->view('tema/topo', $this->data);

    }

    public function editar()
    {

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('nome', 'Nome', 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {

            $nomePermissao = $this->input->post('nome');
            $situacao = $this->input->post('situacao');
            $permissoes = array(

                'aCliente' => $this->input->post('aCliente'),
                'eCliente' => $this->input->post('eCliente'),
                'dCliente' => $this->input->post('dCliente'),
                'vCliente' => $this->input->post('vCliente'),

                'aProduto' => $this->input->post('aProduto'),
                'eProduto' => $this->input->post('eProduto'),
                'dProduto' => $this->input->post('dProduto'),
                'vProduto' => $this->input->post('vProduto'),

                'aServico' => $this->input->post('aServico'),
                'eServico' => $this->input->post('eServico'),
                'dServico' => $this->input->post('dServico'),
                'vServico' => $this->input->post('vServico'),

                'aOs' => $this->input->post('aOs'),
                'eOs' => $this->input->post('eOs'),
                'dOs' => $this->input->post('dOs'),
                'vOs' => $this->input->post('vOs'),

                'aCompra' => $this->input->post('aCompra'),
                'eCompra' => $this->input->post('eCompra'),
                'dCompra' => $this->input->post('dCompra'),
                'vCompra' => $this->input->post('vCompra'),

                'aVenda' => $this->input->post('aVenda'),
                'eVenda' => $this->input->post('eVenda'),
                'dVenda' => $this->input->post('dVenda'),
                'vVenda' => $this->input->post('vVenda'),

                'aArquivo' => $this->input->post('aArquivo'),
                'eArquivo' => $this->input->post('eArquivo'),
                'dArquivo' => $this->input->post('dArquivo'),
                'vArquivo' => $this->input->post('vArquivo'),

                'aLancamento' => $this->input->post('aLancamento'),
                'eLancamento' => $this->input->post('eLancamento'),
                'dLancamento' => $this->input->post('dLancamento'),
                'vLancamento' => $this->input->post('vLancamento'),

                'cUsuario' => $this->input->post('cUsuario'),
                'cEmitente' => $this->input->post('cEmitente'),
                'cPermissao' => $this->input->post('cPermissao'),
                'cBackup' => $this->input->post('cBackup'),

                'rCliente' => $this->input->post('rCliente'),
                'rProduto' => $this->input->post('rProduto'),
                'rServico' => $this->input->post('rServico'),
                'rOs' => $this->input->post('rOs'),
                'rCompra' => $this->input->post('rCompra'),
                'rVenda' => $this->input->post('rVenda'),
                'rFinanceiro' => $this->input->post('rFinanceiro'),

            );
            $permissoes = serialize($permissoes);

            $data = array(
                'nome' => $nomePermissao,
                'permissoes' => $permissoes,
                'situacao' => $situacao,
            );

            if ($this->permissoes_model->edit('permissoes', $data, 'idPermissao', $this->input->post('idPermissao')) == true) {
                $this->session->set_flashdata('success', 'Permissão editada com sucesso!');
                redirect(base_url() . 'index.php/permissoes/editar/' . $this->input->post('idPermissao'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um errro.</p></div>';
            }
        }

        $this->data['result'] = $this->permissoes_model->getById($this->uri->segment(3));

        $this->data['view'] = 'permissoes/editarPermissao';
        $this->load->view('tema/topo', $this->data);

    }

    public function excluir()
    {

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

/* End of file permissoes.php */
/* Location: ./application/controllers/permissoes.php */
