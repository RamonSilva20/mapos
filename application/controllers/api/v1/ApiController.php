<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class ApiController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('Authorization_Token');
        $this->load->model('mapos_model');
    }

    public function index_get()
    {
        $user = $this->logged_user();

        $result = [
            'countOs' => $this->mapos_model->count('os'),
            'clientes' => $this->mapos_model->count('clientes'),
            'produtos' => $this->mapos_model->count('produtos'),
            'servicos' => $this->mapos_model->count('servicos'),
            'garantias' => $this->mapos_model->count('garantias'),
            'vendas' => $this->mapos_model->count('vendas'),
        ];

        if ($this->permission->checkPermission($this->logged_user()->level, 'vOs')) {
            $result['osAbertas'] = $this->mapos_model->getOsAbertas();
            $result['osAndamento'] = $this->mapos_model->getOsAndamento();
            $result['estoqueBaixo'] = $this->mapos_model->getProdutosMinimo();
        }

        $this->response([
            'status' => true,
            'message' => 'Dashboard',
            'result' => $result,
        ], REST_Controller::HTTP_OK);
    }

    public function calendario_get()
    {
        if (! $this->permission->checkPermission($this->logged_user()->level, 'vOs')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Visualizar OSs',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $this->load->model('os_model');
        $status = $this->get('status', true) ?: null;
        $start = $this->get('start', true) ?: date('Y-m-01');
        $end = $this->get('end', true) ?: date('Y-m-t');

        $allOs = $this->mapos_model->calendario($start, $end, $status);

        $events = array_map(function ($os) {
            switch ($os->status) {
                case 'Aberto':
                    $cor = '#00cd00';
                    break;
                case 'Negociação':
                    $cor = '#AEB404';
                    break;
                case 'Em Andamento':
                    $cor = '#436eee';
                    break;
                case 'Orçamento':
                    $cor = '#CDB380';
                    break;
                case 'Cancelado':
                    $cor = '#CD0000';
                    break;
                case 'Finalizado':
                    $cor = '#256';
                    break;
                case 'Faturado':
                    $cor = '#B266FF';
                    break;
                case 'Aguardando Peças':
                    $cor = '#FF7F00';
                    break;
                default:
                    $cor = '#E0E4CC';
                    break;
            }

            return [
                'title' => "OS: {$os->idOs}, Cliente: {$os->nomeCliente}",
                'start' => $os->dataFinal,
                'end' => $os->dataFinal,
                'color' => $cor,
                'extendedProps' => [
                    'id' => $os->idOs,
                    'cliente' => '<b>Cliente:</b> ' . $os->nomeCliente,
                    'dataInicial' => '<b>Data Inicial:</b> ' . date('d/m/Y', strtotime($os->dataInicial)),
                    'dataFinal' => '<b>Data Final:</b> ' . date('d/m/Y', strtotime($os->dataFinal)),
                    'garantia' => '<b>Garantia:</b> ' . $os->garantia . ' dias',
                    'status' => '<b>Status da OS:</b> ' . $os->status,
                    'description' => '<b>Descrição/Produto:</b> ' . strip_tags(html_entity_decode($os->descricaoProduto)),
                    'defeito' => '<b>Defeito:</b> ' . strip_tags(html_entity_decode($os->defeito)),
                    'observacoes' => '<b>Observações:</b> ' . strip_tags(html_entity_decode($os->observacoes)),
                    'total' => '<b>Valor Total:</b> R$ ' . number_format($os->totalProdutos + $os->totalServicos, 2, ',', '.'),
                    'desconto' => '<b>Desconto: </b>R$ ' . number_format($this->desconto(floatval($os->valorTotal), floatval($os->desconto), strval($os->tipo_desconto)), 2, ',', '.'),
                    'valorFaturado' => '<b>Valor Faturado:</b> ' . ($os->faturado ? 'R$ ' . number_format($os->valorTotal - $this->desconto(floatval($os->valorTotal), floatval($os->desconto), strval($os->tipo_desconto)), 2, ',', '.') : 'PENDENTE'),
                    'editar' => $this->os_model->isEditable($os->idOs),
                ],
            ];
        }, $allOs);

        $result = [
            'start' => $start,
            'end' => $end,
            'allOs' => $allOs,
        ];

        $this->response([
            'status' => true,
            'message' => 'OSs do período.',
            'result' => $result,
        ], REST_Controller::HTTP_OK);
    }

    public function emitente_get()
    {
        $this->logged_user();

        $result = [
            'appName' => $this->getConfig('app_name'),
            'emitente' => $this->mapos_model->getEmitente() ?: false,
        ];

        $this->response([
            'status' => true,
            'message' => 'Dados do Map-OS',
            'result' => $result,
        ], REST_Controller::HTTP_OK);
    }

    public function audit_get()
    {
        $this->logged_user();

        if (! $this->permission->checkPermission($this->logged_user()->level, 'cAuditoria')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Visualizar Auditoria',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $perPage = $this->get('perPage', true) ?: 20;
        $page = $this->get('page', true) ?: 0;
        $start = $page ? ($perPage * $page) : 0;

        $this->load->model('Audit_model');
        $logs = $this->Audit_model->get('logs', '*', '', $perPage, $start);

        $this->response([
            'status' => true,
            'message' => 'Listando Logs',
            'result' => $logs,
        ], REST_Controller::HTTP_OK);
    }

    private function desconto(float $valorTotal, float $desconto, string $tipoDesconto)
    {
        return $tipoDesconto === 'porcento'
            ? $valorTotal * ($desconto / 100)
            : $desconto;
    }
}
