<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Configuração da API REST (#2869)
|--------------------------------------------------------------------------
| Lida por REST_Controller. Tudo vem do .env e o padrão mantém o
| comportamento anterior: sem CORS e com um limite de requisições folgado.
*/

/*
| CORS. API_CORS_ORIGINS lista as origens que podem chamar a API de um
| navegador, separadas por vírgula (https://app.exemplo.com). `*` libera
| qualquer origem. Vazio (padrão) = sem cabeçalhos CORS, como na v4; app
| nativo (Android/iOS) não precisa de CORS, só a versão web do app.
*/
$origens = array_values(array_filter(array_map('trim', explode(',', (string) ($_ENV['API_CORS_ORIGINS'] ?? '')))));

$config['check_cors'] = $origens !== [];
$config['allow_any_cors_domain'] = in_array('*', $origens, true);
$config['allowed_cors_origins'] = array_values(array_diff($origens, ['*']));
$config['allowed_cors_headers'] = ['Authorization', 'Content-Type', 'Accept', 'X-Requested-With', 'App-Version'];
$config['allowed_cors_methods'] = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

/*
| Limite de requisições por IP, por minuto (janela fixa). 0 desliga. O
| padrão (300) deixa folga para um app que abre várias telas de uma vez e
| para vários usuários atrás do mesmo IP (NAT de empresa).
*/
$config['api_rate_limit'] = max(0, (int) ($_ENV['API_RATE_LIMIT'] ?? 300));
