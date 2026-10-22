<?php

/**
 * Regras da venda usadas pelas telas migradas (#2843), em funções puras para
 * serem testadas sem banco nem sessão. O que é igual ao da OS (status, texto
 * livre, itens, desconto, fatura) vem do os_helper.
 */
if (! function_exists('vendaDescricoesDaFatura')) {
    /**
     * Descrições com que o lançamento da fatura de uma venda pode ter sido
     * gravado. O formulário de faturar da v4 sugeria "Fatura de Venda Nº: X "
     * (com espaço no fim), e a exclusão procurava "Fatura de Venda - #X", que
     * nunca casava.
     *
     * @return list<string>
     */
    function vendaDescricoesDaFatura(int $idVenda): array
    {
        return ["Fatura de Venda Nº: {$idVenda} ", "Fatura de Venda Nº: {$idVenda}", "Fatura de Venda - #{$idVenda}"];
    }
}

if (! function_exists('vendaGarantiaAte')) {
    /**
     * Fim da garantia (data da venda + dias de garantia) em AAAA-MM-DD, ou
     * null sem garantia (vazia, zero ou inválida) e sem data da venda válida.
     */
    function vendaGarantiaAte(?string $dataVenda, $dias): ?string
    {
        $base = dataIsoParaYmd(substr((string) $dataVenda, 0, 10));
        if ($base === null || ! is_numeric($dias) || (int) $dias <= 0) {
            return null;
        }

        return date('Y-m-d', strtotime($base . ' +' . (int) $dias . ' days'));
    }
}

if (! function_exists('vendaGarantiaPill')) {
    /**
     * Props do pill-status da garantia: vigente (success) ou vencida
     * (neutral, não é erro), sempre com a data. Sem garantia, null.
     *
     * @return array{label: string, variant: string}|null
     */
    function vendaGarantiaPill(?string $garantiaAte, ?string $hoje = null): ?array
    {
        if ($garantiaAte === null) {
            return null;
        }

        $vigente = $garantiaAte >= ($hoje ?? date('Y-m-d'));

        return ['label' => ($vigente ? 'Até ' : 'Venceu em ') . dataBr($garantiaAte), 'variant' => $vigente ? 'success' : 'neutral'];
    }
}

if (! defined('VENDA_CAMPOS_TEXTO')) {
    // Textos livres da venda. Na v4 eram editados com o Trumbowyg e guardavam HTML.
    define('VENDA_CAMPOS_TEXTO', ['observacoes', 'observacoes_cliente']);
}

if (! function_exists('vendaDadosDoFormulario')) {
    /**
     * Converte e confere o POST do formulário da venda, depois das regras do
     * form_validation (grupo "vendas"): data em AAAA-MM-DD (input type=date),
     * status entre os da v4, ids escolhidos nos autocompletes e textos no
     * formato de osTextoParaGravar(). Só lê os campos do formulário.
     *
     * Os erros vêm pelo nome do campo visível (cliente, vendedor...), para a
     * tela marcar o campo certo.
     *
     * @param  array<string, mixed>  $post
     * @return array{0: array<string, mixed>, 1: array<string, string>}  [dados, erros]
     */
    function vendaDadosDoFormulario(array $post): array
    {
        $erros = [];
        $texto = static fn (string $campo): string => is_scalar($post[$campo] ?? null) ? trim((string) $post[$campo]) : '';
        $id = static fn (string $campo): ?int => ctype_digit($texto($campo)) && (int) $texto($campo) > 0 ? (int) $texto($campo) : null;

        $dataVenda = dataIsoParaYmd($texto('dataVenda'));
        if ($dataVenda === null) {
            $erros['dataVenda'] = 'Informe uma data válida.';
        }

        $status = $texto('status');
        if (! array_key_exists($status, OS_STATUS_VARIANTES)) {
            $erros['status'] = 'Escolha um status da lista.';
        }

        $clienteId = $id('clientes_id');
        if ($clienteId === null) {
            $erros['cliente'] = 'Escolha um cliente da lista.';
        }

        $vendedorId = $id('usuarios_id');
        if ($vendedorId === null) {
            $erros['vendedor'] = 'Escolha um vendedor da lista.';
        }

        $garantia = $texto('garantia');
        if ($garantia !== '' && (! ctype_digit($garantia) || (int) $garantia > 9999)) {
            $erros['garantia'] = 'Informe a garantia em dias, de 0 a 9999.';
        }

        $dados = [
            'clientes_id' => $clienteId,
            'usuarios_id' => $vendedorId,
            'status' => $status,
            'dataVenda' => $dataVenda,
            'garantia' => $garantia === '' ? '' : (string) (int) $garantia,
        ];
        foreach (VENDA_CAMPOS_TEXTO as $campo) {
            $dados[$campo] = osTextoParaGravar(is_scalar($post[$campo] ?? null) ? (string) $post[$campo] : '');
        }

        return [$dados, $erros];
    }
}

