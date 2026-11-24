<?php

namespace Libraries\Api\Recursos;

use Libraries\Api\Schema\Schema;

final class Cliente
{
    private static ?Schema $schema = null;

    public static function schema(): Schema
    {
        return self::$schema ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio()->exemplo(7),
            'nome' => Schema::texto()->obrigatorio()->exemplo('Ana Souza'),
            'tipo_pessoa' => Schema::enum(['fisica', 'juridica'])->obrigatorio()->descricao('Deduzido do documento: 11 dígitos é pessoa física.'),
            'documento' => Schema::texto()->anulavel()->obrigatorio()->exemplo('529.982.247-25'),
            'fornecedor' => Schema::booleano()->obrigatorio(),
            'contato' => Schema::texto()->anulavel()->obrigatorio(),
            'email' => Schema::texto()->anulavel()->obrigatorio()->exemplo('ana@exemplo.com'),
            'telefone' => Schema::texto()->anulavel()->obrigatorio(),
            'celular' => Schema::texto()->anulavel()->obrigatorio(),
            'endereco' => Endereco::schema()->obrigatorio(),
            'data_cadastro' => Schema::data()->anulavel()->obrigatorio(),
        ])->nome('Cliente');
    }

    public static function de(object $linha): array
    {
        return [
            'id' => (int) $linha->idClientes,
            'nome' => (string) $linha->nomeCliente,
            'tipo_pessoa' => (int) ($linha->pessoa_fisica ?? 1) === 1 ? 'fisica' : 'juridica',
            'documento' => Valor::textoOuNulo($linha->documento ?? null),
            'fornecedor' => (int) ($linha->fornecedor ?? 0) === 1,
            'contato' => Valor::textoOuNulo($linha->contato ?? null),
            'email' => Valor::textoOuNulo($linha->email ?? null),
            'telefone' => Valor::textoOuNulo($linha->telefone ?? null),
            'celular' => Valor::textoOuNulo($linha->celular ?? null),
            'endereco' => Endereco::de($linha->cep ?? null, $linha->rua ?? null, $linha->numero ?? null, $linha->bairro ?? null, $linha->cidade ?? null, $linha->estado ?? null, $linha->complemento ?? null),
            'data_cadastro' => Valor::data($linha->dataCadastro ?? null),
        ];
    }

    public static function referencia(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'nome' => Schema::texto()->obrigatorio(),
            'documento' => Schema::texto()->anulavel()->obrigatorio(),
            'email' => Schema::texto()->anulavel()->obrigatorio(),
            'telefone' => Schema::texto()->anulavel()->obrigatorio(),
            'celular' => Schema::texto()->anulavel()->obrigatorio(),
        ])->nome('ClienteResumo');
    }

    public static function referenciaDe(int $id, mixed $nome, mixed $documento = null, mixed $email = null, mixed $telefone = null, mixed $celular = null): array
    {
        return [
            'id' => $id,
            'nome' => (string) $nome,
            'documento' => Valor::textoOuNulo($documento),
            'email' => Valor::textoOuNulo($email),
            'telefone' => Valor::textoOuNulo($telefone),
            'celular' => Valor::textoOuNulo($celular),
        ];
    }
}
