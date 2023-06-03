<?php
  if (isset($_POST['image']) && isset($_POST['name'])) {
    $data = $_POST['image'];
    $data = str_replace('data:image/png;base64,', '', $data);
    $data = str_replace(' ', '+', $data);
    $data = base64_decode($data);
    $cliente = $_POST['name'];
    $filename = 'signatures/'.$cliente.'.png'; // directory path and filename
    if (!is_dir('signatures')) {
      mkdir('signatures'); // create directory if it doesn't exist
    }
    file_put_contents($filename, $data);
    echo 'File uploaded successfully.';
  }

?>