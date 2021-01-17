<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$config['payment_gateways'] = [
    'GerencianetSdk' => [
        'name' => 'GerenciaNet',
        'library_name' => 'GerencianetSdk',
        'production' => false,
        'credentials' => [
            'client_id' => 'Client_Id_8f9bbd52f9628ff88b863ebc82ebf8f0fa83ace7',
            'client_secret' => 'Client_Secret_a6de90f01e0b3fbf19fe16ce4877bb3f22c6491f'
        ],
        'timeout' => 30,
        'payment_methods' => [
            [
                'name' => 'Boleto',
                'value' => 'boleto',
            ],
            [
                'name' => 'Link',
                'value' => 'link',
            ]
        ],
        'transaction_status' => [
            'new' => 'Cobrança / Assinatura gerada',
            'waiting' => 'Aguardando a confirmação do pagamento',
            'paid' => 'Pagamento confirmado',
            'unpaid' => 'Não foi possível confirmar o pagamento da cobrança',
            'refunded' => 'Pagamento devolvido pelo lojista ou pelo intermediador Gerencianet',
            'contested' => 'Pagamento em processo de contestação',
            'canceled' => 'Cobrança/Assinatura cancelada pelo vendedor ou pelo pagador ',
            'settled' => 'Cobrança/Pagamento foi confirmada manualmente ',
            'link' => 'Link de pagamento',
            'expired' => 'Link/Assinatura de pagamento expirado',
            'active' => 'Assinatura ativa Todas as cobranças estão sendo geradas',
            'finished' => 'Carnê está finalizado',
            'up_to_date' => 'Carnê encontra-se em dia',
        ]
    ],
    'MercadoPago' => [
        'name' => 'MercadoPago',
        'library_name' => 'MercadoPago',
        'production' => false,
        'credentials' => [],
        'timeout' => 30,
        'payment_methods' => [
            [
                'name' => 'Boleto',
                'value' => 'boleto',
            ],
        ],
        'transaction_status' => []
    ]
];
