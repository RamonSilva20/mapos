<?php

/**
 * Cabeçalhos de segurança das respostas HTTP.
 *
 * Carregado pelo hook pre_system (application/hooks/security_headers.php),
 * antes de o CodeIgniter subir, para que os cabeçalhos saiam em toda
 * resposta: telas, redirecionamentos, 404, downloads e API. Como no
 * cookie_seguro_helper.php, as funções recebem o .env e o $_SERVER como
 * parâmetros para serem testáveis isoladamente.
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'cookie_seguro_helper.php';

/**
 * Política de CSP alvo da v5.
 *
 * Vai em modo report-only: o navegador não bloqueia nada, só relata o que
 * seria bloqueado. As telas legadas violam a política de propósito (scripts
 * inline, CDNs, Google Fonts); os relatórios medem o que falta migrar até o
 * modo enforce da #2878.
 *
 * - script-src sem 'unsafe-inline': os scripts saem das views (#2838).
 * - style-src sem 'unsafe-inline': as views novas não usam style= (#2825).
 * - img-src aceita https: e data: por causa dos QR Codes de PIX e boletos,
 *   que vêm dos gateways como URL externa ou base64.
 * - connect-src libera as consultas de CEP e CNPJ do assets/js/funcoes.js.
 *   Hoje a de CEP é JSONP (vira script-src e será relatada); a migração para
 *   fetch é o caminho para não precisar liberar script externo.
 *
 * @param  string  $reportUri  Caminho do endpoint de relatórios, ou '' para não relatar
 */
function segurancaPoliticaCsp($reportUri)
{
    $diretivas = [
        'default-src' => "'self'",
        'script-src' => "'self'",
        'style-src' => "'self'",
        'img-src' => "'self' data: blob: https:",
        'font-src' => "'self' data:",
        'connect-src' => "'self' https://viacep.com.br https://www.receitaws.com.br",
        'object-src' => "'none'",
        'base-uri' => "'self'",
        'form-action' => "'self'",
        'frame-ancestors' => "'self'",
    ];

    if ($reportUri !== '') {
        $diretivas['report-uri'] = $reportUri;
    }

    $partes = [];
    foreach ($diretivas as $nome => $valor) {
        $partes[] = $nome . ' ' . $valor;
    }

    return implode('; ', $partes);
}

/**
 * Caminho do endpoint de relatórios de CSP, relativo ao host.
 *
 * Usa o caminho do APP_BASEURL para funcionar com o Map-OS instalado numa
 * subpasta. Segue com index.php porque nem toda instalação tem rewrite.
 *
 * @param  string|null  $baseUrl  Conteúdo do APP_BASEURL
 */
function segurancaCspReportUri($baseUrl)
{
    $caminho = (string) parse_url((string) $baseUrl, PHP_URL_PATH);
    $caminho = '/' . trim($caminho, '/');

    return rtrim($caminho, '/') . '/index.php/csp/report';
}

/**
 * Monta os cabeçalhos de segurança da requisição.
 *
 * Variáveis do .env (todas opcionais):
 * - APP_SECURITY_HEADERS: liga/desliga todos os cabeçalhos (padrão true).
 * - APP_HSTS_MAX_AGE: segundos do Strict-Transport-Security; 0 desliga
 *   (padrão 31536000, ou 0 com APP_ENVIRONMENT=development). Só é enviado
 *   quando a requisição chega por HTTPS.
 * - APP_HSTS_INCLUDE_SUBDOMAINS: inclui includeSubDomains (padrão false).
 * - APP_CSP_REPORT_ONLY: envia a CSP em report-only (padrão true).
 * - APP_CSP_REPORT: envia os relatórios ao endpoint do Map-OS (padrão true).
 *
 * @param  array  $env     Normalmente $_ENV
 * @param  array  $server  Normalmente $_SERVER
 * @return array<string, string> nome do cabeçalho => valor
 */
