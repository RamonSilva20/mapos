<?php

/**
 * Localização do arquivo .env (#2879): fora da pasta pública ele não depende de regra do servidor web.
 *
 * Ordem de busca (vale o primeiro arquivo que existir):
 *  1. o caminho absoluto da variável de ambiente MAPOS_ENV_FILE (SetEnv no Apache, fastcgi_param no nginx, php-fpm env[]);
 *  2. `.mapos.env` na pasta acima da raiz do projeto (para hospedagens que não deixam definir variáveis);
 *  3. `application/.env`, o local de sempre.
 *
 * Arquivo PHP puro, sem CodeIgniter: o index.php, o instalador e o painel usam a mesma regra.
 */
function maposEnvArquivo(string $raiz): string
{
    $raiz = rtrim($raiz, '/\\');
    $padrao = $raiz . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . '.env';

    $variavel = getenv('MAPOS_ENV_FILE');
    if (($variavel === false || $variavel === '') && isset($_SERVER['MAPOS_ENV_FILE']) && is_string($_SERVER['MAPOS_ENV_FILE'])) {
        $variavel = $_SERVER['MAPOS_ENV_FILE'];
    }
    // Só caminho absoluto: um relativo mudaria de significado conforme o diretório de trabalho.
    $variavel = is_string($variavel) && preg_match('#^(/|[A-Za-z]:[\\\\/])#', $variavel) === 1 ? $variavel : '';

    $candidatos = array_filter([
        $variavel,
        dirname($raiz) . DIRECTORY_SEPARATOR . '.mapos.env',
        $padrao,
    ]);
    foreach ($candidatos as $candidato) {
        if (is_file($candidato)) {
            return $candidato;
        }
    }

    // Nenhum existe (instalação nova): o instalador grava onde o administrador mandou, senão no local de sempre.
    return $variavel !== '' ? $variavel : $padrao;
}
