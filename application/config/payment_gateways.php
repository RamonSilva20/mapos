<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$config['payment_gateways'] = [
    'GerencianetSdk' => [
        'name' => 'GerenciaNet (Efí)',
        'library_name' => 'GerencianetSdk',
        'production' => false,
        'credentials' => [
            'client_id' => '',
            'client_secret' => ''
        ],
        'timeout' => 30,
        'boleto_expiration' => 'P3D',
        'payment_methods' => [
            [
                'name' => 'Boleto',
                'value' => 'boleto',
            ],
            [
                'name' => 'Link',
                'value' => 'link'
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
        'credentials' => [
            'access_token' => '',
            'public_key' => '',
            'client_secret' => '',
            'client_id' => '',
            'integrator_id' => '',
            'platform_id' => '',
            'corporation_id' => ''
        ],
        'boleto_expiration' => 'P3D',
        'payment_methods' => [
            [
                'name' => 'Boleto',
                'value' => 'boleto',
            ]
        ],
        'transaction_status' => [
            'pending' => 'O usuário ainda não concluiu o processo de pagamento',
            'approved' => 'O pagamento foi aprovado e credenciado',
            'authorized' => 'O pagamento foi autorizado, mas ainda não foi capturado',
            'in_process' => 'O pagamento está sendo revisado',
            'in_mediation' => 'Os usuários iniciaram uma disputa',
            'rejected' => 'O pagamento foi rejeitado, o usuário pode tentar o pagamento novamente',
            'cancelled' => 'O pagamento foi cancelado por uma das partes ou porque o prazo para pagamento expirou',
            'refunded' => 'O pagamento foi reembolsado ao usuário',
            'charged_back' => 'Foi feito um estorno no cartão de crédito do comprador'
        ]
    ],
    'Asaas' => [
        'name' => 'Asaas',
        'library_name' => 'Asaas',
        'production' => false,
        'notify' => false,
        'credentials' => [
            'api_key' => '',
        ],
        'boleto_expiration' => 'P3D',
        'payment_methods' => [
            [
                'name' => 'Boleto',
                'value' => 'boleto',
            ],
            [
                'name' => 'Link',
                'value' => 'link'
            ]
        ],
        'transaction_status' => [
            'PENDING' => 'Aguardando pagamento',
            'RECEIVED' => 'Recebida (saldo já creditado na conta)',
            'CONFIRMED' => 'Pagamento confirmado (saldo ainda não creditado)',
            'OVERDUE' => 'Vencida',
            'REFUNDED' => 'Estornada',
            'RECEIVED_IN_CASH' => 'Recebida em dinheiro (não gera saldo na conta)',
            'REFUND_REQUESTED' => 'Estorno Solicitado',
            'CHARGEBACK_REQUESTED' => 'Recebido chargeback',
            'CHARGEBACK_DISPUTE' => 'Em disputa de chargeback (caso sejam apresentados documentos para contestação)',
            'AWAITING_CHARGEBACK_REVERSAL' => 'Disputa vencida, aguardando repasse da adquirente',
            'DUNNING_REQUESTED' => 'Em processo de recuperação',
            'DUNNING_RECEIVED' => 'Recuperada',
            'AWAITING_RISK_ANALYSIS' => 'Pagamento em análise',
        ]
    ]
];
