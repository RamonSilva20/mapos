<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Proxy para controllers de módulos.
 *
 * O CI3 chama este controller quando não encontra o controller pedido em
 * application/controllers/ (via $route['404_override']).
 * Aqui verificamos se o primeiro segmento da URI corresponde a um módulo
 * instalado e despachamos para o controller real do módulo.
 *
 * Estende CI_Controller (não MY_Controller) propositalmente: a checagem de
 * sessão e permissões fica a cargo do __construct() do controller do módulo.
 */
class Module_dispatch extends CI_Controller
{
    public function index()
    {
        $slug   = $this->uri->segment(1);
        $method = $this->uri->segment(2) ?: 'index';

        if (! $slug) {
            show_404();
        }

        $slug      = strtolower($slug);
        $ctrlClass = ucfirst($slug);
        $ctrlFile  = FCPATH . 'modules/' . $slug . '/controllers/' . $ctrlClass . '.php';

        if (! file_exists($ctrlFile)) {
            show_404($slug . '/' . $method);
        }

        // MY_Controller já está definido aqui (carregado pelo CodeIgniter.php antes)
        require_once $ctrlFile;

        if (! class_exists($ctrlClass, false)) {
            show_404($slug . '/' . $method);
        }

        // Bloqueia chamadas a métodos privados ou herdados de CI_Controller
        if ($method[0] === '_' || method_exists('CI_Controller', $method)) {
            show_404($slug . '/' . $method);
        }

        if (! method_exists($ctrlClass, $method)) {
            show_404($slug . '/' . $method);
        }

        // Parâmetros restantes da URI (segmentos a partir do 3.º)
        $params = [];
        for ($i = 3; ($seg = $this->uri->segment($i)) !== false; $i++) {
            $params[] = $seg;
        }

        // Instancia o controller do módulo — executa __construct() normalmente,
        // incluindo checagem de sessão e permissões via MY_Controller.
        $ctrl = new $ctrlClass();
        $ctrl->$method(...$params);
    }
}
