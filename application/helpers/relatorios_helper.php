<?php

/**
 * Definição das telas de relatórios (#2845): uma tela só (views/relatorios/
 * relatorio.php) monta cada página a partir desta definição, com os atalhos
 * dos relatórios rápidos e o formulário do relatório personalizado.
 *
 * Os relatórios em si (PDF, planilha, DOCX) continuam nas ações do controller
 * Relatorios; as views de impressão ficam fora do Tailwind.
 */
if (! function_exists('relatorioDefinicao')) {
    /**
     * Definição de uma tela de relatório, ou null se a chave não existe.
     *
     * Campos do formulário (tipo):
     * - data: input type=date;
     * - dinheiro: valor em reais com máscara ("1.234,56");
     * - numero: número inteiro;
     * - select: lista (opcoes);
     * - busca: autocomplete com o id no campo oculto de nome `name` (url).
     *
     * @return array{titulo: string, descricao: string, icone: string, rapidos: list<array{label: string, url: string, icone: string}>, acao: string, dica: string|null, campos: list<array<string, mixed>>}|null
     */
    function relatorioDefinicao(string $chave): ?array
    {
        $formato = static fn (array $opcoes = ['pdf' => 'PDF', 'xls' => 'Planilha (XLS)']) => ['tipo' => 'select', 'name' => 'format', 'label' => 'Formato', 'opcoes' => $opcoes];
        $periodo = static fn (string $de, string $ate = 'Até') => [
            ['tipo' => 'data', 'name' => 'dataInicial', 'label' => $de],
            ['tipo' => 'data', 'name' => 'dataFinal', 'label' => $ate],
        ];
        $pdfXls = static fn (string $rota, string $nome, string $icone = 'file-text') => [
            ['label' => $nome . ' (PDF)', 'url' => 'relatorios/' . $rota, 'icone' => $icone],
            ['label' => $nome . ' (planilha)', 'url' => 'relatorios/' . $rota . '?format=xls', 'icone' => 'download'],
        ];
        $statusDaOs = array_combine(array_keys(OS_STATUS_VARIANTES), array_keys(OS_STATUS_VARIANTES));

        $definicoes = [
            'clientes' => [
                'titulo' => 'Relatórios de clientes',
                'descricao' => 'Lista de clientes e fornecedores, em PDF ou planilha.',
                'icone' => 'users',
                'rapidos' => $pdfXls('clientesRapid', 'Todos os clientes'),
                'acao' => 'relatorios/clientesCustom',
                'dica' => null,
                'campos' => [
                    ...$periodo('Cadastrados de'),
                    ['tipo' => 'select', 'name' => 'tipocliente', 'label' => 'Tipo de cadastro', 'opcoes' => ['0' => 'Clientes', '1' => 'Fornecedores']],
                ],
            ],
            'produtos' => [
                'titulo' => 'Relatórios de produtos',
                'descricao' => 'Estoque e preços dos produtos.',
                'icone' => 'package',
                'rapidos' => [
                    ['label' => 'Todos os produtos', 'url' => 'relatorios/produtosRapid', 'icone' => 'file-text'],
                    ['label' => 'Com estoque mínimo', 'url' => 'relatorios/produtosRapidMin', 'icone' => 'triangle-alert'],
                ],
                'acao' => 'relatorios/produtosCustom',
                'dica' => 'Deixe em branco o que não quiser usar como filtro.',
                'campos' => [
                    ['tipo' => 'dinheiro', 'name' => 'precoInicial', 'label' => 'Preço de venda de (R$)'],
                    ['tipo' => 'dinheiro', 'name' => 'precoFinal', 'label' => 'Preço de venda até (R$)'],
                    ['tipo' => 'numero', 'name' => 'estoqueInicial', 'label' => 'Estoque de'],
                    ['tipo' => 'numero', 'name' => 'estoqueFinal', 'label' => 'Estoque até'],
                ],
            ],
            'servicos' => [
                'titulo' => 'Relatórios de serviços',
                'descricao' => 'Serviços cadastrados e seus preços.',
                'icone' => 'wrench',
                'rapidos' => [['label' => 'Todos os serviços', 'url' => 'relatorios/servicosRapid', 'icone' => 'file-text']],
                'acao' => 'relatorios/servicosCustom',
                'dica' => 'Deixe em branco o que não quiser usar como filtro.',
                'campos' => [
                    ['tipo' => 'dinheiro', 'name' => 'precoInicial', 'label' => 'Preço de (R$)'],
                    ['tipo' => 'dinheiro', 'name' => 'precoFinal', 'label' => 'Preço até (R$)'],
                ],
            ],
            'os' => [
                'titulo' => 'Relatórios de ordens de serviço',
                'descricao' => 'Ordens de serviço por período, cliente, responsável e status.',
                'icone' => 'file-text',
                'rapidos' => $pdfXls('osRapid', 'Todas as OS'),
                'acao' => 'relatorios/osCustom',
                'dica' => null,
                'campos' => [
                    ...$periodo('Data de'),
                    ['tipo' => 'busca', 'name' => 'cliente', 'label' => 'Cliente', 'url' => 'os/autoCompleteCliente', 'placeholder' => 'Nome, documento ou telefone'],
                    ['tipo' => 'busca', 'name' => 'responsavel', 'label' => 'Responsável', 'url' => 'os/autoCompleteUsuario', 'placeholder' => 'Nome do técnico'],
                    ['tipo' => 'select', 'name' => 'status', 'label' => 'Status', 'opcoes' => $statusDaOs, 'placeholder' => 'Todos'],
                    $formato(),
                ],
            ],
            'vendas' => [
                'titulo' => 'Relatórios de vendas',
                'descricao' => 'Vendas por período, cliente e vendedor.',
                'icone' => 'shopping-cart',
                'rapidos' => $pdfXls('vendasRapid', 'Todas as vendas'),
                'acao' => 'relatorios/vendasCustom',
                'dica' => null,
                'campos' => [
                    ...$periodo('Data de'),
                    ['tipo' => 'busca', 'name' => 'cliente', 'label' => 'Cliente', 'url' => 'os/autoCompleteCliente', 'placeholder' => 'Nome, documento ou telefone'],
                    ['tipo' => 'busca', 'name' => 'responsavel', 'label' => 'Vendedor', 'url' => 'os/autoCompleteUsuario', 'placeholder' => 'Nome do vendedor'],
                    $formato(),
                ],
            ],
            'financeiro' => [
                'titulo' => 'Relatórios financeiros',
                'descricao' => 'Receitas e despesas por vencimento.',
                'icone' => 'circle-dollar-sign',
                'rapidos' => $pdfXls('financeiroRapid', 'Relatório do mês'),
                'acao' => 'relatorios/financeiroCustom',
                'dica' => null,
                'campos' => [
                    ...$periodo('Vencimento de'),
                    ['tipo' => 'select', 'name' => 'tipo', 'label' => 'Tipo', 'opcoes' => ['todos' => 'Todos', 'receita' => 'Receitas', 'despesa' => 'Despesas']],
                    ['tipo' => 'select', 'name' => 'situacao', 'label' => 'Situação', 'opcoes' => ['todos' => 'Todas', 'pago' => 'Pagas', 'pendente' => 'Pendentes']],
                    $formato(),
                ],
            ],
            'sku' => [
                'titulo' => 'Relatório SKU',
                'descricao' => 'Produtos vendidos, de OS e de vendas.',
                'icone' => 'barcode',
                'rapidos' => $pdfXls('skuRapid', 'SKU'),
                'acao' => 'relatorios/skuCustom',
                'dica' => null,
                'campos' => [
                    ...$periodo('Ocorrência de'),
                    ['tipo' => 'busca', 'name' => 'clientes_id', 'label' => 'Cliente', 'url' => 'os/autoCompleteCliente', 'placeholder' => 'Nome, documento ou telefone'],
                    ['tipo' => 'select', 'name' => 'origem', 'label' => 'Origem', 'opcoes' => ['' => 'Todas', 'os' => 'Ordem de serviço', 'venda' => 'Venda']],
                    $formato(),
                ],
            ],
            'receitasBrutasMei' => [
                'titulo' => 'Receitas brutas do MEI',
                'descricao' => 'Relatório mensal de receitas brutas, em PDF ou DOCX.',
                'icone' => 'receipt',
                'rapidos' => [
                    ['label' => 'Mês atual (PDF)', 'url' => 'relatorios/receitasBrutasRapid?format=pdf', 'icone' => 'file-text'],
                    ['label' => 'Mês atual (DOCX)', 'url' => 'relatorios/receitasBrutasRapid?format=docx', 'icone' => 'download'],
                ],
                'acao' => 'relatorios/receitasBrutasCustom',
                'dica' => null,
                'campos' => [
                    ...$periodo('Ocorrência de'),
                    $formato(['pdf' => 'PDF', 'docx' => 'DOCX']),
                ],
            ],
        ];

        return $definicoes[$chave] ?? null;
    }
}

