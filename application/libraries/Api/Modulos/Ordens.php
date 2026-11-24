<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\OrdemDeServico;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;
use Libraries\Api\Servicos\Comercial;
use Libraries\Api\Upload;

final class Ordens extends Modulo
{
    public static function rotas(): array
    {
        $tag = 'Ordens de serviço';
        $os = OrdemDeServico::schema();

        return [
            Rota::get('/os', [self::class, 'listar'])->permissao('vOs')->tag($tag)
                ->resumo('Lista as ordens de serviço', 'Da mais recente para a mais antiga. `total` já considera os descontos.')
                ->consulta(Comuns::paginacaoConsulta([
                    'busca' => Schema::texto()->max(100)->descricao('Nome ou documento do cliente, descrição do produto ou número da OS.'),
                    'status' => Schema::enum([...OrdemDeServico::status(), 'andamento', 'orcamento'])->descricao('Um status, ou um grupo: `andamento` (abertas e em execução) e `orcamento` (orçamento e negociação).'),
                    'de' => Schema::data()->descricao('Data inicial a partir de.'),
                    'ate' => Schema::data()->descricao('Data final até.'),
                    'cliente_id' => Schema::inteiro()->min(1),
                    'tecnico_id' => Schema::inteiro()->min(1),
                ]))
                ->lista(OrdemDeServico::schemaResumo()),

            Rota::post('/os', [self::class, 'criar'])->permissao('aOs')->tag($tag)
                ->resumo('Abre uma OS', 'Depois de criar, adicione produtos e serviços. Conforme a configuração de notificação, um e-mail é enfileirado.')
                ->corpo(self::entrada())
                ->criado($os)
                ->erros(404),

            Rota::get('/os/opcoes', [self::class, 'opcoes'])->permissao('vOs', 'aOs', 'eOs')->tag($tag)
                ->resumo('Valores aceitos nos campos da OS', 'Status, formas de pagamento do faturamento e as formas de cobrança configuradas.')
                ->dados(Schema::objeto([
                    'status' => Schema::lista(Schema::texto())->obrigatorio(),
                    'formas_pagamento' => Schema::lista(Schema::texto())->obrigatorio(),
                    'cobrancas' => Schema::lista(Schema::objeto([
                        'gateway' => Schema::texto()->obrigatorio(),
                        'forma' => Schema::texto()->obrigatorio(),
                        'nome' => Schema::texto()->obrigatorio(),
                    ]))->obrigatorio(),
                    'controle_estoque' => Schema::booleano()->obrigatorio(),
                    'editar_faturada' => Schema::booleano()->obrigatorio(),
                ])),

            Rota::get('/os/{id}', [self::class, 'ver'])->permissao('vOs')->tag($tag)
                ->resumo('Mostra uma OS completa', 'Com itens, totais, anexos, anotações e histórico de status.')
                ->dados($os)
                ->erros(404),

            Rota::patch('/os/{id}', [self::class, 'atualizar'])->permissao('eOs')->tag($tag)
                ->resumo('Altera os dados da OS', "Envie só o que muda. Mudar para `Cancelado` devolve os produtos ao estoque; sair de `Cancelado` volta a debitá-los. Toda troca de status entra no histórico.")
                ->corpo(self::entrada()->semObrigatorios())
                ->dados($os)
                ->erros(403, 404),

            Rota::delete('/os/{id}', [self::class, 'excluir'])->permissao('dOs')->tag($tag)
                ->resumo('Exclui uma OS', 'Recusa com 409 se houver cobrança ativa ou fatura com pagamento baixado. Devolve os produtos ao estoque, apaga anexos, anotações, histórico e a fatura em aberto.')
                ->semConteudo('OS excluída.')
                ->erros(404, 409),

            Rota::post('/os/{id}/produtos', [self::class, 'adicionarProduto'])->permissao('eOs')->tag($tag)
                ->resumo('Adiciona um produto', 'Com o controle de estoque ligado, o produto sai do estoque. Mudar os itens tira o desconto geral da OS.')
                ->corpo(Comercial::schemaItemEntrada(true))
                ->criado($os, 'Produto adicionado; a OS atualizada.')
                ->erros(403, 404),

            Rota::delete('/os/{id}/produtos/{item}', [self::class, 'removerProduto'])->permissao('eOs')->tag($tag)
                ->resumo('Remove um produto', 'A quantidade volta ao estoque.')
                ->dados($os)
                ->erros(403, 404),

            Rota::put('/os/{id}/produtos/{item}/desconto', [self::class, 'descontoProduto'])->permissao('eOs')->tag($tag)
                ->resumo('Desconto de um produto')
                ->corpo(Comercial::schemaDescontoEntrada())
                ->dados($os)
                ->erros(403, 404),

            Rota::post('/os/{id}/servicos', [self::class, 'adicionarServico'])->permissao('eOs')->tag($tag)
                ->resumo('Adiciona um serviço')
                ->corpo(Comercial::schemaItemEntrada(false))
                ->criado($os, 'Serviço adicionado; a OS atualizada.')
                ->erros(403, 404),

            Rota::delete('/os/{id}/servicos/{item}', [self::class, 'removerServico'])->permissao('eOs')->tag($tag)
                ->resumo('Remove um serviço')
                ->dados($os)
                ->erros(403, 404),

            Rota::put('/os/{id}/servicos/{item}/desconto', [self::class, 'descontoServico'])->permissao('eOs')->tag($tag)
                ->resumo('Desconto de um serviço')
                ->corpo(Comercial::schemaDescontoEntrada())
                ->dados($os)
                ->erros(403, 404),

            Rota::put('/os/{id}/desconto', [self::class, 'desconto'])->permissao('eOs')->tag($tag)
                ->resumo('Desconto geral da OS', 'Calculado no servidor sobre o total dos itens. `valor` 0 tira o desconto.')
                ->corpo(Comercial::schemaDescontoEntrada())
                ->dados($os)
                ->erros(403, 404),

            Rota::post('/os/{id}/faturar', [self::class, 'faturar'])->permissao('eOs')->tag($tag)
                ->resumo('Fatura a OS', 'Cria a receita no financeiro com o total da OS, marca a OS como faturada e registra o status `Faturado`.')
                ->corpo(Comercial::schemaFaturaEntrada())
                ->dados($os)
                ->erros(403, 404),

            Rota::post('/os/{id}/anexos', [self::class, 'anexar'])->permissao('eOs')->tag($tag)
                ->resumo('Anexa um arquivo', 'Um arquivo por chamada, em multipart/form-data. Imagens ganham miniatura. Tipos e tamanho seguem a configuração de armazenamento.')
                ->corpo(Schema::objeto(['arquivo' => Schema::arquivo()->obrigatorio()]), true)
                ->criado(OrdemDeServico::schemaAnexo())
                ->erros(403, 404, 413),

            Rota::get('/os/{id}/anexos/{anexo}', [self::class, 'baixarAnexo'])->permissao('vOs')->tag($tag)
                ->resumo('Baixa um anexo')
                ->consulta(Schema::objeto([
                    'miniatura' => Schema::booleano()->padrao(false),
                    'download' => Schema::booleano()->padrao(false)->descricao('true força o download em vez de abrir no navegador.'),
                ]))
                ->arquivo()
                ->erros(404),

            Rota::delete('/os/{id}/anexos/{anexo}', [self::class, 'removerAnexo'])->permissao('eOs')->tag($tag)
                ->resumo('Exclui um anexo')
                ->semConteudo()
                ->erros(403, 404),

            Rota::post('/os/{id}/anotacoes', [self::class, 'anotar'])->permissao('eOs')->tag($tag)
                ->resumo('Adiciona uma anotação interna', 'O nome de quem anotou vai no início do texto.')
                ->corpo(Schema::objeto(['texto' => Schema::texto()->obrigatorio()->max(250)]))
                ->criado(OrdemDeServico::schemaAnotacao())
                ->erros(403, 404),

            Rota::delete('/os/{id}/anotacoes/{anotacao}', [self::class, 'removerAnotacao'])->permissao('eOs')->tag($tag)
                ->resumo('Exclui uma anotação')
                ->semConteudo()
                ->erros(403, 404),

            Rota::post('/os/{id}/email', [self::class, 'enviarEmail'])->permissao('eOs')->tag($tag)
                ->resumo('Envia a OS por e-mail', 'Enfileira o e-mail para os destinatários da configuração de notificação (cliente, técnico, emitente ou todos).')
                ->resposta(202, Comuns::mensagem(), 'E-mail enfileirado.')
                ->erros(404, 422),

            Rota::get('/os/{id}/pix', [self::class, 'pix'])->permissao('vOs')->tag($tag)
                ->resumo('PIX para pagar a OS', 'Payload copia e cola e QR code com o total da OS. 404 sem chave PIX configurada, sem emitente ou com total zero.')
                ->dados(self::schemaPix())
                ->erros(404),

            Rota::get('/os/{id}/whatsapp', [self::class, 'whatsapp'])->permissao('vOs')->tag($tag)
                ->resumo('Mensagem de WhatsApp para o cliente', 'Texto montado com o modelo das configurações. 404 se o cliente não tem telefone válido.')
                ->dados(Schema::objeto([
                    'telefone' => Schema::texto()->obrigatorio()->exemplo('5511999998888'),
                    'texto' => Schema::texto()->obrigatorio(),
                    'url' => Schema::url()->obrigatorio()->exemplo('https://wa.me/5511999998888?text=...'),
                ]))
                ->erros(404),
        ];
    }

