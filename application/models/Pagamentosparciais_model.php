<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Model para gerenciamento de pagamentos parciais
 */
class Pagamentosparciais_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca todos os pagamentos de um lançamento
     */
    public function getByLancamento($lancamentoId)
    {
        $this->db->select('pagamentos_parciais.*, usuarios.nome as usuario_nome');
        $this->db->from('pagamentos_parciais');
        $this->db->join('usuarios', 'usuarios.idUsuarios = pagamentos_parciais.usuarios_id', 'left');
        $this->db->where('lancamentos_id', $lancamentoId);
        $this->db->order_by('data_pagamento', 'ASC');
        $this->db->order_by('created_at', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Adiciona um pagamento parcial
     */
    public function add($data)
    {
        $this->db->insert('pagamentos_parciais', $data);
        
        if ($this->db->affected_rows() == 1) {
            $pagamentoId = $this->db->insert_id();
            
            // Atualizar valor_pago e status do lançamento
            $this->atualizarLancamento($data['lancamentos_id']);
            
            return $pagamentoId;
        }
        
        return false;
    }

    /**
     * Exclui um pagamento parcial
     */
    public function delete($id)
    {
        // Buscar lancamento_id antes de excluir
        $pagamento = $this->getById($id);
        if (!$pagamento) {
            return false;
        }
        
        $lancamentoId = $pagamento->lancamentos_id;
        
        $this->db->where('idPagamento', $id);
        $this->db->delete('pagamentos_parciais');
        
        if ($this->db->affected_rows() == 1) {
            // Atualizar valor_pago e status do lançamento
            $this->atualizarLancamento($lancamentoId);
            return true;
        }
        
        return false;
    }

    /**
     * Busca um pagamento pelo ID
     */
    public function getById($id)
    {
        $this->db->where('idPagamento', $id);
        return $this->db->get('pagamentos_parciais')->row();
    }

    /**
     * Calcula o total pago de um lançamento
     */
    public function getTotalPago($lancamentoId)
    {
        $this->db->select_sum('valor');
        $this->db->where('lancamentos_id', $lancamentoId);
        $result = $this->db->get('pagamentos_parciais')->row();
        
        return floatval($result->valor ?? 0);
    }

    /**
     * Atualiza o valor_pago e status do lançamento
     */
    public function atualizarLancamento($lancamentoId)
    {
        // Buscar lançamento
        $this->load->model('financeiro_model');
        $lancamento = $this->financeiro_model->getLancamentoById($lancamentoId);
        
        if (!$lancamento) {
            return false;
        }
        
        // Calcular total pago
        $totalPago = $this->getTotalPago($lancamentoId);
        
        // Determinar valor total do lançamento
        $valorTotal = $lancamento->valor_desconto > 0 ? $lancamento->valor_desconto : $lancamento->valor;
        
        // Determinar status
        if ($totalPago >= $valorTotal) {
            $status = 'pago';
            $baixado = 1;
        } elseif ($totalPago > 0) {
            $status = 'parcial';
            $baixado = 0;
        } else {
            $status = 'pendente';
            $baixado = 0;
        }
        
        // Atualizar lançamento
        $this->db->where('idLancamentos', $lancamentoId);
        $this->db->update('lancamentos', [
            'valor_pago' => $totalPago,
            'status_pagamento' => $status,
            'baixado' => $baixado
        ]);
        
        return true;
    }

    /**
     * Calcula saldo restante do lançamento
     */
    public function getSaldoRestante($lancamentoId)
    {
        $this->load->model('financeiro_model');
        $lancamento = $this->financeiro_model->getLancamentoById($lancamentoId);
        
        if (!$lancamento) {
            return 0;
        }
        
        $valorTotal = $lancamento->valor_desconto > 0 ? $lancamento->valor_desconto : $lancamento->valor;
        $totalPago = $this->getTotalPago($lancamentoId);
        
        return max(0, $valorTotal - $totalPago);
    }

    /**
     * Calcula percentual pago
     */
    public function getPercentualPago($lancamentoId)
    {
        $this->load->model('financeiro_model');
        $lancamento = $this->financeiro_model->getLancamentoById($lancamentoId);
        
        if (!$lancamento) {
            return 0;
        }
        
        $valorTotal = $lancamento->valor_desconto > 0 ? $lancamento->valor_desconto : $lancamento->valor;
        if ($valorTotal <= 0) {
            return 100;
        }
        
        $totalPago = $this->getTotalPago($lancamentoId);
        
        return min(100, round(($totalPago / $valorTotal) * 100, 2));
    }
}

