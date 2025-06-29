<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class OsController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('os_model');
        $this->load->model('Api_model');
    }

    public function index_get($id = '')
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'vOs')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Visualizar Ordens de Serviços',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $where_array = [];

        $pesquisa = trim($this->get('search', true));
        $status = $this->get('status', true);
        $de = $this->get('from', true);
        $ate = $this->get('to', true);

        if ($pesquisa) {
            $where_array['pesquisa'] = $pesquisa;
        }
        if ($status) {
            $where_array['status'] = $status;
        }
        if ($de) {
            $de = explode('/', $de);
            $de = $de[2] . '-' . $de[1] . '-' . $de[0];

            $where_array['de'] = $de;
        }
        if ($ate) {
            $ate = explode('/', $ate);
            $ate = $ate[2] . '-' . $ate[1] . '-' . $ate[0];

            $where_array['ate'] = $ate;
        }

        if (! $id) {
            $perPage = $this->get('perPage', true) ?: 20;
            $page = $this->get('page', true) ? ($this->get('page', true) * $perPage) : 0;
            $start = $page ? ($perPage * $page) : 0;

            $oss = $this->os_model->getOs(
                'os',
                'os.*,
                COALESCE((SELECT SUM(produtos_os.preco * produtos_os.quantidade ) FROM produtos_os WHERE produtos_os.os_id = os.idOs), 0) totalProdutos,
                COALESCE((SELECT SUM(servicos_os.preco * servicos_os.quantidade ) FROM servicos_os WHERE servicos_os.os_id = os.idOs), 0) totalServicos',
                $where_array,
                $perPage,
                $page
            );

            $this->response([
                'status' => true,
                'message' => 'Listando OSs',
                'result' => $oss,
            ], REST_Controller::HTTP_OK);
        }

        $os = $this->os_model->getById($id);
        $os->produtos = $this->os_model->getProdutos($id);
        $os->servicos = $this->os_model->getServicos($id);
        $os->anexos = $this->os_model->getAnexos($id);
        $os->anotacoes = $this->os_model->getAnotacoes($id);
        $os->calcTotal = $this->calcTotal($id);
        unset($os->senha);
        $os->totalProdutos = array_sum(array_map(fn($p) => $p->preco * $p->quantidade, $os->produtos));
        $os->totalServicos = array_sum(array_map(fn($p) => $p->preco * $p->quantidade, $os->servicos));

        // Montando texto para whatsapp
        if ($return = $this->os_model->valorTotalOS($id)) {
            $totalProdutos = $return['totalProdutos'];
            $totalServico = $return['totalServico'];
        }

        $os->textoWhatsApp = $this->criarTextoWhats($os, $totalProdutos, $totalServico);

        $this->response([
            'status' => true,
            'message' => 'Detalhes da OS',
            'result' => $os,
        ], REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'aOs')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Adicionar OS!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $_POST = (array) json_decode(file_get_contents('php://input'), true);

        $this->load->library('form_validation');

        if ($this->form_validation->run('os') == false) {
            $this->response([
                'status' => false,
                'message' => validation_errors(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $dataInicial = $this->post('dataInicial', true);
        $dataFinal = $this->post('dataFinal', true);
        $termoGarantiaId = $this->post('termoGarantia', true);

        try {
            $dataInicial = explode('/', $dataInicial);
            $dataInicial = $dataInicial[2] . '-' . $dataInicial[1] . '-' . $dataInicial[0];

            if ($dataFinal) {
                $dataFinal = explode('/', $dataFinal);
                $dataFinal = $dataFinal[2] . '-' . $dataFinal[1] . '-' . $dataFinal[0];
            } else {
                $dataFinal = date('Y/m/d');
            }

            $termoGarantiaId = (! $termoGarantiaId == null || ! $termoGarantiaId == '') ? $this->post('garantias_id', true) : null;
        } catch (Exception $e) {
            $dataInicial = date('Y/m/d');
            $dataFinal = date('Y/m/d');
        }

        $data = [
            'dataInicial' => $dataInicial,
            'clientes_id' => $this->post('clientes_id', true),
            'usuarios_id' => $this->post('usuarios_id', true),
            'dataFinal' => $dataFinal,
            'garantia' => $this->post('garantia', true),
            'garantias_id' => $termoGarantiaId,
            'descricaoProduto' => $this->post('descricaoProduto', true),
            'defeito' => $this->post('defeito', true),
            'status' => $this->post('status', true),
            'observacoes' => $this->post('observacoes', true),
            'laudoTecnico' => $this->post('laudoTecnico', true),
            'faturado' => 0,
        ];

        if (is_numeric($id = $this->os_model->add('os', $data, true))) {
            $this->load->model('mapos_model');
            $this->load->model('usuarios_model');

            $idOs = $id;
            $os = $this->os_model->getById($idOs);
            $emitente = $this->mapos_model->getEmitente();
            $tecnico = $this->usuarios_model->getById($os->usuarios_id);

            // Verificar configuração de notificação
            if ($this->getConfig('os_notification') != 'nenhum' && $this->getConfig('email_automatico') == 1) {
                $remetentes = [];
                switch ($this->getConfig('os_notification')) {
                    case 'todos':
                        array_push($remetentes, $os->email);
                        array_push($remetentes, $tecnico->email);
                        array_push($remetentes, $emitente->email);
                        break;
                    case 'cliente':
                        array_push($remetentes, $os->email);
                        break;
                    case 'tecnico':
                        array_push($remetentes, $tecnico->email);
                        break;
                    case 'emitente':
                        array_push($remetentes, $emitente->email);
                        break;
                    default:
                        array_push($remetentes, $os->email);
                        break;
                }
                $this->enviarOsPorEmail($idOs, $remetentes, 'Ordem de Serviço - Criada');
            }

            $this->log_app('Adicionou uma OS. ID: ' . $id);

            $this->response([
                'status' => true,
                'message' => 'OS adicionada com sucesso!',
                'result' => $os,
            ], REST_Controller::HTTP_CREATED);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível adicionar a OS. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_put($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'eOs')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Adicionar OS!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $os = $this->os_model->getById($id);

        if (! $os) {
            $this->response([
                'status' => false,
                'message' => 'Essa OS não existe',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $_POST = (array) json_decode(file_get_contents('php://input'), true);

        if (! isset($_POST['dataInicial']) ||
           ! isset($_POST['dataFinal']) ||
           ! isset($_POST['status']) ||
           ! isset($_POST['clientes_id']) ||
           ! isset($_POST['usuarios_id'])
        ) {
            $this->response([
                'status' => false,
                'message' => 'Preencha os campos obrigatórios',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $dataInicial = $this->put('dataInicial', true);
        $dataFinal = $this->put('dataFinal', true);
        $termoGarantiaId = $this->put('termoGarantia', true);

        try {
            $dataInicial = explode('/', $dataInicial);
            $dataInicial = $dataInicial[2] . '-' . $dataInicial[1] . '-' . $dataInicial[0];

            if ($dataFinal) {
                $dataFinal = explode('/', $dataFinal);
                $dataFinal = $dataFinal[2] . '-' . $dataFinal[1] . '-' . $dataFinal[0];
            } else {
                $dataFinal = date('Y/m/d');
            }

            $termoGarantiaId = (! $termoGarantiaId == null || ! $termoGarantiaId == '') ? $this->put('garantias_id') : null;
        } catch (Exception $e) {
            $dataInicial = date('Y/m/d');
            $dataFinal = date('Y/m/d');
        }

        $data = [
            'dataInicial' => $dataInicial,
            'clientes_id' => $this->put('clientes_id', true),
            'usuarios_id' => $this->put('usuarios_id', true),
            'dataFinal' => $dataFinal,
            'garantia' => $this->put('garantia', true),
            'garantias_id' => $termoGarantiaId,
            'descricaoProduto' => $this->put('descricaoProduto', true),
            'defeito' => $this->put('defeito', true),
            'status' => $this->put('status', true),
            'observacoes' => $this->put('observacoes', true),
            'laudoTecnico' => $this->put('laudoTecnico', true),
            'faturado' => 0,
        ];

        if (strtolower($this->put('status')) == 'cancelado' && strtolower($os->status) != 'cancelado') {
            $this->devolucaoEstoque($id);
        }
        if (strtolower($os->status) == 'cancelado' && strtolower($this->put('status')) != 'cancelado') {
            $this->debitarEstoque($id);
        }

        if ($this->os_model->edit('os', $data, 'idOs', $id)) {
            $this->load->model('mapos_model');
            $this->load->model('usuarios_model');

            $idOs = $id;
            $os = $this->os_model->getById($idOs);
            $emitente = $this->mapos_model->getEmitente();
            $tecnico = $this->usuarios_model->getById($os->usuarios_id);

            // Verificar configuração de notificação
            if ($this->getConfig('os_notification') != 'nenhum' && $this->getConfig('email_automatico') == 1) {
                $remetentes = [];
                switch ($this->getConfig('os_notification')) {
                    case 'todos':
                        array_push($remetentes, $os->email);
                        array_push($remetentes, $tecnico->email);
                        array_push($remetentes, $emitente->email);
                        break;
                    case 'cliente':
                        array_push($remetentes, $os->email);
                        break;
                    case 'tecnico':
                        array_push($remetentes, $tecnico->email);
                        break;
                    case 'emitente':
                        array_push($remetentes, $emitente->email);
                        break;
                    default:
                        array_push($remetentes, $os->email);
                        break;
                }
                $this->enviarOsPorEmail($idOs, $remetentes, 'Ordem de Serviço - Criada');
            }

            $this->log_app('Alterou uma OS. ID: ' . $id);

            $this->response([
                'status' => true,
                'message' => 'OS editada com sucesso!',
                'result' => $os,
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível editar a OS.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_delete($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'dServico')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Apagar OS!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID da OS!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $os = $this->os_model->getByIdCobrancas($id);
        if ($os == null) {
            $os = $this->os_model->getById($id);
            if ($os == null) {
                $this->response([
                    'status' => false,
                    'message' => 'Erro ao tentar excluir OS!',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }

        if (isset($os->idCobranca) != null) {
            if ($os->status == 'canceled') {
                $this->os_model->delete('cobrancas', 'os_id', $id);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Existe uma cobrança associada a esta OS, deve cancelar e/ou excluir a cobrança primeiro!',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }

        $osStockRefund = $this->os_model->getById($id);
        //Verifica para poder fazer a devolução do produto para o estoque caso OS seja excluida.
        if (strtolower($osStockRefund->status) != 'cancelado') {
            $this->devolucaoEstoque($id);
        }

        $this->os_model->delete('servicos_os', 'os_id', $id);
        $this->os_model->delete('produtos_os', 'os_id', $id);
        $this->os_model->delete('anexos', 'os_id', $id);

        if ((int) $os->faturado === 1) {
            $this->os_model->delete('lancamentos', 'descricao', "Fatura de OS - #${id}");
        }

        if ($this->os_model->delete('os', 'idOs', $id)) {
            $this->log_app('Removeu uma OS ID' . $id);
            $this->response([
                'status' => true,
                'message' => 'OS excluída com sucesso!',
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível excluir a OS Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function desconto_post($id)
    {
        if (empty($this->post('desconto', true)) || empty($this->post('valor_desconto', true))) {
            $this->response([
                'status' => false,
                'message' => 'Campos Desconto e Valor com desconto obrigatórios',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'tipo_desconto' => $this->post('tipoDesconto', true) ?: 'real',
            'desconto' => $this->post('desconto', true),
            'valor_desconto' => $this->post('valor_desconto', true),
        ];

        $editavel = $this->isEditable($id);

        if (! $editavel) {
            $this->response([
                'status' => false,
                'message' => 'Desconto não pode ser adiciona. Os não ja Faturada/Cancelada',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if ($this->os_model->edit('os', $data, 'idOs', $id)) {
            $this->log_app('Adicionou um desconto na OS. ID: ' . $id);
            $this->response([
                'status' => true,
                'message' => 'Desconto adicionado com sucesso!',
            ], REST_Controller::HTTP_CREATED);
        }

        $this->response([
            'status' => false,
            'message' => 'Ocorreu um erro ao tentar adicionar desconto à OS.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function produtos_post($id)
    {
        $this->logged_user();

        $_POST = (array) json_decode(trim(file_get_contents('php://input')));
        $_POST['idOsProduto'] = $id;

        $this->load->library('form_validation');

        if ($this->form_validation->run('adicionar_produto_os') === false) {
            $this->response([
                'status' => false,
                'message' => validation_errors(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'produtos_id' => $this->post('idProduto', true),
            'preco' => $this->post('preco', true),
            'quantidade' => $this->post('quantidade', true),
            'subTotal' => $this->post('preco', true) * $this->post('quantidade', true),
            'os_id' => $id,
        ];

        $os = $this->os_model->getById($id);
        if ($os == null) {
            $this->response([
                'status' => false,
                'message' => 'Erro ao tentar inserir produto na OS. OS Não encontrada!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if ($this->os_model->add('produtos_os', $data)) {
            $lastProdutoOs = $this->Api_model->lastRow('produtos_os', 'idProdutos_os');

            $this->load->model('produtos_model');

            $this->produtoEstoque($this->post('idProduto', true), $this->post('quantidade', true), '-');

            $this->db->set('desconto', 0.00);
            $this->db->set('valor_desconto', 0.00);
            $this->db->set('tipo_desconto', null);
            $this->db->where('idOs', $id);
            $this->db->update('os');

            $this->log_app('Adicionou produto a uma OS. ID (OS): ' . $id);

            $result = $lastProdutoOs;
            unset($result->descricao);
            $result->produto = $this->produtos_model->getById($this->post('idProduto', true));

            $this->response([
                'status' => true,
                'message' => 'Produto adicinado com sucesso!',
                'result' => $result,
            ], REST_Controller::HTTP_CREATED);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível adicionar o Produto. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function produtos_put($id, $idProdutos_os)
    {
        $this->logged_user();
        if (! $id || ! $idProdutos_os) {
            $this->response([
                'status' => false,
                'message' => 'Informe a OS e o Produto',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $ddAntigo = $this->Api_model->getRowById('produtos_os', 'idProdutos_os', $idProdutos_os);

        $subTotal = $this->put('preco', true) * $this->put('quantidade', true);

        $data = [
            'quantidade' => $this->put('quantidade', true),
            'preco' => $this->put('preco', true),
            'subTotal' => $subTotal,
        ];

        if ($this->os_model->edit('produtos_os', $data, 'idProdutos_os', $idProdutos_os)) {
            $operacao = $ddAntigo->quantidade > $this->put('quantidade', true) ? '+' : '-';
            $diferenca = $operacao == '+' ? $ddAntigo->quantidade - $this->put('quantidade', true) : $this->put('quantidade', true) - $ddAntigo->quantidade;

            if ($diferenca) {
                $this->produtoEstoque($ddAntigo->produtos_id, $diferenca, $operacao);
            }

            $this->log_app("Atualizou a quantidade do produto id <b>{$ddAntigo->produtos_id}</b> na OS id <b>{$id}</b> de <b>{$ddAntigo->quantidade}</b> para <b>{$this->put('quantidade', true)}</b>");

            $data['idProdutos_os'] = $idProdutos_os;

            $this->response([
                'status' => true,
                'message' => 'Produto da OS editado com sucesso!',
                'result' => $data,
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível editar o Produto da OS. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function produtos_delete($id, $idProdutos_os)
    {
        $this->logged_user();
        $os = $this->os_model->getById($id);
        if ($os == null) {
            $this->response([
                'status' => false,
                'message' => 'Não foi possível excluir o Produto da OS.',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $ddAntigo = $this->Api_model->getRowById('produtos_os', 'idProdutos_os', $idProdutos_os);

        if (! $ddAntigo) {
            $this->response([
                'status' => false,
                'message' => 'Não foi encontrado esse Produto na OS.',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if ($this->os_model->delete('produtos_os', 'idProdutos_os', $idProdutos_os)) {
            $this->produtoEstoque($ddAntigo->produtos_id, $ddAntigo->quantidade, '+');

            $this->db->set('desconto', 0.00);
            $this->db->set('valor_desconto', 0.00);
            $this->db->set('tipo_desconto', null);
            $this->db->where('idOs', $id);
            $this->db->update('os');

            $this->log_app('Removeu produto de uma OS. ID (OS): ' . $id);

            $this->response([
                'status' => true,
                'message' => 'Produto da OS excluído com sucesso!',
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível excluir o Produto da OS. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function servicos_post($id)
    {
        $this->logged_user();
        $_POST = (array) json_decode(trim(file_get_contents('php://input')));
        $_POST['idOsServico'] = $id;

        $this->load->library('form_validation');

        if ($this->form_validation->run('adicionar_servico_os') === false) {
            $this->response([
                'status' => false,
                'message' => validation_errors(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'servicos_id' => $this->post('idServico', true),
            'quantidade' => $this->post('quantidade', true),
            'preco' => $this->post('preco', true),
            'subTotal' => $this->post('preco', true) * $this->post('quantidade', true),
            'os_id' => $id,
        ];

        if ($this->os_model->add('servicos_os', $data)) {
            $lastServicoOs = $this->Api_model->lastRow('servicos_os', 'idServicos_os');

            $this->load->model('servicos_model');

            $this->db->set('desconto', 0.00);
            $this->db->set('valor_desconto', 0.00);
            $this->db->set('tipo_desconto', null);
            $this->db->where('idOs', $id);
            $this->db->update('os');

            $this->log_app('Adicionou serviço a uma OS. ID (OS): ' . $id);

            $result = $lastServicoOs;
            unset($result->servico);
            $result->servico = $this->servicos_model->getById($this->post('idServico', true));

            $this->response([
                'status' => true,
                'message' => 'Serviço adicinado com sucesso!',
                'result' => $result,
            ], REST_Controller::HTTP_CREATED);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível adicionar o Serviço. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function servicos_put($id, $idServicos_os)
    {
        $this->logged_user();
        if (! $id || ! $idServicos_os) {
            $this->response([
                'status' => false,
                'message' => 'Informe a OS e o Serviço',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $ddAntigo = $this->Api_model->getRowById('servicos_os', 'idServicos_os', $idServicos_os);

        $subTotal = $this->put('preco', true) * $this->put('quantidade', true);

        $data = [
            'quantidade' => $this->put('quantidade', true),
            'preco' => $this->put('preco', true),
            'subTotal' => $subTotal,
        ];

        if ($this->os_model->edit('servicos_os', $data, 'idServicos_os', $idServicos_os)) {
            $this->log_app("Atualizou a quantidade do Serviço id <b>{$ddAntigo->servicos_id}</b> na OS id <b>{$id}</b> para <b>{$this->put('quantidade', true)}</b>");

            $data['idServicos_os'] = $idServicos_os;

            $this->response([
                'status' => true,
                'message' => 'Serviço da OS editado com sucesso!',
                'result' => $data,
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível editar o Serviço da OS. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function servicos_delete($id, $idServicos_os)
    {
        $this->logged_user();
        if ($this->os_model->delete('servicos_os', 'idServicos_os', $idServicos_os)) {
            $this->log_app('Removeu Serviço de uma OS. ID (OS): ' . $id);
            $this->CI = &get_instance();
            $this->CI->load->database();
            $this->db->set('desconto', 0.00);
            $this->db->set('valor_desconto', 0.00);
            $this->db->set('tipo_desconto', null);
            $this->db->where('idOs', $id);
            $this->db->update('os');

            $this->response([
                'status' => true,
                'message' => 'Serviço da OS excluído com sucesso!',
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível excluir o Serviço da OS. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function anotacoes_post($id)
    {
        $this->logged_user();

        $_POST = (array) json_decode(trim(file_get_contents('php://input')));
        $_POST['os_id'] = $id;

        $this->load->library('form_validation');

        if ($this->form_validation->run('anotacoes_os') == false) {
            $this->response([
                'status' => false,
                'message' => validation_errors(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'anotacao' => "[{$this->logged_user()->usuario->nome}] " . $this->post('anotacao', true),
            'data_hora' => date('Y-m-d H:i:s'),
            'os_id' => $id,
        ];

        if ($this->os_model->add('anotacoes_os', $data)) {
            $lastAnotacao = $this->Api_model->lastRow('anotacoes_os', 'idAnotacoes');
            $this->log_app('Adicionou anotação a uma OS. ID (OS): ' . $id);

            $result = [
                'idAnotacoes' => $lastAnotacao->idAnotacoes,
                'anotacao' => $this->post('anotacao', true),
            ];

            $this->response([
                'status' => true,
                'message' => 'Anotação adicinada com sucesso!',
                'result' => $result,
            ], REST_Controller::HTTP_CREATED);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível adicionar Anotação. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function anotacoes_delete($id, $idAnotacao)
    {
        $this->logged_user();
        if ($this->os_model->delete('anotacoes_os', 'idAnotacoes', $idAnotacao)) {
            $this->log_app('Removeu anotação de uma OS. ID (OS): ' . $id);

            $this->response([
                'status' => true,
                'message' => 'Anotação excluída com sucesso!',
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível excluir a Anotação. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function anexos_post($id)
    {
        $this->load->library('upload');
        $this->load->library('image_lib');

        $directory = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . date('m-Y') . DIRECTORY_SEPARATOR . 'OS-' . $id;

        // If it exist, check if it's a directory
        if (! is_dir($directory . DIRECTORY_SEPARATOR . 'thumbs')) {
            // make directory for images and thumbs
            try {
                mkdir($directory . DIRECTORY_SEPARATOR . 'thumbs', 0755, true);
            } catch (Exception $e) {
                $this->response([
                    'status' => false,
                    'message' => 'Não foi anexar o arquivo.',
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $upload_conf = [
            'upload_path' => $directory,
            'allowed_types' => 'jpg|png|gif|jpeg|JPG|PNG|GIF|JPEG|pdf|PDF|cdr|CDR|docx|DOCX|txt', // formatos permitidos para anexos de os
            'max_size' => 0,
        ];

        $this->upload->initialize($upload_conf);

        $error = [];
        $success = [];

        foreach ($_FILES as $field_name => $file) {
            if (! $this->upload->do_upload($field_name)) {
                $error['upload'][] = $this->upload->display_errors();
            } else {
                $upload_data = $this->upload->data();

                // Gera um nome de arquivo aleatório mantendo a extensão original
                $new_file_name = uniqid() . '.' . pathinfo($upload_data['file_name'], PATHINFO_EXTENSION);
                $new_file_path = $upload_data['file_path'] . $new_file_name;
                $url = base_url('assets' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . date('m-Y') . DIRECTORY_SEPARATOR . 'OS-' . $id);

                rename($upload_data['full_path'], $new_file_path);

                if ($upload_data['is_image'] == 1) {
                    $resize_conf = [
                        'source_image' => $new_file_path,
                        'new_image' => $upload_data['file_path'] . 'thumbs' . DIRECTORY_SEPARATOR . 'thumb_' . $new_file_name,
                        'width' => 200,
                        'height' => 125,
                    ];

                    $this->image_lib->initialize($resize_conf);

                    if (! $this->image_lib->resize()) {
                        $error['resize'][] = $this->image_lib->display_errors();
                    } else {
                        $success[] = $upload_data;
                        $result = $this->os_model->anexar($id, $new_file_name, $url, 'thumb_' . $new_file_name, $directory);
                        if (! $result) {
                            $error['db'][] = 'Erro ao inserir no banco de dados.';
                        }
                    }
                } else {
                    $success[] = $upload_data;

                    $result = $this->os_model->anexar($id, $new_file_name, $url, '', $directory);
                    if (! $result) {
                        $error['db'][] = 'Erro ao inserir no banco de dados.';
                    }
                }
            }
        }

        if (count($error) > 0) {
            $this->response([
                'status' => false,
                'message' => 'Ocorreu um erro ao processar o arquivo.',
                'result' => $error,
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $anexo = $this->Api_model->lastRow('anexos', 'idAnexos');

        $retorno = [
            'idAnexos' => $anexo->idAnexos,
            'url' => $url,
            'anexo' => $new_file_name,
            'thumb' => 'thumb_' . $new_file_name,
        ];

        $this->log_app('Adicionou anexo(s) a uma OS. ID (OS): ' . $id);
        $this->response([
            'status' => true,
            'message' => 'Arquivo anexado com sucesso!',
            'result' => $retorno,
        ], REST_Controller::HTTP_CREATED);
    }

    public function anexos_delete($id, $idAnexo)
    {
        $this->logged_user();
        if ($idAnexo != null && is_numeric($idAnexo)) {
            $this->db->where('idAnexos', $idAnexo);
            $file = $this->db->get('anexos', 1)->row();

            if ($file->os_id == $id) {
                unlink($file->path . DIRECTORY_SEPARATOR . $file->anexo);

                if ($file->thumb != null) {
                    unlink($file->path . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $file->thumb);
                }

                if ($this->os_model->delete('anexos', 'idAnexos', $idAnexo)) {
                    $this->log_app('Removeu anexo de uma OS. ID (OS): ' . $id);

                    $this->response([
                        'status' => true,
                        'message' => 'Anexo excluído com sucesso!',
                    ], REST_Controller::HTTP_OK);
                }
            }
        }

        $this->response([
            'status' => false,
            'message' => 'Erro ao tentar excluir anexo.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function calcTotal($id)
    {
        $ordem = $this->os_model->getById($id);
        $produtos = $this->os_model->getProdutos($ordem->idOs);
        $servicos = $this->os_model->getServicos($ordem->idOs);

        $totalProdutos = 0;
        $totalServicos = 0;

        foreach ($produtos as $p) {
            $totalProdutos = $totalProdutos + $p->subTotal;
        }

        foreach ($servicos as $s) {
            $preco = $s->preco ?: $s->precoVenda;
            $subtotal = $preco * ($s->quantidade ?: 1);
            $totalServicos = $totalServicos + $subtotal;
        }

        if ($totalProdutos != 0 || $totalServicos != 0) {
            return number_format(($ordem->valor_desconto != 0 ? $ordem->valor_desconto : ($totalProdutos + $totalServicos)), 2, '.');
        }

        return 0;
    }

    private function produtoEstoque($produtosId, $quantidade, $operacao)
    {
        $this->load->model('produtos_model');

        if ($this->getConfig('control_estoque')) {
            $this->produtos_model->updateEstoque($produtosId, $quantidade, $operacao);
        }
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
                    'headers' => serialize($headers),
                ];
                $this->email_model->add('email_queue', $email);
            } else {
                $this->log_app('Email não adicionado a Lista de envio de e-mails. Verifique se o remetente esta cadastrado. OS ID: ' . $idOs);
            }
        }

        return true;
    }

    private function devolucaoEstoque($id)
    {
        if ($produtos = $this->os_model->getProdutos($id)) {
            $this->load->model('produtos_model');
            if ($this->getConfig('control_estoque')) {
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
            if ($this->getConfig('control_estoque')) {
                foreach ($produtos as $p) {
                    $this->produtos_model->updateEstoque($p->produtos_id, $p->quantidade, '-');
                    log_info('ESTOQUE: Produto id ' . $p->produtos_id . ' baixa do estoque. Quantidade: ' . $p->quantidade . '. Motivo: Mudou status que já estava Cancelado para outro');
                }
            }
        }
    }

    public function criarTextoWhats($os, $totalProdutos, $totalServico)
    {
        $this->load->model('mapos_model');
        $emitente = $this->mapos_model->getEmitente();

        $troca = [
            $os->nomeCliente,
            $os->idOs,
            $os->status,
            'R$ ' . ($os->desconto != 0 && $os->valor_desconto != 0 ? number_format($os->valor_desconto, 2, ',', '.') : number_format($totalProdutos + $totalServico, 2, ',', '.')),
            strip_tags($os->descricaoProduto),
            ($emitente ? $emitente->nome : ''),
            ($emitente ? $emitente->telefone : ''),
            strip_tags($os->observacoes),
            strip_tags($os->defeito),
            strip_tags($os->laudoTecnico),
            date('d/m/Y',
                strtotime($os->dataFinal)),
            date('d/m/Y',
                strtotime($os->dataInicial)),
            $os->garantia . ' dias',
        ];

        $textoBase = $this->getConfig('notifica_whats');

        $procura = ['{CLIENTE_NOME}', '{NUMERO_OS}', '{STATUS_OS}', '{VALOR_OS}', '{DESCRI_PRODUTOS}', '{EMITENTE}', '{TELEFONE_EMITENTE}', '{OBS_OS}', '{DEFEITO_OS}', '{LAUDO_OS}', '{DATA_FINAL}', '{DATA_INICIAL}', '{DATA_GARANTIA}'];
        $textoFinal = str_replace($procura, $troca, $textoBase);
        $textoFinal = strip_tags($textoFinal);

        return $textoFinal;
    }

    public function isEditable($id = null)
    {
        if (! $this->permission->checkPermission($this->logged_user()->level, 'eOs')) {
            return false;
        }

        if ($os = $this->os_model->getById($id)) {
            $osT = (int) ($os->status === 'Faturado' || $os->status === 'Cancelado' || $os->faturado == 1);
            if ($osT) {
                return $this->getConfig('control_editos') == '1';
            }
        }

        return true;
    }
}
