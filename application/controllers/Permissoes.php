<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Permissoes extends MY_Controller
{
    /**
     * author: Ramon Silva
     * email: silva018-mg@yahoo.com.br
     *
     */

    public function __construct()
    {
        parent::__construct();

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cPermissao')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar as permissões no sistema.');
            redirect(base_url());
        }

        $this->load->helper(['form', 'codegen_helper']);
        $this->load->model('permissoes_model');
        $this->data['menuConfiguracoes'] = 'Permissões';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('permissoes/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->permissoes_model->count('permissoes');

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->permissoes_model->get('permissoes', 'idPermissao,nome,data,situacao', '', $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'permissoes/permissoes';
        return $this->layout();
    }

    public function adicionar()
    {
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('nome', 'Nome', 'trim|required');
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $nomePermissao = $this->input->post('nome', TRUE);
            $cadastro = date('Y-m-d');
            $situacao = 1;

            $permissoes = [

                'aCliente' => $this->input->post('aCliente', TRUE),
                'eCliente' => $this->input->post('eCliente', TRUE),
                'dCliente' => $this->input->post('dCliente', TRUE),
                'vCliente' => $this->input->post('vCliente', TRUE),

                'aProduto' => $this->input->post('aProduto', TRUE),
                'eProduto' => $this->input->post('eProduto', TRUE),
                'dProduto' => $this->input->post('dProduto', TRUE),
                'vProduto' => $this->input->post('vProduto', TRUE),

                'aServico' => $this->input->post('aServico', TRUE),
                'eServico' => $this->input->post('eServico', TRUE),
                'dServico' => $this->input->post('dServico', TRUE),
                'vServico' => $this->input->post('vServico', TRUE),

                'aOs' => $this->input->post('aOs', TRUE),
                'eOs' => $this->input->post('eOs', TRUE),
                'dOs' => $this->input->post('dOs', TRUE),
                'vOs' => $this->input->post('vOs', TRUE),

                'aVenda' => $this->input->post('aVenda', TRUE),
                'eVenda' => $this->input->post('eVenda', TRUE),
                'dVenda' => $this->input->post('dVenda', TRUE),
                'vVenda' => $this->input->post('vVenda', TRUE),

                'aGarantia' => $this->input->post('aGarantia', TRUE),
                'eGarantia' => $this->input->post('eGarantia', TRUE),
                'dGarantia' => $this->input->post('dGarantia', TRUE),
                'vGarantia' => $this->input->post('vGarantia', TRUE),

                'aArquivo' => $this->input->post('aArquivo', TRUE),
                'eArquivo' => $this->input->post('eArquivo', TRUE),
                'dArquivo' => $this->input->post('dArquivo', TRUE),
                'vArquivo' => $this->input->post('vArquivo', TRUE),

                'aPagamento' => $this->input->post('aPagamento', TRUE),
                'ePagamento' => $this->input->post('ePagamento', TRUE),
                'dPagamento' => $this->input->post('dPagamento', TRUE),
                'vPagamento' => $this->input->post('vPagamento', TRUE),

                'aLancamento' => $this->input->post('aLancamento', TRUE),
                'eLancamento' => $this->input->post('eLancamento', TRUE),
                'dLancamento' => $this->input->post('dLancamento', TRUE),
                'vLancamento' => $this->input->post('vLancamento', TRUE),

                'cUsuario' => $this->input->post('cUsuario', TRUE),
                'cEmitente' => $this->input->post('cEmitente', TRUE),
                'cPermissao' => $this->input->post('cPermissao', TRUE),
                'cBackup' => $this->input->post('cBackup', TRUE),
                'cAuditoria' => $this->input->post('cAuditoria', TRUE),
                'cEmail' => $this->input->post('cEmail', TRUE),
                'cSistema' => $this->input->post('cSistema', TRUE),

                'rCliente' => $this->input->post('rCliente', TRUE),
                'rProduto' => $this->input->post('rProduto', TRUE),
                'rServico' => $this->input->post('rServico', TRUE),
                'rOs' => $this->input->post('rOs', TRUE),
                'rVenda' => $this->input->post('rVenda', TRUE),
                'rFinanceiro' => $this->input->post('rFinanceiro', TRUE),

                'aCobranca' => $this->input->post('aCobranca', TRUE),
                'eCobranca' => $this->input->post('eCobranca', TRUE),
                'dCobranca' => $this->input->post('dCobranca', TRUE),
                'vCobranca' => $this->input->post('vCobranca', TRUE),
            ];
            $permissoes = serialize($permissoes);

            $data = [
                'nome' => $nomePermissao,
                'data' => $cadastro,
                'permissoes' => $permissoes,
                'situacao' => $situacao,
            ];

            if ($this->permissoes_model->add('permissoes', $data) == true) {
                $this->session->set_flashdata('success', 'Permissão adicionada com sucesso!');
                log_info('Adicionou uma permissão');
                redirect(site_url('permissoes/adicionar/'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->data['view'] = 'permissoes/adicionarPermissao';
        return $this->layout();
    }

    public function editar()
    {
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('nome', 'Nome', 'trim|required');
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $nomePermissao = $this->input->post('nome', TRUE);
            $situacao = $this->input->post('situacao', TRUE);
            $permissoes = [

                'aCliente' => $this->input->post('aCliente', TRUE),
                'eCliente' => $this->input->post('eCliente', TRUE),
                'dCliente' => $this->input->post('dCliente', TRUE),
                'vCliente' => $this->input->post('vCliente', TRUE),

                'aProduto' => $this->input->post('aProduto', TRUE),
                'eProduto' => $this->input->post('eProduto', TRUE),
                'dProduto' => $this->input->post('dProduto', TRUE),
                'vProduto' => $this->input->post('vProduto', TRUE),

                'aServico' => $this->input->post('aServico', TRUE),
                'eServico' => $this->input->post('eServico', TRUE),
                'dServico' => $this->input->post('dServico', TRUE),
                'vServico' => $this->input->post('vServico', TRUE),

                'aOs' => $this->input->post('aOs', TRUE),
                'eOs' => $this->input->post('eOs', TRUE),
                'dOs' => $this->input->post('dOs', TRUE),
                'vOs' => $this->input->post('vOs', TRUE),

                'aVenda' => $this->input->post('aVenda', TRUE),
                'eVenda' => $this->input->post('eVenda', TRUE),
                'dVenda' => $this->input->post('dVenda', TRUE),
                'vVenda' => $this->input->post('vVenda', TRUE),

                'aGarantia' => $this->input->post('aGarantia', TRUE),
                'eGarantia' => $this->input->post('eGarantia', TRUE),
                'dGarantia' => $this->input->post('dGarantia', TRUE),
                'vGarantia' => $this->input->post('vGarantia', TRUE),

                'aArquivo' => $this->input->post('aArquivo', TRUE),
                'eArquivo' => $this->input->post('eArquivo', TRUE),
                'dArquivo' => $this->input->post('dArquivo', TRUE),
                'vArquivo' => $this->input->post('vArquivo', TRUE),

                'aPagamento' => $this->input->post('aPagamento', TRUE),
                'ePagamento' => $this->input->post('ePagamento', TRUE),
                'dPagamento' => $this->input->post('dPagamento', TRUE),
                'vPagamento' => $this->input->post('vPagamento', TRUE),

                'aLancamento' => $this->input->post('aLancamento', TRUE),
                'eLancamento' => $this->input->post('eLancamento', TRUE),
                'dLancamento' => $this->input->post('dLancamento', TRUE),
                'vLancamento' => $this->input->post('vLancamento', TRUE),

                'cUsuario' => $this->input->post('cUsuario', TRUE),
                'cEmitente' => $this->input->post('cEmitente', TRUE),
                'cPermissao' => $this->input->post('cPermissao', TRUE),
                'cBackup' => $this->input->post('cBackup', TRUE),
                'cAuditoria' => $this->input->post('cAuditoria', TRUE),
                'cEmail' => $this->input->post('cEmail', TRUE),
                'cSistema' => $this->input->post('cSistema', TRUE),

                'rCliente' => $this->input->post('rCliente', TRUE),
                'rProduto' => $this->input->post('rProduto', TRUE),
                'rServico' => $this->input->post('rServico', TRUE),
                'rOs' => $this->input->post('rOs', TRUE),
                'rVenda' => $this->input->post('rVenda', TRUE),
                'rFinanceiro' => $this->input->post('rFinanceiro', TRUE),

                'aCobranca' => $this->input->post('aCobranca', TRUE),
                'eCobranca' => $this->input->post('eCobranca', TRUE),
                'dCobranca' => $this->input->post('dCobranca', TRUE),
                'vCobranca' => $this->input->post('vCobranca', TRUE),

            ];
            $permissoes = serialize($permissoes);

            $data = [
                'nome' => $nomePermissao,
                'permissoes' => $permissoes,
                'situacao' => $situacao,
            ];

            if ($this->permissoes_model->edit('permissoes', $data, 'idPermissao', $this->input->post('idPermissao', TRUE)) == true) {
                $this->session->set_flashdata('success', 'Permissão editada com sucesso!');
                log_info('Alterou uma permissão. ID: ' . $this->input->post('idPermissao', TRUE));
                redirect(site_url('permissoes/editar/') . $this->input->post('idPermissao', TRUE));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um errro.</p></div>';
            }
        }

        $this->data['result'] = $this->permissoes_model->getById($this->uri->segment(3));

        $this->data['view'] = 'permissoes/editarPermissao';
        return $this->layout();
    }

    public function desativar()
    {
        $id = $this->input->post('id', TRUE);
        if (!$id) {
            $this->session->set_flashdata('error', 'Erro ao tentar desativar permissão.');
            redirect(site_url('permissoes/gerenciar/'));
        }
        $data = [
            'situacao' => false,
        ];
        if ($this->permissoes_model->edit('permissoes', $data, 'idPermissao', $id)) {
            log_info('Desativou uma permissão. ID: ' . $id);
            $this->session->set_flashdata('success', 'Permissão desativada com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao desativar permissão!');
        }

        redirect(site_url('permissoes/gerenciar/'));
    }
}

/* End of file permissoes.php */
/* Location: ./application/controllers/permissoes.php */
