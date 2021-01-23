<?php

if (!function_exists('convertUrlToUploadsPath')) {
    function convertUrlToUploadsPath($url)
    {
        if (!$url) {
            return;
        }

        return FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename($url);
    }
}

if (!function_exists('limitarTexto')) {
    function limitarTexto($texto, $limite)
    {
        $contador = strlen($texto);

        if ($contador >= $limite) {
            $texto = substr($texto, 0, strrpos(substr($texto, 0, $limite), ' ')) . '...';
            return $texto;
        } else {
            return $texto;
        }
    }
}

if (!function_exists('getMoneyAsCents')) {
    function getMoneyAsCents($value)
    {
        // strip out commas
        $value = preg_replace("/\,/i", "", $value);

        // strip out all but numbers, dash, and dot
        $value = preg_replace('/[^0-9]/', '', $value);

        // make sure we are dealing with a proper number now, no +.4393 or 3...304 or 76.5895,94
        if (!is_numeric($value)) {
            return 0.00;
        }

        // convert to a float explicitly
        $value = (float) $value;

        return (int) round($value, 2) * 100;
    }
}

if (!function_exists('getCobrancaTransactionStatus')) {
    function getCobrancaTransactionStatus($paymentGatewaysConfig, $paymentGateway, $status)
    {
        return $paymentGatewaysConfig[$paymentGateway]['transaction_status'][$status];
    }
}
