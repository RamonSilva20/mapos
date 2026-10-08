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

/*
 * Tela da OS (#2842): itens, desconto, faturamento e histórico.
 */

if (! defined('OS_FORMAS_PAGAMENTO')) {
    // Formas de pagamento do faturamento, as mesmas do formulário da v4.
    define('OS_FORMAS_PAGAMENTO', ['Dinheiro', 'Pix', 'Cartão de Crédito', 'Cartão de Débito', 'Boleto', 'Depósito', 'Cheque']);
}

if (! defined('OS_ABAS')) {
    // Abas da tela da OS (?aba=), na ordem em que aparecem.
    define('OS_ABAS', ['resumo', 'produtos', 'servicos', 'anexos', 'anotacoes', 'historico']);
}

if (! function_exists('osNumero')) {
    /**
     * Quantidade digitada (o serviço aceita fração, como horas): aceita
     * "1.5", "1,5" e "1.234,5". Vazio ou inválido devolve null. Valores em
     * dinheiro usam valorDecimal() (status_helper), no formato das colunas.
     */
    function osNumero($valor): ?float
    {
        if (! is_scalar($valor)) {
            return null;
        }

        $texto = str_replace(' ', '', trim((string) $valor));
        if ($texto === '') {
            return null;
        }

        if (str_contains($texto, ',')) {
            // Vírgula decimal: os pontos são separador de milhar.
            $texto = str_replace(['.', ','], ['', '.'], $texto);
        }

        return preg_match('/^-?\d+(\.\d+)?$/', $texto) ? (float) $texto : null;
    }
}

if (! function_exists('osItemDoFormulario')) {
    /**
     * Confere o POST de um produto ou serviço adicionado à OS e devolve os
     * dados da linha de produtos_os/servicos_os (sem o os_id). Os erros vêm
     * pelo nome do campo da tela: produto ou servico, quantidade e preco.
     *
     * $cadastro é a linha de produtos (idProdutos, estoque) ou de servicos
     * (idServicos) escolhida no autocomplete, já conferida no banco pelo
     * controller. Produto tem quantidade inteira (produtos_os.quantidade é
     * INT) e, com o controle de estoque ligado, não passa do estoque.
     *
     * @param  array<string, mixed>  $post
     * @return array{0: array<string, int|float>, 1: array<string, string>}
     */
    function osItemDoFormulario(array $post, string $tipo, ?object $cadastro, bool $controleEstoque = false): array
    {
        $produto = $tipo === 'produto';
        $erros = [];

        if ($cadastro === null) {
            $erros[$produto ? 'produto' : 'servico'] = $produto ? 'Escolha um produto da lista.' : 'Escolha um serviço da lista.';
        }

        $quantidade = osNumero($post['quantidade'] ?? null);
        if ($quantidade === null || $quantidade <= 0) {
            $erros['quantidade'] = 'Informe uma quantidade maior que zero.';
        } elseif ($produto && floor($quantidade) != $quantidade) {
            $erros['quantidade'] = 'A quantidade de produto é um número inteiro.';
        } elseif ($produto && $controleEstoque && $cadastro !== null && $quantidade > (float) $cadastro->estoque) {
            $erros['quantidade'] = 'Estoque insuficiente: há ' . (int) $cadastro->estoque . ' em estoque.';
        }

        $preco = valorDecimal($post['preco'] ?? null);
        if ($preco === null) {
            $erros['preco'] = 'Informe um preço válido, como 49,90.';
        }

        if ($erros !== []) {
            return [[], $erros];
        }

        $quantidade = $produto ? (int) $quantidade : (float) $quantidade;
        $preco = round((float) $preco, 2);

        return [[
            ($produto ? 'produtos_id' : 'servicos_id') => (int) ($produto ? $cadastro->idProdutos : $cadastro->idServicos),
            'quantidade' => $quantidade,
            'preco' => $preco,
            'subTotal' => round($preco * $quantidade, 2),
        ], []];
    }
}

