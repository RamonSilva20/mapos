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

    public function clientesCustom($dataInicial = null, $dataFinal = null)
    {
        if ($dataInicial == null || $dataFinal == null) {
            $dataInicial = date('Y-m-d');
            $dataFinal = date('Y-m-d');
        }
        $query = "SELECT * FROM clientes WHERE dataCadastro BETWEEN ? AND ?";
        return $this->db->query($query, [$dataInicial, $dataFinal])->result();
    }

    public function clientesRapid()
    {
        $this->db->order_by('nomeCliente', 'asc');
        return $this->db->get('clientes')->result();
    }

    public function produtosRapid()
    {
        $this->db->order_by('descricao', 'asc');
        return $this->db->get('produtos')->result();
    }

    public function produtosRapidMin()
    {
        $this->db->order_by('descricao', 'asc');
        $this->db->where('estoque < estoqueMinimo');
        return $this->db->get('produtos')->result();
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
        $query = "SELECT * FROM produtos WHERE estoque >= 0 $wherePreco $whereEstoque";
        return $this->db->query($query)->result();
    }

    public function produtosEtiquetas($de, $ate)
    {
        $query = "SELECT * FROM produtos WHERE idProdutos BETWEEN ".$this->db->escape($de)." AND ".$this->db->escape($ate)." ORDER BY idProdutos";

        return $this->db->query($query)->result();
    }

    public function servicosRapid()
    {
        $this->db->order_by('nome', 'asc');
        return $this->db->get('servicos')->result();
    }

    public function servicosCustom($precoInicial = null, $precoFinal = null)
    {
        $query = "SELECT * FROM servicos WHERE preco BETWEEN ? AND ?";
        return $this->db->query($query, [$precoInicial, $precoFinal])->result();
    }

    public function osRapid()
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
        return $this->db->get()->result();
    }

    public function osCustom($dataInicial = null, $dataFinal = null, $cliente = null, $responsavel = null, $status = null)
    {
        $whereData = "";
        $whereCliente = "";
        $whereResponsavel = "";
        $whereStatus = "";
        if ($dataInicial != null) {
            $whereData = "AND dataInicial BETWEEN " . $this->db->escape($dataInicial) . " AND " . $this->db->escape($dataFinal);
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
                   WHERE idOs != 0 $whereData $whereCliente $whereResponsavel $whereStatus";

        return $this->db->query($query)->result();
    }

    public function financeiroRapid()
    {
        $dataInicial = date('Y-m-01');
        $dataFinal = date("Y-m-t");
        $query = "SELECT * FROM lancamentos WHERE data_vencimento BETWEEN ? and ? ORDER BY tipo";
        return $this->db->query($query, [$dataInicial, $dataFinal])->result();
    }

    public function financeiroCustom($dataInicial = null, $dataFinal = null, $tipo = null, $situacao = null)
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

        return $this->db->get('lancamentos')->result();
    }

    public function vendasRapid()
    {
        $this->db->select('vendas.*,clientes.nomeCliente, usuarios.nome');
        $this->db->from('vendas');
        $this->db->join('clientes', 'clientes.idClientes = vendas.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = vendas.usuarios_id');
        return $this->db->get()->result();
    }

    public function vendasCustom($dataInicial = null, $dataFinal = null, $cliente = null, $responsavel = null)
    {
        $whereData = "";
        $whereCliente = "";
        $whereResponsavel = "";
        $whereStatus = "";
        if ($dataInicial != null) {
            $whereData = "AND dataVenda BETWEEN " . $this->db->escape($dataInicial) . " AND " . $this->db->escape($dataFinal);
        }
        if ($cliente != null) {
            $whereCliente = "AND clientes_id = " . $this->db->escape($cliente);
        }
        if ($responsavel != null) {
            $whereResponsavel = "AND usuarios_id = " . $this->db->escape($responsavel);
        }

        $query = "SELECT vendas.*,clientes.nomeCliente,usuarios.nome FROM vendas LEFT JOIN clientes ON vendas.clientes_id = clientes.idClientes LEFT JOIN usuarios ON vendas.usuarios_id = usuarios.idUsuarios WHERE idVendas != 0 $whereData $whereCliente $whereResponsavel";
        return $this->db->query($query)->result();
    }
}
