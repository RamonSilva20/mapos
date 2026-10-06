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

if (! function_exists('esc')) {
    /**
     * Escapa um valor para contexto de texto HTML ou atributo entre aspas.
     *
     * Use em `<?= esc($valor) ?>` dentro do corpo da pagina e dentro de
     * atributos delimited por aspas (`value="<?= esc($valor) ?>"`).
     *
     * @param  mixed $value
     */
    function esc($value): string
    {
        if ($value === null || is_bool($value)) {
            return '';
        }

        if (is_array($value) || is_object($value)) {
            return '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (! function_exists('esc_js')) {
    /**
     * Escapa um valor para contexto JavaScript.
     *
     * Use dentro de `<script>`, tanto em literais de string
     * (`var x = <?= esc_js($v) ?>;`) quanto em interpolacoes de atributos
     * JS. O `json_encode` com flags HEX garante que tanto `"` quanto
     * `</script>` fiquem neutralizados.
     *
     * @param  mixed $value
     */
    function esc_js($value): string
    {
        return esc_json($value);
    }
}

if (! function_exists('esc_json')) {
    /**
     * Codifica um valor como JSON seguro para ser embutido em `<script>`.
     *
     * Use para arrays/estruturas: `var cfg = <?= esc_json($arr) ?>;`.
     *
     * @param  mixed $value
     */
    function esc_json($value): string
    {
        $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_array($value) || is_object($value)) {
            $encoded = json_encode($value, $flags);

            return $encoded === false ? 'null' : $encoded;
        }

        $encoded = json_encode((string) $value, $flags);

        return $encoded === false ? '""' : $encoded;
    }
}

if (! function_exists('esc_url')) {
    /**
     * Escapa uma URL para uso em `href`/`src`.
     *
     * Rejeita esquemas executaveis (`javascript:`, `data:`, `vbscript:`) e
     * devolve string vazia, o que neutraliza ataques de URI execucao.
     *
     * @param  mixed $url
     */
    function esc_url($url): string
    {
        if ($url === null || is_array($url) || is_object($url) || is_bool($url)) {
            return '';
        }

        $url = trim((string) $url);

        if ($url === '') {
            return '';
        }

        // Barras de controle e espacos internos sao removidos por alguns
        // navegadores ao resolver a URL, o que permitiria contrabandear um
        // esquema (ex.: "java\nscript:alert(1)"). Recusamos antes de decidir.
        if (preg_match('/[\x00-\x20\x7F]/', $url)) {
            return '';
        }

        // Ancoras, query strings e URLs relativas/associadas nao possuem
        // esquema proprio, portanto nao podem trocar a origem da pagina.
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($scheme === '') {
            return esc($url);
        }

        $allowed = ['http', 'https', 'mailto', 'tel', 'whatsapp', 'ftp', 'ftps'];

        if (! in_array($scheme, $allowed, true)) {
            return '';
        }

        return esc($url);
    }
}

if (! function_exists('esc_css')) {
    /**
     * Escapa um valor para contexto de estilo em CSS inline.
     *
     * @param  mixed $value
     */
    function esc_css($value): string
    {
        if ($value === null || is_array($value) || is_object($value) || is_bool($value)) {
            return '';
        }

        $value = trim((string) $value);

        // Impede quebra de contexto e injecao de regras via `;`, `{` ou `}`.
        $value = (string) preg_replace('/[^a-zA-Z0-9#%.,()\s\-_]/', '', $value);

        return str_replace(['\\', '<', '>'], '', $value);
    }
}

if (! function_exists('esc_msg')) {
    /**
     * Prepara uma mensagem de flashdata para exibicao em um alerta JS.
     *
     * As mensagens historicas carregam `<br>` para quebrar linha. Aqui o
     * `<br>` e convertido em quebra de linha real e **toda** as demais
     * marcacao e descartada, de forma que a mensagem nunca seja interpretada
     * como HTML pelo alerta. Sempre devolver dentro de `esc_js()`, ex.:
     *
     *     Swal.fire({ icon: 'success', text: <?= esc_msg($msg) ?> });
     *
     * @param  mixed $message
     */
    function esc_msg($message): string
    {
        if ($message === null || is_array($message) || is_object($message) || is_bool($message)) {
            return '""';
        }

        $message = (string) $message;
        $message = (string) preg_replace('#<br\s*/?>#i', "\n", $message);
        $message = strip_tags($message);

        return esc_js($message);
    }
}

if (! function_exists('printSafeHtml')) {
    /**
     * Sanitiza conteudo rico (HTML vindo de editor WYSIWYG) via HTMLPurifier.
     *
     * Use **apenas** para campos que legitimamente aceitam HTML
     * (descricao de produto, defeito, observacoes, laudo tecnico, termo de
     * garantia). Para texto simples e para atributos use `esc()`.
     */
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
