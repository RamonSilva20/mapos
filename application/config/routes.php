<?php  if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "mapos";
$route['404_override'] = '';

// Rotas API
$route['api']                            = 'api/ApiController/index';
$route['api/audit']                      = 'api/ApiController/audit';
$route['api/login']                      = 'api/UsuariosController/login';
$route['api/reGenToken']                 = 'api/UsuariosController/reGenToken';
$route['api/conta']                      = 'api/UsuariosController/conta';
$route['api/emitente']                   = 'api/ApiController/emitente';
$route['api/clientes']                   = 'api/ClientesController/index';
$route['api/clientes/(:num)']            = 'api/ClientesController/index/$1';
$route['api/produtos']                   = 'api/ProdutosController/index';
$route['api/produtos/(:num)']            = 'api/ProdutosController/index/$1';
$route['api/servicos']                   = 'api/ServicosController/index';
$route['api/servicos/(:num)']            = 'api/ServicosController/index/$1';
$route['api/usuarios']                   = 'api/UsuariosController/index';
$route['api/usuarios/(:num)']            = 'api/UsuariosController/index/$1';
$route['api/os']                         = 'api/OsController/index';
$route['api/os/(:num)']                  = 'api/OsController/index/$1';
$route['api/os/(:num)/produtos']         = 'api/OsController/produtos/$1';
$route['api/os/(:num)/produtos/(:num)']  = 'api/OsController/produtos/$1/$2';
$route['api/os/(:num)/servicos']         = 'api/OsController/servicos/$1';
$route['api/os/(:num)/servicos/(:num)']  = 'api/OsController/servicos/$1/$2';
$route['api/os/(:num)/anotacoes']        = 'api/OsController/anotacoes/$1';
$route['api/os/(:num)/anotacoes/(:num)'] = 'api/OsController/anotacoes/$1/$2';
$route['api/os/(:num)/anexos']           = 'api/OsController/anexos/$1';
$route['api/os/(:num)/anexos/(:num)']    = 'api/OsController/anexos/$1/$2';
$route['api/os/(:num)/desconto']         = 'api/OsController/desconto/$1';

/* End of file routes.php */
/* Location: ./application/config/routes.php */
