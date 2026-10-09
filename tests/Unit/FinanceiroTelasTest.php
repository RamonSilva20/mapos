<?php

require_once APPPATH . 'controllers/Financeiro.php';

// O url_helper não é carregado nos testes.
if (! function_exists('base_url')) {
    function base_url($uri = '')
    {
        return 'http://mapos.test/' . ltrim((string) $uri, '/');
    }
}

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

if (! function_exists('current_url')) {
    function current_url()
    {
        return 'http://mapos.test/index.php/financeiro/adicionar';
    }
}

/**
 * Telas de lançamentos (#2844): filtros da listagem, para onde o formulário
 * volta depois de salvar e a renderização das duas views (escape, permissões,
 * estados vazios, formulário de cadastro e de edição).
 */
final class FinanceiroTelasTest extends MaposTestCase
{
    protected function setUp(): void
    {
    }

    // --- Controller ----------------------------------------------------------

    private function controller(array $get = []): Financeiro
    {
        $controller = new class() extends Financeiro {
            public $input;

            public function __construct()
            {
            }
        };
        $controller->input = new class($get) {
            public function __construct(private array $get)
            {
            }

            public function get()
            {
                return $this->get;
            }
        };

        return $controller;
    }

    private function filtros(array $get): array
    {
        return $this->invokeMethod($this->controller($get), 'filtrosDaListagem');
    }

    private function hoje(): DateTimeImmutable
    {
        return new DateTimeImmutable('today');
    }

    public function testSemFiltrosMostraOMesAtual(): void
    {
        [$de, $ate] = financeiroPeriodo('mes', $this->hoje());

        $this->assertSame(['periodo' => 'mes', 'de' => $de, 'ate' => $ate], $this->filtros([]));
    }

    public function testPeriodoPredefinidoVenceAsDatasDaUrl(): void
    {
        $dia = $this->hoje()->format('Y-m-d');

        $filtros = $this->filtros(['periodo' => 'dia', 'de' => '2020-01-01', 'ate' => '2020-01-31']);

        $this->assertSame(['periodo' => 'dia', 'de' => $dia, 'ate' => $dia], $filtros);
    }

    public function testPeriodoPersonalizadoUsaAsDatasEEndireitaAsInvertidas(): void
    {
        $this->assertSame(
            ['periodo' => 'personalizado', 'de' => '2026-10-01', 'ate' => '2026-10-10'],
            $this->filtros(['periodo' => 'personalizado', 'de' => '2026-10-01', 'ate' => '2026-10-10'])
        );
        $this->assertSame(
            ['periodo' => 'personalizado', 'de' => '2026-10-01', 'ate' => '2026-10-10'],
            $this->filtros(['periodo' => 'personalizado', 'de' => '2026-10-10', 'ate' => '2026-10-01']),
            'De depois de até: as datas trocam de lugar.'
        );
        $this->assertSame(
            ['periodo' => 'personalizado', 'de' => '2026-03-01', 'ate' => '2026-03-31'],
            $this->filtros(['de' => '2026-03-01', 'ate' => '2026-03-31']),
            'Só as datas, sem período: é personalizado (links antigos e favoritos).'
        );
    }

    public function testDatasInvalidasVoltamAoMesAtual(): void
    {
        [$de, $ate] = financeiroPeriodo('mes', $this->hoje());

        foreach ([
            ['periodo' => 'personalizado'],
            ['periodo' => 'personalizado', 'de' => '31/02/2026', 'ate' => '2026-02-31'],
            ['de' => '2026-10-01'],
            ['periodo' => 'inexistente'],
        ] as $get) {
            $this->assertSame(['periodo' => 'mes', 'de' => $de, 'ate' => $ate], $this->filtros($get), json_encode($get));
        }
    }

