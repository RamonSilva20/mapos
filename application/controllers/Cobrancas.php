<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cobrancas extends MY_Controller
{

    /**
     * author: Ramon Silva
     * email: silva018-mg@yahoo.com.br
     *
     */

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('cobrancas_model');
        $this->data['menuCobrancas'] = 'cobrancas';
    }

    public function index()
    {
        $this->cobrancas();
    }

    public function cobrancas()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vCobranca')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar cobrancas.');
            redirect(base_url());
        }

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('cobrancas/cobrancas/');
        $this->data['configuration']['total_rows'] = $this->cobrancas_model->count('cobrancas');

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->cobrancas_model->get('cobrancas', '*', '', $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'cobrancas/cobrancas';
        
        return $this->layout();
    }

    public function excluir()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dCobranca')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir cobranças');
            redirect(site_url('cobrancas/cobrancas/'));
        }

        $this->load->library('Gateways/GerencianetSdk', null, 'GerencianetSdk');
        $this->load->model('pagamentos_model');
        $change_id = $this->input->post('charge_id');
        if ($change_id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir cobrança.');
            redirect(site_url('cobrancas/cobrancas/'));
        }
        if ($this->cobrancas_model->delete('cobrancas', 'charge_id', $change_id) == true) {
            $defaultPayment = $this->pagamentos_model->getPagamentos(0);
            //Cancelamos o pagamento caso esteja em aberto
            $pagamento = $this->GerencianetSdk->cancelarTransacao(
                $change_id,
                $defaultPayment->client_id,
                $defaultPayment->client_secret
            );

            log_info('Removeu uma cobrança. ID' . $change_id);
            $this->session->set_flashdata('success', 'Cobrança excluida com sucesso!');
        } else {
            $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
        }
        redirect(site_url('cobrancas/cobrancas/'));
    }

    public function atualizar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCobranca')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para atualizar cobrança.');
            redirect(base_url());
        }

        $this->load->library('Gateways/GerencianetSdk', null, 'GerencianetSdk');
        $this->load->model('pagamentos_model');

        $change_id = intval($this->uri->segment(3));
        $defaultPayment = $this->pagamentos_model->getPagamentos(0);

        //Pegamos o retorno para atualizar o banco
        $pagamento = $this->GerencianetSdk->receberInfo(
            $change_id,
            $defaultPayment->client_id,
            $defaultPayment->client_secret
        );

        $pagamento = json_decode($pagamento, true);
        $obj = json_decode(json_encode($pagamento), false);

        $data = [
            'status' => $obj->data->status,
        ];

        if ($this->pagamentos_model->edit('cobrancas', $data, 'charge_id', $change_id) == true) {
            $this->session->set_flashdata('success', 'Cobrança atualizada com sucesso!');
            log_info('Alterou um status de cobrança. ID' .  $change_id);
            //Cobrança foi paga ou foi confirmada de forma manual, então damos baixa
            if ($obj->data->status == "paid" || $obj->data->status == "settled") {
                //TODO: dar baixa no lançamento caso exista
            }
        } else {
            $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
        }
        redirect(site_url('cobrancas/cobrancas/'));
    }

    public function confirmarPagamento()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCobranca')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para confirmar pagamento da cobrança.');
            redirect(base_url());
        }

        $this->load->library('Gateways/GerencianetSdk', null, 'GerencianetSdk');
        $this->load->model('pagamentos_model');

        $change_id = $this->input->post('confirma_id');
        $defaultPayment = $this->pagamentos_model->getPagamentos(0);

        $pagamento = $this->GerencianetSdk->confirmarPagamento(
            $change_id,
            $defaultPayment->client_id,
            $defaultPayment->client_secret
        );

        $pagamento = json_decode($pagamento, true);
        $obj = json_decode(json_encode($pagamento), false);

        if ($obj->code == '200') {
            $this->session->set_flashdata('success', 'Pagamento da cobrança confirmada com sucesso!');
        } else {
            $this->session->set_flashdata('error', $obj->errorDescription);
            redirect(site_url('cobrancas/cobrancas/'));
        }
        //Pegamos o retorno para atualizar o banco
        $pagamento = $this->GerencianetSdk->receberInfo(
            $change_id,
            $defaultPayment->client_id,
            $defaultPayment->client_secret
        );

        $pagamento = json_decode($pagamento, true);
        $obj = json_decode(json_encode($pagamento), false);

        $data = [
            'status' => $obj->data->status,
        ];

        if ($this->pagamentos_model->edit('cobrancas', $data, 'charge_id', $change_id) == true) {
            //TODO: fazer a baixa do financeiro
            log_info('Alterou um status de cobrança. ID' .  $change_id);
        } else {
            $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
        }
        redirect(site_url('cobrancas/cobrancas/'));
    }

    public function cancelar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCobranca')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para cancelar cobrança.');
            redirect(base_url());
        }

        $this->load->library('Gateways/GerencianetSdk', null, 'GerencianetSdk');
        $this->load->model('pagamentos_model');

        $change_id = $this->input->post('cancela_id');
        $defaultPayment = $this->pagamentos_model->getPagamentos(0);
        $pagamento = $this->GerencianetSdk->cancelarTransacao(
            $change_id,
            $defaultPayment->client_id,
            $defaultPayment->client_secret
        );

        $pagamento = json_decode($pagamento, true);
        $obj = json_decode(json_encode($pagamento), false);

        if ($obj->code == '200') {
            $this->session->set_flashdata('success', 'Cobrança cancelada com sucesso!');
        } else {
            $this->session->set_flashdata('error', $obj->errorDescription);

            redirect(site_url('cobrancas/cobrancas/'));
        }
        //Pegamos o retorno para atualizar o banco
        $pagamento = $this->GerencianetSdk->receberInfo(
            $change_id,
            $defaultPayment->client_id,
            $defaultPayment->client_secret
        );
        $pagamento = json_decode($pagamento, true);
        $obj = json_decode(json_encode($pagamento), false);
        $data = [
            'status' => $obj->data->status,
        ];
        if ($this->pagamentos_model->edit('cobrancas', $data, 'charge_id', $change_id) == true) {
            log_info('Alterou um status de cobrança. ID' .  $change_id);
        } else {
            $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
        }
        redirect(site_url('cobrancas/cobrancas/'));
    }
}
