<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once __DIR__ . '/../vendor/autoload.php';

function pdf_create($html, $filename, $stream = true, $landscape = false)
{
    // Garantir que o diretório temporário existe e tem permissões corretas
    $tempDir = FCPATH . 'assets/uploads/temp';
    if (!is_dir($tempDir)) {
        @mkdir($tempDir, 0777, true);
    }
    if (!is_dir($tempDir . '/mpdf')) {
        @mkdir($tempDir . '/mpdf', 0777, true);
    }
    
    // Normalizar o caminho (remover barras duplas)
    $tempDir = rtrim($tempDir, '/') . '/';
    
    if ($landscape) {
        $mpdf = new \Mpdf\Mpdf(['c', 'A4-L', 'tempDir' => $tempDir]);
    } else {
        $mpdf = new \Mpdf\Mpdf(['c', 'A4', 'tempDir' => $tempDir]);
    }

    $mpdf->showImageErrors = true;
    $mpdf->WriteHTML($html);

    if ($stream) {
        $mpdf->Output($filename . '.pdf', 'I');
    } else {
        $mpdf->Output($tempDir . $filename . '.pdf', 'F');

        return $tempDir . $filename . '.pdf';
    }
}
