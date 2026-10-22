<?php

/**
 * Padrões das listagens da v5 (#2852): filtros na URL e paginação que os
 * preserva.
 *
 * No controller:
 *
 *     $filtros = listagemFiltros(['pesquisa' => 'texto', 'tipo' => ['cliente', 'fornecedor']], $this->input->get());
 *     $total = $this->clientes_model->contar($filtros);
 *     $this->data['paginacao'] = $this->paginacao(site_url('clientes/gerenciar'), $total, $this->uri->segment(3), null, $filtros);
 *
 * Os filtros ficam na query string (?pesquisa=ana&tipo=cliente): a URL é
 * compartilhável, o voltar do navegador funciona e a paginação leva os
 * filtros junto. O formulário de filtro é um GET simples, sem JavaScript.
 */
if (! function_exists('listagemFiltros')) {
    /**
     * Filtros válidos de uma listagem, a partir da query string.
     *
     * Cada filtro permitido é 'texto' (trim, sem caracteres de controle, até
     * 100 caracteres) ou a lista dos valores aceitos. Filtro desconhecido,
     * vazio, que não é string ou fora da lista é descartado.
     *
     * @param  array<string, 'texto'|list<string>>  $permitidos
     * @param  mixed                                 $entrada  Normalmente $this->input->get()
     * @return array<string, string>
     */
    function listagemFiltros(array $permitidos, $entrada): array
    {
        $entrada = is_array($entrada) ? $entrada : [];
        $filtros = [];

        foreach ($permitidos as $nome => $regra) {
            $valor = $entrada[$nome] ?? null;
            if (! is_string($valor)) {
                continue;
            }

            $valor = trim((string) preg_replace('/[\x00-\x1F\x7F]/u', '', $valor));

            if ($regra === 'texto') {
                $valor = mb_substr($valor, 0, 100);
            } elseif (! in_array($valor, $regra, true)) {
                continue;
            }

            if ($valor !== '') {
                $filtros[$nome] = $valor;
            }
        }

        return $filtros;
    }
}

if (! function_exists('listagemQuery')) {
    /**
     * Query string dos filtros ("?pesquisa=ana&tipo=cliente"), ou "" sem
     * filtros. Os valores saem codificados (RFC 3986).
     *
     * @param  array<string, string>  $filtros
     */
    function listagemQuery(array $filtros): string
    {
        $filtros = array_filter($filtros, static fn ($valor) => (string) $valor !== '');

        return $filtros === [] ? '' : '?' . http_build_query($filtros, '', '&', PHP_QUERY_RFC3986);
    }
}
