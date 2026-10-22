<?php

/**
 * Regras do formulário de cliente (#2851), fora do controller para serem
 * testadas: o que vai para o banco e o que a tela mostra.
 */
if (! defined('CLIENTE_CAMPOS_TEXTO')) {
    define('CLIENTE_CAMPOS_TEXTO', [
        'nomeCliente', 'contato', 'documento', 'telefone', 'celular', 'email',
        'cep', 'rua', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
    ]);
}

if (! function_exists('clienteDadosDoFormulario')) {
    /**
     * Dados da tabela clientes a partir do POST já validado.
     *
     * - pessoa_fisica: CPF tem 11 caracteres; o resto é CNPJ.
     * - senha: a digitada; num cadastro novo sem senha, o CPF/CNPJ sem
     *   pontuação (como na v4, e a área do cliente avisa); sem documento, uma
     *   senha aleatória que ninguém conhece. Na edição, sem senha, não muda.
     * - estado: só uma UF válida.
     *
     * @param  mixed  $post  $this->input->post()
     */
    function clienteDadosDoFormulario($post, bool $novo): array
    {
        $post = is_array($post) ? $post : [];
        $texto = static fn (string $campo): string => is_string($post[$campo] ?? null) ? trim($post[$campo]) : '';

        $dados = [];
        foreach (CLIENTE_CAMPOS_TEXTO as $campo) {
            $dados[$campo] = $texto($campo);
        }

        $dados['estado'] = array_key_exists(strtoupper($dados['estado']), ufsDoBrasil()) ? strtoupper($dados['estado']) : '';
        $documento = (string) preg_replace('/[^A-Za-z0-9]/', '', $dados['documento']);
        $dados['pessoa_fisica'] = strlen($documento) === 11 ? 1 : 0;
        $dados['fornecedor'] = empty($post['fornecedor']) ? 0 : 1;

        $senha = $texto('senha');
        if ($senha !== '') {
            $dados['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        } elseif ($novo) {
            $dados['senha'] = password_hash($documento !== '' ? $documento : bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        }

        return $dados;
    }
}

if (! function_exists('clienteValoresDoFormulario')) {
    /**
     * Valores dos campos na tela: o que foi enviado (quando a tela volta com
     * erro), senão o cliente em edição, senão vazio. A senha nunca volta.
     *
     * @param  mixed        $post     $this->input->post(), ou null fora de um POST
     * @param  object|null  $cliente
     * @return array<string, string|bool>
     */
    function clienteValoresDoFormulario($post, ?object $cliente): array
    {
        $valores = [];
        foreach (CLIENTE_CAMPOS_TEXTO as $campo) {
            if (is_array($post)) {
                $valores[$campo] = is_string($post[$campo] ?? null) ? $post[$campo] : '';
            } else {
                $valores[$campo] = $cliente !== null ? (string) ($cliente->{$campo} ?? '') : '';
            }
        }

        $valores['fornecedor'] = is_array($post) ? ! empty($post['fornecedor']) : (bool) ($cliente->fornecedor ?? false);

        return $valores;
    }
}
