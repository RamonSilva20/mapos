<?php

use Piggly\Pix\StaticPayload;

class Os_model extends CI_Model
{
    /** Cache de historicoDisponivel(). */
    private ?bool $historicoDisponivel = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields . ',clientes.nomeCliente, clientes.celular as celular_cliente');
        $this->db->from($table);
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->limit($perpage, $start);
        $this->db->order_by('idOs', 'desc');
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getOs($table, $fields, $where = [], $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $lista_clientes = [];
        if ($where) {
            if (array_key_exists('pesquisa', $where)) {
                $this->db->select('idClientes');
                $this->db->like('nomeCliente', $where['pesquisa']);
                $this->db->or_like('documento', $where['pesquisa']);
                $this->db->limit(25);
                $clientes = $this->db->get('clientes')->result();

                foreach ($clientes as $c) {
                    array_push($lista_clientes, $c->idClientes);
                }
            }
        }

        $this->db->select($fields . ',clientes.idClientes, clientes.nomeCliente, clientes.celular as celular_cliente, usuarios.nome, garantias.*');
        $this->db->from($table);
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = os.usuarios_id');
        $this->db->join('garantias', 'garantias.idGarantias = os.garantias_id', 'left');
        $this->db->join('produtos_os', 'produtos_os.os_id = os.idOs', 'left');
        $this->db->join('servicos_os', 'servicos_os.os_id = os.idOs', 'left');

        // condicionais da pesquisa

        // condicional de status
        if (array_key_exists('status', $where)) {
            $this->db->where_in('status', $where['status']);
        }

        // condicional de clientes
        if (array_key_exists('pesquisa', $where)) {
            if ($lista_clientes != null) {
                $this->db->where_in('os.clientes_id', $lista_clientes);
            }
        }

        // condicional data inicial
        if (array_key_exists('de', $where)) {
            $this->db->where('dataInicial >=', $where['de']);
        }
        // condicional data final
        if (array_key_exists('ate', $where)) {
            $this->db->where('dataFinal <=', $where['ate']);
        }

        $this->db->limit($perpage, $start);
        $this->db->order_by('os.idOs', 'desc');
        $this->db->group_by('os.idOs');

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    /**
     * Listagem de OS da v5 (#2842), no padrão das listagens (#2852): os mesmos
     * filtros em listar() e contar(), para a paginação contar só o que a busca
     * encontra.
     *
     * Cada linha traz os dados da OS, o cliente, o responsável e o total
     * (produtos + serviços, ou o valor com desconto quando houver), calculado
     * no SQL para não consultar a OS de novo por linha.
     *
     * @param  array<string, string>  $filtros          pesquisa, status, de, ate (já validados)
     * @param  list<string>|null      $statusVisiveis   Restringe os status quando não há pesquisa nem status
     */
    public function listar(array $filtros, int $limite, int $offset, ?array $statusVisiveis = null): array
    {
        // Mesma regra de valorTotalOS(): serviço sem preço usa o do cadastro, e
        // sem quantidade conta 1.
        $totalProdutos = '(SELECT COALESCE(SUM(produtos_os.subTotal), 0) FROM produtos_os WHERE produtos_os.os_id = os.idOs)';
        $totalServicos = '(SELECT COALESCE(SUM(COALESCE(NULLIF(servicos_os.preco, 0), servicos.preco, 0) * COALESCE(NULLIF(servicos_os.quantidade, 0), 1)), 0)'
            . ' FROM servicos_os LEFT JOIN servicos ON servicos.idServicos = servicos_os.servicos_id WHERE servicos_os.os_id = os.idOs)';

        $this->db->select('os.idOs, os.dataInicial, os.dataFinal, os.descricaoProduto, os.status, os.faturado, os.desconto, os.valor_desconto, os.clientes_id, os.usuarios_id');
        $this->db->select('clientes.nomeCliente, usuarios.nome AS responsavel');
        $this->db->select($totalProdutos . ' AS totalProdutos', false);
        $this->db->select($totalServicos . ' AS totalServicos', false);

        $this->aplicarFiltros($filtros, $statusVisiveis);

        $linhas = $this->db
            ->order_by('os.idOs', 'desc')
            ->limit($limite, max(0, $offset))
            ->get()
            ->result();

        foreach ($linhas as $linha) {
            $bruto = (float) $linha->totalProdutos + (float) $linha->totalServicos;
            $linha->total = (float) $linha->valor_desconto > 0 ? (float) $linha->valor_desconto : $bruto;
        }

        return $linhas;
    }

    /** Total da listagem com os mesmos filtros de listar(). */
    public function contar(array $filtros, ?array $statusVisiveis = null): int
    {
        $this->aplicarFiltros($filtros, $statusVisiveis);

        return (int) $this->db->count_all_results();
    }

    /**
     * pesquisa procura no nome e documento do cliente, na descrição do
     * equipamento e, se for número, no Nº da OS (o OR fica entre parênteses
     * para não anular os outros filtros); status é um só; de e ate limitam a
     * data inicial e a final.
     *
     * Sem pesquisa e sem status, a listagem mostra só os status marcados em
     * Configurações (os_status_list), como na v4, mas agora no SQL: antes as
     * linhas eram escondidas na view e a paginação contava as escondidas.
     */
    private function aplicarFiltros(array $filtros, ?array $statusVisiveis): void
    {
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id', 'left');
        $this->db->join('usuarios', 'usuarios.idUsuarios = os.usuarios_id', 'left');

        $pesquisa = $filtros['pesquisa'] ?? '';
        if ($pesquisa !== '') {
            $this->db->group_start()
                ->like('clientes.nomeCliente', $pesquisa)
                ->or_like('clientes.documento', $pesquisa)
                ->or_like('os.descricaoProduto', $pesquisa);
            if (ctype_digit($pesquisa)) {
                $this->db->or_where('os.idOs', (int) $pesquisa);
            }
            $this->db->group_end();
        }

        if (($filtros['status'] ?? '') !== '') {
            $this->db->where('os.status', $filtros['status']);
        } elseif ($pesquisa === '' && $statusVisiveis !== null) {
            $this->db->where_in('os.status', $statusVisiveis);
        }

        if (($filtros['de'] ?? '') !== '') {
            $this->db->where('os.dataInicial >=', $filtros['de']);
        }
        if (($filtros['ate'] ?? '') !== '') {
            $this->db->where('os.dataFinal <=', $filtros['ate']);
        }
    }

    /**
     * Apaga o lançamento da fatura de uma OS excluída: pelo vínculo os.lancamento
     * quando existir, e pelas descrições que o faturar grava (ver
     * osDescricoesDaFatura()).
     */
    public function excluirFatura(int $idOs, ?int $idLancamento): void
    {
        if ($idLancamento) {
            $this->db->where('idLancamentos', $idLancamento)->delete('lancamentos');
        }

        $this->db->where_in('descricao', osDescricoesDaFatura($idOs))->delete('lancamentos');
    }

    public function getById($id)
    {
        $this->db->select('os.*, clientes.*, clientes.celular as celular_cliente, clientes.telefone as telefone_cliente, clientes.contato as contato_cliente, garantias.refGarantia, garantias.textoGarantia, usuarios.telefone as telefone_usuario, usuarios.email as email_usuario, usuarios.nome');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = os.usuarios_id');
        $this->db->join('garantias', 'garantias.idGarantias = os.garantias_id', 'left');
        $this->db->where('os.idOs', $id);
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function getByIdCobrancas($id)
    {
        $this->db->select('os.*, clientes.*, clientes.celular as celular_cliente, garantias.refGarantia, garantias.textoGarantia, usuarios.telefone as telefone_usuario, usuarios.email as email_usuario, usuarios.nome,cobrancas.os_id,cobrancas.idCobranca,cobrancas.status');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = os.usuarios_id');
        $this->db->join('cobrancas', 'cobrancas.os_id = os.idOs');
        $this->db->join('garantias', 'garantias.idGarantias = os.garantias_id', 'left');
        $this->db->where('os.idOs', $id);
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function getProdutos($id = null)
    {
        $this->db->select('produtos_os.*, produtos.*');
        $this->db->from('produtos_os');
        $this->db->join('produtos', 'produtos.idProdutos = produtos_os.produtos_id');
        $this->db->where('os_id', $id);

        return $this->db->get()->result();
    }

    public function getServicos($id = null)
    {
        $this->db->select('servicos_os.*, servicos.nome, servicos.preco as precoVenda');
        $this->db->from('servicos_os');
        $this->db->join('servicos', 'servicos.idServicos = servicos_os.servicos_id');
        $this->db->where('os_id', $id);

        return $this->db->get()->result();
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

    // Autocompletes: devolvem a lista (vazia quando nada casa) e o controller
    // responde em JSON. Cada item mantém label e id, que as telas legadas
    // (jQuery UI) usam, e traz valor (o texto que fica no campo ao escolher) e
    // detalhe (a linha secundária da lista) para o combobox da v5
    // (assets/js/lib/autocomplete.js).

    public function autoCompleteProduto($q)
    {
        return $this->produtosParaAutocomplete($q, false);
    }

    public function autoCompleteProdutoSaida($q)
    {
        return $this->produtosParaAutocomplete($q, true);
    }

    private function produtosParaAutocomplete($q, bool $saida): array
    {
        $this->db->select('idProdutos, descricao, precoVenda, estoque');
        $this->db->limit(25);
        // O OR entre parênteses, para não anular o filtro de saída.
        $this->db->group_start()->like('codDeBarra', $q)->or_like('descricao', $q)->group_end();
        if ($saida) {
            $this->db->where('saida', 1);
        }

        $itens = [];
        foreach ($this->db->get('produtos')->result_array() as $row) {
            $itens[] = [
                'label' => $row['descricao'] . ' | Preço: R$ ' . $row['precoVenda'] . ' | Estoque: ' . $row['estoque'],
                'valor' => $row['descricao'],
                'detalhe' => 'Preço: R$ ' . $row['precoVenda'] . ' · Estoque: ' . $row['estoque'],
                'estoque' => $row['estoque'],
                'id' => $row['idProdutos'],
                'preco' => $row['precoVenda'],
            ];
        }

        return $itens;
    }

    public function autoCompleteCliente($q)
    {
        $this->db->select('idClientes, nomeCliente, telefone, celular, documento');
        $this->db->limit(25);
        $this->db->like('nomeCliente', $q);
        $this->db->or_like('telefone', $q);
        $this->db->or_like('celular', $q);
        $this->db->or_like('documento', $q);

        $itens = [];
        foreach ($this->db->get('clientes')->result_array() as $row) {
            $detalhe = array_filter([$row['documento'], $row['celular'] ?: $row['telefone']], static fn ($v) => (string) $v !== '');
            $itens[] = [
                'label' => $row['nomeCliente'] . ' | Telefone: ' . $row['telefone'] . ' | Celular: ' . $row['celular'] . ' | Documento: ' . $row['documento'],
                'valor' => $row['nomeCliente'],
                'detalhe' => implode(' · ', $detalhe),
                'id' => $row['idClientes'],
            ];
        }

        return $itens;
    }

    public function autoCompleteUsuario($q)
    {
        $this->db->select('idUsuarios, nome, telefone');
        $this->db->limit(25);
        $this->db->like('nome', $q);
        $this->db->where('situacao', 1);

        $itens = [];
        foreach ($this->db->get('usuarios')->result_array() as $row) {
            $itens[] = [
                'label' => $row['nome'] . ' | Telefone: ' . $row['telefone'],
                'valor' => $row['nome'],
                'detalhe' => (string) $row['telefone'],
                'id' => $row['idUsuarios'],
            ];
        }

        return $itens;
    }

    public function autoCompleteTermoGarantia($q)
    {
        $this->db->select('idGarantias, refGarantia');
        $this->db->limit(25);
        $this->db->like('LOWER(refGarantia)', $q);

        $itens = [];
        foreach ($this->db->get('garantias')->result_array() as $row) {
            $itens[] = ['label' => $row['refGarantia'], 'valor' => $row['refGarantia'], 'detalhe' => '', 'id' => $row['idGarantias']];
        }

        return $itens;
    }

    public function autoCompleteServico($q)
    {
        $this->db->select('idServicos, nome, preco');
        $this->db->limit(25);
        $this->db->like('nome', $q);

        $itens = [];
        foreach ($this->db->get('servicos')->result_array() as $row) {
            $itens[] = [
                'label' => $row['nome'] . ' | Preço: R$ ' . $row['preco'],
                'valor' => $row['nome'],
                'detalhe' => 'Preço: R$ ' . $row['preco'],
                'id' => $row['idServicos'],
                'preco' => $row['preco'],
            ];
        }

        return $itens;
    }

    /**
     * Diz se existe o registro com o id, para conferir os ids escolhidos nos
     * autocompletes antes de gravar a OS. Com $ativo, só usuários ativos.
     */
    public function existe(string $tabela, string $chave, int $id, bool $ativo = false): bool
    {
        $this->db->where($chave, $id);
        if ($ativo) {
            $this->db->where('situacao', 1);
        }

        return $this->db->count_all_results($tabela) > 0;
    }

    public function anexar($os, $anexo, $url, $thumb, $path)
    {
        $this->db->set('anexo', $anexo);
        $this->db->set('url', $url);
        $this->db->set('thumb', $thumb);
        $this->db->set('path', $path);
        $this->db->set('os_id', $os);

        return $this->db->insert('anexos');
    }

    public function getAnexos($os)
    {
        $this->db->where('os_id', $os);

        return $this->db->get('anexos')->result();
    }

    public function getAnotacoes($os)
    {
        $this->db->where('os_id', $os);
        $this->db->order_by('idAnotacoes', 'desc');

        return $this->db->get('anotacoes_os')->result();
    }

    public function getCobrancas($id = null)
    {
        $this->db->select('cobrancas.*');
        $this->db->from('cobrancas');
        $this->db->where('os_id', $id);

        return $this->db->get()->result();
    }

    public function valorTotalOS($id = null)
    {
        $totalServico = 0;
        $totalProdutos = 0;
        $valorDesconto = 0;
        if ($servicos = $this->getServicos($id)) {
            foreach ($servicos as $s) {
                $preco = $s->preco ?: $s->precoVenda;
                $totalServico = $totalServico + ($preco * ($s->quantidade ?: 1));
            }
        }
        if ($produtos = $this->getProdutos($id)) {
            foreach ($produtos as $p) {
                $totalProdutos = $totalProdutos + $p->subTotal;
            }
        }
        if ($valorDescontoBD = $this->getById($id)) {
            $valorDesconto = $valorDescontoBD->valor_desconto;
        }

        return ['totalServico' => $totalServico, 'totalProdutos' => $totalProdutos, 'valor_desconto' => $valorDesconto];
    }

    public function isEditable($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            return false;
        }
        if ($os = $this->getById($id)) {
            $osT = (int) ($os->status === 'Faturado' || $os->status === 'Cancelado' || $os->faturado == 1);
            if ($osT) {
                return $this->data['configuration']['control_editos'] == '1';
            }
        }

        return true;
    }

    public function getQrCode($id, $pixKey, $emitente)
    {
        return $this->pix($id, $pixKey, $emitente)?->getQRCode();
    }

    /**
     * Código PIX "copia e cola" (BR Code) da OS, o mesmo que o QR Code de
     * getQrCode() carrega. Na v4 a tela decodificava a imagem do QR no
     * navegador (jsQR, do rawgit) para obter este texto.
     */
    public function getPixPayload($id, $pixKey, $emitente): ?string
    {
        return $this->pix($id, $pixKey, $emitente)?->getPixCode();
    }

    /**
     * PIX estático com o total da OS (com o desconto, quando houver); null sem
     * chave, sem emitente ou com total zerado.
     */
    private function pix($id, $pixKey, $emitente): ?StaticPayload
    {
        if (empty($id) || empty($pixKey) || empty($emitente)) {
            return null;
        }

        $result = $this->valorTotalOS($id);
        $amount = $result['valor_desconto'] != 0 ? round(floatval($result['valor_desconto']), 2) : round(floatval($result['totalServico'] + $result['totalProdutos']), 2);

        if ($amount <= 0) {
            return null;
        }

        $pix = new StaticPayload();
        $pix
            ->setAmount($amount)
            ->setTid($id)
            ->setDescription(sprintf('%s OS %s', substr($emitente->nome, 0, 18), $id), true)
            ->setPixKey(getPixKeyType($pixKey), $pixKey)
            ->setMerchantName($emitente->nome)
            ->setMerchantCity($emitente->cidade);

        return $pix;
    }

    /**
     * Totais da OS para a tela (osTotais()): produtos, serviços, desconto e
     * total, com a mesma regra de valorTotalOS().
     */
    public function totais(object $os): array
    {
        $soma = $this->valorTotalOS((int) $os->idOs);

        return osTotais((float) $soma['totalProdutos'], (float) $soma['totalServico'], $os->valor_desconto ?? 0, $os->tipo_desconto ?? null, $os->desconto ?? 0);
    }

    /** Linha de produtos_os que pertence à OS, ou null. */
    public function getProdutoDaOs(int $idOs, int $idItem): ?object
    {
        return $this->db->where('idProdutos_os', $idItem)->where('os_id', $idOs)->get('produtos_os', 1)->row() ?: null;
    }

    /** Linha de servicos_os que pertence à OS, ou null. */
    public function getServicoDaOs(int $idOs, int $idItem): ?object
    {
        return $this->db->where('idServicos_os', $idItem)->where('os_id', $idOs)->get('servicos_os', 1)->row() ?: null;
    }

    /** Anexo que pertence à OS, ou null. */
    public function getAnexoDaOs(int $idOs, int $idAnexo): ?object
    {
        return $this->db->where('idAnexos', $idAnexo)->where('os_id', $idOs)->get('anexos', 1)->row() ?: null;
    }

    /** Anotação que pertence à OS, ou null. */
    public function getAnotacaoDaOs(int $idOs, int $idAnotacao): ?object
    {
        return $this->db->where('idAnotacoes', $idAnotacao)->where('os_id', $idOs)->get('anotacoes_os', 1)->row() ?: null;
    }

    /** Produto do cadastro (para conferir o id escolhido e o estoque), ou null. */
    public function getProdutoCadastro(int $id): ?object
    {
        return $this->db->select('idProdutos, descricao, precoVenda, estoque')->where('idProdutos', $id)->get('produtos', 1)->row() ?: null;
    }

    /** Serviço do cadastro, ou null. */
    public function getServicoCadastro(int $id): ?object
    {
        return $this->db->select('idServicos, nome, preco')->where('idServicos', $id)->get('servicos', 1)->row() ?: null;
    }

    /**
     * Tira o desconto da OS. Mudar os itens muda o total, e o desconto antigo
     * (calculado sobre o total anterior) deixaria de bater, como na v4.
     */
    public function zerarDesconto(int $idOs): void
    {
        $this->db->set('desconto', 0)->set('valor_desconto', 0)->set('tipo_desconto', null)->where('idOs', $idOs)->update('os');
    }

    /**
     * Diz se a tabela do histórico existe. Uma instalação que ainda não rodou
     * as migrations (Configurações > Atualizar banco) continua funcionando,
     * só sem histórico.
     */
    public function historicoDisponivel(): bool
    {
        return $this->historicoDisponivel ??= $this->db->table_exists('os_historico');
    }

    /**
     * Registra uma mudança de status da OS. $usuario null = a OS foi aberta
     * pelo cliente na área do cliente. Status igual ao anterior não registra.
     */
    public function registrarStatus(int $idOs, ?string $anterior, string $novo, ?int $usuario): void
    {
        if ($anterior === $novo || ! $this->historicoDisponivel()) {
            return;
        }

        $this->db->insert('os_historico', [
            'os_id' => $idOs,
            'status_anterior' => $anterior,
            'status_novo' => $novo,
            'usuarios_id' => $usuario ?: null,
            'data_hora' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Histórico de status da OS, do mais recente ao mais antigo, com o nome de quem mudou. */
    public function getHistorico(int $idOs): array
    {
        if (! $this->historicoDisponivel()) {
            return [];
        }

        return $this->db
            ->select('os_historico.*, usuarios.nome AS autor')
            ->from('os_historico')
            ->join('usuarios', 'usuarios.idUsuarios = os_historico.usuarios_id', 'left')
            ->where('os_historico.os_id', $idOs)
            ->order_by('os_historico.data_hora', 'desc')
            ->order_by('os_historico.idHistorico', 'desc')
            ->get()
            ->result();
    }
}
