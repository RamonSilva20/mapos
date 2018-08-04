<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Mapos extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('mapos_model', '', true);
    }

    public function index()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        $tecnico = $this->input->get('usuarios_id');
        $this->data['ordensAtrasadas'] = $this->mapos_model->getOsAtrasadas();
        $this->data['ordensVencendo'] = $this->mapos_model->getOsVencendo($tecnico);
        $this->data['ordensAbertas'] = $this->mapos_model->getOsAbertas();
        $this->data['ordensAndamento'] = $this->mapos_model->getOsAndamento();
        $this->data['ordensOrcamento'] = $this->mapos_model->getOsOrcamento();
        $this->data['ordensFinalizado'] = $this->mapos_model->getOsFinalizado();
        $this->data['produtos'] = $this->mapos_model->getProdutosMinimo();
        $this->data['os'] = $this->mapos_model->getOsEstatisticas();
        $this->data['estatisticas_financeiro'] = $this->mapos_model->getEstatisticasFinanceiro();
        $this->data['menuPainel'] = 'Painel';
        $this->data['view'] = 'mapos/painel';

        $this->load->view('tema/topo', $this->data);

    }

    public function minhaConta()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        $this->data['usuario'] = $this->mapos_model->getById($this->session->userdata('id'));
        $this->data['view'] = 'mapos/minhaConta';
        $this->load->view('tema/topo', $this->data);

    }

    public function alterarSenha()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        $oldSenha = $this->input->post('oldSenha');
        $senha = $this->input->post('novaSenha');
        $result = $this->mapos_model->alterarSenha($senha, $oldSenha, $this->session->userdata('id'));

        if ($result) {
            $this->session->set_flashdata('success', 'Senha Alterada com sucesso!');
            redirect(base_url() . 'index.php/mapos/minhaConta');
        } else {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar a senha!');
            redirect(base_url() . 'index.php/mapos/minhaConta');
        }

    }

    public function pesquisar()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        $termo = $this->input->get('termo');
        $osId = $this->mapos_model->pesquisarOs($termo);

        if ($osId != null) {
            $idOs = $osId->idOs;
            redirect(base_url() . 'index.php/os/visualizar/' . $idOs);
        } else {
            $data['results'] = $this->mapos_model->pesquisar($termo);
            $this->data['produtos'] = $data['results']['produtos'];
            $this->data['servicos'] = $data['results']['servicos'];
            $this->data['os'] = $data['results']['os'];
            $this->data['clientes'] = $data['results']['clientes'];
            $this->data['celular'] = $data['results']['celular'];
            $this->data['view'] = 'mapos/pesquisa';
            $this->load->view('tema/topo', $this->data);
        }

    }

    public function login()
    {

        $this->load->view('mapos/login');

    }

    public function sair()
    {

        $this->session->sess_destroy();

        redirect('mapos/login');

    }

    public function verificarLogin()
    {

        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'Email', 'valid_email|required|xss_clean|trim');
        $this->form_validation->set_rules('senha', 'Senha', 'required|xss_clean|trim');

        $ajax = $this->input->get('ajax');

        if ($this->form_validation->run() == false) {
            if ($ajax == true) {

                $json = array('result' => false);
                echo json_encode($json);

            } else {

                $this->session->set_flashdata('error', 'Os dados de acesso estão incorretos.');
                redirect($this->login);

            }

        } else {

            $email = $this->input->post('email');
            $senha = $this->input->post('senha');
            $this->load->library('encrypt');
            $senha = $this->encrypt->sha1($senha);

            $this->db->where('email', $email);
            $this->db->where('senha', $senha);
            $this->db->where('situacao', 1);
            $this->db->limit(1);

            $usuario = $this->db->get('usuarios')->row();

            if (count($usuario) > 0) {
                $dados = array('nome' => $usuario->nome, 'id' => $usuario->idUsuarios, 'permissao' => $usuario->permissoes_id, 'logado' => true);
                $this->session->set_userdata($dados);

                if ($ajax == true) {
                    $json = array('result' => true);
                    echo json_encode($json);
                } else {
                    redirect(base_url() . 'mapos');
                }

            } else {

                if ($ajax == true) {
                    $json = array('result' => false);
                    echo json_encode($json);
                } else {
                    $this->session->set_flashdata('error', 'Os dados de acesso estão incorretos.');
                    redirect($this->login);
                }
            }
        }
    }

    public function backup()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cBackup')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para efetuar backup.');
            redirect(base_url());
        }

        $this->load->dbutil();

        $prefs = array(
            'format' => 'zip',
            'filename' => 'backup' . date('d-m-Y') . '.sql',
        );

        $backup = &$this->dbutil->backup($prefs);
        $this->load->helper('file');
        write_file(base_url() . 'backup/backup.zip', $backup);

        $this->load->helper('download');
        force_download('backup' . date('d-m-Y H:m:s') . '.zip', $backup);

    }

    public function emitente()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $data['menuConfiguracoes'] = 'Configuracoes';
        $data['dados'] = $this->mapos_model->getEmitente();
        $data['view'] = 'mapos/emitente';

        $this->load->view('tema/topo', $data);
        $this->load->view('tema/rodape');

    }

    public function do_upload()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $this->load->library('upload');

        $image_upload_folder = FCPATH . 'assets/uploads';

        if (!file_exists($image_upload_folder)) {
            mkdir($image_upload_folder, DIR_WRITE_MODE, true);
        }

        $this->upload_config = array(
            'upload_path' => $image_upload_folder,
            'allowed_types' => 'png|jpg|jpeg|bmp',
            'max_size' => 2048,
            'remove_space' => true,
            'encrypt_name' => true,
        );

        $this->upload->initialize($this->upload_config);

        if (!$this->upload->do_upload()) {
            $upload_error = $this->upload->display_errors();
            print_r($upload_error);
            exit();

        } else {
            $file_info = array($this->upload->data());
            return $file_info[0]['file_name'];
        }

    }

    public function cadastrarEmitente()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('index.php/mapos/login');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('nome', 'Razão Social', 'required|xss_clean|trim');
        $this->form_validation->set_rules('cnpj', 'CNPJ', 'required|xss_clean|trim');
        $this->form_validation->set_rules('ie', 'IE', 'required|xss_clean|trim');
        $this->form_validation->set_rules('logradouro', 'Logradouro', 'required|xss_clean|trim');
        $this->form_validation->set_rules('numero', 'Número', 'required|xss_clean|trim');
        $this->form_validation->set_rules('bairro', 'Bairro', 'required|xss_clean|trim');
        $this->form_validation->set_rules('cidade', 'Cidade', 'required|xss_clean|trim');
        $this->form_validation->set_rules('uf', 'UF', 'required|xss_clean|trim');
        $this->form_validation->set_rules('telefone', 'Telefone', 'required|xss_clean|trim');
        $this->form_validation->set_rules('email', 'E-mail', 'required|xss_clean|trim');

        if ($this->form_validation->run() == false) {

            $this->session->set_flashdata('error', 'Campos obrigatórios não foram preenchidos.');
            redirect(base_url() . 'index.php/mapos/emitente');

        } else {

            $nome = $this->input->post('nome');
            $cnpj = $this->input->post('cnpj');
            $ie = $this->input->post('ie');
            $logradouro = $this->input->post('logradouro');
            $numero = $this->input->post('numero');
            $bairro = $this->input->post('bairro');
            $cidade = $this->input->post('cidade');
            $uf = $this->input->post('uf');
            $telefone = $this->input->post('telefone');
            $celular = $this->input->post('celular');
            $whatsapp = $this->input->post('whatsapp');
            $email = $this->input->post('email');
            $image = $this->do_upload();

            $logo = base_url() . 'assets/uploads/' . $image;
            $retorno = $this->mapos_model->addEmitente($nome, $cnpj, $ie, $logradouro, $numero, $bairro, $cidade, $uf, $telefone, $celular, $whatsapp, $email, $logo);

            if ($retorno) {
                $this->session->set_flashdata('success', 'As informações foram inseridas com sucesso.');
                redirect(base_url() . 'index.php/mapos/emitente');

            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar inserir as informações.');
                redirect(base_url() . 'index.php/mapos/emitente');
            }
        }
    }

    public function editarEmitente()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('index.php/mapos/login');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('nome', 'Razão Social', 'required|xss_clean|trim');
        $this->form_validation->set_rules('cnpj', 'CNPJ', 'required|xss_clean|trim');
        $this->form_validation->set_rules('ie', 'IE', 'required|xss_clean|trim');
        $this->form_validation->set_rules('logradouro', 'Logradouro', 'required|xss_clean|trim');
        $this->form_validation->set_rules('numero', 'Número', 'required|xss_clean|trim');
        $this->form_validation->set_rules('bairro', 'Bairro', 'required|xss_clean|trim');
        $this->form_validation->set_rules('cidade', 'Cidade', 'required|xss_clean|trim');
        $this->form_validation->set_rules('uf', 'UF', 'required|xss_clean|trim');
        $this->form_validation->set_rules('telefone', 'Telefone', 'required|xss_clean|trim');
        $this->form_validation->set_rules('email', 'E-mail', 'required|xss_clean|trim');

        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('error', 'Campos obrigatórios não foram preenchidos.');
            redirect(base_url() . 'index.php/mapos/emitente');

        } else {

            $nome = $this->input->post('nome');
            $cnpj = $this->input->post('cnpj');
            $ie = $this->input->post('ie');
            $logradouro = $this->input->post('logradouro');
            $numero = $this->input->post('numero');
            $bairro = $this->input->post('bairro');
            $cidade = $this->input->post('cidade');
            $uf = $this->input->post('uf');
            $telefone = $this->input->post('telefone');
            $celular = $this->input->post('celular');
            $whatsapp = $this->input->post('whatsapp');
            $email = $this->input->post('email');
            $id = $this->input->post('id');

            $retorno = $this->mapos_model->editEmitente($id, $nome, $cnpj, $ie, $logradouro, $numero, $bairro, $cidade, $uf, $telefone, $celular, $whatsapp, $email);
            if ($retorno) {
                $this->session->set_flashdata('success', 'As informações foram alteradas com sucesso.');
                redirect(base_url() . 'index.php/mapos/emitente');
            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar as informações.');
                redirect(base_url() . 'index.php/mapos/emitente');
            }

        }

    }

    public function editarLogo()
    {

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('index.php/mapos/login');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $id = $this->input->post('id');

        if ($id == null || !is_numeric($id)) {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar a logomarca.');
            redirect(base_url() . 'index.php/mapos/emitente');
        }

        $this->load->helper('file');

        delete_files(FCPATH . 'assets/uploads/');

        $image = $this->do_upload();

        $logo = base_url() . 'assets/uploads/' . $image;

        $retorno = $this->mapos_model->editLogo($id, $logo);

        if ($retorno) {
            $this->session->set_flashdata('success', 'As informações foram alteradas com sucesso.');
            redirect(base_url() . 'index.php/mapos/emitente');

        } else {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar as informações.');
            redirect(base_url() . 'index.php/mapos/emitente');

        }

    }

}
