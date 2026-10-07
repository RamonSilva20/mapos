<?php

namespace Tests\Support\Database;

use RuntimeException;

/**
 * As recusas que impedem a suíte de fazer estrago, e as regras de nome.
 *
 * Estas funções não tocam no banco: elas dizem não, ou calculam um nome, e é por
 * isso que não dependem de conexão nenhuma. Ficam fora de TestDatabase porque a
 * guarda é o que a suíte não pode perder: um `exit()` dentro da classe que também
 * abre conexão é um `exit()` que ninguém acha quando a conexão some.
 *
 * Recusar é lançar exceção, e não sair. `exit()` num script de linha de comando
 * funciona, e é o que se espera, mas mata o processo inteiro: um `exit()` dentro de
 * uma asserção não pode ser capturado, então a função não pode ser testada, e uma
 * função de guarda que não pode ser testada não é uma guarda, é um costume. Quem
 * chama nos scripts de `bin/` é que traduz a exceção em `fwrite(STDERR)` e
 * `exit(1)`, e essa tradução fica onde a interface de linha de comando está.
 *
 * A regra do nome seguro de banco é a premissa das outras. O check-schema-parity.php
 * e o drop-worker-databases.php autenticam com privilégio para recriar bancos, e
 * sem o sufixo `_test` não há nada que os impeça de recriar o banco de
 * desenvolvimento.
 */
