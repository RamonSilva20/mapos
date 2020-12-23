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
        $id,
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
            'name' => $title . ' ' . $id, // nome do item, produto ou serviço
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
        $expiration_date = (new DateTime())->add(new DateInterval('P3D')); // quantidade de dias para vencimento 3 indica 3 dias
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
            return json_encode($pay_charge);
        } catch (GerencianetException $e) {
            $error = array("code" => $e->code, "error" => $e->error, "errorDescription" => $e->errorDescription);
            return json_encode($error);
        } catch (Exception $e) {
            $error = array("error" => "Error", "errorDescription" => $e->getMessage());
            return json_encode($error);
        }
    }

    public function gerarLink(
        $client_Id,
        $client_Secret,
        $id,
        $title,
        $unit_price,
        $quantity
    ) {

        $clientId = $client_Id; // informe seu Client_Id
        $clientSecret = $client_Secret; // informe seu Client_Secret

        $new_unit_price = preg_replace('/[^0-9]/', '', number_format($unit_price, 2, '.', ''));
        $expiration_date = (new DateTime())->add(new DateInterval('P3D')); // quantidade de dias para vencimento 3 indica 3 dias
        $expiration_date = ($expiration_date->format('Y-m-d'));

        $options = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'sandbox' => true // altere conforme o ambiente (true = desenvolvimento e false = produção)
        ];

        $item_1 = [
            'name' => $title . " " . $id,
            'amount' => (int) $quantity,
            'value' => (int) $new_unit_price
        ];

        $items = [
            $item_1
        ];

        $body = ['items' => $items];

        try {
            $api = new Gerencianet($options);
            $charge = $api->createCharge([], $body);


            if ($charge["code"] == 200) {

                $params = ['id' => $charge["data"]["charge_id"]];

                $body = [
                    //'billet_discount' => 1,
                    //'card_discount' => 1,
                    'message' => "Pagamento referente a " . $title . " " . $id, //$_POST["message"], Mensagem que ira aparecer no topo para o cliente
                    'expire_at' => $expiration_date,
                    //'request_delivery_address' => (boolean) $_POST["request"],
                    'request_delivery_address' => (bool) false, //$_POST["request"],  solicitação de endereço para entrega true or false
                    'payment_method' => "all" // $_POST["method"] aqui você escolhe o metodo de pagamento
                ];

                //$body = ['payment' => $payment];

                $api = new Gerencianet($options);
                $response = $api->linkCharge($params, $body);
                echo json_encode($response);
            } else {
            }
        } catch (GerencianetException $e) {
            $error = array("code" => $e->code, "error" => $e->error, "errorDescription" => $e->errorDescription);
            return json_encode($error);
        } catch (Exception $e) {
            $error = array("error" => "Error", "errorDescription" => $e->getMessage());
            return json_encode($error);
        }
    }

    public function gerarCarne(
        $client_Id,
        $client_Secret,
        $nameClient,
        $emailClient,
        $cpfClient,
        $phoneClient,
        $addressCep,
        $id,
        $title,
        $unit_price,
        $quantity,
        $parcelas,
        $vencimento
    ) {
        $clientId = $client_Id;
        $clientSecret = $client_Secret;

        $new_unit_price = preg_replace('/[^0-9]/', '', number_format($unit_price, 2, '.', ''));

        $phoneClient = preg_replace("/[^0-9]/", "", $phoneClient);
        $cpfClient = preg_replace("/[^0-9]/", "", $cpfClient);
        $addressCep = preg_replace("/[^0-9]/", "", $addressCep);

        $expiration_date = strtotime($vencimento); // data de vencimento do boleto
        $expiration_date = date('Y-m-d',$expiration_date);

        $options = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'sandbox' => true
        ];

        $instructions = ["Pago em qualquer loterica", "Pagar até o vencimento", "Caixa após vencimento não aceitar"]; // Pode colocar até quatro instrunções

        $item_1 = [
            'name' => $title . " " . $id,
            'amount' => (int) $quantity,
            'value' => (int) $new_unit_price
        ];

        $items = [
            $item_1
        ];

        $customer = [
            'name' => $nameClient,
            'cpf' => $cpfClient,
            'phone_number' => $phoneClient,
            'email' => $emailClient,
        ];

        $body = [
            'items' => $items,
            'repeats' => (int)$parcelas, // numero de parcelas que ira conter no boleto
            'split_items' => false,
            'expire_at' => $expiration_date,
            'customer' => $customer,
            'instructions' => $instructions
        ];

        try {
            $api = new Gerencianet($options);
            $charge = $api->createCarnet([], $body);
            return json_encode($charge);
        } catch (GerencianetException $e) {
            $error = array("code" => $e->code, "error" => $e->error, "errorDescription" => $e->errorDescription);
            return json_encode($error);
        } catch (Exception $e) {
            $error = array("error" => "Error", "errorDescription" => $e->getMessage());
            return json_encode($error);
        }
    }
}
