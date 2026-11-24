<?php

namespace Libraries\Api\Recursos;

final class Valor
{
    public static function textoOuNulo(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }
        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }

    public static function data(mixed $valor): ?string
    {
        $texto = trim((string) $valor);
        if ($texto === '' || str_starts_with($texto, '0000-00-00')) {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}/', $texto) ? substr($texto, 0, 10) : null;
    }

    public static function dataHora(mixed $valor): ?string
    {
        $texto = trim((string) $valor);
        if ($texto === '' || str_starts_with($texto, '0000-00-00')) {
            return null;
        }
        $data = date_create($texto);

        return $data === false ? null : $data->format(DATE_ATOM);
    }

    public static function decimal(mixed $valor, int $casas = 2): string
    {
        return number_format(is_numeric($valor) ? (float) $valor : 0.0, $casas, '.', '');
    }

    public static function decimalOuNulo(mixed $valor, int $casas = 2): ?string
    {
        return $valor === null || $valor === '' ? null : self::decimal($valor, $casas);
    }

    public static function inteiroOuNulo(mixed $valor): ?int
    {
        return $valor === null || $valor === '' || ! is_numeric($valor) ? null : (int) $valor;
    }

    public static function texto(mixed $valor): string
    {
        return trim((string) $valor);
    }

    public static function textoDoEditor(mixed $valor): string
    {
        return osTextoParaEdicao((string) $valor);
    }
}
