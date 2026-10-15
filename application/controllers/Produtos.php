<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Produtos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('produtos_model');
        $this->data['menuProdutos'] = 'Produtos';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! authorize('vProduto', 'Você não tem permissão para visualizar produtos.')) {
            return;
        }

        $pesquisa = $this->input->get('pesquisa');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('produtos/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->produtos_model->count('produtos');
        if ($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/produtos")."\?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->produtos_model->get('produtos', '*', $pesquisa, $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'produtos/produtos';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! authorize('aProduto', 'Você não tem permissão para adicionar produtos.')) {
            return;
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('produtos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $precoCompra = $this->input->post('precoCompra');
            $precoCompra = str_replace(',', '', $precoCompra);
            $precoVenda = $this->input->post('precoVenda');
            $precoVenda = str_replace(',', '', $precoVenda);
            $data = [
                'codDeBarra' => set_value('codDeBarra'),
                'descricao' => set_value('descricao'),
                'unidade' => set_value('unidade'),
                'precoCompra' => $precoCompra,
                'precoVenda' => $precoVenda,
                'estoque' => set_value('estoque'),
                'estoqueMinimo' => set_value('estoqueMinimo'),
                'saida' => set_value('saida'),
                'entrada' => set_value('entrada'),
            ];

            if ($this->produtos_model->add('produtos', $data) == true) {
                $this->session->set_flashdata('success', 'Produto adicionado com sucesso!');
                log_info('Adicionou um produto');
                respond_redirect(site_url('produtos/adicionar/'));

                return;
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>An Error Occured.</p></div>';
            }
        }
        $this->data['view'] = 'produtos/adicionarProduto';

        return $this->layout();
    }

    public function editar()
    {
        $id = $this->uri->segment(3);

        if (! $id || ! is_numeric($id) || ! $this->produtos_model->getById($id)) {
            $this->session->set_flashdata('error', 'Produto não encontrado ou parâmetro inválido.');
            respond_redirect(site_url('produtos/gerenciar'));

            return;
        }

        if (! authorize('eProduto', 'Você não tem permissão para editar produtos.')) {
            return;
        }
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('produtos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $precoCompra = $this->input->post('precoCompra');
            $precoCompra = str_replace(',', '', $precoCompra);
            $precoVenda = $this->input->post('precoVenda');
            $precoVenda = str_replace(',', '', $precoVenda);
            $data = [
                'codDeBarra' => set_value('codDeBarra'),
                'descricao' => $this->input->post('descricao'),
                'unidade' => $this->input->post('unidade'),
                'precoCompra' => $precoCompra,
                'precoVenda' => $precoVenda,
                'estoque' => $this->input->post('estoque'),
                'estoqueMinimo' => $this->input->post('estoqueMinimo'),
                'saida' => set_value('saida'),
                'entrada' => set_value('entrada'),
            ];

            if ($this->produtos_model->edit('produtos', $data, 'idProdutos', $id) == true) {
                $this->session->set_flashdata('success', 'Produto editado com sucesso!');
                log_info('Alterou um produto. ID: ' . $id);
                respond_redirect(site_url('produtos/editar/') . $id);

                return;
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>An Error Occured</p></div>';
            }
        }

        $this->data['result'] = $this->produtos_model->getById($id);

        $this->data['view'] = 'produtos/editarProduto';

        return $this->layout();
    }

    public function visualizar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            respond_redirect(site_url('mapos'));

            return;
        }

        if (! authorize('vProduto', 'Você não tem permissão para visualizar produtos.')) {
            return;
        }

        $this->data['result'] = $this->produtos_model->getById($this->uri->segment(3));

        if ($this->data['result'] == null) {
            $this->session->set_flashdata('error', 'Produto não encontrado.');
            respond_redirect(site_url('produtos/gerenciar'));

            return;
        }

        $this->data['view'] = 'produtos/visualizarProduto';

        return $this->layout();
    }

    public function excluir()
    {
        if (! authorize('dProduto', 'Você não tem permissão para excluir produtos.')) {
            return;
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir produto.');
            respond_redirect(site_url('produtos/gerenciar/'));

            return;
        }

        // As três exclusões são uma operação só: os itens de OS e de venda
        // referenciam o produto, então uma falha no meio deixaria os filhos
        // removidos sem o pai. O rollback do ramo de erro desfaz os dois
        // primeiros quando o produto não sai.
        $this->db->trans_start();

        $this->produtos_model->delete('produtos_os', 'produtos_id', $id);
        $this->produtos_model->delete('itens_de_vendas', 'produtos_id', $id);

        if ($this->produtos_model->delete('produtos', 'idProdutos', $id) === false) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Erro ao tentar excluir produto.');
            respond_redirect(site_url('produtos/gerenciar/'));

            return;
        }

        if (! $this->db->trans_complete()) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir produto.');
            respond_redirect(site_url('produtos/gerenciar/'));

            return;
        }

        log_info('Removeu um produto. ID: ' . $id);

        $this->session->set_flashdata('success', 'Produto excluido com sucesso!');
        respond_redirect(site_url('produtos/gerenciar/'));
    }

    public function atualizar_estoque()
    {
        if (! authorize('eProduto', 'Você não tem permissão para atualizar estoque de produtos.')) {
            return;
        }

        $idProduto = $this->input->post('id');
        $novoEstoque = $this->input->post('estoque');
        $estoqueAtual = $this->input->post('estoqueAtual');

        if (! is_numeric($estoqueAtual) || ! is_numeric($novoEstoque)) {
            $this->session->set_flashdata('error', 'Estoque inválido.');
            respond_redirect(site_url('produtos/gerenciar/'));

            return;
        }

        $data = [
            'estoque' => (int) $estoqueAtual + (int) $novoEstoque,
        ];

        $this->produtos_model->edit('produtos', $data, 'idProdutos', $idProduto);

        $this->session->set_flashdata('success', 'Estoque de Produto atualizado com sucesso!');
        log_info('Atualizou estoque de um produto. ID: ' . $idProduto);
        respond_redirect(site_url('produtos/visualizar/') . $idProduto);
    }
}
