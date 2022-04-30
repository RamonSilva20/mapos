<?php

namespace Libraries\Gateways\Contracts;

interface PaymentGateway
{
    public const PAYMENT_TYPE_OS = 'os';

    public const PAYMENT_TYPE_VENDAS = 'venda';

    public const PAYMENT_METHOD_BILLET = 'boleto';

    public const PAYMENT_METHOD_LINK = 'link';

    public function gerarCobranca($id, $tipo, $metodoPagamento, $data = []);

    public function findEntity($id, $tipo);

    public function enviarPorEmail($id);

    public function atualizarDados($id);

    public function cancelar($id);

    public function confirmarPagamento($id);

    public function errosCadastro($entity);
}
