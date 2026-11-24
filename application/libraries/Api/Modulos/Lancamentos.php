<?php

namespace Libraries\Api\Modulos;

use DateTimeImmutable;
use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Valor;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;

final class Lancamentos extends Modulo
{
    public static function rotas(): array
    {
        $tag = 'Lançamentos';

        return [
            Rota::get('/lancamentos', [self::class, 'listar'])->permissao('vLancamento')->tag($tag)
                ->resumo('Lista receitas e despesas', 'Por data de vencimento, da mais antiga para a mais nova. Sem período, vale o mês atual. `meta.totais` soma os lançamentos do filtro (valores líquidos).')
                ->consulta(Comuns::paginacaoConsulta([
                    'busca' => Schema::texto()->max(100)->descricao('Cliente/fornecedor, descrição, observações ou número.'),
                    'tipo' => Schema::enum(['receita', 'despesa']),
                    'situacao' => Schema::enum(['pendente', 'pago', 'vencido']),
                    'periodo' => Schema::enum(['dia', 'semana', 'mes_anterior', 'mes', 'mes_posterior', 'ano'])->descricao('Ignorado quando `de` e `ate` são enviados.'),
                    'de' => Schema::data(),
                    'ate' => Schema::data(),
                ]))
                ->resposta(200, Schema::objeto([
                    'data' => Schema::lista(self::schema())->obrigatorio(),
                    'meta' => Comuns::paginacao()->com([
                        'de' => Schema::data()->obrigatorio(),
                        'ate' => Schema::data()->obrigatorio(),
                        'totais' => self::schemaTotais()->obrigatorio(),
                    ])->obrigatorio(),
                ]), 'Página da listagem com os totais do filtro.'),

            Rota::get('/lancamentos/resumo', [self::class, 'resumo'])->permissao('vLancamento')->tag($tag)
                ->resumo('Visão geral do financeiro', 'Saldo realizado, a receber, a pagar, descontos e o realizado dos últimos meses.')
                ->consulta(Schema::objeto(['meses' => Schema::inteiro()->min(2)->max(24)->padrao(6)]))
                ->dados(Schema::objeto([
                    'saldo_realizado' => Schema::decimal()->obrigatorio(),
                    'a_receber' => Schema::decimal()->obrigatorio(),
                    'a_pagar' => Schema::decimal()->obrigatorio(),
                    'descontos' => Schema::decimal()->obrigatorio(),
                    'meses' => Schema::lista(Schema::objeto([
                        'mes' => Schema::texto()->obrigatorio()->exemplo('2026-11'),
                        'receitas' => Schema::decimal()->obrigatorio(),
                        'despesas' => Schema::decimal()->obrigatorio(),
                    ]))->obrigatorio(),
                ])),

            Rota::get('/lancamentos/sugestoes', [self::class, 'sugestoes'])->permissao('vLancamento', 'aLancamento', 'eLancamento')->tag($tag)
                ->resumo('Sugestões de cliente ou fornecedor', 'Clientes cadastrados (com id) e nomes já usados em lançamentos (sem id), até 10.')
                ->consulta(Schema::objeto(['busca' => Schema::texto()->obrigatorio()->min(1)->max(100)]))
                ->dados(Schema::lista(Schema::objeto([
                    'cliente_id' => Schema::inteiro()->anulavel()->obrigatorio(),
                    'nome' => Schema::texto()->obrigatorio(),
                    'detalhe' => Schema::texto()->obrigatorio(),
                ]))),

            Rota::post('/lancamentos', [self::class, 'criar'])->permissao('aLancamento')->tag($tag)
                ->resumo('Lança uma receita ou despesa', 'Com `parcelas` maior que 1, cria uma linha por parcela (mensais a partir do vencimento) e, se houver `entrada`, uma linha já paga com ela. Parcelado nasce em aberto. Devolve todos os lançamentos criados.')
                ->corpo(self::entrada(true))
                ->resposta(201, Schema::objeto(['data' => Schema::lista(self::schema())->obrigatorio()]), 'Lançamentos criados.'),

            Rota::get('/lancamentos/{id}', [self::class, 'ver'])->permissao('vLancamento')->tag($tag)
                ->resumo('Mostra um lançamento')
                ->dados(self::schema())
                ->erros(404),

            Rota::patch('/lancamentos/{id}', [self::class, 'atualizar'])->permissao('eLancamento')->tag($tag)
                ->resumo('Altera um lançamento', 'Para dar baixa, envie `pago: true`, `data_pagamento` e `forma_pagamento`. Com o controle de baixa ligado, a baixa só aceita a data de hoje.')
                ->corpo(self::entrada(false)->semObrigatorios())
                ->dados(self::schema())
                ->erros(404),

            Rota::delete('/lancamentos/{id}', [self::class, 'excluir'])->permissao('dLancamento')->tag($tag)
                ->resumo('Exclui um lançamento', 'A OS ou venda faturada com ele volta para `Finalizado`, sem fatura.')
                ->semConteudo()
                ->erros(404),
        ];
    }

