<?php

namespace Exceptions\Http;

/**
 * A requisição não tem sessão: ninguém está autenticado.
 *
 * É o 401, e no lado web a resposta é o redirect para o login — que é o que o
 * guard de `MY_Controller::__construct()` já fazia antes desta exceção
 * existir. A distinção com `AuthorizationDenied` é quem está autenticado, e ela
 * importa para o usuário: quem não está autenticado é redirigido para o login
 * porque ainda pode entrar; quem está autenticado e sem permissão não ganha
 * nada sendo levado ao login, e por isso vai para a home.
 *
 * A mensagem é opcional e existe para a API. No lado web ela não aparece em
 * lugar nenhum: o flashdata do guard, quando existe, já foi escrito antes do
 * throw, e o redirect não mostra texto.
 */
final class AuthenticationRequired extends HttpException
{
    public function __construct(string $message = 'Faça login para acessar.')
    {
        parent::__construct($message, 401);
    }
}
