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

        # Salvar e postar a preferência
        $this->preference->save();
        return $this->preference;
    }
}
