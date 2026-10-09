<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vendas extends MY_Controller
{
    /** Filtros da listagem, na query string (listagemFiltros()). */
    public const FILTROS = [
        'pesquisa' => 'texto',
        'status' => ['Orçamento', 'Negociação', 'Aberto', 'Aprovado', 'Em Andamento', 'Aguardando Peças', 'Finalizado', 'Faturado', 'Cancelado'],
        'de' => 'texto',
        'ate' => 'texto',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->helper(['form', 'os', 'vendas']);
        $this->load->model('vendas_model');
        $this->data['menuVendas'] = 'Vendas';
    }

    public function index()
    {
        $this->gerenciar();
    }

    /**
     * Listagem de vendas migrada para os componentes da v5 (#2843), no padrão
     * das listagens (#2852): filtros na URL, paginação que os mantém e
     * exclusão confirmada em modal-confirm.
     */
    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $filtros = $this->filtrosDaListagem();
        $offset = (int) $this->uri->segment(3);
        $total = $this->vendas_model->contar($filtros);

        $this->data['filtros'] = $filtros;
        $this->data['total'] = $total;
        $this->data['results'] = $this->vendas_model->listar($filtros, (int) $this->data['configuration']['per_page'], $offset);
        $this->data['paginacao'] = $this->paginacao(site_url('vendas/gerenciar'), $total, $offset, null, $filtros);
        $this->data['pode'] = [
            'adicionar' => $this->permite('aVenda'),
            'editar' => $this->permite('eVenda'),
            'excluir' => $this->permite('dVenda'),
        ];
        $this->data['controle_edicao'] = ($this->data['configuration']['control_edit_vendas'] ?? '0') == '1';

        if ($this->data['pode']['adicionar']) {
            $this->data['topbar_acao'] = ['label' => 'Nova venda', 'icon' => 'plus', 'href' => site_url('vendas/adicionar')];
        }

        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'vendas/vendas';

        return $this->layout();
    }

    /**
     * Filtros válidos da listagem: pesquisa (texto), status (um dos status) e
     * as datas de/ate em AAAA-MM-DD (input type=date); data inválida é
     * descartada.
     *
     * @return array<string, string>
     */
    private function filtrosDaListagem(): array
    {
        $filtros = listagemFiltros(self::FILTROS, $this->input->get());

        foreach (['de', 'ate'] as $data) {
            if (isset($filtros[$data])) {
                $valida = dataIsoParaYmd($filtros[$data]);
                if ($valida === null) {
                    unset($filtros[$data]);
                } else {
                    $filtros[$data] = $valida;
                }
            }
        }

        return $filtros;
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar Vendas.');
            redirect(base_url());
        }

        return $this->formulario(null);
    }

    public function editar()
    {
        $venda = is_numeric($this->uri->segment(3)) ? $this->vendas_model->getById((int) $this->uri->segment(3)) : null;
        if (! $venda) {
            $this->session->set_flashdata('error', 'Venda não encontrada ou parâmetro inválido.');
            redirect('vendas/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar vendas');
            redirect(base_url());
        }

        // Pelo id da URL: na v4 a checagem usava o POST, e o GET de uma venda
        // faturada abria a tela de edição.
        if (! $this->vendas_model->isEditable((int) $venda->idVendas)) {
            $this->session->set_flashdata('error', 'Esta venda está faturada ou cancelada e não pode mais ser alterada. Se precisar, abra uma nova venda.');
            redirect(site_url('vendas'));
        }

        return $this->formulario($venda);
    }

    /**
     * Formulário de venda, para cadastrar e editar os dados (#2843), no padrão
     * dos formulários da v5 (#2851): POST comum, validação no servidor com os
     * erros em cada campo e redirecionamento com toast.
     *
     * Produtos, desconto e faturamento ficam na tela da venda
     * (vendas/visualizar).
     */
    private function formulario(?object $venda)
    {
        $erros = [];

        if ($this->input->method() === 'post') {
            $erros = $this->validarFormulario('vendas');
            // As regras do grupo "vendas" checam os ids ocultos; o erro aparece
            // no campo visível do autocomplete.
            foreach (['clientes_id' => 'cliente', 'usuarios_id' => 'vendedor'] as $oculto => $visivel) {
                if (isset($erros[$oculto])) {
                    $erros[$visivel] = $erros[$oculto];
                    unset($erros[$oculto]);
                }
            }

            $dados = [];
            if ($erros === []) {
                [$dados, $erros] = vendaDadosDoFormulario($this->input->post());
            }

            if ($erros === []) {
                if (! $this->vendas_model->existe('clientes', 'idClientes', $dados['clientes_id'])) {
                    $erros['cliente'] = 'Escolha um cliente da lista.';
                }
                if (! $this->vendas_model->existe('usuarios', 'idUsuarios', $dados['usuarios_id'], true)) {
                    $erros['vendedor'] = 'Escolha um vendedor ativo da lista.';
                }
            }

            if ($erros === []) {
                $idVenda = $venda === null ? $this->criarVenda($dados) : $this->salvarVenda($venda, $dados);

                if ($idVenda !== null) {
                    if ($venda === null) {
                        log_info('Adicionou uma venda. ID: ' . $idVenda);
                        $this->session->set_flashdata('success', 'Venda #' . $idVenda . ' criada. Agora adicione os produtos.');

                        return redirect('vendas/visualizar/' . $idVenda . ($this->permite('eVenda') ? '#secao-adicionar-produto' : ''));
                    }

                    log_info('Alterou uma venda. ID: ' . $idVenda);
                    $this->session->set_flashdata('success', 'Alterações da venda #' . $idVenda . ' salvas.');

                    return redirect('vendas/editar/' . $idVenda);
                }

                $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
            }
        }

        $this->data['venda'] = $venda;
        $this->data['valores'] = vendaValoresDoFormulario(
            $this->input->method() === 'post' ? $this->input->post() : null,
            $venda,
            [
                'vendedor' => (string) $this->session->userdata('nome_admin'),
                'usuarios_id' => (string) $this->session->userdata('id_admin'),
                'status' => 'Orçamento',
                'dataVenda' => date('Y-m-d'),
            ]
        );
        $this->data['erros'] = $erros;
        // Campos com formatação do editor antigo (Trumbowyg), que sai ao salvar.
        $this->data['formatados'] = $venda === null ? [] : array_values(array_filter(VENDA_CAMPOS_TEXTO, static fn ($campo) => osTemFormatacao($venda->{$campo} ?? null)));
        $this->data['pode'] = [
            'cadastrar_cliente' => $this->permite('aCliente'),
            'ver_venda' => $venda !== null,
        ];
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'vendas/formulario';

        return $this->layout();
    }

    /** Cria a venda e devolve o id, ou null se não gravou. */
    private function criarVenda(array $dados): ?int
    {
        $id = $this->vendas_model->add('vendas', $dados + ['faturado' => 0], true);

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * Grava os dados da venda. Cancelar devolve os produtos ao estoque, e sair
     * de Cancelado volta a debitá-los, como na OS (na v4 a venda não mexia no
     * estoque ao ser cancelada).
     */
    private function salvarVenda(object $venda, array $dados): ?int
    {
        $idVenda = (int) $venda->idVendas;
        $antes = strtolower((string) $venda->status);
        $depois = strtolower((string) $dados['status']);

        if ($depois === 'cancelado' && $antes !== 'cancelado') {
            $this->devolucaoEstoque($idVenda);
        }
        if ($antes === 'cancelado' && $depois !== 'cancelado') {
            $this->debitarEstoque($idVenda);
        }

        return $this->vendas_model->edit('vendas', $dados, 'idVendas', $idVenda) ? $idVenda : null;
    }

    private function usuarioLogado(): ?int
    {
        $id = (int) $this->session->userdata('id_admin');

        return $id > 0 ? $id : null;
    }

    private function devolucaoEstoque(int $idVenda): void
    {
        $this->movimentarEstoque($idVenda, '+', 'Motivo: Cancelamento/Exclusão da venda');
    }

    private function debitarEstoque(int $idVenda): void
    {
        $this->movimentarEstoque($idVenda, '-', 'Motivo: Mudou o status que já estava Cancelado para outro');
    }

    private function movimentarEstoque(int $idVenda, string $operacao, string $motivo): void
    {
        if (! ($this->data['configuration']['control_estoque'] ?? false)) {
            return;
        }

        $this->load->model('produtos_model');
        foreach ($this->vendas_model->getProdutos($idVenda) as $p) {
            $this->produtos_model->updateEstoque($p->produtos_id, $p->quantidade, $operacao);
            log_info('ESTOQUE: Produto id ' . $p->produtos_id . ($operacao === '+' ? ' voltou ao estoque' : ' baixa do estoque') . '. Quantidade: ' . $p->quantidade . '. ' . $motivo . '. Venda: ' . $idVenda);
        }
    }

    /**
     * Tela da venda (#2843): dados, produtos, desconto, faturamento e PIX numa
     * página. Produtos e desconto mudam sem recarregar: os endpoints devolvem
     * os trechos da tela (views/vendas/partes/*) renderizados de novo.
     */
    public function visualizar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $venda = $this->vendas_model->getById((int) $this->uri->segment(3));
        if (! $venda) {
            $this->session->set_flashdata('error', 'Venda não encontrada ou parâmetro inválido.');
            redirect('vendas/gerenciar');
        }

        $this->data = array_merge($this->data, $this->dadosDaTela($venda));

        if ($this->data['pode']['editar']) {
            $this->data['topbar_acao'] = ['label' => 'Editar venda', 'icon' => 'pencil', 'href' => site_url('vendas/editar/' . (int) $venda->idVendas)];
        }

        $this->data['gateways'] = [];
        if ($this->data['pode']['cobrar']) {
            $this->load->config('payment_gateways');
            $this->data['gateways'] = osOpcoesDeCobranca($this->config->item('payment_gateways'));
        }

        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'vendas/visualizar';

        return $this->layout();
    }

    /**
     * Tudo o que a tela da venda e os trechos (views/vendas/partes/*) usam. Os
     * endpoints de produtos chamam de novo depois de gravar, para devolver os
     * trechos já com os dados novos.
     */
    private function dadosDaTela(object $venda): array
    {
        $idVenda = (int) $venda->idVendas;
        $this->load->model('mapos_model');
        $emitente = $this->mapos_model->getEmitente();
        $configuracao = $this->data['configuration'];

        $totais = $this->vendas_model->totais($venda);
        $editavel = $this->permite('eVenda') && $this->vendas_model->isEditable($idVenda);
        $cobrancas = $this->vendas_model->getCobrancas($idVenda);

        $pixPayload = $this->vendas_model->getPixPayload($idVenda, $configuracao['pix_key'] ?? null, $emitente);
        $telefone = osTelefoneWhatsApp($venda->celular ?: $venda->telefone);

        return [
            'venda' => $venda,
            'totais' => $totais,
            'produtos' => $this->vendas_model->getProdutos($idVenda),
            'cobranca' => $cobrancas[0] ?? null,
            'emitente' => $emitente,
            'controle_estoque' => (bool) ($configuracao['control_estoque'] ?? false),
            'pix' => $pixPayload === null ? null : [
                'payload' => $pixPayload,
                'qr' => $this->vendas_model->getQrCode($idVenda, $configuracao['pix_key'], $emitente),
                'chave' => $this->formatarChave($configuracao['pix_key']),
            ],
            'whatsapp' => $telefone === null ? null : ['telefone' => $telefone],
            'garantia_ate' => vendaGarantiaAte($venda->dataVenda, $venda->garantia),
            'pode' => [
                'editar' => $editavel,
                // Produtos de uma venda cancelada não mudam: o estoque deles já voltou.
                'itens' => $editavel && $venda->status !== 'Cancelado',
                'faturar' => $editavel && (int) $venda->faturado === 0 && $venda->status !== 'Cancelado',
                'excluir' => $this->permite('dVenda') && $this->vendas_model->isEditable($idVenda),
                'cobrar' => $this->permite('aCobranca') && ! $cobrancas && $totais['total'] > 0,
                'ver_cobranca' => $this->permite('vCobranca'),
                'ver_cliente' => $this->permite('vCliente'),
            ],
        ];
    }

    /**
     * Trechos da tela da venda renderizados de novo, para os endpoints de
     * produtos e desconto devolverem ao JavaScript (que troca o conteúdo de
     * [data-os-parte]).
     *
     * @param  list<string>  $partes  Arquivos de views/vendas/partes/
     * @return array<string, string>
     */
    protected function partesDaTela(int $idVenda, array $partes): array
    {
        $dados = $this->dadosDaTela($this->vendas_model->getById($idVenda));

        $html = [];
        foreach ($partes as $parte) {
            $html[$parte] = $this->load->view('vendas/partes/' . $parte, $dados, true);
        }

        return $html;
    }

    /** Resposta JSON dos endpoints da tela da venda. */
    private function responderJson(int $status, array $corpo): void
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($corpo));
    }

    /**
     * Venda do POST (idVendas) que pode ser alterada, ou null com a resposta de
     * erro (404 ou 403) já montada. Vale para todos os endpoints da tela: na v4
     * só o desconto e o faturamento conferiam se a venda era editável.
     *
     * Com $mexeNosItens, uma venda cancelada também é recusada: o estoque dos
     * produtos dela já voltou, e mexer nos itens o desacertaria.
     */
    private function vendaParaAlterar(bool $mexeNosItens = false): ?object
    {
        $id = (int) $this->input->post('idVendas');
        $venda = $id > 0 ? $this->vendas_model->getById($id) : null;

        if (! $venda) {
            $this->responderJson(404, ['result' => false, 'message' => 'Venda não encontrada. Recarregue a página.']);

            return null;
        }

        if (! $this->vendas_model->isEditable($id)) {
            $this->responderJson(403, ['result' => false, 'message' => 'Esta venda está faturada ou cancelada e não pode mais ser alterada.']);

            return null;
        }

        if ($mexeNosItens && $venda->status === 'Cancelado') {
            $this->responderJson(403, ['result' => false, 'message' => 'Esta venda está cancelada: reabra a venda (status) para mexer nos produtos.']);

            return null;
        }

        return $venda;
    }

    public function imprimir()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['qrCode'] = $this->vendas_model->getQrCode(
            $this->uri->segment(3),
            $this->data['configuration']['pix_key'],
            $this->data['emitente']
        );
        $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);

        $this->load->view('vendas/imprimirVenda', $this->data);
    }

    public function imprimirTermica()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['qrCode'] = $this->vendas_model->getQrCode(
            $this->uri->segment(3),
            $this->data['configuration']['pix_key'],
            $this->data['emitente']
        );
        
        $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);

        $this->load->view('vendas/imprimirVendaTermica', $this->data);
    }

    public function imprimirVendaOrcamento()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['qrCode'] = $this->vendas_model->getQrCode(
            $this->uri->segment(3),
            $this->data['configuration']['pix_key'],
            $this->data['emitente']
        );
        
        $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);
        $this->load->view('vendas/imprimirVendaOrcamento', $this->data);
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir vendas');
            redirect(base_url());
        }

        $id = (int) $this->input->post('id');
        // Volta para a listagem com os mesmos filtros (só os permitidos).
        $listagem = site_url('vendas/gerenciar') . listagemQuery($this->filtrosDaListagem());

        if (! $this->vendas_model->isEditable($id)) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir. Venda já faturada ou cancelada.');
            redirect($listagem);
        }

        $venda = $this->vendas_model->getByIdCobrancas($id);
        if ($venda == null) {
            $venda = $this->vendas_model->getById($id);
            if ($venda == null) {
                $this->session->set_flashdata('error', 'Erro ao tentar excluir venda.');
                redirect($listagem);
            }
        }

        if (isset($venda->idCobranca) != null) {
            if ($venda->status == 'canceled') {
                $this->vendas_model->delete('cobrancas', 'vendas_id', $id);
            } else {
                $this->session->set_flashdata('error', 'Existe uma cobrança associada a esta venda, deve cancelar e/ou excluir a cobrança primeiro!');
                redirect($listagem);
            }
        }

        // getByIdCobrancas() devolve o status da cobrança no lugar do da venda.
        $vendaAtual = $this->vendas_model->getById($id);
        // Os produtos voltam ao estoque, a não ser que o cancelamento já os tenha devolvido.
        if (strtolower((string) $vendaAtual->status) !== 'cancelado') {
            $this->devolucaoEstoque($id);
        }

        $this->vendas_model->delete('itens_de_vendas', 'vendas_id', $id);
        $this->vendas_model->delete('vendas', 'idVendas', $id);
        if ((int) $vendaAtual->faturado === 1) {
            $this->vendas_model->excluirFatura($id, isset($vendaAtual->lancamentos_id) ? (int) $vendaAtual->lancamentos_id : null);
        }

        log_info('Removeu uma venda. ID: ' . $id);

        $this->session->set_flashdata('success', 'Venda excluída com sucesso!');
        redirect($listagem);
    }

    /*
     * Endpoints da tela da venda (#2843). Todos recebem o idVendas no POST,
     * conferem se a venda existe e pode ser alterada (vendaParaAlterar()) e
     * respondem JSON:
     *
     *     {result, message, erros?: {campo: mensagem}, html?: {parte: html}, totais?}
     *
     * 200 com os trechos da tela renderizados de novo; 403 sem permissão ou
     * com a venda faturada/cancelada; 404 venda ou item inexistente; 422 com
     * os erros por campo; 500 quando o banco não gravou.
     */

    /**
     * Produto novo na venda. O id do cadastro vem do autocomplete e é
     * conferido no banco; com o controle de estoque ligado, o produto sai do
     * estoque. Mudar os itens tira o desconto (o total mudou), como na v4.
     */
    public function adicionarProduto()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar vendas.']);

            return;
        }

        $venda = $this->vendaParaAlterar(true);
        if (! $venda) {
            return;
        }

        $idCadastro = (int) $this->input->post('idProduto');
        $cadastro = $idCadastro > 0 ? $this->vendas_model->getProdutoCadastro($idCadastro) : null;
        $controleEstoque = (bool) ($this->data['configuration']['control_estoque'] ?? false);

        [$dados, $erros] = osItemDoFormulario($this->input->post(), 'produto', $cadastro, $controleEstoque);
        if ($erros !== []) {
            $this->responderJson(422, ['result' => false, 'message' => 'Confira os campos destacados.', 'erros' => $erros]);

            return;
        }

        $idVenda = (int) $venda->idVendas;
        if (! $this->vendas_model->add('itens_de_vendas', $dados + ['vendas_id' => $idVenda])) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível adicionar. Tente de novo.']);

            return;
        }

        if ($controleEstoque) {
            $this->load->model('produtos_model');
            $this->produtos_model->updateEstoque($dados['produtos_id'], $dados['quantidade'], '-');
        }

        $tinhaDesconto = (float) $venda->valor_desconto > 0;
        $this->vendas_model->zerarDesconto($idVenda);
        log_info('Adicionou produto à venda com ID: ' . $idVenda);

        $this->responderTrechos($idVenda, 'produto', $cadastro->descricao . ' adicionado à venda.' . ($tinhaDesconto ? ' O desconto foi removido porque o total mudou.' : ''));
    }

    /**
     * Tira um produto da venda. O item, a quantidade e o produto vêm do banco
     * (na v4 a quantidade a devolver ao estoque vinha do navegador).
     */
    public function excluirProduto()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar vendas.']);

            return;
        }

        $venda = $this->vendaParaAlterar(true);
        if (! $venda) {
            return;
        }

        $idVenda = (int) $venda->idVendas;
        $idItem = (int) $this->input->post('idItem');
        $item = $idItem > 0 ? $this->vendas_model->getProdutoDaVenda($idVenda, $idItem) : null;

        if (! $item) {
            $this->responderJson(404, ['result' => false, 'message' => 'Produto não encontrado nesta venda. Recarregue a página.']);

            return;
        }

        if (! $this->vendas_model->delete('itens_de_vendas', 'idItens', $idItem)) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível excluir. Tente de novo.']);

            return;
        }

        if ($this->data['configuration']['control_estoque'] ?? false) {
            $this->load->model('produtos_model');
            $this->produtos_model->updateEstoque($item->produtos_id, $item->quantidade, '+');
        }

        $tinhaDesconto = (float) $venda->valor_desconto > 0;
        $this->vendas_model->zerarDesconto($idVenda);
        log_info('Removeu produto da venda. ID da Venda: ' . $idVenda . ', ID do Produto: ' . $item->produtos_id);

        $this->responderTrechos($idVenda, 'produto', 'Produto removido da venda.' . ($tinhaDesconto ? ' O desconto foi removido porque o total mudou.' : ''));
    }

    /**
     * Resposta de sucesso com os trechos da tela que a mudança afeta.
     */
    private function responderTrechos(int $idVenda, string $tipo, string $mensagem, int $status = 200, array $extras = []): void
    {
        $partes = match ($tipo) {
            'produto' => ['produtos', 'totais', 'desconto', 'pix'],
            'desconto' => ['totais', 'desconto', 'pix'],
            default => throw new InvalidArgumentException("Tipo de trecho desconhecido: {$tipo}"),
        };

        $this->responderJson($status, [
            'result' => $status < 400,
            'message' => $mensagem,
            'html' => $this->partesDaTela($idVenda, $partes),
            'totais' => $this->vendas_model->totais($this->vendas_model->getById($idVenda)),
        ] + $extras);
    }

    /**
     * Desconto da venda em R$ ou %, calculado no servidor sobre o total dos
     * produtos (vendaCalcularDesconto()). 0 remove o desconto. Na v4 o total
     * com desconto vinha pronto do navegador.
     */
    public function adicionarDesconto()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar vendas.']);

            return;
        }

        $venda = $this->vendaParaAlterar();
        if (! $venda) {
            return;
        }

        $idVenda = (int) $venda->idVendas;
        $totais = $this->vendas_model->totais($venda);
        [$dados, $erros] = vendaCalcularDesconto($totais['bruto'], $this->input->post('tipoDesconto'), $this->input->post('desconto'));

        if ($erros !== []) {
            $this->responderJson(422, ['result' => false, 'message' => reset($erros), 'erros' => $erros]);

            return;
        }

        if (! $this->vendas_model->edit('vendas', $dados, 'idVendas', $idVenda)) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível gravar o desconto. Tente de novo.']);

            return;
        }

        log_info(($dados['valor_desconto'] > 0 ? 'Adicionou um desconto' : 'Removeu o desconto') . ' na Venda. ID: ' . $idVenda);
        $this->responderTrechos($idVenda, 'desconto', $dados['valor_desconto'] > 0 ? 'Desconto aplicado. Total da venda: ' . dinheiro($dados['valor_desconto']) . '.' : 'Desconto removido.');
    }

    /**
     * Fatura a venda: grava a receita em lancamentos (com o vínculo
     * lancamentos.vendas_id) e marca a venda como faturada, com o vínculo
     * vendas.lancamentos_id. Valor e desconto vêm dos totais calculados no
     * servidor. O desconto e o tipo gravados na venda ficam como estão: os
     * gateways de cobrança e a área do cliente os leem.
     */
    public function faturar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar vendas.']);

            return;
        }

        $venda = $this->vendaParaAlterar();
        if (! $venda) {
            return;
        }

        $idVenda = (int) $venda->idVendas;
        if ((int) $venda->faturado === 1 || $venda->status === 'Cancelado') {
            $this->responderJson(422, ['result' => false, 'message' => 'Esta venda já foi faturada ou está cancelada.', 'erros' => ['_geral' => 'Esta venda já foi faturada ou está cancelada.']]);

            return;
        }

        $totais = $this->vendas_model->totais($venda);
        [$lancamento, $erros] = vendaFaturaDoFormulario($this->input->post(), $venda, $totais, $this->usuarioLogado());

        if ($erros !== []) {
            $this->responderJson(422, ['result' => false, 'message' => $erros['_geral'] ?? 'Confira os campos destacados.', 'erros' => $erros]);

            return;
        }

        $this->db->trans_start();

        $idLancamento = $this->vendas_model->add('lancamentos', $lancamento, true);
        if (! is_numeric($idLancamento)) {
            $this->db->trans_rollback();

            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível criar o lançamento. Tente de novo.']);

            return;
        }

        $this->vendas_model->edit('vendas', [
            'faturado' => 1,
            'valorTotal' => $totais['bruto'],
            'valor_desconto' => $totais['total'],
            'lancamentos_id' => (int) $idLancamento,
            'status' => 'Faturado',
        ], 'idVendas', $idVenda);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível faturar a venda. Tente de novo.']);

            return;
        }

        log_info('Faturou a venda com ID.' . $idVenda);
        $this->session->set_flashdata('success', 'Venda #' . $idVenda . ' faturada. Lançamento de ' . dinheiro($totais['total']) . ' criado no financeiro.');

        $this->responderJson(200, [
            'result' => true,
            'message' => 'Venda faturada.',
            'redirecionar' => site_url('vendas/visualizar/' . $idVenda),
        ]);
    }

    public function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }
        $soma1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma1 += $cpf[$i] * (10 - $i);
        }
        $resto1 = $soma1 % 11;
        $dv1 = ($resto1 < 2) ? 0 : 11 - $resto1;
        if ($dv1 != $cpf[9]) {
            return false;
        }
        $soma2 = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma2 += $cpf[$i] * (11 - $i);
        }
        $resto2 = $soma2 % 11;
        $dv2 = ($resto2 < 2) ? 0 : 11 - $resto2;
    
        return $dv2 == $cpf[10];
    }
    
    public function validarCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }
        $soma1 = 0;
        for ($i = 0, $pos = 5; $i < 12; $i++, $pos--) {
            $pos = ($pos < 2) ? 9 : $pos;
            $soma1 += $cnpj[$i] * $pos;
        }
        $dv1 = ($soma1 % 11 < 2) ? 0 : 11 - ($soma1 % 11);
        if ($dv1 != $cnpj[12]) {
            return false;
        }
        $soma2 = 0;
        for ($i = 0, $pos = 6; $i < 13; $i++, $pos--) {
            $pos = ($pos < 2) ? 9 : $pos;
            $soma2 += $cnpj[$i] * $pos;
        }
        $dv2 = ($soma2 % 11 < 2) ? 0 : 11 - ($soma2 % 11);
    
        return $dv2 == $cnpj[13];
    }
    
    public function formatarChave($chave)
    {
        if ($this->validarCPF($chave)) {
            return substr($chave, 0, 3) . '.' . substr($chave, 3, 3) . '.' . substr($chave, 6, 3) . '-' . substr($chave, 9);
        } elseif ($this->validarCNPJ($chave)) {
            return substr($chave, 0, 2) . '.' . substr($chave, 2, 3) . '.' . substr($chave, 5, 3) . '/' . substr($chave, 8, 4) . '-' . substr($chave, 12);
        } elseif (strlen($chave) === 11) {
            return '(' . substr($chave, 0, 2) . ') ' . substr($chave, 2, 5) . '-' . substr($chave, 7);
        }
        return $chave;
    }
}
