<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Garantias_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields . ', usuarios.nome, usuarios.idUsuarios');
        $this->db->from($table);
        $this->db->limit($perpage, $start);
        $this->db->join('usuarios', 'usuarios.idUsuarios = garantias.usuarios_id');
        $this->db->order_by('idGarantias', 'asc');
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = !$one ? $query->result() : $query->row();

        return $result;
    }

    /**
     * Termos da listagem (#2845), do mais novo para o mais antigo, com o nome
     * do responsável. A busca olha a referência e o texto do termo.
     *
     * @param  array<string, string>  $filtros  listagemFiltros()
     * @return list<object>
     */
    public function listar(array $filtros, int $limite, int $offset): array
    {
        $this->aplicarFiltros($filtros);

        return $this->db->select('garantias.*, usuarios.nome')
            ->from('garantias')
            ->join('usuarios', 'usuarios.idUsuarios = garantias.usuarios_id', 'left')
            ->order_by('garantias.idGarantias', 'desc')
            ->limit($limite, max(0, $offset))
            ->get()->result();
    }

    /**
     * Quantas OS usam o termo. O banco não tem a chave estrangeira
     * os.garantias_id (só o índice), então o DELETE não é recusado sozinho.
     */
    public function osVinculadas(int $id): int
    {
        return (int) $this->db->where('garantias_id', $id)->count_all_results('os');
    }

    public function contar(array $filtros): int
    {
        $this->aplicarFiltros($filtros);

        return (int) $this->db->count_all_results('garantias');
    }

    private function aplicarFiltros(array $filtros): void
    {
        if (($filtros['pesquisa'] ?? '') !== '') {
            $this->db->group_start()
                ->like('garantias.refGarantia', $filtros['pesquisa'])
                ->or_like('garantias.textoGarantia', $filtros['pesquisa'])
                ->group_end();
        }
    }

    public function getById($id)
    {
        $this->db->select('garantias.*, usuarios.telefone, usuarios.email, usuarios.nome');
        $this->db->from('garantias');
        $this->db->join('usuarios', 'usuarios.idUsuarios = garantias.usuarios_id');
        $this->db->where('garantias.idGarantias', $id);
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function getByIdOsGarantia($id)
    {
        $this->db->select('garantias.*, clientes.nomeCliente, os.idOS as idOs, os.dataFinal as osDataFinal,
         usuarios.telefone as tecnicoTelefone, usuarios.email as tecnicoEmail, usuarios.nome as tecnicoName');
        $this->db->from('garantias');
        $this->db->join('os', 'os.garantias_id = garantias.idGarantias');
        $this->db->join('clientes', 'os.clientes_id = clientes.idClientes');
        $this->db->join('usuarios', 'os.usuarios_id = usuarios.idUsuarios');
        $this->db->where('os.idOs', $id);
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function add($table, $data, $returnId = false)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            if ($returnId == true) {
                return $this->db->insert_id($table);
            }

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
}

/* End of file vendas_model.php */
/* Location: ./application/models/vendas_model.php */
