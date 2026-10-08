<?php

/*
    Ivan Sarkozin
    https://github.com/sarkozin
*/

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class ClientLoginController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Conecte_model');
        $this->load->library('Authorization_Token');
    }

    public function login_post()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        $_POST = (array) json_decode(file_get_contents('php://input'), true);
        $email = $this->input->post('email', true);
        $password = $this->input->post('password', true);

        $ip = $this->input->ip_address();

        // Limite de tentativas (#2870), no mesmo escopo da área do cliente.
        $this->load->library('Limite_login');
        $restantes = $this->limite_login->segundosRestantes('cliente', $email, $ip);
        if ($restantes > 0) {
            header('Retry-After: ' . $restantes);
            $this->response([
                'status' => false,
                'message' => Limite_login::MENSAGEM,
            ], REST_Controller::HTTP_TOO_MANY_REQUESTS);

            return;
        }

        $cliente = $this->check_credentials($email);

        // Mesma resposta para e-mail inexistente e senha errada: "Usuário não
        // encontrado" revelava quais e-mails possuem cadastro.
        if (!$cliente || !password_verify((string) $password, $cliente->senha)) {
            $this->limite_login->registrarFalha('cliente', $email, $ip);
            $this->response([
                'status' => false,
                'message' => 'Os dados de acesso estão incorretos.'
                ], REST_Controller::HTTP_UNAUTHORIZED);

            return;
        }

        $this->limite_login->registrarSucesso('cliente', $email);

        $tokenData = [
            'uid' => $cliente->idClientes,
            'nome' => $cliente->nomeCliente,
            'email' => $cliente->email,
            'type' => REST_Controller::TOKEN_TYPE_CLIENT,
        ];

        $result = [
            'access_token' => $this->authorization_token->generateToken($tokenData),
        ];

        $this->load->model('Audit_model');
        $log_data = [
            'usuario' => $cliente->nomeCliente,
            'tarefa' => 'Cliente ' . $cliente->nomeCliente . ' efetuou login pelo APP',
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'ip' => $ip
        ];
        $this->Audit_model->add($log_data);

        $this->response([
            'status' => true,
            'message' => 'Login bem-sucedido',
            'result' => $result
        ], REST_Controller::HTTP_OK);
    }

    private function check_credentials($email)
    {
        $this->db->where('email', $email);
        $this->db->limit(1);

        return $this->db->get('clientes')->row();
    }
}
