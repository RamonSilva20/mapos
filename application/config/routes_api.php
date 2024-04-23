<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// Rotas API V1
$route['api/v1'] = 'api/v1/ApiController/index';
$route['api/v1/audit'] = 'api/v1/ApiController/audit';
$route['api/v1/emitente'] = 'api/v1/ApiController/emitente';
$route['api/v1/calendario'] = 'api/v1/ApiController/calendario';
$route['api/v1/login'] = 'api/v1/UsuariosController/login';
$route['api/v1/reGenToken'] = 'api/v1/UsuariosController/reGenToken';
$route['api/v1/conta'] = 'api/v1/UsuariosController/conta';
$route['api/v1/clientes'] = 'api/v1/ClientesController/index';
$route['api/v1/clientes/(:num)'] = 'api/v1/ClientesController/index/$1';
$route['api/v1/produtos'] = 'api/v1/ProdutosController/index';
$route['api/v1/produtos/(:num)'] = 'api/v1/ProdutosController/index/$1';
$route['api/v1/servicos'] = 'api/v1/ServicosController/index';
$route['api/v1/servicos/(:num)'] = 'api/v1/ServicosController/index/$1';
$route['api/v1/usuarios'] = 'api/v1/UsuariosController/index';
$route['api/v1/usuarios/(:num)'] = 'api/v1/UsuariosController/index/$1';
$route['api/v1/os'] = 'api/v1/OsController/index';
$route['api/v1/os/(:num)'] = 'api/v1/OsController/index/$1';
$route['api/v1/os/(:num)/produtos'] = 'api/v1/OsController/produtos/$1';
$route['api/v1/os/(:num)/produtos/(:num)'] = 'api/v1/OsController/produtos/$1/$2';
$route['api/v1/os/(:num)/servicos'] = 'api/v1/OsController/servicos/$1';
$route['api/v1/os/(:num)/servicos/(:num)'] = 'api/v1/OsController/servicos/$1/$2';
$route['api/v1/os/(:num)/anotacoes'] = 'api/v1/OsController/anotacoes/$1';
$route['api/v1/os/(:num)/anotacoes/(:num)'] = 'api/v1/OsController/anotacoes/$1/$2';
$route['api/v1/os/(:num)/anexos'] = 'api/v1/OsController/anexos/$1';
$route['api/v1/os/(:num)/anexos/(:num)'] = 'api/v1/OsController/anexos/$1/$2';
$route['api/v1/os/(:num)/desconto'] = 'api/v1/OsController/desconto/$1';