if (! function_exists('relatorioFaixa')) {
    /**
     * Limites de um filtro "de ... até ..." digitados como valor em reais
     * ("1.234,56") ou número inteiro: cada limite vem como decimal "1234.56"
     * (dinheiro) ou "12" (inteiro), ou null quando vazio ou inválido. Os dois
     * são independentes: só o "de" ou só o "até" filtra do mesmo jeito.
     *
     * @param  mixed  $minimo
     * @param  mixed  $maximo
     * @return array{0: string|null, 1: string|null}
     */
    function relatorioFaixa($minimo, $maximo, bool $dinheiro): array
    {
        $limite = static function ($valor) use ($dinheiro): ?string {
            if (! is_string($valor) || trim($valor) === '') {
                return null;
            }

            return $dinheiro ? valorDecimal($valor) : (preg_match('/^\d{1,9}$/', trim($valor)) ? ltrim(trim($valor), '0') ?: '0' : null);
        };

        return [$limite($minimo), $limite($maximo)];
    }
}

if (! function_exists('planilhaCelulasSeguras')) {
    /**
     * Neutraliza injeção de fórmula em planilhas: texto que começa com =, +, -,
     * @, tab ou CR viraria fórmula no Excel/LibreOffice (ex.: =HYPERLINK(...)
     * num nome de cliente). Números reais passam intactos.
     */
    function planilhaCelulasSeguras(array $linha): array
    {
        return array_map(static function ($celula) {
            if (is_string($celula) && $celula !== '' && ! is_numeric($celula) && strpbrk($celula[0], "=+-@\t\r") !== false) {
                return "'" . $celula;
            }

            return $celula;
        }, $linha);
    }
}