    public function testTipoSituacaoEPesquisaValidos(): void
    {
        $filtros = $this->filtros(['tipo' => 'despesa', 'status' => 'vencido', 'pesquisa' => '  aluguel  ']);

        $this->assertSame('despesa', $filtros['tipo']);
        $this->assertSame('vencido', $filtros['status']);
        $this->assertSame('aluguel', $filtros['pesquisa']);

        $filtros = $this->filtros(['tipo' => 'outro', 'status' => '1', 'pesquisa' => ['x'], 'extra' => 'y']);
        $this->assertSame(['periodo', 'de', 'ate'], array_keys($filtros));
    }

    public function testVoltaParaAListagemComOsFiltrosDeOndeVeio(): void
    {
        $controller = $this->controller();
        $retorno = ['periodo' => 'personalizado', 'de' => '2026-10-01', 'ate' => '2026-10-31', 'status' => 'pendente', 'tipo' => 'despesa'];

        // Vencimento dentro do período e mesmo tipo: tudo igual.
        $this->assertSame(
            'http://mapos.test/index.php/financeiro/lancamentos?periodo=personalizado&de=2026-10-01&ate=2026-10-31&status=pendente&tipo=despesa',
            $this->invokeMethod($controller, 'urlDaListagemApos', [$retorno, '2026-10-15', 'despesa'])
        );

        // Salvou uma receita com o filtro de despesas: o filtro sai, senão ela sumiria.
        $url = $this->invokeMethod($controller, 'urlDaListagemApos', [$retorno, '2026-10-15', 'receita']);
        $this->assertStringNotContainsString('tipo=', $url);
        $this->assertStringContainsString('status=pendente', $url);

        // Vencimento fora do período: a listagem passa para o mês dele.
        $url = $this->invokeMethod($controller, 'urlDaListagemApos', [$retorno, '2026-12-05', 'despesa']);
        $this->assertStringContainsString('periodo=personalizado&de=2026-12-01&ate=2026-12-31', $url);

        // Sem filtros de data, o mês atual é o período.
        [$de, $ate] = financeiroPeriodo('mes', $this->hoje());
        $url = $this->invokeMethod($controller, 'urlDaListagemApos', [[], $de, 'receita']);
        $this->assertSame('http://mapos.test/index.php/financeiro/lancamentos', $url);
    }

    public function testControllerSegueOPadraoDaV5(): void
    {
        $controller = (string) file_get_contents(APPPATH . 'controllers/Financeiro.php');

        preg_match('/public function lancamentos\(\).*?\n    }\n/s', $controller, $lancamentos);
        $this->assertNotEmpty($lancamentos);
        $this->assertStringContainsString("\$this->data['legacy_assets'] = false;", $lancamentos[0]);
        $this->assertStringContainsString("'Novo lançamento'", $lancamentos[0]);
        $this->assertStringContainsString("'vLancamento'", $lancamentos[0]);

        preg_match('/public function editar\(\).*?\n    }\n/s', $controller, $editar);
        $this->assertNotEmpty($editar);
        $this->assertStringContainsString('$this->uri->segment(3)', $editar[0], 'O id editado vem da URL.');
        $this->assertStringNotContainsString("post('id')", $editar[0]);

        preg_match('/private function formulario\(.*?\n    }\n/s', $controller, $formulario);
        $this->assertNotEmpty($formulario);
        $this->assertStringNotContainsString("post('urlAtual')", $formulario[0], 'Nada de redirecionar para uma URL vinda do POST.');
        $this->assertStringContainsString('financeiroLancamentoDoFormulario(', $formulario[0]);

        $this->assertStringNotContainsString('urlAtual', $controller);
        $this->assertStringNotContainsString('print_r(', $controller);
        $this->assertStringNotContainsString('${', $controller);
    }

    // --- Views -----------------------------------------------------------------

