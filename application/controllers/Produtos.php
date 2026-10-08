<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Produtos extends MY_Controller
{
    /** Filtros da listagem, na query string (listagemFiltros()). */
    public const FILTROS = [
        'pesquisa' => 'texto',
        'estoque' => ['baixo'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('produtos_model');
        $this->load->helper('produtos');
        $this->data['menuProdutos'] = 'Produtos';
    }

    public function index()
    {
        $this->gerenciar();
    }

    /**
     * Listagem de produtos (#2841), no padrão das listagens (#2852), com o
     * filtro de estoque baixo e as etiquetas em modal.
     */
    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar produtos.');
            redirect(base_url());
        }

        $filtros = listagemFiltros(self::FILTROS, $this->input->get());
        $offset = (int) $this->uri->segment(3);
        $total = $this->produtos_model->contar($filtros);

        $this->data['filtros'] = $filtros;
        $this->data['total'] = $total;
        $this->data['results'] = $this->produtos_model->listar($filtros, (int) $this->data['configuration']['per_page'], $offset);
        $this->data['paginacao'] = $this->paginacao(site_url('produtos/gerenciar'), $total, $offset, null, $filtros);
        $this->data['pode'] = [
            'adicionar' => $this->permite('aProduto'),
            'editar' => $this->permite('eProduto'),
            'excluir' => $this->permite('dProduto'),
            'etiquetas' => $this->permite('rProduto'),
        ];

        if ($this->data['pode']['adicionar']) {
            $this->data['topbar_acao'] = ['label' => 'Novo produto', 'icon' => 'plus', 'href' => site_url('produtos/adicionar')];
        }

        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'produtos/produtos';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar produtos.');
            redirect(base_url());
        }

        return $this->formulario(null);
    }

    public function editar()
    {
        $produto = $this->produtoDaUrl();

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar produtos.');
            redirect(base_url());
        }

        return $this->formulario($produto);
    }

    /**
     * Formulário de produto (#2841), no padrão dos formulários (#2851). O id
     * editado é o da URL; os preços aceitam "1.234,56" ou "1234.56".
     */
    private function formulario(?object $produto)
    {
        $erros = [];

        if ($this->input->method() === 'post') {
            $erros = $this->validarFormulario('produtos');
            $precos = [];
            foreach (['precoCompra', 'precoVenda'] as $campo) {
                $precos[$campo] = valorDecimal($this->input->post($campo));
                if (! isset($erros[$campo]) && $precos[$campo] === null) {
                    $erros[$campo] = 'Informe o valor em reais, como 150,00 (até 99.999.999,99).';
                }
            }

            if ($erros === []) {
                $dados = produtoDadosDoFormulario($this->input->post(), $precos['precoCompra'], $precos['precoVenda']);

                $salvou = $produto === null
                    ? $this->produtos_model->add('produtos', $dados)
                    : $this->produtos_model->edit('produtos', $dados, 'idProdutos', (int) $produto->idProdutos);

                if ($salvou) {
                    log_info($produto === null ? 'Adicionou um produto' : 'Alterou um produto. ID: ' . (int) $produto->idProdutos);
                    $this->session->set_flashdata('success', $produto === null ? 'Produto cadastrado com sucesso!' : 'Alterações salvas.');

                    return redirect($produto === null ? 'produtos' : 'produtos/visualizar/' . (int) $produto->idProdutos);
                }

                $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
            }
        }

        $post = $this->input->method() === 'post' ? $this->input->post() : null;
        $valores = [];
        foreach (['codDeBarra', 'descricao', 'unidade', 'precoCompra', 'precoVenda', 'estoque', 'estoqueMinimo'] as $campo) {
            $valores[$campo] = is_array($post) ? (is_string($post[$campo] ?? null) ? $post[$campo] : '') : (string) ($produto->{$campo} ?? '');
        }
        // Novo produto: entra e sai do estoque, como na v4.
        foreach (['entrada', 'saida'] as $campo) {
            $valores[$campo] = is_array($post) ? ! empty($post[$campo]) : ($produto === null || (bool) $produto->{$campo});
        }

        $this->data['produto'] = $produto;
        $this->data['valores'] = $valores;
        $this->data['erros'] = $erros;
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'produtos/formulario';

        return $this->layout();
    }

    /**
     * Ficha do produto (#2841): dados, preços com o lucro e a entrada de
     * estoque.
     */
    public function visualizar()
    {
        $produto = $this->produtoDaUrl();

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar produtos.');
            redirect(base_url());
        }

        $this->data['produto'] = $produto;
        $this->data['pode'] = [
            'editar' => $this->permite('eProduto'),
            'excluir' => $this->permite('dProduto'),
        ];

        if ($this->data['pode']['editar']) {
            $this->data['topbar_acao'] = ['label' => 'Editar produto', 'icon' => 'pencil', 'href' => site_url('produtos/editar/' . (int) $produto->idProdutos)];
        }

        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'produtos/visualizar';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir produtos.');
            redirect(base_url());
        }

        $id = (int) $this->input->post('id');
        if ($id <= 0) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir produto.');
            redirect(site_url('produtos'));
        }

        $this->produtos_model->delete('produtos_os', 'produtos_id', $id);
        $this->produtos_model->delete('itens_de_vendas', 'produtos_id', $id);
        $this->produtos_model->delete('produtos', 'idProdutos', $id);

        log_info('Removeu um produto. ID: ' . $id);

        $this->session->set_flashdata('success', 'Produto excluído com sucesso!');
        redirect(site_url('produtos') . listagemQuery(listagemFiltros(self::FILTROS, $this->input->get())));
    }

    /**
     * Soma (ou, com número negativo, retira) uma quantidade do estoque. A
     * conta é feita no banco (estoque = estoque + ?): o valor atual não vem
     * mais do navegador.
     */
    public function atualizar_estoque()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para atualizar estoque de produtos.');
            redirect(base_url());
        }

        $produto = $this->produtos_model->getById((int) $this->input->post('id'));
        if (! $produto) {
            $this->session->set_flashdata('error', 'Produto não encontrado.');
            redirect(site_url('produtos'));
        }

        $quantidade = filter_var($this->input->post('quantidade'), FILTER_VALIDATE_INT);
        $destino = $this->input->post('voltar') === 'lista' ? site_url('produtos') : site_url('produtos/visualizar/' . (int) $produto->idProdutos);

        if ($quantidade === false || $quantidade === 0 || abs($quantidade) > 1000000) {
            $this->session->set_flashdata('error', 'Informe uma quantidade inteira diferente de zero (negativa para retirar).');
            redirect($destino);
        }

        if ((int) $produto->estoque + $quantidade < 0) {
            $this->session->set_flashdata('error', 'O estoque não pode ficar negativo: há ' . (int) $produto->estoque . ' em estoque.');
            redirect($destino);
        }

        $this->produtos_model->updateEstoque((int) $produto->idProdutos, abs($quantidade), $quantidade > 0 ? '+' : '-');
        log_info('Atualizou estoque de um produto. ID: ' . (int) $produto->idProdutos . ' (' . sprintf('%+d', $quantidade) . ')');

        $this->session->set_flashdata('success', 'Estoque atualizado: ' . sprintf('%+d', $quantidade) . ' (agora ' . ((int) $produto->estoque + $quantidade) . ').');
        redirect($destino);
    }

    /** Produto do segmento 3 da URL, ou redireciona para a listagem. */
    private function produtoDaUrl(): object
    {
        $produto = is_numeric($this->uri->segment(3)) ? $this->produtos_model->getById((int) $this->uri->segment(3)) : null;
        if (! $produto) {
            $this->session->set_flashdata('error', 'Produto não encontrado ou parâmetro inválido.');
            redirect('produtos/gerenciar');
        }

        return $produto;
    }
}
