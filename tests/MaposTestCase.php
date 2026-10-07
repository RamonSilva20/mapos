<?php

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base dos testes do Map-OS.
 *
 * Cada teste recebe um SQLite em memória novo e acesso a reflexão, que é como
 * as classes do CodeIgniter são testadas: elas foram escritas para viver dentro
 * do framework, então não dá para construí-las do jeito normal.
 */
abstract class MaposTestCase extends PHPUnitTestCase
{
    /** @var object Conexão do Query Builder em SQLite em memória */
    protected $db;

    protected function setUp(): void
    {
        $this->db = maposTestDatabase();
    }

    /**
     * Cria a instância de uma classe sem passar pelo construtor.
     *
     * O construtor das classes do CodeIgniter chama get_instance() e carrega o
     * banco; nos testes só o método é de interesse.
     */
    protected function makeInstance(string $className)
    {
        return (new ReflectionClass($className))->newInstanceWithoutConstructor();
    }

    /**
     * Chama um método, inclusive privado, sem depender da visibilidade.
     */
    protected function invokeMethod(object $object, string $method, array $arguments = [])
    {
        // Desde o PHP 8.1 a reflexão ignora a visibilidade, então não é preciso
        // chamar setAccessible(), que virou deprecado no 8.5.
        return (new ReflectionMethod($object, $method))->invokeArgs($object, $arguments);
    }

    /**
     * Escreve em uma propriedade privada, inclusive para injetar dependências
     * que o construtor normalmente montaria.
     */
    protected function setPrivateProperty(object $object, string $property, $value): void
    {
        (new ReflectionProperty($object, $property))->setValue($object, $value);
    }

    /**
     * Lê uma propriedade privada, inclusive para conferir o estado depois de
     * uma chamada.
     */
    protected function readPrivateProperty(object $object, string $property)
    {
        return (new ReflectionProperty($object, $property))->getValue($object);
    }

    /**
     * Objeto mínimo no formato que as classes esperam do $this->CI.
     */
    protected function fakeCiInstance($db)
    {
        return (object) ['db' => $db];
    }

    /**
     * Cria a tabela de lançamentos com um conjunto pequeno e conhecido de dados.
     */
    protected function seedLancamentos(): void
    {
        $this->db->query('CREATE TABLE lancamentos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cliente_fornecedor TEXT,
            tipo TEXT,
            baixado INTEGER,
            data_vencimento DATE
        )');

        $linhas = [
            ['Acme Ltda', 'receita', 1, '2026-01-10'],
            ['Acme Ltda', 'despesa', 0, '2026-01-20'],
            ['Outro Fornecedor', 'despesa', 0, '2026-02-15'],
            ['Beta SA', 'receita', 0, '2026-03-05'],
        ];

        foreach ($linhas as $linha) {
            $this->db->query('INSERT INTO lancamentos (cliente_fornecedor, tipo, baixado, data_vencimento) VALUES (?, ?, ?, ?)', $linha);
        }
    }

    /**
     * Roda o WHERE montado contra a tabela de lançamentos e devolve a contagem.
     */
    protected function countLancamentosWhere(string $where): int
    {
        $resultado = $this->db->query('SELECT COUNT(*) AS total FROM lancamentos WHERE ' . $where)->row();

        return (int) $resultado->total;
    }
}
