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

if (! defined('OS_CAMPOS_TEXTO')) {
    // Textos livres da OS. Na v4 eram editados com o Trumbowyg e guardavam HTML.
    define('OS_CAMPOS_TEXTO', ['descricaoProduto', 'defeito', 'observacoes', 'laudoTecnico']);
}

if (! function_exists('osTextoParaEdicao')) {
    /**
     * Texto de um campo livre da OS para a textarea do formulário: as quebras
     * (<br>, fim de parágrafo e de item) viram linhas, as tags somem e as
     * entidades são decodificadas. Serve tanto ao HTML do editor antigo quanto
     * ao que o formulário novo grava (osTextoParaGravar()).
     */
    function osTextoParaEdicao(?string $html): string
    {
        $texto = str_replace(["\r\n", "\r"], "\n", (string) $html);
        $texto = (string) preg_replace('#<br\s*/?>\n?#i', "\n", $texto);
        $texto = (string) preg_replace('#</(p|div|li|h[1-6])>\n?#i', "\n", $texto);
        $texto = html_entity_decode(strip_tags($texto), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = (string) preg_replace("/[ \t]+\n/", "\n", $texto);
        $texto = (string) preg_replace("/\n{3,}/", "\n\n", $texto);

        return trim($texto);
    }
}

if (! function_exists('osTextoParaGravar')) {
    /**
     * Texto digitado na textarea, no formato que o banco já guarda: HTML com o
     * texto escapado e <br> nas quebras de linha. Assim as telas que exibem
     * esses campos com printSafeHtml() (visualizar, impressão, e-mail, área do
     * cliente) continuam mostrando as linhas, e nenhuma tag digitada vira HTML.
     *
     * Só & < > são escapados: aspas não são perigosas fora de atributos e
     * apareceriam como entidade no texto do WhatsApp, que usa strip_tags().
     */
    function osTextoParaGravar(?string $texto): string
    {
        $texto = trim(str_replace(["\r\n", "\r"], "\n", (string) $texto));
        if ($texto === '') {
            return '';
        }

        return str_replace("\n", "<br>\n", htmlspecialchars($texto, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }
}

if (! function_exists('osTemFormatacao')) {
    /**
     * Diz se o campo guarda formatação do editor antigo (negrito, listas...),
     * que se perde ao salvar pelo formulário novo. <br> não conta: é só quebra
     * de linha e continua preservado.
     */
    function osTemFormatacao(?string $html): bool
    {
        return (bool) preg_match('#<(?!br\s*/?>)[a-z][^>]*>#i', (string) $html);
    }
}

if (! function_exists('osDataParaCampo')) {
    /** Data do banco (DATE) para o input type=date; vazia ou zerada vira ''. */
    function osDataParaCampo(?string $data): string
    {
        return dataIsoParaYmd(substr((string) $data, 0, 10)) ?? '';
    }
}

if (! function_exists('osDadosDoFormulario')) {
    /**
     * Converte e confere o POST do formulário da OS, depois das regras do
     * form_validation (grupo "os"): datas em AAAA-MM-DD (input type=date),
     * status entre os da v4, ids escolhidos nos autocompletes e textos no
     * formato de osTextoParaGravar(). Só lê os campos do formulário.
     *
     * Os erros vêm pelo nome do campo visível (cliente, tecnico,
     * termoGarantia...), para a tela marcar o campo certo.
     *
     * @param  array<string, mixed>  $post
     * @return array{0: array<string, mixed>, 1: array<string, string>}  [dados, erros]
     */
    function osDadosDoFormulario(array $post): array
    {
        $erros = [];
        $texto = static fn (string $campo): string => is_scalar($post[$campo] ?? null) ? trim((string) $post[$campo]) : '';
        $id = static fn (string $campo): ?int => ctype_digit($texto($campo)) && (int) $texto($campo) > 0 ? (int) $texto($campo) : null;

        $dataInicial = dataIsoParaYmd($texto('dataInicial'));
        $dataFinal = dataIsoParaYmd($texto('dataFinal'));
        if ($dataInicial === null) {
            $erros['dataInicial'] = 'Informe uma data válida.';
        }
        if ($dataFinal === null) {
            $erros['dataFinal'] = 'Informe uma data válida.';
        } elseif ($dataInicial !== null && $dataFinal < $dataInicial) {
            $erros['dataFinal'] = 'A data final não pode ser anterior à data inicial.';
        }

        $status = $texto('status');
        if (! array_key_exists($status, OS_STATUS_VARIANTES)) {
            $erros['status'] = 'Escolha um status da lista.';
        }

        $clienteId = $id('clientes_id');
        if ($clienteId === null) {
            $erros['cliente'] = 'Escolha um cliente da lista.';
        }

        $tecnicoId = $id('usuarios_id');
        if ($tecnicoId === null) {
            $erros['tecnico'] = 'Escolha um técnico da lista.';
        }

        $garantiaId = $id('garantias_id');
        if ($garantiaId === null && $texto('termoGarantia') !== '') {
            $erros['termoGarantia'] = 'Escolha um termo da lista ou deixe o campo em branco.';
        }

        $garantia = $texto('garantia');
        if ($garantia !== '' && (! ctype_digit($garantia) || (int) $garantia > 9999)) {
            $erros['garantia'] = 'Informe a garantia em dias, de 0 a 9999.';
        }

        $dados = [
            'clientes_id' => $clienteId,
            'usuarios_id' => $tecnicoId,
            'status' => $status,
            'dataInicial' => $dataInicial,
            'dataFinal' => $dataFinal,
            'garantia' => $garantia === '' ? '' : (string) (int) $garantia,
            'garantias_id' => $garantiaId,
        ];
        foreach (OS_CAMPOS_TEXTO as $campo) {
            $dados[$campo] = osTextoParaGravar(is_scalar($post[$campo] ?? null) ? (string) $post[$campo] : '');
        }

        return [$dados, $erros];
    }
}

if (! function_exists('osValoresDoFormulario')) {
    /**
     * Valores dos campos do formulário: o POST quando a tela volta com erro, a
     * OS ao editar ou os padrões ao cadastrar.
     *
     * @param  array<string, mixed>|null  $post
     * @param  array<string, string>      $padrao  Valores de uma OS nova (técnico logado, data de hoje...)
     * @return array<string, string>
     */
    function osValoresDoFormulario(?array $post, ?object $os, array $padrao = []): array
    {
        $campos = ['cliente', 'clientes_id', 'tecnico', 'usuarios_id', 'status', 'dataInicial', 'dataFinal', 'garantia', 'termoGarantia', 'garantias_id', ...OS_CAMPOS_TEXTO];

        if ($post !== null) {
            $valores = [];
            foreach ($campos as $campo) {
                $valores[$campo] = is_scalar($post[$campo] ?? null) ? trim((string) $post[$campo]) : '';
            }

            return $valores;
        }

        if ($os !== null) {
            $valores = [
                'cliente' => (string) ($os->nomeCliente ?? ''),
                'clientes_id' => (string) ($os->clientes_id ?? ''),
                'tecnico' => (string) ($os->nome ?? ''),
                'usuarios_id' => (string) ($os->usuarios_id ?? ''),
                'status' => (string) ($os->status ?? ''),
                'dataInicial' => osDataParaCampo($os->dataInicial ?? null),
                'dataFinal' => osDataParaCampo($os->dataFinal ?? null),
                'garantia' => (string) ($os->garantia ?? ''),
                'termoGarantia' => (string) ($os->refGarantia ?? ''),
                'garantias_id' => (string) ($os->garantias_id ?? ''),
            ];
            foreach (OS_CAMPOS_TEXTO as $campo) {
                $valores[$campo] = osTextoParaEdicao($os->{$campo} ?? null);
            }

            return $valores;
        }

        return array_merge(array_fill_keys($campos, ''), $padrao);
    }
}
