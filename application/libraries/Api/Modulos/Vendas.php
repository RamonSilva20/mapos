<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\OrdemDeServico;
use Libraries\Api\Recursos\Venda;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;
use Libraries\Api\Servicos\Comercial;

final class Vendas extends Modulo
{
    public static function rotas(): array
    {
        $tag = 'Vendas';
        $venda = Venda::schema();

        return [
            Rota::get('/vendas', [self::class, 'listar'])->permissao('vVenda')->tag($tag)
                ->resumo('Lista as vendas')
                ->consulta(Comuns::paginacaoConsulta([
                    'busca' => Schema::texto()->max(100)->descricao('Nome ou documento do cliente, ou número da venda.'),
                    'status' => Schema::enum([...OrdemDeServico::status(), 'abertas'])->descricao('Um status, ou `abertas` (orçamento, negociação, aberto e aprovado).'),
                    'de' => Schema::data(),
                    'ate' => Schema::data(),
                    'cliente_id' => Schema::inteiro()->min(1),
                    'vendedor_id' => Schema::inteiro()->min(1),
                ]))
                ->lista(Venda::schemaResumo()),

            Rota::post('/vendas', [self::class, 'criar'])->permissao('aVenda')->tag($tag)
                ->resumo('Abre uma venda', 'Depois de criar, adicione os produtos.')
                ->corpo(self::entrada())
                ->criado($venda),

            Rota::get('/vendas/{id}', [self::class, 'ver'])->permissao('vVenda')->tag($tag)
                ->resumo('Mostra uma venda completa')
                ->dados($venda)
                ->erros(404),

            Rota::patch('/vendas/{id}', [self::class, 'atualizar'])->permissao('eVenda')->tag($tag)
                ->resumo('Altera os dados da venda', 'Mudar para `Cancelado` devolve os produtos ao estoque; sair de `Cancelado` volta a debitá-los.')
                ->corpo(self::entrada()->semObrigatorios())
                ->dados($venda)
                ->erros(403, 404),

            Rota::delete('/vendas/{id}', [self::class, 'excluir'])->permissao('dVenda')->tag($tag)
                ->resumo('Exclui uma venda', 'Recusa venda faturada ou cancelada (sem a configuração de edição) e venda com cobrança ativa. Devolve os produtos ao estoque.')
                ->semConteudo()
                ->erros(403, 404, 409),

            Rota::post('/vendas/{id}/produtos', [self::class, 'adicionarProduto'])->permissao('eVenda')->tag($tag)
                ->resumo('Adiciona um produto', 'Com o controle de estoque ligado, o produto sai do estoque. Mudar os itens tira o desconto geral.')
                ->corpo(Comercial::schemaItemEntrada(true))
                ->criado($venda, 'Produto adicionado; a venda atualizada.')
                ->erros(403, 404),

            Rota::delete('/vendas/{id}/produtos/{item}', [self::class, 'removerProduto'])->permissao('eVenda')->tag($tag)
                ->resumo('Remove um produto')
                ->dados($venda)
                ->erros(403, 404),

            Rota::put('/vendas/{id}/produtos/{item}/desconto', [self::class, 'descontoProduto'])->permissao('eVenda')->tag($tag)
                ->resumo('Desconto de um produto')
                ->corpo(Comercial::schemaDescontoEntrada())
                ->dados($venda)
                ->erros(403, 404),

            Rota::put('/vendas/{id}/desconto', [self::class, 'desconto'])->permissao('eVenda')->tag($tag)
                ->resumo('Desconto geral da venda')
                ->corpo(Comercial::schemaDescontoEntrada())
                ->dados($venda)
                ->erros(403, 404),

            Rota::post('/vendas/{id}/faturar', [self::class, 'faturar'])->permissao('eVenda')->tag($tag)
                ->resumo('Fatura a venda', 'Cria a receita no financeiro com o total e marca a venda como faturada.')
                ->corpo(Comercial::schemaFaturaEntrada())
                ->dados($venda)
                ->erros(403, 404),

            Rota::get('/vendas/{id}/pix', [self::class, 'pix'])->permissao('vVenda')->tag($tag)
                ->resumo('PIX para pagar a venda')
                ->dados(Ordens::schemaPix())
                ->erros(404),
        ];
    }

