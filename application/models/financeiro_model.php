<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Financeiro_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {

        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('data_vencimento', 'asc');
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = !$one ? $query->result() : $query->row();
        return $result;
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

    public function count($table)
    {
        return $this->db->count_all($table);
    }

    public function getEstatisticasFinanceiro($dataInicial, $dataFinal)
    {
        $sql = "SELECT SUM(CASE WHEN baixado = 1 AND tipo = 'receita' AND data_pagamento >= '$dataInicial' AND data_pagamento < '$dataFinal' THEN valor END) as total_receita,
                       SUM(CASE WHEN baixado = 1 AND tipo = 'despesa' AND data_pagamento >= '$dataInicial' AND data_pagamento < '$dataFinal' THEN valor END) as total_despesa,
                       SUM(CASE WHEN baixado = 0 AND tipo = 'receita' AND data_vencimento >= '$dataInicial' AND data_vencimento < '$dataFinal' THEN valor END) as total_receita_pendente,
                       SUM(CASE WHEN baixado = 0 AND tipo = 'despesa' AND data_vencimento >= '$dataInicial' AND data_vencimento < '$dataFinal' THEN valor END) as total_despesa_pendente FROM lancamentos";
        return $this->db->query($sql)->row();
    }

}
