<?php
// defined('BASEPATH') OR exit('No direct script access allowed');

class Assinatura extends MY_Controller
{
    public function upload_signature()
    {
        // Carrega a Model de OS
        $this->load->model('os_model');
        $this->load->model('usuarios_model');
        // Carrega a biblioteca de validação
        $this->load->library('form_validation');

        // Define as validações
        $this->form_validation->set_rules('idOs', 'idOs', 'required');
        $this->form_validation->set_rules('assClienteImg', 'valid_base64');
        $this->form_validation->set_rules('assTecnicoImg', 'valid_base64');

        // Define o padrão da resposta
        $response = [
            'code'    => '400',
            'success' => false
        ];

        // Executa a validação dos dados recebidos. Se a validação falhar (false) retorna erro 400
        if ($this->form_validation->run() == false) {
            $response['message'] = 'Erro: Corrija os dados das assinaturas e tente novamente.';
            $this->response_signature($response);
        }

        // Seta as variáveis que precisaremos
        $idOs          = $this->input->post('idOs'); //ID da OS
        $signaturesDir = realpath(APPPATH . '../') . '/assets/assinaturas/'; // Caminho onde as assinaturas serão salvas
        
        // Cria o diretório caso não exista
        if (!is_dir($signaturesDir)) {
            mkdir($signaturesDir, 0777, true);
        }

        if($this->input->post('inserirAssCli')) {
            if(!empty($this->input->post('assClienteImg'))){
                $assClienteImg = preg_replace('#^data:image/[^;]+;base64,#', '', $this->input->post('assClienteImg')); // Imagem da assinatura do cliente
                $assClienteImgName = $idOs . '_' . date('YmdHis') . '_' . rand(1000,9999) . '.png'; // Define o nome da imagem de assinatura do cliente "IDdaOS_DataNoFormatoAnomêsdiahoraminutosegundo_NumeroAleatorioDe4Digitos.png"
                $assClientePatch   = $signaturesDir . $assClienteImgName;

                // Se conseguir salvar a assinatura do cliente na pasta, coloca o nome final do arquivo na variável
                if(file_put_contents($assClientePatch, base64_decode($assClienteImg))) {
                    $data['assClienteImg']  = $assClienteImgName;
                    $data['assClienteIp']   = $this->input->ip_address(); // Pega o IP de quem enviou a imagem
                    $data['assClienteData'] = date('Y-m-d H:i:s'); // Pega a data de quando enviou
                    
                    $this->CI = &get_instance();
                    $this->CI->load->database();
                    $data['status'] = $this->CI->db->get_where('configuracoes', ['config' => 'status_assinatura'])->row_object()->valor;
                } else {
                    $response['message'] = 'Erro: Falha ao salvar assinatura do cliente.';
                    $this->response_signature($response);
                }
            } else {
                $response['message'] = 'Erro: Falha ao salvar assinatura do técnico.';
                $this->response_signature($response);
            }
        }

        if($this->input->post('inserirAssTec')) {
            $assinaturaImg = $this->session->userdata('assinatura');
            
            if($assinaturaImg) {
                $data['assTecnicoImg']  = $assinaturaImg;
                $data['assTecnicoIp']   = $this->input->ip_address(); // Pega o IP de quem enviou a imagem
                $data['assTecnicoData'] = date('Y-m-d H:i:s'); // Pega a data de quando enviou
            } elseif(!empty($this->input->post('assTecnicoImg'))) {
                // Cria o diretório caso não exista
                if (!is_dir($signaturesDir.'/tecnicos/',)) {
                    mkdir($signaturesDir.'/tecnicos/', 0777, true);
                }
                $assTecnicoImg = preg_replace('#^data:image/[^;]+;base64,#', '', $this->input->post('assTecnicoImg')); // Imagem da assinatura do técnico
                $assTecnicoImgName = $this->session->userdata('id_admin') . '_' . date('YmdHis') . '.png'; // Define o nome da imagem de assinatura do técnico "IDdaOS_DataNoFormatoAnomêsdiahoraminutosegundo_NumeroAleatorioDe4Digitos.png"
                $assTecnicoPatch   = $signaturesDir . '/tecnicos/' . $assTecnicoImgName;
            
                // Se conseguir salvar a assinatura do técnico na pasta, coloca o nome final do arquivo na variável
                if(file_put_contents($assTecnicoPatch, base64_decode($assTecnicoImg))) {
                    $this->usuarios_model->edit('usuarios', ['assinaturaImg' => $assTecnicoImgName], 'idUsuarios', $this->session->userdata('id_admin'));
                    $session_admin_data = ['nome_admin' => $this->session->userdata('nome_admin'), 'email_admin' => $this->session->userdata('email_admin'), 'url_image_user_admin' => $this->session->userdata('url_image_user_admin'), 'id_admin' => $this->session->userdata('id_admin'), 'permissao' => $this->session->userdata('permissao'), 'logado' => true, 'assinatura' => $assTecnicoImgName];
                    $this->session->set_userdata($session_admin_data);
                    $data['assTecnicoImg']  = $assTecnicoImgName;
                    $data['assTecnicoIp']   = $this->input->ip_address(); // Pega o IP de quem enviou a imagem
                    $data['assTecnicoData'] = date('Y-m-d H:i:s'); // Pega a data de quando enviou
                } else {
                    $response['message'] = 'Erro: Falha ao salvar assinatura do técnico.';
                    $this->response_signature($response);
                }
            } else {
                $response['message'] = 'Erro: Falha ao salvar assinatura do técnico.';
                $this->response_signature($response);
            }
        }

        // Se conseguir persistir os dados no banco, responde com sucesso
        if ($this->os_model->edit('os', $data, 'idOs', $this->input->post('idOs')) == true) {
            // Se chegou até aqui, a assinatura foi salva com sucesso
            $response = [
                'code'    => '200',
                'success' => true,
                'message' => 'Assinatura(s) salva(s) com sucesso.'
            ];
            
            if(array_key_exists('assClienteImg', $data)) {
                $response['assClienteImg']  = $assClienteImgName;
                $response['assClienteIp']   = $data['assClienteIp'];
                $response['assClienteData'] = date('d/m/Y H:i:s', strtotime($data['assClienteData']));
            }
            if(array_key_exists('assTecnicoImg', $data)) {
                $response['assTecnicoImg']  = $data['assTecnicoImg'];
                $response['assTecnicoIp']   = $data['assTecnicoIp'];
                $response['assTecnicoData'] = date('d/m/Y H:i:s', strtotime($data['assTecnicoData']));
            }
        }else{
            $response['message'] = 'Erro: Falha ao salvar os dados da assinatura.';
        }
        
        $this->response_signature($response);
    }

    protected function response_signature($response)
    {
        return $this->output
        ->set_status_header($response['code'])
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}