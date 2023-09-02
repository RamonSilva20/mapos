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

        // Strip the metadata from the base64-encoded image data
        $imageData = preg_replace('#^data:image/[^;]+;base64,#', '', $imageData);
        $imageData2 = preg_replace('#^data:image/[^;]+;base64,#', '', $imageData2);

        // Get the project root path
        $projectRoot = realpath(APPPATH . '../');

        // Set the directory path for signatures
        $signaturesDir = $projectRoot . '/assets/signatures/';

        // Create the directory if it doesn't exist
        if (!is_dir($signaturesDir)) {
            mkdir($signaturesDir, 0777, true);
        }

        // Set the file path for the client signature
        $filePath = $signaturesDir . $nOs . $clientName . '.png';

        // Save the client signature image file
        file_put_contents($filePath, base64_decode($imageData));

        // Set the file path for the technician signature
        $technicoFilePath = $signaturesDir . $tecnico . '.png';

        // Check if the technician signature file already exists
        if (!file_exists($technicoFilePath)) {
            // Save the image file for the technician only if it doesn't exist
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