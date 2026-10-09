<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Financeiro_model extends CI_Model
{
    /**
     * Valor líquido de um lançamento no SQL (o que entra ou sai de fato): o
     * valor_desconto quando preenchido; senão, o valor menos o desconto. Mesma
     * regra de financeiroLiquido(), que serve às linhas já carregadas.
     */
    public const LIQUIDO = 'CASE WHEN lancamentos.valor_desconto > 0 THEN lancamentos.valor_desconto ELSE lancamentos.valor - COALESCE(lancamentos.desconto, 0) END';

    /** Model de OS, para registrar no histórico a OS que volta a Finalizada (injetável nos testes). */
    public ?object $osModel = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Listagem de lançamentos da v5 (#2844), no padrão das listagens (#2852):
     * os mesmos filtros em listar() e contar(), para a paginação contar só o
     * que a busca encontra.
     *
     * Cada linha traz o líquido (liquido) e quem alterou por último
     * (modificado_por). Ordem: vencimento mais antigo primeiro.
     *
     * @param  array<string, string>  $filtros  pesquisa, tipo, status, de, ate (já validados)
     * @param  string                 $hoje     AAAA-MM-DD, para o status "vencido"
     */
    public function listar(array $filtros, int $limite, int $offset, string $hoje): array
    {
        $this->db->select('lancamentos.*, usuarios.nome AS modificado_por');
        $this->db->select(self::LIQUIDO . ' AS liquido', false);
        $this->aplicarFiltros($filtros, $hoje);

        return $this->db
            ->join('usuarios', 'usuarios.idUsuarios = lancamentos.usuarios_id', 'left')
            ->order_by('lancamentos.data_vencimento', 'asc')
            ->order_by('lancamentos.idLancamentos', 'asc')
            ->limit($limite, max(0, $offset))
            ->get()
            ->result();
    }

    /** Total da listagem com os mesmos filtros de listar(). */
    public function contar(array $filtros, string $hoje): int
    {
        $this->aplicarFiltros($filtros, $hoje);

        return (int) $this->db->count_all_results();
    }

    /**
     * pesquisa procura no cliente/fornecedor, na descrição e nas observações
     * e, se for número, no Nº do lançamento (o OR fica entre parênteses para
     * não anular os outros filtros); tipo é receita ou despesa; status é
     * pendente, pago ou vencido (pendente com o vencimento antes de hoje); de
     * e ate limitam a data de vencimento.
     */
    private function aplicarFiltros(array $filtros, string $hoje): void
    {
        $this->db->from('lancamentos');

        $pesquisa = $filtros['pesquisa'] ?? '';
        if ($pesquisa !== '') {
            $this->db->group_start()
                ->like('lancamentos.cliente_fornecedor', $pesquisa)
                ->or_like('lancamentos.descricao', $pesquisa)
                ->or_like('lancamentos.observacoes', $pesquisa);
            if (ctype_digit($pesquisa)) {
                $this->db->or_where('lancamentos.idLancamentos', (int) $pesquisa);
            }
            $this->db->group_end();
        }

        if (in_array($filtros['tipo'] ?? '', array_keys(FINANCEIRO_TIPOS), true)) {
            $this->db->where('lancamentos.tipo', $filtros['tipo']);
        }

        switch ($filtros['status'] ?? '') {
            case 'pago':
                $this->db->where('lancamentos.baixado', 1);
                break;
            case 'pendente':
                $this->db->group_start()->where('lancamentos.baixado', 0)->or_where('lancamentos.baixado IS NULL', null, false)->group_end();
                break;
            case 'vencido':
                $this->db->group_start()->where('lancamentos.baixado', 0)->or_where('lancamentos.baixado IS NULL', null, false)->group_end();
                $this->db->where('lancamentos.data_vencimento <', $hoje);
                break;
        }

        if (($filtros['de'] ?? '') !== '') {
            $this->db->where('lancamentos.data_vencimento >=', $filtros['de']);
        }
        if (($filtros['ate'] ?? '') !== '') {
            $this->db->where('lancamentos.data_vencimento <=', $filtros['ate']);
        }
    }

    /**
     * Totais dos lançamentos da listagem (mesmos filtros de listar()), no
     * mesmo critério de líquido para receitas e despesas.
     *
     * @return array<string, float>  receitas, despesas (total), receitas_pagas, despesas_pagas,
     *                               receitas_pendentes, despesas_pendentes, vencidos (quantidade),
     *                               receitas_vencidas, despesas_vencidas
     */
    public function totais(array $filtros, string $hoje): array
    {
        $liquido = self::LIQUIDO;
        $pendente = '(lancamentos.baixado = 0 OR lancamentos.baixado IS NULL)';
        $vencido = $pendente . ' AND lancamentos.data_vencimento < ' . $this->db->escape($hoje);
        $soma = static fn (string $condicao): string => "COALESCE(SUM(CASE WHEN {$condicao} THEN {$liquido} END), 0)";

        $this->db->select($soma("lancamentos.tipo = 'receita'") . ' AS receitas', false);
        $this->db->select($soma("lancamentos.tipo = 'despesa'") . ' AS despesas', false);
        $this->db->select($soma("lancamentos.tipo = 'receita' AND lancamentos.baixado = 1") . ' AS receitas_pagas', false);
        $this->db->select($soma("lancamentos.tipo = 'despesa' AND lancamentos.baixado = 1") . ' AS despesas_pagas', false);
        $this->db->select($soma("lancamentos.tipo = 'receita' AND {$pendente}") . ' AS receitas_pendentes', false);
        $this->db->select($soma("lancamentos.tipo = 'despesa' AND {$pendente}") . ' AS despesas_pendentes', false);
        $this->db->select($soma("lancamentos.tipo = 'receita' AND {$vencido}") . ' AS receitas_vencidas', false);
        $this->db->select($soma("lancamentos.tipo = 'despesa' AND {$vencido}") . ' AS despesas_vencidas', false);
        $this->db->select("COALESCE(SUM(CASE WHEN {$vencido} THEN 1 END), 0) AS vencidos", false);
        $this->aplicarFiltros($filtros, $hoje);

        $linha = (array) $this->db->get()->row();

        return array_map(static fn ($valor) => (float) $valor, $linha);
    }

    /**
     * Visão geral de todos os lançamentos, sem filtro: o realizado (pago), o
     * que falta receber e pagar e os descontos dados.
     *
     * @return array<string, float>  saldo_realizado, a_receber, a_pagar, descontos
     */
    public function visaoGeral(): array
    {
        $liquido = self::LIQUIDO;
        $pendente = '(lancamentos.baixado = 0 OR lancamentos.baixado IS NULL)';

        $linha = (array) $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN lancamentos.baixado = 1 AND lancamentos.tipo = 'receita' THEN {$liquido} END), 0) AS receitas_pagas,
                COALESCE(SUM(CASE WHEN lancamentos.baixado = 1 AND lancamentos.tipo = 'despesa' THEN {$liquido} END), 0) AS despesas_pagas,
                COALESCE(SUM(CASE WHEN {$pendente} AND lancamentos.tipo = 'receita' THEN {$liquido} END), 0) AS a_receber,
                COALESCE(SUM(CASE WHEN {$pendente} AND lancamentos.tipo = 'despesa' THEN {$liquido} END), 0) AS a_pagar,
                COALESCE(SUM(lancamentos.valor - ({$liquido})), 0) AS descontos
            FROM lancamentos"
        )->row();

        return [
            'saldo_realizado' => round((float) $linha['receitas_pagas'] - (float) $linha['despesas_pagas'], 2),
            'a_receber' => (float) $linha['a_receber'],
            'a_pagar' => (float) $linha['a_pagar'],
            'descontos' => (float) $linha['descontos'],
        ];
    }

    /**
     * Lançamentos em aberto do vencimento mais antigo para o mais novo, com o
     * líquido, para a lista do painel inicial (#2847).
     */
    public function proximosPendentes(int $limite): array
    {
        return $this->db
            ->select('lancamentos.idLancamentos, lancamentos.tipo, lancamentos.descricao, lancamentos.cliente_fornecedor, lancamentos.data_vencimento')
            ->select('(' . self::LIQUIDO . ') AS liquido', false)
            ->from('lancamentos')
            ->group_start()->where('lancamentos.baixado', 0)->or_where('lancamentos.baixado IS NULL', null, false)->group_end()
            ->order_by('lancamentos.data_vencimento', 'ASC')
            ->order_by('lancamentos.idLancamentos', 'ASC')
            ->limit($limite)
            ->get()
            ->result();
    }

    /**
     * Receitas e despesas pagas de um ano, pelo mês do pagamento, com o mesmo
     * líquido da listagem (na v4 o painel descontava só das receitas e
     * calculava o desconto em % de novo). Linhas: mes (AAAA-MM), tipo, total.
     * O painel monta os 12 meses com painelBalanco().
     */
    public function balancoAnual(int $ano): array
    {
        return $this->db
            ->select('SUBSTR(lancamentos.data_pagamento, 1, 7) AS mes, lancamentos.tipo', false)
            ->select('SUM(' . self::LIQUIDO . ') AS total', false)
            ->from('lancamentos')
            ->where('lancamentos.baixado', 1)
            ->where('lancamentos.data_pagamento >=', $ano . '-01-01')
            ->where('lancamentos.data_pagamento <=', $ano . '-12-31')
            ->group_by(['mes', 'lancamentos.tipo'])
            ->get()
            ->result();
    }

    /** Lançamento com o nome de quem o alterou por último, ou null. */
    public function getLancamento(int $id): ?object
    {
        return $this->db
            ->select('lancamentos.*, usuarios.nome AS modificado_por')
            ->from('lancamentos')
            ->join('usuarios', 'usuarios.idUsuarios = lancamentos.usuarios_id', 'left')
            ->where('lancamentos.idLancamentos', $id)
            ->limit(1)
            ->get()
            ->row() ?: null;
    }

    /** Cliente do cadastro (para conferir o id escolhido na sugestão), ou null. */
    public function getCliente(int $id): ?object
    {
        return $this->db->select('idClientes, nomeCliente')->where('idClientes', $id)->get('clientes', 1)->row() ?: null;
    }

    /** Grava um lançamento e devolve o id, ou null se não gravou. */
    public function adicionar(array $dados): ?int
    {
        $this->db->insert('lancamentos', $dados);

        return $this->db->affected_rows() === 1 ? (int) $this->db->insert_id() : null;
    }

    /**
     * Grava várias linhas (entrada e parcelas) de uma vez: ou entram todas ou
     * nenhuma.
     *
     * @param  list<array<string, mixed>>  $linhas
     */
    public function adicionarVarios(array $linhas): bool
    {
        $this->db->trans_begin();

        foreach ($linhas as $linha) {
            $this->db->insert('lancamentos', $linha);
            if ($this->db->affected_rows() !== 1) {
                $this->db->trans_rollback();

                return false;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return false;
        }

        $this->db->trans_commit();

        return true;
    }

    /** Atualiza o lançamento (linha existente) e devolve se deu certo. */
    public function atualizar(int $id, array $dados): bool
    {
        $this->db->where('idLancamentos', $id)->update('lancamentos', $dados);

        return $this->db->affected_rows() >= 0;
    }

    /**
     * Exclui o lançamento. Se ele é a fatura de uma venda ou de uma OS, o
     * vínculo cai antes (a chave estrangeira impediria a exclusão) e a venda
     * ou a OS voltam a não faturadas, com status Finalizado; a mudança da OS
     * entra no histórico dela. Tudo numa transação.
     */
    public function excluir(int $id, ?int $usuario): bool
    {
        $this->db->trans_begin();

        $this->db->set('lancamentos_id', null)->set('faturado', 0)->set('status', 'Finalizado')
            ->where('lancamentos_id', $id)->update('vendas');

        $faturadas = $this->db->select('idOs, status')->where('lancamento', $id)->get('os')->result();
        foreach ($faturadas as $os) {
            $this->osModel()->registrarStatus((int) $os->idOs, $os->status, 'Finalizado', $usuario);
        }
        $this->db->set('lancamento', null)->set('faturado', 0)->set('status', 'Finalizado')
            ->where('lancamento', $id)->update('os');

        $this->db->where('idLancamentos', $id)->delete('lancamentos');
        $apagou = $this->db->affected_rows() === 1;

        if (! $apagou || $this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return false;
        }

        $this->db->trans_commit();

        return true;
    }

    private function osModel(): object
    {
        if ($this->osModel === null) {
            $this->load->model('os_model');
            $this->osModel = $this->os_model;
        }

        return $this->osModel;
    }

    // Sugestões do formulário e do filtro. Sempre devolvem a lista (vazia
    // quando nada casa); o controller responde em JSON.

    /**
     * Clientes do cadastro que casam com o termo, com o id para o vínculo.
     *
     * @return list<array{id: int, label: string, valor: string, detalhe: string}>
     */
    public function sugerirClientes(string $termo): array
    {
        $this->db->select('idClientes, nomeCliente, documento, celular');
        $this->db->group_start()->like('nomeCliente', $termo)->or_like('documento', $termo)->group_end();
        $this->db->order_by('nomeCliente', 'asc')->limit(8);

        $itens = [];
        foreach ($this->db->get('clientes')->result_array() as $linha) {
            $detalhe = array_filter([$linha['documento'], $linha['celular']], static fn ($v) => (string) $v !== '');
            $itens[] = [
                'id' => (int) $linha['idClientes'],
                'label' => $linha['nomeCliente'],
                'valor' => $linha['nomeCliente'],
                'detalhe' => implode(' · ', $detalhe),
            ];
        }

        return $itens;
    }

    /**
     * Nomes já usados em lançamentos (fornecedores e clientes avulsos), sem
     * id: servem só para completar o texto.
     *
     * @return list<array{id: null, label: string, valor: string, detalhe: string}>
     */
    public function sugerirNomesUsados(string $termo): array
    {
        $linhas = $this->db->distinct()->select('cliente_fornecedor')
            ->like('cliente_fornecedor', $termo)
            ->where('cliente_fornecedor IS NOT NULL', null, false)
            ->order_by('cliente_fornecedor', 'asc')
            ->limit(8)
            ->get('lancamentos')
            ->result_array();

        return array_map(static fn ($linha) => [
            'id' => null,
            'label' => $linha['cliente_fornecedor'],
            'valor' => $linha['cliente_fornecedor'],
            'detalhe' => 'Já usado em lançamentos',
        ], $linhas);
    }
}
