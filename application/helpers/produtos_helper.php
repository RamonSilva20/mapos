<?php

/**
 * Regras do cadastro de produtos (#2841), fora do controller para serem
 * testadas.
 */
if (! function_exists('unidadesDeMedida')) {
    /**
     * Unidades de medida, sigla => "SIGLA — descrição", da tabela que a v4
     * carregava por AJAX (assets/json/tabela_medidas.json).
     *
     * @return array<string, string>
     */
    function unidadesDeMedida(): array
    {
        static $unidades = null;

        if ($unidades === null) {
            $raiz = defined('FCPATH') ? FCPATH : dirname(APPPATH) . DIRECTORY_SEPARATOR;
            $dados = json_decode((string) @file_get_contents($raiz . 'assets/json/tabela_medidas.json'), true);
            $unidades = [];
            foreach ((array) ($dados['medidas'] ?? []) as $medida) {
                $sigla = trim((string) ($medida['sigla'] ?? ''));
                if ($sigla !== '') {
                    $descricao = trim((string) ($medida['descricao'] ?? ''));
                    $unidades[$sigla] = $descricao !== '' && strcasecmp($descricao, $sigla) !== 0 ? $sigla . ' — ' . mb_convert_case($descricao, MB_CASE_TITLE, 'UTF-8') : $sigla;
                }
            }
        }

        return $unidades;
    }
}

if (! function_exists('produtoDadosDoFormulario')) {
    /**
     * Dados da tabela produtos a partir do POST já validado. Os preços já vêm
     * convertidos por valorDecimal(); estoques são inteiros não negativos.
     *
     * @param  mixed  $post  $this->input->post()
     */
    function produtoDadosDoFormulario($post, string $precoCompra, string $precoVenda): array
    {
        $post = is_array($post) ? $post : [];
        $texto = static fn (string $campo): string => is_string($post[$campo] ?? null) ? trim($post[$campo]) : '';
        $inteiro = static fn (string $campo): int => max(0, (int) $texto($campo));

        return [
            'codDeBarra' => $texto('codDeBarra'),
            'descricao' => $texto('descricao'),
            'unidade' => $texto('unidade'),
            'precoCompra' => $precoCompra,
            'precoVenda' => $precoVenda,
            'estoque' => $inteiro('estoque'),
            'estoqueMinimo' => $texto('estoqueMinimo') === '' ? null : $inteiro('estoqueMinimo'),
            'entrada' => empty($post['entrada']) ? 0 : 1,
            'saida' => empty($post['saida']) ? 0 : 1,
        ];
    }
}

if (! function_exists('produtoEstoqueBaixo')) {
    /** No mínimo ou abaixo dele, com mínimo definido. */
    function produtoEstoqueBaixo(object $produto): bool
    {
        $minimo = (int) ($produto->estoqueMinimo ?? 0);

        return $minimo > 0 && (int) $produto->estoque <= $minimo;
    }
}
