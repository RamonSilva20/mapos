<?php

class Relatorios_model extends CI_Model
{
    /**
     * author: Ramon Silva
     * email: silva018-mg@yahoo.com.br
     *
     */

    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = !$one ? $query->result() : $query->row();
        return $result;
    }

    public function add($table, $data)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
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

    public function count($table)
    {
        return $this->db->count_all($table);
    }

    public function clientesCustom($dataInicial = null, $dataFinal = null, $tipo = null)
    {
        $whereData = '';
        if ($dataInicial != null) {
            $whereData .= "AND dataCadastro >= " . $this->db->escape($dataInicial);
        }
        if ($dataFinal != null) {
            $whereData .= "AND dataCadastro <= " . $this->db->escape($dataFinal);
        }
        if ($tipo != null) {
            $whereData .= "AND fornecedor = " . $this->db->escape($tipo);
        }
        $query = "SELECT idClientes, nomeCliente, sexo, pessoa_fisica,
        documento, telefone, celular, contato, email, fornecedor,
        dataCadastro, rua, numero, complemento, bairro, cidade, estado,
        cep FROM clientes WHERE dataCadastro $whereData ORDER BY nomeCliente";

        return $this->db->query($query, [$dataInicial, $dataFinal])->result();
    }

    public function clientesRapid($array = false)
    {
        $this->db->select('idClientes, nomeCliente, sexo, pessoa_fisica,
        documento, telefone, celular, contato, email, fornecedor,
        dataCadastro, rua, numero, complemento, bairro, cidade, estado,
        cep');

        $this->db->order_by('nomeCliente', 'asc');

        $this->db->select('idClientes, nomeCliente, sexo, pessoa_fisica,
        documento, telefone, celular, contato, email, fornecedor,
        dataCadastro, rua, numero, complemento, bairro, cidade, estado,
        cep');

        $result = $this->db->get('clientes');
        if ($array) {
            return $result->result_array();
        }
        return $result->result();
    }

    public function produtosRapid()
    {
        $query = "
            SELECT produtos.*, SUM(produtos.estoque * produtos.precoVenda) as valorEstoque
            FROM produtos
            GROUP BY produtos.idProdutos
            ORDER BY descricao
        ";

        return $this->db->query($query)->result();
    }

    public function produtosRapidMin()
    {
        $query = "
            SELECT produtos.*, SUM(produtos.estoque * produtos.precoVenda) as valorEstoque
            FROM produtos
            WHERE estoque <= estoqueMinimo
            GROUP BY produtos.idProdutos
            ORDER BY descricao
        ";

        return $this->db->query($query)->result();
    }

    public function produtosCustom($precoInicial = null, $precoFinal = null, $estoqueInicial = null, $estoqueFinal = null)
    {
        $wherePreco = "";
        $whereEstoque = "";
        if ($precoInicial != null) {
            $wherePreco = "AND precoVenda BETWEEN " . $this->db->escape($precoInicial) . " AND " . $this->db->escape($precoFinal);
        }
        if ($estoqueInicial != null) {
            $whereEstoque = "AND estoque BETWEEN " . $this->db->escape($estoqueInicial) . " AND " . $this->db->escape($estoqueFinal);
        }
        $query = "
            SELECT produtos.*, SUM(produtos.estoque * produtos.precoVenda) as valorEstoque
            FROM produtos
            WHERE estoque >= 0 $wherePreco $whereEstoque
            GROUP BY produtos.idProdutos
            ORDER BY descricao
        ";

        return $this->db->query($query)->result();
    }

    public function produtosEtiquetas($de, $ate)
    {
        $query = "SELECT * FROM produtos WHERE idProdutos BETWEEN " . $this->db->escape($de) . " AND " . $this->db->escape($ate) . " ORDER BY idProdutos";

        $this->db->order_by('descricao', 'asc');

        return $this->db->query($query)->result();
    }

    public function skuRapid($array = false)
    {
        $this->db->select('clientes.idClientes, clientes.nomeCliente, produtos.idProdutos, produtos.descricao, itens_de_vendas.quantidade, vendas.idVendas as idRelacionado, vendas.dataVenda as dataOcorrencia, itens_de_vendas.preco, (itens_de_vendas.preco * itens_de_vendas.quantidade) as precoTotal, "venda" as origem');
        $this->db->from('vendas');
        $this->db->join('itens_de_vendas', 'vendas.idVendas = itens_de_vendas.vendas_id');
        $this->db->join('clientes', 'clientes.idClientes = vendas.clientes_id');
        $this->db->join('produtos', 'produtos.idProdutos = itens_de_vendas.produtos_id');
        $subQuery1 = $this->db->get_compiled_select();
        $this->db->reset_query();

        $this->db->select('clientes.idClientes, clientes.nomeCliente, produtos.idProdutos, produtos.descricao, produtos_os.quantidade, os.idOs as idRelacionado, os.dataInicial as dataOcorrencia, produtos_os.preco , (produtos_os.preco * produtos_os.quantidade) as precoTotal, "os" as origem');
        $this->db->from('os');
        $this->db->join('produtos_os', 'produtos_os.os_id = os.idOs');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->join('produtos', 'produtos.idProdutos = produtos_os.produtos_id');
        $subQuery2 = $this->db->get_compiled_select();
        $this->db->reset_query();

        $query = $this->db->query("SELECT * FROM ($subQuery1 UNION $subQuery2) as result ORDER BY dataOcorrencia DESC");
        if ($array) {
            return $query->result_array();
        }

        return $query->result();
    }

    public function skuCustom($dataInicial = null, $dataFinal = null, $cliente = null, $origem = null, $array = false)
    {
        $this->db->select('clientes.idClientes, clientes.nomeCliente, produtos.idProdutos, produtos.descricao, itens_de_vendas.quantidade, vendas.idVendas as idRelacionado, vendas.dataVenda as dataOcorrencia, itens_de_vendas.preco, (itens_de_vendas.preco * itens_de_vendas.quantidade) as precoTotal, "venda" as origem');
        $this->db->from('vendas');
        $this->db->join('itens_de_vendas', 'vendas.idVendas = itens_de_vendas.vendas_id');
        $this->db->join('clientes', 'clientes.idClientes = vendas.clientes_id');
        $this->db->join('produtos', 'produtos.idProdutos = itens_de_vendas.produtos_id');
        $subQuery1 = $this->db->get_compiled_select();
        $this->db->reset_query();

        $this->db->select('clientes.idClientes, clientes.nomeCliente, produtos.idProdutos, produtos.descricao, produtos_os.quantidade, os.idOs as idRelacionado, os.dataInicial as dataOcorrencia, produtos_os.preco , (produtos_os.preco * produtos_os.quantidade) as precoTotal, "os" as origem');
        $this->db->from('os');
        $this->db->join('produtos_os', 'produtos_os.os_id = os.idOs');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->join('produtos', 'produtos.idProdutos = produtos_os.produtos_id');
        $subQuery2 = $this->db->get_compiled_select();
        $this->db->reset_query();

        $query = "
            CREATE TEMPORARY TABLE IF NOT EXISTS results
            (SELECT * FROM ($subQuery1 UNION $subQuery2) as unionTable)
        ";
        $this->db->query($query);

        $this->db->from("results");
        $this->db->order_by("dataOcorrencia", "desc");
        if ($dataInicial) {
            $this->db->where('dataOcorrencia >=', $dataInicial);
        }

        if ($dataFinal) {
            $this->db->where('dataOcorrencia <=', $dataFinal);
        }

        if ($cliente) {
            $this->db->where('idClientes =', $cliente);
        }

        if ($origem) {
            $this->db->where('origem =', $origem);
        }

        $result = $this->db->get();
        if ($array) {
            return $result->result_array();
        }

        return $result->result();
    }

    public function servicosRapid()
    {
        $this->db->order_by('nome', 'asc');

        return $this->db->get('servicos')->result();
    }

    public function servicosCustom($precoInicial = null, $precoFinal = null)
    {
        $query = "SELECT * FROM servicos WHERE preco BETWEEN ? AND ? ORDER BY nome";

        return $this->db->query($query, [$precoInicial, $precoFinal])->result();
    }

    public function osRapid($array = false)
    {
        $query = 'CREATE TEMPORARY TABLE IF NOT EXISTS total_produtos SELECT SUM(subTotal) as total_produto, os_id FROM produtos_os GROUP BY os_id; ';
        $this->db->query($query);

        $query = 'CREATE TEMPORARY TABLE IF NOT EXISTS total_servicos SELECT SUM(subTotal) as total_servico, os_id FROM servicos_os GROUP BY os_id; ';
        $this->db->query($query);

        $this->db->select('os.*,clientes.nomeCliente, total_servicos.total_servico, total_produtos.total_produto');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->join('total_produtos', 'total_produtos.os_id = os.idOs', 'left');
        $this->db->join('total_servicos', 'total_servicos.os_id = os.idOs', 'left');
        $this->db->order_by('os.dataInicial', 'DESC');

        $result = $this->db->get();
        if ($array) {
            return $result->result_array();
        }

        return $result->result();
    }

    public function osCustom($dataInicial = null, $dataFinal = null, $cliente = null, $responsavel = null, $status = null, $array = false)
    {
        $whereData = "";
        $whereCliente = "";
        $whereResponsavel = "";
        $whereStatus = "";
        if ($dataInicial != null) {
            $whereData .= "AND dataInicial >= " . $this->db->escape($dataInicial);
        }
        if ($dataFinal != null) {
            $whereData .= "AND dataInicial <= " . $this->db->escape($dataFinal);
        }
        if ($cliente != null) {
            $whereCliente = "AND clientes_id = " . $this->db->escape($cliente);
        }
        if ($responsavel != null) {
            $whereResponsavel = "AND usuarios_id = " . $this->db->escape($responsavel);
        }
        if ($status != null) {
            $whereStatus = "AND status = " . $this->db->escape($status);
        }
        $query = 'CREATE TEMPORARY TABLE IF NOT EXISTS total_produtos SELECT SUM(subTotal) as total_produto, os_id FROM produtos_os GROUP BY os_id; ';
        $this->db->query($query);

        $query = 'CREATE TEMPORARY TABLE IF NOT EXISTS total_servicos SELECT SUM(subTotal) as total_servico, os_id FROM servicos_os GROUP BY os_id; ';
        $this->db->query($query);

        $query = "SELECT os.*,clientes.nomeCliente, total_servicos.total_servico, total_produtos.total_produto FROM os
                   LEFT JOIN total_produtos ON total_produtos.os_id = os.idOs
                   LEFT JOIN total_servicos ON total_servicos.os_id = os.idOs
                   LEFT JOIN clientes ON os.clientes_id = clientes.idClientes
                   WHERE idOs != 0 $whereData $whereCliente $whereResponsavel $whereStatus
                   ORDER BY os.dataInicial";

        $result = $this->db->query($query);
        if ($array) {
            return $result->result_array();
        }

        return $result->result();
    }

    public function financeiroRapid($array = false)
    {
        $primeiroDiaMes = new \DateTime('first day of this month');
        $ultimodiaMes = new DateTime('last day of this month');

        $this->db->where('data_vencimento >=', $primeiroDiaMes->format('Y-m-d'));
        $this->db->where('data_vencimento <=', $ultimodiaMes->format('Y-m-d'));
        $this->db->order_by('data_vencimento', 'asc');
        $result = $this->db->get('lancamentos');
        if ($array) {
            return $result->result_array();
        }

        return $result->result();
    }

    public function financeiroCustom($dataInicial = null, $dataFinal = null, $tipo = null, $situacao = null, $array = false)
    {
        if ($dataInicial) {
            $this->db->where('data_vencimento >=', $dataInicial);
        }

        if ($dataFinal) {
            $this->db->where('data_vencimento <=', $dataFinal);
        }

        if ($tipo !== 'todos' && $tipo) {
            $this->db->where('tipo', $tipo);
        }

        if ($situacao !== 'todos' && $situacao) {
            if ($situacao === 'pendente') {
                $this->db->where('baixado', 0);
            }
            if ($situacao === 'pago') {
                $this->db->where('baixado', 1);
            }
        }

        $this->db->order_by('data_vencimento', 'asc');
        $result = $this->db->get('lancamentos');
        if ($array) {
            return $result->result_array();
        }

        return $result->result();
    }

    public function vendasRapid($array = false)
    {
        $this->db->select('vendas.*,clientes.nomeCliente, usuarios.nome');
        $this->db->from('vendas');
        $this->db->join('clientes', 'clientes.idClientes = vendas.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = vendas.usuarios_id');
        $this->db->order_by('vendas.idVendas', 'ASC');

        $result = $this->db->get();
        if ($array) {
            return $result->result_array();
        }

        return $result->result();
    }

    public function vendasCustom($dataInicial = null, $dataFinal = null, $cliente = null, $responsavel = null, $array = false)
    {
        $whereData = "";
        $whereCliente = "";
        $whereResponsavel = "";
        $whereStatus = "";
        if ($dataInicial != null) {
            $whereData .= "AND dataVenda >= " . $this->db->escape($dataInicial);
        }
        if ($dataFinal != null) {
            $whereData .= "AND dataVenda <= " . $this->db->escape($dataFinal);
        }
        if ($cliente != null) {
            $whereCliente = "AND clientes_id = " . $this->db->escape($cliente);
        }
        if ($responsavel != null) {
            $whereResponsavel = "AND usuarios_id = " . $this->db->escape($responsavel);
        }

        $query = "SELECT vendas.*,clientes.nomeCliente, usuarios.nome FROM vendas
        LEFT JOIN clientes ON vendas.clientes_id = clientes.idClientes
        LEFT JOIN usuarios ON vendas.usuarios_id = usuarios.idUsuarios
        WHERE idVendas != 0 $whereData $whereCliente $whereResponsavel ORDER BY vendas.idVendas";

        $result = $this->db->query($query);
        if ($array) {
            return $result->result_array();
        }

        return $result->result();
    }

    public function receitasBrutasRapid()
    {
        $emitente = $this->db->query("SELECT * FROM emitente LIMIT 1")->row_array();

        $inicio = (new DateTime())->modify('first day of this month');
        $fim = (new DateTime())->modify('last day of this month');

        $query = "
            SELECT
                SUM(valor) total,
                SUM(case when descricao NOT LIKE '%Fatura de OS%' AND descricao NOT LIKE '%Fatura de Venda%' then valor else 0 end) as totalOutros,
                SUM(case when descricao LIKE '%Fatura de OS%' then valor - desconto else 0 end) as totalServicos,
                SUM(case when descricao LIKE '%Fatura de Venda%' then valor - desconto else 0 end) as totalVendas
            FROM lancamentos
                WHERE baixado = 1
                AND tipo = 'receita'
                AND data_vencimento >= ?
                AND data_vencimento <= ?
        ";

        $relatorio = $this->db->query($query, [$inicio->format('c'), $fim->format('c')])->row_array();

        $mercadoriasTotalSemNf = floatval($relatorio['totalVendas']);
        $mercadoriasTotalComNf = 0;
        $mercadoriasTotal = $mercadoriasTotalSemNf + $mercadoriasTotalComNf;

        $industriaTotalSemNf = 0;
        $industriaTotalComNf = 0;
        $industriaTotal = $industriaTotalSemNf + $industriaTotalComNf;

        $servicosTotalSemNf = floatval($relatorio['totalServicos']);
        $servicosTotalComNf = 0;
        $servicosTotal = $servicosTotalSemNf + $servicosTotalComNf;

        $totalMes = $mercadoriasTotal + $industriaTotal = $servicosTotal;

        $periodo = sprintf("%s à %s", $inicio->format('d/m/Y'), $fim->format('d/m/Y'));

        return [
            'cnpj' => $emitente['cnpj'],
            'emitente' => $emitente['nome'],
            'periodo' => $periodo,
            'mercadoriasTotalSemNf' => number_format($mercadoriasTotalSemNf, 2, ',', '.'),
            'mercadoriasTotalComNf' => number_format($mercadoriasTotalComNf, 2, ',', '.'),
            'mercadoriasTotal' => number_format($mercadoriasTotal, 2, ',', '.'),
            'industriaTotalSemNf' => number_format($industriaTotalSemNf, 2, ',', '.'),
            'industriaTotalComNf' => number_format($industriaTotalComNf, 2, ',', '.'),
            'industriaTotal' => number_format($industriaTotal, 2, ',', '.'),
            'servicosTotalSemNf' => number_format($servicosTotalSemNf, 2, ',', '.'),
            'servicosTotalComNf' => number_format($servicosTotalComNf, 2, ',', '.'),
            'servicosTotal' => number_format($servicosTotal, 2, ',', '.'),
            'totalMes' => number_format($totalMes, 2, ',', '.'),
            'localEdata' => sprintf("%s, %s", $emitente['cidade'], (new DateTime())->format('d/m/Y')),
        ];
    }

    public function receitasBrutasCustom($dataInicial = null, $dataFinal = null)
    {
        $emitente = $this->db->query("SELECT * FROM emitente LIMIT 1")->row_array();

        $query = "
            SELECT
                SUM(valor) total,
                SUM(case when descricao NOT LIKE '%Fatura de OS%' AND descricao NOT LIKE '%Fatura de Venda%' then valor else 0 end) as totalOutros,
                SUM(case when descricao LIKE '%Fatura de OS%' then valor else 0 end) as totalServicos,
                SUM(case when descricao LIKE '%Fatura de Venda%' then valor else 0 end) as totalVendas
            FROM lancamentos
                WHERE baixado = 1
                AND tipo = 'receita'
                AND data_vencimento >= ?
                AND data_vencimento <= ?
        ";

        $inicio = new DateTime($dataInicial);
        $fim = new DateTime($dataFinal);

        $relatorio = $this->db->query($query, [$inicio->format('c'), $fim->format('c')])->row_array();

        $mercadoriasTotalSemNf = floatval($relatorio['totalVendas']);
        $mercadoriasTotalComNf = 0;
        $mercadoriasTotal = $mercadoriasTotalSemNf + $mercadoriasTotalComNf;

        $industriaTotalSemNf = 0;
        $industriaTotalComNf = 0;
        $industriaTotal = $industriaTotalSemNf + $industriaTotalComNf;

        $servicosTotalSemNf = floatval($relatorio['totalServicos']);
        $servicosTotalComNf = 0;
        $servicosTotal = $servicosTotalSemNf + $servicosTotalComNf;

        $totalMes = $mercadoriasTotal + $industriaTotal = $servicosTotal;

        $periodo = sprintf("%s à %s", $inicio->format('d/m/Y'), $fim->format('d/m/Y'));

        return [
            'cnpj' => $emitente['cnpj'],
            'emitente' => $emitente['nome'],
            'periodo' => $periodo,
            'mercadoriasTotalSemNf' => number_format($mercadoriasTotalSemNf, 2, ',', '.'),
            'mercadoriasTotalComNf' => number_format($mercadoriasTotalComNf, 2, ',', '.'),
            'mercadoriasTotal' => number_format($mercadoriasTotal, 2, ',', '.'),
            'industriaTotalSemNf' => number_format($industriaTotalSemNf, 2, ',', '.'),
            'industriaTotalComNf' => number_format($industriaTotalComNf, 2, ',', '.'),
            'industriaTotal' => number_format($industriaTotal, 2, ',', '.'),
            'servicosTotalSemNf' => number_format($servicosTotalSemNf, 2, ',', '.'),
            'servicosTotalComNf' => number_format($servicosTotalComNf, 2, ',', '.'),
            'servicosTotal' => number_format($servicosTotal, 2, ',', '.'),
            'totalMes' => number_format($totalMes, 2, ',', '.'),
            'localEdata' => sprintf("%s, %s", $emitente['cidade'], (new DateTime())->format('d/m/Y')),
        ];
    }
}
