<?php

namespace Libraries\Gateways;

use Libraries\Gateways\Contracts\PaymentGateway;

abstract class BasePaymentGateway implements PaymentGateway
{
    public function gerarCobranca($id, $tipo, $metodoPagamento, $data = [])
    {
        switch ($metodoPagamento) {
            case PaymentGateway::PAYMENT_METHOD_BILLET:
                return $this->gerarCobrancaBoleto($id, $tipo);
                break;
            case PaymentGateway::PAYMENT_METHOD_LINK:
                return $this->gerarCobrancaLink($id, $tipo);
                break;
            default:
                throw new \Exception('Método de pagamento inválido!');
        }
    }

    public function findEntity($id, $tipo)
    {
        switch ($tipo) {
            case PaymentGateway::PAYMENT_TYPE_OS:
                return $this->ci->Os_model->getById($id);
                break;
            case PaymentGateway::PAYMENT_TYPE_VENDAS:
                return $this->ci->vendas_model->getById($id);
                break;
            default:
                throw new \Exception('Tipo de entidade a ser gerado a cobrança inválido!');
                break;
        }
    }

    public function enviarPorEmail($id)
    {
        throw new \Exception('Não implementado');
    }

    public function atualizarDados($id)
    {
        throw new \Exception('Não implementado');
    }

    public function cancelar($id)
    {
        throw new \Exception('Não implementado');
    }

    public function confirmarPagamento($id)
    {
        throw new \Exception('Não implementado');
    }

    abstract protected function gerarCobrancaBoleto($id, $tipo);

    abstract protected function gerarCobrancaLink($id, $tipo);
}