    private static function entrada(): Schema
    {
        return Schema::objeto([
            'cliente_id' => Schema::inteiro()->obrigatorio()->min(1),
            'vendedor_id' => Schema::inteiro()->min(1)->descricao('Sem vendedor, fica o usuário logado.'),
            'status' => Schema::enum(OrdemDeServico::status())->padrao('Orçamento'),
            'data_venda' => Schema::data()->descricao('Padrão: hoje.'),
            'garantia_dias' => Schema::inteiro()->min(0)->max(9999)->anulavel(),
            'observacoes' => Schema::texto()->max(20000),
            'observacoes_cliente' => Schema::texto()->max(20000),
        ])->nome('VendaEntrada');
    }

    public function listar(): Resposta
    {
        $consulta = $this->ctx->consulta();

        return $this->listarPelaConsulta(function () use ($consulta): object {
            $q = Venda::consultaResumo($this->db);
            $busca = $consulta['busca'] ?? '';
            if ($busca !== '') {
                $q->group_start()->like('clientes.nomeCliente', $busca)->or_like('clientes.documento', $busca);
                if (ctype_digit($busca)) {
                    $q->or_where('vendas.idVendas', (int) $busca);
                }
                $q->group_end();
            }
            if (($consulta['status'] ?? null) !== null) {
                $grupo = painelGrupoStatus($consulta['status']);
                $grupo !== null ? $q->where_in('vendas.status', $grupo) : $q->where('vendas.status', $consulta['status']);
            }
            foreach (['de' => 'vendas.dataVenda >=', 'ate' => 'vendas.dataVenda <=', 'cliente_id' => 'vendas.clientes_id', 'vendedor_id' => 'vendas.usuarios_id'] as $campo => $coluna) {
                if (($consulta[$campo] ?? null) !== null) {
                    $q->where($coluna, $consulta[$campo]);
                }
            }

            return $q;
        }, 'vendas.idVendas', [Venda::class, 'resumoDe']);
    }

    public function ver(): Resposta
    {
        return Resposta::dados($this->detalhe($this->venda()));
    }

    public function criar(): Resposta
    {
        $entrada = $this->ctx->corpo();
        $entrada['vendedor_id'] ??= $this->ctx->titular()->id;
        $entrada['data_venda'] ??= date('Y-m-d');
        $id = $this->inserirLinha('vendas', $this->paraBanco($entrada) + ['faturado' => 0]);
        $this->ctx->auditar('Adicionou uma venda. ID: ' . $id);

        return Resposta::criado($this->detalhe($this->venda($id)), $this->ctx->url('vendas/' . $id));
    }

    public function atualizar(): Resposta
    {
        $venda = $this->vendaEditavel();
        $id = (int) $venda->idVendas;
        [$entrada] = $this->ctx->corpoParcial([
            'cliente_id' => (int) $venda->clientes_id,
            'vendedor_id' => (int) $venda->usuarios_id,
            'status' => (string) $venda->status,
            'data_venda' => osDataParaCampo($venda->dataVenda) ?: null,
            'garantia_dias' => is_numeric($venda->garantia) ? (int) $venda->garantia : null,
            'observacoes' => osTextoParaEdicao($venda->observacoes),
            'observacoes_cliente' => osTextoParaEdicao($venda->observacoes_cliente),
        ]);
        $dados = $this->paraBanco($entrada);

        $this->transacao(function () use ($venda, $id, $dados): void {
            $antes = strtolower((string) $venda->status);
            $depois = strtolower((string) $dados['status']);
            $comercial = new Comercial($this->ctx);
            if ($depois === 'cancelado' && $antes !== 'cancelado') {
                $comercial->movimentarEstoque($this->modelo()->getProdutos($id), '+', 'Motivo: Cancelamento da venda ' . $id);
            }
            if ($antes === 'cancelado' && $depois !== 'cancelado') {
                $comercial->movimentarEstoque($this->modelo()->getProdutos($id), '-', 'Motivo: Venda ' . $id . ' saiu de Cancelado');
            }
            $this->atualizarLinha('vendas', 'idVendas', $id, $dados);
        });
        $this->ctx->auditar('Alterou uma venda. ID: ' . $id);

        return Resposta::dados($this->detalhe($this->venda($id)));
    }

