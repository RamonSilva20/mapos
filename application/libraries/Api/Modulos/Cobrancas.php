<?php

namespace Libraries\Api\Modulos;

use Exception;
use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Valor;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;

final class Cobrancas extends Modulo
{
    public static function rotas(): array
    {
        $tag = 'Cobranças';
        $cobranca = self::schema();

        return [
            Rota::get('/cobrancas', [self::class, 'listar'])->permissao('vCobranca')->tag($tag)
                ->resumo('Lista as cobranças')
                ->consulta(Comuns::paginacaoConsulta([
                    'busca' => Schema::texto()->max(100)->descricao('Nome do cliente, número da cobrança ou id no gateway.'),
                    'status' => Schema::texto()->max(40)->descricao('Status do gateway, como `paid` ou `waiting`.'),
                    'origem' => Schema::enum(['os', 'venda']),
                ]))
                ->lista($cobranca),

            Rota::post('/cobrancas', [self::class, 'criar'])->permissao('aCobranca')->tag($tag)
                ->resumo('Gera uma cobrança no gateway', 'Para uma OS ou venda que ainda não tem cobrança. As opções de gateway e forma vêm de `GET /os/opcoes`.')
                ->corpo(Schema::objeto([
                    'origem' => Schema::enum(['os', 'venda'])->obrigatorio(),
                    'origem_id' => Schema::inteiro()->obrigatorio()->min(1),
                    'gateway' => Schema::texto()->obrigatorio()->max(60)->exemplo('Asaas'),
                    'forma_pagamento' => Schema::texto()->obrigatorio()->max(30)->exemplo('boleto'),
                ]))
                ->criado($cobranca)
                ->erros(404, 409, 502),

            Rota::get('/cobrancas/{id}', [self::class, 'ver'])->permissao('vCobranca')->tag($tag)
                ->resumo('Mostra uma cobrança')
                ->dados($cobranca)
                ->erros(404),

            Rota::post('/cobrancas/{id}/atualizar', [self::class, 'atualizarStatus'])->permissao('eCobranca')->tag($tag)
                ->resumo('Consulta o status no gateway')
                ->dados($cobranca)
                ->erros(404, 502),

            Rota::post('/cobrancas/{id}/confirmar-pagamento', [self::class, 'confirmarPagamento'])->permissao('eCobranca')->tag($tag)
                ->resumo('Confirma o pagamento')
                ->dados($cobranca)
                ->erros(404, 502),

            Rota::post('/cobrancas/{id}/cancelar', [self::class, 'cancelar'])->permissao('eCobranca')->tag($tag)
                ->resumo('Cancela a cobrança no gateway')
                ->dados($cobranca)
                ->erros(404, 502),

            Rota::post('/cobrancas/{id}/email', [self::class, 'enviarEmail'])->permissao('eCobranca')->tag($tag)
                ->resumo('Envia a cobrança por e-mail ao cliente')
                ->resposta(202, Comuns::mensagem(), 'E-mail enfileirado.')
                ->erros(404, 502),

            Rota::delete('/cobrancas/{id}', [self::class, 'excluir'])->permissao('dCobranca')->tag($tag)
                ->resumo('Cancela no gateway e exclui a cobrança')
                ->semConteudo()
                ->erros(404, 502),
        ];
    }

