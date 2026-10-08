<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Catálogo da biblioteca de componentes (application/views/components/).
 *
 * Só existe em development: em qualquer outro ambiente responde 404 antes de
 * qualquer outra coisa, inclusive da checagem de login.
 */
class Componentes extends MY_Controller
{
    public function __construct()
    {
        if (ENVIRONMENT !== 'development') {
            show_404();
        }

        parent::__construct();
    }

    public function index()
    {
        $this->load->view('componentes/catalogo', $this->data);
    }
}