if (! function_exists('osTotais')) {
    /**
     * Totais da OS a partir das somas de produtos e serviços e dos campos de
     * desconto da tabela os.
     *
     * os.valor_desconto guarda o total já com desconto (0 quando não há
     * desconto). Antes de faturar, os.desconto é o número digitado (R$ ou %)
     * e os.tipo_desconto diz qual; depois de faturar, os.desconto passa a ser
     * o valor do desconto em reais.
     *
     * @return array{produtos: float, servicos: float, bruto: float, desconto: float, total: float, tipo: string|null, informado: float}
     */
    function osTotais(float $produtos, float $servicos, $valorComDesconto = 0, $tipoDesconto = null, $descontoInformado = 0): array
    {
        $bruto = round($produtos + $servicos, 2);
        $comDesconto = round((float) $valorComDesconto, 2);
        $total = $comDesconto > 0 && $comDesconto < $bruto ? $comDesconto : $bruto;
        $temDesconto = $total < $bruto;

        return [
            'produtos' => round($produtos, 2),
            'servicos' => round($servicos, 2),
            'bruto' => $bruto,
            'desconto' => round($bruto - $total, 2),
            'total' => $total,
            'tipo' => $temDesconto ? (in_array($tipoDesconto, ['real', 'porcento'], true) ? $tipoDesconto : 'real') : null,
            'informado' => $temDesconto ? round((float) $descontoInformado, 2) : 0.0,
        ];
    }
}

if (! function_exists('osCalcularDesconto')) {
    /**
     * Desconto da OS calculado no servidor (na v4 o total com desconto vinha
     * pronto do navegador). Valor 0 remove o desconto.
     *
     * @return array{0: array{tipo_desconto: string|null, desconto: float, valor_desconto: float}|null, 1: array<string, string>}
     */
    function osCalcularDesconto(float $bruto, $tipo, $valor): array
    {
        if (! in_array($tipo, ['real', 'porcento'], true)) {
            return [null, ['tipoDesconto' => 'Escolha o tipo de desconto.']];
        }

        $numero = valorDecimal($valor);
        if ($numero === null) {
            return [null, ['desconto' => 'Informe o desconto, como 10 ou 10,50.']];
        }
        $numero = (float) $numero;

        if ($numero == 0) {
            return [['tipo_desconto' => null, 'desconto' => 0.0, 'valor_desconto' => 0.0], []];
        }

        if ($bruto <= 0) {
            return [null, ['desconto' => 'Adicione produtos ou serviços antes de dar desconto.']];
        }

        if ($tipo === 'porcento' && $numero > 100) {
            return [null, ['desconto' => 'O desconto em porcentagem vai até 100%.']];
        }

        $emReais = $tipo === 'porcento' ? round($bruto * $numero / 100, 2) : round($numero, 2);
        $total = round($bruto - $emReais, 2);

        // O banco usa valor_desconto = 0 para "sem desconto": um desconto que
        // zera (ou passa) o total não tem como ser gravado.
        if ($total <= 0) {
            return [null, ['desconto' => 'O desconto tem de ser menor que o total da OS (' . dinheiro($bruto) . ').']];
        }

        return [['tipo_desconto' => $tipo, 'desconto' => round($numero, 2), 'valor_desconto' => $total], []];
    }
}

if (! function_exists('osFaturaDoFormulario')) {
    /**
     * Lançamento (receita) do faturamento da OS. Valor, desconto e cliente
     * vêm da OS e dos totais calculados no servidor; do formulário só saem a
     * descrição, as datas, a forma de pagamento e as observações.
     *
     * @param  array<string, mixed>  $post
     * @param  array{bruto: float, desconto: float, total: float}  $totais
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    function osFaturaDoFormulario(array $post, object $os, array $totais, ?int $usuario): array
    {
        $texto = static fn (string $campo): string => is_scalar($post[$campo] ?? null) ? trim((string) $post[$campo]) : '';
        $erros = [];

        if ($totais['total'] <= 0) {
            $erros['_geral'] = 'Adicione produtos ou serviços antes de faturar.';
        }

        $descricao = $texto('descricao');
        if ($descricao === '') {
            $erros['descricao'] = 'Informe a descrição do lançamento.';
        } elseif (mb_strlen($descricao) > 255) {
            $erros['descricao'] = 'A descrição tem até 255 caracteres.';
        }

        $vencimento = dataIsoParaYmd($texto('vencimento'));
        if ($vencimento === null) {
            $erros['vencimento'] = 'Informe a data de vencimento.';
        }

        $recebido = in_array($texto('recebido'), ['1', 'on', 'true'], true);
        $recebimento = null;
        if ($recebido) {
            $recebimento = dataIsoParaYmd($texto('recebimento'));
            if ($recebimento === null) {
                $erros['recebimento'] = 'Informe a data em que foi recebido.';
            }
        }

        $forma = $texto('formaPgto');
        if (! in_array($forma, OS_FORMAS_PAGAMENTO, true)) {
            $erros['formaPgto'] = 'Escolha a forma de pagamento.';
        }

        if ($erros !== []) {
            return [[], $erros];
        }

        return [[
            'descricao' => $descricao,
            'valor' => $totais['bruto'],
            'tipo_desconto' => 'real',
            'desconto' => $totais['desconto'],
            'valor_desconto' => $totais['total'],
            'clientes_id' => (int) $os->clientes_id,
            'cliente_fornecedor' => (string) $os->nomeCliente,
            'data_vencimento' => $vencimento,
            'data_pagamento' => $recebimento,
            'baixado' => $recebido ? 1 : 0,
            'forma_pgto' => $forma,
            'tipo' => 'receita',
            'observacoes' => $texto('observacoes'),
            'usuarios_id' => $usuario,
        ], []];
    }
}

if (! function_exists('osTextoExibicao')) {
    /**
     * Texto livre da OS para exibir na tela: o que o formulário grava (texto
     * escapado com <br>) e o HTML do editor antigo passam pelo HTMLPurifier.
     */
    function osTextoExibicao(?string $html): HtmlSeguro
    {
        return html_purificado((string) $html);
    }
}

