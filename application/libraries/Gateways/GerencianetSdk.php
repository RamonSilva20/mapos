<?php defined('BASEPATH') or exit('No direct script access allowed');

use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;

class GerencianetSdk
{
    public function gerarBoleto(
        $client_Id,
        $client_Secret,
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
        $idOs,
        $title,
        $unit_price,
        $quantity
    ) {

        $phoneClient = preg_replace("/[^0-9]/", "", $phoneClient);
        $addressCep = preg_replace("/[^0-9]/", "", $addressCep);

        $cpfClient = preg_replace("/[^0-9]/", "", $cpfClient);

        $new_unit_price = intval(preg_replace('/[^0-9]/', '', number_format($unit_price, 2, '.', '')));


        $clientId = $client_Id; // insira seu Client_Id, conforme o ambiente (Des ou Prod)
        $clientSecret = $client_Secret; // insira seu Client_Secret, conforme o ambiente (Des ou Prod)

        $options = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'sandbox' => true // altere conforme o ambiente (true = desenvolvimento e false = producao)
        ];

        $item_1 = [
            'name' => 'OS: ' . $idOs, // nome do item, produto ou serviço
            'amount' => $quantity, // quantidade
            'value' => $new_unit_price // valor (1000 = R$ 10,00) (Obs: É possível a criação de itens com valores negativos. Porém, o valor total da fatura deve ser superior ao valor mínimo para geração de transações.)
        ];
        $items = [
            $item_1
        ];
        $metadata = array('notification_url' => 'http://mapos.com.br/'); //Url de notificações
        $address = [
            "street" => $addressRua,
            "number" => $addressNumero,
            "neighborhood" => $addressBairro,
            "zipcode" => $addressCep,
            "city" => $addressCidade,
            "complement" => '',
            "state" => $addressEstado,
        ];
        $customer = [
            'name' => $nameClient, // nome do cliente
            'cpf' => $cpfClient, // cpf válido do cliente
            'phone_number' => $phoneClient, // telefone do cliente
            'email' => $emailClient,
            'address' => $address,

        ];

        /*$discount = [ // configuração de descontos
            'type' => 'currency', // tipo de desconto a ser aplicado
            'value' => 599 // valor de desconto 
        ];
        $configurations = [ // configurações de juros e mora
            'fine' => 200, // porcentagem de multa
            'interest' => 33 // porcentagem de juros
        ];
        $conditional_discount = [ // configurações de desconto condicional
            'type' => 'percentage', // seleção do tipo de desconto 
            'value' => 500, // porcentagem de desconto
            'until_date' => '2019-08-30' // data máxima para aplicação do desconto
        ];*/
        $expiration_date = (new DateTime())->add(new DateInterval('P3D'));
        $expiration_date = ($expiration_date->format('Y-m-d'));
        $bankingBillet = [
            'expire_at' => $expiration_date, // data de vencimento do titulo
            'message' => 'Pago em qualquer loterica\nPagar até o vencimento\nCaixa após vencimento não aceitar', // mensagem a ser exibida no boleto
            'customer' => $customer,
            /* 'discount' => $discount,
            'conditional_discount' => $conditional_discount*/
        ];
        $payment = [
            'banking_billet' => $bankingBillet // forma de pagamento (banking_billet = boleto)
        ];
        $body = [
            'items' => $items,
            'metadata' => $metadata,
            'payment' => $payment
        ];
        try {
            $api = new Gerencianet($options);
            $pay_charge = $api->oneStep([], $body);
            $date = new DateTime($pay_charge["data"]["expire_at"]);

            echo 'Nº da Transação: ' . $pay_charge["data"]["charge_id"] . '<br />';
            echo 'Vencimento: ' . $date->format('d/m/Y') . '<br />';
            echo 'Status: ' . $pay_charge["data"]["status"] . '<br />';
            echo 'Valor: R$ ' . number_format(floatval($pay_charge["data"]["total"]) / 100, 2, ',', '.') . '<br />';
            echo 'Codigo de Barra: ' . $pay_charge["data"]["barcode"] . '<br />';
            echo '<a href="' . $pay_charge["data"]['pdf']["charge"] . '" target="_blank">Clique aqui para abrir boleto</a>';
        } catch (GerencianetException $e) {
            print_r($e->code);
            print_r($e->error);
            print_r($e->errorDescription);
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }
}
