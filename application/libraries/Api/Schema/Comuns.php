<?php

namespace Libraries\Api\Schema;

final class Comuns
{
    private static array $cache = [];

    public static function paginacao(): Schema
    {
        return self::$cache['paginacao'] ??= Schema::objeto([
            'pagina' => Schema::inteiro()->obrigatorio()->exemplo(1),
            'por_pagina' => Schema::inteiro()->obrigatorio()->exemplo(20),
            'total' => Schema::inteiro()->obrigatorio()->exemplo(135),
            'paginas' => Schema::inteiro()->obrigatorio()->exemplo(7),
        ])->nome('Paginacao');
    }

    public static function erro(): Schema
    {
        return self::$cache['erro'] ??= Schema::objeto([
            'type' => Schema::texto()->obrigatorio()->exemplo('urn:mapos:erro:validacao'),
            'title' => Schema::texto()->obrigatorio()->exemplo('Dados inválidos'),
            'status' => Schema::inteiro()->obrigatorio()->exemplo(422),
            'codigo' => Schema::texto()->obrigatorio()->descricao('Código estável do erro, para o app decidir o que fazer.')->exemplo('validacao'),
            'detail' => Schema::texto()->exemplo('Confira os campos informados.'),
            'erros' => Schema::mapa(Schema::texto())->descricao('Mensagem por campo. Campos aninhados usam ponto e índice: itens[0].quantidade.')->exemplo(['email' => 'Informe um e-mail válido.']),
        ])->descricao('Erro no formato RFC 9457 (application/problem+json).')->nome('Erro');
    }

    public static function paginacaoConsulta(array $extras = []): Schema
    {
        return Schema::objeto(array_merge([
            'pagina' => Schema::inteiro()->min(1)->padrao(1)->descricao('Página, começando em 1.'),
            'por_pagina' => Schema::inteiro()->min(1)->max(100)->padrao(20)->descricao('Itens por página, até 100.'),
        ], $extras));
    }

    public static function referencia(string $rotulo = 'nome'): Schema
    {
        return Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            $rotulo => Schema::texto()->obrigatorio(),
        ]);
    }

    public static function pessoa(): Schema
    {
        return self::$cache['pessoa'] ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'nome' => Schema::texto()->obrigatorio(),
        ])->nome('Referencia');
    }

    public static function mensagem(): Schema
    {
        return self::$cache['mensagem'] ??= Schema::objeto([
            'mensagem' => Schema::texto()->obrigatorio(),
        ])->nome('Mensagem');
    }
}