    /**
     * Renderiza uma view como o CI_Loader faz: variáveis compartilhadas e
     * $this->security para o token CSRF.
     */
    private function renderizar(string $view, array $variaveis): string
    {
        $carregador = new class($variaveis) {
            public object $load;

            public object $security;

            public function __construct(private array $variaveis)
            {
                $this->load = $this;
                $this->security = new class() {
                    public function get_csrf_token_name()
                    {
                        return 'MAPOS_TOKEN';
                    }

                    public function get_csrf_hash()
                    {
                        return 'hash-csrf';
                    }
                };
            }

            public function view(string $nome, array $mais = [], bool $retornar = false)
            {
                $this->variaveis = array_merge($this->variaveis, $mais);
                $arquivo = APPPATH . 'views' . DIRECTORY_SEPARATOR . $nome . '.php';

                return (function () use ($arquivo) {
                    extract($this->variaveis);
                    ob_start();

                    try {
                        include $arquivo;
                    } catch (Throwable $erro) {
                        ob_end_clean();

                        throw $erro;
                    }

                    return (string) ob_get_clean();
                })();
            }
        };

        return $carregador->view($view, [], true);
    }

    private function dadosDaListagem(bool $comPermissao = true, array $mudancas = []): array
    {
        $xss = '<script>alert(1)</script>';
        $linha = static fn (array $campos) => (object) ($campos + [
            'tipo' => 'receita', 'clientes_id' => null, 'forma_pgto' => 'Pix', 'baixado' => 0, 'desconto' => '0', 'valor_desconto' => '0',
            'data_vencimento' => '2026-10-20', 'cliente_fornecedor' => 'Papelaria', 'observacoes' => '',
        ]);

        return $mudancas + [
            'hoje' => '2026-10-14',
            'filtros' => ['periodo' => 'personalizado', 'de' => '2026-10-01', 'ate' => '2026-10-31'],
            'total' => 3,
            'results' => [
                $linha(['idLancamentos' => 7, 'descricao' => 'Venda ' . $xss, 'cliente_fornecedor' => 'Ana ' . $xss, 'clientes_id' => 3, 'valor' => '1000', 'desconto' => '250', 'valor_desconto' => '750']),
                $linha(['idLancamentos' => 8, 'tipo' => 'despesa', 'descricao' => 'Aluguel', 'data_vencimento' => '2026-10-05', 'valor' => '2000']),
                $linha(['idLancamentos' => 9, 'descricao' => 'Peça', 'baixado' => 1, 'valor' => '300', 'valor_desconto' => '300']),
            ],
            'totais' => [
                'receitas' => 1050.0, 'despesas' => 2000.0, 'receitas_pagas' => 300.0, 'despesas_pagas' => 0.0, 'receitas_pendentes' => 750.0,
                'despesas_pendentes' => 2000.0, 'receitas_vencidas' => 0.0, 'despesas_vencidas' => 2000.0, 'vencidos' => 1.0,
            ],
            'visao_geral' => ['saldo_realizado' => 300.0, 'a_receber' => 750.0, 'a_pagar' => 2000.0, 'descontos' => 250.0],
            'paginacao' => ['total_pages' => 1, 'current' => 1, 'url' => 'http://mapos.test/index.php/financeiro/lancamentos/{offset}', 'per_page' => 10],
            'pode' => ['adicionar' => $comPermissao, 'editar' => $comPermissao, 'excluir' => $comPermissao, 'ver_cliente' => $comPermissao],
        ];
    }

    public function testListagemRenderizaEscapandoTudo(): void
    {
        $html = $this->renderizar('financeiro/lancamentos', $this->dadosDaListagem());

        $this->assertStringNotContainsString('<script', $html, 'Nenhum script inline.');
        $this->assertStringNotContainsString('alert(1)</script>', $html);
        $this->assertStringContainsString('Venda &lt;script&gt;', $html);
        $this->assertStringContainsString('Ana &lt;script&gt;', $html);
        $this->assertStringNotContainsString(' onclick', $html);
        $this->assertStringNotContainsString('bx-', $html);
        $this->assertStringContainsString('data-module="financeiro/filtros"', $html);
        $this->assertStringContainsString('3 lançamentos com vencimento de 01/10/2026 a 31/10/2026', $html);
    }

