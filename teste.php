<?php
// Require composer autoload
require_once 'vendor/autoload.php';

// Create an instance of the class:

$mpdf = new mPDF();

// Write some HTML code:

$mpdf->WriteHTML('Hello World');

// Output a PDF file directly to the browser
$mpdf->Output();
