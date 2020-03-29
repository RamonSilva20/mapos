<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Pagamentos extends MY_Controller
{

    /**
     * author: Wilmerson Felipe
     * email: will.phelipe@gmail.com
     *
     */

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('pagamentos_model');
        $this->data['menuConfiguracoes'] = 'Pagamentos';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vPagamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar Credencial de Pagamento.');
            redirect(base_url());
        }
        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('pagamentos/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->pagamentos_model->count('pagamento');

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->pagamentos_model->get('pagamento', '*', '', $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'pagamentos/pagamentos';
        return $this->layout();
    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aPagamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar Pagamentos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('pagamentos') == false) {
            $this->data['custom_error'] = (validation_errors() ? true : false);
        } else {
            $data = [
                'nome' => $this->input->post('nomePag'),
                'public_key' => $this->input->post('publicKey'),
                'access_token' => $this->input->post('accessToken'),
                'client_id' => $this->input->post('clientId'),
                'client_secret' => $this->input->post('clientSecret'),
                'default_pag' => (isset($_POST['default_pag'])) ? true : false,

            ];

            if (is_numeric($id = $this->pagamentos_model->add('pagamento', $data, true))) {
                log_info('Adicionou um pagamento');
                $this->session->set_flashdata('success', 'Pagamento adicionado com sucesso.');
                redirect('pagamentos/editar/' . $id);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->data['view'] = 'pagamentos/adicionarPagamento';
        return $this->layout();
    }

    public function editar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'ePagamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar Credencial de Pagamento');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('pagamentos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'nome' => $this->input->post('nomePag'),
                'client_id' => $this->input->post('clientId'),
                'client_secret' => $this->input->post('clientSecret'),
                'public_key' => $this->input->post('publicKey'),
                'access_token' => $this->input->post('accessToken'),
                'default_pag' => (isset($_POST['default_pag'])) ? true : false,

            ];

            if ($this->pagamentos_model->edit('pagamento', $data, 'idPag', $this->input->post('idPag')) == true) {
                $this->session->set_flashdata('success', 'Credencial de Pagamento editada com sucesso!');
                log_info('Alterou uma credencial de pagamento. ID: ' . $this->input->post('idPag'));
                redirect(site_url('pagamentos/editar/') . $this->input->post('idPag'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
            }
        }

        $this->data['result'] = $this->pagamentos_model->getById($this->uri->segment(3));
        $this->data['view'] = 'pagamentos/editarPagamento';
        return $this->layout();
    }

    public function visualizar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vPagamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar a credencial de pagamento.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['pagamento'] = $this->pagamentos_model->getById($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['view'] = 'pagamentos/visualizarPagamento';
        return $this->layout();
    }

    public function excluir()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dPagamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir Credencial de Pagamento');
            redirect(base_url());
        }

        $ID = $this->input->post('idPag');

        if ($ID == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir credencial de pagamento.' . $ID);
            redirect(site_url('pagamentos/gerenciar/'));
        }

        if ($this->pagamentos_model->delete('pagamento', 'idPag', $ID) == true) {
            $this->pagamentos_model->delete('pagamento', 'idPag', $ID);
            $this->session->set_flashdata('success', 'Credencial de Pagamento excluída com sucesso!');
            log_info('Removeu uma credencial de pagamento. ID: ' . $ID);
        } else {
            $this->session->set_flashdata('error', 'Você não pode excluir essa Credencial de Pagamento.');
        }

        redirect(site_url('pagamentos/gerenciar/'));
    }
}
