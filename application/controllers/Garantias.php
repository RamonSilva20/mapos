<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Garantias extends MY_Controller
{
    /** Filtros da listagem, na query string (listagemFiltros()). */
    public const FILTROS = ['pesquisa' => 'texto'];

    public function __construct()
    {
        parent::__construct();

        $this->load->helper(['form', 'os']);
        $this->load->model('garantias_model');
        $this->data['menuGarantia'] = 'Garantias';
    }

    public function index()
    {
        $this->gerenciar();
    }

    /**
     * Listagem dos termos de garantia (#2845), no padrão das listagens
     * (#2852).
     */
    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar Termo de Garantia.');
            redirect(base_url());
        }

        $filtros = listagemFiltros(self::FILTROS, $this->input->get());
        $offset = (int) $this->uri->segment(3);
        $total = $this->garantias_model->contar($filtros);

        $this->data['filtros'] = $filtros;
        $this->data['total'] = $total;
        $this->data['results'] = $this->garantias_model->listar($filtros, (int) $this->data['configuration']['per_page'], $offset);
        $this->data['paginacao'] = $this->paginacao(site_url('garantias/gerenciar'), $total, $offset, null, $filtros);
        $this->data['pode'] = [
            'adicionar' => $this->permite('aGarantia'),
            'editar' => $this->permite('eGarantia'),
            'excluir' => $this->permite('dGarantia'),
        ];

        if ($this->data['pode']['adicionar']) {
            $this->data['topbar_acao'] = ['label' => 'Novo termo', 'icon' => 'plus', 'href' => site_url('garantias/adicionar')];
        }

        $this->data['view'] = 'garantias/garantias';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar Termo de Garantia.');
            redirect(base_url());
        }

        return $this->formulario(null);
    }

    public function editar()
    {
        $garantia = is_numeric($this->uri->segment(3)) ? $this->garantias_model->getById((int) $this->uri->segment(3)) : null;
        if (! $garantia) {
            $this->session->set_flashdata('error', 'Termo de garantia não encontrado ou parâmetro inválido.');
            redirect('garantias/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar termo de garantia');
            redirect(base_url());
        }

        return $this->formulario($garantia);
    }

    /**
     * Formulário do termo, para cadastrar e editar (#2845), no padrão dos
     * formulários (#2851). O id editado é o da URL. O texto vira o formato dos
     * textos da OS (osTextoParaGravar()), como no formulário da OS: sem o
     * editor rico, e as impressões o exibem pelo HTMLPurifier.
     */
    private function formulario(?object $garantia)
    {
        $erros = [];

        if ($this->input->method() === 'post') {
            $erros = $this->validarFormulario('garantias');

            if ($erros === []) {
                $dados = [
                    'refGarantia' => trim((string) $this->input->post('refGarantia')),
                    'textoGarantia' => osTextoParaGravar((string) $this->input->post('textoGarantia')),
                ];

                if ($garantia === null) {
                    $id = $this->garantias_model->add('garantias', $dados + [
                        'dataGarantia' => date('Y-m-d'),
                        'usuarios_id' => $this->session->userdata('id_admin'),
                    ], true);
                    $salvou = is_numeric($id);
                } else {
                    $id = (int) $garantia->idGarantias;
                    $salvou = $this->garantias_model->edit('garantias', $dados, 'idGarantias', $id);
                }

                if ($salvou) {
                    log_info($garantia === null ? 'Adicionou uma garantia' : 'Alterou uma garantia. ID: ' . $id);
                    $this->session->set_flashdata('success', $garantia === null ? 'Termo de garantia cadastrado com sucesso!' : 'Alterações salvas.');

                    return redirect('garantias');
                }

                $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
            }
        }

        $post = $this->input->method() === 'post' ? $this->input->post() : null;
        $this->data['garantia'] = $garantia;
        $this->data['valores'] = [
            'refGarantia' => is_array($post) ? (string) ($post['refGarantia'] ?? '') : (string) ($garantia->refGarantia ?? ''),
            'textoGarantia' => is_array($post) ? (string) ($post['textoGarantia'] ?? '') : osTextoParaEdicao($garantia->textoGarantia ?? ''),
        ];
        $this->data['erros'] = $erros;
        $this->data['view'] = 'garantias/formulario';

        return $this->layout();
    }

    public function visualizar()
    {
        $garantia = is_numeric($this->uri->segment(3)) ? $this->garantias_model->getById((int) $this->uri->segment(3)) : null;
        if (! $garantia) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('garantias/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar o termo de garantia.');
            redirect(base_url());
        }

        $this->data['result'] = $garantia;
        $this->data['pode'] = [
            'editar' => $this->permite('eGarantia'),
            'excluir' => $this->permite('dGarantia'),
        ];

        if ($this->data['pode']['editar']) {
            $this->data['topbar_acao'] = ['label' => 'Editar termo', 'icon' => 'pencil', 'href' => site_url('garantias/editar/' . (int) $garantia->idGarantias)];
        }

        $this->data['view'] = 'garantias/visualizar';

        return $this->layout();
    }

    public function imprimir()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para imprimir o Termo de Garantia.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->garantias_model->getById($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->load->view('garantias/imprimirGarantia', $this->data);
    }

    public function imprimirGarantiaOs()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para imprimir o Termo de Garantia.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['osGarantia'] = $this->garantias_model->getByIdOsGarantia($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->load->view('garantias/imprimirGarantiaOs', $this->data);
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dGarantia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir termo de garantia');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir termo de garantia.');
            redirect(site_url('garantias/gerenciar/'));
        }

        $emUso = $this->garantias_model->osVinculadas((int) $id);
        if ($emUso > 0) {
            $this->session->set_flashdata('error', 'Você não pode excluir esse termo de garantia: ' . ($emUso === 1 ? '1 OS usa' : $emUso . ' OS usam') . ' este termo. Troque o termo dela e tente de novo.');
            redirect(site_url('garantias') . listagemQuery(listagemFiltros(self::FILTROS, $this->input->get())));
        }

        if ($this->garantias_model->delete('garantias', 'idGarantias', $id) == true) {
            $this->session->set_flashdata('success', 'Termo de garantia excluído com sucesso!');
            log_info('Removeu uma garantia. ID: ' . $id);
        } else {
            $this->session->set_flashdata('error', 'Não foi possível excluir o termo de garantia.');
        }

        redirect(site_url('garantias') . listagemQuery(listagemFiltros(self::FILTROS, $this->input->get())));
    }
}