    public static function schemaPix(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'valor' => Schema::decimal()->obrigatorio(),
            'payload' => Schema::texto()->obrigatorio()->descricao('PIX copia e cola.'),
            'qr_code' => Schema::texto()->obrigatorio()->descricao('Imagem do QR code como data URI.'),
            'chave' => Schema::texto()->obrigatorio(),
        ])->nome('Pix');
    }

    private static function entrada(): Schema
    {
        return Schema::objeto([
            'cliente_id' => Schema::inteiro()->obrigatorio()->min(1),
            'tecnico_id' => Schema::inteiro()->min(1)->descricao('Sem técnico, fica o usuário logado.'),
            'status' => Schema::enum(OrdemDeServico::status())->padrao('Aberto'),
            'data_inicial' => Schema::data()->descricao('Padrão: hoje.'),
            'data_final' => Schema::data()->obrigatorio(),
            'garantia_dias' => Schema::inteiro()->min(0)->max(9999)->anulavel(),
            'termo_garantia_id' => Schema::inteiro()->min(1)->anulavel(),
            'descricao_produto' => Schema::texto()->max(20000),
            'defeito' => Schema::texto()->max(20000),
            'observacoes' => Schema::texto()->max(20000),
            'laudo_tecnico' => Schema::texto()->max(20000),
        ])->nome('OrdemDeServicoEntrada');
    }

    public function listar(): Resposta
    {
        $consulta = $this->ctx->consulta();
        $pagina = $this->ctx->paginacao();
        $consultaBase = function () use ($consulta): object {
            $q = OrdemDeServico::consultaResumo($this->db);
            $busca = $consulta['busca'] ?? '';
            if ($busca !== '') {
                $q->group_start()->like('clientes.nomeCliente', $busca)->or_like('clientes.documento', $busca)->or_like('os.descricaoProduto', $busca);
                if (ctype_digit($busca)) {
                    $q->or_where('os.idOs', (int) $busca);
                }
                $q->group_end();
            }
            if (($consulta['status'] ?? null) !== null) {
                $grupo = painelGrupoStatus($consulta['status']);
                $grupo !== null ? $q->where_in('os.status', $grupo) : $q->where('os.status', $consulta['status']);
            }
            if (($consulta['de'] ?? null) !== null) {
                $q->where('os.dataInicial >=', $consulta['de']);
            }
            if (($consulta['ate'] ?? null) !== null) {
                $q->where('os.dataFinal <=', $consulta['ate']);
            }
            if (($consulta['cliente_id'] ?? null) !== null) {
                $q->where('os.clientes_id', $consulta['cliente_id']);
            }
            if (($consulta['tecnico_id'] ?? null) !== null) {
                $q->where('os.usuarios_id', $consulta['tecnico_id']);
            }

            return $q;
        };

        $total = (int) $consultaBase()->count_all_results();
        $linhas = $consultaBase()->order_by('os.idOs', 'DESC')->limit($pagina['por_pagina'], $pagina['deslocamento'])->get()->result();

        return Resposta::lista(array_map([OrdemDeServico::class, 'resumoDe'], $linhas), ['pagina' => $pagina['pagina'], 'por_pagina' => $pagina['por_pagina'], 'total' => $total]);
    }

    public function opcoes(): Resposta
    {
        $this->ctx->ci->load->config('payment_gateways');
        $cobrancas = [];
        foreach (osOpcoesDeCobranca($this->ctx->ci->config->item('payment_gateways')) as $valor => $nome) {
            [$gateway, $forma] = explode('|', $valor, 2);
            $cobrancas[] = ['gateway' => $gateway, 'forma' => $forma, 'nome' => $nome];
        }

        return Resposta::dados([
            'status' => OrdemDeServico::status(),
            'formas_pagamento' => OS_FORMAS_PAGAMENTO,
            'cobrancas' => $cobrancas,
            'controle_estoque' => (bool) $this->ctx->configuracao('control_estoque'),
            'editar_faturada' => $this->ctx->configuracao('control_editos') == '1',
        ]);
    }

    public function ver(): Resposta
    {
        return Resposta::dados($this->detalhe($this->os()));
    }

    public function criar(): Resposta
    {
        $entrada = $this->ctx->corpo();
        $entrada['tecnico_id'] ??= $this->ctx->titular()->id;
        $entrada['data_inicial'] ??= date('Y-m-d');
        $dados = $this->paraBanco($entrada);

        $id = $this->transacao(function () use ($dados): int {
            $id = $this->inserirLinha('os', $dados + ['faturado' => 0]);
            $this->modelo()->registrarStatus($id, null, (string) $dados['status'], $this->ctx->titular()->id);

            return $id;
        });
        $this->ctx->auditar('Adicionou uma OS. ID: ' . $id);
        $this->notificar($id, 'Ordem de Serviço - Criada');

        return Resposta::criado($this->detalhe($this->os($id)), $this->ctx->url('os/' . $id));
    }

    public function atualizar(): Resposta
    {
        $os = $this->osEditavel();
        $id = (int) $os->idOs;
        [$entrada] = $this->ctx->corpoParcial(self::paraEntrada($os));
        $dados = $this->paraBanco($entrada);

        $this->transacao(function () use ($os, $id, $dados): void {
            $antes = strtolower((string) $os->status);
            $depois = strtolower((string) $dados['status']);
            $comercial = new Comercial($this->ctx);
            if ($depois === 'cancelado' && $antes !== 'cancelado') {
                $comercial->movimentarEstoque($this->modelo()->getProdutos($id), '+', 'Motivo: Cancelamento da OS ' . $id);
            }
            if ($antes === 'cancelado' && $depois !== 'cancelado') {
                $comercial->movimentarEstoque($this->modelo()->getProdutos($id), '-', 'Motivo: OS ' . $id . ' saiu de Cancelado');
            }
            $this->atualizarLinha('os', 'idOs', $id, $dados);
            $this->modelo()->registrarStatus($id, (string) $os->status, (string) $dados['status'], $this->ctx->titular()->id);
        });
        $this->ctx->auditar('Alterou uma OS. ID: ' . $id);
        $this->notificar($id, 'Ordem de Serviço - Editada');

        return Resposta::dados($this->detalhe($this->os($id)));
    }

    public function excluir(): Resposta
    {
        $os = $this->os();
        $id = (int) $os->idOs;
        $modelo = $this->modelo();

        $cobranca = $this->db->select('idCobranca, status')->where('os_id', $id)->get('cobrancas', 1)->row();
        if ($cobranca && $cobranca->status !== 'canceled') {
            throw HttpErro::conflito('Existe uma cobrança associada a esta OS: cancele ou exclua a cobrança primeiro.', 'cobranca_ativa');
        }
        $lancamento = isset($os->lancamento) ? (int) $os->lancamento : null;
        if ((int) $os->faturado === 1 && $modelo->faturaBaixada($id, $lancamento)) {
            throw HttpErro::conflito('A fatura desta OS já tem pagamento baixado: estorne o recebimento antes de excluir a OS.', 'fatura_baixada');
        }

        $anexos = $modelo->getAnexos($id);
        $this->transacao(function () use ($os, $id, $modelo, $cobranca, $lancamento): void {
            if ($cobranca) {
                $this->removerLinha('cobrancas', 'os_id', $id);
            }
            if (strtolower((string) $os->status) !== 'cancelado') {
                (new Comercial($this->ctx))->movimentarEstoque($modelo->getProdutos($id), '+', 'Motivo: Exclusão da OS ' . $id);
            }
            foreach (['servicos_os', 'produtos_os', 'anexos', 'anotacoes_os', 'os_historico'] as $tabela) {
                $this->removerLinha($tabela, 'os_id', $id);
            }
            $this->removerLinha('os', 'idOs', $id);
            if ((int) $os->faturado === 1) {
                $modelo->excluirFatura($id, $lancamento);
            }
        });

        foreach ($anexos as $anexo) {
            foreach ([false, true] as $miniatura) {
                $arquivo = armazenamentoArquivoDoAnexo($anexo, $miniatura);
                if ($arquivo !== null) {
                    @unlink($arquivo);
                }
            }
        }
        $this->ctx->auditar('Removeu uma OS. ID: ' . $id);

        return Resposta::semConteudo();
    }

    public function adicionarProduto(): Resposta
    {
        return $this->adicionarItem(true);
    }

    public function adicionarServico(): Resposta
    {
        return $this->adicionarItem(false);
    }

    public function removerProduto(): Resposta
    {
        return $this->removerItem(true);
    }

    public function removerServico(): Resposta
    {
        return $this->removerItem(false);
    }

    public function descontoProduto(): Resposta
    {
        return $this->descontoItem(true);
    }

    public function descontoServico(): Resposta
    {
        return $this->descontoItem(false);
    }

    private function adicionarItem(bool $produto): Resposta
    {
        $os = $this->osEditavel();
        $id = (int) $os->idOs;
        $comercial = new Comercial($this->ctx);
        $dados = $comercial->item($this->ctx->corpo(), $produto);

        $this->transacao(function () use ($produto, $dados, $id, $comercial): void {
            $this->inserirLinha($produto ? 'produtos_os' : 'servicos_os', $dados + ['os_id' => $id]);
            if ($produto) {
                $comercial->movimentarEstoque([(object) $dados], '-', 'Motivo: Adicionado à OS ' . $id);
            }
            $this->modelo()->zerarDesconto($id);
        });
        $this->ctx->auditar(($produto ? 'Adicionou produto' : 'Adicionou serviço') . ' a uma OS. ID (OS): ' . $id);

        return Resposta::criado($this->detalhe($this->os($id)), $this->ctx->url('os/' . $id));
    }

    private function removerItem(bool $produto): Resposta
    {
        $os = $this->osEditavel();
        $id = (int) $os->idOs;
        $item = $this->itemDaOs($id, $produto);

        $this->transacao(function () use ($produto, $item, $id): void {
            $this->removerLinha($produto ? 'produtos_os' : 'servicos_os', $produto ? 'idProdutos_os' : 'idServicos_os', $produto ? (int) $item->idProdutos_os : (int) $item->idServicos_os);
            if ($produto) {
                (new Comercial($this->ctx))->movimentarEstoque([$item], '+', 'Motivo: Removido da OS ' . $id);
            }
            $this->modelo()->zerarDesconto($id);
        });
        $this->ctx->auditar(($produto ? 'Removeu produto' : 'Removeu serviço') . ' de uma OS. ID (OS): ' . $id);

        return Resposta::dados($this->detalhe($this->os($id)));
    }

    private function descontoItem(bool $produto): Resposta
    {
        $os = $this->osEditavel();
        $id = (int) $os->idOs;
        $item = $this->itemDaOs($id, $produto);
        if (! $produto && (float) $item->preco <= 0) {
            $cadastro = $this->modelo()->getServicoCadastro((int) $item->servicos_id);
            $item->precoVenda = $cadastro->preco ?? 0;
        }

        $desconto = (new Comercial($this->ctx))->descontoDoItem($item, $this->ctx->corpo());
        $this->transacao(function () use ($produto, $desconto, $item, $id): void {
            $this->atualizarLinha($produto ? 'produtos_os' : 'servicos_os', $produto ? 'idProdutos_os' : 'idServicos_os', $produto ? (int) $item->idProdutos_os : (int) $item->idServicos_os, $desconto);
            $this->modelo()->zerarDesconto($id);
        });
        $this->ctx->auditar('Alterou o desconto de ' . ($produto ? 'um produto' : 'um serviço') . ' de uma OS. ID (OS): ' . $id);

        return Resposta::dados($this->detalhe($this->os($id)));
    }

    public function desconto(): Resposta
    {
        $os = $this->osEditavel();
        $id = (int) $os->idOs;
        $totais = $this->modelo()->totais($os);
        $dados = (new Comercial($this->ctx))->descontoGeral((float) $totais['bruto'], $this->ctx->corpo(), 'OS', 'produtos ou serviços');

        $this->atualizarLinha('os', 'idOs', $id, $dados);
        $this->ctx->auditar(($dados['valor_desconto'] > 0 ? 'Adicionou um desconto' : 'Removeu o desconto') . ' na OS. ID: ' . $id);

        return Resposta::dados($this->detalhe($this->os($id)));
    }

    public function faturar(): Resposta
    {
        $os = $this->osEditavel();
        $id = (int) $os->idOs;
        if ((int) $os->faturado === 1 || $os->status === 'Cancelado') {
            throw HttpErro::regraDeNegocio('Esta OS já foi faturada ou está cancelada.', 'ja_faturada');
        }

        $totais = $this->modelo()->totais($os);
        [$lancamento, $erros] = osFaturaDoFormulario(Comercial::postDaFatura($this->ctx->corpo()), $os, $totais, $this->ctx->titular()->id);
        if (isset($erros['_geral'])) {
            throw HttpErro::regraDeNegocio($erros['_geral'], 'sem_itens');
        }
        if ($erros !== []) {
            throw HttpErro::validacao(Comercial::errosDaFatura($erros));
        }

        $this->transacao(function () use ($lancamento, $totais, $os, $id): void {
            $idLancamento = $this->inserirLinha('lancamentos', $lancamento);
            $this->atualizarLinha('os', 'idOs', $id, [
                'faturado' => 1,
                'valorTotal' => $totais['bruto'],
                'desconto' => $totais['desconto'],
                'valor_desconto' => $totais['total'],
                'status' => 'Faturado',
                'lancamento' => $idLancamento,
            ]);
            $this->modelo()->registrarStatus($id, (string) $os->status, 'Faturado', $this->ctx->titular()->id);
        });
        $this->ctx->auditar('Faturou uma OS. ID: ' . $id);

        return Resposta::dados($this->detalhe($this->os($id)));
    }

    public function anexar(): Resposta
    {
        $os = $this->osEditavel();
        $id = (int) $os->idOs;
        $this->ctx->helper('armazenamento');

        $relativo = 'anexos/' . date('m-Y') . '/OS-' . $id;
        $pasta = armazenamentoPasta($relativo . '/thumbs') !== null ? armazenamentoPasta($relativo) : null;
        if ($pasta === null) {
            throw HttpErro::interno('Não foi possível criar a pasta dos anexos no servidor.');
        }

        $upload = new Upload($this->ctx);
        $arquivo = $upload->receber($this->ctx->corpo()['arquivo'], $pasta, armazenamentoExtensoesAnexo());
        $miniatura = '';
        if ($arquivo['imagem'] && $upload->miniatura($arquivo['caminho'], $arquivo['pasta'] . 'thumbs' . DIRECTORY_SEPARATOR . 'thumb_' . $arquivo['nome'])) {
            $miniatura = 'thumb_' . $arquivo['nome'];
        }

        if (! $this->modelo()->anexar($id, $arquivo['nome'], '', $miniatura, $relativo)) {
            @unlink($arquivo['caminho']);

            throw HttpErro::interno('Não foi possível gravar o anexo.');
        }
        $idAnexo = (int) $this->db->insert_id();
        $this->ctx->auditar('Adicionou anexo(s) a uma OS. ID (OS): ' . $id);

        $anexo = $this->modelo()->getAnexoDaOs($id, $idAnexo);
        $url = $this->ctx->url('os/' . $id . '/anexos/' . $idAnexo);

        return Resposta::criado(OrdemDeServico::anexoDe($anexo, $url), $url);
    }

    public function baixarAnexo(): Resposta
    {
        $os = $this->os();
        $anexo = $this->modelo()->getAnexoDaOs((int) $os->idOs, $this->ctx->id('anexo'));
        $consulta = $this->ctx->consulta();
        $this->ctx->helper('armazenamento');
        $arquivo = $anexo ? armazenamentoArquivoDoAnexo($anexo, (bool) $consulta['miniatura']) : null;
        if ($arquivo === null) {
            throw HttpErro::naoEncontrado('Anexo não encontrado.');
        }

        return Upload::resposta($arquivo, 'anexo-' . (int) $anexo->idAnexos . '.' . strtolower(pathinfo($arquivo, PATHINFO_EXTENSION)), (bool) $consulta['download']);
    }

    public function removerAnexo(): Resposta
    {
        $os = $this->osEditavel();
        $id = (int) $os->idOs;
        $anexo = $this->modelo()->getAnexoDaOs($id, $this->ctx->id('anexo'));
        if (! $anexo) {
            throw HttpErro::naoEncontrado('Anexo não encontrado nesta OS.');
        }

        $this->ctx->helper('armazenamento');
        foreach ([false, true] as $miniatura) {
            $arquivo = armazenamentoArquivoDoAnexo($anexo, $miniatura);
            if ($arquivo !== null) {
                @unlink($arquivo);
            }
        }
        $this->removerLinha('anexos', 'idAnexos', (int) $anexo->idAnexos);
        $this->ctx->auditar('Removeu anexo de uma OS. ID (OS): ' . $id);

        return Resposta::semConteudo();
    }

    public function anotar(): Resposta
    {
        $os = $this->osEditavel();
        $id = (int) $os->idOs;
        $prefixo = '[' . $this->ctx->titular()->nome . '] ';
        $texto = $this->ctx->corpo()['texto'];
        $limite = 255 - mb_strlen($prefixo);
        if (mb_strlen($texto) > $limite) {
            throw HttpErro::validacao(['texto' => 'A anotação tem até ' . $limite . ' caracteres.']);
        }

        $idAnotacao = $this->inserirLinha('anotacoes_os', ['anotacao' => $prefixo . $texto, 'data_hora' => date('Y-m-d H:i:s'), 'os_id' => $id]);
        $this->ctx->auditar('Adicionou anotação a uma OS. ID (OS): ' . $id);

        return Resposta::criado(OrdemDeServico::anotacaoDe($this->modelo()->getAnotacaoDaOs($id, $idAnotacao)), $this->ctx->url('os/' . $id));
    }

    public function removerAnotacao(): Resposta
    {
        $os = $this->osEditavel();
        $id = (int) $os->idOs;
        $idAnotacao = $this->ctx->id('anotacao');
        if (! $this->modelo()->getAnotacaoDaOs($id, $idAnotacao)) {
            throw HttpErro::naoEncontrado('Anotação não encontrada nesta OS.');
        }
        $this->removerLinha('anotacoes_os', 'idAnotacoes', $idAnotacao);
        $this->ctx->auditar('Removeu anotação de uma OS. ID (OS): ' . $id);

        return Resposta::semConteudo();
    }

    public function enviarEmail(): Resposta
    {
        $os = $this->os();
        $id = (int) $os->idOs;
        $notificacao = (string) $this->ctx->configuracao('os_notification', 'cliente');
        if ($notificacao === 'nenhum') {
            throw HttpErro::regraDeNegocio('O sistema está configurado para não enviar e-mails de OS.', 'notificacao_desligada');
        }
        if (in_array($notificacao, ['todos', 'cliente'], true) && ! filter_var((string) $os->email, FILTER_VALIDATE_EMAIL)) {
            throw HttpErro::regraDeNegocio('O cliente não tem um e-mail válido cadastrado.', 'cliente_sem_email');
        }
        if (! $this->enfileirarEmail($id, $this->destinatarios($os, $notificacao), 'Ordem de Serviço')) {
            throw HttpErro::regraDeNegocio('Cadastre o e-mail do emitente antes de enviar.', 'emitente_sem_email');
        }
        $this->ctx->auditar('Enviou e-mail para o cliente: ' . $os->nomeCliente . '. E-mail: ' . $os->email);

        return Resposta::json(['data' => ['mensagem' => 'O e-mail está na fila e será enviado em breve.']], 202);
    }

    public function pix(): Resposta
    {
        $os = $this->os();
        $id = (int) $os->idOs;
        $chave = (string) $this->ctx->configuracao('pix_key', '');
        $emitente = $this->ctx->modelo('Mapos_model')->getEmitente();
        $payload = $chave === '' || ! $emitente ? null : $this->modelo()->getPixPayload($id, $chave, $emitente);
        if ($payload === null) {
            throw HttpErro::naoEncontrado('PIX indisponível: configure a chave PIX e o emitente, e confira se a OS tem valor.');
        }
        $totais = $this->modelo()->totais($os);

        return Resposta::dados([
            'valor' => number_format((float) $totais['total'], 2, '.', ''),
            'payload' => $payload,
            'qr_code' => (string) $this->modelo()->getQrCode($id, $chave, $emitente),
            'chave' => self::formatarChavePix($chave),
        ]);
    }

    public function whatsapp(): Resposta
    {
        $os = $this->os();
        $telefone = osTelefoneWhatsApp($os->celular_cliente ?: $os->telefone_cliente);
        if ($telefone === null) {
            throw HttpErro::naoEncontrado('O cliente não tem um telefone válido para WhatsApp.');
        }
        $emitente = $this->ctx->modelo('Mapos_model')->getEmitente();
        $totais = $this->modelo()->totais($os);
        $texto = osTextoWhatsApp((string) $this->ctx->configuracao('notifica_whats', ''), [
            '{CLIENTE_NOME}' => (string) $os->nomeCliente,
            '{NUMERO_OS}' => (string) $os->idOs,
            '{STATUS_OS}' => (string) $os->status,
            '{VALOR_OS}' => dinheiro($totais['total']),
            '{DESCRI_PRODUTOS}' => (string) $os->descricaoProduto,
            '{EMITENTE}' => (string) ($emitente->nome ?? ''),
            '{TELEFONE_EMITENTE}' => (string) ($emitente->telefone ?? ''),
            '{OBS_OS}' => (string) $os->observacoes,
            '{DEFEITO_OS}' => (string) $os->defeito,
            '{LAUDO_OS}' => (string) $os->laudoTecnico,
            '{DATA_FINAL}' => dataBr($os->dataFinal),
            '{DATA_INICIAL}' => dataBr($os->dataInicial),
            '{DATA_GARANTIA}' => (string) $os->garantia . ' dias',
        ]);

        return Resposta::dados(['telefone' => $telefone, 'texto' => $texto, 'url' => 'https://wa.me/' . $telefone . '?text=' . rawurlencode($texto)]);
    }

    public static function formatarChavePix(string $chave): string
    {
        $digitos = preg_replace('/\D/', '', $chave);
        if ($digitos === $chave && strlen($chave) === 11 && valid_cpf($chave)) {
            return substr($chave, 0, 3) . '.' . substr($chave, 3, 3) . '.' . substr($chave, 6, 3) . '-' . substr($chave, 9);
        }
        if ($digitos === $chave && strlen($chave) === 14 && valid_cnpj($chave)) {
            return substr($chave, 0, 2) . '.' . substr($chave, 2, 3) . '.' . substr($chave, 5, 3) . '/' . substr($chave, 8, 4) . '-' . substr($chave, 12);
        }
        if ($digitos === $chave && strlen($chave) === 11) {
            return '(' . substr($chave, 0, 2) . ') ' . substr($chave, 2, 5) . '-' . substr($chave, 7);
        }

        return $chave;
    }

    private function modelo(): object
    {
        return $this->ctx->modelo('Os_model');
    }

    private function os(?int $id = null): object
    {
        $os = $this->modelo()->getById($id ?? $this->ctx->id());
        if (! $os) {
            throw HttpErro::naoEncontrado('OS não encontrada.');
        }

        return $os;
    }

    private function editavel(object $os): bool
    {
        return osEditavel($os, true, $this->ctx->configuracao('control_editos') == '1');
    }

    private function osEditavel(): object
    {
        $os = $this->os();
        if (! $this->editavel($os)) {
            throw HttpErro::proibido('Esta OS está faturada ou cancelada e não pode mais ser alterada.', 'os_fechada');
        }

        return $os;
    }

    private function itemDaOs(int $id, bool $produto): object
    {
        $idItem = $this->ctx->id('item');
        $item = $produto ? $this->modelo()->getProdutoDaOs($id, $idItem) : $this->modelo()->getServicoDaOs($id, $idItem);
        if (! $item) {
            throw HttpErro::naoEncontrado('Item não encontrado nesta OS.');
        }

        return $item;
    }

    private function detalhe(object $os): array
    {
        $id = (int) $os->idOs;
        $modelo = $this->modelo();
        $cobranca = $this->db->select('idCobranca')->where('os_id', $id)->order_by('idCobranca', 'DESC')->get('cobrancas', 1)->row();

        return OrdemDeServico::de($os, [
            'totais' => $modelo->totais($os),
            'editavel' => $this->editavel($os),
            'produtos' => $modelo->getProdutos($id),
            'servicos' => $modelo->getServicos($id),
            'anexos' => $modelo->getAnexos($id),
            'anotacoes' => $modelo->getAnotacoes($id),
            'historico' => $modelo->getHistorico($id),
            'cobranca_id' => $cobranca ? (int) $cobranca->idCobranca : null,
        ], $this->ctx->url('os/' . $id));
    }

    /** @return array<string, mixed> */
    public static function paraEntrada(object $os): array
    {
        return [
            'cliente_id' => (int) $os->clientes_id,
            'tecnico_id' => (int) $os->usuarios_id,
            'status' => (string) $os->status,
            'data_inicial' => osDataParaCampo($os->dataInicial) ?: null,
            'data_final' => osDataParaCampo($os->dataFinal) ?: null,
            'garantia_dias' => is_numeric($os->garantia) ? (int) $os->garantia : null,
            'termo_garantia_id' => $os->garantias_id ? (int) $os->garantias_id : null,
            'descricao_produto' => osTextoParaEdicao($os->descricaoProduto),
            'defeito' => osTextoParaEdicao($os->defeito),
            'observacoes' => osTextoParaEdicao($os->observacoes),
            'laudo_tecnico' => osTextoParaEdicao($os->laudoTecnico),
        ];
    }

    private function paraBanco(array $entrada): array
    {
        $erros = [];
        if (! $this->db->where('idClientes', $entrada['cliente_id'])->count_all_results('clientes')) {
            $erros['cliente_id'] = 'Cliente não encontrado.';
        }
        if (! $this->db->where('idUsuarios', $entrada['tecnico_id'])->where('situacao', 1)->count_all_results('usuarios')) {
            $erros['tecnico_id'] = 'Escolha um técnico ativo.';
        }
        if (($entrada['termo_garantia_id'] ?? null) !== null && ! $this->db->where('idGarantias', $entrada['termo_garantia_id'])->count_all_results('garantias')) {
            $erros['termo_garantia_id'] = 'Termo de garantia não encontrado.';
        }
        if (($entrada['data_inicial'] ?? null) !== null && ($entrada['data_final'] ?? null) !== null && $entrada['data_final'] < $entrada['data_inicial']) {
            $erros['data_final'] = 'A data final não pode ser anterior à data inicial.';
        }
        if ($erros !== []) {
            throw HttpErro::validacao($erros);
        }

        return [
            'clientes_id' => (int) $entrada['cliente_id'],
            'usuarios_id' => (int) $entrada['tecnico_id'],
            'status' => (string) ($entrada['status'] ?? 'Aberto'),
            'dataInicial' => $entrada['data_inicial'],
            'dataFinal' => $entrada['data_final'],
            'garantia' => ($entrada['garantia_dias'] ?? null) === null ? '' : (string) $entrada['garantia_dias'],
            'garantias_id' => $entrada['termo_garantia_id'] ?? null,
            'descricaoProduto' => osTextoParaGravar($entrada['descricao_produto'] ?? ''),
            'defeito' => osTextoParaGravar($entrada['defeito'] ?? ''),
            'observacoes' => osTextoParaGravar($entrada['observacoes'] ?? ''),
            'laudoTecnico' => osTextoParaGravar($entrada['laudo_tecnico'] ?? ''),
        ];
    }

    /** @return list<string> */
    private function destinatarios(object $os, string $notificacao): array
    {
        $tecnico = $this->db->select('email')->where('idUsuarios', (int) $os->usuarios_id)->get('usuarios', 1)->row();
        $emitente = $this->ctx->modelo('Mapos_model')->getEmitente();

        $lista = match ($notificacao) {
            'todos' => [$os->email, $tecnico->email ?? null, $emitente->email ?? null],
            'tecnico' => [$tecnico->email ?? null],
            'emitente' => [$emitente->email ?? null],
            default => [$os->email],
        };

        return array_values(array_unique(array_filter($lista)));
    }

    private function notificar(int $id, string $assunto): void
    {
        $notificacao = (string) $this->ctx->configuracao('os_notification', 'nenhum');
        if ($notificacao === 'nenhum' || $this->ctx->configuracao('email_automatico', 0) != 1) {
            return;
        }
        $os = $this->os($id);
        $this->enfileirarEmail($id, $this->destinatarios($os, $notificacao), $assunto);
    }

    /** @param  list<string>  $destinatarios */
    private function enfileirarEmail(int $id, array $destinatarios, string $assunto): bool
    {
        $emitente = $this->ctx->modelo('Mapos_model')->getEmitente();
        if (! isset($emitente->email) || $emitente->email === '') {
            return false;
        }
        $modelo = $this->modelo();
        $html = $this->ctx->ci->load->view('os/emails/os', [
            'result' => $modelo->getById($id),
            'produtos' => $modelo->getProdutos($id),
            'servicos' => $modelo->getServicos($id),
            'emitente' => $emitente,
        ], true);

        foreach ($destinatarios as $destinatario) {
            $this->inserirLinha('email_queue', [
                'to' => $destinatario,
                'message' => $html,
                'status' => 'pending',
                'date' => date('Y-m-d H:i:s'),
                'headers' => json_encode(['From' => $emitente->email, 'Subject' => $assunto, 'Return-Path' => '']),
            ]);
        }

        return true;
    }
}
