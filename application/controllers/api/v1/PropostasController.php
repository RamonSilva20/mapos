<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class PropostasController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('propostas_model');
    }

    public function index_get($id = '')
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'vPropostas')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a visualizar propostas.',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $where_array = [];

        $pesquisa = trim($this->get('search', true));
        $status   = $this->get('status', true);
        $de       = $this->get('from', true);
        $ate      = $this->get('to', true);

        if ($pesquisa) {
            $where_array['pesquisa'] = $pesquisa;
        }
        if ($status !== '' && $status !== null) {
            $where_array['status'] = is_array($status) ? $status : [ $status ];
        }
        if ($de) {
            if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $de)) {
                $where_array['de'] = $de;
            } else {
                $parts = explode('/', $de);
                $where_array['de'] = (count($parts) >= 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $de;
            }
        }
        if ($ate) {
            if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $ate)) {
                $where_array['ate'] = $ate;
            } else {
                $parts = explode('/', $ate);
                $where_array['ate'] = (count($parts) >= 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $ate;
            }
        }

        if (! $id) {
            $perPage = (int) ($this->get('perPage', true) ?: 20);
            $page    = (int) ($this->get('page', true) ?: 0);
            $start   = $page * $perPage;

            $propostas = $this->propostas_model->getPropostas($where_array, $perPage, $start);

            $this->response([
                'status'  => true,
                'message' => 'Lista de Propostas Comerciais',
                'result'  => $propostas,
            ], REST_Controller::HTTP_OK);
        }

        if ($id && is_numeric($id)) {
            $proposta = $this->propostas_model->getById($id);

            if (! $proposta) {
                $this->response([
                    'status'  => false,
                    'message' => 'Proposta não encontrada.',
                    'result'  => null,
                ], REST_Controller::HTTP_NOT_FOUND);
            }

            $proposta->produtos = $this->propostas_model->getProdutos($id);
            $proposta->servicos = $this->propostas_model->getServicos($id);
            $proposta->parcelas = $this->propostas_model->getParcelas($id);
            $proposta->outros   = $this->propostas_model->getOutros($id);

            $totalProdutos = 0;
            $totalServicos = 0;
            foreach ($proposta->produtos as $p) {
                $totalProdutos += (float) $p->preco * (float) $p->quantidade;
            }
            foreach ($proposta->servicos as $s) {
                $totalServicos += (float) $s->preco * (float) $s->quantidade;
            }
            $proposta->totalProdutos = $totalProdutos;
            $proposta->totalServicos = $totalServicos;

            $this->response([
                'status'  => true,
                'message' => 'Detalhes da Proposta',
                'result'  => $proposta,
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status'  => false,
            'message' => 'Nenhuma proposta localizada.',
            'result'  => null,
        ], REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'aPropostas')) {
            $this->response([
                'status'  => false,
                'message' => 'Você não está autorizado a adicionar propostas.',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $input = $this->getRequestBody();

        $msg = $this->validarDadosProposta($input, false);
        if ($msg !== null) {
            $this->response([
                'status'  => false,
                'message' => $msg,
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = $this->montarDadosProposta($input, false);
        $data['usuarios_id'] = isset($input['usuarios_id']) && $input['usuarios_id']
            ? (int) $input['usuarios_id']
            : (int) $this->logged_user()->usuario->idUsuarios;
        $data['numero_proposta'] = $this->propostas_model->gerarNumeroProposta();

        $idProposta = $this->propostas_model->add('propostas', $data, true);
        if (! is_numeric($idProposta) || $idProposta <= 0) {
            $this->response([
                'status'  => false,
                'message' => 'Não foi possível criar a proposta.',
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->salvarProdutos($idProposta, $input);
        $this->salvarServicos($idProposta, $input);
        $this->salvarParcelas($idProposta, $input);
        $this->salvarOutros($idProposta, $input);

        $this->atualizarValorTotalProposta($idProposta);

        $this->log_app('Adicionou uma Proposta via API. ID: ' . $idProposta);

        $proposta = $this->propostas_model->getById($idProposta);
        $proposta->produtos = $this->propostas_model->getProdutos($idProposta);
        $proposta->servicos = $this->propostas_model->getServicos($idProposta);
        $proposta->parcelas = $this->propostas_model->getParcelas($idProposta);
        $proposta->outros   = $this->propostas_model->getOutros($idProposta);

        $this->response([
            'status'  => true,
            'message' => 'Proposta criada com sucesso!',
            'result'  => $proposta,
        ], REST_Controller::HTTP_CREATED);
    }

    public function index_put($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'ePropostas')) {
            $this->response([
                'status'  => false,
                'message' => 'Você não está autorizado a editar propostas.',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id || ! is_numeric($id)) {
            $this->response([
                'status'  => false,
                'message' => 'ID da proposta inválido.',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $propostaAntiga = $this->propostas_model->getById($id);
        if (! $propostaAntiga) {
            $this->response([
                'status'  => false,
                'message' => 'Proposta não encontrada.',
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $input = $this->getRequestBody();

        // Salva o status original real ANTES de qualquer alteração
        $trueOriginalStatus = $propostaAntiga->status ?? 'Aberto';

        // Captura o status da query string (GET) ou do body (input)
        $qStatus = $this->input->get('status') ?: $this->get('status', false);
        if ($qStatus) {
            $qStatus = trim((string) $qStatus);
        }

        if ($qStatus !== '' && $qStatus !== null && (! isset($input['status']) || $input['status'] === '')) {
            $input['status'] = $qStatus;
        }

        $novoStatus = null;
        if (! empty($input['status'])) {
            $novoStatus = $this->normalizarStatusProposta($input['status']);
            $input['status'] = $novoStatus; 
        }

        // Update imediato do status se ele mudou
        if ($novoStatus && $novoStatus !== $trueOriginalStatus) {
            $this->db->where('idProposta', $id);
            $this->db->update('propostas', ['status' => $novoStatus]);
            // Recarrega apenas para que o restante do fluxo veja o status novo
            $propostaAntiga = $this->propostas_model->getById($id);
        }

        $msg = $this->validarDadosProposta($input, true);
        if ($msg !== null) {
            $this->response([
                'status'  => false,
                'message' => $msg,
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = $this->montarDadosProposta($input, true, $propostaAntiga);
        if (! array_key_exists('status', $input)) {
            $data['status'] = $propostaAntiga->status ?? 'Aberto';
        }

        $statusAntigo = $trueOriginalStatus; // Usa o status de antes da requisição
        $novoStatus   = $data['status'];
        $statusAntigoConsome = $this->statusConsumeEstoque($statusAntigo);
        $novoStatusConsome   = $this->statusConsumeEstoque($novoStatus);

        if (! $this->propostas_model->edit('propostas', $data, 'idProposta', $id)) {
            $this->response([
                'status'  => false,
                'message' => 'Não foi possível atualizar a proposta.',
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $atualizarProdutos = array_key_exists('produtos', $input) || array_key_exists('produtos_json', $input);
        $atualizarServicos = array_key_exists('servicos', $input) || array_key_exists('servicos_json', $input);
        $atualizarParcelas = array_key_exists('parcelas', $input) || array_key_exists('parcelas_json', $input);
        $atualizarOutros   = array_key_exists('outros', $input) || array_key_exists('descricao_outros', $input) || array_key_exists('preco_outros', $input);

        if ($atualizarProdutos) {
            $this->db->where('proposta_id', $id);
            $this->db->delete('produtos_proposta');
            $this->salvarProdutos($id, $input);
        }
        if ($atualizarServicos) {
            $this->db->where('proposta_id', $id);
            $this->db->delete('servicos_proposta');
            $this->salvarServicos($id, $input);
        }
        if ($atualizarParcelas) {
            $this->db->where('proposta_id', $id);
            $this->db->delete('parcelas_proposta');
            $this->salvarParcelas($id, $input);
        }
        if ($atualizarOutros) {
            $this->db->where('proposta_id', $id);
            $this->db->delete('outros_proposta');
            $this->salvarOutros($id, $input);
        }

        if (! $statusAntigoConsome && $novoStatusConsome) {
            $this->consumirEstoqueProposta($id);
        } elseif ($statusAntigoConsome && ! $novoStatusConsome) {
            $this->devolverEstoqueProposta($id);
        }

        $this->atualizarValorTotalProposta($id);

        $this->log_app('Editou uma Proposta via API. ID: ' . $id);

        $proposta = $this->propostas_model->getById($id);
        $proposta->produtos = $this->propostas_model->getProdutos($id);
        $proposta->servicos = $this->propostas_model->getServicos($id);
        $proposta->parcelas = $this->propostas_model->getParcelas($id);
        $proposta->outros   = $this->propostas_model->getOutros($id);

        $this->response([
            'status'  => true,
            'message' => 'Proposta atualizada com sucesso!',
            'result'  => $proposta,
        ], REST_Controller::HTTP_OK);
    }

    /**
     * PATCH /api/v1/propostas/{id}
     * Atualização parcial (ex.: apenas status). Reutiliza a lógica do PUT.
     */
    public function index_patch($id)
    {
        return $this->index_put($id);
    }

    /**
     * PUT /api/v1/propostas/{id}/status
     * Endpoint dedicado para atualizar apenas o status da proposta.
     * Facilita a integração e evita problemas com body JSON complexo.
     */
    public function status_put($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'ePropostas')) {
            $this->response(['status' => false, 'message' => 'Sem permissão'], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id || ! is_numeric($id)) {
            $this->response(['status' => false, 'message' => 'ID inválido'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $input = $this->getRequestBody();
        $novoStatus = $this->input->get('status') ?: ($input['status'] ?? null);
        
        if (! $novoStatus) {
            $this->response(['status' => false, 'message' => 'Status não informado'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $novoStatus = $this->normalizarStatusProposta($novoStatus);
        
        $proposta = $this->propostas_model->getById($id);
        if (! $proposta) {
            $this->response(['status' => false, 'message' => 'Proposta não encontrada'], REST_Controller::HTTP_NOT_FOUND);
        }

        $statusAntigo = $proposta->status ?? 'Aberto';
        
        if ($novoStatus === $statusAntigo) {
            $this->response(['status' => true, 'message' => 'Status já é o atual', 'result' => $proposta], REST_Controller::HTTP_OK);
        }

        // Controle de estoque
        $statusAntigoConsome = $this->statusConsumeEstoque($statusAntigo);
        $novoStatusConsome   = $this->statusConsumeEstoque($novoStatus);

        if (! $statusAntigoConsome && $novoStatusConsome) {
            $this->consumirEstoqueProposta($id);
        } elseif ($statusAntigoConsome && ! $novoStatusConsome) {
            $this->devolverEstoqueProposta($id);
        }

        // Update direto no DB para máxima confiabilidade
        $this->db->where('idProposta', $id);
        $this->db->update('propostas', ['status' => $novoStatus]);

        $this->log_app('Atualizou status da Proposta via API (Dedicado). ID: ' . $id . ' | De: ' . $statusAntigo . ' | Para: ' . $novoStatus);

        $result = $this->propostas_model->getById($id);
        $this->response([
            'status'  => true,
            'message' => "Status atualizado de '{$statusAntigo}' para '{$novoStatus}'",
            'result'  => $result
        ], REST_Controller::HTTP_OK);
    }

    /**
     * Adiciona produtos à proposta (incremental, passo a passo).
     * POST /api/v1/propostas/{id}/produtos
     * Body: { "itens": [ { "descricao", "quantidade", "preco", "produtos_id"? } ] }
     * ou um único item: { "descricao", "quantidade", "preco" }
     */
    public function produtos_post($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'ePropostas')) {
            $this->response(['status' => false, 'message' => 'Sem permissão para editar propostas.'], REST_Controller::HTTP_UNAUTHORIZED);
        }
        if (! $id || ! is_numeric($id)) {
            $this->response(['status' => false, 'message' => 'ID da proposta inválido.'], REST_Controller::HTTP_BAD_REQUEST);
        }
        $proposta = $this->propostas_model->getById($id);
        if (! $proposta) {
            $this->response(['status' => false, 'message' => 'Proposta não encontrada.'], REST_Controller::HTTP_NOT_FOUND);
        }

        $input = $this->getRequestBody();
        $itens = $this->normalizarItens($input, 'produtos');
        if (empty($itens)) {
            $this->response(['status' => false, 'message' => 'Envie ao menos um item: { "descricao", "quantidade", "preco" } ou { "itens": [ ... ] }.'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $fake = [ 'produtos' => $itens ];
        $this->acrescentarProdutos($id, $fake);
        $this->atualizarValorTotalProposta($id);

        $proposta = $this->propostas_model->getById($id);
        $proposta->produtos = $this->propostas_model->getProdutos($id);
        $proposta->servicos = $this->propostas_model->getServicos($id);
        $proposta->outros   = $this->propostas_model->getOutros($id);
        $this->log_app('Acrescentou produtos à Proposta via API. ID: ' . $id);

        $this->response([
            'status'  => true,
            'message' => 'Produtos adicionados.',
            'result'  => $proposta,
        ], REST_Controller::HTTP_CREATED);
    }

    /**
     * Adiciona serviços à proposta (incremental).
     * POST /api/v1/propostas/{id}/servicos
     * Body: { "itens": [ { "descricao", "quantidade", "preco", "servicos_id"? } ] } ou um único item.
     */
    public function servicos_post($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'ePropostas')) {
            $this->response(['status' => false, 'message' => 'Sem permissão para editar propostas.'], REST_Controller::HTTP_UNAUTHORIZED);
        }
        if (! $id || ! is_numeric($id)) {
            $this->response(['status' => false, 'message' => 'ID da proposta inválido.'], REST_Controller::HTTP_BAD_REQUEST);
        }
        $proposta = $this->propostas_model->getById($id);
        if (! $proposta) {
            $this->response(['status' => false, 'message' => 'Proposta não encontrada.'], REST_Controller::HTTP_NOT_FOUND);
        }

        $input = $this->getRequestBody();
        $itens = $this->normalizarItens($input, 'servicos');
        if (empty($itens)) {
            $this->response(['status' => false, 'message' => 'Envie ao menos um item: { "descricao", "quantidade", "preco" } ou { "itens": [ ... ] }.'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $fake = [ 'servicos' => $itens ];
        $this->acrescentarServicos($id, $fake);
        $this->atualizarValorTotalProposta($id);

        $proposta = $this->propostas_model->getById($id);
        $proposta->produtos = $this->propostas_model->getProdutos($id);
        $proposta->servicos = $this->propostas_model->getServicos($id);
        $proposta->outros   = $this->propostas_model->getOutros($id);
        $this->log_app('Acrescentou serviços à Proposta via API. ID: ' . $id);

        $this->response([
            'status'  => true,
            'message' => 'Serviços adicionados.',
            'result'  => $proposta,
        ], REST_Controller::HTTP_CREATED);
    }

    /**
     * Adiciona "outros" à proposta (mão de obra, itens avulsos, etc.).
     * POST /api/v1/propostas/{id}/outros
     * Body: { "itens": [ { "descricao", "preco" } ] } ou { "descricao", "preco" }.
     */
    public function outros_post($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'ePropostas')) {
            $this->response(['status' => false, 'message' => 'Sem permissão para editar propostas.'], REST_Controller::HTTP_UNAUTHORIZED);
        }
        if (! $id || ! is_numeric($id)) {
            $this->response(['status' => false, 'message' => 'ID da proposta inválido.'], REST_Controller::HTTP_BAD_REQUEST);
        }
        $proposta = $this->propostas_model->getById($id);
        if (! $proposta) {
            $this->response(['status' => false, 'message' => 'Proposta não encontrada.'], REST_Controller::HTTP_NOT_FOUND);
        }

        $input = $this->getRequestBody();
        $itens = $this->normalizarItensOutros($input);
        if (empty($itens)) {
            $this->response(['status' => false, 'message' => 'Envie ao menos um item: { "descricao", "preco" } ou { "itens": [ ... ] }.'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $fake = [ 'outros' => $itens ];
        $this->acrescentarOutros($id, $fake);
        $this->atualizarValorTotalProposta($id);

        $proposta = $this->propostas_model->getById($id);
        $proposta->produtos = $this->propostas_model->getProdutos($id);
        $proposta->servicos = $this->propostas_model->getServicos($id);
        $proposta->outros   = $this->propostas_model->getOutros($id);
        $this->log_app('Acrescentou outros itens à Proposta via API. ID: ' . $id);

        $this->response([
            'status'  => true,
            'message' => 'Itens adicionados.',
            'result'  => $proposta,
        ], REST_Controller::HTTP_CREATED);
    }

    public function index_delete($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'dPropostas')) {
            $this->response([
                'status'  => false,
                'message' => 'Você não está autorizado a excluir propostas.',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id || ! is_numeric($id)) {
            $this->response([
                'status'  => false,
                'message' => 'ID da proposta inválido.',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $proposta = $this->propostas_model->getById($id);
        if (! $proposta) {
            $this->response([
                'status'  => false,
                'message' => 'Proposta não encontrada.',
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if ($this->statusConsumeEstoque($proposta->status ?? 'Aberto')) {
            $this->devolverEstoqueProposta($id);
        }

        $this->db->trans_start();
        $this->db->where('proposta_id', $id);
        $this->db->delete('produtos_proposta');
        $this->db->where('proposta_id', $id);
        $this->db->delete('servicos_proposta');
        $this->db->where('proposta_id', $id);
        $this->db->delete('parcelas_proposta');
        $this->db->where('proposta_id', $id);
        $this->db->delete('outros_proposta');
        $this->db->where('idProposta', $id);
        $this->db->delete('propostas');
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $err = $this->db->error();
            $this->response([
                'status'  => false,
                'message' => 'Erro ao excluir proposta: ' . (isset($err['message']) ? $err['message'] : 'erro desconhecido'),
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->log_app('Removeu uma Proposta via API. ID: ' . $id);

        $this->response([
            'status'  => true,
            'message' => 'Proposta excluída com sucesso!',
        ], REST_Controller::HTTP_OK);
    }

    /**
     * GET /api/v1/propostas/{id}/pdf
     * Gera e retorna o PDF da proposta (mesmo modelo do "Imprimir" na web).
     * Requer autenticação e permissão vPropostas.
     * Query: ?inline=1 para abrir no navegador (inline); omitir para download.
     */
    public function pdf_get($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'vPropostas')) {
            $this->response([
                'status'  => false,
                'message' => 'Você não está autorizado a visualizar propostas.',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id || ! is_numeric($id)) {
            $this->response([
                'status'  => false,
                'message' => 'ID da proposta inválido.',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $result = $this->propostas_model->getById($id);
        if (! $result) {
            $this->response([
                'status'  => false,
                'message' => 'Proposta não encontrada.',
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $this->load->model('mapos_model');
        $emitente = $this->mapos_model->getEmitente();

        $configuration = [ 'pix_key' => '' ];
        $rows = $this->db->get('configuracoes')->result();
        foreach ($rows as $c) {
            $configuration[$c->config] = $c->valor;
        }

        $this->data = [
            'result'        => $result,
            'produtos'      => $this->propostas_model->getProdutos($id),
            'servicos'      => $this->propostas_model->getServicos($id),
            'parcelas'      => $this->propostas_model->getParcelas($id),
            'outros'        => $this->propostas_model->getOutros($id),
            'emitente'      => $emitente,
            'configuration' => $configuration,
        ];

        if (! empty($configuration['pix_key'])) {
            $qr = $this->propostas_model->getQrCode($id, $configuration['pix_key'], $emitente);
            $this->data['qrCode'] = $qr ?: '';
            $this->data['chaveFormatada'] = $this->formatarChavePix($configuration['pix_key']);
        }

        $this->load->helper('mpdf');
        $html = $this->load->view('propostas/imprimirProposta', $this->data, true);
        $html = preg_replace('/<script[^>]*>[\s\S]*?window\.print\(\)[\s\S]*?<\/script>/i', '', $html);

        $numeroProposta = $result->numero_proposta ?: $result->idProposta;
        $numeroProposta = preg_replace('/[^0-9]/', '', $numeroProposta);
        if ($numeroProposta === '') {
            $numeroProposta = $result->idProposta;
        }
        $filename = 'Proposta_' . $numeroProposta . '_' . date('YmdHis');

        $pdfPath = pdf_create($html, $filename, false, false, false);
        if (! $pdfPath || ! is_file($pdfPath)) {
            $this->response([
                'status'  => false,
                'message' => 'Erro ao gerar PDF da proposta.',
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $pdfBinary = file_get_contents($pdfPath);
        @unlink($pdfPath);

        $inline = $this->get('inline', true) === '1' || $this->get('inline', true) === 'true';
        $disposition = $inline ? 'inline' : 'attachment';
        $safeName = 'Proposta_' . $numeroProposta . '.pdf';

        $this->log_app('Download do PDF da Proposta via API. ID: ' . $id);

        while (ob_get_level()) {
            ob_end_clean();
        }

        $this->output
            ->set_content_type('application/pdf')
            ->set_header('Content-Disposition: ' . $disposition . '; filename="' . $safeName . '"')
            ->set_header('Content-Length: ' . strlen($pdfBinary))
            ->set_output($pdfBinary);

        $this->output->_display();
        exit;
    }

    /**
     * Recalcula o valor total da proposta a partir de produtos, serviços e outros,
     * aplica valor_desconto e atualiza propostas.valor_total.
     * Garante que a listagem exiba o total corretamente após criar/editar via API.
     */
    private function atualizarValorTotalProposta($idProposta)
    {
        $proposta = $this->propostas_model->getById($idProposta);
        if (! $proposta) {
            return;
        }

        $produtos = $this->propostas_model->getProdutos($idProposta);
        $servicos = $this->propostas_model->getServicos($idProposta);
        $outros   = $this->propostas_model->getOutros($idProposta);

        $totalProdutos = 0;
        foreach ($produtos as $p) {
            $totalProdutos += (float) ($p->subtotal ?? ($p->preco * $p->quantidade));
        }
        $totalServicos = 0;
        foreach ($servicos as $s) {
            $totalServicos += (float) $s->preco * (float) ($s->quantidade ?: 1);
        }
        $totalOutros = 0;
        foreach ($outros as $o) {
            $totalOutros += (float) ($o->preco ?? 0);
        }

        $subtotal   = $totalProdutos + $totalServicos + $totalOutros;
        $valorDesc  = (float) ($proposta->valor_desconto ?? 0);
        $valorTotal = max(0, $subtotal - $valorDesc);

        $this->propostas_model->edit('propostas', [ 'valor_total' => round($valorTotal, 2) ], 'idProposta', $idProposta);
    }

    /**
     * Formata chave Pix para exibição (CPF, CNPJ, telefone).
     */
    private function formatarChavePix($chave)
    {
        if (! $chave) {
            return '';
        }
        $chaveLimpa = preg_replace('/[^0-9]/', '', $chave);
        if (strlen($chaveLimpa) === 11) {
            return substr($chaveLimpa, 0, 3) . '.' . substr($chaveLimpa, 3, 3) . '.' . substr($chaveLimpa, 6, 3) . '-' . substr($chaveLimpa, 9);
        }
        if (strlen($chaveLimpa) === 14) {
            return substr($chaveLimpa, 0, 2) . '.' . substr($chaveLimpa, 2, 3) . '.' . substr($chaveLimpa, 5, 3) . '/' . substr($chaveLimpa, 8, 4) . '-' . substr($chaveLimpa, 12);
        }
        if (strlen($chaveLimpa) === 11 && (int) substr($chaveLimpa, 0, 2) >= 10) {
            return '(' . substr($chaveLimpa, 0, 2) . ') ' . substr($chaveLimpa, 2, 5) . '-' . substr($chaveLimpa, 7);
        }
        return $chave;
    }

    /**
     * Obtém o body da requisição (JSON) como array.
     * Usa raw_input_stream (cache do CI) ou, se vazio, os args já parseados pelo REST_Controller.
     * Suporta POST, PUT e PATCH.
     */
    private function getRequestBody()
    {
        $raw = $this->input->raw_input_stream;
        if ($raw !== null && $raw !== '') {
            $dec = json_decode($raw, true);
            if (is_array($dec)) {
                return $dec;
            }
        }
        $method = strtoupper($this->input->method());
        $args   = null;
        if ($method === 'PUT') {
            $args = $this->put();
        } elseif ($method === 'POST') {
            $args = $this->post();
        } elseif ($method === 'PATCH') {
            $args = $this->patch();
        }
        if ($args !== null && (is_array($args) || is_object($args))) {
            return is_array($args) ? $args : (array) json_decode(json_encode($args), true);
        }
        return [];
    }

    private function validarDadosProposta(array $input, $edicao)
    {
        if (! $edicao) {
            $temCliente = (! empty($input['clientes_id']) && is_numeric($input['clientes_id']))
                || (! empty($input['cliente_nome']) && is_string(trim($input['cliente_nome'] ?? '')));
            if (! $temCliente) {
                return 'Informe o cliente (clientes_id ou cliente_nome).';
            }
        }
        return null;
    }

    private function parseData($str)
    {
        if (empty($str)) {
            return null;
        }
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $str, $m)) {
            return $str;
        }
        $parts = preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $str, $m) ? [ $m[3], $m[2], $m[1] ] : null;
        return $parts ? ($parts[0] . '-' . $parts[1] . '-' . $parts[2]) : null;
    }

    private function montarDadosProposta(array $input, $edicao, $propostaAntiga = null)
    {
        $dataProposta = $this->parseData($input['data_proposta'] ?? '');
        $dataValidade = $this->parseData($input['data_validade'] ?? '');

        $clienteId   = null;
        $clienteNome = null;
        if (! empty($input['clientes_id']) && is_numeric($input['clientes_id'])) {
            $clienteId = (int) $input['clientes_id'];
        } elseif (! empty($input['cliente_nome'])) {
            $clienteNome = trim($input['cliente_nome']);
        } elseif ($edicao && $propostaAntiga) {
            $clienteId   = $propostaAntiga->clientes_id;
            $clienteNome = $propostaAntiga->cliente_nome;
        }

        $desconto = 0;
        if (isset($input['desconto'])) {
            $d = is_string($input['desconto']) ? str_replace([ '.', ',' ], [ '', '.' ], $input['desconto']) : $input['desconto'];
            $desconto = (float) $d;
        } elseif ($edicao && $propostaAntiga && isset($propostaAntiga->desconto)) {
            $desconto = (float) $propostaAntiga->desconto;
        }

        $opt = function ($key, $default = null) use ($input, $edicao, $propostaAntiga) {
            if (array_key_exists($key, $input)) {
                return $input[$key];
            }
            if ($edicao && $propostaAntiga && isset($propostaAntiga->$key)) {
                return $propostaAntiga->$key;
            }
            return $default;
        };

        $validadeDias = null;
        if (array_key_exists('validade_dias', $input)) {
            $validadeDias = $input['validade_dias'] !== '' && $input['validade_dias'] !== null
                ? (int) $input['validade_dias'] : null;
        } elseif ($edicao && $propostaAntiga) {
            $validadeDias = $propostaAntiga->validade_dias;
        }

        $ant = $edicao && $propostaAntiga ? $propostaAntiga : null;
        $data = [
            'data_proposta'     => $dataProposta ?: ($ant && isset($ant->data_proposta) ? $ant->data_proposta : date('Y-m-d')),
            'data_validade'     => $dataValidade !== null ? $dataValidade : ($ant ? ($ant->data_validade ?? null) : null),
            'status'            => $input['status'] ?? ($ant && isset($ant->status) ? $ant->status : 'Aberto'),
            'clientes_id'       => $clienteId,
            'cliente_nome'      => $clienteNome,
            'observacoes'       => $opt('observacoes', null),
            'desconto'          => $desconto,
            'valor_desconto'    => (float) (array_key_exists('valor_desconto', $input) ? $input['valor_desconto'] : ($ant ? ($ant->valor_desconto ?? 0) : 0)),
            'tipo_desconto'     => $opt('tipo_desconto', 'real'),
            'valor_total'       => (float) (array_key_exists('valor_total', $input) ? $input['valor_total'] : ($ant ? ($ant->valor_total ?? 0) : 0)),
            'tipo_cond_comerc'  => $opt('tipo_cond_comerc', 'N'),
            'cond_comerc_texto' => $opt('cond_comerc_texto', null),
            'validade_dias'     => $validadeDias,
            'prazo_entrega'     => $opt('prazo_entrega', null),
        ];

        if (! $edicao) {
            $data['usuarios_id'] = isset($input['usuarios_id']) && $input['usuarios_id']
                ? (int) $input['usuarios_id']
                : (int) $this->logged_user()->usuario->idUsuarios;
        } elseif (isset($input['usuarios_id'])) {
            $data['usuarios_id'] = (int) $input['usuarios_id'];
        }

        return $data;
    }

    /**
     * Normaliza body para itens de produtos ou serviços.
     * Aceita { "itens": [ ... ] } ou um único objeto { "descricao", "quantidade", "preco" }.
     */
    private function normalizarItens(array $input, $tipo)
    {
        $itens = [];
        if (! empty($input['itens']) && is_array($input['itens'])) {
            $itens = $input['itens'];
        } elseif (! empty($input['descricao']) || ! empty($input['description']) || ! empty($input['nome']) || ! empty($input['name'])) {
            $itens = [ $input ];
        }
        $out = [];
        foreach ($itens as $x) {
            $d = trim((string) ($x['descricao'] ?? $x['description'] ?? $x['nome'] ?? $x['name'] ?? ''));
            if ($d === '') {
                continue;
            }
            $q = (float) ($x['quantidade'] ?? $x['quantity'] ?? 1);
            $p = (float) ($x['preco'] ?? $x['price'] ?? 0);
            $row = [ 'descricao' => $d, 'quantidade' => $q, 'preco' => $p ];
            if ($tipo === 'produtos' && isset($x['produtos_id']) && is_numeric($x['produtos_id'])) {
                $row['produtos_id'] = (int) $x['produtos_id'];
            }
            if ($tipo === 'servicos' && isset($x['servicos_id']) && is_numeric($x['servicos_id'])) {
                $row['servicos_id'] = (int) $x['servicos_id'];
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Normaliza body para "outros": { "itens": [ { "descricao", "preco" } ] } ou único { "descricao", "preco" }.
     */
    private function normalizarItensOutros(array $input)
    {
        $itens = [];
        if (! empty($input['itens']) && is_array($input['itens'])) {
            $itens = $input['itens'];
        } elseif (array_key_exists('descricao', $input) || array_key_exists('description', $input) || array_key_exists('preco', $input) || array_key_exists('price', $input)) {
            $itens = [ $input ];
        }
        $out = [];
        foreach ($itens as $x) {
            $d = trim((string) ($x['descricao'] ?? $x['description'] ?? ''));
            $p = isset($x['preco']) || isset($x['price'])
                ? (float) (is_string($x['preco'] ?? $x['price'] ?? 0) ? str_replace([ '.', ',' ], [ '', '.' ], $x['preco'] ?? $x['price'] ?? 0) : ($x['preco'] ?? $x['price'] ?? 0))
                : 0;
            if ($d === '' && $p <= 0) {
                continue;
            }
            $out[] = [ 'descricao' => $d, 'preco' => $p ];
        }
        return $out;
    }

    private function acrescentarProdutos($idProposta, array $input)
    {
        $produtos = $input['produtos'] ?? [];
        if (empty($produtos)) {
            return;
        }
        $fake = [ 'produtos' => $produtos ];
        $this->salvarProdutos($idProposta, $fake);
    }

    private function acrescentarServicos($idProposta, array $input)
    {
        $servicos = $input['servicos'] ?? [];
        if (empty($servicos)) {
            return;
        }
        $fake = [ 'servicos' => $servicos ];
        $this->salvarServicos($idProposta, $fake);
    }

    private function acrescentarOutros($idProposta, array $input)
    {
        $outros = $input['outros'] ?? [];
        if (empty($outros)) {
            return;
        }
        $fake = [ 'outros' => $outros ];
        $this->salvarOutros($idProposta, $fake);
    }

    private function salvarProdutos($idProposta, array $input)
    {
        $produtos = [];
        if (! empty($input['produtos']) && is_array($input['produtos'])) {
            $produtos = $input['produtos'];
        } elseif (! empty($input['produtos_json'])) {
            $dec = is_string($input['produtos_json']) ? json_decode($input['produtos_json'], true) : $input['produtos_json'];
            $produtos = is_array($dec) ? $dec : [];
        }

        $proposta = $this->propostas_model->getById($idProposta);
        $statusConsome = $this->statusConsumeEstoque($proposta->status ?? 'Aberto');
        $controlEstoque = $this->getConfig('control_estoque');
        $campos = $this->db->list_fields('produtos_proposta');
        $temEstoqueConsumido = in_array('estoque_consumido', $campos);

        foreach ($produtos as $p) {
            $descricao = trim((string) ($p['descricao'] ?? $p['description'] ?? $p['nome'] ?? $p['name'] ?? ''));
            if ($descricao === '') {
                continue;
            }
            $preco      = (float) ($p['preco'] ?? $p['price'] ?? 0);
            $quantidade = (float) ($p['quantidade'] ?? $p['quantity'] ?? 1);
            $subtotal   = $preco * $quantidade;
            $produtosId = ! empty($p['produtos_id']) && is_numeric($p['produtos_id']) ? (int) $p['produtos_id'] : null;

            $row = [
                'proposta_id'  => $idProposta,
                'produtos_id'  => $produtosId,
                'descricao'    => $descricao,
                'quantidade'   => $quantidade,
                'preco'        => $preco,
                'subtotal'     => $subtotal,
            ];
            if ($temEstoqueConsumido) {
                $row['estoque_consumido'] = 0;
            }

            $this->db->insert('produtos_proposta', $row);
            $lastId = $this->db->insert_id();

            if ($statusConsome && $produtosId && $controlEstoque) {
                $this->load->model('produtos_model');
                $this->produtos_model->updateEstoque($produtosId, $quantidade, '-');
                if ($temEstoqueConsumido) {
                    $this->db->where('idProdutoProposta', $lastId);
                    $this->db->update('produtos_proposta', [ 'estoque_consumido' => 1 ]);
                }
            }
        }
    }

    private function salvarServicos($idProposta, array $input)
    {
        $servicos = [];
        if (! empty($input['servicos']) && is_array($input['servicos'])) {
            $servicos = $input['servicos'];
        } elseif (! empty($input['servicos_json'])) {
            $dec = is_string($input['servicos_json']) ? json_decode($input['servicos_json'], true) : $input['servicos_json'];
            $servicos = is_array($dec) ? $dec : [];
        }

        foreach ($servicos as $s) {
            if (empty($s['descricao'])) {
                continue;
            }
            $preco      = (float) ($s['preco'] ?? 0);
            $quantidade = (float) ($s['quantidade'] ?? 1);
            $subtotal   = $preco * $quantidade;
            $servicosId = ! empty($s['servicos_id']) && is_numeric($s['servicos_id']) ? (int) $s['servicos_id'] : null;

            $this->db->insert('servicos_proposta', [
                'proposta_id'  => $idProposta,
                'servicos_id'  => $servicosId,
                'descricao'    => $s['descricao'],
                'quantidade'   => $quantidade,
                'preco'        => $preco,
                'subtotal'     => $subtotal,
            ]);
        }
    }

    private function salvarParcelas($idProposta, array $input)
    {
        $parcelas = [];
        if (! empty($input['parcelas']) && is_array($input['parcelas'])) {
            $parcelas = $input['parcelas'];
        } elseif (! empty($input['parcelas_json'])) {
            $dec = is_string($input['parcelas_json']) ? json_decode($input['parcelas_json'], true) : $input['parcelas_json'];
            $parcelas = is_array($dec) ? $dec : [];
        }

        $dataProposta = $this->db->select('data_proposta')->where('idProposta', $idProposta)->get('propostas')->row();
        $dataBase = $dataProposta && $dataProposta->data_proposta ? $dataProposta->data_proposta : date('Y-m-d');

        foreach ($parcelas as $parc) {
            $valor = $parc['valor'] ?? 0;
            if (is_string($valor)) {
                $valor = str_replace([ '.', ',' ], [ '', '.' ], $valor);
            }
            $valor = (float) $valor;
            $dias  = (int) ($parc['dias'] ?? 0);

            $dataVencimento = null;
            if ($dias > 0) {
                $dataVencimento = date('Y-m-d', strtotime($dataBase . " +{$dias} days"));
            } elseif (! empty($parc['data_vencimento'])) {
                $dataVencimento = $this->parseData($parc['data_vencimento']);
            }

            $this->db->insert('parcelas_proposta', [
                'proposta_id'    => $idProposta,
                'numero'         => (int) ($parc['numero'] ?? 1),
                'dias'           => $dias,
                'valor'          => $valor,
                'observacao'     => $parc['observacao'] ?? null,
                'data_vencimento' => $dataVencimento,
            ]);
        }
    }

    private function salvarOutros($idProposta, array $input)
    {
        $outros = [];

        if (! empty($input['outros']) && is_array($input['outros'])) {
            $outros = $input['outros'];
        } else {
            $desc = $input['descricao_outros'] ?? '';
            $preco = $input['preco_outros'] ?? 0;
            if ($desc !== '' || (is_numeric($preco) && (float) $preco > 0)) {
                $p = is_string($preco) ? str_replace([ '.', ',' ], [ '', '.' ], $preco) : $preco;
                $outros[] = [ 'descricao' => $desc, 'preco' => (float) $p ];
            }
        }

        foreach ($outros as $o) {
            $preco = (float) ($o['preco'] ?? 0);
            $desc  = trim($o['descricao'] ?? '');
            if ($desc === '' && $preco <= 0) {
                continue;
            }
            $this->db->insert('outros_proposta', [
                'proposta_id' => $idProposta,
                'descricao'   => $desc,
                'preco'       => $preco,
            ]);
        }
    }

    /**
     * Normaliza o status da proposta para o valor canônico (com acentos).
     * Aceita variações como "Negociacao", "Orcamento", "Aguardando Pecas", etc.
     */
    private function normalizarStatusProposta($status)
    {
        $s = trim((string) $status);
        if ($s === '') {
            return $s;
        }
        $map = [
            'negociacao'         => 'Negociação',
            'negociação'         => 'Negociação',
            'negociacão'         => 'Negociação',
            'orcamento'          => 'Orçamento',
            'orçamento'          => 'Orçamento',
            'orcamento '         => 'Orçamento',
            'aguardando pecas'   => 'Aguardando Peças',
            'aguardando peças'   => 'Aguardando Peças',
            'em andamento'       => 'Em Andamento',
            'aberto'             => 'Aberto',
            'faturado'           => 'Faturado',
            'finalizado'         => 'Finalizado',
            'cancelado'          => 'Cancelado',
            'aprovado'           => 'Aprovado',
        ];
        $low = mb_strtolower($s, 'UTF-8');
        if (isset($map[$low])) {
            return $map[$low];
        }
        $canonical = [ 'Aberto', 'Faturado', 'Negociação', 'Em Andamento', 'Orçamento', 'Finalizado', 'Cancelado', 'Aguardando Peças', 'Aprovado' ];
        foreach ($canonical as $c) {
            if (mb_strtolower($c, 'UTF-8') === $low) {
                return $c;
            }
        }
        return $s;
    }

    private function statusConsumeEstoque($status)
    {
        $naoConsomem = [ 'Orçamento', 'Negociação' ];
        return ! in_array($status, $naoConsomem, true);
    }

    private function consumirEstoqueProposta($idProposta)
    {
        $campos = $this->db->list_fields('produtos_proposta');
        $temEstoqueConsumido = in_array('estoque_consumido', $campos);

        $sql = "SELECT pp.idProdutoProposta, pp.produtos_id, pp.quantidade
                FROM produtos_proposta pp
                WHERE pp.proposta_id = ? AND pp.produtos_id IS NOT NULL";
        if ($temEstoqueConsumido) {
            $sql .= " AND pp.estoque_consumido = 0";
        }
        $produtos = $this->db->query($sql, [ $idProposta ])->result();

        if (empty($produtos)) {
            return;
        }

        $this->load->model('produtos_model');
        if (! $this->getConfig('control_estoque')) {
            return;
        }

        foreach ($produtos as $p) {
            $this->produtos_model->updateEstoque($p->produtos_id, $p->quantidade, '-');
            if ($temEstoqueConsumido) {
                $this->db->where('idProdutoProposta', $p->idProdutoProposta);
                $this->db->update('produtos_proposta', [ 'estoque_consumido' => 1 ]);
            }
        }
    }

    private function devolverEstoqueProposta($idProposta)
    {
        $campos = $this->db->list_fields('produtos_proposta');
        if (! in_array('estoque_consumido', $campos, true)) {
            return;
        }

        $produtos = $this->db->query(
            "SELECT pp.idProdutoProposta, pp.produtos_id, pp.quantidade
             FROM produtos_proposta pp
             WHERE pp.proposta_id = ? AND pp.estoque_consumido = 1 AND pp.produtos_id IS NOT NULL",
            [ $idProposta ]
        )->result();

        if (empty($produtos)) {
            return;
        }

        $this->load->model('produtos_model');
        if (! $this->getConfig('control_estoque')) {
            return;
        }

        foreach ($produtos as $p) {
            $this->produtos_model->updateEstoque($p->produtos_id, $p->quantidade, '+');
            $this->db->where('idProdutoProposta', $p->idProdutoProposta);
            $this->db->update('produtos_proposta', [ 'estoque_consumido' => 0 ]);
        }
    }
}