final class DatabaseGuard
{
    /**
     * Só pode ser carregado pela linha de comando.
     *
     * A suíte mora dentro da raiz do documento e a pasta de testes é servida pelo
     * nginx, que não lê .htaccess. Os scripts daqui reconstroem um banco, então
     * precisam recusar qualquer execução vinda da web.
     *
     * @throws RuntimeException se o processo não for de linha de comando
     */
    public static function assertCommandLine(): void
    {
        if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
            throw new RuntimeException('No direct script access allowed');
        }
    }

    /**
     * A suíte recria o banco a cada execução, e o install/do_install.php reconstrói
     * o banco de produção. Esta é a única barreira contra um teste apagar o banco de
     * desenvolvimento.
     *
     * São DUAS exigências, e as duas são necessárias: o nome termina em `_test`, e o
     * nome é um identificador. O sufixo é o que separa o banco de teste do banco de
     * desenvolvimento, e o identificador é o que torna o nome seguro de concatenar,
     * porque o nome entra entre crases num `DROP DATABASE`, num `CREATE DATABASE` e
     * num qualificado `banco`.`tabela` — e uma crase, um espaço, uma barra ou um
     * ponto-e-vírgula no meio do nome fecham a instrução antes do fim.
     *
     * Só o sufixo não bastava, e essa é a diferença entre a guarda e o que ela
     * documentava ser. `../../x_test` termina em `_test` e escapava do diretório de
     * impressões digitais; `x`;DROP DATABASE mapos;-- _test` também, e chegava ao
     * `exec()` com o privilégio de recriação que o próprio script tem. Ambos
     * exigem controle da variável de ambiente e não de uma requisição, e por isso a
     * consequência é limitada e não um XSrf — mas a única coisa entre o banco de
     * desenvolvimento e este nome é esta guarda, e uma guarda que documenta uma
     * força e tem outra é pior do que uma que não documenta nada.
     *
     * (A suíte NÃO importa banco.sql: o `banco.sql` tem `CREATE TABLE IF NOT
     * EXISTS` sem nenhum `DROP`, e importá-lo nunca exercita uma linha de migration.
     * Quem reconstrói o schema de teste é a cadeia de migrations, via
     * `TestApplication::migrate()`. Ver AGENTS.md.)
     *
     * @throws RuntimeException se o nome não terminar em '_test' ou não for identificador
     */
    public static function assertDatabaseNameIsSafe(string $name): void
    {
        if (preg_match('/_test$/', $name) === 1 && self::isSafeIdentifier($name)) {
            return;
        }

        throw new RuntimeException(
            "O banco de testes precisa terminar em '_test' E ser um identificador "
            . "(letras, dígitos e sublinhado), recebido '{$name}'. "
            . "Defina MAPOS_TEST_DB_DATABASE com um nome válido, por exemplo mapos_test."
        );
    }

    /**
     * O nome é um identificador que pode entrar num CREATE sem aspas?
     *
     * Uma única resposta para "isto é seguro para concatenar numa instrução", e ela
     * mora aqui porque `workerDatabaseName()` e `SchemaReader::identifier()` já
     * respondiam a mesma pergunta com a mesma regex, em dois lugares. Uma correção
     * num deles não alcançava o outro, que é a forma comum de um dois-em-um virar
     * um buraco.
     *
     * O traço e o ponto ficam de fora de propósito: o que passa por aqui é o que
     * pode ser escrito entre crases num identificador, e nenhum dos dois é
     * interpretado como parte do nome.
     */
    public static function isSafeIdentifier(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9_]+$/', $value) === 1;
    }

    /**
     * A base do modelo, sem o sufixo '_test'.
     *
     * O mesmo cálculo que `workerDatabaseName()` faz, isolado porque o
     * drop-worker-databases.php precisa dele para montar o filtro da consulta sem
     * duplicar a regra. Recriar o `preg_replace` lá e aqui é como um nome passa a
     * ser esquecido por um dos lados.
     *
     * @throws RuntimeException se o modelo não tiver base aproveitável
     */
    public static function modelBase(string $template): string
    {
        $base = preg_replace('/_test$/', '', $template);

        if ($base === null || $base === '') {
            throw new RuntimeException(
                "O nome do modelo '{$template}' não tem base aproveitável depois de tirar o "
                . "sufixo '_test'."
            );
        }

        return $base;
    }

    /**
     * O nome do banco de um worker, a partir do nome do modelo.
     *
     * O token entra ANTES do sufixo `_test`, e não depois. A razão é a guarda:
     * assertDatabaseNameIsSafe() exige terminar em `_test`, e ela não é
     * negociável porque é a única coisa entre um teste e o banco de
     * desenvolvimento. Um `mapos_test_1` seria recusado por ela, e o motivo
     * dessa recusa é o motivo de o token estar aqui dentro.
     *
     * Função pura e pública para poder ser testada sem banco: a decisão é
     * pequena, e um nome errado aqui é um worker apontando para o banco de
     * outro, que é a falha de isolamento voltando pela porta da frente — e ela
     * passaria a suíte, porque dois processos no mesmo banco só se denunciam na
     * contenção de lock, que é intermitente por definição.
     *
     * @throws RuntimeException se o token não couber num nome de banco
     */
    public static function workerDatabaseName(string $template, string $token): string
    {
        // Recusar, e não sanitizar. Sanitizar mapearia '1-a' e '1_a' para o
        // mesmo banco, e dois workers no mesmo banco é exatamente a linha
        // contestada que o clone existe para evitar — criada em silêncio por uma
        // troca de caractere. O ParaTest emite um inteiro, então a recusa nunca
        // dispara na prática; se disparar, é porque o token veio de outro lugar,
        // e a mensagem diz.
        if (! self::isSafeIdentifier($token)) {
            throw new RuntimeException(
                "O TEST_TOKEN '{$token}' tem caractere que não pode entrar num nome de banco. "
                . 'Aceito apenas letras, dígitos e sublinhado. O token é recusado em vez de '
                . 'sanitizado porque dois tokens que sanitizam para o mesmo nome seriam dois '
                . 'workers no mesmo banco, e a suíte só perceberia isso quando um deles esperasse '
                . 'o lock do outro.'
            );
        }

        $name = self::modelBase($template) . '_' . $token . '_test';

        // 64 é o limite do MySQL para identificador, e o nome entra em CREATE
        // DATABASE e em information_schema. Um nome que não cabe seria truncado
        // pelo servidor, e truncar 'mapos_x_12_test' pode virar 'mapos_x_1_test'
        // — dois workers no mesmo banco de novo.
        if (strlen($name) > 64) {
            throw new RuntimeException(
                "O nome do worker '{$name}' tem " . strlen($name) . ' caracteres e o MySQL aceita 64. '
                . 'Encurte MAPOS_TEST_DB_DATABASE.'
            );
        }

        return $name;
    }

    /**
     * O nome de um banco de worker, com o token resolvido por quem busca.
     *
     * workerDatabaseName() exige o token na mão; esta é a forma de chamada que os
     * scripts e o clone usam de verdade: o token do ParaTest quando existe, e
     * 'solo' quando não. O `?? 'solo'` aparecia copiado em cinco arquivos, e cada
     * cópia era uma chance de um deles derivar o token de um jeito diferente e
     * dois processos brigarem pelo mesmo banco sem ninguém ver.
     */
    public static function workerName(string $template): string
    {
        return self::workerDatabaseName($template, TestDatabase::parallelToken() ?? 'solo');
    }

    /**
     * Este nome é o de um worker derivado DESTE modelo?
     *
     * A conferida é o que separa o drop-worker-databases.php de um `DROP` preguiçoso,
     * e ela é feita refazendo o nome em vez de comparando com um padrão escrito
     * aqui. Passar por esta função significa que nada é apagado por inferência: só
     * sai daqui um nome que o próprio código de clonagem sabe produzir. Um banco de
     * teste que alguém tenha criado com cara parecida é ignorado e relatado, para
     * quem o criou decidir.
     *
     * Comparar com um padrão duplicaria a regra do token, e uma regra duplicada
     * diverge no dia em que alguém aceita um caractere novo no token — que é
     * exatamente quando este script passaria a apagar o banco errado sem reclamar.
     *
     * A pergunta é "este nome é meu?", e não "este nome parece com o de um worker?":
     * com um modelo `mapos_test`, um padrão como `/_\d+_test$/` também casaria com
     * `financeiro_2_test`, que é de outro dono e de outro branch.
     */
    public static function isWorkerDatabaseName(string $name, string $template): bool
    {
        // O token é o que está entre a base e o sufixo. Recusar aqui é a mesma
        // resposta que `workerDatabaseName()` dá, e é a que interessa: um token que
        // ela recusaria é um token que ela não teria gerado.
        $prefix = self::modelBase($template) . '_';
        $suffix = '_test';

        if (! str_starts_with($name, $prefix) || ! str_ends_with($name, $suffix)) {
            return false;
        }

        $token = substr($name, strlen($prefix), -strlen($suffix));

        if ($token === '') {
            return false;
        }

        try {
            return self::workerDatabaseName($template, $token) === $name;
        } catch (RuntimeException) {
            return false;
        }
    }
}
