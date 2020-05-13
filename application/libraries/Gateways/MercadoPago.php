<?php defined('BASEPATH') or exit('No direct script access allowed');

use Libraries\Gateways\Contracts\GatewayPagamento;

class MercadoPago implements GatewayPagamento
{
    public function getPreference($access_token, $idOs, $title = 'Pagamento da OS', $unit_price, $quantity = 1)
    {

        // SDK de Mercado Pago
        // Configura credenciais
        MercadoPago\SDK::setAccessToken($access_token);

        # Criar um objeto preferência
        $this->preference = new MercadoPago\Preference();

        $item = new MercadoPago\Item();
        $item->id = $idOs;
        $item->title = $title . $idOs;
        $item->quantity = $quantity;
        $item->unit_price = $unit_price;
        $this->preference->items = [$item];

       #Deixar apenas cartão de credito e excluir as demais forma de pagamentos, delete /* */ linha 26  e 31
        /*$this->preference->payment_methods = [
            "excluded_payment_types" => [
                ["id" => "ticket"],
            ],
            "installments" => 12,
        ];*/

        # Salvar e postar a preferência
        $this->preference->save();
        return $this->preference;
    }
}
