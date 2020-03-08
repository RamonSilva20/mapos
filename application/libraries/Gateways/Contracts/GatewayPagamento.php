<?php

namespace Libraries\Gateways\Contracts;

defined('BASEPATH') or exit('No direct script access allowed');

interface GatewayPagamento
{
    public function getPreference($access_token, $id, $title, $unit_price, $quantity);
}
