<?php

use Exceptions\Http\AuthorizationDenied;
use Exceptions\Http\HttpException;

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

/**
 * Para onde a tela vai quando a requisição não passa pelo guard.
 *
 * Separado de render_http_exception() pelo mesmo motivo que redirect_status_for()
 * está separado de respond_redirect(): o destino é decidido por um `if` e
 * escolhido por um `redirect()` que chama `exit()`, e nenhum dos dois é
 * observável de dentro do processo. Como função pura, a regra que realmente
 * importa fica coberta — que um 401 vai para o login e um 403 vai para a home,
 * e não os dois para o mesmo lugar.
 *
 * E a diferença não é de estilo. Quem não está autenticado entra pelo login e
 * resolve; quem está autenticado e sem uma permissão não resolve nada entrando
 * de novo, e mandá-lo para o login ainda deita fora a sessão que ele tem.
 *
 * O alvo de `login` é relativo de propósito: é o que o guard escrevia antes, e o
 * `redirect()` do CI3 o expande por `site_url()`. Passar um URL absoluto aqui
 * pareceria mais explícito e trocaria o header `Location` que o servidor emite.
 */
function http_exception_redirect_target(HttpException $e): string
{
    return $e instanceof AuthorizationDenied ? base_url() : 'login';
}

/**
 * A resposta de uma exceção de autorização, no formato que a requisição pediu.
 *
 * Uma exceção, dois destinos, e a divisão é do pedido e não do erro: a tela é
 * HTML e o destino certo é uma página, a API é JSON e redirecionar é
 * indefensável. Por isso o `index.php` captura a exceção e chama isto, em vez de
 * cada guard escolher o que fazer.
 *
 * O caminho web reproduz o redirect() de antes byte a byte, e o detalhe que
 * importa é o alvo: `login` relativo no `AuthenticationRequired`, `base_url()`
 * no `AuthorizationDenied`. São destinos diferentes de propósito, porque quem
 * não está autenticado ainda pode entrar e quem está autenticado sem permissão
 * não resolve o problema entrando de novo.
 *
 * O caminho JSON não usa `respond_redirect()` por dois motivos. Um header
 * `Location` acumulado em `CI_Output` só sai no `_display()`, e aqui o
 * `_display()` é justamente o que estamos chamando — mas o `Location` não teria
 * que ser enviado para um 401, e sim o `Content-Type`. E o `response()` do
 * `REST_Controller`, que é onde a API já escreve JSON, faz o mesmo que isto:
 * content type, status, output, `_display()` e `exit` por fora.
 *
 * A única divergência conhecida com o `response()` é o ramo de `enable_profiling`,
 * que ecoa o JSON e sai sem passar pelo CI_Output. O `response()` continua sendo
 * o dono da resposta da API; isto cobre o caso em que a exceção atravessa o
 * `index.php`, que hoje ainda não acontece porque `logged_user()` e
 * `logged_client()` respondem por conta própria.
 */
function render_http_exception(HttpException $e): void
{
    $ci = &get_instance();

    if (is_api_request()) {
        $ci->output->set_content_type('application/json');
        $ci->output->set_status_header($e->status());
        $ci->output->set_output((string) json_encode([
            'status' => false,
            'message' => $e->getMessage(),
        ]));

        $ci->output->_display();

        return;
    }

    redirect(http_exception_redirect_target($e));
}

/**
 * A requisição atual é da API?
 *
 * Só o prefixo. Toda rota de API do app está sob `api/` — em
 * `application/config/routes_api.php` e em `application/controllers/api/` — e um
 * teste melhor que o prefixo seria pior: `Accept: application/json` também
 * aparece em Fetch do admin, e `is_ajax_request()` só é verdade com XHR, que
 * não é o que um cliente de API faz.
 */
function is_api_request(): bool
{
    $uri = get_instance()->uri->uri_string();

    return $uri === 'api' || str_starts_with($uri, 'api/');
}