    public static function schema(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'tipo' => Schema::enum(['receita', 'despesa'])->obrigatorio(),
            'descricao' => Schema::texto()->obrigatorio(),
            'cliente_fornecedor' => Schema::texto()->anulavel()->obrigatorio(),
            'cliente_id' => Schema::inteiro()->anulavel()->obrigatorio(),
            'valor' => Schema::decimal()->obrigatorio(),
            'desconto' => Schema::decimal()->obrigatorio(),
            'valor_liquido' => Schema::decimal()->obrigatorio(),
            'data_vencimento' => Schema::data()->anulavel()->obrigatorio(),
            'data_pagamento' => Schema::data()->anulavel()->obrigatorio(),
            'pago' => Schema::booleano()->obrigatorio(),
            'situacao' => Schema::enum(['pendente', 'pago', 'vencido'])->obrigatorio(),
            'forma_pagamento' => Schema::texto()->anulavel()->obrigatorio(),
            'observacoes' => Schema::texto()->obrigatorio(),
            'venda_id' => Schema::inteiro()->anulavel()->obrigatorio(),
            'modificado_por' => Comuns::pessoa()->anulavel()->obrigatorio(),
        ])->nome('Lancamento');
    }

    private static function schemaTotais(): Schema
    {
        static $schema = null;
        $campo = static fn () => Schema::decimal()->obrigatorio();

        return $schema ??= Schema::objeto([
            'receitas' => $campo(), 'despesas' => $campo(), 'saldo' => $campo(),
            'receitas_pagas' => $campo(), 'despesas_pagas' => $campo(),
            'receitas_pendentes' => $campo(), 'despesas_pendentes' => $campo(),
            'receitas_vencidas' => $campo(), 'despesas_vencidas' => $campo(),
            'vencidos' => Schema::inteiro()->obrigatorio(),
        ])->nome('TotaisFinanceiros');
    }

    private static function entrada(bool $novo): Schema
    {
        $campos = [
            'tipo' => Schema::enum(['receita', 'despesa'])->obrigatorio(),
            'descricao' => Schema::texto()->obrigatorio()->max(255),
            'cliente_fornecedor' => Schema::texto()->obrigatorio()->max(255),
            'cliente_id' => Schema::inteiro()->min(1)->anulavel()->descricao('Liga ao cadastro quando o nome bate com o do cliente.'),
            'valor' => Schema::decimal()->obrigatorio()->min(0.01),
            'desconto' => Schema::decimal()->min(0)->padrao('0.00'),
            'data_vencimento' => Schema::data()->obrigatorio(),
            'pago' => Schema::booleano()->padrao(false),
            'data_pagamento' => Schema::data()->anulavel()->descricao('Obrigatória quando pago é true.'),
            'forma_pagamento' => Schema::enum(FINANCEIRO_FORMAS_PAGAMENTO)->anulavel(),
            'observacoes' => Schema::texto()->max(5000),
        ];
        if ($novo) {
            $campos += [
                'parcelas' => Schema::inteiro()->min(1)->max(FINANCEIRO_PARCELAS_MAX)->padrao(1),
                'entrada' => Schema::decimal()->min(0)->descricao('Valor pago na entrada (só parcelado).'),
                'data_entrada' => Schema::data()->descricao('Obrigatória com entrada.'),
            ];
        }

        return Schema::objeto($campos)->nome($novo ? 'LancamentoEntrada' : 'LancamentoAlteracao');
    }

    public static function de(object $l): array
    {
        $pago = (int) $l->baixado === 1;
        $vencimento = Valor::data($l->data_vencimento);

        return [
            'id' => (int) $l->idLancamentos,
            'tipo' => $l->tipo === 'despesa' ? 'despesa' : 'receita',
            'descricao' => (string) $l->descricao,
            'cliente_fornecedor' => Valor::textoOuNulo($l->cliente_fornecedor),
            'cliente_id' => Valor::inteiroOuNulo($l->clientes_id),
            'valor' => Valor::decimal($l->valor),
            'desconto' => Valor::decimal(financeiroDescontoEmReais($l)),
            'valor_liquido' => Valor::decimal(financeiroLiquido($l)),
            'data_vencimento' => $vencimento,
            'data_pagamento' => Valor::data($l->data_pagamento),
            'pago' => $pago,
            'situacao' => $pago ? 'pago' : ($vencimento !== null && $vencimento < date('Y-m-d') ? 'vencido' : 'pendente'),
            'forma_pagamento' => Valor::textoOuNulo($l->forma_pgto),
            'observacoes' => (string) $l->observacoes,
            'venda_id' => Valor::inteiroOuNulo($l->vendas_id),
            'modificado_por' => $l->usuarios_id ? ['id' => (int) $l->usuarios_id, 'nome' => (string) ($l->modificado_por ?? '')] : null,
        ];
    }

    public function listar(): Resposta
    {
        $consulta = $this->ctx->consulta();
        $hoje = date('Y-m-d');
        $filtros = $this->filtros($consulta);
        $pagina = $this->ctx->paginacao();
        $modelo = $this->modelo();

        $total = $modelo->contar($filtros, $hoje);
        $linhas = $modelo->listar($filtros, $pagina['por_pagina'], $pagina['deslocamento'], $hoje);
        $totais = $modelo->totais($filtros, $hoje);
        $totais['saldo'] = $totais['receitas'] - $totais['despesas'];
        $saida = [];
        foreach ($totais as $chave => $valor) {
            $saida[$chave] = $chave === 'vencidos' ? (int) $valor : Valor::decimal($valor);
        }

        return Resposta::lista(array_map([self::class, 'de'], $linhas), ['pagina' => $pagina['pagina'], 'por_pagina' => $pagina['por_pagina'], 'total' => $total], [
            'de' => $filtros['de'],
            'ate' => $filtros['ate'],
            'totais' => $saida,
        ]);
    }

    public function resumo(): Resposta
    {
        $modelo = $this->modelo();
        $geral = $modelo->visaoGeral();
        $serie = $modelo->serieMensal((int) $this->ctx->consulta()['meses'], date('Y-m-d'));

        $quantidade = count($serie['receitas']);
        $meses = [];
        foreach ($serie['receitas'] as $i => $receita) {
            $meses[] = [
                'mes' => date('Y-m', strtotime(date('Y-m-01') . ' -' . ($quantidade - 1 - $i) . ' months')),
                'receitas' => Valor::decimal($receita),
                'despesas' => Valor::decimal($serie['despesas'][$i] ?? 0),
            ];
        }

        return Resposta::dados([
            'saldo_realizado' => Valor::decimal($geral['saldo_realizado']),
            'a_receber' => Valor::decimal($geral['a_receber']),
            'a_pagar' => Valor::decimal($geral['a_pagar']),
            'descontos' => Valor::decimal($geral['descontos']),
            'meses' => $meses,
        ]);
    }

    public function sugestoes(): Resposta
    {
        $termo = $this->ctx->consulta()['busca'];
        $modelo = $this->modelo();
        $clientes = $modelo->sugerirClientes($termo);
        $nomesClientes = array_map(static fn ($c) => mb_strtolower($c['valor']), $clientes);
        $nomes = array_filter($modelo->sugerirNomesUsados($termo), static fn ($n) => ! in_array(mb_strtolower($n['valor']), $nomesClientes, true));

        return Resposta::dados(array_map(static fn (array $item): array => [
            'cliente_id' => $item['id'],
            'nome' => (string) $item['valor'],
            'detalhe' => (string) $item['detalhe'],
        ], array_slice(array_merge($clientes, array_values($nomes)), 0, 10)));
    }

    public function ver(): Resposta
    {
        return Resposta::dados(self::de($this->lancamento()));
    }

    public function criar(): Resposta
    {
        $entrada = $this->ctx->corpo();
        [$dados, $erros] = $this->dadosDoFormulario($entrada, null);
        [$parcelamento, $errosParcelas] = financeiroParcelamentoDoFormulario([
            'parcelas' => (string) $entrada['parcelas'],
            'entrada' => (string) ($entrada['entrada'] ?? ''),
            'data_entrada' => (string) ($entrada['data_entrada'] ?? ''),
        ], $dados);
        $erros += self::traduzir($errosParcelas);
        if ($erros !== []) {
            throw HttpErro::validacao($erros);
        }

        $dados['usuarios_id'] = $this->ctx->titular()->id;
        $antes = (int) $this->db->select_max('idLancamentos')->get('lancamentos')->row('idLancamentos');
        $modelo = $this->modelo();
        $salvou = $parcelamento['parcelas'] > 1
            ? $modelo->adicionarVarios(financeiroLinhasDoParcelamento($dados, $parcelamento, $dados['usuarios_id']))
            : $modelo->adicionar($dados) !== null;
        if (! $salvou) {
            throw HttpErro::interno('Não foi possível gravar o lançamento.');
        }
        $this->ctx->auditar('Adicionou um lançamento em Financeiro');

        $criados = $this->consulta()->where('lancamentos.idLancamentos >', $antes)->where('lancamentos.usuarios_id', $dados['usuarios_id'])
            ->order_by('lancamentos.idLancamentos', 'ASC')->get()->result();

        return Resposta::json(['data' => array_map([self::class, 'de'], $criados)], 201, ['Location' => $this->ctx->url('lancamentos/' . (int) ($criados[0]->idLancamentos ?? 0))]);
    }

    public function atualizar(): Resposta
    {
        $lancamento = $this->lancamento();
        $id = (int) $lancamento->idLancamentos;
        [$entrada] = $this->ctx->corpoParcial([
            'tipo' => $lancamento->tipo === 'despesa' ? 'despesa' : 'receita',
            'descricao' => (string) $lancamento->descricao,
            'cliente_fornecedor' => (string) $lancamento->cliente_fornecedor,
            'cliente_id' => Valor::inteiroOuNulo($lancamento->clientes_id),
            'valor' => Valor::decimal($lancamento->valor),
            'desconto' => Valor::decimal(financeiroDescontoEmReais($lancamento)),
            'data_vencimento' => Valor::data($lancamento->data_vencimento),
            'pago' => (int) $lancamento->baixado === 1,
            'data_pagamento' => Valor::data($lancamento->data_pagamento),
            'forma_pagamento' => Valor::textoOuNulo($lancamento->forma_pgto),
            'observacoes' => (string) $lancamento->observacoes,
        ]);
        [$dados, $erros] = $this->dadosDoFormulario($entrada, substr((string) $lancamento->data_pagamento, 0, 10));
        if ($erros !== []) {
            throw HttpErro::validacao($erros);
        }
        $dados['usuarios_id'] = $this->ctx->titular()->id;
        if (! $this->modelo()->atualizar($id, $dados)) {
            throw HttpErro::interno('Não foi possível gravar o lançamento.');
        }
        $this->ctx->auditar('Alterou um lançamento no financeiro. ID ' . $id);

        return Resposta::dados(self::de($this->lancamento($id)));
    }

    public function excluir(): Resposta
    {
        $id = (int) $this->lancamento()->idLancamentos;
        if (! $this->modelo()->excluir($id, $this->ctx->titular()->id)) {
            throw HttpErro::interno('Não foi possível excluir o lançamento.');
        }
        $this->ctx->auditar('Excluiu um lançamento. ID: ' . $id);

        return Resposta::semConteudo();
    }

    private function modelo(): object
    {
        return $this->ctx->modelo('Financeiro_model');
    }

    private function consulta(): object
    {
        return $this->db->select('lancamentos.*, usuarios.nome AS modificado_por')->from('lancamentos')->join('usuarios', 'usuarios.idUsuarios = lancamentos.usuarios_id', 'left');
    }

    private function lancamento(?int $id = null): object
    {
        $linha = $this->consulta()->where('lancamentos.idLancamentos', $id ?? $this->ctx->id())->limit(1)->get()->row();
        if (! $linha) {
            throw HttpErro::naoEncontrado('Lançamento não encontrado.');
        }

        return $linha;
    }

    /** @return array<string, string> */
    private function filtros(array $consulta): array
    {
        $de = $consulta['de'] ?? null;
        $ate = $consulta['ate'] ?? null;
        if ($de !== null && $ate !== null) {
            $intervalo = $de <= $ate ? [$de, $ate] : [$ate, $de];
        } else {
            $intervalo = financeiroPeriodo($consulta['periodo'] ?? 'mes', new DateTimeImmutable('today'));
        }

        return array_filter([
            'pesquisa' => $consulta['busca'] ?? '',
            'tipo' => $consulta['tipo'] ?? '',
            'status' => $consulta['situacao'] ?? '',
            'de' => $intervalo[0],
            'ate' => $intervalo[1],
        ], static fn ($v) => $v !== '');
    }

    /** @return array{0: array<string, mixed>, 1: array<string, string>} */
    private function dadosDoFormulario(array $entrada, ?string $pagamentoAtual): array
    {
        $cliente = ($entrada['cliente_id'] ?? null) !== null ? $this->modelo()->getCliente((int) $entrada['cliente_id']) : null;
        [$dados, $erros] = financeiroLancamentoDoFormulario([
            'tipo' => (string) $entrada['tipo'],
            'descricao' => (string) $entrada['descricao'],
            'cliente_fornecedor' => (string) $entrada['cliente_fornecedor'],
            'valor' => (string) $entrada['valor'],
            'desconto' => (string) ($entrada['desconto'] ?? ''),
            'data_vencimento' => (string) $entrada['data_vencimento'],
            'baixado' => ! empty($entrada['pago']) ? '1' : '',
            'data_pagamento' => (string) ($entrada['data_pagamento'] ?? ''),
            'forma_pgto' => (string) ($entrada['forma_pagamento'] ?? ''),
            'observacoes' => (string) ($entrada['observacoes'] ?? ''),
        ], [
            'hoje' => date('Y-m-d'),
            'controle_baixa' => $this->ctx->configuracao('control_baixa', '0') == '1',
            'pagamento_atual' => $pagamentoAtual,
        ], $cliente);

        return [$dados, self::traduzir($erros)];
    }

    /** @return array<string, string> */
    private static function traduzir(array $erros): array
    {
        $mapa = ['baixado' => 'pago', 'forma_pgto' => 'forma_pagamento'];
        $saida = [];
        foreach ($erros as $campo => $mensagem) {
            $saida[$mapa[$campo] ?? $campo] = (string) $mensagem;
        }

        return $saida;
    }
}
