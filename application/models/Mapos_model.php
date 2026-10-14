<?php

class Mapos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        $this->db->from('usuarios');
        $this->db->select('usuarios.*, permissoes.nome as permissao');
        $this->db->join('permissoes', 'permissoes.idPermissao = usuarios.permissoes_id', 'left');
        $this->db->where('idUsuarios', $id);
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function alterarSenha($senha)
    {
        $this->db->set('senha', password_hash($senha, PASSWORD_DEFAULT));
        $this->db->where('idUsuarios', $this->session->userdata('id_admin'));
        $this->db->update('usuarios');

        if ($this->db->affected_rows() >= 0) {
            // As outras sessões do painel deste usuário deixam de valer (#2879);
            // a atual segue com a versão nova.
            $versao = $this->invalidarSessoes((int) $this->session->userdata('id_admin'));
            if ($versao !== null) {
                $this->session->set_userdata('sessao_versao', $versao);
            }

            // As sessões da API deste usuário deixam de renovar (#2869).
            $this->load->library('Refresh_token');
            $this->refresh_token->revogarDoUsuario((int) $this->session->userdata('id_admin'));
            $this->load->library('Authorization_Token');
            $this->authorization_token->incrementarVersao('usuarios', 'idUsuarios', (int) $this->session->userdata('id_admin'));

            return true;
        }

        return false;
    }

    /**
     * Soma 1 à versão de sessão do usuário: as sessões do painel abertas com a
     * versão anterior caem na próxima requisição (#2879). Devolve a versão nova,
     * ou null se a migration ainda não rodou (nada a invalidar).
     */
    public function invalidarSessoes(int $idUsuario): ?int
    {
        if (! $this->db->field_exists('sessao_versao', 'usuarios')) {
            return null;
        }

        $this->db->set('sessao_versao', 'sessao_versao + 1', false)->where('idUsuarios', $idUsuario)->update('usuarios');

        $linha = $this->db->select('sessao_versao')->where('idUsuarios', $idUsuario)->limit(1)->get('usuarios')->row();

        return (int) ($linha->sessao_versao ?? 0);
    }

    /**
     * Estado atual do usuário da sessão do painel, lido do banco a cada
     * requisição (#2879): conta ativa, expiração, grupo e versão da sessão.
     * Devolve null se o usuário não existe mais.
     *
     * @return array{situacao: int, expirada: bool, permissoes_id: int, grupo_ativo: bool, versao: int, exige_2fa_cadastro: bool}|null
     */
    public function estadoDaSessao(int $idUsuario): ?array
    {
        // Colunas de migrations recentes só entram se já existirem.
        $campos = 'situacao, dataExpiracao, permissoes_id';
        foreach (['totp_ativado_em', 'sessao_versao'] as $coluna) {
            if ($this->db->field_exists($coluna, 'usuarios')) {
                $campos .= ', ' . $coluna;
            }
        }
        $usuario = $this->db->select($campos)->where('idUsuarios', $idUsuario)->limit(1)->get('usuarios')->row();

        if (! $usuario) {
            return null;
        }

        $grupo = (int) $usuario->permissoes_id;

        return [
            'situacao' => (int) $usuario->situacao,
            'expirada' => contaExpirada($usuario->dataExpiracao),
            'permissoes_id' => $grupo,
            'grupo_ativo' => $this->grupoAtivo($grupo),
            'versao' => (int) ($usuario->sessao_versao ?? 0),
            'exige_2fa_cadastro' => ! ($usuario->totp_ativado_em ?? null) && $this->grupoExige2fa($grupo),
        ];
    }

    public function pesquisar($termo)
    {
        $data = [];
        // buscando clientes
        $this->db->like('nomeCliente', $termo);
        $this->db->or_like('telefone', $termo);
        $this->db->or_like('celular', $termo);
        $this->db->or_like('documento', $termo);
        $this->db->limit(15);
        $data['clientes'] = $this->db->get('clientes')->result();

        // buscando os
        $this->db->like('idOs', $termo);
        $this->db->or_like('descricaoProduto', $termo);
        $this->db->limit(15);
        $data['os'] = $this->db->get('os')->result();

        // buscando produtos
        $this->db->like('codDeBarra', $termo);
        $this->db->or_like('descricao', $termo);
        $this->db->limit(50);
        $data['produtos'] = $this->db->get('produtos')->result();

        //buscando serviços
        $this->db->like('nome', $termo);
        $this->db->limit(15);
        $data['servicos'] = $this->db->get('servicos')->result();

        return $data;
    }

    /**
     * Busca da paleta Ctrl+K: poucos resultados por grupo e só as colunas que
     * a lista mostra. Cada consulta agrupa seus OR; o Query Builder escapa % e _ do termo.
     *
     * @return array{clientes: list<object>, os: list<object>, produtos: list<object>, servicos: list<object>}
     */
    public function buscaRapida(string $termo, int $limite = 5): array
    {
        $dados = [];

        $dados['clientes'] = $this->db->select('idClientes, nomeCliente, documento, telefone, celular')
            ->group_start()->like('nomeCliente', $termo)->or_like('documento', $termo)->or_like('telefone', $termo)->or_like('celular', $termo)->group_end()
            ->order_by('nomeCliente')->limit($limite)->get('clientes')->result();

        $dados['os'] = $this->db->select('os.idOs, os.status, clientes.nomeCliente')
            ->join('clientes', 'clientes.idClientes = os.clientes_id', 'left')
            ->group_start()->like('os.idOs', $termo)->or_like('clientes.nomeCliente', $termo)->or_like('os.descricaoProduto', $termo)->group_end()
            ->order_by('os.idOs', 'DESC')->limit($limite)->get('os')->result();

        $dados['produtos'] = $this->db->select('idProdutos, descricao, precoVenda, estoque')
            ->group_start()->like('descricao', $termo)->or_like('codDeBarra', $termo)->group_end()
            ->order_by('descricao')->limit($limite)->get('produtos')->result();

        $dados['servicos'] = $this->db->select('idServicos, nome, preco')
            ->like('nome', $termo)
            ->order_by('nome')->limit($limite)->get('servicos')->result();

        return $dados;
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
    
    public function getOsAbertas()
    {
        $this->db->select('os.*, clientes.nomeCliente');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->where('os.status', 'Aberto');
        $this->db->limit(10);

        return $this->db->get()->result();
    }

    public function getOsAndamento()
    {
        $this->db->select('os.*, clientes.nomeCliente');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->where('os.status', 'Em Andamento');
        $this->db->limit(10);

        return $this->db->get()->result();
    }
    
    /*
     * Painel inicial da v5 (#2847).
     */

    /**
     * Quantidade de OS ou de vendas por status.
     *
     * @return array<string, int>  status => quantidade
     */
    public function contarPorStatus(string $tabela): array
    {
        if (! in_array($tabela, ['os', 'vendas'], true)) {
            throw new InvalidArgumentException("Tabela sem status no painel: {$tabela}");
        }

        $contagem = [];
        foreach ($this->db->select('status, COUNT(*) AS total')->group_by('status')->get($tabela)->result() as $linha) {
            $contagem[(string) $linha->status] = (int) $linha->total;
        }

        return $contagem;
    }

    /**
     * OS nos status pedidos, da entrega mais próxima para a mais distante (sem
     * data de entrega no fim), para a lista do painel.
     */
    public function osParaPainel(array $status, int $limite): array
    {
        return $this->db
            ->select('os.idOs, os.status, os.dataFinal, os.clientes_id, clientes.nomeCliente')
            ->from('os')
            ->join('clientes', 'clientes.idClientes = os.clientes_id', 'left')
            ->where_in('os.status', $status)
            ->order_by('os.dataFinal IS NULL', 'ASC', false)
            ->order_by('os.dataFinal', 'ASC')
            ->order_by('os.idOs', 'DESC')
            ->limit($limite)
            ->get()
            ->result();
    }

    /** Vendas nos status pedidos, da mais recente para a mais antiga. */
    public function vendasParaPainel(array $status, int $limite): array
    {
        return $this->db
            ->select('vendas.idVendas, vendas.status, vendas.dataVenda, vendas.clientes_id, clientes.nomeCliente')
            ->from('vendas')
            ->join('clientes', 'clientes.idClientes = vendas.clientes_id', 'left')
            ->where_in('vendas.status', $status)
            ->order_by('vendas.idVendas', 'DESC')
            ->limit($limite)
            ->get()
            ->result();
    }

    /**
     * Produtos no estoque mínimo ou abaixo dele (só os que têm mínimo), os que
     * estão mais abaixo primeiro.
     */
    public function produtosEstoqueBaixo(int $limite): array
    {
        return $this->db
            ->select('idProdutos, descricao, estoque, estoqueMinimo')
            ->from('produtos')
            ->where('estoqueMinimo >', 0)
            ->where('estoque <= estoqueMinimo', null, false)
            ->order_by('(estoque - estoqueMinimo)', 'ASC', false)
            ->order_by('descricao', 'ASC')
            ->limit($limite)
            ->get()
            ->result();
    }

    public function calendario($start, $end, $status = null)
    {
        // Com o desconto individual dos itens (#2880), o valor do item é o subTotal gravado.
        $valorProduto = 'produtos_os.preco * produtos_os.quantidade';
        $valorServico = 'servicos_os.preco * servicos_os.quantidade';
        if ($this->db->field_exists('desconto', 'produtos_os') && $this->db->field_exists('desconto', 'servicos_os')) {
            $valorProduto = "CASE WHEN produtos_os.desconto > 0 THEN produtos_os.subTotal ELSE {$valorProduto} END";
            $valorServico = "CASE WHEN servicos_os.desconto > 0 THEN servicos_os.subTotal ELSE {$valorServico} END";
        }

        $this->db->select(
            "os.*,
            clientes.nomeCliente,
            COALESCE((SELECT SUM({$valorProduto}) FROM produtos_os WHERE produtos_os.os_id = os.idOs), 0) totalProdutos,
            COALESCE((SELECT SUM({$valorServico}) FROM servicos_os WHERE servicos_os.os_id = os.idOs), 0) totalServicos"
        );
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->join('produtos_os', 'produtos_os.os_id = os.idOs', 'left');
        $this->db->join('servicos_os', 'servicos_os.os_id = os.idOs', 'left');
        $this->db->where('os.dataFinal >=', $start);
        $this->db->where('os.dataFinal <=', $end);
        $this->db->group_by('os.idOs');

        if (! empty($status)) {
            $this->db->where('os.status', $status);
        }

        return $this->db->get()->result();
    }

    public function getProdutosMinimo()
    {
        $sql = 'SELECT * FROM produtos WHERE estoque <= estoqueMinimo AND estoqueMinimo > 0 LIMIT 10';

        return $this->db->query($sql)->result();
    }

    public function getEmitente()
    {
        return $this->db->get('emitente')->row();
    }

    public function addEmitente($nome, $cnpj, $ie, $cep, $logradouro, $numero, $bairro, $cidade, $uf, $telefone, $email, $logo)
    {
        $this->db->set('nome', $nome);
        $this->db->set('cnpj', $cnpj);
        $this->db->set('ie', $ie);
        $this->db->set('cep', $cep);
        $this->db->set('rua', $logradouro);
        $this->db->set('numero', $numero);
        $this->db->set('bairro', $bairro);
        $this->db->set('cidade', $cidade);
        $this->db->set('uf', $uf);
        $this->db->set('telefone', $telefone);
        $this->db->set('email', $email);
        $this->db->set('url_logo', $logo);

        return $this->db->insert('emitente');
    }

    public function editEmitente($id, $nome, $cnpj, $ie, $cep, $logradouro, $numero, $bairro, $cidade, $uf, $telefone, $email)
    {
        $this->db->set('nome', $nome);
        $this->db->set('cnpj', $cnpj);
        $this->db->set('ie', $ie);
        $this->db->set('cep', $cep);
        $this->db->set('rua', $logradouro);
        $this->db->set('numero', $numero);
        $this->db->set('bairro', $bairro);
        $this->db->set('cidade', $cidade);
        $this->db->set('uf', $uf);
        $this->db->set('telefone', $telefone);
        $this->db->set('email', $email);
        $this->db->where('id', $id);

        return $this->db->update('emitente');
    }

    public function editLogo($id, $logo)
    {
        $this->db->set('url_logo', $logo);
        $this->db->where('id', $id);

        return $this->db->update('emitente');
    }

    public function editImageUser($id, $imageUserPath)
    {
        $this->db->set('url_image_user', $imageUserPath);
        $this->db->where('idUsuarios', $id);

        return $this->db->update('usuarios');
    }

    /** Diz se o grupo de permissão existe e está ativo (login, #2846). */
    public function grupoAtivo(int $idPermissao): bool
    {
        return $this->db->where('idPermissao', $idPermissao)->where('situacao', 1)->count_all_results('permissoes') > 0;
    }

    /** Grava o modo de cor do usuário (#2854). Falso se a migration ainda não rodou. */
    public function salvarTemaModo(int $idUsuario, string $modo): bool
    {
        if (! $this->db->field_exists('tema_modo', 'usuarios')) {
            return false;
        }

        $this->db->where('idUsuarios', $idUsuario)->update('usuarios', ['tema_modo' => $modo]);

        return $this->db->affected_rows() >= 0;
    }

    /** O grupo de permissão exige o 2FA ativo (#2876)? Falso se a migration ainda não rodou. */
    public function grupoExige2fa(int $idPermissao): bool
    {
        if (! $this->db->field_exists('exigir_2fa', 'permissoes')) {
            return false;
        }

        return $this->db->where('idPermissao', $idPermissao)->where('exigir_2fa', 1)->count_all_results('permissoes') > 0;
    }

    /** Usuário (com os campos do 2FA) pelo id, só se estiver ativo. */
    public function usuarioParaLoginPorId(int $id)
    {
        return $this->db->where('idUsuarios', $id)->where('situacao', 1)->limit(1)->get('usuarios')->row();
    }

    /**
     * Guarda o segredo cifrado e os códigos de recuperação (só o hash) e liga o
     * 2FA do usuário. Troca os códigos antigos.
     *
     * @param  list<string>  $hashesDosCodigos
     */
    public function ativarTotp(int $idUsuario, string $segredoCifrado, array $hashesDosCodigos, int $passo): bool
    {
        $this->db->trans_start();
        $this->db->where('idUsuarios', $idUsuario)->update('usuarios', ['totp_secreto' => $segredoCifrado, 'totp_ativado_em' => date('Y-m-d H:i:s'), 'totp_ultimo_passo' => $passo]);
        $this->substituirCodigosDeRecuperacao($idUsuario, $hashesDosCodigos);
        $this->db->trans_complete();

        return $this->db->trans_status() !== false;
    }

    /** @param list<string> $hashes */
    public function substituirCodigosDeRecuperacao(int $idUsuario, array $hashes): void
    {
        $this->db->where('usuario_id', $idUsuario)->delete('usuarios_codigos_recuperacao');
        $agora = date('Y-m-d H:i:s');
        foreach ($hashes as $hash) {
            $this->db->insert('usuarios_codigos_recuperacao', ['usuario_id' => $idUsuario, 'codigo_hash' => $hash, 'criado_em' => $agora]);
        }
    }

    public function desativarTotp(int $idUsuario): void
    {
        $this->db->trans_start();
        $this->db->where('idUsuarios', $idUsuario)->update('usuarios', ['totp_secreto' => null, 'totp_ativado_em' => null, 'totp_ultimo_passo' => null]);
        $this->db->where('usuario_id', $idUsuario)->delete('usuarios_codigos_recuperacao');
        $this->db->trans_complete();
    }

    /** Grava a janela aceita só se for maior que a última: o mesmo código não vale duas vezes, nem em duas requisições juntas. */
    public function registrarPassoTotp(int $idUsuario, int $passo): bool
    {
        $this->db->where('idUsuarios', $idUsuario)->group_start()->where('totp_ultimo_passo IS NULL', null, false)->or_where('totp_ultimo_passo <', $passo)->group_end()->update('usuarios', ['totp_ultimo_passo' => $passo]);

        return $this->db->affected_rows() === 1;
    }

    /** Consome um código de recuperação (uso único). */
    public function usarCodigoDeRecuperacao(int $idUsuario, string $hash): bool
    {
        $this->db->where(['usuario_id' => $idUsuario, 'codigo_hash' => $hash])->where('usado_em IS NULL', null, false)->update('usuarios_codigos_recuperacao', ['usado_em' => date('Y-m-d H:i:s')]);

        return $this->db->affected_rows() === 1;
    }

    public function codigosDeRecuperacaoRestantes(int $idUsuario): int
    {
        return $this->db->where('usuario_id', $idUsuario)->where('usado_em IS NULL', null, false)->count_all_results('usuarios_codigos_recuperacao');
    }

    public function check_credentials($email)
    {
        $this->db->where('email', $email);
        $this->db->where('situacao', 1);
        $this->db->limit(1);

        return $this->db->get('usuarios')->row();
    }

    /**
     * Salvar configurações do sistema
     *
     * @param  array  $data
     * @return bool
     */
    public function saveConfiguracao($data)
    {
        try {
            foreach ($data as $key => $valor) {
                $this->db->set('valor', $valor);
                $this->db->where('config', $key);
                $this->db->update('configuracoes');
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