    public function excluir(): Resposta
    {
        $venda = $this->vendaEditavel('Venda já faturada ou cancelada: não pode ser excluída.');
        $id = (int) $venda->idVendas;

        $cobranca = $this->db->select('idCobranca, status')->where('vendas_id', $id)->get('cobrancas', 1)->row();
        if ($cobranca && $cobranca->status !== 'canceled') {
            throw HttpErro::conflito('Existe uma cobrança associada a esta venda: cancele ou exclua a cobrança primeiro.', 'cobranca_ativa');
        }

        $this->transacao(function () use ($venda, $id, $cobranca): void {
            if ($cobranca) {
                $this->removerLinha('cobrancas', 'vendas_id', $id);
            }
            if (strtolower((string) $venda->status) !== 'cancelado') {
                (new Comercial($this->ctx))->movimentarEstoque($this->modelo()->getProdutos($id), '+', 'Motivo: Exclusão da venda ' . $id);
            }
            $this->removerLinha('itens_de_vendas', 'vendas_id', $id);
            $this->removerLinha('vendas', 'idVendas', $id);
            if ((int) $venda->faturado === 1) {
                $this->modelo()->excluirFatura($id, isset($venda->lancamentos_id) ? (int) $venda->lancamentos_id : null);
            }
        });
        $this->ctx->auditar('Removeu uma venda. ID: ' . $id);

        return Resposta::semConteudo();
    }

    public function adicionarProduto(): Resposta
    {
        $venda = $this->vendaEditavel(null, true);
        $id = (int) $venda->idVendas;
        $comercial = new Comercial($this->ctx);
        $dados = $comercial->item($this->ctx->corpo(), true);

        $this->transacao(function () use ($dados, $id, $comercial): void {
            $this->inserirLinha('itens_de_vendas', $dados + ['vendas_id' => $id]);
            $comercial->movimentarEstoque([(object) $dados], '-', 'Motivo: Adicionado à venda ' . $id);
            $this->modelo()->zerarDesconto($id);
        });
        $this->ctx->auditar('Adicionou produto à venda com ID: ' . $id);

        return Resposta::criado($this->detalhe($this->venda($id)), $this->ctx->url('vendas/' . $id));
    }

    public function removerProduto(): Resposta
    {
        $venda = $this->vendaEditavel(null, true);
        $id = (int) $venda->idVendas;
        $item = $this->item($id);

        $this->transacao(function () use ($item, $id): void {
            $this->removerLinha('itens_de_vendas', 'idItens', (int) $item->idItens);
            (new Comercial($this->ctx))->movimentarEstoque([$item], '+', 'Motivo: Removido da venda ' . $id);
            $this->modelo()->zerarDesconto($id);
        });
        $this->ctx->auditar('Removeu produto da venda. ID da Venda: ' . $id . ', ID do Produto: ' . (int) $item->produtos_id);

        return Resposta::dados($this->detalhe($this->venda($id)));
    }

    public function descontoProduto(): Resposta
    {
        $venda = $this->vendaEditavel(null, true);
        $id = (int) $venda->idVendas;
        $item = $this->item($id);
        $desconto = (new Comercial($this->ctx))->descontoDoItem($item, $this->ctx->corpo());

        $this->transacao(function () use ($item, $id, $desconto): void {
            $this->atualizarLinha('itens_de_vendas', 'idItens', (int) $item->idItens, $desconto);
            $this->modelo()->zerarDesconto($id);
        });
        $this->ctx->auditar('Alterou o desconto de um produto da venda. ID da Venda: ' . $id);

        return Resposta::dados($this->detalhe($this->venda($id)));
    }

    public function desconto(): Resposta
    {
        $venda = $this->vendaEditavel();
        $id = (int) $venda->idVendas;
        $totais = $this->modelo()->totais($venda);
        $dados = (new Comercial($this->ctx))->descontoGeral((float) $totais['bruto'], $this->ctx->corpo(), 'venda', 'produtos');

        $this->atualizarLinha('vendas', 'idVendas', $id, $dados);
        $this->ctx->auditar(($dados['valor_desconto'] > 0 ? 'Adicionou um desconto' : 'Removeu o desconto') . ' na Venda. ID: ' . $id);

        return Resposta::dados($this->detalhe($this->venda($id)));
    }

    public function faturar(): Resposta
    {
        $venda = $this->vendaEditavel();
        $id = (int) $venda->idVendas;
        if ((int) $venda->faturado === 1 || $venda->status === 'Cancelado') {
            throw HttpErro::regraDeNegocio('Esta venda já foi faturada ou está cancelada.', 'ja_faturada');
        }

        $totais = $this->modelo()->totais($venda);
        [$lancamento, $erros] = vendaFaturaDoFormulario(Comercial::postDaFatura($this->ctx->corpo()), $venda, $totais, $this->ctx->titular()->id);
        if (isset($erros['_geral'])) {
            throw HttpErro::regraDeNegocio($erros['_geral'], 'sem_itens');
        }
        if ($erros !== []) {
            throw HttpErro::validacao(Comercial::errosDaFatura($erros));
        }

        $this->transacao(function () use ($lancamento, $totais, $id): void {
            $idLancamento = $this->inserirLinha('lancamentos', $lancamento);
            $this->atualizarLinha('vendas', 'idVendas', $id, [
                'faturado' => 1,
                'valorTotal' => $totais['bruto'],
                'valor_desconto' => $totais['total'],
                'lancamentos_id' => $idLancamento,
                'status' => 'Faturado',
            ]);
        });
        $this->ctx->auditar('Faturou a venda com ID.' . $id);

        return Resposta::dados($this->detalhe($this->venda($id)));
    }

