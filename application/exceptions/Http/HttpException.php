<?php

namespace Exceptions\Http;

use RuntimeException;

/**
 * A base das exceções que carregam um status HTTP.
 *
 * Existe por causa do `exit()`. Os guards de sessão e de permissão dos
 * controllers chamavam `redirect()`, e `redirect()` chama `exit()` — o que está
 * certo numa requisição web e é justamente o que impede a suíte in-process de
 * observar qualquer coisa: incluir o `index.php` executa o ciclo inteiro da
 * requisição, e um `exit()` no meio do caminho acaba com o processo do PHPUnit.
 * Lançar a exceção troca o `exit()` por algo que o teste consegue ver, sem
 * mudar o que sai do servidor: o `index.php` captura e devolve o mesmo redirect.
 *
 * A exceção NÃO carrega a URL de destino, e essa é a parte que costuma ser
 * feita do jeito errado. Quem decide para onde ir é o renderizador, no
 * `index.php`, porque a resposta muda com o contexto: uma sessão ausente
 * manda para o login, uma permissão ausente manda para a home. Colocar a URL na
 * exceção além de duplicar essa decisão faria cada guard escolher o seu
 * destino, e um destino escolhido por dado de requisição é um redirecionamento
 * aberto. A exceção carrega o que é uma propriedade do erro — o status — e nada
 * mais.
 *
 * O status fica na exceção, e não no renderizador, para que a resposta JSON da
 * API saia com o código certo sem o renderizador precisar saber o que cada
 * subclasse significa. As subclasses fixam o próprio status no construtor, e
 * nenhum ponto de chamada escolhe o número: um 403 escrito à mão num throw é um
 * 403 que ninguém revisa.
 */
abstract class HttpException extends RuntimeException
{
    public function __construct(string $message, private readonly int $status)
    {
        parent::__construct($message);
    }

    /**
     * O status HTTP que esta falha representa.
     *
     * Para o lado web o renderizador o ignora, porque um redirect é um 3xx. Ele
     * existe para a API, que responde com o status real em vez de redirecionar.
     */
    public function status(): int
    {
        return $this->status;
    }
}