function segurancaCabecalhos(array $env, array $server)
{
    if (! cookieEnvFlag($env['APP_SECURITY_HEADERS'] ?? null, true)) {
        return [];
    }

    $cabecalhos = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        // Nenhuma tela usa câmera, microfone, localização ou sensores. A
        // leitura do QR Code do PIX é feita sobre a imagem, sem câmera.
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), accelerometer=(), gyroscope=(), magnetometer=()',
    ];

    // Em desenvolvimento o HSTS fica desligado por padrão: o navegador guarda
    // a regra por um ano e passaria a forçar HTTPS num domínio local (ex.:
    // mapos.test) que também é usado por HTTP. Um valor explícito no .env vale.
    $padraoMaxAge = ($env['APP_ENVIRONMENT'] ?? '') === 'development' ? 0 : 31536000;
    $maxAge = filter_var($env['APP_HSTS_MAX_AGE'] ?? $padraoMaxAge, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($maxAge === false) {
        $maxAge = $padraoMaxAge;
    }

    if ($maxAge > 0 && cookieRequisicaoHttps($server, $env['APP_PROXY_IPS'] ?? '')) {
        $hsts = 'max-age=' . $maxAge;
        if (cookieEnvFlag($env['APP_HSTS_INCLUDE_SUBDOMAINS'] ?? null, false)) {
            $hsts .= '; includeSubDomains';
        }
        $cabecalhos['Strict-Transport-Security'] = $hsts;
    }

    if (cookieEnvFlag($env['APP_CSP_REPORT_ONLY'] ?? null, true)) {
        $reportUri = cookieEnvFlag($env['APP_CSP_REPORT'] ?? null, true)
            ? segurancaCspReportUri($env['APP_BASEURL'] ?? '')
            : '';
        $cabecalhos['Content-Security-Policy-Report-Only'] = segurancaPoliticaCsp($reportUri);
    }

    return $cabecalhos;
}

/**
 * Tamanho máximo, em bytes, de um relatório de CSP aceito pelo endpoint.
 */
const SEGURANCA_CSP_RELATORIO_MAX = 16384;

/**
 * Quantas violações distintas o arquivo agregado guarda. Passou disso, as
 * novas são descartadas e as já conhecidas continuam sendo contadas.
 */
const SEGURANCA_CSP_CHAVES_MAX = 500;

/**
 * Decide se o endpoint aceita a requisição de relatório.
 *
 * @param  string  $metodo       Método HTTP
 * @param  string  $contentType  Cabeçalho Content-Type cru
 * @param  string  $corpo        Corpo lido, com no máximo SEGURANCA_CSP_RELATORIO_MAX + 1 bytes
 * @return int 0 quando aceita, ou o status HTTP de recusa (405, 415 ou 413)
 */
function segurancaCspRecusa($metodo, $contentType, $corpo)
{
    if (strtoupper((string) $metodo) !== 'POST') {
        return 405;
    }

    $tipo = strtolower(trim(explode(';', (string) $contentType)[0]));
    if (! in_array($tipo, ['application/csp-report', 'application/reports+json', 'application/json'], true)) {
        return 415;
    }

    if (strlen((string) $corpo) > SEGURANCA_CSP_RELATORIO_MAX) {
        return 413;
    }

    return 0;
}

/**
 * Lê o corpo de um relatório de CSP e devolve as violações normalizadas.
 *
 * Aceita os dois formatos que os navegadores enviam: o do report-uri
 * (application/csp-report, um objeto "csp-report") e o da Reporting API
 * (application/reports+json, uma lista de relatórios "csp-violation").
 * Payload malformado devolve lista vazia em vez de erro.
 *
 * @param  string  $corpo  Corpo cru da requisição
 * @return list<array{diretiva: string, bloqueado: string, pagina: string, fonte: string}>
 */
