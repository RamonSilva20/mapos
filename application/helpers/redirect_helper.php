<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Escolhe o status de um redirect, igual ao redirect() do helper url.
 *
 * Separado de respond_redirect() para ser verificável. O status sai por
 * header() nativo, e em CLI isso não é observável: não há getter no CI_Output e
 * xdebug_get_headers() não existe. Como uma função pura, a regra fica coberta de
 * verdade em vez de ser apenas replicada na documentação — a tabela toda está em
 * GeneralHelperTest, e é o único lugar onde ela deve ser testada. O controller
 * não é o dono da regra, e um teste dele aqui seria a mesma tabela duas vezes.
 *
 * 307 e 303 preservam o método da requisição, o que é o que se quer num POST.
 * Fora de HTTP/1.1 não há informação confiável e o redirect() cai em 302.
 */
function redirect_status_for(?string $method, ?string $protocol): int
{
    if ($protocol !== 'HTTP/1.1' || $method === null) {
        return 302;
    }

    return $method !== 'GET' ? 303 : 307;
}

/**
 * Redireciona sem encerrar o processo.
 *
 * O redirect() do helper url do CI3 emite o header Location, escolhe o status e
 * chama exit(). O exit() é o que impede a suíte in-process de conferir o que
 * aconteceu: include()ar o index.php executa o ciclo inteiro da requisição, e
 * qualquer exit() no meio do caminho acaba com o processo do PHPUnit.
 *
 * O status é o mesmo que o redirect() escolheria, via redirect_status_for(),
 * para não trocar 307 por 302 (ou 303) numa navegação só porque o método passou a
 * retornar.
 *
 * Use quando o fluxo realmente precisa continuar depois do redirect. Não é o
 * caso geral: nos outros pontos do app, redirect() é a opção certa, e a suíte
 * contorna o exit() com um processo filho em vez de uma variante do framework.
 */
function respond_redirect(string $url): void
{
    $ci = &get_instance();

    $ci->output->set_header('Location: ' . $url, true);
    $ci->output->set_status_header(redirect_status_for(
        $_SERVER['REQUEST_METHOD'] ?? null,
        $_SERVER['SERVER_PROTOCOL'] ?? null
    ));
}
