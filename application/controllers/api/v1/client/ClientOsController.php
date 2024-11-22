<?php

/*
    Ivan Sarkozin
    https://github.com/sarkozin
*/

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';


class ClientOsController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Conecte_model');
        $this->load->library('Authorization_Token');
        $this->load->library('pagination');
        $this->load->library('format');
        $this->load->model('mapos_model');
        $this->load->model('os_model');
    }

    public function Index_get()
    {
        $clientLogged = $this->logged_client();
        
        $data['os'] = $this->Conecte_model->getLastOs($this->logged_client()->usuario->idClientes);
        $data['compras'] = $this->Conecte_model->getLastCompras($this->logged_client()->usuario->idClientes);
        
        if(empty($data['os'])) {
            $this->response([
                'status' => true,
                'message' => 'Nenhuma ordem de serviço encontrada'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => true,
                'message' => 'Listando resultados',
                'result' => [
                    'Os' => $data['os'],
                    'compras' => $data['compras']
                ],
            ], REST_Controller::HTTP_OK);
        }
    }

    public function os_get(int $id = null)
    {
        $clientLogged = $this->logged_client();
        if (! $id) {
            $config['per_page'] = $this->CI->db->get_where('configuracoes', ['config' => 'per_page'])->row_object()->valor;
            $this->pagination->initialize($config);
            $data['results'] = $this->Conecte_model->getOs('os', '*', '', $config['per_page'], $this->uri->segment(3), '', '', $this->logged_client()->usuario->idClientes);
        
            $this->response([
                'status' => true,
                'message' => 'Listando resultados',
                'result' => [
                    'Os' => $data['results'],
                ],
            ], REST_Controller::HTTP_OK);
        }
        
        $data['pix_key'] = $this->CI->db->get_where('configuracoes', ['config' => 'pix_key'])->row_object()->valor;
        $data['result'] = $this->os_model->getById($this->uri->segment(5));
        if (empty($data['result'])) {
            $this->response([
                'status' => false,
                'message' => 'Ordem de serviço encontrada'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $os = $this->os_model->getById($this->uri->segment(5));
        $produtos = $this->os_model->getProdutos($this->uri->segment(5));
        $servicos = $this->os_model->getServicos($this->uri->segment(5));
        $anexos = $this->os_model->getAnexos($this->uri->segment(5));
        $emitente = $this->mapos_model->getEmitente();
        $qrCode = $this->os_model->getQrCode(
            $id,
            $data['pix_key'],
            $emitente
        );
        $chaveFormatada = $this->format->formatarChave($data['pix_key']);
        foreach ($produtos as $produto) {
            unset(
                $produto->precoCompra,
                $produto->estoque,
                $produto->estoqueMinimo,
                $produto->saida,
                $produto->entrada
            );
        }

        foreach ($anexos as $anexo){
            unset($anexo->path);
        }

        unset($os->senha);

        $data = [
            'pix_key' => $data['pix_key'],
            'os' => $os,
            'produtos' => $produtos,
            'servicos' => $servicos,
            'anexos' => $anexos,
            'emitente' => $emitente,
            'qrCode' => $qrCode,
            'chaveFormatada' => $chaveFormatada,
        ];
        
        $this->response([
            'status' => true,
            'message' => 'Listando resultados',
            'result' => $data,
        ], REST_Controller::HTTP_OK);
    }

    public function os_post()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        $_POST = (array) json_decode(file_get_contents('php://input'), true);

        $clientLogged = $this->logged_client();

        $this->load->library('form_validation');
        $this->form_validation->set_rules('descricaoProduto', 'Descrição', 'required');
        $this->form_validation->set_rules('defeito', 'Defeito');
        $this->form_validation->set_rules('observacoes', 'Observações');
    
        if ($this->form_validation->run() === false) {
            $this->response([
                'status' => false,
                'message' => validation_errors() ?: 'Erro de validação.',
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
    
        $usuario = $this->db->query('SELECT usuarios_id, count(*) as down FROM os GROUP BY usuarios_id ORDER BY down LIMIT 1')->row();

        if ($usuario == null) {
            $this->db->where('situacao', 1);
            $this->db->limit(1);
            $usuario = $this->db->get('usuarios')->row();
    
            if ($usuario->idUsuarios == null) {
                $this->response([
                    'status' => false,
                    'message' => 'Ocorreu um erro ao atribuir a ordem de serviço. Por favor, contate o administrador do sistema.',
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                return;
            }
        }

        $usuarioId = $usuario->usuarios_id ?? $usuario->idUsuarios;

        $data = [
            'dataInicial' => date('Y-m-d'),
            'clientes_id' => $this->logged_client()->usuario->idClientes,
            'usuarios_id' => $usuarioId,
            'dataFinal' => date('Y-m-d'),
            'descricaoProduto' => $this->security->xss_clean($this->input->post('descricaoProduto')),
            'defeito' => $this->security->xss_clean($this->input->post('defeito')),
            'status' => 'Aberto',
            'observacoes' => $this->security->xss_clean($this->input->post('observacoes')),
            'faturado' => 0,
        ];

        $osId = $this->Conecte_model->add('os', $data, true);

        $this->load->model('Audit_model');
        $log_data = [
            'Usuario' => '[APP]',
            'tarefa' => 'Cliente ' . $this->logged_client()->usuario->nomeCliente . " Adicionou uma OS Nº ${osId} pelo APP",
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        $this->Audit_model->add($log_data);
            
        if (is_numeric($osId)) {
            $this->response([
                'status' => true,
                'message' => 'Ordem de serviço criada com sucesso.',
                'os_id' => $osId,
            ], REST_Controller::HTTP_CREATED);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Ocorreu um erro ao criar a ordem de serviço.',
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
