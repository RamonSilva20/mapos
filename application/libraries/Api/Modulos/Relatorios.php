<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\OrdemDeServico;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Schema;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use Throwable;
use XLSXWriter;

final class Relatorios extends Modulo
{
    private const COLUNAS_OCULTAS = ['senha', 'token_versao', 'asaas_id'];

    public static function rotas(): array
    {
        $tag = 'Relatórios';
        $formato = Schema::enum(['json', 'pdf', 'xlsx'])->padrao('json')->descricao('`json` traz as linhas e os totais; `pdf` é o mesmo relatório impresso do painel; `xlsx` é a planilha das linhas.');
        $periodo = [
            'de' => Schema::data(),
            'ate' => Schema::data(),
        ];
        $resposta = static fn (Rota $rota): Rota => $rota
            ->resposta(200, Schema::objeto([
                'data' => Schema::objeto([
                    'linhas' => Schema::lista(Schema::mapa(Schema::qualquer()))->obrigatorio()->descricao('As colunas do relatório, com os nomes em snake_case.'),
                    'totais' => Schema::mapa(Schema::qualquer())->obrigatorio(),
                ])->obrigatorio(),
            ]), 'Relatório. Com `formato=pdf` ou `xlsx`, o arquivo.')
            ->erros(422);

        $rotas = [
            Rota::get('/relatorios/clientes', [self::class, 'clientes'])->permissao('rCliente')
                ->resumo('Relatório de clientes')
                ->consulta(Schema::objeto($periodo + [
                    'busca' => Schema::texto()->max(100)->descricao('Nome, telefone, celular ou documento.'),
                    'tipo' => Schema::enum(['cliente', 'fornecedor']),
                    'formato' => clone $formato,
                ])),

            Rota::get('/relatorios/produtos', [self::class, 'produtos'])->permissao('rProduto')
                ->resumo('Relatório de produtos')
                ->consulta(Schema::objeto([
                    'busca' => Schema::texto()->max(100)->descricao('Descrição ou código de barras.'),
                    'preco_min' => Schema::decimal()->min(0),
                    'preco_max' => Schema::decimal()->min(0),
                    'estoque_min' => Schema::inteiro()->min(0),
                    'estoque_max' => Schema::inteiro()->min(0),
                    'estoque_baixo' => Schema::booleano()->padrao(false)->descricao('Só os que estão no estoque mínimo ou abaixo (ignora os outros filtros).'),
                    'formato' => clone $formato,
                ])),

            Rota::get('/relatorios/servicos', [self::class, 'servicos'])->permissao('rServico')
                ->resumo('Relatório de serviços')
                ->consulta(Schema::objeto([
                    'busca' => Schema::texto()->max(100),
                    'preco_min' => Schema::decimal()->min(0),
                    'preco_max' => Schema::decimal()->min(0),
                    'cliente_id' => Schema::inteiro()->min(1)->descricao('Só os serviços feitos para este cliente, com a quantidade.'),
                    'formato' => clone $formato,
                ])),

            Rota::get('/relatorios/os', [self::class, 'ordens'])->permissao('rOs')
                ->resumo('Relatório de ordens de serviço')
                ->consulta(Schema::objeto($periodo + [
                    'cliente_id' => Schema::inteiro()->min(1),
                    'tecnico_id' => Schema::inteiro()->min(1),
                    'status' => Schema::enum(OrdemDeServico::status()),
                    'formato' => clone $formato,
                ])),

            Rota::get('/relatorios/vendas', [self::class, 'vendas'])->permissao('rVenda')
                ->resumo('Relatório de vendas')
                ->consulta(Schema::objeto($periodo + [
                    'cliente_id' => Schema::inteiro()->min(1),
                    'vendedor_id' => Schema::inteiro()->min(1),
                    'formato' => clone $formato,
                ])),

            Rota::get('/relatorios/financeiro', [self::class, 'financeiro'])->permissao('rFinanceiro')
                ->resumo('Relatório financeiro')
                ->consulta(Schema::objeto($periodo + [
                    'tipo' => Schema::enum(['receita', 'despesa']),
                    'situacao' => Schema::enum(['pago', 'pendente']),
                    'forma_pagamento' => Schema::enum(FINANCEIRO_FORMAS_PAGAMENTO),
                    'formato' => clone $formato,
                ])),

            Rota::get('/relatorios/sku', [self::class, 'sku'])->todasAsPermissoes('rVenda', 'rOs')
                ->resumo('Produtos vendidos e usados em OS (SKU)')
                ->consulta(Schema::objeto($periodo + [
                    'cliente_id' => Schema::inteiro()->min(1),
                    'origem' => Schema::enum(['os', 'venda']),
                    'formato' => clone $formato,
                ])),
        ];

        foreach ($rotas as $rota) {
            $resposta($rota->tag($tag));
        }

        return [
            ...$rotas,
            Rota::get('/relatorios/etiquetas', [self::class, 'etiquetas'])->permissao('rProduto')->tag($tag)
                ->resumo('Etiquetas de produtos (PDF)', 'Uma etiqueta por produto, do id `de` ao id `ate`.')
                ->consulta(Schema::objeto([
                    'de' => Schema::inteiro()->obrigatorio()->min(1),
                    'ate' => Schema::inteiro()->obrigatorio()->min(1),
                ]))
                ->arquivo('application/pdf')
                ->erros(422),

            Rota::get('/relatorios/receitas-brutas-mei', [self::class, 'receitasBrutasMei'])->permissao('rFinanceiro')->tag($tag)
                ->resumo('Relatório mensal das receitas brutas do MEI', 'No modelo oficial, em DOCX ou PDF.')
                ->consulta(Schema::objeto($periodo + ['formato' => Schema::enum(['docx', 'pdf'])->padrao('docx')]))
                ->arquivo('application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                ->erros(422),
        ];
    }

    public function clientes(): Resposta
    {
        $c = $this->ctx->consulta();
        $tipo = match ($c['tipo'] ?? null) {
            'cliente' => '0',
            'fornecedor' => '1',
            default => null,
        };
        $linhas = $this->modelo()->clientesCustom($c['de'] ?? null, $c['ate'] ?? null, $tipo, $c['busca'] ?? null);

        return $this->responder('clientes', $linhas, [], 'imprimirClientes', ['clientes' => $linhas, 'title' => 'Relatório de Clientes'] + $this->periodo($c));
    }

    public function produtos(): Resposta
    {
        $c = $this->ctx->consulta();
        $linhas = $c['estoque_baixo']
            ? $this->modelo()->produtosRapidMin()
            : $this->modelo()->produtosCustom($c['preco_min'] ?? null, $c['preco_max'] ?? null, $c['estoque_min'] ?? null, $c['estoque_max'] ?? null, $c['busca'] ?? null);

        return $this->responder('produtos', $linhas, [], 'imprimirProdutos', ['produtos' => $linhas, 'title' => 'Relatório de Produtos']);
    }

    public function servicos(): Resposta
    {
        $c = $this->ctx->consulta();
        $linhas = $this->modelo()->servicosCustom($c['preco_min'] ?? null, $c['preco_max'] ?? null, $c['busca'] ?? null, $c['cliente_id'] ?? null);

        return $this->responder('servicos', $linhas, [], 'imprimirServicos', ['servicos' => $linhas, 'title' => 'Relatório de Serviços']);
    }

    public function ordens(): Resposta
    {
        $c = $this->ctx->consulta();
        $linhas = $this->modelo()->osCustom($c['de'] ?? null, $c['ate'] ?? null, $c['cliente_id'] ?? null, $c['tecnico_id'] ?? null, $c['status'] ?? null);

        $produtos = $servicos = $desconto = $comDesconto = 0.0;
        foreach ($linhas as $o) {
            $produtos += (float) $o->total_produto;
            $servicos += (float) $o->total_servico;
            $desconto += (float) $o->desconto;
            $comDesconto += (float) $o->valor_desconto != 0 ? (float) $o->valor_desconto : (float) $o->total_produto + (float) $o->total_servico;
        }
        $totais = ['produtos' => $produtos, 'servicos' => $servicos, 'bruto' => $produtos + $servicos, 'total' => $comDesconto, 'quantidade' => count($linhas)];

        return $this->responder('os', $linhas, $totais, 'imprimirOs', [
            'os' => $linhas,
            'total_produtos' => $produtos,
            'total_servicos' => $servicos,
            'total_geral_desconto' => $desconto,
            'total_geral' => $comDesconto,
            'title' => 'Relatório de OS',
        ] + $this->periodo($c), true);
    }

    public function vendas(): Resposta
    {
        $c = $this->ctx->consulta();
        $linhas = $this->modelo()->vendasCustom($c['de'] ?? null, $c['ate'] ?? null, $c['cliente_id'] ?? null, $c['vendedor_id'] ?? null);

        $bruto = $comDesconto = 0.0;
        foreach ($linhas as $v) {
            $bruto += (float) $v->valorTotal;
            $comDesconto += (float) $v->valor_desconto != 0 ? (float) $v->valor_desconto : (float) $v->valorTotal;
        }

        return $this->responder('vendas', $linhas, ['bruto' => $bruto, 'total' => $comDesconto, 'quantidade' => count($linhas)], 'imprimirVendas', [
            'vendas' => $linhas,
            'total_vendas' => $bruto,
            'title' => 'Relatório de Vendas',
        ] + $this->periodo($c));
    }

    public function financeiro(): Resposta
    {
        $c = $this->ctx->consulta();
        $linhas = $this->modelo()->financeiroCustom($c['de'] ?? null, $c['ate'] ?? null, $c['tipo'] ?? 'todos', $c['situacao'] ?? 'todos', $c['forma_pagamento'] ?? null);

        $receitas = $despesas = 0.0;
        foreach ($linhas as $l) {
            $valor = financeiroLiquido($l);
            $l->tipo === 'despesa' ? $despesas += $valor : $receitas += $valor;
        }

        return $this->responder('financeiro', $linhas, ['receitas' => $receitas, 'despesas' => $despesas, 'saldo' => $receitas - $despesas, 'quantidade' => count($linhas)], 'imprimirFinanceiro', [
            'lancamentos' => $linhas,
            'title' => 'Relatório Financeiro',
        ] + $this->periodo($c));
    }

    public function sku(): Resposta
    {
        $c = $this->ctx->consulta();
        $linhas = $this->modelo()->skuCustom($c['de'] ?? null, $c['ate'] ?? null, $c['cliente_id'] ?? null, $c['origem'] ?? null);

        return $this->responder('sku', $linhas, ['quantidade' => count($linhas)], 'imprimirSKU', ['resultados' => $linhas, 'title' => 'Relatório de SKU'] + $this->periodo($c));
    }

    public function etiquetas(): Resposta
    {
        $c = $this->ctx->consulta();
        if ($c['ate'] < $c['de'] || $c['ate'] - $c['de'] > 1000) {
            throw HttpErro::validacao(['ate' => 'Use um intervalo crescente de até 1000 produtos.']);
        }
        $produtos = $this->modelo()->produtosEtiquetas($c['de'], $c['ate']);

        return $this->pdf('etiquetas_' . $c['de'] . '_' . $c['ate'], 'imprimirEtiquetas', ['produtos' => $produtos]);
    }

    public function receitasBrutasMei(): Resposta
    {
        $c = $this->ctx->consulta();
        $modelo = realpath(FCPATH . 'assets/relatorios/RELATORIO_MENSAL_DAS_RECEITAS_BRUTAS_MEI.docx');
        if (! $modelo) {
            throw HttpErro::interno('Modelo do relatório não encontrado.');
        }

        $dados = ($c['de'] ?? null) !== null || ($c['ate'] ?? null) !== null
            ? $this->modelo()->receitasBrutasCustom($c['de'] ?? null, $c['ate'] ?? null)
            : $this->modelo()->receitasBrutasRapid();

        $temporario = tempnam(sys_get_temp_dir(), 'mapos-mei-');
        $docx = $temporario . '.docx';
        $pdf = $temporario . '.pdf';
        try {
            $processador = new TemplateProcessor($modelo);
            $processador->setValues($dados);
            $processador->saveAs($docx);

            if ($c['formato'] === 'docx') {
                return Resposta::conteudo((string) file_get_contents($docx), 'relatorio_receitas_brutas_mei.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            }

            Settings::setPdfRendererName(Settings::PDF_RENDERER_MPDF);
            Settings::setPdfRendererPath('.');
            IOFactory::createWriter(IOFactory::load($docx), 'PDF')->save($pdf);

            return Resposta::conteudo((string) file_get_contents($pdf), 'relatorio_receitas_brutas_mei.pdf', 'application/pdf', false);
        } finally {
            foreach ([$temporario, $docx, $pdf] as $arquivo) {
                if (is_file($arquivo)) {
                    @unlink($arquivo);
                }
            }
        }
    }

    private function modelo(): object
    {
        return $this->ctx->modelo('Relatorios_model');
    }

    /** @return array<string, string> */
    private function periodo(array $consulta): array
    {
        $saida = [];
        if (($consulta['de'] ?? null) !== null) {
            $saida['dataInicial'] = date('d/m/Y', strtotime($consulta['de']));
        }
        if (($consulta['ate'] ?? null) !== null) {
            $saida['dataFinal'] = date('d/m/Y', strtotime($consulta['ate']));
        }

        return $saida;
    }

    /**
     * @param  list<object>  $linhas
     * @param  array<string, int|float>  $totais
     * @param  array<string, mixed>  $dadosDaView
     */
    private function responder(string $nome, array $linhas, array $totais, string $view, array $dadosDaView, bool $paisagem = false): Resposta
    {
        $formato = $this->ctx->consulta()['formato'] ?? 'json';
        $normalizadas = array_map([self::class, 'normalizar'], $linhas);

        if ($formato === 'pdf') {
            return $this->pdf('relatorio_' . $nome, $view, $dadosDaView, $paisagem);
        }

        if ($formato === 'xlsx') {
            $planilha = new XLSXWriter();
            $cabecalho = array_fill_keys(array_keys($normalizadas[0] ?? ['vazio' => '']), 'string');
            $planilha->writeSheetHeader('Relatório', $cabecalho);
            foreach ($normalizadas as $linha) {
                $planilha->writeSheetRow('Relatório', planilhaCelulasSeguras(array_map(static fn ($v) => is_bool($v) ? ($v ? 'sim' : 'não') : $v, array_values($linha))));
            }

            return Resposta::conteudo($planilha->writeToString(), 'relatorio_' . $nome . '.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }

        return Resposta::dados([
            'linhas' => $normalizadas,
            'totais' => (object) array_map(static fn ($v) => is_float($v) ? number_format($v, 2, '.', '') : $v, $totais),
        ]);
    }

    private function pdf(string $nome, string $view, array $dados, bool $paisagem = false): Resposta
    {
        $this->ctx->helper('mpdf');
        $dados['emitente'] = $this->ctx->modelo('Mapos_model')->getEmitente();
        try {
            $html = $this->ctx->ci->load->view('relatorios/imprimir/' . $view, $dados, true);
            $mpdf = mpdfCriar($paisagem);
            $mpdf->WriteHTML($html);
            $conteudo = $mpdf->Output('', 'S');
        } catch (Throwable $e) {
            log_message('error', 'API, relatório ' . $nome . ': ' . $e->getMessage());

            throw HttpErro::interno('Não foi possível gerar o PDF do relatório.');
        }

        return Resposta::conteudo((string) $conteudo, $nome . '.pdf', 'application/pdf', false);
    }

    /** @return array<string, mixed> */
    public static function normalizar(object|array $linha): array
    {
        $saida = [];
        foreach ((array) $linha as $chave => $valor) {
            if (in_array($chave, self::COLUNAS_OCULTAS, true)) {
                continue;
            }
            $saida[strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', (string) $chave))] = $valor;
        }

        return $saida;
    }
}
