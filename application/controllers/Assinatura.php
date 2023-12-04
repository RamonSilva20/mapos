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
            http_response_code(400);
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

        // Verificar se já existe uma assinatura para esta OS
        $this->load->database();
        $this->db->select('id');
        $this->db->from('ass');
        $this->db->where('nOs', $nOs);
        $this->db->order_by('assinaturaDate', 'DESC'); // Ordernar pela data de assinatura decrescente
        $this->db->limit(1); // Apenas a assinatura mais recente
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            // Já existe uma assinatura para esta OS
            http_response_code(400);
            $response = array(
                'success' => false,
                'message' => 'Erro: Já existe uma assinatura para esta OS.'
            );
            echo json_encode($response);
            return;
        }

        // Continuar com a inserção se não existir uma assinatura para esta OS

        // Adicionar estas linhas para obter a data e o IP
        $assinaturaDate = date('Y-m-d H:i:s');
        $assinaturaIP = $_SERVER['REMOTE_ADDR'];

        // Strip the metadata from the base64-encoded image data
        $imageData = preg_replace('#^data:image/[^;]+;base64,#', '', $imageData);
        $imageData2 = preg_replace('#^data:image/[^;]+;base64,#', '', $imageData2);

        // Get the project root path
        $projectRoot = realpath(APPPATH . '../');

        // Set the directory path for signatures
        $signaturesDir = $projectRoot . '/assets/assinaturas/';

        // Create the directory if it doesn't exist
        if (!is_dir($signaturesDir)) {
            mkdir($signaturesDir, 0777, true);
        }

        // Set the file path for the client signature
        $filePath = '/assets/assinaturas/' . $nOs . $clientName . '.png';

        // Save the client signature image file
        file_put_contents($projectRoot . '/' . $filePath, base64_decode($imageData));

        // Set the file path for the technician signature
        $technicoFilePath = 'assets/assinaturas/' . $tecnico . '.png';

        // Check if the technician signature file already exists
        if (!file_exists($projectRoot . '/' . $technicoFilePath)) {
            // Save the image file for the technician only if it doesn't exist
            file_put_contents($projectRoot . '/' . $technicoFilePath, base64_decode($imageData2));
        }

        // Salvar a assinatura no banco de dados
        $this->load->database();

        // Preparar os dados para inserção
        $data = array(
            'nOs' => $nOs,
            'clientName' => $clientName,
            'tecnico' => $tecnico,
            'filePath' => $filePath,
            'assinaturaDate' => $assinaturaDate,
            'assinaturaIP' => $assinaturaIP
        );

        // Inserir dados no banco de dados
        if (!$this->db->insert('ass', $data)) {
            // Não foi possível salvar a assinatura no banco de dados
            http_response_code(500);
            $response = array(
                'success' => false,
                'message' => 'Erro interno do servidor: Não foi possível salvar a assinatura.'
            );
            echo json_encode($response);
            return;
        }

        // Se chegou até aqui, a assinatura foi salva com sucesso
        $response = array(
            'success' => true,
            'message' => 'Assinatura salva com sucesso',
            'local' => $filePath,
            'assinaturaDate' => $assinaturaDate,
            'assinaturaIP' => $assinaturaIP
        );
        echo json_encode($response);
    }
}