<?php

/**
 * Regras dos padrões de cookie e sessão.
 *
 * Carregado direto pelo config.php, antes de o CodeIgniter subir, porque os
 * valores de cookie precisam estar resolvidos quando a configuração é lida.
 * Por isso as funções recebem $_SERVER e os valores do .env como parâmetros,
 * em vez de lerem o estado global: assim são testáveis isoladamente.
 */

/**
 * Lê uma flag booleana do .env.
 *
 * Variável ausente ou vazia usa o padrão. Valor que não é booleano reconhecível
 * também usa o padrão, em vez de virar false em silêncio.
 *
 * @param  mixed  $valor   Valor cru do $_ENV, ou null quando a variável não existe
 * @param  bool   $padrao  Valor usado quando a variável não está definida
 */
function cookieEnvFlag($valor, $padrao)
{
    if ($valor === null || trim((string) $valor) === '') {
        return $padrao;
    }

    $flag = filter_var($valor, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

    return $flag ?? $padrao;
}

/**
 * Diz se um IP está na lista de proxies confiáveis.
 *
 * Aceita o mesmo formato do proxy_ips do CodeIgniter: IPs e sub-redes CIDR,
 * separados por vírgula ou em array, IPv4 e IPv6.
 *
 * @param  string        $ip     Endereço que fez a conexão (REMOTE_ADDR)
 * @param  string|array  $lista  Conteúdo do APP_PROXY_IPS
 */
function cookieIpConfiavel($ip, $lista)
{
    $binario = @inet_pton((string) $ip);

    if ($binario === false) {
        return false;
    }

    $itens = is_array($lista) ? $lista : explode(',', (string) $lista);

    foreach ($itens as $item) {
        $item = trim((string) $item);

        if ($item === '') {
            continue;
        }

        [$rede, $mascara] = array_pad(explode('/', $item, 2), 2, null);
        $redeBinaria = @inet_pton($rede);

        // IPv4 e IPv6 têm tamanhos diferentes e nunca casam entre si.
        if ($redeBinaria === false || strlen($redeBinaria) !== strlen($binario)) {
            continue;
        }

        $bits = strlen($binario) * 8;
        $mascara = $mascara === null ? $bits : (int) $mascara;

        if ($mascara < 0 || $mascara > $bits) {
            continue;
        }

        $bytesInteiros = intdiv($mascara, 8);
        $restoBits = $mascara % 8;

        if (substr($binario, 0, $bytesInteiros) !== substr($redeBinaria, 0, $bytesInteiros)) {
            continue;
        }

        if ($restoBits === 0) {
            return true;
        }

        $mascaraByte = (0xFF << (8 - $restoBits)) & 0xFF;

        if ((ord($binario[$bytesInteiros]) & $mascaraByte) === (ord($redeBinaria[$bytesInteiros]) & $mascaraByte)) {
            return true;
        }
    }

    return false;
}

/**
 * Diz se a requisição atual chegou por HTTPS.
 *
 * O cabeçalho X-Forwarded-Proto só é aceito quando a conexão vem de um proxy
 * listado em APP_PROXY_IPS. Sem isso, qualquer cliente poderia mandar o
 * cabeçalho e o servidor acharia que está em HTTPS. O is_https() do
 * CodeIgniter confia nele sem checar a origem, por isso não é usado aqui.
 *
 * @param  array         $server     Normalmente $_SERVER
 * @param  string|array  $proxyIps   Conteúdo do APP_PROXY_IPS
 */
function cookieRequisicaoHttps(array $server, $proxyIps)
{
    $https = strtolower((string) ($server['HTTPS'] ?? ''));

    if ($https !== '' && $https !== 'off') {
        return true;
    }

    $encaminhado = strtolower(trim((string) ($server['HTTP_X_FORWARDED_PROTO'] ?? '')));

    // Em cadeia de proxies o cabeçalho pode vir como "https, http"; o primeiro
    // valor é o do cliente original.
    $encaminhado = trim(explode(',', $encaminhado)[0]);

    if ($encaminhado === 'https' && cookieIpConfiavel($server['REMOTE_ADDR'] ?? '', $proxyIps)) {
        return true;
    }

    return false;
}

/**
 * Resolve o cookie_secure a partir do APP_COOKIE_SECURE.
 *
 * - "auto" ou ausente: Secure só quando a requisição chega por HTTPS. Em HTTP
 *   o cookie continua sem Secure, senão o navegador o descartaria e o login
 *   pararia de funcionar.
 * - "true" ou "false": valor fixo, respeitado como está.
 *
 * @param  mixed         $valor     Valor cru do $_ENV
 * @param  array         $server    Normalmente $_SERVER
 * @param  string|array  $proxyIps  Conteúdo do APP_PROXY_IPS
 */
function cookieSecure($valor, array $server, $proxyIps)
{
    $texto = strtolower(trim((string) $valor));

    if ($valor === null || $texto === '' || $texto === 'auto') {
        return cookieRequisicaoHttps($server, $proxyIps);
    }

    $flag = filter_var($texto, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

    return $flag ?? cookieRequisicaoHttps($server, $proxyIps);
}

/**
 * Resolve o atributo SameSite.
 *
 * Padrão Lax. "None" exige Secure no navegador; sem Secure o cookie seria
 * recusado, então cai para Lax.
 *
 * @param  mixed  $valor   Valor cru do $_ENV
 * @param  bool   $secure  Se o cookie sai com Secure
 */
function cookieSameSite($valor, $secure)
{
    $valor = ucfirst(strtolower(trim((string) $valor)));

    if (! in_array($valor, ['Lax', 'Strict', 'None'], true)) {
        return 'Lax';
    }

    if ($valor === 'None' && ! $secure) {
        return 'Lax';
    }

    return $valor;
}
