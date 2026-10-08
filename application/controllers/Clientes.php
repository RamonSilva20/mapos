<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Clientes extends MY_Controller
{
    /** Filtros da listagem, na query string (listagemFiltros()). */
    /** Quantas OS e vendas recentes a ficha do cliente mostra. */
    public const RECENTES = 20;

    public const FILTROS = [
        'pesquisa' => 'texto',
        'tipo' => ['cliente', 'fornecedor'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('clientes_model');
        $this->load->helper('clientes');
        $this->data['menuClientes'] = 'clientes';
    }

    public function index()
    {
        $this->gerenciar();
    }

    /**
     * Listagem de clientes e fornecedores, a primeira tela migrada para os
     * componentes da v5 (#2841, #2852): filtros na URL, paginação que os
     * mantém e exclusão confirmada em modal-confirm.
     */
    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar clientes.');
            redirect(base_url());
        }

        $filtros = listagemFiltros(self::FILTROS, $this->input->get());
        $offset = (int) $this->uri->segment(3);
        $total = $this->clientes_model->contar($filtros);

        $this->data['filtros'] = $filtros;
        $this->data['total'] = $total;
        $this->data['results'] = $this->clientes_model->listar($filtros, (int) $this->data['configuration']['per_page'], $offset);
        $this->data['paginacao'] = $this->paginacao(site_url('clientes/gerenciar'), $total, $offset, null, $filtros);
        $this->data['pode'] = [
            'adicionar' => $this->permite('aCliente'),
            'editar' => $this->permite('eCliente'),
            'excluir' => $this->permite('dCliente'),
        ];

        if ($this->data['pode']['adicionar']) {
            $this->data['topbar_acao'] = ['label' => 'Novo cliente', 'icon' => 'plus', 'href' => site_url('clientes/adicionar')];
        }

        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'clientes/clientes';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar clientes.');
            redirect(base_url());
        }

        return $this->formulario(null);
    }

    public function editar()
    {
        $cliente = is_numeric($this->uri->segment(3)) ? $this->clientes_model->getById((int) $this->uri->segment(3)) : null;
        if (! $cliente) {
            $this->session->set_flashdata('error', 'Cliente não encontrado ou parâmetro inválido.');
            redirect('clientes/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar clientes.');
            redirect(base_url());
        }

        return $this->formulario($cliente);
    }

    /**
     * Formulário de cliente da v5 (#2851), para adicionar ($cliente null) e
     * editar. Padrão dos formulários: POST comum; com erro, a tela volta com
     * os valores digitados e cada erro no seu campo (validarFormulario()); com
     * sucesso, redireciona com o toast.
     *
     * O id editado é sempre o da URL, nunca um campo do POST.
     */
    private function formulario(?object $cliente)
    {
        $erros = [];

        if ($this->input->method() === 'post') {
            $erros = $this->validarFormulario('clientes');
            if ($erros === []) {
                $email = trim((string) $this->input->post('email'));
                if ($email !== '' && $this->clientes_model->emailExists($email, $cliente ? (int) $cliente->idClientes : null)) {
                    $erros['email'] = 'Este e-mail já está sendo usado por outro cliente.';
                } else {
                    $dados = clienteDadosDoFormulario($this->input->post(), $cliente === null);

                    $salvou = $cliente === null
                        ? $this->clientes_model->add('clientes', $dados + ['dataCadastro' => date('Y-m-d')])
                        : $this->clientes_model->edit('clientes', $dados, 'idClientes', (int) $cliente->idClientes);

                    if ($salvou) {
                        log_info($cliente === null ? 'Adicionou um cliente.' : 'Alterou um cliente. ID ' . (int) $cliente->idClientes);
                        $this->session->set_flashdata('success', $cliente === null ? 'Cliente cadastrado com sucesso!' : 'Alterações salvas.');

                        return redirect($cliente === null ? 'clientes' : 'clientes/editar/' . (int) $cliente->idClientes);
                    }

                    $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
                }
            }
        }

        $this->data['cliente'] = $cliente;
        $this->data['valores'] = clienteValoresDoFormulario($this->input->method() === 'post' ? $this->input->post() : null, $cliente);
        $this->data['erros'] = $erros;
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'clientes/formulario';

        return $this->layout();
    }

    /**
     * Ficha do cliente (#2841): dados, ordens de serviço e vendas em abas por
     * link (?aba=dados|os|vendas), sem JavaScript.
     */
    public function visualizar()
    {
        $cliente = is_numeric($this->uri->segment(3)) ? $this->clientes_model->getById((int) $this->uri->segment(3)) : null;
        if (! $cliente) {
            $this->session->set_flashdata('error', 'Cliente não encontrado ou parâmetro inválido.');
            redirect('clientes/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar clientes.');
            redirect(base_url());
        }

        $id = (int) $cliente->idClientes;
        $aba = listagemFiltros(['aba' => ['dados', 'os', 'vendas']], $this->input->get())['aba'] ?? 'dados';

        $this->data['cliente'] = $cliente;
        $this->data['aba'] = $aba;
        $this->data['total_os'] = $this->clientes_model->contarOsDoCliente($id);
        $this->data['total_vendas'] = $this->clientes_model->contarVendasDoCliente($id);
        $this->data['os'] = $aba === 'os' ? $this->clientes_model->osDoCliente($id, self::RECENTES) : [];
        $this->data['vendas'] = $aba === 'vendas' ? $this->clientes_model->vendasDoCliente($id, self::RECENTES) : [];
        $this->data['pode'] = [
            'editar' => $this->permite('eCliente'),
            'excluir' => $this->permite('dCliente'),
            'ver_os' => $this->permite('vOs'),
            'editar_os' => $this->permite('eOs'),
            'ver_venda' => $this->permite('vVenda'),
            'editar_venda' => $this->permite('eVenda'),
        ];

        if ($this->data['pode']['editar']) {
            $this->data['topbar_acao'] = ['label' => 'Editar cliente', 'icon' => 'pencil', 'href' => site_url('clientes/editar/' . $id)];
        }

        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'clientes/visualizar';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir clientes.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir cliente.');
            redirect(site_url('clientes/gerenciar/'));
        }

        $os = $this->clientes_model->getAllOsByClient($id);
        if ($os != null) {
            $this->clientes_model->removeClientOs($os);
        }

        // excluindo Vendas vinculadas ao cliente
        $vendas = $this->clientes_model->getAllVendasByClient($id);
        if ($vendas != null) {
            $this->clientes_model->removeClientVendas($vendas);
        }

        $this->clientes_model->delete('clientes', 'idClientes', $id);
        log_info('Removeu um cliente. ID' . $id);

        $this->session->set_flashdata('success', 'Cliente excluído com sucesso!');
        // Volta para a listagem com os mesmos filtros (só os permitidos).
        redirect(site_url('clientes') . listagemQuery(listagemFiltros(self::FILTROS, $this->input->get())));
    }
}
