<?php

class MY_Controller extends CI_Controller
{
    public $data = [
        'configuration' => [
            'per_page' => 10,
            'next_link' => 'Próxima',
            'prev_link' => 'Anterior',
            'full_tag_open' => '<div class="pagination alternate"><ul>',
            'full_tag_close' => '</ul></div>',
            'num_tag_open' => '<li>',
            'num_tag_close' => '</li>',
            'cur_tag_open' => '<li><a style="color: #2D335B"><b>',
            'cur_tag_close' => '</b></a></li>',
            'prev_tag_open' => '<li>',
            'prev_tag_close' => '</li>',
            'next_tag_open' => '<li>',
            'next_tag_close' => '</li>',
            'first_link' => 'Primeira',
            'last_link' => 'Última',
            'first_tag_open' => '<li>',
            'first_tag_close' => '</li>',
            'last_tag_open' => '<li>',
            'last_tag_close' => '</li>',
            'app_name' => 'Map-OS',
            'app_theme' => 'default',
            'os_notification' => 'cliente',
            'control_estoque' => '1',
            'notifica_whats' => '',
            'control_baixa' => '0',
            'control_editos' => '1',
            'control_datatable' => '1',
            'pix_key' => '',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        if ((! session_id()) || (! $this->session->userdata('logado'))) {
            redirect('login');
        }
        $this->load_configuration();
    }

    private function load_configuration()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
        $configuracoes = $this->CI->db->get('configuracoes')->result();

        foreach ($configuracoes as $c) {
            $this->data['configuration'][$c->config] = $c->valor;
        }

        // Carrega módulos ativos: package paths, view hooks e menu items
        if ($this->CI->db->table_exists('modulos')) {
            $activeModules = $this->CI->db
                ->where('status', 'ativo')
                ->get('modulos')
                ->result();

            $viewHooks       = [];
            $controllerHooks = [];
            foreach ($activeModules as $module) {
                $path = FCPATH . 'modules/' . $module->slug . '/';
                if (! is_dir($path)) {
                    continue;
                }
                // Adiciona path do módulo para views, models, helpers, config
                $this->CI->load->add_package_path($path);

                // Registra view hooks e controller hooks do módulo
                $hookFile = $path . 'hooks/module_hooks.php';
                if (file_exists($hookFile)) {
                    $moduleViewHooks       = [];
                    $moduleControllerHooks = [];
                    include $hookFile;
                    foreach ($moduleViewHooks as $hookName => $files) {
                        $viewHooks[$hookName] = array_merge(
                            $viewHooks[$hookName] ?? [],
                            (array) $files
                        );
                    }
                    foreach ($moduleControllerHooks as $hookName => $files) {
                        $controllerHooks[$hookName] = array_merge(
                            $controllerHooks[$hookName] ?? [],
                            (array) $files
                        );
                    }
                }
            }
            $this->CI->config->set_item('module_view_hooks', $viewHooks);
            $this->CI->config->set_item('module_controller_hooks', $controllerHooks);

            $this->data['module_menu_items'] = $this->CI->db
                ->select('modulo_menu_items.*')
                ->join('modulos', 'modulos.slug = modulo_menu_items.modulo_slug')
                ->where('modulos.status', 'ativo')
                ->order_by('modulo_menu_items.ordem', 'ASC')
                ->get('modulo_menu_items')
                ->result();
        } else {
            $this->data['module_menu_items'] = [];
        }
    }

    public function layout()
    {
        // load views
        $this->load->view('tema/topo', $this->data);
        $this->load->view('tema/menu');
        $this->load->view('tema/conteudo');
        $this->load->view('tema/rodape');
    }
}
