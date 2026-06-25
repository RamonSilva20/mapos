<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Executa um ponto de injeção de módulos numa view.
 *
 * Uso em qualquer view existente:
 *   <?php module_hook('os_detail_tabs'); ?>
 */
/**
 * Executa um ponto de hook em um controller existente.
 *
 * O array $context e passado por referencia para que os arquivos de hook possam
 * modificar dados (ex: adicionar campos ao array de save, abortar uma operacao).
 *
 * Convencao de $context:
 *   - qualquer chave de dados relevante ao ponto de hook
 *   - $context['abort']    = true   => o controller deve interromper a operacao
 *   - $context['response'] = string => conteudo a ser enviado ao cliente ao abortar
 *
 * Uso em controllers existentes:
 *   $context = ['cliente' => $cliente];
 *   module_controller_hook('mine_login_after_verify', $context);
 *   if (!empty($context['abort'])) { echo $context['response']; return; }
 */
if (! function_exists('module_controller_hook')) {
    function module_controller_hook(string $hookName, array &$context = []): void
    {
        $CI = &get_instance();

        $hooks = $CI->config->item('module_controller_hooks');

        if ($hooks === null) {
            $hooks  = [];
            $tables = $CI->db->list_tables();
            if (in_array('modulos', $tables)) {
                $modules = $CI->db
                    ->where('status', 'ativo')
                    ->get('modulos')
                    ->result();
                foreach ($modules as $module) {
                    $path     = FCPATH . 'modules/' . $module->slug . '/';
                    $hookFile = $path . 'hooks/module_hooks.php';
                    if (file_exists($hookFile)) {
                        $moduleControllerHooks = [];
                        include $hookFile;
                        foreach ($moduleControllerHooks as $name => $files) {
                            $hooks[$name] = array_merge($hooks[$name] ?? [], (array) $files);
                        }
                    }
                }
            }
            $CI->config->set_item('module_controller_hooks', $hooks);
        }

        if (empty($hooks[$hookName])) {
            return;
        }

        foreach ($hooks[$hookName] as $hookFile) {
            if (file_exists($hookFile)) {
                include $hookFile;
            }
        }
    }
}

if (! function_exists('module_hook')) {
    function module_hook(string $hookName)
    {
        $CI = &get_instance();

        // Tenta usar o cache do config (populado por MY_Controller::load_configuration).
        // Se retornar null (nunca foi setado), carrega diretamente do banco como fallback.
        $hooks = $CI->config->item('module_view_hooks');

        if ($hooks === null) {
            $hooks  = [];
            $tables = $CI->db->list_tables();
            if (in_array('modulos', $tables)) {
                $modules = $CI->db
                    ->where('status', 'ativo')
                    ->get('modulos')
                    ->result();
                foreach ($modules as $module) {
                    $path     = FCPATH . 'modules/' . $module->slug . '/';
                    $hookFile = $path . 'hooks/module_hooks.php';
                    if (file_exists($hookFile)) {
                        $moduleViewHooks = [];
                        include $hookFile;
                        foreach ($moduleViewHooks as $name => $files) {
                            $hooks[$name] = array_merge($hooks[$name] ?? [], (array) $files);
                        }
                    }
                }
            }
            $CI->config->set_item('module_view_hooks', $hooks);
        }

        if (empty($hooks[$hookName])) {
            return;
        }

        // Extrai as variáveis cacheadas do CI Loader (result, configuration, etc.)
        // para que os arquivos de hook possam acessá-las.
        extract($CI->load->get_vars());

        foreach ($hooks[$hookName] as $viewFile) {
            if (file_exists($viewFile)) {
                include $viewFile;
            }
        }
    }
}
