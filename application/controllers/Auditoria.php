<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auditoria extends MY_Controller
{
    /** Filtros da listagem, na query string (listagemFiltros()). */
    public const FILTROS = [
        'pesquisa' => 'texto',
        'de' => 'texto',
        'ate' => 'texto',
    ];

    public function __construct()
    {
        parent::__construct();

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cAuditoria')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar logs do sistema.');
            redirect(base_url());
        }
        $this->load->model('Audit_model');
        $this->data['menuConfiguracoes'] = 'Auditoria';
    }

    /**
     * Registro das ações dos usuários (#2846), no padrão das listagens
     * (#2852): busca e período na URL.
     */
    public function index()
    {
        $filtros = $this->filtros();
        $offset = (int) $this->uri->segment(3);
        $total = $this->Audit_model->contar($filtros);

        $this->data['filtros'] = $filtros;
        $this->data['total'] = $total;
        $this->data['results'] = $this->Audit_model->listar($filtros, (int) $this->data['configuration']['per_page'], $offset);
        $this->data['paginacao'] = $this->paginacao(site_url('auditoria/index'), $total, $offset, null, $filtros);
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'auditoria/logs';

        return $this->layout();
    }

    /** Filtros válidos: pesquisa e as datas de/ate em AAAA-MM-DD. */
    private function filtros(): array
    {
        $filtros = listagemFiltros(self::FILTROS, $this->input->get());
        foreach (['de', 'ate'] as $data) {
            if (isset($filtros[$data]) && dataIsoParaYmd($filtros[$data]) === null) {
                unset($filtros[$data]);
            }
        }

        return $filtros;
    }

    /** Apaga os registros com mais de 30 dias (POST com o token CSRF). */
    public function clean()
    {
        if ($this->input->method() !== 'post') {
            redirect(site_url('auditoria'));
        }

        if ($this->Audit_model->clean()) {
            log_info('Efetuou limpeza de logs');
            $this->session->set_flashdata('success', 'Registros com mais de 30 dias apagados.');
        } else {
            $this->session->set_flashdata('error', 'Nenhum registro com mais de 30 dias.');
        }
        redirect(site_url('auditoria'));
    }
}
