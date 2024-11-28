<?php

/*
    Ivan Sarkozin
    https://github.com/sarkozin
*/

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class ClientComprasController extends REST_Controller
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

    public function index_get(int $idVendas = null)
    {
        $clientLogged = $this->logged_client();

        if ($idVendas == '') {
            $config['per_page'] = $this->CI->db->get_where('configuracoes', ['config' => 'per_page'])->row_object()->valor;
            $this->pagination->initialize($config);

            $compras = $this->Conecte_model->getCompras('vendas', '*', '', $config['per_page'], $this->uri->segment(3), '', '', $this->logged_client()->usuario->idClientes);
            if (empty($compras)) {
                $this->response([
                    'status' => false,
                    'message' => 'Nenhuma compra encontrada'
                ], REST_Controller::HTTP_NOT_FOUND);
            }

            foreach ($compras as $compra) {
                unset($compra->senha);
            }
            
            $this->response([
                'status' => true,
                'message' => 'Listando resultados',
                'result' => [
                    'Compras' => $compras,
                ],
            ], REST_Controller::HTTP_OK);
        }

        $pixKey = $this->CI->db->get_where('configuracoes', ['config' => 'pix_key'])->row_object()->valor;
        $emitente = $this->mapos_model->getEmitente();
        $qrCode = $this->os_model->getQrCode(
            $idVendas,
            $pixKey,
            $emitente
        );
        $chaveFormatada = $this->format->formatarChave($pixKey);
        
        $this->load->model('vendas_model');
        $result = $this->vendas_model->getById($this->uri->segment(5));
        if (empty($result)) {
            $this->response([
                'status' => false,
                'message' => 'Compra Não encontrada'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $produtos = $this->vendas_model->getProdutos($this->uri->segment(5));
        unset(
            $result->senha,
            $result->email,
            $result->telefone,
            $result->celular,
            $result->dataCadastro,
            $result->usuarios_id
        );
        $this->response([
        'status' => true,
        'message' => 'Listando resultados',
        'result' => [
            'Compras' => $result,
            'Produtos' => $produtos,
            'QrCode' => $qrCode,
            'ChaveFormatada' => $chaveFormatada,
            ],
        ], REST_Controller::HTTP_OK);
    }
}
