<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Arquivos_model extends CI_Model
{
    /**
     * Arquivos da listagem (#2845), do mais novo para o mais antigo.
     *
     * @param  array<string, string>  $filtros  pesquisa (nome ou descrição),
     *                                          de e ate (AAAA-MM-DD, pela data do arquivo)
     * @return list<object>
     */
    public function listar(array $filtros, int $limite, int $offset): array
    {
        $this->aplicarFiltros($filtros);

        return $this->db->order_by('idDocumentos', 'desc')->limit($limite, max(0, $offset))->get('documentos')->result();
    }

    public function contar(array $filtros): int
    {
        $this->aplicarFiltros($filtros);

        return (int) $this->db->count_all_results('documentos');
    }

    private function aplicarFiltros(array $filtros): void
    {
        if (($filtros['pesquisa'] ?? '') !== '') {
            $this->db->group_start()->like('documento', $filtros['pesquisa'])->or_like('descricao', $filtros['pesquisa'])->group_end();
        }

        if (($filtros['de'] ?? '') !== '') {
            $this->db->where('cadastro >=', $filtros['de']);
        }

        if (($filtros['ate'] ?? '') !== '') {
            $this->db->where('cadastro <=', $filtros['ate']);
        }
    }

    public function getById($id)
    {
        $this->db->where('idDocumentos', $id);
        $this->db->limit(1);

        return $this->db->get('documentos')->row();
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
}

/* End of file arquivos_model.php */
/* Location: ./application/models/arquivos_model.php */
