<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SignaturePad extends CI_Controller {

    public function index()
    {
       
        
    }

    public function upload_signature()
    {
	    $nOs = $this->input->post('nOs');	
        $imageData = $this->input->post('imageData');
        $imageData2 = $this->input->post('imageData2');
        $clientName = $this->input->post('clientName');
        $tecnico = $this->input->post('tecnico');

        // Strip the metadata from the base64-encoded image data
        $imageData = preg_replace('#^data:image/[^;]+;base64,#', '', $imageData);
        $imageData2 = preg_replace('#^data:image/[^;]+;base64,#', '', $imageData2);

        // Save the image file
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/mapos-master/application/signatures/' . $nOs . $clientName . '.png';
file_put_contents($filePath, base64_decode($imageData));

        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/mapos-master/application/signatures/' . $tecnico . '.png';
        file_put_contents($filePath, base64_decode($imageData2));

        echo 'Signature saved successfully. LOCAL = ' .$filePath;

    }

}
