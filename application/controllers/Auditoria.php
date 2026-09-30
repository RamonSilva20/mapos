<?php

use Exceptions\Http\AuthorizationDenied;

defined('BASEPATH') or exit('No direct script access allowed');

class Auditoria extends MY_Controller
{
    public const CLEAN_SUCCESS_MESSAGE = 'Limpeza de logs realizada com sucesso.';

    public const CLEAN_EMPTY_MESSAGE = 'Nenhum log com mais de 30 dias encontrado.';

    public const CLEAN_AUDIT_TASK = 'Efetuou limpeza de logs';

    public function __construct()
    {
        parent::__construct();

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cAuditoria')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar logs do sistema.');
            throw new AuthorizationDenied('Você não tem permissão para visualizar logs do sistema.');
        }
        $this->load->model('Audit_model');
        $this->data['menuConfiguracoes'] = 'Auditoria';
    }

    public function index()
    {
        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('auditoria/index/');
        $this->data['configuration']['total_rows'] = $this->Audit_model->count('logs');

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->Audit_model->get('logs', '*', '', $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'auditoria/logs';

        return $this->layout();
    }

    public function clean()
    {
        if ($this->Audit_model->clean()) {
            log_info(self::CLEAN_AUDIT_TASK);
            $this->session->set_flashdata('success', self::CLEAN_SUCCESS_MESSAGE);
        } else {
            $this->session->set_flashdata('error', self::CLEAN_EMPTY_MESSAGE);
        }
        respond_redirect(site_url('auditoria'));
    }
}

/* End of file Controllername.php */
