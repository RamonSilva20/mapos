<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Permissoes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cPermissao')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar as permissões no sistema.');
            redirect(base_url());
        }

        $this->load->helper(['form', 'usuarios']);
        $this->load->model('permissoes_model');
        $this->data['menuConfiguracoes'] = 'Permissões';
    }

    public function index()
    {
        $this->gerenciar();
    }

    /**
     * Grupos de permissão (#2846), com quantos usuários usam cada um. São
     * poucos: sem filtro nem paginação.
     */
    public function gerenciar()
    {
        $this->data['results'] = $this->permissoes_model->listarComUsuarios();
        $this->data['grupo_logado'] = (int) $this->session->userdata('permissao');
        $this->data['topbar_acao'] = ['label' => 'Novo grupo', 'icon' => 'plus', 'href' => site_url('permissoes/adicionar')];
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'permissoes/permissoes';

        return $this->layout();
    }

    public function adicionar()
    {
        return $this->formulario(null);
    }

    public function editar()
    {
        $grupo = is_numeric($this->uri->segment(3)) ? $this->permissoes_model->getById((int) $this->uri->segment(3)) : null;
        if (! $grupo) {
            $this->session->set_flashdata('error', 'Grupo de permissão não encontrado.');
            redirect('permissoes/gerenciar');
        }

        return $this->formulario($grupo);
    }

    /**
     * Formulário do grupo (#2846): nome e a matriz de permissões (ver,
     * adicionar, editar e excluir por módulo), relatórios e sistema. O grupo
     * editado vem da URL (na v4 vinha do idPermissao do POST).
     */
    private function formulario(?object $grupo)
    {
        $erros = [];
        $doLogado = $grupo !== null && (int) $grupo->idPermissao === (int) $this->session->userdata('permissao');
        // O grupo Administrador (instalação) e o do usuário logado não podem
        // ser desativados nem perder o acesso a usuários e permissões.
        $protegido = $doLogado || ($grupo !== null && (int) $grupo->idPermissao === PERMISSAO_ADMIN);

        if ($this->input->method() === 'post') {
            [$dados, $erros] = permissaoDadosDoFormulario($this->input->post(), $grupo === null);

            if ($protegido && $dados['situacao'] !== 1) {
                $erros['situacao'] = 'Este grupo não pode ser desativado.';
            }
            if ($protegido && array_diff(['cPermissao', 'cUsuario'], permissoesMarcadas($dados['permissoes'])) !== []) {
                $erros['_geral'] = ($doLogado ? 'Este é o grupo do seu usuário' : 'Este é o grupo Administrador') . ': mantenha "Usuários" e "Permissões" marcados para não perder o acesso a estas telas.';
            }

            if ($erros === []) {
                $salvou = $grupo === null
                    ? $this->permissoes_model->add('permissoes', $dados + ['data' => date('Y-m-d')])
                    : $this->permissoes_model->edit('permissoes', $dados, 'idPermissao', (int) $grupo->idPermissao);

                if ($salvou) {
                    log_info($grupo === null ? 'Adicionou uma permissão' : 'Alterou uma permissão. ID: ' . (int) $grupo->idPermissao);
                    $this->session->set_flashdata('success', $grupo === null ? 'Grupo de permissão criado.' : 'Alterações salvas.');

                    return redirect($grupo === null ? 'permissoes' : 'permissoes/editar/' . (int) $grupo->idPermissao);
                }

                $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
            }
        }

        $post = $this->input->method() === 'post' ? $this->input->post() : null;
        $this->data['grupo'] = $grupo;
        $this->data['valores'] = [
            'nome' => $post !== null ? (is_scalar($post['nome'] ?? null) ? trim((string) $post['nome']) : '') : (string) ($grupo->nome ?? ''),
            'situacao' => $post !== null ? in_array($post['situacao'] ?? null, ['1', 'on'], true) : (int) ($grupo->situacao ?? 1) === 1,
            'marcadas' => $post !== null
                ? permissoesMarcadas(json_encode(permissoesDoFormulario($post['permissoes'] ?? [])))
                : permissoesMarcadas($grupo->permissoes ?? null),
        ];
        $this->data['erros'] = $erros;
        $this->data['do_logado'] = $doLogado;
        $this->data['protegido'] = $protegido;
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'permissoes/formulario';

        return $this->layout();
    }

    /**
     * Desativa um grupo (POST com o token CSRF). O grupo do usuário logado
     * fica de fora; os usuários de um grupo inativo perdem o acesso.
     */
    public function desativar()
    {
        $id = (int) $this->input->post('id');
        $grupo = $id > 0 ? $this->permissoes_model->getById($id) : null;

        if (! $grupo) {
            $this->session->set_flashdata('error', 'Grupo de permissão não encontrado.');
            redirect(site_url('permissoes/gerenciar/'));
        }

        if ($id === (int) $this->session->userdata('permissao') || $id === PERMISSAO_ADMIN) {
            $this->session->set_flashdata('error', $id === PERMISSAO_ADMIN ? 'O grupo Administrador não pode ser desativado.' : 'Este é o grupo do seu usuário: ele não pode ser desativado.');
            redirect(site_url('permissoes/gerenciar/'));
        }

        if ($this->permissoes_model->edit('permissoes', ['situacao' => 0], 'idPermissao', $id)) {
            log_info('Desativou uma permissão. ID: ' . $id);
            $this->session->set_flashdata('success', 'Grupo "' . $grupo->nome . '" desativado.');
        } else {
            $this->session->set_flashdata('error', 'Erro ao desativar o grupo.');
        }

        redirect(site_url('permissoes/gerenciar/'));
    }
}
