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
     * Diz se outro usuário já usa o CPF, com ou sem a máscara (cadastros
     * antigos podem ter só os números).
     */
    public function cpfEmUso(string $cpf, ?int $exceto): bool
    {
        $digitos = (string) preg_replace('/\D/', '', $cpf);
        $this->db->group_start()->where('cpf', $cpf)->or_where('cpf', $digitos)->group_end();
        if ($exceto !== null) {
            $this->db->where('idUsuarios !=', $exceto);
        }

        return $this->db->count_all_results('usuarios') > 0;
    }

    /** Outro usuário já usa o e-mail (sem diferenciar maiúsculas)? */
    public function emailEmUso(string $email, ?int $exceto): bool
    {
        return $this->db->query(
            'SELECT 1 FROM usuarios WHERE LOWER(email) = ? AND idUsuarios != ? LIMIT 1',
            [mb_strtolower($email), $exceto ?? 0]
        )->num_rows() > 0;
    }

    /** A senha confere com o hash do usuário? O hash é lido aqui e não sai do model. */
    public function senhaConfere(int $id, string $senha): bool
    {
        $linha = $this->db->select('senha')->where('idUsuarios', $id)->limit(1)->get('usuarios')->row();

        return $linha !== null && password_verify($senha, (string) $linha->senha);
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

    /** O usuário tem o 2FA ativo (#2876)? Falso se a migration ainda não rodou. */
    public function doisFatoresAtivo(int $id): bool
    {
        if (! $this->db->field_exists('totp_ativado_em', 'usuarios')) {
            return false;
        }

        return $this->db->where('idUsuarios', $id)->where('totp_ativado_em IS NOT NULL', null, false)->count_all_results('usuarios') > 0;
    }

    public function edit($table, $data, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0) {
            // Senha trocada ou conta desativada: as sessões da API não renovam mais (#2869).
            if ($table === 'usuarios' && $fieldID === 'idUsuarios' && (isset($data['senha']) || (isset($data['situacao']) && (int) $data['situacao'] !== 1))) {
                $this->load->library('Refresh_token');
                $this->refresh_token->revogarDoUsuario((int) $ID);
                // Os access tokens já emitidos também deixam de valer.
                $this->load->library('Authorization_Token');
                $this->authorization_token->incrementarVersao('usuarios', 'idUsuarios', (int) $ID);
            }

            // Senha trocada: as sessões abertas do painel também caem (#2879). Quem
            // edita o próprio usuário no painel renova a versão da sessão atual
            // (Usuarios::formulario); sem a migration não há o que invalidar.
            if ($table === 'usuarios' && $fieldID === 'idUsuarios' && isset($data['senha']) && $this->db->field_exists('sessao_versao', 'usuarios')) {
                $this->db->set('sessao_versao', 'sessao_versao + 1', false)->where('idUsuarios', (int) $ID)->update('usuarios');
            }

            return true;
        }

        return false;
    }

    public function delete($table, $fieldID, $ID)
    {
        // Os códigos de recuperação do 2FA não ficam órfãos (#2876).
        if ($table === 'usuarios' && $fieldID === 'idUsuarios' && $this->db->table_exists('usuarios_codigos_recuperacao')) {
            $this->db->where('usuario_id', $ID)->delete('usuarios_codigos_recuperacao');
        }

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
