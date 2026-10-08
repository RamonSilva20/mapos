<?php

use Piggly\Pix\Parser;

if (! function_exists('convertUrlToUploadsPath')) {
    function convertUrlToUploadsPath($url)
    {
        if (! $url) {
            return;
        }

        return FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename($url);
    }
}

if (! function_exists('limitarTexto')) {
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

if (! function_exists('getMoneyAsCents')) {
    function getMoneyAsCents($value)
    {
        // make sure we are dealing with a proper number now, no +.4393 or 3...304 or 76.5895,94
        if (! is_numeric($value)) {
            throw new \InvalidArgumentException('A entrada deve ser numérica!');
        }

        return intval(round(floatval($value), 2) * 100);
    }
}

if (! function_exists('getCobrancaTransactionStatus')) {
    function getCobrancaTransactionStatus($paymentGatewaysConfig, $paymentGateway, $status)
    {
        return $paymentGatewaysConfig[$paymentGateway]['transaction_status'][$status];
    }
}

if (! function_exists('getPixKeyType')) {
    function getPixKeyType($value)
    {
        if (Parser::validateDocument($value)) {
            return Parser::KEY_TYPE_DOCUMENT;
        }

        if (Parser::validateEmail($value)) {
            return Parser::KEY_TYPE_EMAIL;
        }

        if (Parser::validatePhone($value)) {
            return Parser::KEY_TYPE_PHONE;
        }

        if (Parser::validateRandom($value)) {
            return Parser::KEY_TYPE_RANDOM;
        }

        return null;
    }
}

if (! function_exists('getAmount')) {
    function getAmount($money)
    {
        $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);

        $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
        $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '', $stringWithCommaOrDot);

        return floatval(str_replace(',', '.', $removedThousandSeparator));
    }
}

if (! function_exists('json_decode_legacy')) {
    function json_decode_legacy(string $raw): mixed
    {
        $decoded = json_decode($raw, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            $decoded = unserialize($raw, ['allowed_classes' => false]);
        }

        return $decoded;
    }
}

if (! function_exists('printSafeHtml')) {
    function printSafeHtml(string $html): string
    {
        static $purifier = null;

        if ($purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $purifier = new HTMLPurifier($config);
        }

        return $purifier->purify($html);
    }
}

if (! function_exists('protegerDiretorioUpload')) {
    /**
     * Cria um index.html nos diretórios de upload informados para impedir a
     * listagem do conteúdo quando o servidor web permite indexação.
     */
    function protegerDiretorioUpload(array $diretorios)
    {
        foreach ($diretorios as $diretorio) {
            $index = rtrim($diretorio, '/\\') . DIRECTORY_SEPARATOR . 'index.html';

            if (is_dir($diretorio) && ! file_exists($index)) {
                @file_put_contents($index, "<html>\n<head>\n\t<title>403 Forbidden</title>\n</head>\n<body>\n\n<p>Directory access is forbidden.</p>\n\n</body>\n</html>\n");
            }
        }
    }
}

if (! function_exists('dataIsoParaYmd')) {
    /**
     * Data em AAAA-MM-DD a partir de um parâmetro de requisição, ou null se
     * faltar ou for inválida. Aceita a data pura ou um ISO 8601 com hora e fuso
     * (2026-10-01T00:00:00-03:00, como o FullCalendar envia), do qual só a
     * data interessa.
     */
    function dataIsoParaYmd($valor): ?string
    {
        if (! is_string($valor) || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:$|T)/', $valor, $partes)) {
            return null;
        }

        if (! checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
            return null;
        }

        return "{$partes[1]}-{$partes[2]}-{$partes[3]}";
    }
}
