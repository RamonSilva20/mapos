<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Financeiro_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        // Incluir campos de pagamento parcial se existirem
        $this->db->select($fields . ', usuarios.*, lancamentos.valor_pago, lancamentos.status_pagamento');
        $this->db->from($table);
        $this->db->join('usuarios', 'usuarios.idUsuarios = usuarios_id', 'left');
        $this->db->order_by('data_vencimento', 'asc');
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getTotals($where = '')
    {
        $this->db->select("
            SUM(case when tipo = 'despesa' then valor - desconto end) as despesas,
            SUM(case when tipo = 'receita' then (IF(valor_desconto = 0, valor, valor_desconto)) end) as receitas
        ");
        $this->db->from('lancamentos');

        if ($where) {
            $this->db->where($where);
        }

        return (array) $this->db->get()->row();
    }

    public function getEstatisticasFinanceiro2()
    {
        $sql = "SELECT SUM(CASE WHEN baixado = 1 AND tipo = 'receita' THEN IF(valor_desconto = 0, valor, valor_desconto) END) as total_receita,
                       SUM(CASE WHEN baixado = 1 AND tipo = 'despesa' THEN valor - desconto END) as total_despesa,
                       SUM(CASE WHEN baixado = 1 THEN desconto END) as total_valor_desconto,
                       SUM(CASE WHEN baixado = 0 THEN valor - valor_desconto END) as total_valor_desconto_pendente,
                       SUM(CASE WHEN tipo = 'receita' THEN valor END) as total_receita_sem_desconto,
                       SUM(CASE WHEN tipo = 'despesa' THEN valor END) as total_despesa_sem_desconto,
                       SUM(CASE WHEN baixado = 0 AND tipo = 'receita' THEN valor_desconto END) as total_receita_pendente,
                       SUM(CASE WHEN baixado = 0 AND tipo = 'despesa' THEN valor_desconto END) as total_despesa_pendente FROM lancamentos";

        return $this->db->query($sql)->row();
    }

    public function getById($id)
    {
        $this->db->where('idClientes', $id);
        $this->db->limit(1);

        return $this->db->get('clientes')->row();
    }

    public function getLancamentoById($id)
    {
        $this->db->select('lancamentos.*, clientes.nomeCliente, clientes.documento, clientes.rua, clientes.numero, clientes.bairro, clientes.complemento, clientes.cidade, clientes.estado, clientes.cep, clientes.telefone, clientes.celular, clientes.email, usuarios.nome as usuario_nome');
        $this->db->from('lancamentos');
        $this->db->join('clientes', 'clientes.idClientes = lancamentos.clientes_id', 'left');
        $this->db->join('usuarios', 'usuarios.idUsuarios = lancamentos.usuarios_id', 'left');
        $this->db->where('lancamentos.idLancamentos', $id);
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function add($table, $data)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function add1($table, $data1)
    {
        $this->db->insert($table, $data1);
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

    /**
     * Busca dados para o dashboard financeiro
     */
    public function getDashboardData()
    {
        $hoje = date('Y-m-d');
        $proximos7Dias = date('Y-m-d', strtotime('+7 days'));

        // Total a Receber (pendente)
        $this->db->select("SUM(IF(valor_desconto = 0, valor, valor_desconto)) as total");
        $this->db->from('lancamentos');
        $this->db->where('tipo', 'receita');
        $this->db->where('baixado', 0);
        $query = $this->db->get();
        $totalReceber = ($query && $query->num_rows() > 0) ? ($query->row()->total ?? 0) : 0;

        // Total a Pagar (pendente)
        $this->db->select("SUM(valor - desconto) as total");
        $this->db->from('lancamentos');
        $this->db->where('tipo', 'despesa');
        $this->db->where('baixado', 0);
        $query = $this->db->get();
        $totalPagar = ($query && $query->num_rows() > 0) ? ($query->row()->total ?? 0) : 0;

        // Saldo Atual (receitas pagas - despesas pagas)
        $this->db->select("
            SUM(CASE WHEN tipo = 'receita' AND baixado = 1 THEN IF(valor_desconto = 0, valor, valor_desconto) END) as receitas_pagas,
            SUM(CASE WHEN tipo = 'despesa' AND baixado = 1 THEN valor - desconto END) as despesas_pagas
        ");
        $this->db->from('lancamentos');
        $query = $this->db->get();
        $saldo = ($query && $query->num_rows() > 0) ? $query->row() : null;
        $saldoAtual = $saldo ? (($saldo->receitas_pagas ?? 0) - ($saldo->despesas_pagas ?? 0)) : 0;

        // Contas Vencidas
        $this->db->select("COUNT(*) as total");
        $this->db->from('lancamentos');
        $this->db->where('baixado', 0);
        $this->db->where('data_vencimento <', $hoje);
        $query = $this->db->get();
        $contasVencidas = ($query && $query->num_rows() > 0) ? ($query->row()->total ?? 0) : 0;

        // Contas a vencer (próximos 7 dias)
        $this->db->select("COUNT(*) as total");
        $this->db->from('lancamentos');
        $this->db->where('baixado', 0);
        $this->db->where('data_vencimento >=', $hoje);
        $this->db->where('data_vencimento <=', $proximos7Dias);
        $query = $this->db->get();
        $contasAVencer = ($query && $query->num_rows() > 0) ? ($query->row()->total ?? 0) : 0;

        return [
            'totalReceber' => floatval($totalReceber),
            'totalPagar' => floatval($totalPagar),
            'saldoAtual' => floatval($saldoAtual),
            'contasVencidas' => intval($contasVencidas),
            'contasAVencer' => intval($contasAVencer)
        ];
    }

    /**
     * Busca receitas e despesas do mês atual
     */
    public function getReceitasDespesasMes()
    {
        $mesAtual = date('Y-m');
        
        $this->db->select("
            SUM(CASE WHEN tipo = 'receita' AND baixado = 1 THEN IF(valor_desconto = 0, valor, valor_desconto) END) as receitas,
            SUM(CASE WHEN tipo = 'despesa' AND baixado = 1 THEN valor - desconto END) as despesas
        ");
        $this->db->from('lancamentos');
        $this->db->where("DATE_FORMAT(COALESCE(data_pagamento, data_vencimento), '%Y-%m')", $mesAtual);
        $this->db->where('baixado', 1);
        
        $query = $this->db->get();
        if ($query && $query->num_rows() > 0) {
            return $query->row();
        }
        
        // Retorna objeto vazio se não houver dados
        return (object) ['receitas' => 0, 'despesas' => 0];
    }

    /**
     * Busca fluxo de caixa dos últimos 6 meses
     */
    public function getFluxoCaixa6Meses()
    {
        $meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $data = date('Y-m', strtotime("-$i months"));
            $meses[] = $data;
        }

        $resultado = [];
        foreach ($meses as $mes) {
            $this->db->select("
                SUM(CASE WHEN tipo = 'receita' AND baixado = 1 THEN IF(valor_desconto = 0, valor, valor_desconto) END) as receitas,
                SUM(CASE WHEN tipo = 'despesa' AND baixado = 1 THEN valor - desconto END) as despesas
            ");
            $this->db->from('lancamentos');
            $this->db->where("DATE_FORMAT(COALESCE(data_pagamento, data_vencimento), '%Y-%m')", $mes);
            $this->db->where('baixado', 1);
            
            $query = $this->db->get();
            $dados = ($query && $query->num_rows() > 0) ? $query->row() : null;
            $resultado[] = [
                'mes' => $mes,
                'mes_nome' => $this->getNomeMes($mes),
                'receitas' => floatval($dados->receitas ?? 0),
                'despesas' => floatval($dados->despesas ?? 0)
            ];
        }

        return $resultado;
    }

    /**
     * Busca contas a vencer (próximos 7 dias)
     */
    public function getContasAVencer()
    {
        $hoje = date('Y-m-d');
        $proximos7Dias = date('Y-m-d', strtotime('+7 days'));

        $this->db->select('lancamentos.*, clientes.nomeCliente');
        $this->db->from('lancamentos');
        $this->db->join('clientes', 'clientes.idClientes = lancamentos.clientes_id', 'left');
        $this->db->where('lancamentos.baixado', 0);
        $this->db->where('lancamentos.data_vencimento >=', $hoje);
        $this->db->where('lancamentos.data_vencimento <=', $proximos7Dias);
        $this->db->order_by('lancamentos.data_vencimento', 'ASC');
        $this->db->limit(10);

        $query = $this->db->get();
        return ($query && $query->num_rows() > 0) ? $query->result() : [];
    }

    /**
     * Busca contas vencidas
     */
    public function getContasVencidas()
    {
        $hoje = date('Y-m-d');

        $this->db->select('lancamentos.*, clientes.nomeCliente');
        $this->db->from('lancamentos');
        $this->db->join('clientes', 'clientes.idClientes = lancamentos.clientes_id', 'left');
        $this->db->where('lancamentos.baixado', 0);
        $this->db->where('lancamentos.data_vencimento <', $hoje);
        $this->db->order_by('lancamentos.data_vencimento', 'ASC');
        $this->db->limit(10);

        $query = $this->db->get();
        return ($query && $query->num_rows() > 0) ? $query->result() : [];
    }

    /**
     * Retorna nome do mês em português
     */
    private function getNomeMes($mes)
    {
        $meses = [
            '01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr',
            '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
            '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez'
        ];
        
        $partes = explode('-', $mes);
        return $meses[$partes[1]] ?? $mes;
    }
}
