<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Usuarios extends MY_Controller
{
    /** Filtros da listagem, na query string (listagemFiltros()). */
    public const FILTROS = [
        'pesquisa' => 'texto',
        'situacao' => ['ativo', 'inativo'],
    ];

    public function __construct()
    {
        parent::__construct();

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cUsuario')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar os usuários.');
            redirect(base_url());
        }

        $this->load->helper(['form', 'usuarios']);
        $this->load->model('usuarios_model');
        $this->data['menuUsuarios'] = 'Usuários';
        $this->data['menuConfiguracoes'] = 'Configurações';
    }

    public function index()
    {
        $this->gerenciar();
    }

    /**
     * Listagem de usuários migrada para os componentes da v5 (#2846), no
     * padrão das listagens (#2852).
     */
    public function gerenciar()
    {
        $filtros = listagemFiltros(self::FILTROS, $this->input->get());
        $offset = (int) $this->uri->segment(3);
        $total = $this->usuarios_model->contar($filtros);

        $this->data['filtros'] = $filtros;
        $this->data['total'] = $total;
        $this->data['results'] = $this->usuarios_model->listar($filtros, (int) $this->data['configuration']['per_page'], $offset);
        $this->data['paginacao'] = $this->paginacao(site_url('usuarios/gerenciar'), $total, $offset, null, $filtros);
        $this->data['logado'] = (int) $this->session->userdata('id_admin');
        $this->data['hoje'] = date('Y-m-d');
        $this->data['topbar_acao'] = ['label' => 'Novo usuário', 'icon' => 'plus', 'href' => site_url('usuarios/adicionar')];
        $this->data['view'] = 'usuarios/usuarios';

        return $this->layout();
    }

    public function adicionar()
    {
        return $this->formulario(null);
    }

    public function editar()
    {
        $usuario = is_numeric($this->uri->segment(3)) ? $this->usuarios_model->getById((int) $this->uri->segment(3)) : null;
        if (! $usuario) {
            $this->session->set_flashdata('error', 'Usuário não encontrado ou parâmetro inválido.');
            redirect('usuarios/gerenciar');
        }

        $motivo = usuarioPodeSerEditadoPor((int) $usuario->idUsuarios, (int) $this->session->userdata('id_admin'));
        if ($motivo === null && ! $this->podeDelegarGrupo((int) $usuario->permissoes_id)) {
            $motivo = 'Este usuário tem permissões que você não tem. Só quem as tem pode alterá-lo.';
        }
        if ($motivo !== null) {
            $this->session->set_flashdata('error', $motivo);
            redirect('usuarios/gerenciar');
        }

        return $this->formulario($usuario);
    }

    /**
     * Formulário de usuário (#2846), no padrão dos formulários da v5 (#2851).
     * O usuário editado vem da URL (na v4 vinha do idUsuarios do POST).
     */
    private function formulario(?object $usuario)
    {
        $erros = [];
        $logado = (int) $this->session->userdata('id_admin');

        if ($this->input->method() === 'post') {
            $erros = $this->validarFormulario('usuarios_formulario');
            [$dados, $errosDoFormulario, $senha] = usuarioDadosDoFormulario($this->input->post(), $usuario === null);
            $erros += $errosDoFormulario;

            if (! isset($erros['cpf']) && $this->usuarios_model->cpfEmUso($dados['cpf'], $usuario !== null ? (int) $usuario->idUsuarios : null)) {
                $erros['cpf'] = 'Este CPF já está cadastrado para outro usuário.';
            }

            if ($erros === [] && $usuario !== null && $dados['situacao'] === 0 && ($motivo = usuarioPodeSerRemovido((int) $usuario->idUsuarios, $logado)) !== null) {
                $erros['situacao'] = $motivo;
            }

            // Trocar o próprio grupo pode tirar o acesso a esta tela.
            if ($erros === [] && $usuario !== null && (int) $usuario->idUsuarios === $logado && $dados['permissoes_id'] !== (int) $usuario->permissoes_id) {
                $erros['permissoes_id'] = 'Você não pode trocar o grupo do seu próprio usuário.';
            }

            if ($erros === []) {
                $this->load->model('permissoes_model');
                $grupo = $this->permissoes_model->getById($dados['permissoes_id']);
                // Grupo inativo só fica se o usuário já estava nele.
                if (! $grupo || ((int) $grupo->situacao !== 1 && (int) ($usuario->permissoes_id ?? 0) !== (int) $grupo->idPermissao)) {
                    $erros['permissoes_id'] = 'Escolha um grupo de permissão ativo.';
                } elseif ((int) ($usuario->permissoes_id ?? 0) !== (int) $grupo->idPermissao && ! $this->podeDelegarGrupo((int) $grupo->idPermissao)) {
                    // Só concede grupo com permissões que o próprio operador tem (#2879).
                    $erros['permissoes_id'] = 'Você só pode escolher grupos com permissões que você também tem.';
                }
            }

            if ($erros === []) {
                if ($senha !== null) {
                    $dados['senha'] = password_hash($senha, PASSWORD_DEFAULT);
                }

                $salvou = $usuario === null
                    ? $this->usuarios_model->add('usuarios', $dados + ['dataCadastro' => date('Y-m-d')])
                    : $this->usuarios_model->edit('usuarios', $dados, 'idUsuarios', (int) $usuario->idUsuarios);

                if ($salvou) {
                    // Trocou a própria senha por aqui: a sessão atual segue valendo, as outras caem (#2879).
                    if ($usuario !== null && isset($dados['senha']) && (int) $usuario->idUsuarios === $logado) {
                        $this->load->model('mapos_model');
                        $this->session->set_userdata('sessao_versao', (int) ($this->mapos_model->estadoDaSessao($logado)['versao'] ?? 0));
                    }

                    log_info($usuario === null ? 'Adicionou um usuário.' : 'Alterou um usuário. ID: ' . (int) $usuario->idUsuarios);
                    $this->session->set_flashdata('success', $usuario === null ? 'Usuário cadastrado.' : 'Alterações salvas.');

                    return redirect($usuario === null ? 'usuarios' : 'usuarios/editar/' . (int) $usuario->idUsuarios);
                }

                $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
            }
        }

        $this->load->model('permissoes_model');
        $this->data['usuario'] = $usuario;
        $this->data['valores'] = usuarioValoresDoFormulario($this->input->method() === 'post' ? $this->input->post() : null, $usuario);
        $this->data['erros'] = $erros;
        $this->data['grupos'] = $this->gruposParaEscolher($usuario);
        $this->data['protegido'] = $usuario !== null && usuarioPodeSerRemovido((int) $usuario->idUsuarios, $logado) !== null;
        $this->data['dois_fatores_ativo'] = $usuario !== null && $this->usuarios_model->doisFatoresAtivo((int) $usuario->idUsuarios);
        $this->data['view'] = 'usuarios/formulario';

        return $this->layout();
    }

    /**
     * Redefine o 2FA de um usuário que perdeu o celular e os códigos de
     * recuperação (#2876): desliga o 2FA e apaga os códigos (POST com CSRF). O
     * usuário entra só com a senha; se o grupo exige o 2FA, será levado a
     * ativá-lo de novo.
     */
    public function redefinirDoisFatores()
    {
        $id = (int) $this->input->post('id');
        $usuario = $id > 0 ? $this->usuarios_model->getById($id) : null;

        if ($this->input->method() !== 'post' || ! $usuario) {
            $this->session->set_flashdata('error', 'Usuário não encontrado.');

            return redirect('usuarios/gerenciar');
        }

        // Redefinir o 2FA remove a segunda barreira de uma conta: vale a regra da edição (#2879).
        $motivo = usuarioPodeSerEditadoPor($id, (int) $this->session->userdata('id_admin'));
        if ($motivo === null && ! $this->podeDelegarGrupo((int) $usuario->permissoes_id)) {
            $motivo = 'Este usuário tem permissões que você não tem. Só quem as tem pode redefinir o 2FA dele.';
        }
        if ($motivo !== null) {
            $this->session->set_flashdata('error', $motivo);

            return redirect('usuarios/gerenciar');
        }

        $this->load->model('mapos_model');
        $this->mapos_model->desativarTotp($id);
        log_info('Redefiniu o 2FA de um usuário. ID: ' . $id);
        $this->session->set_flashdata('success', 'Autenticação em dois fatores do usuário redefinida.');

        return redirect('usuarios/editar/' . $id);
    }

    /**
     * Grupos ativos e, ao editar, o grupo atual do usuário mesmo inativo
     * (para o select não trocar de grupo sem ninguém pedir).
     *
     * @return array<string, string> id => nome
     */
    private function gruposParaEscolher(?object $usuario): array
    {
        $grupos = [];
        foreach ($this->permissoes_model->getActive('permissoes', 'permissoes.idPermissao,permissoes.nome') as $grupo) {
            $grupos[(string) $grupo->idPermissao] = (string) $grupo->nome;
        }

        if ($usuario !== null && ! isset($grupos[(string) $usuario->permissoes_id])) {
            $atual = $this->permissoes_model->getById((int) $usuario->permissoes_id);
            if ($atual) {
                $grupos[(string) $atual->idPermissao] = $atual->nome . ' (inativo)';
            }
        }

        return $grupos;
    }

    /**
     * Exclui um usuário (POST com o token CSRF; na v4 era um link GET). O
     * super admin, o próprio usuário logado e quem tem OS, vendas,
     * lançamentos ou garantias não são excluídos: o caminho é desativar.
     */
    public function excluir()
    {
        $id = (int) $this->input->post('id');
        $listagem = site_url('usuarios/gerenciar') . listagemQuery(listagemFiltros(self::FILTROS, $this->input->get()));

        if ($this->input->method() !== 'post' || $id <= 0 || ! $this->usuarios_model->getById($id)) {
            $this->session->set_flashdata('error', 'Usuário não encontrado.');
            redirect($listagem);
        }

        $motivo = usuarioPodeSerRemovido($id, (int) $this->session->userdata('id_admin'));
        $alvo = $this->usuarios_model->getById($id);
        if ($motivo === null && ! $this->podeDelegarGrupo((int) $alvo->permissoes_id)) {
            $motivo = 'Este usuário tem permissões que você não tem. Só quem as tem pode excluí-lo.';
        }
        if ($motivo === null && $this->usuarios_model->referencias($id) > 0) {
            $motivo = 'Este usuário tem OS, vendas, lançamentos ou termos de garantia. Desative-o em vez de excluir.';
        }

        if ($motivo !== null) {
            $this->session->set_flashdata('error', $motivo);
            redirect($listagem);
        }

        if ($this->usuarios_model->delete('usuarios', 'idUsuarios', $id)) {
            log_info('Removeu um usuário. ID: ' . $id);
            $this->session->set_flashdata('success', 'Usuário excluído.');
        } else {
            $this->session->set_flashdata('error', 'Não foi possível excluir o usuário.');
        }

        redirect($listagem);
    }
}
