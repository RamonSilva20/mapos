<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Financeiro extends MY_Controller
{
    /** Filtros da listagem, na query string (listagemFiltros()). */
    public const FILTROS = [
        'pesquisa' => 'texto',
        'tipo' => ['receita', 'despesa'],
        'status' => ['pendente', 'pago', 'vencido'],
        'periodo' => ['dia', 'semana', 'mes_anterior', 'mes', 'mes_posterior', 'ano', 'personalizado'],
        'de' => 'texto',
        'ate' => 'texto',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'financeiro']);
        $this->load->model('financeiro_model');
        $this->data['menuLancamentos'] = 'financeiro';
    }

    public function index()
    {
        $this->lancamentos();
    }

    /**
     * Listagem de lançamentos da v5 (#2844), no padrão das listagens (#2852):
     * filtros na URL (período, vencimento, tipo, situação e busca), cards de
     * resumo do que o filtro encontra, paginação que mantém os filtros e
     * exclusão confirmada em modal-confirm.
     */
    public function lancamentos()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar lançamentos.');
            redirect(base_url());
        }

        $hoje = date('Y-m-d');
        $filtros = $this->filtrosDaListagem();
        $offset = (int) $this->uri->segment(3);
        $total = $this->financeiro_model->contar($filtros, $hoje);

        $this->data['hoje'] = $hoje;
        $this->data['filtros'] = $filtros;
        $this->data['total'] = $total;
        $this->data['results'] = $this->financeiro_model->listar($filtros, (int) $this->data['configuration']['per_page'], $offset, $hoje);
        $this->data['totais'] = $this->financeiro_model->totais($filtros, $hoje);
        $this->data['visao_geral'] = $this->financeiro_model->visaoGeral();
        $this->data['paginacao'] = $this->paginacao(site_url('financeiro/lancamentos'), $total, $offset, null, $filtros);
        $this->data['pode'] = [
            'adicionar' => $this->permite('aLancamento'),
            'editar' => $this->permite('eLancamento'),
            'excluir' => $this->permite('dLancamento'),
            'ver_cliente' => $this->permite('vCliente'),
        ];

        if ($this->data['pode']['adicionar']) {
            $this->data['topbar_acao'] = ['label' => 'Novo lançamento', 'icon' => 'plus', 'href' => site_url('financeiro/adicionar') . listagemQuery($filtros)];
        }

        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'financeiro/lancamentos';

        return $this->layout();
    }

    /**
     * Filtros válidos da listagem. O período predefinido escolhido (dia,
     * semana, mês...) vale mais que as datas da URL, que então são calculadas
     * dele; "personalizado" usa as datas de/ate (AAAA-MM-DD, de input
     * type=date), e data inválida ou ausente volta ao mês atual. Sem nada na
     * URL, a listagem mostra o mês atual (a v4 mostrava só o dia).
     *
     * @param  array<string, string>|null  $entrada  Filtros a normalizar; sem eles, a query string
     * @return array<string, string>
     */
    private function filtrosDaListagem(?array $entrada = null): array
    {
        $filtros = listagemFiltros(self::FILTROS, $entrada ?? $this->input->get());
        $hoje = new DateTimeImmutable('today');

        $de = dataIsoParaYmd($filtros['de'] ?? null);
        $ate = dataIsoParaYmd($filtros['ate'] ?? null);
        $periodo = $filtros['periodo'] ?? ($de !== null && $ate !== null ? 'personalizado' : 'mes');

        $intervalo = $periodo === 'personalizado' ? null : financeiroPeriodo($periodo, $hoje);
        if ($intervalo === null && $de !== null && $ate !== null) {
            $intervalo = $de <= $ate ? [$de, $ate] : [$ate, $de];
            $periodo = 'personalizado';
        } elseif ($intervalo === null) {
            $intervalo = financeiroPeriodo('mes', $hoje);
            $periodo = 'mes';
        }

        // Período e datas primeiro, nessa ordem, como ficam na URL.
        return ['periodo' => $periodo, 'de' => $intervalo[0], 'ate' => $intervalo[1]]
            + array_diff_key($filtros, ['periodo' => true, 'de' => true, 'ate' => true]);
    }

    /**
     * Filtros de volta da listagem, vindos da URL do formulário. Valem só os
     * da lista de FILTROS (nada de URL digitada), e voltam como query string.
     *
     * @return array<string, string>
     */
    private function filtrosDeRetorno(): array
    {
        $filtros = listagemFiltros(self::FILTROS, $this->input->get());
        foreach (['de', 'ate'] as $data) {
            if (isset($filtros[$data]) && dataIsoParaYmd($filtros[$data]) === null) {
                unset($filtros[$data]);
            }
        }

        return $filtros;
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar lançamentos.');
            redirect(base_url());
        }

        return $this->formulario(null);
    }

    public function editar()
    {
        $lancamento = is_numeric($this->uri->segment(3)) ? $this->financeiro_model->getLancamento((int) $this->uri->segment(3)) : null;
        if (! $lancamento) {
            $this->session->set_flashdata('error', 'Lançamento não encontrado ou parâmetro inválido.');
            redirect('financeiro/lancamentos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar lançamentos.');
            redirect(base_url());
        }

        return $this->formulario($lancamento);
    }

    /**
     * Formulário de lançamento da v5 (#2844), para cadastrar e editar, no
     * padrão dos formulários (#2851): POST comum, validação no servidor com os
     * erros em cada campo e redirecionamento com toast. O id editado vem da
     * URL, nunca do POST.
     *
     * Valor líquido, parcelas e datas são calculados aqui (na v4 o navegador
     * mandava o total com desconto). Ao cadastrar, um lançamento parcelado
     * vira uma linha por parcela (mais a entrada, se houver).
     */
    private function formulario(?object $lancamento)
    {
        $editando = $lancamento !== null;
        $erros = [];
        $retorno = $this->filtrosDeRetorno();

        if ($this->input->method() === 'post') {
            $post = $this->input->post();
            $idCliente = is_scalar($post['clientes_id'] ?? null) && ctype_digit((string) $post['clientes_id']) ? (int) $post['clientes_id'] : 0;

            [$dados, $erros] = financeiroLancamentoDoFormulario(
                $post,
                [
                    'hoje' => date('Y-m-d'),
                    'controle_baixa' => ($this->data['configuration']['control_baixa'] ?? '0') == '1',
                    'pagamento_atual' => $editando ? substr((string) $lancamento->data_pagamento, 0, 10) : null,
                ],
                $idCliente > 0 ? $this->financeiro_model->getCliente($idCliente) : null
            );

            $parcelamento = ['parcelas' => 1, 'entrada' => 0, 'data_entrada' => null];
            if (! $editando) {
                [$parcelamento, $errosParcelas] = financeiroParcelamentoDoFormulario($post, $dados);
                $erros += $errosParcelas;
            }

            if ($erros === []) {
                $dados['usuarios_id'] = $this->usuarioLogado();

                if ($editando) {
                    $salvou = $this->financeiro_model->atualizar((int) $lancamento->idLancamentos, $dados);
                    $mensagem = 'Lançamento #' . (int) $lancamento->idLancamentos . ' salvo.';
                } elseif ($parcelamento['parcelas'] > 1) {
                    $linhas = financeiroLinhasDoParcelamento($dados, $parcelamento, $dados['usuarios_id']);
                    $salvou = $this->financeiro_model->adicionarVarios($linhas);
                    $mensagem = ucfirst(FINANCEIRO_TIPOS[$dados['tipo']]) . ' parcelada em ' . $parcelamento['parcelas'] . 'x: ' . count($linhas) . ' lançamentos criados.';
                } else {
                    $salvou = $this->financeiro_model->adicionar($dados) !== null;
                    $mensagem = ucfirst(FINANCEIRO_TIPOS[$dados['tipo']]) . ' lançada com vencimento em ' . dataBr($dados['data_vencimento']) . '.';
                }

                if ($salvou) {
                    log_info(($editando ? 'Alterou um lançamento no financeiro. ID ' . (int) $lancamento->idLancamentos : 'Adicionou um lançamento em Financeiro'));
                    $this->session->set_flashdata('success', $mensagem);

                    return redirect($this->urlDaListagemApos($retorno, (string) $dados['data_vencimento'], (string) $dados['tipo']));
                }

                $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
            }
        }

        $tipoPadrao = ($retorno['tipo'] ?? 'receita');
        $this->data['lancamento'] = $lancamento;
        $this->data['valores'] = financeiroValoresDoFormulario(
            $this->input->method() === 'post' ? $this->input->post() : null,
            $lancamento,
            ['tipo' => $tipoPadrao, 'data_vencimento' => date('Y-m-d'), 'data_entrada' => date('Y-m-d'), 'data_pagamento' => date('Y-m-d')]
        );
        $this->data['erros'] = $erros;
        $this->data['retorno'] = $retorno;
        $this->data['controle_baixa'] = ($this->data['configuration']['control_baixa'] ?? '0') == '1';
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'financeiro/formulario';

        return $this->layout();
    }

    /**
     * Listagem para onde o formulário volta, com os filtros de onde veio. O
     * filtro de tipo sai quando o lançamento salvo é do outro tipo, e o
     * período passa a ser o mês do vencimento quando ele fica fora do período
     * da listagem (senão a pessoa não veria o que acabou de lançar).
     *
     * @param  array<string, string>  $retorno
     */
    private function urlDaListagemApos(array $retorno, string $vencimento, string $tipo): string
    {
        if (isset($retorno['tipo']) && $retorno['tipo'] !== $tipo) {
            unset($retorno['tipo']);
        }

        $periodo = $this->filtrosDaListagem($retorno);
        if ($vencimento < $periodo['de'] || $vencimento > $periodo['ate']) {
            $mes = financeiroPeriodo('mes', new DateTimeImmutable($vencimento));
            [$retorno['de'], $retorno['ate']] = $mes;
            $retorno['periodo'] = 'personalizado';
        }

        return site_url('financeiro/lancamentos') . listagemQuery($retorno);
    }

    private function usuarioLogado(): ?int
    {
        $id = (int) $this->session->userdata('id_admin');

        return $id > 0 ? $id : null;
    }

    /**
     * Exclui o lançamento (id no POST). Se ele era a fatura de uma venda ou de
     * uma OS, a venda ou a OS volta a não faturada (Financeiro_model::excluir()).
     */
    public function excluirLancamento()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir lançamentos.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        // Volta para a listagem com os mesmos filtros (só os permitidos).
        $listagem = site_url('financeiro/lancamentos') . listagemQuery($this->filtrosDeRetorno());

        if (! is_scalar($id) || ! ctype_digit((string) $id) || ! $this->financeiro_model->getLancamento((int) $id)) {
            $this->session->set_flashdata('error', 'Lançamento não encontrado.');
            redirect($listagem);
        }

        if (! $this->financeiro_model->excluir((int) $id, $this->usuarioLogado())) {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar excluir o lançamento.');
            redirect($listagem);
        }

        log_info('Excluiu um lançamento. ID: ' . (int) $id);
        $this->session->set_flashdata('success', 'Lançamento #' . (int) $id . ' excluído.');
        redirect($listagem);
    }

    /**
     * Sugestões do campo Cliente / Fornecedor: clientes do cadastro (com id,
     * que vincula o lançamento ao cliente) e nomes já usados em lançamentos.
     * Sempre uma lista JSON, vazia sem permissão ou sem termo.
     */
    public function autoCompleteClienteFornecedor()
    {
        $termo = $this->input->get('term');
        $itens = [];

        if ($this->hasAnyPermission(['vLancamento', 'aLancamento', 'eLancamento']) && is_string($termo) && trim($termo) !== '') {
            $termo = trim($termo);
            $clientes = $this->financeiro_model->sugerirClientes($termo);
            $nomes = array_filter(
                $this->financeiro_model->sugerirNomesUsados($termo),
                static fn ($nome) => ! in_array(mb_strtolower($nome['valor']), array_map(static fn ($c) => mb_strtolower($c['valor']), $clientes), true)
            );
            $itens = array_slice(array_merge($clientes, array_values($nomes)), 0, 10);
        }

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($itens));
    }
}
