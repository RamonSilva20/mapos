<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Model para gerenciamento de parcelas de OS
 */
class Parcelas_os_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca todas as parcelas de uma OS
     */
    public function getByOs($os_id)
    {
        $this->db->where('os_id', $os_id);
        $this->db->order_by('numero', 'ASC');
        $query = $this->db->get('parcelas_os');
        return $query ? $query->result() : [];
    }

    /**
     * Busca uma parcela específica
     */
    public function getById($id)
    {
        $this->db->where('idParcela', $id);
        $query = $this->db->get('parcelas_os');
        return $query && $query->num_rows() > 0 ? $query->row() : null;
    }

    /**
     * Adiciona uma parcela
     */
    public function add($data)
    {
        return $this->db->insert('parcelas_os', $data);
    }

    /**
     * Atualiza uma parcela
     */
    public function update($id, $data)
    {
        $this->db->where('idParcela', $id);
        return $this->db->update('parcelas_os', $data);
    }

    /**
     * Remove uma parcela
     */
    public function delete($id)
    {
        $this->db->where('idParcela', $id);
        return $this->db->delete('parcelas_os');
    }

    /**
     * Remove todas as parcelas de uma OS
     */
    public function deleteByOs($os_id)
    {
        $this->db->where('os_id', $os_id);
        return $this->db->delete('parcelas_os');
    }

    /**
     * Salva múltiplas parcelas (usado ao criar/editar OS)
     */
    public function saveParcelas($os_id, $parcelas)
    {
        // Remove parcelas antigas
        $this->deleteByOs($os_id);

        if (empty($parcelas) || !is_array($parcelas)) {
            return true;
        }

        // Calcula data base (data final da OS ou hoje)
        $os = $this->db->select('dataFinal')->where('idOs', $os_id)->get('os')->row();
        $dataBase = $os && $os->dataFinal ? $os->dataFinal : date('Y-m-d');

        foreach ($parcelas as $parcela) {
            $data = [
                'os_id' => $os_id,
                'numero' => intval($parcela['numero']),
                'dias' => intval($parcela['dias']),
                'valor' => floatval($parcela['valor']),
                'observacao' => $parcela['observacao'] ?? '',
                'data_vencimento' => date('Y-m-d', strtotime($dataBase . ' + ' . intval($parcela['dias']) . ' days')),
                'status' => 'pendente'
            ];
            $this->add($data);
        }

        return true;
    }
}



