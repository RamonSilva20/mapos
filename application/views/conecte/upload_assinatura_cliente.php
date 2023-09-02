<?php
$this->load->library('upload');

$response = array();

if (isset($_POST['image']) && isset($_POST['name'])) {
    $data = $_POST['image'];
    $data = str_replace('data:image/png;base64,', '', $data);
    $data = str_replace(' ', '+', $data);
    $data = base64_decode($data);
    $cliente = $_POST['name'];
    $clienteFilename = 'signatures/'.$cliente.'.png'; // Diretório e nome do arquivo do cliente
    $tecnicoFilename = 'signatures/tecnico.png'; // Diretório e nome do arquivo do técnico
    
    if (!is_dir('signatures')) {
        mkdir('signatures'); // Cria o diretório se ele não existir
    }
    
    // Verifica se o arquivo do técnico já existe
    if (!file_exists($tecnicoFilename)) {
        file_put_contents($tecnicoFilename, $data); // Salva a assinatura do técnico
    }
    
    // Salva a assinatura do cliente sempre
    file_put_contents($clienteFilename, $data); // Salva a assinatura do cliente
    
    $response['success'] = true;
    $response['message'] = 'Assinatura do cliente enviada com sucesso.';
} else {
    $response['success'] = false;
    $response['message'] = 'Falha ao enviar a assinatura do cliente. Por favor, verifique os dados enviados.';
}

echo json_encode($response);
<?>
