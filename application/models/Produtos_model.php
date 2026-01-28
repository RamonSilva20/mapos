<?php

class Produtos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('idProdutos', 'desc');
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->like('codDeBarra', $where);
            $this->db->or_like('descricao', $where);
        }

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    /**
     * Busca produtos por uma ou mais palavras/partes do nome ou código de barras.
     * Cada termo é buscado com LIKE em codDeBarra OU descricao; todos os termos devem bater (AND).
     * Útil para buscar por palavras soltas ou trechos, ex.: "teclado gamer" ou "not".
     *
     * @param array $words Lista de termos (ex.: ['teclado', 'gamer'])
     * @param int   $perPage
     * @param int   $start
     * @return object[]
     */
    public function searchProducts(array $words, $perPage = 20, $start = 0)
    {
        $this->db->select('*');
        $this->db->from('produtos');
        $this->db->order_by('descricao', 'asc');

        foreach ($words as $term) {
            $term = trim($term);
            if ($term === '') {
                continue;
            }
            $this->db->group_start();
            $this->db->like('codDeBarra', $term);
            $this->db->or_like('descricao', $term);
            $this->db->group_end();
        }

        $this->db->limit($perPage, $start);

        return $this->db->get()->result();
    }

    public function getById($id)
    {
        $this->db->where('idProdutos', $id);
        $this->db->limit(1);

        return $this->db->get('produtos')->row();
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

    public function updateEstoque($produto, $quantidade, $operacao = '-')
    {
        $sql = "UPDATE produtos set estoque = estoque $operacao ? WHERE idProdutos = ?";

        return $this->db->query($sql, [$quantidade, $produto]);
    }
}
