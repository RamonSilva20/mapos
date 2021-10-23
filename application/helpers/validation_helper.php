<?php

use Piggly\Pix\Parser;

if (!function_exists('multiplica_cnpj')) {
    function multiplica_cnpj($cnpj, $posicao = 5)
    {
        // Variável para o cálculo
        $calculo = 0;

        // Laço para percorrer os item do cnpj
        for ($i = 0; $i < strlen($cnpj); $i++) {
            // Cálculo mais posição do CNPJ * a posição
            $calculo = $calculo + ($cnpj[$i] * $posicao);

            // Decrementa a posição a cada volta do laço
            $posicao--;

            // Se a posição for menor que 2, ela se torna 9
            if ($posicao < 2) {
                $posicao = 9;
            }
        }
        // Retorna o cálculo
        return $calculo;
    }
}

if (!function_exists('valid_cnpj')) {
    function valid_cnpj($cnpj)
    {
        // Deixa o CNPJ com apenas números
        // $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Garante que o CNPJ é uma string
        //$cnpj = (string) $cnpj;

        // O valor original
        $cnpj_original = $cnpj;

        // Captura os primeiros 12 números do CNPJ
        $primeiros_numeros_cnpj = substr($cnpj, 0, 12);

        // Faz o primeiro cálculo
        $primeiro_calculo = multiplica_cnpj($primeiros_numeros_cnpj);

        // Se o resto da divisão entre o primeiro cálculo e 11 for menor que 2, o primeiro
        // Dígito é zero (0), caso contrário é 11 - o resto da divisão entre o cálculo e 11
        $primeiro_digito = ($primeiro_calculo % 11) < 2 ? 0 : 11 - ($primeiro_calculo % 11);

        // Concatena o primeiro dígito nos 12 primeiros números do CNPJ
        // Agora temos 13 números aqui
        $primeiros_numeros_cnpj .= $primeiro_digito;

        // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
        $segundo_calculo = multiplica_cnpj($primeiros_numeros_cnpj, 6);
        $segundo_digito = ($segundo_calculo % 11) < 2 ? 0 : 11 - ($segundo_calculo % 11);

        // Concatena o segundo dígito ao CNPJ
        $cnpj = $primeiros_numeros_cnpj . $segundo_digito;

        // Verifica se o CNPJ gerado é idêntico ao enviado
        if ($cnpj === $cnpj_original) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('valid_cpf')) {
    function valid_cpf($cpf)
    {
        // Extrai somente os números
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('verific_cpf_cnpj')) {
    function verific_cpf_cnpj($cpfCnpjValor)
    {
        $cpfCnpj = preg_replace('/[^0-9]/', '', $cpfCnpjValor);
        $cpfCnpj = (string) $cpfCnpj;

        if (strlen($cpfCnpj) === 11) {
            return valid_cpf($cpfCnpj);
        }

        if (strlen($cpfCnpj) === 14) {
            return valid_cnpj($cpfCnpj);
        }

        return false;
    }
}

if (!function_exists('unique')) {
    function unique($value, $params)
    {
        $CI = &get_instance();
        $CI->load->database();

        $CI->form_validation->set_message('unique', "O campo %s já está cadastrado.");

        list($table, $field, $current_id, $key) = explode(".", $params);

        $query = $CI->db->select()->from($table)->where($field, $value)->limit(1)->get();

        if ($query->row() && $query->row()->{$key} != $current_id) {
            return false;
        } else {
            return true;
        }
    }
}

if (!function_exists('valid_pix_key')) {
    function valid_pix_key($value)
    {
        if (Parser::validateDocument($value)) {
            return true;
        }

        if (Parser::validateEmail($value)) {
            return true;
        }

        if (Parser::validatePhone($value)) {
            return true;
        }

        if (Parser::validateRandom($value)) {
            return true;
        }

        return false;
    }
}
