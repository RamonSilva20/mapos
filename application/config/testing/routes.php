<?php

/*
|--------------------------------------------------------------------------
| Rotas do ambiente de testes
|--------------------------------------------------------------------------
|
| Este arquivo é carregado DEPOIS de application/config/routes.php
| (ver Router::_set_default_controller), então os valores aqui têm
| precedência sobre os de produção.
|
| O default_controller e o 404_override apontam para um controller inerte de
| propósito: o bootstrap da suíte precisa que a requisição de boot não toque em
| dados. O 404_override também absorve qualquer rota inesperada, o que impede
| que um exit() do show_404() mate o processo de teste.
|
*/

$route['default_controller'] = 'phpunit/index';
$route['404_override'] = 'phpunit/index';
$route['translate_uri_dashes'] = false;
