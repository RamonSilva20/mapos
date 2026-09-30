<?php

namespace Exceptions\Http;

/**
 * Quem está autenticado não tem a permissão que este controller exige.
 *
 * É o 403, e é a diferença entre "não sei quem você é" e "sei, e não pode".
 * No lado web a resposta é o redirect para a home com o flashdata `error` — o
 * mesmo par destino+mensagem que os guards já escreviam, e que o usuário já
 * conhece.
 *
 * O destino é a home e não o login por uma razão prática, não estética: um
 * operador autenticado e sem uma permissão específica não resolve o problema
 * entrando de novo, e mandá-lo para o login deita fora a sessão que ele tem e
 * o deixa pedindo credencial para um acesso que nenhuma credencial libera.
 *
 * A mensagem é escrita no ponto de chamada porque ela é específica do que foi
 * negado — ver a diferença entre configurar permissões e configurar usuários —
 * e o flashdata que o guard escreve antes do throw é a MESMA frase. São dois
 * usos de uma frase só: o flashdata para o usuário, e a mensagem para a API,
 * que não tem flashdata. Se divergirem, quem ver a API e quem usa a tela
 * recebem textos diferentes para a mesma falha.
 */
final class AuthorizationDenied extends HttpException
{
    public function __construct(string $message = 'Você não tem permissão para acessar essa área.')
    {
        parent::__construct($message, 403);
    }
}
