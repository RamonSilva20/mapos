<?php defined('BASEPATH') or exit('No direct script access allowed');

class Ident_Pagamento
{

    public function IdPagamento($IdNome, $access_token, $idOs, $totalProdutos, $totalServico)
    {

        if ($IdNome === 'MercadoPago' || $IdNome === 'Mercado Pago') {

            // SDK de Mercado Pago
            // Configura credenciais
            MercadoPago\SDK::setAccessToken($access_token);

            # Criar um objeto preferência
            $preference = new MercadoPago\Preference();
            # Cria itens na preferência
            $item = new MercadoPago\Item();
            $item->id = $idOs;
            $item->title = "Pagamento da OS " . $idOs;
            $item->quantity = 1;
            $item->unit_price = $totalProdutos + $totalServico;

            $preference->items = array($item);

            #exclui metodo de pagamento boleto
            $preference->payment_methods = array(
                "excluded_payment_types" => array(
                    array("id" => "ticket")
                ),
                "installments" => 12
            );

            # Salvar e postar a preferência
            $preference->save();

            return $preference;
        }
        if ($IdNome === 'PayPal' || $IdNome === 'Pay Pal') {

            $apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                  'YOUR APPLICATION CLIENT ID',
                  'YOUR APPLICATION CLIENT SECRET'
                )
              );

              print('PayPal SDK pegou :D');
        }
    }
}
