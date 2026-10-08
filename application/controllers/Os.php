<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Os extends MY_Controller
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
        $this->load->helper(['form', 'os']);
        $this->load->model('os_model');
        $this->data['menuOs'] = 'OS';
    }

    public function index()
    {
        $this->gerenciar();
    }

    /**
     * Listagem de OS migrada para os componentes da v5 (#2842), no padrão das
     * listagens (#2852): filtros na URL, paginação que os mantém e exclusão
     * confirmada em modal-confirm.
     */
    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }

        $filtros = $this->filtrosDaListagem();
        $offset = (int) $this->uri->segment(3);
        $statusVisiveis = osStatusVisiveis($this->data['configuration']['os_status_list'] ?? null);
        $total = $this->os_model->contar($filtros, $statusVisiveis);

        $this->data['filtros'] = $filtros;
        $this->data['total'] = $total;
        $this->data['status_ocultos'] = ! isset($filtros['pesquisa']) && ! isset($filtros['status']) && $statusVisiveis !== null
            && array_diff(array_keys(OS_STATUS_VARIANTES), $statusVisiveis) !== [];
        $this->data['results'] = $this->os_model->listar($filtros, (int) $this->data['configuration']['per_page'], $offset, $statusVisiveis);
        $this->data['paginacao'] = $this->paginacao(site_url('os/gerenciar'), $total, $offset, null, $filtros);
        $this->data['pode'] = [
            'adicionar' => $this->permite('aOs'),
            'editar' => $this->permite('eOs'),
            'excluir' => $this->permite('dOs'),
        ];
        $this->data['controle_editos'] = ($this->data['configuration']['control_editos'] ?? '0') == '1';

        if ($this->data['pode']['adicionar']) {
            $this->data['topbar_acao'] = ['label' => 'Nova OS', 'icon' => 'plus', 'href' => site_url('os/adicionar')];
        }

        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'os/os';

        return $this->layout();
    }

    /**
     * Filtros válidos da listagem: pesquisa (texto), status (um dos status de
     * OS) e as datas de/ate em AAAA-MM-DD (input type=date); data inválida é
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
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar O.S.');
            redirect(base_url());
        }

        return $this->formulario(null);
    }

    public function editar()
    {
        $os = is_numeric($this->uri->segment(3)) ? $this->os_model->getById((int) $this->uri->segment(3)) : null;
        if (! $os) {
            $this->session->set_flashdata('error', 'OS não encontrada ou parâmetro inválido.');
            redirect('os/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar O.S.');
            redirect(base_url());
        }

        // Pelo id da URL: na v4 a checagem usava o POST, e o GET de uma OS
        // faturada ou cancelada abria a tela de edição.
        if (! $this->os_model->isEditable((int) $os->idOs)) {
            $this->session->set_flashdata('error', 'Esta OS está faturada ou cancelada e não pode mais ser alterada. Se precisar, abra uma nova OS.');
            redirect(site_url('os'));
        }

        return $this->formulario($os);
    }

    /**
     * Formulário de OS, para cadastrar e editar os dados (#2842), no padrão
     * dos formulários da v5 (#2851): POST comum, validação no servidor com os
     * erros em cada campo e redirecionamento com toast.
     *
     * Produtos, serviços, anexos, anotações, desconto e faturamento ficam na
     * tela da OS (os/visualizar).
     */
    private function formulario(?object $os)
    {
        $erros = [];
        $dados = [];

        if ($this->input->method() === 'post') {
            $erros = $this->validarFormulario('os');
            // As regras do grupo "os" checam os ids ocultos; o erro aparece no
            // campo visível do autocomplete.
            foreach (['clientes_id' => 'cliente', 'usuarios_id' => 'tecnico'] as $oculto => $visivel) {
                if (isset($erros[$oculto])) {
                    $erros[$visivel] = $erros[$oculto];
                    unset($erros[$oculto]);
                }
            }

            if ($erros === []) {
                [$dados, $erros] = osDadosDoFormulario($this->input->post());
            }

            if ($erros === []) {
                if (! $this->os_model->existe('clientes', 'idClientes', $dados['clientes_id'])) {
                    $erros['cliente'] = 'Escolha um cliente da lista.';
                }
                if (! $this->os_model->existe('usuarios', 'idUsuarios', $dados['usuarios_id'], true)) {
                    $erros['tecnico'] = 'Escolha um técnico ativo da lista.';
                }
                if ($dados['garantias_id'] !== null && ! $this->os_model->existe('garantias', 'idGarantias', $dados['garantias_id'])) {
                    $erros['termoGarantia'] = 'Escolha um termo da lista ou deixe o campo em branco.';
                }
            }

            if ($erros === []) {
                $idOs = $os === null ? $this->criarOs($dados) : $this->salvarOs($os, $dados);

                if ($idOs !== null) {
                    $this->notificarOs($idOs, $os === null ? 'Ordem de Serviço - Criada' : 'Ordem de Serviço - Editada');

                    if ($os === null) {
                        log_info('Adicionou uma OS. ID: ' . $idOs);
                        $this->session->set_flashdata('success', 'OS #' . $idOs . ' criada. Agora adicione os produtos e serviços.');

                        return redirect('os/visualizar/' . $idOs . ($this->permite('eOs') ? '?aba=produtos' : ''));
                    }

                    log_info('Alterou uma OS. ID: ' . $idOs);
                    $this->session->set_flashdata('success', 'Alterações da OS #' . $idOs . ' salvas.');

                    return redirect('os/editar/' . $idOs);
                }

                $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
            }
        }

        $this->data['os'] = $os;
        $this->data['valores'] = osValoresDoFormulario(
            $this->input->method() === 'post' ? $this->input->post() : null,
            $os,
            [
                'tecnico' => (string) $this->session->userdata('nome_admin'),
                'usuarios_id' => (string) $this->session->userdata('id_admin'),
                'status' => 'Aberto',
                'dataInicial' => date('Y-m-d'),
            ]
        );
        $this->data['erros'] = $erros;
        // Campos com formatação do editor antigo (Trumbowyg), que sai ao salvar.
        $this->data['formatados'] = $os === null ? [] : array_values(array_filter(OS_CAMPOS_TEXTO, static fn ($campo) => osTemFormatacao($os->{$campo} ?? null)));
        $this->data['pode'] = [
            'cadastrar_cliente' => $this->permite('aCliente'),
            'ver_os' => $os !== null,
        ];
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'os/formulario';

        return $this->layout();
    }

    /** Cria a OS e devolve o id, ou null se não gravou. */
    private function criarOs(array $dados): ?int
    {
        $id = $this->os_model->add('os', $dados + ['faturado' => 0], true);
        if (! is_numeric($id)) {
            return null;
        }

        $this->os_model->registrarStatus((int) $id, null, (string) $dados['status'], $this->usuarioLogado());

        return (int) $id;
    }

    private function usuarioLogado(): ?int
    {
        $id = (int) $this->session->userdata('id_admin');

        return $id > 0 ? $id : null;
    }

    /**
     * Grava os dados da OS. Cancelar devolve os produtos ao estoque, e sair de
     * Cancelado volta a debitá-los, como na v4.
     */
    private function salvarOs(object $os, array $dados): ?int
    {
        $idOs = (int) $os->idOs;
        $antes = strtolower((string) $os->status);
        $depois = strtolower((string) $dados['status']);

        if ($depois === 'cancelado' && $antes !== 'cancelado') {
            $this->devolucaoEstoque($idOs);
        }
        if ($antes === 'cancelado' && $depois !== 'cancelado') {
            $this->debitarEstoque($idOs);
        }

        if (! $this->os_model->edit('os', $dados, 'idOs', $idOs)) {
            return null;
        }

        $this->os_model->registrarStatus($idOs, (string) $os->status, (string) $dados['status'], $this->usuarioLogado());

        return $idOs;
    }

    /**
     * E-mail automático da OS, conforme as configurações os_notification e
     * email_automatico.
     */
    private function notificarOs(int $idOs, string $assunto): void
    {
        $configuracao = $this->data['configuration'];
        if (($configuracao['os_notification'] ?? 'nenhum') === 'nenhum' || ($configuracao['email_automatico'] ?? 0) != 1) {
            return;
        }

        $this->load->model('mapos_model');
        $this->load->model('usuarios_model');

        $os = $this->os_model->getById($idOs);
        $emitente = $this->mapos_model->getEmitente();
        $tecnico = $this->usuarios_model->getById($os->usuarios_id);

        $remetentes = match ($configuracao['os_notification']) {
            'todos' => [$os->email, $tecnico->email ?? null, $emitente->email ?? null],
            'tecnico' => [$tecnico->email ?? null],
            'emitente' => [$emitente->email ?? null],
            default => [$os->email],
        };

        $this->enviarOsPorEmail($idOs, array_values(array_filter($remetentes)), $assunto);
    }

    /**
     * Tela da OS (#2842): resumo, produtos, serviços, anexos, anotações e
     * histórico em abas (?aba=). Itens, anexos, anotações e desconto mudam
     * sem recarregar a página: os endpoints devolvem os trechos da tela
     * (views/os/partes/*) renderizados de novo.
     */
    public function visualizar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }

        $this->data['result'] = $this->os_model->getById((int) $this->uri->segment(3));

        // OS inexistente: mesmo tratamento do editar(), em vez de renderizar a
        // view com $result nulo (#2901).
        if (! $this->data['result']) {
            $this->session->set_flashdata('error', 'OS não encontrada ou parâmetro inválido.');
            redirect('os/gerenciar');
        }

        $aba = listagemFiltros(['aba' => OS_ABAS], $this->input->get())['aba'] ?? 'resumo';
        $this->data = array_merge($this->data, $this->dadosDaTela($this->data['result'], $aba));

        if ($this->data['pode']['editar']) {
            $this->data['topbar_acao'] = ['label' => 'Editar OS', 'icon' => 'pencil', 'href' => site_url('os/editar/' . (int) $this->data['result']->idOs)];
        }

        $this->data['gateways'] = [];
        if ($this->data['pode']['cobrar']) {
            $this->load->config('payment_gateways');
            $this->data['gateways'] = osOpcoesDeCobranca($this->config->item('payment_gateways'));
        }

        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'os/visualizar';

        return $this->layout();
    }

    /**
     * Tudo o que a tela da OS e os trechos (views/os/partes/*) usam. Os
     * endpoints de itens chamam de novo depois de gravar, para devolver os
     * trechos já com os dados novos.
     */
    private function dadosDaTela(object $os, string $aba): array
    {
        $idOs = (int) $os->idOs;
        $this->load->model('mapos_model');
        $emitente = $this->mapos_model->getEmitente();
        $configuracao = $this->data['configuration'];

        $totais = $this->os_model->totais($os);
        $editavel = $this->os_model->isEditable($idOs);
        $cobrancas = $this->os_model->getCobrancas($idOs);
        $produtos = $this->os_model->getProdutos($idOs);
        $servicos = $this->os_model->getServicos($idOs);
        $anexos = $this->os_model->getAnexos($idOs);
        $anotacoes = $this->os_model->getAnotacoes($idOs);
        $historico = $this->os_model->getHistorico($idOs);

        $pixPayload = $this->os_model->getPixPayload($idOs, $configuracao['pix_key'] ?? null, $emitente);
        $telefone = osTelefoneWhatsApp($os->celular_cliente ?: $os->telefone_cliente);

        $troca = [
            '{CLIENTE_NOME}' => (string) $os->nomeCliente,
            '{NUMERO_OS}' => (string) $idOs,
            '{STATUS_OS}' => (string) $os->status,
            '{VALOR_OS}' => dinheiro($totais['total']),
            '{DESCRI_PRODUTOS}' => (string) $os->descricaoProduto,
            '{EMITENTE}' => (string) ($emitente->nome ?? ''),
            '{TELEFONE_EMITENTE}' => (string) ($emitente->telefone ?? ''),
            '{OBS_OS}' => (string) $os->observacoes,
            '{DEFEITO_OS}' => (string) $os->defeito,
            '{LAUDO_OS}' => (string) $os->laudoTecnico,
            '{DATA_FINAL}' => dataBr($os->dataFinal),
            '{DATA_INICIAL}' => dataBr($os->dataInicial),
            '{DATA_GARANTIA}' => (string) $os->garantia . ' dias',
        ];

        return [
            'os' => $os,
            'aba' => $aba,
            'totais' => $totais,
            'produtos' => $produtos,
            'servicos' => $servicos,
            'anexos' => $anexos,
            'anotacoes' => $anotacoes,
            'historico' => $historico,
            'historico_disponivel' => $this->os_model->historicoDisponivel(),
            'cobranca' => $cobrancas[0] ?? null,
            'emitente' => $emitente,
            'controle_estoque' => (bool) ($configuracao['control_estoque'] ?? false),
            'pix' => $pixPayload === null ? null : [
                'payload' => $pixPayload,
                'qr' => $this->os_model->getQrCode($idOs, $configuracao['pix_key'], $emitente),
                'chave' => $this->formatarChave($configuracao['pix_key']),
            ],
            'whatsapp' => $telefone === null ? null : [
                'telefone' => $telefone,
                'texto' => osTextoWhatsApp($configuracao['notifica_whats'] ?? '', $troca),
            ],
            'garantia_ate' => osVencimentoGarantia($os->dataFinal, $os->garantia, $os->status),
            'pode' => [
                'editar' => $editavel,
                'faturar' => $editavel && (int) $os->faturado === 0 && $os->status !== 'Cancelado',
                'excluir' => $editavel && $this->permite('dOs'),
                'cobrar' => $this->permite('aCobranca') && ! $cobrancas && $totais['total'] > 0,
                'ver_cobranca' => $this->permite('vCobranca'),
                'ver_cliente' => $this->permite('vCliente'),
            ],
        ];
    }

    /**
     * Trechos da tela da OS renderizados de novo, para os endpoints de itens
     * devolverem ao JavaScript (que troca o conteúdo de [data-os-parte]).
     *
     * @param  list<string>  $partes  Arquivos de views/os/partes/
     * @return array<string, string>
     */
    protected function partesDaTela(int $idOs, array $partes): array
    {
        $os = $this->os_model->getById($idOs);
        $aba = listagemFiltros(['aba' => OS_ABAS], $this->input->post())['aba'] ?? 'resumo';
        $dados = $this->dadosDaTela($os, $aba);

        $html = [];
        foreach ($partes as $parte) {
            $html[$parte] = $this->load->view('os/partes/' . $parte, $dados, true);
        }

        return $html;
    }

    /** Resposta JSON dos endpoints da tela da OS. */
    private function responderJson(int $status, array $corpo): void
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($corpo));
    }

    /**
     * OS do POST (idOs) que pode ser alterada, ou null com a resposta de erro
     * (404 ou 403) já montada. Vale para todos os endpoints de itens: na v4
     * só o desconto e o faturamento conferiam se a OS era editável.
     */
    private function osParaAlterar(): ?object
    {
        $id = (int) $this->input->post('idOs');
        $os = $id > 0 ? $this->os_model->getById($id) : null;

        if (! $os) {
            $this->responderJson(404, ['result' => false, 'message' => 'OS não encontrada. Recarregue a página.']);

            return null;
        }

        if (! $this->os_model->isEditable($id)) {
            $this->responderJson(403, ['result' => false, 'message' => 'Esta OS está faturada ou cancelada e não pode mais ser alterada.']);

            return null;
        }

        return $os;
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

    public function imprimir()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $this->data['anexos'] = $this->os_model->getAnexos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        if ($this->data['configuration']['pix_key']) {
            $this->data['qrCode'] = $this->os_model->getQrCode(
                $this->uri->segment(3),
                $this->data['configuration']['pix_key'],
                $this->data['emitente']
            );
            $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);
        }
        
        $this->data['imprimirAnexo'] = isset($_ENV['IMPRIMIR_ANEXOS']) ? (filter_var($_ENV['IMPRIMIR_ANEXOS'] ?? false, FILTER_VALIDATE_BOOLEAN)) : false;

        $this->load->view('os/imprimirOs', $this->data);
    }

    public function imprimirTermica()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['qrCode'] = $this->os_model->getQrCode(
            $this->uri->segment(3),
            $this->data['configuration']['pix_key'],
            $this->data['emitente']
        );
        $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);

        $this->load->view('os/imprimirOsTermica', $this->data);
    }

    public function enviar_email()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para enviar O.S. por e-mail.');
            redirect(base_url());
        }

        $this->load->model('mapos_model');
        $this->load->model('usuarios_model');
        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
        if (! isset($this->data['result']->email)) {
            $this->session->set_flashdata('error', 'O cliente não tem e-mail cadastrado.');
            redirect(site_url('os'));
        }

        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        if (! isset($this->data['emitente']->email)) {
            $this->session->set_flashdata('error', 'Efetue o cadastro dos dados de emitente');
            redirect(site_url('os'));
        }

        $idOs = $this->uri->segment(3);

        $emitente = $this->data['emitente'];
        $tecnico = $this->usuarios_model->getById($this->data['result']->usuarios_id);

        // Verificar configuração de notificação
        $ValidarEmail = false;
        if ($this->data['configuration']['os_notification'] != 'nenhum') {
            $remetentes = [];
            switch ($this->data['configuration']['os_notification']) {
                case 'todos':
                    array_push($remetentes, $this->data['result']->email);
                    array_push($remetentes, $tecnico->email);
                    array_push($remetentes, $emitente->email);
                    $ValidarEmail = true;
                    break;
                case 'cliente':
                    array_push($remetentes, $this->data['result']->email);
                    $ValidarEmail = true;
                    break;
                case 'tecnico':
                    array_push($remetentes, $tecnico->email);
                    break;
                case 'emitente':
                    array_push($remetentes, $emitente->email);
                    break;
                default:
                    array_push($remetentes, $this->data['result']->email);
                    $ValidarEmail = true;
                    break;
            }

            if ($ValidarEmail) {
                if (empty($this->data['result']->email) || ! filter_var($this->data['result']->email, FILTER_VALIDATE_EMAIL)) {
                    $this->session->set_flashdata('error', 'Por favor preencha o email do cliente');
                    redirect(site_url('os/visualizar/') . $this->uri->segment(3));
                }
            }

            $enviouEmail = $this->enviarOsPorEmail($idOs, $remetentes, 'Ordem de Serviço');

            if ($enviouEmail) {
                $this->session->set_flashdata('success', 'O email está sendo processado e será enviado em breve.');
                log_info('Enviou e-mail para o cliente: ' . $this->data['result']->nomeCliente . '. E-mail: ' . $this->data['result']->email);
                redirect(site_url('os'));
            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao enviar e-mail.');
                redirect(site_url('os'));
            }
        }

        $this->session->set_flashdata('success', 'O sistema está com uma configuração ativada para não notificar. Entre em contato com o administrador.');
        redirect(site_url('os'));
    }

    private function devolucaoEstoque($id)
    {
        if ($produtos = $this->os_model->getProdutos($id)) {
            $this->load->model('produtos_model');
            if ($this->data['configuration']['control_estoque']) {
                foreach ($produtos as $p) {
                    $this->produtos_model->updateEstoque($p->produtos_id, $p->quantidade, '+');
                    log_info('ESTOQUE: Produto id ' . $p->produtos_id . ' voltou ao estoque. Quantidade: ' . $p->quantidade . '. Motivo: Cancelamento/Exclusão');
                }
            }
        }
    }

    private function debitarEstoque($id)
    {
        if ($produtos = $this->os_model->getProdutos($id)) {
            $this->load->model('produtos_model');
            if ($this->data['configuration']['control_estoque']) {
                foreach ($produtos as $p) {
                    $this->produtos_model->updateEstoque($p->produtos_id, $p->quantidade, '-');
                    log_info('ESTOQUE: Produto id ' . $p->produtos_id . ' baixa do estoque. Quantidade: ' . $p->quantidade . '. Motivo: Mudou status que já estava Cancelado para outro');
                }
            }
        }
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir O.S.');
            redirect(base_url());
        }

        $id = (int) $this->input->post('id');
        // Volta para a listagem com os mesmos filtros (só os permitidos).
        $listagem = site_url('os/gerenciar') . listagemQuery($this->filtrosDaListagem());

        $os = $this->os_model->getByIdCobrancas($id);
        if ($os == null) {
            $os = $this->os_model->getById($id);
            if ($os == null) {
                $this->session->set_flashdata('error', 'Erro ao tentar excluir OS.');
                redirect($listagem);
            }
        }

        if (isset($os->idCobranca) != null) {
            if ($os->status == 'canceled') {
                $this->os_model->delete('cobrancas', 'os_id', $id);
            } else {
                $this->session->set_flashdata('error', 'Existe uma cobrança associada a esta OS, deve cancelar e/ou excluir a cobrança primeiro!');
                redirect($listagem);
            }
        }

        $osStockRefund = $this->os_model->getById($id);
        //Verifica para poder fazer a devolução do produto para o estoque caso OS seja excluida.
        if (strtolower($osStockRefund->status) != 'cancelado') {
            $this->devolucaoEstoque($id);
        }

        // Os arquivos dos anexos saem do disco junto com os registros (só os que
        // ficam dentro de assets/anexos; ver osArquivosDosAnexos()).
        foreach (osArquivosDosAnexos($this->os_model->getAnexos($id), FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos') as $arquivo) {
            @unlink($arquivo);
        }

        $this->os_model->delete('servicos_os', 'os_id', $id);
        $this->os_model->delete('produtos_os', 'os_id', $id);
        $this->os_model->delete('anexos', 'os_id', $id);
        $this->os_model->delete('anotacoes_os', 'os_id', $id);
        if ($this->os_model->historicoDisponivel()) {
            $this->os_model->delete('os_historico', 'os_id', $id);
        }
        $this->os_model->delete('os', 'idOs', $id);
        if ((int) $osStockRefund->faturado === 1) {
            $this->os_model->excluirFatura($id, isset($osStockRefund->lancamento) ? (int) $osStockRefund->lancamento : null);
        }

        log_info('Removeu uma OS. ID: ' . $id);
        $this->session->set_flashdata('success', 'OS excluída com sucesso!');
        redirect($listagem);
    }

    public function autoCompleteProduto()
    {
        $this->responderAutocomplete($this->hasAnyPermission(['aOs', 'eOs']), 'autoCompleteProduto');
    }

    public function autoCompleteProdutoSaida()
    {
        $this->responderAutocomplete($this->hasAnyPermission(['aOs', 'eOs', 'aVenda', 'eVenda']), 'autoCompleteProdutoSaida');
    }

    public function autoCompleteCliente()
    {
        $this->responderAutocomplete($this->hasAnyPermission(['aOs', 'eOs', 'rOs', 'aVenda', 'eVenda', 'rVenda']), 'autoCompleteCliente');
    }

    public function autoCompleteUsuario()
    {
        $this->responderAutocomplete($this->hasAnyPermission(['aOs', 'eOs', 'rOs', 'aVenda', 'eVenda', 'rVenda']), 'autoCompleteUsuario');
    }

    public function autoCompleteTermoGarantia()
    {
        $this->responderAutocomplete($this->hasAnyPermission(['aOs', 'eOs']), 'autoCompleteTermoGarantia');
    }

    public function autoCompleteServico()
    {
        $this->responderAutocomplete($this->hasAnyPermission(['aOs', 'eOs']), 'autoCompleteServico');
    }

    /**
     * Resposta dos autocompletes: sempre um array JSON, vazio sem permissão,
     * sem termo ou sem resultado (na v4 a resposta vinha vazia, sem corpo).
     *
     * A permissão é conferida em cada método público (o PermissionsMapTest
     * confere a regra do mapa com a checagem de cada um).
     */
    private function responderAutocomplete(bool $permitido, string $metodo): void
    {
        $termo = $this->input->get('term');
        $itens = $permitido && is_string($termo) && trim($termo) !== ''
            ? $this->os_model->{$metodo}(mb_strtolower(trim($termo)))
            : [];

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($itens));
    }

    /*
     * Endpoints da tela da OS (#2842). Todos recebem o idOs no POST, conferem
     * se a OS existe e pode ser alterada (osParaAlterar()) e respondem JSON:
     *
     *     {result, message, erros?: {campo: mensagem}, html?: {parte: html}, totais?}
     *
     * 200 com os trechos da tela renderizados de novo; 403 sem permissão ou
     * com a OS faturada/cancelada; 404 OS ou item inexistente; 422 com os
     * erros por campo; 500 quando o banco não gravou.
     */

    public function adicionarProduto()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar O.S.']);

            return;
        }

        $this->adicionarItem('produto');
    }

    public function excluirProduto()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar O.S.']);

            return;
        }

        $this->excluirItem('produto');
    }

    public function adicionarServico()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar O.S.']);

            return;
        }

        $this->adicionarItem('servico');
    }

    public function excluirServico()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar O.S.']);

            return;
        }

        $this->excluirItem('servico');
    }

    /**
     * Produto ou serviço novo na OS. O id do cadastro vem do autocomplete e é
     * conferido no banco; com o controle de estoque ligado, o produto sai do
     * estoque. Mudar os itens tira o desconto (o total mudou), como na v4.
     */
    private function adicionarItem(string $tipo): void
    {
        $os = $this->osParaAlterar();
        if (! $os) {
            return;
        }

        $produto = $tipo === 'produto';
        $idCadastro = (int) $this->input->post($produto ? 'idProduto' : 'idServico');
        $cadastro = $idCadastro > 0
            ? ($produto ? $this->os_model->getProdutoCadastro($idCadastro) : $this->os_model->getServicoCadastro($idCadastro))
            : null;
        $controleEstoque = (bool) ($this->data['configuration']['control_estoque'] ?? false);

        [$dados, $erros] = osItemDoFormulario($this->input->post(), $tipo, $cadastro, $controleEstoque);
        if ($erros !== []) {
            $this->responderJson(422, ['result' => false, 'message' => 'Confira os campos destacados.', 'erros' => $erros]);

            return;
        }

        $idOs = (int) $os->idOs;
        if (! $this->os_model->add($produto ? 'produtos_os' : 'servicos_os', $dados + ['os_id' => $idOs])) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível adicionar. Tente de novo.']);

            return;
        }

        if ($produto && $controleEstoque) {
            $this->load->model('produtos_model');
            $this->produtos_model->updateEstoque($dados['produtos_id'], $dados['quantidade'], '-');
        }

        $tinhaDesconto = (float) $os->valor_desconto > 0;
        $this->os_model->zerarDesconto($idOs);
        log_info(($produto ? 'Adicionou produto' : 'Adicionou serviço') . ' a uma OS. ID (OS): ' . $idOs);

        $nome = $produto ? $cadastro->descricao : $cadastro->nome;
        $this->responderTrechos($idOs, $tipo, $nome . ' adicionado à OS.' . ($tinhaDesconto ? ' O desconto foi removido porque o total mudou.' : ''));
    }

    /**
     * Tira um produto ou serviço da OS. O item, a quantidade e o produto vêm
     * do banco (na v4 a quantidade a devolver ao estoque vinha do navegador).
     */
    private function excluirItem(string $tipo): void
    {
        $os = $this->osParaAlterar();
        if (! $os) {
            return;
        }

        $produto = $tipo === 'produto';
        $idOs = (int) $os->idOs;
        $idItem = (int) $this->input->post('idItem');
        $item = $idItem > 0
            ? ($produto ? $this->os_model->getProdutoDaOs($idOs, $idItem) : $this->os_model->getServicoDaOs($idOs, $idItem))
            : null;

        if (! $item) {
            $this->responderJson(404, ['result' => false, 'message' => 'Item não encontrado nesta OS. Recarregue a página.']);

            return;
        }

        if (! $this->os_model->delete($produto ? 'produtos_os' : 'servicos_os', $produto ? 'idProdutos_os' : 'idServicos_os', $idItem)) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível excluir. Tente de novo.']);

            return;
        }

        if ($produto && ($this->data['configuration']['control_estoque'] ?? false)) {
            $this->load->model('produtos_model');
            $this->produtos_model->updateEstoque($item->produtos_id, $item->quantidade, '+');
        }

        $tinhaDesconto = (float) $os->valor_desconto > 0;
        $this->os_model->zerarDesconto($idOs);
        log_info(($produto ? 'Removeu produto' : 'Removeu serviço') . ' de uma OS. ID (OS): ' . $idOs);

        $this->responderTrechos($idOs, $tipo, ($produto ? 'Produto removido da OS.' : 'Serviço removido da OS.') . ($tinhaDesconto ? ' O desconto foi removido porque o total mudou.' : ''));
    }

    /**
     * Resposta de sucesso com os trechos da tela que a mudança afeta.
     */
    private function responderTrechos(int $idOs, string $tipo, string $mensagem, int $status = 200, array $extras = []): void
    {
        $partes = match ($tipo) {
            'produto' => ['abas', 'produtos', 'totais', 'desconto', 'pix'],
            'servico' => ['abas', 'servicos', 'totais', 'desconto', 'pix'],
            'anexo' => ['abas', 'anexos'],
            'anotacao' => ['abas', 'anotacoes'],
            'desconto' => ['totais', 'desconto', 'pix'],
            default => throw new InvalidArgumentException("Tipo de trecho desconhecido: {$tipo}"),
        };

        $this->responderJson($status, [
            'result' => $status < 400,
            'message' => $mensagem,
            'html' => $this->partesDaTela($idOs, $partes),
            'totais' => $this->os_model->totais($this->os_model->getById($idOs)),
        ] + $extras);
    }

    public function anexar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar O.S.']);

            return;
        }

        $os = $this->osParaAlterar();
        if (! $os) {
            return;
        }

        // O id compõe o caminho do diretório: vem da OS conferida no banco, e
        // não do POST.
        $idOs = (int) $os->idOs;

        $arquivos = $_FILES['userfile'] ?? null;
        if (! is_array($arquivos) || ! is_array($arquivos['name'] ?? null) || array_filter($arquivos['name'], static fn ($nome) => (string) $nome !== '') === []) {
            $this->responderJson(422, ['result' => false, 'message' => 'Escolha ao menos um arquivo.', 'erros' => ['userfile[]' => 'Escolha ao menos um arquivo.']]);

            return;
        }

        $this->load->library('upload');
        $this->load->library('image_lib');

        $pastaMes = date('m-Y');
        $directory = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . $pastaMes . DIRECTORY_SEPARATOR . 'OS-' . $idOs;

        if (! is_dir($directory . DIRECTORY_SEPARATOR . 'thumbs') && ! @mkdir($directory . DIRECTORY_SEPARATOR . 'thumbs', 0755, true)) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível criar a pasta dos anexos no servidor.']);

            return;
        }

        protegerDiretorioUpload([dirname($directory), $directory, $directory . DIRECTORY_SEPARATOR . 'thumbs']);

        $this->upload->initialize([
            'upload_path' => $directory,
            'allowed_types' => 'jpg|png|gif|jpeg|JPG|PNG|GIF|JPEG|pdf|PDF|cdr|CDR|docx|DOCX|txt', // formatos permitidos para anexos de os
            'max_size' => 0,
        ]);

        foreach ($arquivos as $key => $val) {
            $i = 1;
            foreach ($val as $v) {
                $_FILES['file_' . $i][$key] = $v;
                $i++;
            }
        }
        unset($_FILES['userfile']);

        $url = base_url('assets/anexos/' . $pastaMes . '/OS-' . $idOs);
        $falhas = [];
        $enviados = 0;

        foreach ($_FILES as $campo => $arquivo) {
            if (! str_starts_with((string) $campo, 'file_')) {
                continue;
            }

            if (! $this->upload->do_upload($campo)) {
                $falhas[] = ($arquivo['name'] ?? 'Arquivo') . ': ' . trim(strip_tags($this->upload->display_errors('', '')));

                continue;
            }

            $upload = $this->upload->data();

            // Nome aleatório, mantendo a extensão original.
            $nome = bin2hex(random_bytes(16)) . '.' . strtolower(pathinfo($upload['file_name'], PATHINFO_EXTENSION));
            $caminho = $upload['file_path'] . $nome;
            rename($upload['full_path'], $caminho);

            $thumb = '';
            if ($upload['is_image'] == 1) {
                $this->image_lib->clear();
                $this->image_lib->initialize([
                    'source_image' => $caminho,
                    'new_image' => $upload['file_path'] . 'thumbs' . DIRECTORY_SEPARATOR . 'thumb_' . $nome,
                    'width' => 200,
                    'height' => 125,
                ]);

                if ($this->image_lib->resize()) {
                    $thumb = 'thumb_' . $nome;
                }
            }

            if ($this->os_model->anexar($idOs, $nome, $url, $thumb, $directory)) {
                $enviados++;
            } else {
                @unlink($caminho);
                $falhas[] = ($arquivo['name'] ?? 'Arquivo') . ': não foi possível gravar no banco.';
            }
        }

        if ($enviados > 0) {
            log_info('Adicionou anexo(s) a uma OS. ID (OS): ' . $idOs);
        }

        if ($falhas !== []) {
            // Os que deram certo já aparecem na lista; a mensagem diz quais falharam.
            $this->responderTrechos($idOs, 'anexo', 'Não foi possível anexar: ' . implode(' ', $falhas), 422, [
                'erros' => ['userfile[]' => implode(' ', $falhas)],
            ]);

            return;
        }

        $this->responderTrechos($idOs, 'anexo', $enviados === 1 ? 'Arquivo anexado.' : $enviados . ' arquivos anexados.');
    }

    /**
     * Exclui um anexo da OS (id no POST, ou no fim da URL como na v4). O anexo
     * precisa pertencer à OS, e só arquivos dentro de assets/anexos saem do
     * disco (osArquivosDosAnexos()).
     */
    public function excluirAnexo($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar O.S.']);

            return;
        }

        $os = $this->osParaAlterar();
        if (! $os) {
            return;
        }

        $idOs = (int) $os->idOs;
        $idAnexo = (int) ($this->input->post('idItem') ?: $id);
        $anexo = $idAnexo > 0 ? $this->os_model->getAnexoDaOs($idOs, $idAnexo) : null;

        if (! $anexo) {
            $this->responderJson(404, ['result' => false, 'message' => 'Anexo não encontrado nesta OS. Recarregue a página.']);

            return;
        }

        foreach (osArquivosDosAnexos([$anexo], FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos') as $arquivo) {
            @unlink($arquivo);
        }

        if (! $this->os_model->delete('anexos', 'idAnexos', $idAnexo)) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível excluir o anexo. Tente de novo.']);

            return;
        }

        log_info('Removeu anexo de uma OS. ID (OS): ' . $idOs);
        $this->responderTrechos($idOs, 'anexo', 'Anexo excluído.');
    }

    public function downloadanexo($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }

        $file = $id != null && is_numeric($id) ? $this->db->where('idAnexos', (int) $id)->get('anexos', 1)->row() : null;
        $arquivos = $file ? osArquivosDosAnexos([(object) ['path' => $file->path, 'anexo' => $file->anexo, 'thumb' => '']], FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos') : [];

        if ($arquivos === []) {
            $this->session->set_flashdata('error', 'Anexo não encontrado.');
            redirect(site_url('os'));
        }

        $this->load->library('zip');
        $this->zip->read_file($arquivos[0]);
        $this->zip->download('file' . date('d-m-Y-H.i.s') . '.zip');
    }

    /**
     * Desconto da OS em R$ ou %, calculado no servidor sobre o total dos
     * itens (osCalcularDesconto()). 0 remove o desconto.
     */
    public function adicionarDesconto()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar O.S.']);

            return;
        }

        $os = $this->osParaAlterar();
        if (! $os) {
            return;
        }

        $idOs = (int) $os->idOs;
        $totais = $this->os_model->totais($os);
        [$dados, $erros] = osCalcularDesconto($totais['bruto'], $this->input->post('tipoDesconto'), $this->input->post('desconto'));

        if ($erros !== []) {
            $this->responderJson(422, ['result' => false, 'message' => reset($erros), 'erros' => $erros]);

            return;
        }

        if (! $this->os_model->edit('os', $dados, 'idOs', $idOs)) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível gravar o desconto. Tente de novo.']);

            return;
        }

        log_info(($dados['valor_desconto'] > 0 ? 'Adicionou um desconto' : 'Removeu o desconto') . ' na OS. ID: ' . $idOs);
        $this->responderTrechos($idOs, 'desconto', $dados['valor_desconto'] > 0 ? 'Desconto aplicado. Total da OS: ' . dinheiro($dados['valor_desconto']) . '.' : 'Desconto removido.');
    }

    /**
     * Fatura a OS: grava a receita em lancamentos (com o vínculo em
     * os.lancamento, que a v4 nunca preenchia), marca a OS como faturada e
     * registra a mudança de status. Valor e desconto vêm dos totais
     * calculados no servidor.
     */
    public function faturar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar O.S.']);

            return;
        }

        $os = $this->osParaAlterar();
        if (! $os) {
            return;
        }

        $idOs = (int) $os->idOs;
        if ((int) $os->faturado === 1 || $os->status === 'Cancelado') {
            $this->responderJson(422, ['result' => false, 'message' => 'Esta OS já foi faturada ou está cancelada.', 'erros' => ['_geral' => 'Esta OS já foi faturada ou está cancelada.']]);

            return;
        }

        $totais = $this->os_model->totais($os);
        [$lancamento, $erros] = osFaturaDoFormulario($this->input->post(), $os, $totais, $this->usuarioLogado());

        if ($erros !== []) {
            $this->responderJson(422, ['result' => false, 'message' => $erros['_geral'] ?? 'Confira os campos destacados.', 'erros' => $erros]);

            return;
        }

        $this->db->trans_start();

        $idLancamento = $this->os_model->add('lancamentos', $lancamento, true);
        if (! is_numeric($idLancamento)) {
            $this->db->trans_rollback();

            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível criar o lançamento. Tente de novo.']);

            return;
        }

        $this->os_model->edit('os', [
            'faturado' => 1,
            'valorTotal' => $totais['bruto'],
            'desconto' => $totais['desconto'],
            'valor_desconto' => $totais['total'],
            'status' => 'Faturado',
            'lancamento' => (int) $idLancamento,
        ], 'idOs', $idOs);
        $this->os_model->registrarStatus($idOs, (string) $os->status, 'Faturado', $this->usuarioLogado());

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível faturar a OS. Tente de novo.']);

            return;
        }

        log_info('Faturou uma OS. ID: ' . $idOs);
        $this->session->set_flashdata('success', 'OS #' . $idOs . ' faturada. Lançamento de ' . dinheiro($totais['total']) . ' criado no financeiro.');

        $this->responderJson(200, [
            'result' => true,
            'message' => 'OS faturada.',
            'redirecionar' => site_url('os/visualizar/' . $idOs),
        ]);
    }

    private function enviarOsPorEmail($idOs, $remetentes, $assunto)
    {
        $dados = [];

        $this->load->model('mapos_model');
        $dados['result'] = $this->os_model->getById($idOs);
        if (! isset($dados['result']->email)) {
            return false;
        }

        $dados['produtos'] = $this->os_model->getProdutos($idOs);
        $dados['servicos'] = $this->os_model->getServicos($idOs);
        $dados['emitente'] = $this->mapos_model->getEmitente();
        $emitente = $dados['emitente'];
        if (! isset($emitente->email)) {
            return false;
        }

        $html = $this->load->view('os/emails/os', $dados, true);

        $this->load->model('email_model');

        $remetentes = array_unique($remetentes);
        foreach ($remetentes as $remetente) {
            if ($remetente) {
                $headers = ['From' => $emitente->email, 'Subject' => $assunto, 'Return-Path' => ''];
                $email = [
                    'to' => $remetente,
                    'message' => $html,
                    'status' => 'pending',
                    'date' => date('Y-m-d H:i:s'),
                    'headers' => json_encode($headers),
                ];
                $this->email_model->add('email_queue', $email);
            } else {
                log_info('Email não adicionado a Lista de envio de e-mails. Verifique se o remetente esta cadastrado. OS ID: ' . $idOs);
            }
        }

        return true;
    }

    /**
     * Anotação interna da OS. O autor vai no próprio texto, entre colchetes,
     * como na v4 (a tabela anotacoes_os não tem coluna de usuário).
     */
    public function adicionarAnotacao()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar O.S.']);

            return;
        }

        $os = $this->osParaAlterar();
        if (! $os) {
            return;
        }

        $idOs = (int) $os->idOs;
        $texto = trim((string) (is_scalar($this->input->post('anotacao')) ? $this->input->post('anotacao') : ''));
        $prefixo = '[' . $this->session->userdata('nome_admin') . '] ';
        // anotacoes_os.anotacao é VARCHAR(255), com o nome do autor.
        $limite = 255 - mb_strlen($prefixo);

        if ($texto === '') {
            $this->responderJson(422, ['result' => false, 'message' => 'Escreva a anotação.', 'erros' => ['anotacao' => 'Escreva a anotação.']]);

            return;
        }
        if (mb_strlen($texto) > $limite) {
            $this->responderJson(422, ['result' => false, 'message' => 'A anotação tem até ' . $limite . ' caracteres.', 'erros' => ['anotacao' => 'A anotação tem até ' . $limite . ' caracteres.']]);

            return;
        }

        if (! $this->os_model->add('anotacoes_os', ['anotacao' => $prefixo . $texto, 'data_hora' => date('Y-m-d H:i:s'), 'os_id' => $idOs])) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível gravar a anotação. Tente de novo.']);

            return;
        }

        log_info('Adicionou anotação a uma OS. ID (OS): ' . $idOs);
        $this->responderTrechos($idOs, 'anotacao', 'Anotação adicionada.');
    }

    public function excluirAnotacao()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->responderJson(403, ['result' => false, 'message' => 'Você não tem permissão para editar O.S.']);

            return;
        }

        $os = $this->osParaAlterar();
        if (! $os) {
            return;
        }

        $idOs = (int) $os->idOs;
        $idAnotacao = (int) $this->input->post('idItem');
        if ($idAnotacao <= 0 || ! $this->os_model->getAnotacaoDaOs($idOs, $idAnotacao)) {
            $this->responderJson(404, ['result' => false, 'message' => 'Anotação não encontrada nesta OS. Recarregue a página.']);

            return;
        }

        if (! $this->os_model->delete('anotacoes_os', 'idAnotacoes', $idAnotacao)) {
            $this->responderJson(500, ['result' => false, 'message' => 'Não foi possível excluir a anotação. Tente de novo.']);

            return;
        }

        log_info('Removeu anotação de uma OS. ID (OS): ' . $idOs);
        $this->responderTrechos($idOs, 'anotacao', 'Anotação excluída.');
    }
}