    public static function schema(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'origem' => Schema::objeto([
                'tipo' => Schema::enum(['os', 'venda'])->obrigatorio(),
                'id' => Schema::inteiro()->obrigatorio(),
            ])->anulavel()->obrigatorio(),
            'cliente' => Comuns::pessoa()->anulavel()->obrigatorio(),
            'gateway' => Schema::texto()->anulavel()->obrigatorio(),
            'forma_pagamento' => Schema::texto()->anulavel()->obrigatorio(),
            'status' => Schema::texto()->obrigatorio()->descricao('Status bruto do gateway.'),
            'situacao' => Schema::texto()->obrigatorio()->descricao('Status agrupado: Paga, Gerada, Aguardando, Vencida, Cancelada, Estornada ou Em disputa.')->exemplo('Aguardando'),
            'status_descricao' => Schema::texto()->obrigatorio(),
            'valor' => Schema::decimal()->obrigatorio(),
            'vencimento' => Schema::data()->anulavel()->obrigatorio(),
            'id_no_gateway' => Schema::texto()->anulavel()->obrigatorio(),
            'codigo_de_barras' => Schema::texto()->anulavel()->obrigatorio(),
            'link_pagamento' => Schema::url()->anulavel()->obrigatorio(),
            'link_boleto' => Schema::url()->anulavel()->obrigatorio(),
            'pdf' => Schema::url()->anulavel()->obrigatorio(),
            'mensagem' => Schema::texto()->anulavel()->obrigatorio(),
            'criada_em' => Schema::dataHora()->anulavel()->obrigatorio(),
        ])->nome('Cobranca');
    }

    public function de(object $c): array
    {
        $origem = $c->os_id ? ['tipo' => 'os', 'id' => (int) $c->os_id] : ($c->vendas_id ? ['tipo' => 'venda', 'id' => (int) $c->vendas_id] : null);

        return [
            'id' => (int) $c->idCobranca,
            'origem' => $origem,
            'cliente' => $c->clientes_id ? ['id' => (int) $c->clientes_id, 'nome' => (string) ($c->nomeCliente ?? '')] : null,
            'gateway' => Valor::textoOuNulo($c->payment_gateway),
            'forma_pagamento' => Valor::textoOuNulo($c->payment_method),
            'status' => (string) $c->status,
            'situacao' => cobrancaStatusPill($c->status)['label'],
            'status_descricao' => cobrancaDescricaoDoStatus($this->gateways(), $c->payment_gateway, $c->status),
            'valor' => Valor::decimal((float) $c->total / 100),
            'vencimento' => Valor::data($c->expire_at),
            'id_no_gateway' => Valor::textoOuNulo($c->charge_id),
            'codigo_de_barras' => Valor::textoOuNulo($c->barcode),
            'link_pagamento' => cobrancaUrlSegura($c->payment_url),
            'link_boleto' => cobrancaUrlSegura($c->link),
            'pdf' => cobrancaUrlSegura($c->pdf),
            'mensagem' => Valor::textoOuNulo($c->message),
            'criada_em' => Valor::dataHora($c->created_at),
        ];
    }

    public function listar(): Resposta
    {
        $consulta = $this->ctx->consulta();

        return $this->listarPeloModelo($this->modelo(), [
            'pesquisa' => $consulta['busca'] ?? '',
            'status' => $consulta['status'] ?? '',
            'tipo' => $consulta['origem'] ?? '',
        ], [$this, 'de']);
    }

    public function ver(): Resposta
    {
        return Resposta::dados($this->de($this->cobranca()));
    }

    public function criar(): Resposta
    {
        $dados = $this->ctx->corpo();
        $os = $dados['origem'] === 'os';
        $tabela = $os ? 'os' : 'vendas';
        if (! $this->db->where($os ? 'idOs' : 'idVendas', $dados['origem_id'])->count_all_results($tabela)) {
            throw HttpErro::naoEncontrado($os ? 'OS não encontrada.' : 'Venda não encontrada.');
        }
        if ($this->db->where($os ? 'os_id' : 'vendas_id', $dados['origem_id'])->count_all_results('cobrancas')) {
            throw HttpErro::conflito('Já existe cobrança para esta ' . ($os ? 'OS' : 'venda') . '.', 'cobranca_existente');
        }
        if (! cobrancaGatewayValido($dados['gateway'], $this->gateways())) {
            throw HttpErro::validacao(['gateway' => 'Gateway de pagamento inválido.']);
        }

        $this->ctx->ci->load->library('Gateways/' . $dados['gateway'], null, 'PaymentGateway');
        $antes = (int) $this->db->select_max('idCobranca')->get('cobrancas')->row('idCobranca');
        $this->noGateway(fn () => $this->ctx->ci->PaymentGateway->gerarCobranca($dados['origem_id'], $dados['origem'], $dados['forma_pagamento']));

        $nova = $this->db->select('idCobranca')->where($os ? 'os_id' : 'vendas_id', $dados['origem_id'])->where('idCobranca >', $antes)->order_by('idCobranca', 'DESC')->get('cobrancas', 1)->row();
        if (! $nova) {
            throw new HttpErro(502, 'gateway', 'Falha no gateway', 'O gateway não confirmou a criação da cobrança.');
        }
        $this->ctx->auditar('Adicionou uma cobrança. ID ' . (int) $nova->idCobranca);

        return Resposta::criado($this->de($this->cobranca((int) $nova->idCobranca)), $this->ctx->url('cobrancas/' . (int) $nova->idCobranca));
    }

    public function atualizarStatus(): Resposta
    {
        return $this->agir('atualizarStatus');
    }

    public function confirmarPagamento(): Resposta
    {
        return $this->agir('confirmarPagamento');
    }

    public function cancelar(): Resposta
    {
        return $this->agir('cancelarPagamento');
    }

    public function enviarEmail(): Resposta
    {
        $id = (int) $this->cobranca()->idCobranca;
        $this->noGateway(fn () => $this->modelo()->enviarEmail($id));

        return Resposta::json(['data' => ['mensagem' => 'E-mail adicionado na fila.']], 202);
    }

    public function excluir(): Resposta
    {
        $id = (int) $this->cobranca()->idCobranca;
        $this->noGateway(fn () => $this->modelo()->cancelarPagamento($id));
        $this->removerLinha('cobrancas', 'idCobranca', $id);
        $this->ctx->auditar('Removeu uma cobrança. ID' . $id);

        return Resposta::semConteudo();
    }

    private function agir(string $metodo): Resposta
    {
        $id = (int) $this->cobranca()->idCobranca;
        $this->noGateway(fn () => $this->modelo()->{$metodo}($id));

        return Resposta::dados($this->de($this->cobranca($id)));
    }

    private function noGateway(\Closure $operacao): mixed
    {
        try {
            return $operacao();
        } catch (Exception $e) {
            $mensagem = $e->getMessage();
            if (in_array($mensagem, ['unauthorized: Must provide your access_token to proceed', 'Unauthorized'], true)) {
                $mensagem = 'Configure as credenciais do gateway no .env (PAYMENT_GATEWAYS_*).';
            }

            throw new HttpErro(502, 'gateway', 'Falha no gateway', $mensagem !== '' ? $mensagem : 'O gateway de pagamento não respondeu como esperado.');
        }
    }

    private function modelo(): object
    {
        return $this->ctx->modelo('Cobrancas_model');
    }

    private function gateways(): array
    {
        $this->ctx->ci->load->config('payment_gateways');

        return (array) $this->ctx->ci->config->item('payment_gateways');
    }

    private function cobranca(?int $id = null): object
    {
        $linha = $this->db->select('cobrancas.*, clientes.nomeCliente')->from('cobrancas')->join('clientes', 'clientes.idClientes = cobrancas.clientes_id', 'left')
            ->where('cobrancas.idCobranca', $id ?? $this->ctx->id())->limit(1)->get()->row();
        if (! $linha) {
            throw HttpErro::naoEncontrado('Cobrança não encontrada.');
        }

        return $linha;
    }
}
