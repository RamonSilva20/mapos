<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// Rotas API
$route['api'] = 'api/ApiController/index';
$route['api/audit'] = 'api/ApiController/audit';
$route['api/emitente'] = 'api/ApiController/emitente';
$route['api/calendario'] = 'api/ApiController/calendario';
$route['api/login'] = 'api/UsuariosController/login';
$route['api/reGenToken'] = 'api/UsuariosController/reGenToken';
$route['api/conta'] = 'api/UsuariosController/conta';
$route['api/clientes'] = 'api/ClientesController/index';
$route['api/clientes/(:num)'] = 'api/ClientesController/index/$1';
$route['api/produtos'] = 'api/ProdutosController/index';
$route['api/produtos/(:num)'] = 'api/ProdutosController/index/$1';
$route['api/servicos'] = 'api/ServicosController/index';
$route['api/servicos/(:num)'] = 'api/ServicosController/index/$1';
$route['api/usuarios'] = 'api/UsuariosController/index';
$route['api/usuarios/(:num)'] = 'api/UsuariosController/index/$1';
$route['api/os'] = 'api/OsController/index';
$route['api/os/(:num)'] = 'api/OsController/index/$1';
$route['api/os/(:num)/produtos'] = 'api/OsController/produtos/$1';
$route['api/os/(:num)/produtos/(:num)'] = 'api/OsController/produtos/$1/$2';
$route['api/os/(:num)/servicos'] = 'api/OsController/servicos/$1';
$route['api/os/(:num)/servicos/(:num)'] = 'api/OsController/servicos/$1/$2';
$route['api/os/(:num)/anotacoes'] = 'api/OsController/anotacoes/$1';
$route['api/os/(:num)/anotacoes/(:num)'] = 'api/OsController/anotacoes/$1/$2';
$route['api/os/(:num)/anexos'] = 'api/OsController/anexos/$1';
$route['api/os/(:num)/anexos/(:num)'] = 'api/OsController/anexos/$1/$2';
$route['api/os/(:num)/desconto'] = 'api/OsController/desconto/$1';
