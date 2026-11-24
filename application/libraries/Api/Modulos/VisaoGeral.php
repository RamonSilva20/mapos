<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\OrdemDeServico;
use Libraries\Api\Recursos\Valor;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;

final class VisaoGeral extends Modulo
{
    private const LIMITE_SUGESTOES = 25;

    public static function rotas(): array
    {
        $porStatus = Schema::mapa(Schema::inteiro())->descricao('Quantidade por status.')->exemplo(['Aberto' => 4, 'Em Andamento' => 2]);
        $sugestao = Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'nome' => Schema::texto()->obrigatorio(),
            'detalhe' => Schema::texto()->obrigatorio(),
            'preco' => Schema::decimal()->anulavel()->obrigatorio(),
            'estoque' => Schema::inteiro()->anulavel()->obrigatorio(),
        ])->nome('Sugestao');
        $termo = Schema::objeto(['busca' => Schema::texto()->max(100)->descricao('Sem termo, traz os primeiros em ordem alfabética.')]);

        return [
            Rota::get('/painel', [self::class, 'painel'])->tag('Painel')
                ->resumo('Números do painel', 'Cada seção só vem para quem pode ver: `os` (vOs), `vendas` (vVenda), `financeiro` (vLancamento), `balanco` (rFinanceiro), `estoque_baixo` (vProduto) e `avisos` (cSistema). As outras chegam nulas.')
                ->consulta(Schema::objeto([
                    'mes' => Schema::enum(['atual', 'passado'])->padrao('atual')->descricao('Mês do resumo de OS.'),
                    'ano' => Schema::inteiro()->min(2000)->max(2100)->descricao('Ano do balanço. Padrão: o atual.'),
                ]))
                ->dados(Schema::objeto([
                    'os' => Schema::objeto([
                        'por_status' => (clone $porStatus)->obrigatorio(),
                        'mes' => Schema::objeto([
                            'inicio' => Schema::data()->obrigatorio(),
                            'fim' => Schema::data()->obrigatorio(),
                            'por_status' => Schema::lista(Schema::objeto([
                                'status' => Schema::texto()->obrigatorio(),
                                'total' => Schema::inteiro()->obrigatorio(),
                                'valor' => Schema::decimal()->obrigatorio(),
                            ]))->obrigatorio(),
                        ])->obrigatorio(),
                    ])->anulavel()->obrigatorio(),
                    'vendas' => Schema::objeto(['por_status' => (clone $porStatus)->obrigatorio()])->anulavel()->obrigatorio(),
                    'financeiro' => Schema::objeto([
                        'mes' => Schema::objeto([
                            'receitas' => Schema::decimal()->obrigatorio(),
                            'despesas' => Schema::decimal()->obrigatorio(),
                            'saldo' => Schema::decimal()->obrigatorio(),
                        ])->obrigatorio(),
                        'saldo_realizado' => Schema::decimal()->obrigatorio(),
                        'a_receber' => Schema::decimal()->obrigatorio(),
                        'a_pagar' => Schema::decimal()->obrigatorio(),
                        'proximos_pendentes' => Schema::lista(Schema::objeto([
                            'id' => Schema::inteiro()->obrigatorio(),
                            'tipo' => Schema::texto()->obrigatorio(),
                            'descricao' => Schema::texto()->obrigatorio(),
                            'cliente_fornecedor' => Schema::texto()->anulavel()->obrigatorio(),
                            'data_vencimento' => Schema::data()->anulavel()->obrigatorio(),
                            'valor_liquido' => Schema::decimal()->obrigatorio(),
                        ]))->obrigatorio(),
                    ])->anulavel()->obrigatorio(),
                    'balanco' => Schema::objeto([
                        'ano' => Schema::inteiro()->obrigatorio(),
                        'meses' => Schema::lista(Schema::objeto([
                            'mes' => Schema::inteiro()->obrigatorio()->exemplo(1),
                            'receitas' => Schema::decimal()->obrigatorio(),
                            'despesas' => Schema::decimal()->obrigatorio(),
                            'saldo' => Schema::decimal()->obrigatorio(),
                        ]))->obrigatorio(),
                    ])->anulavel()->obrigatorio(),
                    'estoque_baixo' => Schema::lista(Schema::objeto([
                        'id' => Schema::inteiro()->obrigatorio(),
                        'descricao' => Schema::texto()->obrigatorio(),
                        'estoque' => Schema::inteiro()->obrigatorio(),
                        'estoque_minimo' => Schema::inteiro()->obrigatorio(),
                    ]))->anulavel()->obrigatorio(),
                    'avisos' => Schema::lista(Schema::texto())->anulavel()->obrigatorio(),
                ])),

            Rota::get('/calendario', [self::class, 'calendario'])->permissao('vOs')->tag('Painel')
                ->resumo('OS por data final', 'Para montar a agenda. O intervalo vai até 366 dias.')
                ->consulta(Schema::objeto([
                    'de' => Schema::data()->obrigatorio(),
                    'ate' => Schema::data()->obrigatorio(),
                    'status' => Schema::enum(OrdemDeServico::status()),
                ]))
                ->dados(Schema::lista(Schema::objeto([
                    'os_id' => Schema::inteiro()->obrigatorio(),
                    'cliente' => Schema::texto()->obrigatorio(),
                    'status' => Schema::texto()->obrigatorio(),
                    'data_inicial' => Schema::data()->anulavel()->obrigatorio(),
                    'data_final' => Schema::data()->anulavel()->obrigatorio(),
                    'total' => Schema::decimal()->obrigatorio(),
                    'faturado' => Schema::booleano()->obrigatorio(),
                ]))),

            Rota::get('/busca', [self::class, 'busca'])->tag('Busca')
                ->resumo('Busca geral', 'Clientes, OS, produtos e serviços. Cada grupo só vem para quem pode ver; os outros chegam vazios.')
                ->consulta(Schema::objeto(['busca' => Schema::texto()->obrigatorio()->min(2)->max(100)]))
                ->dados(Schema::objeto([
                    'clientes' => Schema::lista(Comuns::pessoa())->obrigatorio(),
                    'os' => Schema::lista(Schema::objeto([
                        'id' => Schema::inteiro()->obrigatorio(),
                        'cliente' => Schema::texto()->anulavel()->obrigatorio(),
                        'status' => Schema::texto()->obrigatorio(),
                    ]))->obrigatorio(),
                    'produtos' => Schema::lista($sugestao)->obrigatorio(),
                    'servicos' => Schema::lista($sugestao)->obrigatorio(),
                ])),

            Rota::get('/busca/clientes', [self::class, 'sugerirClientes'])->permissao('aOs', 'eOs', 'rOs', 'aVenda', 'eVenda', 'rVenda', 'vCliente')->tag('Busca')
                ->resumo('Sugestões de cliente', 'Para escolher o cliente de uma OS ou venda. Também para quem só edita OS ou vendas, sem acesso ao cadastro de clientes.')
                ->consulta($termo)->dados(Schema::lista($sugestao)),

            Rota::get('/busca/produtos', [self::class, 'sugerirProdutos'])->permissao('aOs', 'eOs', 'aVenda', 'eVenda', 'vProduto')->tag('Busca')
                ->resumo('Sugestões de produto', 'Só produtos marcados para saída (OS e vendas).')
                ->consulta($termo)->dados(Schema::lista($sugestao)),

            Rota::get('/busca/servicos', [self::class, 'sugerirServicos'])->permissao('aOs', 'eOs', 'vServico')->tag('Busca')
                ->resumo('Sugestões de serviço')
                ->consulta($termo)->dados(Schema::lista($sugestao)),

            Rota::get('/busca/usuarios', [self::class, 'sugerirUsuarios'])->permissao('aOs', 'eOs', 'rOs', 'aVenda', 'eVenda', 'rVenda', 'cUsuario')->tag('Busca')
                ->resumo('Sugestões de técnico ou vendedor', 'Só usuários ativos.')
                ->consulta($termo)->dados(Schema::lista($sugestao)),

            Rota::get('/busca/garantias', [self::class, 'sugerirGarantias'])->permissao('aOs', 'eOs', 'vGarantia')->tag('Busca')
                ->resumo('Sugestões de termo de garantia')
                ->consulta($termo)->dados(Schema::lista($sugestao)),
        ];
    }

    public function painel(): Resposta
    {
        $consulta = $this->ctx->consulta();
        $mapos = $this->ctx->modelo('Mapos_model');
        $hoje = date('Y-m-d');
        $anoAtual = (int) date('Y');
        $dados = ['os' => null, 'vendas' => null, 'financeiro' => null, 'balanco' => null, 'estoque_baixo' => null, 'avisos' => null];

        if ($this->ctx->pode('vOs')) {
            $intervalo = painelMesIntervalo($consulta['mes'], $hoje);
            $dados['os'] = [
                'por_status' => (object) $mapos->contarPorStatus('os'),
                'mes' => [
                    'inicio' => substr($intervalo['inicio'], 0, 10),
                    'fim' => substr($intervalo['fim'], 0, 10),
                    'por_status' => array_map(static fn (array $f): array => ['status' => $f['status'], 'total' => $f['total'], 'valor' => Valor::decimal($f['valor'])], painelOsGrafico($mapos->osResumoPorStatus($intervalo['inicio'], $intervalo['fim']))),
                ],
            ];
        }
        if ($this->ctx->pode('vVenda')) {
            $dados['vendas'] = ['por_status' => (object) $mapos->contarPorStatus('vendas')];
        }

        $financeiro = $this->ctx->modelo('Financeiro_model');
        if ($this->ctx->pode('vLancamento')) {
            $balanco = painelBalanco($financeiro->balancoAnual($anoAtual), $anoAtual);
            $indice = (int) date('n') - 1;
            $geral = $financeiro->visaoGeral();
            $dados['financeiro'] = [
                'mes' => [
                    'receitas' => Valor::decimal($balanco['receitas'][$indice]),
                    'despesas' => Valor::decimal($balanco['despesas'][$indice]),
                    'saldo' => Valor::decimal($balanco['saldo'][$indice]),
                ],
                'saldo_realizado' => Valor::decimal($geral['saldo_realizado']),
                'a_receber' => Valor::decimal($geral['a_receber']),
                'a_pagar' => Valor::decimal($geral['a_pagar']),
                'proximos_pendentes' => array_map(static fn (object $l): array => [
                    'id' => (int) $l->idLancamentos,
                    'tipo' => (string) $l->tipo,
                    'descricao' => (string) $l->descricao,
                    'cliente_fornecedor' => Valor::textoOuNulo($l->cliente_fornecedor),
                    'data_vencimento' => Valor::data($l->data_vencimento),
                    'valor_liquido' => Valor::decimal($l->liquido),
                ], $financeiro->proximosPendentes(8)),
            ];
        }
        if ($this->ctx->pode('rFinanceiro')) {
            $ano = painelAno((string) ($consulta['ano'] ?? ''), $anoAtual);
            $balanco = painelBalanco($financeiro->balancoAnual($ano), $ano);
            $meses = [];
            for ($i = 0; $i < 12; $i++) {
                $meses[] = ['mes' => $i + 1, 'receitas' => Valor::decimal($balanco['receitas'][$i]), 'despesas' => Valor::decimal($balanco['despesas'][$i]), 'saldo' => Valor::decimal($balanco['saldo'][$i])];
            }
            $dados['balanco'] = ['ano' => $ano, 'meses' => $meses];
        }
        if ($this->ctx->pode('vProduto')) {
            $dados['estoque_baixo'] = array_map(static fn (object $p): array => [
                'id' => (int) $p->idProdutos,
                'descricao' => (string) $p->descricao,
                'estoque' => (int) $p->estoque,
                'estoque_minimo' => (int) $p->estoqueMinimo,
            ], $mapos->produtosEstoqueBaixo(8));
        }
        if ($this->ctx->pode('cSistema')) {
            $dados['avisos'] = array_map('strval', manutencaoAvisos($this->db, $_ENV, ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'));
        }

        return Resposta::dados($dados);
    }

    public function calendario(): Resposta
    {
        $consulta = $this->ctx->consulta();
        [$de, $ate] = $consulta['de'] <= $consulta['ate'] ? [$consulta['de'], $consulta['ate']] : [$consulta['ate'], $consulta['de']];
        if ((strtotime($ate) - strtotime($de)) / 86400 > 366) {
            throw HttpErro::validacao(['ate' => 'O intervalo vai até 366 dias.']);
        }

        $linhas = $this->ctx->modelo('Mapos_model')->calendario($de, $ate, $consulta['status'] ?? null);

        return Resposta::dados(array_map(static function (object $os): array {
            $bruto = (float) $os->totalProdutos + (float) $os->totalServicos;
            $comDesconto = (float) ($os->valor_desconto ?? 0);

            return [
                'os_id' => (int) $os->idOs,
                'cliente' => (string) ($os->nomeCliente ?? ''),
                'status' => (string) $os->status,
                'data_inicial' => Valor::data($os->dataInicial),
                'data_final' => Valor::data($os->dataFinal),
                'total' => Valor::decimal($comDesconto > 0 && $comDesconto < $bruto ? $comDesconto : $bruto),
                'faturado' => (int) $os->faturado === 1,
            ];
        }, $linhas));
    }

    public function busca(): Resposta
    {
        $termo = $this->ctx->consulta()['busca'];
        $r = $this->ctx->modelo('Mapos_model')->buscaRapida($termo, 10);

        return Resposta::dados([
            'clientes' => $this->ctx->pode('vCliente') ? array_map(static fn ($c) => ['id' => (int) $c->idClientes, 'nome' => (string) $c->nomeCliente], $r['clientes']) : [],
            'os' => $this->ctx->pode('vOs') ? array_map(static fn ($o) => ['id' => (int) $o->idOs, 'cliente' => Valor::textoOuNulo($o->nomeCliente ?? null), 'status' => (string) $o->status], $r['os']) : [],
            'produtos' => $this->ctx->pode('vProduto') ? array_map(static fn ($p) => self::sugestao((int) $p->idProdutos, $p->descricao, '', $p->precoVenda, $p->estoque), $r['produtos']) : [],
            'servicos' => $this->ctx->pode('vServico') ? array_map(static fn ($s) => self::sugestao((int) $s->idServicos, $s->nome, '', $s->preco), $r['servicos']) : [],
        ]);
    }

    public function sugerirClientes(): Resposta
    {
        $termo = (string) ($this->ctx->consulta()['busca'] ?? '');
        $linhas = $this->db->select('idClientes, nomeCliente, telefone, celular, documento')
            ->group_start()->like('nomeCliente', $termo)->or_like('telefone', $termo)->or_like('celular', $termo)->or_like('documento', $termo)->group_end()
            ->order_by('nomeCliente', 'ASC')->limit(self::LIMITE_SUGESTOES)->get('clientes')->result();

        return Resposta::dados(array_map(static fn ($c) => self::sugestao((int) $c->idClientes, $c->nomeCliente, implode(' · ', array_filter([(string) $c->documento, (string) ($c->celular ?: $c->telefone)]))), $linhas));
    }

    public function sugerirProdutos(): Resposta
    {
        $termo = (string) ($this->ctx->consulta()['busca'] ?? '');
        $linhas = $this->db->select('idProdutos, descricao, precoVenda, estoque')
            ->group_start()->like('codDeBarra', $termo)->or_like('descricao', $termo)->group_end()
            ->where('saida', 1)->order_by('descricao', 'ASC')->limit(self::LIMITE_SUGESTOES)->get('produtos')->result();

        return Resposta::dados(array_map(static fn ($p) => self::sugestao((int) $p->idProdutos, $p->descricao, 'Estoque: ' . (int) $p->estoque, $p->precoVenda, $p->estoque), $linhas));
    }

    public function sugerirServicos(): Resposta
    {
        $linhas = $this->db->select('idServicos, nome, preco')->like('nome', (string) ($this->ctx->consulta()['busca'] ?? ''))->order_by('nome', 'ASC')->limit(self::LIMITE_SUGESTOES)->get('servicos')->result();

        return Resposta::dados(array_map(static fn ($s) => self::sugestao((int) $s->idServicos, $s->nome, '', $s->preco), $linhas));
    }

    public function sugerirUsuarios(): Resposta
    {
        $linhas = $this->db->select('idUsuarios, nome, telefone')->like('nome', (string) ($this->ctx->consulta()['busca'] ?? ''))->where('situacao', 1)->order_by('nome', 'ASC')->limit(self::LIMITE_SUGESTOES)->get('usuarios')->result();

        return Resposta::dados(array_map(static fn ($u) => self::sugestao((int) $u->idUsuarios, $u->nome, (string) $u->telefone), $linhas));
    }

    public function sugerirGarantias(): Resposta
    {
        $linhas = $this->db->select('idGarantias, refGarantia')->like('refGarantia', (string) ($this->ctx->consulta()['busca'] ?? ''))->order_by('refGarantia', 'ASC')->limit(self::LIMITE_SUGESTOES)->get('garantias')->result();

        return Resposta::dados(array_map(static fn ($g) => self::sugestao((int) $g->idGarantias, $g->refGarantia, ''), $linhas));
    }

    private static function sugestao(int $id, mixed $nome, string $detalhe, mixed $preco = null, mixed $estoque = null): array
    {
        return [
            'id' => $id,
            'nome' => (string) $nome,
            'detalhe' => $detalhe,
            'preco' => $preco === null ? null : Valor::decimal($preco),
            'estoque' => $estoque === null ? null : (int) $estoque,
        ];
    }
}
