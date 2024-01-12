<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assinatura extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('os_model');
        $this->load->model('usuarios_model');
        $this->load->model('clientes_model');
    }

    public function upload_signature()
    {
        $this->load->library('form_validation');

        if(!$this->input->post('idUsuario')) {
            $this->form_validation->set_rules('idOs', 'idOs', 'required');
            $this->form_validation->set_rules('nomeAssinatura', 'nomeAssinatura');
            $this->form_validation->set_rules('assClienteImg', 'valid_base64');
            $this->form_validation->set_rules('assTecnicoImg', 'valid_base64');

            $response = [
                'code'    => '400',
                'success' => false
            ];

            if ($this->form_validation->run() == false) {
                $response['message'] = 'Erro: Corrija os dados das assinaturas e tente novamente.';
                $this->responseSignature($response);
            }

            $assClienteImg64 = $this->input->post('assClienteImg');
            $assTecnicoImg64 = $this->input->post('assTecnicoImg');
            $data            = [];

            if($this->input->post('inserirAssCli') && !empty($this->input->post('assClienteImg'))) {
                $data = $this->assCliente($assClienteImg64, $data, $this->input->post('idOs'));
            }

            if($this->input->post('inserirAssTec')) {
                $data = $this->assTecnico($assTecnicoImg64, $data, $this->input->post('idOs'));
            }

            if ($this->os_model->edit('os', $data, 'idOs', $this->input->post('idOs')) == true) {
                $response = [
                    'code'    => '200',
                    'success' => true,
                    'message' => 'Assinatura(s) salva(s) com sucesso.'
                ];
                
                if(array_key_exists('assClienteImg', $data)) {
                    $response['assClienteImg']  = $data['assClienteImg'];
                    $response['assClienteIp']   = $data['assClienteIp'];
                    $response['assClienteData'] = date('d/m/Y H:i:s', strtotime($data['assClienteData']));
                }
                if(array_key_exists('assTecnicoImg', $data)) {
                    $response['assTecnicoImg']  = $data['assTecnicoImg'];
                    $response['assTecnicoIp']   = $data['assTecnicoIp'];
                    $response['assTecnicoData'] = date('d/m/Y H:i:s', strtotime($data['assTecnicoData']));
                }

                $this->responseSignature($response);
            }
        }

        $this->form_validation->set_rules('idUsuario', 'idUsuario', 'required');
        $this->form_validation->set_rules('assUsuarioImg', 'valid_base64');

        if($this->assUsuario($this->input->post('assUsuarioImg'), $this->input->post('idUsuario'))) {
            $response = [
                'code'    => '200',
                'success' => true,
                'message' => 'Assinatura salva com sucesso.'
            ];
            $this->responseSignature($response);
        }
        
        $response['message'] = 'Erro: Falha ao salvar os dados da assinatura.';
    }

    protected function assCliente($assClienteImg64, $data, $idOs)
    {
        $idCliente    = $this->os_model->getById($idOs)->clientes_id;
        $dadosCliente = $this->clientes_model->getById($idCliente);

        $data['assClienteImg']  = $assClienteImg64;
        $data['assClienteIp']   = $this->input->ip_address();
        $data['assClienteData'] = date('Y-m-d H:i:s');
        
        $this->CI = &get_instance();
        $this->CI->load->database();
        $data['status'] = $this->CI->db->get_where('configuracoes', ['config' => 'status_assinatura'])->row_object()->valor;

        log_info("[Ass. Digital] OS ID <b>{$idOs}</b> assinada por <b>{$this->input->post('nomeAssinatura')}</b>");
        $anotacao = [
            'anotacao' => "[Ass. Digital] OS assinada por <b>{$this->input->post('nomeAssinatura')}</b> por meio do IP (ip) <b>{$data['assClienteIp']}</b>",
            'data_hora' => date('Y-m-d H:i:s'),
            'os_id' => $idOs,
        ];
        $this->os_model->add('anotacoes_os', $anotacao);
        
        return $data;
    }

    protected function assTecnico($assTecnicoImg64, $data, $idOs)
    {
        $dadosDoTecnico = $this->usuarios_model->getById($this->session->userdata('id_admin'));
            
        if($dadosDoTecnico->assinaturaImg) {
            $data['assTecnicoImg']  = $dadosDoTecnico->assinaturaImg;
            $data['assTecnicoIp']   = $this->input->ip_address();
            $data['assTecnicoData'] = date('Y-m-d H:i:s');

            log_info("[Ass. Digital] OS ID <b>{$idOs}</b> assinada por <b>".mb_substr($dadosDoTecnico->nome, 0, 18)."</b>.");
            $anotacao = [
                'anotacao' => "[Ass. Digital] OS assinada por <b>{$dadosDoTecnico->nome}</b> por meio do IP <b>{$data['assTecnicoIp']}</b>.",
                'data_hora' => date('Y-m-d H:i:s'),
                'os_id' => $idOs,
            ];
            $this->os_model->add('anotacoes_os', $anotacao);

            return $data;
        }

        if($this->usuarios_model->edit('usuarios', ['assinaturaImg' => $assTecnicoImg64], 'idUsuarios', $this->session->userdata('id_admin'))) {
            $data['assTecnicoImg']  = $assTecnicoImg64;
            $data['assTecnicoIp']   = $this->input->ip_address();
            $data['assTecnicoData'] = date('Y-m-d H:i:s');

            log_info("[Ass. Digital] OS ID <b>{$idOs}</b> assinada por <b>".mb_substr($dadosDoTecnico->nome, 0, 18)."</b>.");
            $anotacao = [
                'anotacao' => "[Ass. Digital] OS assinada por <b>{$dadosDoTecnico->nome}</b> por meio do IP <b>{$data['assTecnicoIp']}</b>.",
                'data_hora' => date('Y-m-d H:i:s'),
                'os_id' => $idOs,
            ];
            $this->os_model->add('anotacoes_os', $anotacao);

            return $data;
        }

        $response['message'] = 'Erro: Falha ao salvar assinatura do técnico.';
        $this->responseSignature($response);
    }

    protected function assUsuario($assUsuarioImg64, $idUsuario)
    {
        $dadosDoUsuario = $this->usuarios_model->getById($idUsuario);
        $dadosDoLogado  = $this->usuarios_model->getById($this->session->userdata('id_admin'));

        if($this->usuarios_model->edit('usuarios', ['assinaturaImg' => $assUsuarioImg64], 'idUsuarios', $idUsuario)) {
            log_info("[Ass. Digital] O usuário <b>(ID {$idUsuario}) {$dadosDoUsuario->nome}</b> salvou uma nova assinatura.");
            
            return true;
        }

        $response['message'] = 'Erro: Falha ao salvar assinatura do técnico.';
        $this->responseSignature($response);
    }

    protected function responseSignature($response)
    {
        return $this->output
        ->set_status_header($response['code'])
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function excluirAssinaturaUsuario()
    {
        $idUsuario = $this->input->post('idUsuario');
        if($this->usuarios_model->edit('usuarios', ['assinaturaImg' => null], 'idUsuarios', $idUsuario)) {
            $dadosDoUsuario = $this->usuarios_model->getById($idUsuario);
            $dadosDoLogado  = $this->usuarios_model->getById($this->session->userdata('id_admin'));
            log_info("[Ass. Digital] O usuário <b>(ID {$idUsuario}) {$dadosDoUsuario->nome}</b> excluiu sua assinatura.</b>");
            $this->session->set_flashdata('success', 'OS excluída com sucesso!');
            redirect(site_url('mapos/minhaConta/'));
        }

        $this->session->set_flashdata('error', 'Não foi possível excluir a assinatura!');
        redirect(site_url('mapos/minhaConta/'));
    }
}