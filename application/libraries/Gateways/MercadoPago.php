<?php

use Libraries\Gateways\BasePaymentGateway;
use Libraries\Gateways\Contracts\PaymentGateway;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\Exceptions\MPException;

class MercadoPago extends BasePaymentGateway
{
    private $mercadoPagoConfig;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->config('payment_gateways');
        $this->ci->load->model('Os_model');
        $this->ci->load->model('vendas_model');
        $this->ci->load->model('cobrancas_model');
        $this->ci->load->model('mapos_model');
        $this->ci->load->model('email_model');

        $this->mercadoPagoConfig = $this->ci->config->item('payment_gateways')['MercadoPago'];

        MercadoPagoConfig::setAccessToken($this->mercadoPagoConfig['credentials']['access_token']);
    }

    public function cancelar($id)
    {
        $cobranca = $this->ci->cobrancas_model->getById($id);
        if (!$cobranca) {
            throw new \Exception('Cobrança não existe!');
        }

        try {
            $client = new PaymentClient();
            $payment = $client->cancel($cobranca->charge_id);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this->atualizarDados($id);
    }

    public function enviarPorEmail($id)
    {
        $cobranca = $this->ci->cobrancas_model->getById($id);
        if (!$cobranca) {
            throw new \Exception('Cobrança não existe!');
        }

        $emitente = $this->ci->mapos_model->getEmitente();
        if (!$emitente) {
            throw new \Exception('Emitente não configurado!');
        }

        $html = $this->ci->load->view(
            'cobrancas/emails/cobranca',
            [
                'cobranca' => $cobranca,
                'emitente' => $emitente[0],
                'paymentGatewaysConfig' => $this->ci->config->item('payment_gateways'),
            ],
            true
        );

        $assunto = 'Cobrança - ' . $emitente[0]->nome;
        if ($cobranca->os_id) {
            $assunto .= ' - OS #' . $cobranca->os_id;
        } else {
            $assunto .= ' - Venda #' . $cobranca->vendas_id;
        }

        $remetentes = [$cobranca->email];
        foreach ($remetentes as $remetente) {
            $headers = [
                'From' => $emitente[0]->email,
                'Subject' => $assunto,
                'Return-Path' => '',
            ];
            $email = [
                'to' => $remetente,
                'message' => $html,
                'status' => 'pending',
                'date' => date('Y-m-d H:i:s'),
                'headers' => serialize($headers),
            ];
            $this->ci->email_model->add('email_queue', $email);
        }
    }

    public function atualizarDados($id)
    {
        $cobranca = $this->ci->cobrancas_model->getById($id);
        if (!$cobranca) {
            throw new \Exception('Cobrança não existe!');
        }

        try {
            $client = new PaymentClient();
            $payment = $client->get($cobranca->charge_id);
        } catch (MPApiException | MPException $e) {
            throw new \Exception($this->mpErrorMessage($e));
        }

        $this->ci->cobrancas_model->edit(
            'cobrancas',
            ['status' => $payment->status],
            'idCobranca',
            $id
        );

        $this->ci->session->set_flashdata('success', 'Cobrança atualizada com sucesso!');
        log_info('Atualizou cobrança Mercado Pago. ID: ' . $id);
    }

    private function mpErrorMessage(\Exception $e): string
    {
        if ($e instanceof \MercadoPago\Exceptions\MPApiException) {
            $content = $e->getApiResponse()->getContent();

            if (is_object($content)) {
                $content = json_decode(json_encode($content), true);
            }

            if (is_array($content)) {
                $msg = $content['message'] ?? $content['error'] ?? $content['cause'][0]['description'] ?? null;
                if (!$msg) {
                    $msg = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                return "Erro API Mercado Pago: " . $msg;
            }

            if (is_string($content)) {
                return "Erro API Mercado Pago: " . $content;
            }

            return "Erro API Mercado Pago: resposta inesperada.";
        }

        return $e->getMessage();
    }

    public function confirmarPagamento($id)
    {
        $cobranca = $this->ci->cobrancas_model->getById($id);
        if (!$cobranca) {
            throw new \Exception('Cobrança não existe!');
        }

        try {
            $client = new PaymentClient();
            $payment = $client->get($cobranca->charge_id);

            if ($payment->status === 'approved' || $payment->payment_method_id === 'bolbradesco') {
                $this->atualizarDados($id);
                return $payment;
            }

            if ($payment->status === 'authorized') {
                $valor = isset($cobranca->total) ? (float) $cobranca->total : 0;
                if ($valor > 1000) $valor /= 100;

                $payment = $client->capture(
                    (int) $cobranca->charge_id,
                    (float) $valor
                );
            } else {
                throw new \Exception('O pagamento não está em um estado que permite captura (' . $payment->status . ').');
            }

            $this->atualizarDados($id);
            return $payment;
        } catch (MPApiException $e) {
            throw new \Exception($this->mpErrorMessage($e));
        } catch (MPException $e) {
            throw new \Exception('Erro Mercado Pago: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Erro ao confirmar pagamento: ' . $e->getMessage());
        }
    }


    protected function gerarCobrancaBoleto($id, $tipo)
    {
        $entity = $this->findEntity($id, $tipo);
        $produtos = $tipo === PaymentGateway::PAYMENT_TYPE_OS
            ? $this->ci->Os_model->getProdutos($id)
            : $this->ci->vendas_model->getProdutos($id);
        $servicos = $tipo === PaymentGateway::PAYMENT_TYPE_OS
            ? $this->ci->Os_model->getServicos($id)
            : [];
        $desconto = [$tipo === PaymentGateway::PAYMENT_TYPE_OS
            ? $this->ci->Os_model->getById($id)
            : $this->ci->vendas_model->getById($id)];
        $tipo_desconto = [$tipo === PaymentGateway::PAYMENT_TYPE_OS
            ? $this->ci->Os_model->getById($id)
            : $this->ci->vendas_model->getById($id)];

        $totalProdutos = array_reduce($produtos, fn ($t, $i) => $t + ($i->preco * $i->quantidade), 0);
        $totalServicos = array_reduce($servicos, fn ($t, $i) => $t + ($i->preco * $i->quantidade), 0);
        $tipoDesconto = array_reduce($tipo_desconto, fn ($t, $i) => $i->tipo_desconto, 0);
        $totalDesconto = array_reduce($desconto, fn ($t, $i) => $i->desconto, 0);

        if (empty($entity)) throw new \Exception('OS ou venda não existe!');
        if (($totalProdutos + $totalServicos) <= 0) throw new \Exception('Valor inválido!');

        if ($err = $this->errosCadastro($entity)) throw new \Exception($err);

        $clientNameParts = explode(' ', $entity->nomeCliente);
        $documento = preg_replace('/[^0-9]/', '', $entity->documento);
        $expirationDate = (new DateTime())->add(new DateInterval($this->mercadoPagoConfig['boleto_expiration']))->format(DateTime::RFC3339_EXTENDED);

        try {
            $client = new PaymentClient();
            $payment = $client->create([
                "transaction_amount" => (float)$this->valorTotal($totalProdutos, $totalServicos, $totalDesconto, $tipoDesconto),
                "description" => PaymentGateway::PAYMENT_TYPE_OS ? "OS #$id" : "Venda #$id",
                "payment_method_id" => "bolbradesco",
                "notification_url" => "https://seusite.com.br/mercadopago/retorno",
                "date_of_expiration" => $expirationDate,
                "payer" => [
                    "email" => $entity->email,
                    "first_name" => $clientNameParts[0],
                    "last_name" => end($clientNameParts),
                    "identification" => [
                        "type" => strlen($documento) == 11 ? "CPF" : "CNPJ",
                        "number" => $documento,
                    ],
                    "address" => [
                        "zip_code" => preg_replace('/[^0-9]/', '', $entity->cep),
                        "street_name" => $entity->rua,
                        "street_number" => $entity->numero,
                        "neighborhood" => $entity->bairro,
                        "city" => $entity->cidade,
                        "federal_unit" => $entity->estado,
                    ],
                ],
            ]);
        } catch (MPApiException $e) {
            throw new \Exception($e->getApiResponse()->getContent());
        } catch (MPException $e) {
            throw new \Exception($e->getMessage());
        }

        $data = [
            'barcode' => $payment->barcode->content ?? '',
            'link' => $payment->transaction_details->external_resource_url ?? '',
            'pdf' => $payment->transaction_details->external_resource_url ?? '',
            'expire_at' => $payment->date_of_expiration ?? $expirationDate,
            'charge_id' => $payment->id,
            'status' => $payment->status,
            'total' => getMoneyAsCents($this->valorTotal($totalProdutos, $totalServicos, $totalDesconto, $tipoDesconto)),
            'clientes_id' => $entity->idClientes,
            'payment_method' => 'boleto',
            'payment_gateway' => 'MercadoPago',
        ];

        if ($tipo === PaymentGateway::PAYMENT_TYPE_OS) {
            $data['os_id'] = $id;
        } else {
            $data['vendas_id'] = $id;
        }

        if ($id = $this->ci->cobrancas_model->add('cobrancas', $data, true)) {
            $data['idCobranca'] = $id;
            log_info('Cobrança criada com sucesso. ID: ' . $payment->id);
        } else {
            throw new \Exception('Erro ao salvar cobrança!');
        }

        return $data;
    }

    protected function gerarCobrancaLink($id, $tipo)
    {
        throw new \Exception('Mercado Pago não suporta gerar link pela API, apenas pelo painel!');
    }

    private function valorTotal($produtosValor, $servicosValor, $desconto, $tipo_desconto)
    {
        if ($tipo_desconto == 'porcento') {
            $def_desconto = $desconto * ($produtosValor + $servicosValor) / 100;
        } elseif ($tipo_desconto == 'real') {
            $def_desconto = $desconto;
        } else {
            $def_desconto = 0;
        }

        return ($produtosValor + $servicosValor) - $def_desconto;
    }
}