    public function testListagemMostraResumoValoresESituacao(): void
    {
        $html = $this->renderizar('financeiro/lancamentos', $this->dadosDaListagem());

        $this->assertStringContainsString('R$ 1.050,00', $html, 'Receitas.');
        $this->assertStringContainsString('R$ 2.000,00', $html, 'Despesas.');
        $this->assertStringContainsString('-R$ 950,00', str_replace('R$ -', '-R$ ', $html), 'Saldo previsto: receitas menos despesas.');
        $this->assertStringContainsString('R$ 750,00', $html, 'Líquido da linha com desconto.');
        $this->assertStringContainsString('R$ 250,00', $html, 'Desconto da linha.');
        $this->assertStringContainsString('Vencido', $html, 'O aluguel venceu em 05/10.');
        $this->assertStringContainsString('Pago', $html);
        $this->assertStringContainsString('Pendente', $html);
        $this->assertStringContainsString('Visão geral', $html);
    }

    public function testListagemSoMostraAcoesComPermissao(): void
    {
        $com = $this->renderizar('financeiro/lancamentos', $this->dadosDaListagem(true));
        $this->assertStringContainsString('financeiro/editar/7?periodo=personalizado&amp;de=2026-10-01&amp;ate=2026-10-31', $com);
        $this->assertStringContainsString('data-modal-abrir="excluir-lancamento"', $com);
        $this->assertStringContainsString('data-valor-id="7"', $com);
        $this->assertStringContainsString('id="form-excluir-lancamento"', $com);
        $this->assertStringContainsString('name="MAPOS_TOKEN" value="hash-csrf"', $com);
        $this->assertStringContainsString('clientes/visualizar/3', $com);

        $sem = $this->renderizar('financeiro/lancamentos', $this->dadosDaListagem(false));
        $this->assertStringNotContainsString('financeiro/editar/', $sem);
        $this->assertStringNotContainsString('excluir-lancamento', $sem);
        $this->assertStringNotContainsString('clientes/visualizar/', $sem);
    }

    public function testListagemVaziaDiferenciaPeriodoSemLancamentosDeBuscaSemResultado(): void
    {
        $semFiltro = $this->renderizar('financeiro/lancamentos', $this->dadosDaListagem(true, ['results' => [], 'total' => 0]));
        $this->assertStringContainsString('Nenhum lançamento neste período', $semFiltro);
        $this->assertStringContainsString('Cadastrar lançamento', $semFiltro);
        $this->assertStringNotContainsString('Limpar filtros', $semFiltro);

        $comFiltro = $this->renderizar('financeiro/lancamentos', $this->dadosDaListagem(true, [
            'results' => [], 'total' => 0,
            'filtros' => ['periodo' => 'mes', 'de' => '2026-10-01', 'ate' => '2026-10-31', 'pesquisa' => 'zzz'],
        ]));
        $this->assertStringContainsString('Nenhum lançamento encontrado', $comFiltro);
        $this->assertStringContainsString('Limpar filtros', $comFiltro);
        $this->assertStringContainsString('financeiro/lancamentos?periodo=mes&amp;de=2026-10-01&amp;ate=2026-10-31', $comFiltro, 'Limpar mantém o período.');
    }

    private function dadosDoFormulario(?object $lancamento = null, array $mudancas = []): array
    {
        return $mudancas + [
            'lancamento' => $lancamento,
            'valores' => financeiroValoresDoFormulario(null, $lancamento, ['tipo' => 'receita', 'data_vencimento' => '2026-10-14', 'data_entrada' => '2026-10-14', 'data_pagamento' => '2026-10-14']),
            'erros' => [],
            'retorno' => ['periodo' => 'mes', 'de' => '2026-10-01', 'ate' => '2026-10-31'],
            'controle_baixa' => false,
        ];
    }

