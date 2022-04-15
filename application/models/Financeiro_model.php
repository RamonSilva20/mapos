<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Financeiro_model extends CI_Model
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
        $this->db->select($fields.', usuarios.*');
        $this->db->from($table);
        $this->db->join('usuarios', 'usuarios.idUsuarios = usuarios_id', 'left');
        $this->db->order_by('data_vencimento', 'asc');
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = !$one ? $query->result() : $query->row();

        return $result;
    }

    public function getTotals($where = '')
    {
        $this->db->select("
            SUM(case when tipo = 'despesa' then valor end) as despesas,
            SUM(case when tipo = 'receita' then valor end) as receitas
        ");
        $this->db->from('lancamentos');

        if ($where) {
            $this->db->where($where);
        }

        return (array) $this->db->get()->row();
    }


    public function getById($id)
    {
        $this->db->where('idClientes', $id);
        $this->db->limit(1);
        return $this->db->get('clientes')->row();
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

    public function count($table, $where)
    {
        $this->db->from($table);
        if ($where) {
            $this->db->where($where);
        }
        return $this->db->count_all_results();
    }

    public function autoCompleteClienteFornecedor($q)
    {
        $this->db->select('DISTINCT(cliente_fornecedor) as cliente_fornecedor');
        $this->db->limit(5);
        $this->db->like('cliente_fornecedor', $q);
        $query = $this->db->get('lancamentos');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = ['label' => $row['cliente_fornecedor'], 'id' => $row['cliente_fornecedor']];
            }
            echo json_encode($row_set);
        }
    }

    public function autoCompleteClienteReceita($q)
    {
        $this->db->select('idClientes, nomeCliente');
        $this->db->limit(5);
        $this->db->like('nomeCliente', $q);
        $query = $this->db->get('clientes');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = ['label' => $row['nomeCliente'], 'id' => $row['idClientes']];
            }
            echo json_encode($row_set);
        }
    }
}
