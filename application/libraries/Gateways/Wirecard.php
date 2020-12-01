<?php defined('BASEPATH') or exit('No direct script access allowed');

use Moip\Moip;
use Moip\Auth\BasicAuth;
use Moip\Helper\Filters;
use Moip\Resource\OrdersList;
use Moip\Helper\Pagination;

class Wirecard
{

    public function getPayment(
        $access_token,
        $public_key,
        $nameClient,
        $emailClient,
        $cpfClient,
        $phoneClient,
        $addressRua,
        $addressNumero,
        $addressBairro,
        $addressCidade,
        $addressEstado,
        $addressCep,
        $id,
        $title,
        $unit_price,
        $quantity
    ) {

        $token = $access_token;
        $key = $public_key;

        $numero = $phoneClient;
        $novoNumero = preg_replace("/[^0-9]/", "", $numero);
        $ddd_cliente = preg_replace('/\A.{2}?\K[\d]+/', '', $novoNumero);
        $numero_cliente = preg_replace('/^\d{2}/', '', $novoNumero);

        $addressCep = preg_replace("/[^0-9]/", "", $addressCep);

        $cpfClient = preg_replace("/[^0-9]/", "", $cpfClient);

        $new_unit_price = intval(preg_replace('/[^0-9]/', '', number_format($unit_price, 2, '.', '')));

        $moip = new Moip(new BasicAuth($token, $key), Moip::ENDPOINT_SANDBOX);

        try {
            /*
     * If you want to persist your customer data and save later, now is the time to create it.
     * TIP: Don't forget to generate your `ownId` or use one you already have,
     * here we set using uniqid() function.
     */
            $customer = $moip->customers()->setOwnId(uniqid())
                ->setFullname($nameClient)
                ->setEmail($emailClient)
                //->setBirthDate('1988-12-30')
                ->setTaxDocument($cpfClient)
                ->setPhone($ddd_cliente, $numero_cliente)
                ->addAddress(
                    'BILLING',
                    $addressRua,
                    $addressNumero,
                    $addressBairro,
                    $addressCidade,
                    $addressEstado,
                    $addressCep,
                    //$addressComplemento
                )
                ->addAddress(
                    'SHIPPING',
                    $addressRua,
                    $addressNumero,
                    $addressBairro,
                    $addressCidade,
                    $addressEstado,
                    $addressCep,
                    //$addressComplemento
                )
                ->create();

            // Creating an order
            $order = $moip->orders()->setOwnId(uniqid())
                ->addItem($title . ' ' . $id, $quantity, 'sku1', $new_unit_price)
                ->setShippingAmount(0)->setAddition(0)->setDiscount(0)
                ->setCustomer($customer)
                ->create();

            $logo_uri = 'https://cdn.moip.com.br/wp-content/uploads/2016/05/02163352/logo-moip.png';
            $expiration_date = (new DateTime())->add(new DateInterval('P3D'));
            $instruction_lines = ['Pagamento até o dia do vencimento.', 'Caixa não aceitar se tiver vencido.', 'Pagavel em qualquer banco e loterica.'];

            // Creating payment to order
            $payment = $order->payments()
                ->setBoleto($expiration_date, $logo_uri, $instruction_lines)
                ->execute();

            echo 'Order ID: ' . $order->getId() . '<br />';
            echo 'Payment ID: ' . $payment->getId() . '<br />';
            echo 'Created at: ' . $payment->getCreatedAt()->format('Y-m-d H:i:s') . '<br />';
            echo 'Status: ' . $payment->getStatus() . '<br />';
            echo 'Amount: ' . $payment->getAmount()->total . '<br />';
            echo 'Funding Instrument: ' . $payment->getFundingInstrument()->method . '<br />';

            echo 'Codigo de barra: ' . $payment->getLineCodeBoleto().'<br />';
            $boleto = $payment->getHrefPrintBoleto();

            echo '<a href="' . $boleto . '" target="_blank">Clique aqui para abrir boleto</a>';

        } catch (\Moip\Exceptions\UnautorizedException $e) {
            echo $e->getMessage();
        } catch (\Moip\Exceptions\ValidationException $e) {
            printf($e->__toString());
        } catch (\Moip\Exceptions\UnexpectedException $e) {
            echo $e->getMessage();
        }
    }

    function gerarBoleto($access_token, $public_key, $codePayment)
    {

        $token = $access_token;
        $key = $public_key;

        $moip = new Moip(new BasicAuth($token, $key), Moip::ENDPOINT_SANDBOX);

        $payment = $moip->payments()->get($codePayment);

        return $payment->getHrefPrintBoleto();
    }


    public function allPayment($access_token, $public_key)
    {
        $token = $access_token;
        $key = $public_key;

        $moip = new Moip(new BasicAuth($token, $key), Moip::ENDPOINT_SANDBOX);

        $orders = $moip->orders()->getList();

        // With filters
        $filters = new Filters();
        //$filters->greaterThanOrEqual(OrdersList::CREATED_AT, "2020-11-19");
        $filters->in(OrdersList::PAYMENT_METHOD, ["BOLETO"]);
        //$filters->lessThan(OrdersList::VALUE, 100000);

        $orders = $moip->orders()->getList(null, $filters);

        // With pagination
        $orders = $moip->orders()->getList(new Pagination(100, 0));

        // With specific value
        //$orders = $this->moip->orders()->getList(null, null, "josé silva");

        //echo (json_encode($orders));

        return json_encode($orders, JSON_PRETTY_PRINT);
    }
}
