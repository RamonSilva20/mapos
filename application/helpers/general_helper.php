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

if (! function_exists('esc_scalar')) {
    /**
     * Reduz um valor a um unico texto renderizavel, ou `null` se ele nao e um.
     *
     * Todos os escapers de texto precisam responder a mesma pergunta — "isto
     * e um valor de texto?" — e so depois cada um decide o que o meio de
     * saida faz com um `null`. Centralizar a pergunta aqui e o que impede o
     * sexto escaper de inventar a sexta politica: `esc()` devolve string vazia,
     * `esc_msg()` devolve `""`, `esc_json()` devolve `null`, porque cada meio
     * tem sua propria forma de "vazio", e essa traducao fica em uma linha por
     * escaper em vez de um `if` copiado.
     *
     * `null`, `bool`, `array` e `object` nao sao texto. Os dois primeiros
     * chegaram aqui porque o CI3 devolve `NULL` explicito para coluna
     * `TEXT NULL` ausente do payload, e porque um booleano em contexto de
     * texto e bug do chamador, nao algo a renderizar — se a view precisa
     * mostrar "Sim"/"Nao", ela escolhe o texto no ternario, em vez de deixar o
     * `esc()` adivinhar entre `''` e `'1'`.
     *
     * @param  mixed $value
     */
    function esc_scalar($value): ?string
    {
        if ($value === null || is_bool($value) || is_array($value) || is_object($value)) {
            return null;
        }

        return (string) $value;
    }
}

