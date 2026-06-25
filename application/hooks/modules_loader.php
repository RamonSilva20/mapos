<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Hook pre_controller: carrega todos os módulos ativos,
 * adicionando seus paths ao CI loader e registrando view hooks.
 */
function load_active_modules()
{
    $CI = &get_instance();

    // Verifica se a tabela existe antes de consultar (evita erro em primeira instalação)
    if (! $CI->db->table_exists('modulos')) {
        return;
    }

    $modules = $CI->db
        ->where('status', 'ativo')
        ->get('modulos')
        ->result();

    $viewHooks = [];

    foreach ($modules as $module) {
        $path = FCPATH . 'modules/' . $module->slug . '/';
        if (! is_dir($path)) {
            continue;
        }

        // Adiciona o path do módulo ao CI loader (models, views, helpers, config)
        $CI->load->add_package_path($path);

        // Carrega o arquivo de view hooks do módulo
        $hookFile = $path . 'hooks/module_hooks.php';
        if (file_exists($hookFile)) {
            $moduleViewHooks = [];
            include $hookFile;
            // Módulo define $moduleViewHooks['hook_name'] = ['arquivo.php']
            foreach ($moduleViewHooks as $hookName => $files) {
                if (! isset($viewHooks[$hookName])) {
                    $viewHooks[$hookName] = [];
                }
                $viewHooks[$hookName] = array_merge($viewHooks[$hookName], (array) $files);
            }
        }
    }

    $CI->config->set_item('module_view_hooks', $viewHooks);
}
