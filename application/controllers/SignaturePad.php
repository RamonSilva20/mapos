<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SignaturePad extends CI_Controller {

    public function index()
    {
       
        
    }

    public function upload_signature()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('nOs', 'nOs', 'required');
        $this->form_validation->set_rules('imageData', 'imageData', 'required');
        $this->form_validation->set_rules('imageData2', 'imageData2', 'required');
        $this->form_validation->set_rules('clientName', 'clientName', 'required');
        $this->form_validation->set_rules('tecnico', 'tecnico', 'required');

        if ($this->form_validation->run() === FALSE) {
            echo 'Erro: Dados de assinatura ausentes.';
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

        // Save the image file
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/sisfw/application/signatures/' . $nOs . $clientName . '.png';
	    file_put_contents($filePath, base64_decode($imageData));

        // Check if the technician signature file already exists
        $technicoFilePath = $_SERVER['DOCUMENT_ROOT'] . '/sisfw/application/signatures/' . $tecnico . '.png';
        if (!file_exists($technicoFilePath)) {
        // Save the image file for the technician only if it doesn't exist
        file_put_contents($technicoFilePath, base64_decode($imageData2));
    }

        echo 'Signature saved successfully. LOCAL = ' .$filePath;

    }

}