function segurancaCspLerRelatorio($corpo)
{
    $dados = json_decode((string) $corpo, true);

    if (! is_array($dados)) {
        return [];
    }

    $brutos = [];

    if (isset($dados['csp-report']) && is_array($dados['csp-report'])) {
        $r = $dados['csp-report'];
        $brutos[] = [
            $r['effective-directive'] ?? $r['violated-directive'] ?? '',
            $r['blocked-uri'] ?? '',
            $r['document-uri'] ?? '',
            segurancaCspFonte($r['source-file'] ?? '', $r['line-number'] ?? null),
        ];
    } elseif (array_is_list($dados)) {
        foreach ($dados as $relatorio) {
            if (! is_array($relatorio) || ($relatorio['type'] ?? '') !== 'csp-violation' || ! is_array($relatorio['body'] ?? null)) {
                continue;
            }
            $b = $relatorio['body'];
            $brutos[] = [
                $b['effectiveDirective'] ?? '',
                $b['blockedURL'] ?? '',
                $b['documentURL'] ?? ($relatorio['url'] ?? ''),
                segurancaCspFonte($b['sourceFile'] ?? '', $b['lineNumber'] ?? null),
            ];
        }
    }

    $violacoes = [];
    foreach ($brutos as [$diretiva, $bloqueado, $pagina, $fonte]) {
        if (! is_string($diretiva) || ! is_string($bloqueado) || ! is_string($pagina) || trim($diretiva) === '') {
            continue;
        }

        $violacoes[] = [
            // O violated-directive antigo vem como "script-src-elem 'self'": fica só o nome.
            'diretiva' => segurancaCspCurto(explode(' ', trim($diretiva))[0]),
            'bloqueado' => segurancaCspCurto(segurancaCspOrigem($bloqueado)),
            'pagina' => segurancaCspCurto((string) parse_url($pagina, PHP_URL_PATH)),
            'fonte' => segurancaCspCurto($fonte),
        ];
    }

    return $violacoes;
}

/**
 * Arquivo e linha que causaram a violação, quando o navegador informa.
 *
 * @param  mixed  $arquivo
 * @param  mixed  $linha
 */
function segurancaCspFonte($arquivo, $linha)
{
    $arquivo = is_string($arquivo) ? $arquivo : '';

    return is_int($linha) && $arquivo !== '' ? $arquivo . ':' . $linha : $arquivo;
}

/**
 * Reduz o recurso bloqueado à origem, para agregar violações iguais: o mesmo
 * CDN com caminhos diferentes conta como uma violação só. Palavras-chave como
 * "inline" e "eval" passam como estão.
 *
 * @param  string  $bloqueado
 */
function segurancaCspOrigem($bloqueado)
{
    $bloqueado = trim((string) $bloqueado);
    $partes = parse_url($bloqueado);

    if (is_array($partes) && isset($partes['scheme'], $partes['host'])) {
        return $partes['scheme'] . '://' . $partes['host'] . (isset($partes['port']) ? ':' . $partes['port'] : '');
    }

    if (is_array($partes) && isset($partes['scheme'])) {
        return $partes['scheme'] . ':'; // data:, blob:
    }

    return $bloqueado;
}

/**
 * Remove caracteres de controle e limita o tamanho, para que um relatório
 * forjado não escreva quebras de linha no log nem entradas gigantes.
 *
 * @param  string  $texto
 */
function segurancaCspCurto($texto)
{
    return mb_substr((string) preg_replace('/[\x00-\x1F\x7F]/', '', (string) $texto), 0, 200);
}

/**
 * Soma as violações ao arquivo agregado.
 *
 * Cada violação distinta (diretiva + recurso bloqueado + página) vira uma
 * entrada com contagem, primeira e última ocorrência. O arquivo é JSON e é
 * gravado com trava, então requisições simultâneas não perdem contagem.
 *
 * @param  array   $violacoes  Saída de segurancaCspLerRelatorio()
 * @param  string  $arquivo    Caminho do JSON agregado
 * @param  int     $agora      Timestamp
 * @return array<int, string> chaves vistas pela primeira vez nesta chamada
 */
function segurancaCspRegistrar(array $violacoes, $arquivo, $agora)
{
    if ($violacoes === []) {
        return [];
    }

    $fp = @fopen($arquivo, 'c+');
    if ($fp === false) {
        return [];
    }

    $novas = [];

    try {
        flock($fp, LOCK_EX);
        $agregado = json_decode((string) stream_get_contents($fp), true);
        if (! is_array($agregado)) {
            $agregado = [];
        }

        $data = date('Y-m-d H:i:s', $agora);

        foreach ($violacoes as $v) {
            $chave = $v['diretiva'] . ' | ' . $v['bloqueado'] . ' | ' . $v['pagina'];

            if (isset($agregado[$chave])) {
                $agregado[$chave]['total']++;
                $agregado[$chave]['ultima'] = $data;

                continue;
            }

            if (count($agregado) >= SEGURANCA_CSP_CHAVES_MAX) {
                continue;
            }

            $agregado[$chave] = $v + ['total' => 1, 'primeira' => $data, 'ultima' => $data];
            $novas[] = $chave;
        }

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string) json_encode($agregado, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        fflush($fp);
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    return $novas;
}
