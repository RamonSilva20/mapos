<?php

namespace Libraries\Api\Recursos;

use Libraries\Api\Schema\Schema;

final class Endereco
{
    private static ?Schema $schema = null;

    private static ?Schema $entrada = null;

    public static function schema(): Schema
    {
        return self::$schema ??= Schema::objeto([
            'cep' => Schema::texto()->anulavel()->obrigatorio()->exemplo('01310-100'),
            'rua' => Schema::texto()->anulavel()->obrigatorio()->exemplo('Av. Paulista'),
            'numero' => Schema::texto()->anulavel()->obrigatorio()->exemplo('1000'),
            'complemento' => Schema::texto()->anulavel(),
            'bairro' => Schema::texto()->anulavel()->obrigatorio()->exemplo('Bela Vista'),
            'cidade' => Schema::texto()->anulavel()->obrigatorio()->exemplo('São Paulo'),
            'estado' => Schema::texto()->anulavel()->obrigatorio()->exemplo('SP'),
        ])->nome('Endereco');
    }

    public static function entrada(bool $obrigatorio = false): Schema
    {
        $schema = Schema::objeto([
            'cep' => Schema::texto()->max(20)->obrigatorio($obrigatorio),
            'rua' => Schema::texto()->max(70)->obrigatorio($obrigatorio),
            'numero' => Schema::texto()->max(15)->obrigatorio($obrigatorio),
            'complemento' => Schema::texto()->max(45),
            'bairro' => Schema::texto()->max(45)->obrigatorio($obrigatorio),
            'cidade' => Schema::texto()->max(45)->obrigatorio($obrigatorio),
            'estado' => Schema::texto()->max(20)->obrigatorio($obrigatorio),
        ]);

        return $obrigatorio ? $schema->nome('EnderecoObrigatorioEntrada') : (self::$entrada ??= $schema->nome('EnderecoEntrada'));
    }

    public static function de(?string $cep, ?string $rua, ?string $numero, ?string $bairro, ?string $cidade, ?string $estado, ?string $complemento = null): array
    {
        $endereco = [
            'cep' => Valor::textoOuNulo($cep),
            'rua' => Valor::textoOuNulo($rua),
            'numero' => Valor::textoOuNulo($numero),
            'bairro' => Valor::textoOuNulo($bairro),
            'cidade' => Valor::textoOuNulo($cidade),
            'estado' => Valor::textoOuNulo($estado),
        ];
        if (func_num_args() > 6) {
            $endereco['complemento'] = Valor::textoOuNulo($complemento);
        }

        return $endereco;
    }

    /** @return array<string, string> */
    public static function paraBanco(array $endereco, bool $comComplemento = false): array
    {
        $dados = [];
        foreach (['cep', 'rua', 'numero', 'bairro', 'cidade', 'estado'] as $campo) {
            $dados[$campo] = (string) ($endereco[$campo] ?? '');
        }
        if ($comComplemento) {
            $dados['complemento'] = (string) ($endereco['complemento'] ?? '');
        }

        return $dados;
    }
}
