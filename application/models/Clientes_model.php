<?php

class Clientes_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('idClientes', 'desc');
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->like('nomeCliente', $where);
            $this->db->or_like('documento', $where);
            $this->db->or_like('email', $where);
            $this->db->or_like('telefone', $where);
        }

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    /**
     * Clientes da listagem (#2852), do mais recente para o mais antigo.
     *
     * @param  array{pesquisa?: string, tipo?: 'cliente'|'fornecedor'}  $filtros  Saída de listagemFiltros()
     */
    public function listar(array $filtros, int $limite, int $offset): array
    {
        $this->aplicarFiltros($filtros);

        return $this->db
            ->order_by('idClientes', 'desc')
            ->limit($limite, max(0, $offset))
            ->get('clientes')
            ->result();
    }

    /**
     * Total da listagem com os mesmos filtros de listar(): a paginação conta
     * só o que a busca encontra.
     */
    public function contar(array $filtros): int
    {
        $this->aplicarFiltros($filtros);

        return (int) $this->db->count_all_results('clientes');
    }

    /**
     * pesquisa procura em nome, documento, e-mail, telefone e celular (o OR
     * fica entre parênteses, para não anular o filtro de tipo); tipo separa
     * clientes de fornecedores.
     */
    private function aplicarFiltros(array $filtros): void
    {
        if (($filtros['pesquisa'] ?? '') !== '') {
            $this->db->group_start()
                ->like('nomeCliente', $filtros['pesquisa'])
                ->or_like('documento', $filtros['pesquisa'])
                ->or_like('email', $filtros['pesquisa'])
                ->or_like('telefone', $filtros['pesquisa'])
                ->or_like('celular', $filtros['pesquisa'])
                ->group_end();
        }

        if (($filtros['tipo'] ?? '') !== '') {
            $this->db->where('fornecedor', $filtros['tipo'] === 'fornecedor' ? 1 : 0);
        }
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
            return $this->db->insert_id($table);
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

    /** OS do cliente, das mais recentes para as mais antigas. */
    public function osDoCliente(int $id, int $limite): array
    {
        return $this->db->where('clientes_id', $id)->order_by('idOs', 'desc')->limit($limite)->get('os')->result();
    }

    /** Vendas do cliente, das mais recentes para as mais antigas. */
    public function vendasDoCliente(int $id, int $limite): array
    {
        return $this->db->where('clientes_id', $id)->order_by('idVendas', 'desc')->limit($limite)->get('vendas')->result();
    }

    public function contarOsDoCliente(int $id): int
    {
        return (int) $this->db->where('clientes_id', $id)->count_all_results('os');
    }

    public function contarVendasDoCliente(int $id): int
    {
        return (int) $this->db->where('clientes_id', $id)->count_all_results('vendas');
    }

    public function getOsByCliente($id)
    {
        $this->db->where('clientes_id', $id);
        $this->db->order_by('idOs', 'desc');
        $this->db->limit(10);

        return $this->db->get('os')->result();
    }

    /**
     * Retorna todas as OS vinculados ao cliente
     *
     * @param  int  $id
     * @return array
     */
    public function getAllOsByClient($id)
    {
        $this->db->where('clientes_id', $id);

        return $this->db->get('os')->result();
    }

    /**
     * Remover todas as OS por cliente
     *
     * @param  array  $os
     * @return bool
     */
    public function removeClientOs($os)
    {
        try {
            foreach ($os as $o) {
                $this->db->where('os_id', $o->idOs);
                $this->db->delete('servicos_os');

                $this->db->where('os_id', $o->idOs);
                $this->db->delete('produtos_os');

                $this->db->where('idOs', $o->idOs);
                $this->db->delete('os');
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Retorna todas as Vendas vinculados ao cliente
     *
     * @param  int  $id
     * @return array
     */
    public function getAllVendasByClient($id)
    {
        $this->db->where('clientes_id', $id);

        return $this->db->get('vendas')->result();
    }

    /**
     * Remover todas as Vendas por cliente
     *
     * @param  array  $vendas
     * @return bool
     */
    public function removeClientVendas($vendas)
    {
        try {
            foreach ($vendas as $v) {
                $this->db->where('vendas_id', $v->idVendas);
                $this->db->delete('itens_de_vendas');

                $this->db->where('idVendas', $v->idVendas);
                $this->db->delete('vendas');
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Verifica se o e-mail já existe na tabela de clientes
     *
     * @param  string  $email
     * @param  int     $id (opcional, para excluir o próprio cliente na edição)
     * @return bool
     */
    public function emailExists($email, $id = null)
    {
        $this->db->where('email', $email);
        
        if ($id !== null) {
            $this->db->where('idClientes !=', $id);
        }
        
        $query = $this->db->get('clientes');
        
        return $query->num_rows() > 0;
    }
}