if (! function_exists('osTelefoneWhatsApp')) {
    /**
     * Número para o link do WhatsApp (wa.me): só dígitos, com o DDI 55 quando
     * vier só DDD + número. Sem número válido, null.
     */
    function osTelefoneWhatsApp(?string $telefone): ?string
    {
        $digitos = (string) preg_replace('/\D/', '', (string) $telefone);
        if (strlen($digitos) === 10 || strlen($digitos) === 11) {
            return '55' . $digitos;
        }

        return strlen($digitos) === 12 || strlen($digitos) === 13 ? $digitos : null;
    }
}

if (! function_exists('osTextoWhatsApp')) {
    /**
     * Mensagem de WhatsApp da OS (configuração notifica_whats) com os
     * marcadores trocados. Os textos da OS guardam HTML (texto escapado com
     * <br>, ou o HTML do editor antigo): viram texto puro com as quebras de
     * linha, sem entidades como &amp;lt; (na v4 o texto saía com elas).
     *
     * @param  array<string, string>  $troca  Marcador ({CLIENTE_NOME}...) => valor
     */
    function osTextoWhatsApp(?string $modelo, array $troca): string
    {
        // Cada parte vira texto puro uma vez só: limpar de novo depois da troca
        // apagaria um "<" que o próprio texto da OS tenha.
        $limpos = array_map(static fn ($valor) => osTextoParaEdicao((string) $valor), $troca);

        return strtr(osTextoParaEdicao((string) $modelo), $limpos);
    }
}

if (! function_exists('osOpcoesDeCobranca')) {
    /**
     * Opções do "Gerar cobrança" a partir de config/payment_gateways.php: uma
     * por gateway e forma de pagamento, com o valor "Biblioteca|forma" (o
     * módulo os/tela separa os dois campos que cobrancas/adicionar espera).
     *
     * @return array<string, string> valor => rótulo
     */
    function osOpcoesDeCobranca($gateways): array
    {
        $opcoes = [];
        foreach (is_array($gateways) ? $gateways : [] as $gateway) {
            if (! is_array($gateway) || ! isset($gateway['library_name'], $gateway['name']) || ! preg_match('/^[A-Za-z0-9_]+$/', (string) $gateway['library_name'])) {
                continue;
            }

            foreach ($gateway['payment_methods'] ?? [] as $forma) {
                if (isset($forma['value'], $forma['name'])) {
                    $opcoes[$gateway['library_name'] . '|' . $forma['value']] = $gateway['name'] . ' — ' . $forma['name'];
                }
            }
        }

        return $opcoes;
    }
}

if (! function_exists('osVencimentoGarantia')) {
    /**
     * Fim da garantia (data final da OS + dias de garantia), quando a garantia
     * já corre (OS Finalizada ou Faturada). Sem garantia, null.
     */
    function osVencimentoGarantia(?string $dataFinal, $dias, ?string $status): ?string
    {
        $dias = is_numeric($dias) ? (int) $dias : 0;
        $base = dataIsoParaYmd(substr((string) $dataFinal, 0, 10));
        if ($dias <= 0 || $base === null || ! in_array($status, ['Finalizado', 'Faturado'], true)) {
            return null;
        }

        return date('Y-m-d', strtotime($base . ' +' . $dias . ' days'));
    }
}
