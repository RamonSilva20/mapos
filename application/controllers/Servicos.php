<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Servicos extends MY_Controller
{
    /** Filtros da listagem, na query string (listagemFiltros()). */
    public const FILTROS = ['pesquisa' => 'texto'];

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('servicos_model');
        $this->data['menuServicos'] = 'Serviços';
    }

    public function index()
    {
        $this->gerenciar();
    }

    /**
     * Listagem de serviços (#2841), no padrão das listagens (#2852).
     */
    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar serviços.');
            redirect(base_url());
        }

        $filtros = listagemFiltros(self::FILTROS, $this->input->get());
        $offset = (int) $this->uri->segment(3);
        $total = $this->servicos_model->contar($filtros);

        $this->data['filtros'] = $filtros;
        $this->data['total'] = $total;
        $this->data['results'] = $this->servicos_model->listar($filtros, (int) $this->data['configuration']['per_page'], $offset);
        $this->data['paginacao'] = $this->paginacao(site_url('servicos/gerenciar'), $total, $offset, null, $filtros);
        $this->data['pode'] = [
            'adicionar' => $this->permite('aServico'),
            'editar' => $this->permite('eServico'),
            'excluir' => $this->permite('dServico'),
        ];

        if ($this->data['pode']['adicionar']) {
            $this->data['topbar_acao'] = ['label' => 'Novo serviço', 'icon' => 'plus', 'href' => site_url('servicos/adicionar')];
        }

        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'servicos/servicos';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar serviços.');
            redirect(base_url());
        }

        return $this->formulario(null);
    }

    public function editar()
    {
        $servico = is_numeric($this->uri->segment(3)) ? $this->servicos_model->getById((int) $this->uri->segment(3)) : null;
        if (! $servico) {
            $this->session->set_flashdata('error', 'Serviço não encontrado ou parâmetro inválido.');
            redirect('servicos/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar serviços.');
            redirect(base_url());
        }

        return $this->formulario($servico);
    }

    /**
     * Formulário de serviço (#2841), no padrão dos formulários (#2851). O id
     * editado é o da URL; o preço aceita "1.234,56" ou "1234.56".
     */
    private function formulario(?object $servico)
    {
        $erros = [];

        if ($this->input->method() === 'post') {
            $erros = $this->validarFormulario('servicos');
            $preco = valorDecimal($this->input->post('preco'));
            if (! isset($erros['preco']) && $preco === null) {
                $erros['preco'] = 'Informe o preço em reais, como 150,00 (até 99.999.999,99).';
            }

            if ($erros === []) {
                $dados = [
                    'nome' => trim((string) $this->input->post('nome')),
                    'descricao' => trim((string) $this->input->post('descricao')),
                    'preco' => $preco,
                ];

                $salvou = $servico === null
                    ? $this->servicos_model->add('servicos', $dados)
                    : $this->servicos_model->edit('servicos', $dados, 'idServicos', (int) $servico->idServicos);

                if ($salvou) {
                    log_info($servico === null ? 'Adicionou um serviço' : 'Alterou um serviço. ID: ' . (int) $servico->idServicos);
                    $this->session->set_flashdata('success', $servico === null ? 'Serviço cadastrado com sucesso!' : 'Alterações salvas.');

                    return redirect('servicos');
                }

                $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
            }
        }

        $post = $this->input->method() === 'post' ? $this->input->post() : null;
        $this->data['servico'] = $servico;
        $this->data['valores'] = [
            'nome' => is_array($post) ? (string) ($post['nome'] ?? '') : (string) ($servico->nome ?? ''),
            'descricao' => is_array($post) ? (string) ($post['descricao'] ?? '') : (string) ($servico->descricao ?? ''),
            'preco' => is_array($post) ? (string) ($post['preco'] ?? '') : (string) ($servico->preco ?? ''),
        ];
        $this->data['erros'] = $erros;
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'servicos/formulario';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir serviços.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir serviço.');
            redirect(site_url('servicos/gerenciar/'));
        }

        $this->servicos_model->delete('servicos_os', 'servicos_id', $id);
        $this->servicos_model->delete('servicos', 'idServicos', $id);

        log_info('Removeu um serviço. ID: ' . $id);

        $this->session->set_flashdata('success', 'Serviço excluído com sucesso!');
        redirect(site_url('servicos') . listagemQuery(listagemFiltros(self::FILTROS, $this->input->get())));
    }
}
