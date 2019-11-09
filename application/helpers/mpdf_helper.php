<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

function pdf_create($html, $filename, $stream = true, $landscape = false)
{

    require_once APPPATH . 'helpers/mpdf/mpdf.php';

    if ($landscape) {
        $mpdf = new mPDF('c', 'A4-L');
    } else {
        $mpdf = new mPDF('c', 'A4');
    }

    $mpdf->WriteHTML($html);

    if ($stream) {
        $mpdf->Output($filename . '.pdf', 'I');
    } else {
        $mpdf->Output('./uploads/temp/' . $filename . '.pdf', 'F');

        return './uploads/temp/' . $filename . '.pdf';
    }
}