if (! function_exists('esc')) {
    /**
     * Escapa um valor para contexto de texto HTML ou atributo entre aspas.
     *
     * Use em `<?= esc($valor) ?>` dentro do corpo da pagina e dentro de
     * atributos delimitados por aspas (`value="<?= esc($valor) ?>"`).
     *
     * @param  mixed $value
     */
    function esc($value): string
    {
        $text = esc_scalar($value);

        return $text === null
            ? ''
            : htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (! function_exists('esc_json')) {
    /**
     * Codifica um valor como JSON seguro para ser embutido em `<script>`.
     *
     * Use tanto para literais como para estruturas, escrevendo sempre sem aspas
     * em volta: `var x = <?= esc_json($v) ?>` e `var cfg = <?= esc_json($arr) ?>`.
     *
     * O nome é `esc_json` e não algo como `esc_js` porque o que sai daqui é um
     * valor JSON puro, não uma string: um escalar vem como `"texto"` e um array
     * como `{...}`. É por isso que nunca se embrulha em aspas à mão nem se entrega
     * a `JSON.parse()` — nenhum dos dois recebe uma string utilizável. Havia um
     * `esc_js()` idêntico a este, com o nome sugerindo o contrário, e ele é a
     * razão pela qual essas duas armadilhas já tinham sido cometidas.
     *
     * @param  mixed $value
     */
    function esc_json($value): string
    {
        $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;

        // JSON tem null e boolean nativos, entao estes dois nao sao o "vazio"
        // que `esc_scalar()` representa: sao valores, e codificam como valores.
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // Escalar continua virando string, como sempre: `esc_json(1.5)` emite
        // `"1.5"`, nao `1.5`. O `json_encode()` aceita os dois, mas o casts
        // explicito mantem esse contrato visivel em vez de depender de ele
        // "sair certo" por acidente.
        $payload = is_array($value) || is_object($value) ? $value : (string) $value;

        // Uma unica chamada cobre escalar, array e objeto, entao nao ha mais
        // dois fallbacks divergindo entre "falhou um array" e "falhou um
        // escalar" — os dois viram `null`, que e JSON valido nas duas posicoes.
        $encoded = json_encode($payload, $flags);

        return $encoded === false ? 'null' : $encoded;
    }
}

if (! function_exists('clean_url')) {
    /**
     * Normaliza uma URL e recusa a que é estruturalmente inválida.
     *
     * A parte mecânica — trim, vazio, barras de controle — é a mesma para
     * qualquer contexto de URL, então mora aqui. O que cada contexto aceita
     * como esquema é política, e essa fica em `esc_url()` e `esc_img_src()`,
     * uma linha cada, para que a diferença entre as duas seja legível.
     *
     * @param  mixed $value
     */
    function clean_url($value): ?string
    {
        $url = esc_scalar($value);

        if ($url === null) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        // Barras de controle e espacos internos sao removidos por alguns
        // navegadores ao resolver a URL, o que permitiria contrabandear um
        // esquema (ex.: "java\nscript:alert(1)"). Recusamos antes de decidir.
        if (preg_match('/[\x00-\x20\x7F]/', $url)) {
            return null;
        }

        return $url;
    }
}

if (! function_exists('url_scheme')) {
    /**
     * Esquema em caixa baixa, ou null quando a URL não tem um.
     *
     * Âncoras, query strings e caminhos relativos/associados não têm esquema
     * próprio, então não conseguem trocar a origem da página.
     *
     * @param  string $url
     */
    function url_scheme(string $url): ?string
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return $scheme === '' ? null : $scheme;
    }
}

if (! function_exists('esc_url')) {
    /**
     * Escapa uma URL **navegável**, para `href`, `action` ou `location`.
     *
     * Rejeita esquemas executáveis (`javascript:`, `vbscript:`) e `data:`, e
     * devolve string vazia, o que neutraliza ataques de URI execução.
     *
     * Não use em `src` de `<img>`: lá `data:image/...` é legítimo, e passar
     * por esta função devolve string vazia e some com a imagem sem aviso. Para
     * `src` existe `esc_img_src()`.
     *
     * @param  mixed $url
     */
    function esc_url($url): string
    {
        $url = clean_url($url);

        if ($url === null) {
            return '';
        }

        $scheme = url_scheme($url);

        if ($scheme === null) {
            return esc($url);
        }

        $allowed = ['http', 'https', 'mailto', 'tel', 'ftp', 'ftps'];

        if (! in_array($scheme, $allowed, true)) {
            return '';
        }

        return esc($url);
    }
}

if (! function_exists('esc_img_src')) {
    /**
     * Escapa a URL de uma **sub-recurso de imagem**, para `<img src>`.
     *
     * Existe separada de `esc_url()` porque as duas respondem a perguntas
     * diferentes. `href` é navegável: clicar executa o esquema, então a lista
     * é restrita e `data:` fora. `src` de `<img>` só faz o navegador buscar a
     * imagem, e ali `data:image/...` é o formato normal — os QR codes de
     * pagamento são exatamente isso. Com uma função só, `data:` tinha de ser
     * recusado por segurança no `href` e aceito por conveniência no `src`, o
     * que obrigava a documentar "use `esc()` aqui, mas `esc_url()` ali". A
     * exceção desapareceu porque agora existe a função certa para o contexto.
     *
     * @param  mixed $src
     */
    function esc_img_src($src): string
    {
        $src = clean_url($src);

        if ($src === null) {
            return '';
        }

        $scheme = url_scheme($src);

        if ($scheme === null) {
            return esc($src);
        }

        // `data:image/*` é o caso que torna `esc_url()` errado para `src`: os
        // QR codes de pagamento são data URIs e, recusados, viravam string
        // vazia — a imagem sumia da tela sem nenhum erro. Só `image/` passa;
        // `data:text/html` num `src` é inerte, mas não há motivo para aceitar.
        if ($scheme === 'data') {
            return str_starts_with(strtolower($src), 'data:image/') ? esc($src) : '';
        }

        return in_array($scheme, ['http', 'https'], true) ? esc($src) : '';
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
        $value = esc_scalar($value);

        if ($value === null) {
            return '';
        }

        $value = trim($value);

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
     * como HTML pelo alerta. Sempre usar direto, sem aspas em volta, ex.:
     *
     *     Swal.fire({ icon: 'success', text: <?= esc_msg($msg) ?> });
     *
     * @param  mixed $message
     */
    function esc_msg($message): string
    {
        $message = esc_scalar($message);

        // Vazio de um alerta JS e uma string vazia entre aspas, nao nada.
        if ($message === null) {
            return '""';
        }

        $message = (string) preg_replace('#<br\s*/?>#i', "\n", $message);
        $message = strip_tags($message);

        return esc_json($message);
    }
}

if (! function_exists('printSafeHtml')) {
    /**
     * Sanitiza conteudo rico (HTML vindo de editor WYSIWYG) via HTMLPurifier.
     *
     * Use **apenas** para campos que legitimamente aceitam HTML
     * (descricao de produto, defeito, observacoes, laudo tecnico, termo de
     * garantia). Para texto simples e para atributos use `esc()`.
     *
     * O parametro e nullable de proposito: todos esses campos sao `TEXT NULL`
     * no schema, e o CI3 grava `NULL` explicito quando a chave nao vem no
     * payload (por exemplo quando `set_rules('defeito', 'Defeito')` e
     * declarado sem regra nenhuma). Um tipo `string` aqui transformava esse
     * caso comum em `TypeError`, derrubando a pagina inteira.
     *
     * @param  string|null $html
     */
    function printSafeHtml(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        static $purifier = null;

        if ($purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $purifier = new HTMLPurifier($config);
        }

        return $purifier->purify($html);
    }
}
