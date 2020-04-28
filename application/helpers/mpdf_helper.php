<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once __DIR__ . '/../vendor/autoload.php';

function pdf_create($html, $filename, $stream = true, $landscape = false)
{
    if ($landscape) {
        $mpdf = new \Mpdf\Mpdf(['c', 'A4-L', 'tempDir' => FCPATH.'assets/uploads/temp/']);
    } else {
        $mpdf = new \Mpdf\Mpdf(['c', 'A4', 'tempDir' => FCPATH.'assets/uploads/temp/']);
    }

    $mpdf->showImageErrors = true;
    $mpdf->WriteHTML($html);

    if ($stream) {
        $mpdf->Output($filename . '.pdf', 'I');
    } else {
        $mpdf->Output(FCPATH.'assets/uploads/temp/' . $filename . '.pdf', 'F');

        return FCPATH.'assets/uploads/temp/' . $filename . '.pdf';
    }
}