    public function pix(): Resposta
    {
        $venda = $this->venda();
        $id = (int) $venda->idVendas;
        $chave = (string) $this->ctx->configuracao('pix_key', '');
        $emitente = $this->ctx->modelo('Mapos_model')->getEmitente();
        $payload = $chave === '' || ! $emitente ? null : $this->modelo()->getPixPayload($id, $chave, $emitente);
        if ($payload === null) {
            throw HttpErro::naoEncontrado('PIX indisponível: configure a chave PIX e o emitente, e confira se a venda tem valor.');
        }

        return Resposta::dados([
            'valor' => number_format((float) $this->modelo()->totais($venda)['total'], 2, '.', ''),
            'payload' => $payload,
            'qr_code' => (string) $this->modelo()->getQrCode($id, $chave, $emitente),
            'chave' => Ordens::formatarChavePix($chave),
        ]);
    }

    private function modelo(): object
    {
        return $this->ctx->modelo('Vendas_model');
    }

    private function venda(?int $id = null): object
    {
        $venda = $this->modelo()->getById($id ?? $this->ctx->id());
        if (! $venda) {
            throw HttpErro::naoEncontrado('Venda não encontrada.');
        }

        return $venda;
    }

    private function editavel(object $venda): bool
    {
        return osEditavel($venda, true, $this->ctx->configuracao('control_edit_vendas') == '1');
    }

    private function vendaEditavel(?string $mensagem = null, bool $mexeNosItens = false): object
    {
        $venda = $this->venda();
        if (! $this->editavel($venda)) {
            throw HttpErro::proibido($mensagem ?? 'Esta venda está faturada ou cancelada e não pode mais ser alterada.', 'venda_fechada');
        }
        if ($mexeNosItens && $venda->status === 'Cancelado') {
            throw HttpErro::proibido('Esta venda está cancelada: reabra a venda (status) para mexer nos produtos.', 'venda_cancelada');
        }

        return $venda;
    }

    private function item(int $id): object
    {
        $item = $this->modelo()->getProdutoDaVenda($id, $this->ctx->id('item'));
        if (! $item) {
            throw HttpErro::naoEncontrado('Produto não encontrado nesta venda.');
        }

        return $item;
    }

    private function detalhe(object $venda): array
    {
        $id = (int) $venda->idVendas;
        $cobranca = $this->db->select('idCobranca')->where('vendas_id', $id)->order_by('idCobranca', 'DESC')->get('cobrancas', 1)->row();

        return Venda::de($venda, [
            'totais' => $this->modelo()->totais($venda),
            'editavel' => $this->editavel($venda),
            'produtos' => $this->modelo()->getProdutos($id),
            'cobranca_id' => $cobranca ? (int) $cobranca->idCobranca : null,
        ]);
    }

    private function paraBanco(array $entrada): array
    {
        $erros = [];
        if (! $this->db->where('idClientes', $entrada['cliente_id'])->count_all_results('clientes')) {
            $erros['cliente_id'] = 'Cliente não encontrado.';
        }
        if (! $this->db->where('idUsuarios', $entrada['vendedor_id'])->where('situacao', 1)->count_all_results('usuarios')) {
            $erros['vendedor_id'] = 'Escolha um vendedor ativo.';
        }
        if ($erros !== []) {
            throw HttpErro::validacao($erros);
        }

        return [
            'clientes_id' => (int) $entrada['cliente_id'],
            'usuarios_id' => (int) $entrada['vendedor_id'],
            'status' => (string) ($entrada['status'] ?? 'Orçamento'),
            'dataVenda' => $entrada['data_venda'],
            'garantia' => $entrada['garantia_dias'] ?? null,
            'observacoes' => osTextoParaGravar($entrada['observacoes'] ?? ''),
            'observacoes_cliente' => osTextoParaGravar($entrada['observacoes_cliente'] ?? ''),
        ];
    }
}
