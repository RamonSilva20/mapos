<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assinatura extends CI_Controller {

    public function upload_signature()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('nOs', 'nOs', 'required');
        $this->form_validation->set_rules('imageData', 'imageData', 'required');
        $this->form_validation->set_rules('imageData2', 'imageData2', 'required');
        $this->form_validation->set_rules('clientName', 'clientName', 'required');
        $this->form_validation->set_rules('tecnico', 'tecnico', 'required');

        if (!$this->form_validation->run()) {
            http_response_code(400); // Define o código de status HTTP para 400 Bad Request
            
            $response = array(
                'success' => false,
                'message' => 'Erro: Dados de assinatura ausentes.'
            );
            echo json_encode($response);
            return;
        }

        $nOs = $this->input->post('nOs');    
        $imageData = $this->input->post('imageData');
        $imageData2 = $this->input->post('imageData2');
        $clientName = $this->input->post('clientName');
        $tecnico = $this->input->post('tecnico');

        // Retire os metadados dos dados de imagem codificados em base64
        $imageData = preg_replace('#^data:image/[^;]+;base64,#', '', $imageData);
        $imageData2 = preg_replace('#^data:image/[^;]+;base64,#', '', $imageData2);

        // Obter o caminho raiz do projeto
        $projectRoot = realpath(APPPATH . '../');

        // Define o direotiro aonde será salvo a assinatura
        $signaturesDir = $projectRoot . '/assets/signatures/';

        // Cria o diretorio se não existir
        if (!is_dir($signaturesDir)) {
            mkdir($signaturesDir, 0777, true);
        }

        // Define o caminho do arquivo para a assinatura do cliente
        $filePath = $signaturesDir . $nOs . $clientName . '.png';

        // Salve o arquivo de imagem da assinatura do cliente
        file_put_contents($filePath, base64_decode($imageData));

        // Defina o caminho do arquivo para a assinatura do técnico
        $technicoFilePath = $signaturesDir . $tecnico . '.png';

        // Faz uma verificação se existe o arquivo
        if (!file_exists($technicoFilePath)) {
            // Sava o arquivo com assinatura do tecnico somente se não existir
            file_put_contents($technicoFilePath, base64_decode($imageData2));
        }

        $response = array(
            'success' => true,
            'message' => 'Signature saved successfully',
            'local' => $filePath
        );
        echo json_encode($response);
    }
}
