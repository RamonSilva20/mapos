<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Contas_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('conta', 'asc');
        $this->db->limit($perpage, $start);
        
        if ($where) {
            $this->db->like('conta', $where);
            $this->db->or_like('banco', $where);
            $this->db->or_like('numero', $where);
        }

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        $this->db->where('idContas', $id);
        $this->db->limit(1);

        return $this->db->get('contas')->row();
    }

    public function add($table, $data)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return $this->db->insert_id();
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
        // Verificar se há lançamentos vinculados
        $this->db->where('contas_id', $ID);
        $count = $this->db->count_all_results('lancamentos');
        
        if ($count > 0) {
            return false; // Não pode excluir se houver lançamentos
        }

        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table, $where = '')
    {
        if ($where) {
            $this->db->like('conta', $where);
            $this->db->or_like('banco', $where);
            $this->db->or_like('numero', $where);
        }
        return $this->db->count_all_results($table);
    }

    public function getAll()
    {
        $this->db->order_by('conta', 'asc');
        return $this->db->get('contas')->result();
    }

    public function hasLancamentos($id)
    {
        $this->db->where('contas_id', $id);
        return $this->db->count_all_results('lancamentos') > 0;
    }

    /**
     * Calcula o saldo atual de uma conta baseado nos lançamentos
     * 
     * @param int $idConta
     * @return float
     */
    public function calcularSaldo($idConta)
    {
        // Receitas pagas (entrada)
        $this->db->select_sum('valor_desconto', 'total_receitas');
        $this->db->where('contas_id', $idConta);
        $this->db->where('tipo', 'receita');
        $this->db->where('baixado', 1);
        $receitas = $this->db->get('lancamentos')->row();
        $totalReceitas = $receitas->total_receitas ?? 0;

        // Despesas pagas (saída)
        $this->db->select_sum('valor_desconto', 'total_despesas');
        $this->db->where('contas_id', $idConta);
        $this->db->where('tipo', 'despesa');
        $this->db->where('baixado', 1);
        $despesas = $this->db->get('lancamentos')->row();
        $totalDespesas = $despesas->total_despesas ?? 0;

        // Saldo = Receitas - Despesas
        $saldo = $totalReceitas - $totalDespesas;

        return $saldo;
    }

    /**
     * Atualiza o saldo de uma conta no banco
     * 
     * @param int $idConta
     * @return bool
     */
    public function atualizarSaldo($idConta)
    {
        $saldo = $this->calcularSaldo($idConta);
        
        $this->db->where('idContas', $idConta);
        $this->db->update('contas', ['saldo' => $saldo]);
        
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Retorna o extrato de uma conta
     * 
     * @param int $idConta
     * @param string $dataInicio
     * @param string $dataFim
     * @return array
     */
    public function getExtrato($idConta, $dataInicio = null, $dataFim = null)
    {
        $this->db->select('lancamentos.*, categorias.categoria as nome_categoria');
        $this->db->from('lancamentos');
        $this->db->join('categorias', 'categorias.idCategorias = lancamentos.categorias_id', 'left');
        $this->db->where('lancamentos.contas_id', $idConta);
        
        if ($dataInicio) {
            $this->db->where('lancamentos.data_vencimento >=', $dataInicio);
        }
        if ($dataFim) {
            $this->db->where('lancamentos.data_vencimento <=', $dataFim);
        }
        
        $this->db->order_by('lancamentos.data_vencimento', 'desc');
        $this->db->order_by('lancamentos.idLancamentos', 'desc');
        
        return $this->db->get()->result();
    }
}



