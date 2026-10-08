<?php

/**
 * Regras da OS usadas pelas telas migradas (#2842), em funções puras para
 * serem testadas sem banco nem sessão.
 */
if (! function_exists('osTextoCurto')) {
    /**
     * Resumo em texto puro de um campo que pode guardar HTML (descricaoProduto,
     * defeito... vinham do editor Trumbowyg): sem tags, entidades decodificadas,
     * espaços colapsados e cortado em $limite caracteres com reticências.
     */
    function osTextoCurto(?string $html, int $limite = 60): string
    {
        $texto = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '</li>'], ' ', (string) $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = trim((string) preg_replace('/\s+/u', ' ', $texto));

        return mb_strimwidth($texto, 0, $limite, '…', 'UTF-8');
    }
}

if (! function_exists('osEditavel')) {
    /**
     * Mesma regra de Os_model::isEditable(), a partir da linha já carregada (a
     * listagem não consulta a OS de novo por linha): exige eOs; OS faturada ou
     * cancelada só é editável com a configuração control_editos ligada.
     */
    function osEditavel(object $os, bool $podeEditar, bool $controleEditos): bool
    {
        if (! $podeEditar) {
            return false;
        }

        $fechada = ($os->status ?? null) === 'Faturado' || ($os->status ?? null) === 'Cancelado' || (int) ($os->faturado ?? 0) === 1;

        return ! $fechada || $controleEditos;
    }
}

if (! function_exists('osStatusVisiveis')) {
    /**
     * Status que a listagem mostra sem filtro de status nem pesquisa: os
     * marcados em Configurações (os_status_list, JSON). null = todos, quando a
     * configuração está vazia ou inválida.
     *
     * @return list<string>|null
     */
    function osStatusVisiveis($osStatusList): ?array
    {
        $lista = is_string($osStatusList) ? json_decode($osStatusList, true) : null;
        if (! is_array($lista)) {
            return null;
        }

        $lista = array_values(array_filter($lista, static fn ($s) => is_string($s) && $s !== ''));

        return $lista === [] ? null : $lista;
    }
}

if (! function_exists('osDescricoesDaFatura')) {
    /**
     * Descrições com que o lançamento da fatura de uma OS pode ter sido gravado.
     * O formulário de faturar da v4 sugeria "Fatura de OS Nº: X " (com espaço no
     * fim), e a exclusão procurava "Fatura de OS - #X", que nunca casava.
     *
     * @return list<string>
     */
    function osDescricoesDaFatura(int $idOs): array
    {
        return ["Fatura de OS Nº: {$idOs} ", "Fatura de OS Nº: {$idOs}", "Fatura de OS - #{$idOs}"];
    }
}

if (! function_exists('osArquivosDosAnexos')) {
    /**
     * Caminhos dos arquivos (e miniaturas) dos anexos de uma OS que podem ser
     * apagados: só os que existem e ficam dentro de $raiz (a pasta de anexos).
     * O path vem do banco; um registro adulterado não apaga nada fora dela.
     *
     * @param  iterable<object>  $anexos  Linhas da tabela anexos (path, anexo, thumb)
     * @return list<string>
     */
    function osArquivosDosAnexos(iterable $anexos, string $raiz): array
    {
        $raiz = realpath($raiz);
        if ($raiz === false) {
            return [];
        }

        $arquivos = [];
        foreach ($anexos as $anexo) {
            $pasta = rtrim((string) ($anexo->path ?? ''), '/\\');
            $candidatos = [];
            if (($anexo->anexo ?? '') !== '') {
                $candidatos[] = $pasta . DIRECTORY_SEPARATOR . basename((string) $anexo->anexo);
            }
            if (($anexo->thumb ?? '') !== '') {
                $candidatos[] = $pasta . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . basename((string) $anexo->thumb);
            }

            foreach ($candidatos as $candidato) {
                $real = realpath($candidato);
                if ($real !== false && is_file($real) && str_starts_with($real, $raiz . DIRECTORY_SEPARATOR)) {
                    $arquivos[] = $real;
                }
            }
        }

        return array_values(array_unique($arquivos));
    }
}
