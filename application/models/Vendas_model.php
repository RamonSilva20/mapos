<?php

use Piggly\Pix\StaticPayload;

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vendas_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        // totais() e o PIX usam as regras de valores da OS e da venda.
        $this->load->helper(['os', 'vendas']);
    }

    /**
     * Listagem de vendas da v5 (#2843), no padrão das listagens (#2852): os
     * mesmos filtros em listar() e contar(), para a paginação contar só o que
     * a busca encontra (na v4 o total ignorava os filtros).
     *
     * Cada linha traz a venda, o cliente, o vendedor e o total, calculado no
     * SQL para não consultar a venda de novo por linha: o valor com desconto,
     * quando houver, ou a soma dos itens.
     *
     * @param  array<string, string>  $filtros  pesquisa, status, de, ate (já validados)
     */
    public function listar(array $filtros, int $limite, int $offset): array
    {
        $totalItens = '(SELECT COALESCE(SUM(itens_de_vendas.subTotal), 0) FROM itens_de_vendas WHERE itens_de_vendas.vendas_id = vendas.idVendas)';

        $this->db->select('vendas.idVendas, vendas.dataVenda, vendas.status, vendas.faturado, vendas.garantia, vendas.desconto, vendas.valor_desconto, vendas.clientes_id, vendas.usuarios_id');
        $this->db->select('clientes.nomeCliente, usuarios.nome AS vendedor');
        $this->db->select($totalItens . ' AS totalItens', false);

        $this->aplicarFiltros($filtros);

        $linhas = $this->db
            ->order_by('vendas.idVendas', 'desc')
            ->limit($limite, max(0, $offset))
            ->get()
            ->result();

        foreach ($linhas as $linha) {
            $linha->total = (float) $linha->valor_desconto > 0 ? (float) $linha->valor_desconto : (float) $linha->totalItens;
        }

        return $linhas;
    }

    /** Total da listagem com os mesmos filtros de listar(). */
    public function contar(array $filtros): int
    {
        $this->aplicarFiltros($filtros);

        return (int) $this->db->count_all_results();
    }

    /**
     * pesquisa procura no nome e documento do cliente e, se for número, no Nº
     * da venda (o OR fica entre parênteses para não anular os outros filtros);
     * status é um só; de e ate limitam a data da venda, inclusive.
     */
    private function aplicarFiltros(array $filtros): void
    {
        $this->db->from('vendas');
        $this->db->join('clientes', 'clientes.idClientes = vendas.clientes_id', 'left');
        $this->db->join('usuarios', 'usuarios.idUsuarios = vendas.usuarios_id', 'left');

        $pesquisa = $filtros['pesquisa'] ?? '';
        if ($pesquisa !== '') {
            $this->db->group_start()
                ->like('clientes.nomeCliente', $pesquisa)
                ->or_like('clientes.documento', $pesquisa);
            if (ctype_digit($pesquisa)) {
                $this->db->or_where('vendas.idVendas', (int) $pesquisa);
            }
            $this->db->group_end();
        }

        if (($filtros['status'] ?? '') !== '') {
            $this->db->where('vendas.status', $filtros['status']);
        }

        if (($filtros['de'] ?? '') !== '') {
            $this->db->where('vendas.dataVenda >=', $filtros['de']);
        }
        if (($filtros['ate'] ?? '') !== '') {
            $this->db->where('vendas.dataVenda <=', $filtros['ate']);
        }
    }

    /**
     * Apaga o lançamento da fatura de uma venda excluída: pelo vínculo
     * vendas.lancamentos_id (que o faturar sempre gravou) e pelas descrições
     * conhecidas (ver vendaDescricoesDaFatura()). Chamar depois de apagar a
     * venda: vendas.lancamentos_id tem chave estrangeira para o lançamento.
     */
    public function excluirFatura(int $idVenda, ?int $idLancamento): void
    {
        if ($idLancamento) {
            $this->db->where('idLancamentos', $idLancamento)->delete('lancamentos');
        }

        $this->db->where('vendas_id', $idVenda)->where_in('descricao', vendaDescricoesDaFatura($idVenda))->delete('lancamentos');
    }

    public function getById($id)
    {
        $this->db->select('vendas.*, clientes.*, clientes.contato as contato_cliente, clientes.email as emailCliente, lancamentos.data_vencimento, usuarios.telefone as telefone_usuario, usuarios.email as email_usuario, usuarios.nome as nome');
        $this->db->from('vendas');
        $this->db->join('clientes', 'clientes.idClientes = vendas.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = vendas.usuarios_id');
        $this->db->join('lancamentos', 'vendas.idVendas = lancamentos.vendas_id', 'LEFT');
        $this->db->where('vendas.idVendas', $id);
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    /**
     * Venda faturada ou cancelada só pode ser alterada com a configuração
     * control_edit_vendas ligada, a mesma regra da OS (Os_model::isEditable()).
     * Na v4 só a faturada era barrada. A permissão (eVenda, dVenda) é do
     * controller.
     */
    public function isEditable($id = null)
    {
        if ($venda = $this->getById($id)) {
            if ($venda->status === 'Faturado' || $venda->status === 'Cancelado' || $venda->faturado == 1) {
                return $this->data['configuration']['control_edit_vendas'] == '1';
            }
        }

        return true;
    }

    public function getByIdCobrancas($id)
    {
        $this->db->select('vendas.*, clientes.*, clientes.email as emailCliente, lancamentos.data_vencimento, usuarios.telefone as telefone_usuario, usuarios.email as email_usuario, usuarios.nome, usuarios.nome, cobrancas.vendas_id,cobrancas.idCobranca,cobrancas.status');
        $this->db->from('vendas');
        $this->db->join('clientes', 'clientes.idClientes = vendas.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = vendas.usuarios_id');
        $this->db->join('cobrancas', 'cobrancas.vendas_id = vendas.idVendas');
        $this->db->join('lancamentos', 'vendas.idVendas = lancamentos.vendas_id', 'LEFT');
        $this->db->where('vendas.idVendas', $id);
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function getProdutos($id = null)
    {
        $this->db->select('itens_de_vendas.*, produtos.*');
        $this->db->from('itens_de_vendas');
        $this->db->join('produtos', 'produtos.idProdutos = itens_de_vendas.produtos_id');
        $this->db->where('vendas_id', $id);

        return $this->db->get()->result();
    }

    public function getCobrancas($id = null)
    {
        $this->db->select('cobrancas.*');
        $this->db->from('cobrancas');
        $this->db->where('vendas_id', $id);

        return $this->db->get()->result();
    }

    public function add($table, $data, $returnId = false)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            if ($returnId == true) {
                return $this->db->insert_id($table);
            }

            return true;
        }

        return false;
    }

    public function edit($table, $data, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0) {
            return true;
        }

        return false;
    }

    public function delete($table, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    /**
     * Totais da venda para a tela (osTotais(), sem serviços): produtos,
     * desconto e total, calculados pelos itens e pelo desconto gravado.
     */
    public function totais(object $venda): array
    {
        $soma = (float) ($this->db
            ->select_sum('subTotal')
            ->where('vendas_id', (int) $venda->idVendas)
            ->get('itens_de_vendas')
            ->row()->subTotal ?? 0);

        return osTotais($soma, 0.0, $venda->valor_desconto ?? 0, $venda->tipo_desconto ?? null, $venda->desconto ?? 0);
    }

    /** Linha de itens_de_vendas que pertence à venda, ou null. */
    public function getProdutoDaVenda(int $idVenda, int $idItem): ?object
    {
        return $this->db->where('idItens', $idItem)->where('vendas_id', $idVenda)->get('itens_de_vendas', 1)->row() ?: null;
    }

    /** Produto do cadastro (para conferir o id escolhido e o estoque), ou null. */
    public function getProdutoCadastro(int $id): ?object
    {
        return $this->db->select('idProdutos, descricao, precoVenda, estoque')->where('idProdutos', $id)->get('produtos', 1)->row() ?: null;
    }

    /**
     * Diz se existe o registro com o id, para conferir os ids escolhidos nos
     * autocompletes antes de gravar a venda. Com $ativo, só usuários ativos.
     */
    public function existe(string $tabela, string $chave, int $id, bool $ativo = false): bool
    {
        $this->db->where($chave, $id);
        if ($ativo) {
            $this->db->where('situacao', 1);
        }

        return $this->db->count_all_results($tabela) > 0;
    }

    /**
     * Tira o desconto da venda. Mudar os itens muda o total, e o desconto
     * antigo (calculado sobre o total anterior) deixaria de bater, como na v4.
     */
    public function zerarDesconto(int $idVenda): void
    {
        $this->db->set('desconto', 0)->set('valor_desconto', 0)->set('tipo_desconto', null)->where('idVendas', $idVenda)->update('vendas');
    }

    public function getQrCode($id, $pixKey, $emitente)
    {
        return $this->pix($id, $pixKey, $emitente)?->getQRCode();
    }

    /**
     * Código PIX "copia e cola" (BR Code) da venda: o mesmo payload do QR Code
     * de getQrCode(). Na v4 a tela decodificava a imagem do QR no navegador
     * (jsQR, do rawgit) para obter este texto.
     */
    public function getPixPayload($id, $pixKey, $emitente): ?string
    {
        return $this->pix($id, $pixKey, $emitente)?->getPixCode();
    }

    /**
     * PIX estático com o total da venda (com o desconto, quando houver); null
     * sem chave, sem emitente ou com total zerado.
     */
    private function pix($id, $pixKey, $emitente): ?StaticPayload
    {
        if (empty($id) || empty($pixKey) || empty($emitente)) {
            return null;
        }

        $venda = $this->getById($id);
        $amount = $venda ? round($this->totais($venda)['total'], 2) : 0.0;

        if ($amount <= 0) {
            return null;
        }

        $pix = new StaticPayload();
        $pix
            ->setAmount($amount)
            ->setTid($id)
            ->setDescription(sprintf('%s Venda %s', substr($emitente->nome, 0, 18), $id), true)
            ->setPixKey(getPixKeyType($pixKey), $pixKey)
            ->setMerchantName($emitente->nome)
            ->setMerchantCity($emitente->cidade);

        return $pix;
    }
}

/* End of file vendas_model.php */
/* Location: ./application/models/vendas_model.php */
