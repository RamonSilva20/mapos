<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Modulos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cSistema')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para acessar os módulos.');
            redirect(site_url());
        }

        $this->load->model('modulos_model');
        $this->load->library('module_manager');
    }

    public function index()
    {
        $this->data['modulos']     = $this->modulos_model->getAll();
        $this->data['disponiveis'] = $this->module_manager->getAvailable();
        $this->data['menuModulos'] = true;
        $this->data['view']        = 'modulos/index';

        return $this->layout();
    }

    public function upload()
    {
        if (! $this->input->is_ajax_request() && $this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect(site_url('modulos'));
        }

        if (empty($_FILES['modulo_zip']['name'])) {
            $this->session->set_flashdata('error', 'Nenhum arquivo enviado.');
            redirect(site_url('modulos'));
        }

        $result = $this->module_manager->upload($_FILES['modulo_zip']);

        if (isset($result['error'])) {
            $this->session->set_flashdata('error', $result['error']);
            redirect(site_url('modulos'));
        }

        redirect(site_url('modulos/confirmar/' . $result['slug']));
    }

    public function confirmar(string $slug)
    {
        $slug = $this->sanitizeSlug($slug);
        $manifest = $this->module_manager->getManifest($slug);

        if (! $manifest) {
            $this->session->set_flashdata('error', 'Módulo não encontrado ou inválido.');
            redirect(site_url('modulos'));
        }

        $this->data['manifest']    = $manifest;
        $this->data['slug']        = $slug;
        $this->data['menuModulos'] = true;
        $this->data['view']        = 'modulos/confirmar';

        return $this->layout();
    }

    public function instalar(string $slug)
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect(site_url('modulos'));
        }

        $slug   = $this->sanitizeSlug($slug);
        $result = $this->module_manager->install($slug);

        if (isset($result['error'])) {
            $this->session->set_flashdata('error', 'Erro na instalação: ' . $result['error']);
            redirect(site_url('modulos'));
        }

        $manifest = $this->module_manager->getManifest($slug);
        $name = $manifest['name'] ?? $slug;
        $this->session->set_flashdata('success', "Módulo '{$name}' instalado com sucesso!");
        redirect(site_url('modulos'));
    }

    public function desinstalar(string $slug)
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect(site_url('modulos'));
        }

        $slug   = $this->sanitizeSlug($slug);
        $module = $this->modulos_model->getBySlug($slug);

        if (! $module) {
            $this->session->set_flashdata('error', 'Módulo não encontrado.');
            redirect(site_url('modulos'));
        }

        $result = $this->module_manager->uninstall($slug);

        if (isset($result['error'])) {
            $this->session->set_flashdata('error', 'Erro na desinstalação: ' . $result['error']);
            redirect(site_url('modulos'));
        }

        $this->session->set_flashdata('success', "Módulo '{$module->nome}' desinstalado com sucesso!");
        redirect(site_url('modulos'));
    }

    public function visualizar(string $slug)
    {
        $slug     = $this->sanitizeSlug($slug);
        $manifest = $this->module_manager->getManifest($slug);

        if (! $manifest) {
            $this->session->set_flashdata('error', 'Módulo não encontrado.');
            redirect(site_url('modulos'));
        }

        $instalado = $this->modulos_model->getBySlug($slug);

        $this->data['manifest']    = $manifest;
        $this->data['slug']        = $slug;
        $this->data['instalado']   = $instalado;
        $this->data['menuModulos'] = true;
        $this->data['view']        = 'modulos/visualizar';

        return $this->layout();
    }

    public function toggle(string $slug)
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect(site_url('modulos'));
        }

        $slug   = $this->sanitizeSlug($slug);
        $result = $this->module_manager->toggle($slug);

        if (isset($result['error'])) {
            $this->session->set_flashdata('error', $result['error']);
            redirect(site_url('modulos'));
        }

        $label = $result['status'] === 'ativo' ? 'ativado' : 'desativado';
        $this->session->set_flashdata('success', "Módulo {$label} com sucesso.");
        redirect(site_url('modulos'));
    }

    private function sanitizeSlug(string $slug)
    {
        return preg_replace('/[^a-z0-9_]/', '', strtolower($slug));
    }
}
