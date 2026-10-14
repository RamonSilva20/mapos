<?php

/**
 * Página de erro para exceção não tratada em produção (#2849).
 *
 * O handler do CodeIgniter só mostra a exceção quando display_errors está
 * ligado; em produção ele registra no log e sai, deixando a tela em branco.
 * Este hook (pre_system) o substitui: registra no log do mesmo jeito, responde
 * 500 e mostra a página errors/html/error_500.php, sem detalhes técnicos fora
 * de development. Com display_errors ligado (desenvolvimento) devolve ao
 * handler original, e o hook do Whoops, que roda depois, continua mandando
 * quando está habilitado.
 */
function paginaDeErroExibeErros(): bool
{
    // A mesma conta do _exception_handler do CodeIgniter, por veracidade do
    // resultado: "0", "off", "none", "no", "false", "null" e vazio são desligado.
    return (bool) str_ireplace(['off', 'none', 'no', 'false', 'null'], '', (string) ini_get('display_errors'));
}

function registrarPaginaDeErro(): void
{
    set_exception_handler(static function (Throwable $exception): void {
        if (is_cli() || paginaDeErroExibeErros()) {
            _exception_handler($exception);

            return;
        }

        load_class('Exceptions', 'core')->log_exception('error', 'Exception: ' . $exception->getMessage(), $exception->getFile(), $exception->getLine());

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        if (! headers_sent()) {
            set_status_header(500);
        }

        include VIEWPATH . 'errors/html/error_500.php';

        exit(1);
    });
}