if (! function_exists('vendaValoresDoFormulario')) {
    /**
     * Valores dos campos do formulário: o POST quando a tela volta com erro, a
     * venda ao editar ou os padrões ao cadastrar.
     *
     * @param  array<string, mixed>|null  $post
     * @param  array<string, string>      $padrao  Valores de uma venda nova (vendedor logado, data de hoje...)
     * @return array<string, string>
     */
    function vendaValoresDoFormulario(?array $post, ?object $venda, array $padrao = []): array
    {
        $campos = ['cliente', 'clientes_id', 'vendedor', 'usuarios_id', 'status', 'dataVenda', 'garantia', ...VENDA_CAMPOS_TEXTO];

        if ($post !== null) {
            $valores = [];
            foreach ($campos as $campo) {
                $valores[$campo] = is_scalar($post[$campo] ?? null) ? trim((string) $post[$campo]) : '';
            }

            return $valores;
        }

        if ($venda !== null) {
            $valores = [
                'cliente' => (string) ($venda->nomeCliente ?? ''),
                'clientes_id' => (string) ($venda->clientes_id ?? ''),
                'vendedor' => (string) ($venda->nome ?? ''),
                'usuarios_id' => (string) ($venda->usuarios_id ?? ''),
                'status' => (string) ($venda->status ?? ''),
                'dataVenda' => osDataParaCampo($venda->dataVenda ?? null),
                'garantia' => (string) ($venda->garantia ?? ''),
            ];
            foreach (VENDA_CAMPOS_TEXTO as $campo) {
                $valores[$campo] = osTextoParaEdicao($venda->{$campo} ?? null);
            }

            return $valores;
        }

        return array_merge(array_fill_keys($campos, ''), $padrao);
    }
}

if (! function_exists('vendaCalcularDesconto')) {
    /**
     * Desconto da venda calculado no servidor, com a regra e as mensagens da
     * OS (osCalcularDesconto()). Valor 0 remove o desconto.
     *
     * @return array{0: array{tipo_desconto: string|null, desconto: float, valor_desconto: float}|null, 1: array<string, string>}
     */
    function vendaCalcularDesconto(float $bruto, $tipo, $valor): array
    {
        return osCalcularDesconto($bruto, $tipo, $valor, 'venda', 'produtos');
    }
}

if (! function_exists('vendaFaturaDoFormulario')) {
    /**
     * Lançamento (receita) do faturamento da venda: a mesma regra da OS
     * (osFaturaDoFormulario()), com o vínculo lancamentos.vendas_id. Valor,
     * desconto e cliente vêm da venda e dos totais calculados no servidor.
     *
     * @param  array<string, mixed>  $post
     * @param  array{bruto: float, desconto: float, total: float}  $totais
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    function vendaFaturaDoFormulario(array $post, object $venda, array $totais, ?int $usuario): array
    {
        [$lancamento, $erros] = osFaturaDoFormulario($post, $venda, $totais, $usuario, 'produtos');
        if ($erros !== []) {
            return [[], $erros];
        }

        return [$lancamento + ['vendas_id' => (int) $venda->idVendas], []];
    }
}