    public function testFormularioDeCadastro(): void
    {
        $html = $this->renderizar('financeiro/formulario', $this->dadosDoFormulario());

        $this->assertStringContainsString('Novo lançamento', $html);
        $this->assertStringContainsString('name="MAPOS_TOKEN" value="hash-csrf"', $html);
        $this->assertStringContainsString('data-module="formulario/padrao"', $html);
        $this->assertStringContainsString('data-module="financeiro/formulario"', $html);
        $this->assertStringContainsString('financeiro/adicionar?periodo=mes&amp;de=2026-10-01&amp;ate=2026-10-31', $html, 'O POST volta com os filtros da listagem.');
        $this->assertStringContainsString('Parcelamento', $html);
        $this->assertStringContainsString('name="parcelas"', $html);
        $this->assertStringContainsString('<datalist id="cliente-sugestoes">', $html);
        $this->assertStringContainsString('name="clientes_id"', $html);
        $this->assertStringContainsString('data-mascara="dinheiro"', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('name="id"', $html, 'O id vem da URL, nunca do formulário.');
        $this->assertStringNotContainsString('urlAtual', $html);
    }

    public function testFormularioDeEdicaoNaoTemParcelamentoEMostraQuemAlterou(): void
    {
        $lancamento = (object) [
            'idLancamentos' => 12, 'tipo' => 'despesa', 'descricao' => 'Aluguel <b>', 'cliente_fornecedor' => 'Imobiliária', 'clientes_id' => null,
            'valor' => '2000', 'desconto' => '0', 'valor_desconto' => '2000', 'data_vencimento' => '2026-10-05', 'baixado' => 1,
            'data_pagamento' => '2026-10-04', 'forma_pgto' => 'Pix', 'observacoes' => 'Contrato', 'modificado_por' => 'Maria',
        ];

        $html = $this->renderizar('financeiro/formulario', $this->dadosDoFormulario($lancamento));

        $this->assertStringContainsString('Editar lançamento #12', $html);
        $this->assertStringContainsString('Última alteração por Maria.', $html);
        $this->assertStringContainsString('value="Aluguel &lt;b&gt;"', $html);
        $this->assertStringContainsString('value="2.000,00"', $html);
        $this->assertStringContainsString('Pago', $html);
        $this->assertStringNotContainsString('name="parcelas"', $html);
        $this->assertStringNotContainsString('name="entrada"', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*name="baixado"[^>]*checked/', $html);
        $this->assertStringNotContainsString('data-financeiro-baixa hidden', $html, 'Baixado: data e forma de pagamento visíveis.');
    }

    public function testFormularioComErrosMostraCadaUmNoSeuCampo(): void
    {
        $post = ['tipo' => 'receita', 'descricao' => '', 'cliente_fornecedor' => 'Ana', 'clientes_id' => '', 'valor' => 'abc', 'parcelas' => '3', 'entrada' => '10,00'];
        $dados = $this->dadosDoFormulario(null, [
            'valores' => financeiroValoresDoFormulario($post, null),
            'erros' => ['descricao' => 'Informe a descrição.', 'valor' => 'Informe um valor maior que zero, como 1.250,00.'],
        ]);

        $html = $this->renderizar('financeiro/formulario', $dados);

        $this->assertStringContainsString('Confira os 2 campos destacados.', $html);
        $this->assertStringContainsString('Informe a descrição.', $html);
        $this->assertStringContainsString('id="campo-valor-erro"', $html);
        $this->assertStringContainsString('value="abc"', $html, 'Mantém o que foi digitado.');
        // Parcelado: a entrada aparece e a opção de baixa some.
        $this->assertStringNotContainsString('data-financeiro-entrada hidden', $html);
        $this->assertStringContainsString('data-financeiro-baixa-opcao hidden', $html);

        $geral = $this->renderizar('financeiro/formulario', $this->dadosDoFormulario(null, ['erros' => ['_geral' => 'Não foi possível salvar. Tente de novo.']]));
        $this->assertStringContainsString('Não foi possível salvar. Tente de novo.', $geral);
    }

    public function testControleDeBaixaApareceNaAjudaDaData(): void
    {
        $html = $this->renderizar('financeiro/formulario', $this->dadosDoFormulario(null, ['controle_baixa' => true]));

        $this->assertStringContainsString('só a data de hoje', $html);
    }
}
