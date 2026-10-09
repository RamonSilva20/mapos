<?php

class Usuarios_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Colunas de `usuarios` expostas para leitura.
     *
     * O hash da senha nunca entra aqui: estes resultados são serializados
     * direto na API e nas telas. A conferência de senha usa
     * Mapos_model::check_credentials, que lê a coluna separadamente.
     */
    private const CAMPOS_PUBLICOS = 'usuarios.idUsuarios, usuarios.nome, usuarios.rg, usuarios.cpf, usuarios.cep,
        usuarios.rua, usuarios.numero, usuarios.bairro, usuarios.cidade, usuarios.estado, usuarios.email,
        usuarios.telefone, usuarios.celular, usuarios.dataCadastro, usuarios.dataExpiracao, usuarios.situacao,
        usuarios.permissoes_id, usuarios.url_image_user';

    public function get($perpage = 0, $start = 0, $one = false)
    {
        $this->db->from('usuarios');
        $this->db->select(self::CAMPOS_PUBLICOS . ', permissoes.nome as permissao');
        $this->db->limit($perpage, $start);
        $this->db->join('permissoes', 'usuarios.permissoes_id = permissoes.idPermissao', 'left');

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    /**
     * Listagem de usuários da v5 (#2846): os mesmos filtros em listar() e
     * contar(). pesquisa procura no nome, e-mail, CPF e telefone; situacao é
     * ativo ou inativo.
     *
     * @param  array<string, string>  $filtros
     */
    public function listar(array $filtros, int $limite, int $offset): array
    {
        $this->db->select(self::CAMPOS_PUBLICOS . ', permissoes.nome AS permissao');
        $this->aplicarFiltros($filtros);

        return $this->db->order_by('usuarios.nome', 'ASC')->limit($limite, max(0, $offset))->get()->result();
    }

    public function contar(array $filtros): int
    {
        $this->aplicarFiltros($filtros);

        return (int) $this->db->count_all_results();
    }

    private function aplicarFiltros(array $filtros): void
    {
        $this->db->from('usuarios');
        $this->db->join('permissoes', 'usuarios.permissoes_id = permissoes.idPermissao', 'left');

        $pesquisa = $filtros['pesquisa'] ?? '';
        if ($pesquisa !== '') {
            $this->db->group_start()
                ->like('usuarios.nome', $pesquisa)
                ->or_like('usuarios.email', $pesquisa)
                ->or_like('usuarios.cpf', $pesquisa)
                ->or_like('usuarios.telefone', $pesquisa)
                ->group_end();
        }

        if (($filtros['situacao'] ?? '') === 'ativo') {
            $this->db->where('usuarios.situacao', 1);
        } elseif (($filtros['situacao'] ?? '') === 'inativo') {
            $this->db->where('usuarios.situacao', 0);
        }
    }

    /**
     * Quantos registros apontam para o usuário (OS, vendas, lançamentos e
     * termos de garantia, todos com chave estrangeira): com algum, a exclusão
     * falharia no banco, e o caminho é desativar.
     */
    public function referencias(int $id): int
    {
        $total = 0;
        foreach (['os', 'vendas', 'lancamentos', 'garantias'] as $tabela) {
            $total += (int) $this->db->where('usuarios_id', $id)->count_all_results($tabela);
        }

        return $total;
    }

    public function getAllTipos()
    {
        $this->db->where('situacao', 1);

        return $this->db->get('tiposUsuario')->result();
    }

    public function getById($id)
    {
        $this->db->select(self::CAMPOS_PUBLICOS);
        $this->db->where('idUsuarios', $id);
        $this->db->limit(1);

        return $this->db->get('usuarios')->row();
    }

    public function getAll()
    {
        $this->db->select(self::CAMPOS_PUBLICOS);

        return $this->db->get('usuarios')->result();
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
}
