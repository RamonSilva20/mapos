<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Module_manager
{
    private $CI;
    private $modulesPath;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
        $this->modulesPath = FCPATH . 'modules/';
    }

    /**
     * Faz o upload e extração de um arquivo ZIP para a pasta modules/.
     * Retorna o slug do módulo ou false em caso de erro.
     */
    public function upload(array $file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Erro no upload do arquivo.'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            return ['error' => 'Apenas arquivos .zip são permitidos.'];
        }

        if (! class_exists('ZipArchive')) {
            return ['error' => 'Extensão ZipArchive não está disponível no PHP.'];
        }

        $tmpSlug = 'tmp_' . uniqid();
        $extractPath = $this->modulesPath . $tmpSlug . '/';

        if (! is_dir($this->modulesPath)) {
            mkdir($this->modulesPath, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) {
            return ['error' => 'Não foi possível abrir o arquivo ZIP.'];
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Se o ZIP contém uma subpasta raiz, sobe o conteúdo para $extractPath
        $items = array_diff(scandir($extractPath), ['.', '..']);
        if (count($items) === 1) {
            $firstItem = reset($items);
            $subDir    = $extractPath . $firstItem . '/';
            if (is_dir($subDir)) {
                // Usa um caminho IRMÃO (fora de $extractPath) como temporário
                $tmpSibling = rtrim($extractPath, '/') . '_mv';
                rename($subDir, $tmpSibling);
                rmdir(rtrim($extractPath, '/'));
                rename($tmpSibling, rtrim($extractPath, '/'));
            }
        }

        // Valida o module.json
        $manifestPath = $extractPath . 'module.json';
        if (! file_exists($manifestPath)) {
            $this->deleteDir($extractPath);
            return ['error' => 'Arquivo module.json não encontrado no módulo.'];
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (! $manifest || empty($manifest['slug'])) {
            $this->deleteDir($extractPath);
            return ['error' => 'module.json inválido ou sem campo "slug".'];
        }

        $slug = $this->sanitizeSlug($manifest['slug']);
        $destPath = $this->modulesPath . $slug . '/';

        if (is_dir($destPath)) {
            $this->deleteDir($extractPath);
            return ['error' => "Módulo '{$slug}' já existe. Desinstale-o antes de reinstalar."];
        }

        rename($extractPath, $destPath);

        return ['slug' => $slug, 'manifest' => $manifest];
    }

    /**
     * Lê e valida o module.json de um módulo.
     */
    public function getManifest(string $slug)
    {
        $path = $this->modulesPath . $slug . '/module.json';
        if (! file_exists($path)) {
            return false;
        }

        $manifest = json_decode(file_get_contents($path), true);
        if (! $manifest || empty($manifest['slug'])) {
            return false;
        }

        return $manifest;
    }

    /**
     * Instala um módulo já presente em /modules/<slug>/.
     */
    public function install(string $slug)
    {
        $manifest = $this->getManifest($slug);
        if (! $manifest) {
            return ['error' => 'module.json inválido ou não encontrado.'];
        }

        // Verifica se já está instalado
        $existing = $this->CI->db->where('slug', $slug)->get('modulos')->row();
        if ($existing) {
            return ['error' => 'Módulo já está instalado.'];
        }

        $modulePath = $this->modulesPath . $slug . '/';

        // 1. Executa SQL de instalação
        $sqlFile = $modulePath . 'migrations/install.sql';
        if (file_exists($sqlFile)) {
            $result = $this->runSqlFile($sqlFile);
            if ($result !== true) {
                return ['error' => 'Erro ao executar SQL de instalação: ' . $result];
            }
        }

        // 2. Chama install.php do módulo (com $CI disponível)
        $installScript = $modulePath . 'install.php';
        if (file_exists($installScript)) {
            $CI = $this->CI;
            $moduleSlug = $slug;
            $moduleManifest = $manifest;
            include $installScript;
        }

        // 3. Gera proxies para controllers hookados + wrappers para controllers do módulo
        $this->generateProxies($slug);
        $createdFiles = [];
        $createdFiles = array_merge($createdFiles, $this->createWrappers($slug, $modulePath, 'controllers'));
        $createdFiles = array_merge($createdFiles, $this->createWrappers($slug, $modulePath, 'models'));

        // 4. Registra o módulo PRIMEIRO — menu items têm FK para modulos.slug
        $this->CI->db->insert('modulos', [
            'slug'      => $slug,
            'nome'      => $manifest['name'] ?? $slug,
            'versao'    => $manifest['version'] ?? '1.0.0',
            'descricao' => $manifest['description'] ?? '',
            'autor'     => $manifest['author'] ?? '',
            'status'    => 'ativo',
        ]);

        // 5. Registra menu items (FK satisfeita após passo 4)
        if (! empty($manifest['menu']) && is_array($manifest['menu'])) {
            foreach ($manifest['menu'] as $item) {
                $this->CI->db->insert('modulo_menu_items', [
                    'modulo_slug' => $slug,
                    'titulo'      => $item['title'] ?? $manifest['name'],
                    'url'         => $item['url'] ?? $slug,
                    'icone'       => $item['icon'] ?? 'bx bx-extension',
                    'permissao'   => $item['permission'] ?? null,
                    'tooltip'     => $item['tooltip'] ?? ($item['title'] ?? $manifest['name']),
                    'ordem'       => $item['ordem'] ?? 100,
                ]);
            }
        }

        // 6. Registra configurações
        if (! empty($manifest['configurations']) && is_array($manifest['configurations'])) {
            foreach ($manifest['configurations'] as $config) {
                if (empty($config['key'])) {
                    continue;
                }
                $this->CI->db->query(
                    "INSERT IGNORE INTO `configuracoes` (`config`, `valor`) VALUES (?, ?)",
                    [$config['key'], $config['value'] ?? '']
                );
            }
        }

        return ['success' => true, 'files' => $createdFiles];
    }

    /**
     * Desinstala um módulo.
     */
    public function uninstall(string $slug)
    {
        $module = $this->CI->db->where('slug', $slug)->get('modulos')->row();
        if (! $module) {
            return ['error' => 'Módulo não encontrado.'];
        }

        $modulePath = $this->modulesPath . $slug . '/';

        // 1. Executa SQL de desinstalação
        $sqlFile = $modulePath . 'migrations/uninstall.sql';
        if (file_exists($sqlFile)) {
            $this->runSqlFile($sqlFile);
        }

        // 2. Chama uninstall.php do módulo
        $uninstallScript = $modulePath . 'uninstall.php';
        if (file_exists($uninstallScript)) {
            $CI = $this->CI;
            $moduleSlug = $slug;
            include $uninstallScript;
        }

        // 3. Remove wrappers e restaura controllers originais se necessário
        $this->removeWrappers($slug, 'controllers');
        $this->removeWrappers($slug, 'models');
        $this->restoreControllers($slug);

        // 4. Remove configurações do módulo (via manifest)
        $manifest = $this->getManifest($slug);
        if ($manifest && ! empty($manifest['configurations'])) {
            foreach ($manifest['configurations'] as $config) {
                if (! empty($config['key'])) {
                    $this->CI->db->delete('configuracoes', ['config' => $config['key']]);
                }
            }
        }

        // 5. Remove da tabela modulos (CASCADE cuida de modulo_menu_items)
        $this->CI->db->delete('modulos', ['slug' => $slug]);

        // 6. Remove pasta do módulo
        if (is_dir($modulePath)) {
            $this->deleteDir($modulePath);
        }

        return ['success' => true];
    }

    /**
     * Alterna o status ativo/inativo do módulo.
     */
    public function toggle(string $slug)
    {
        $module = $this->CI->db->where('slug', $slug)->get('modulos')->row();
        if (! $module) {
            return ['error' => 'Módulo não encontrado.'];
        }

        $newStatus = ($module->status === 'ativo') ? 'inativo' : 'ativo';
        $this->CI->db->update('modulos', ['status' => $newStatus], ['slug' => $slug]);

        return ['success' => true, 'status' => $newStatus];
    }

    /**
     * Lista módulos disponíveis em /modules/ que ainda não estão instalados.
     */
    public function getAvailable()
    {
        if (! is_dir($this->modulesPath)) {
            return [];
        }

        $installed = $this->CI->db->get('modulos')->result();
        $installedSlugs = array_column($installed, 'slug');

        $available = [];
        foreach (scandir($this->modulesPath) as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            if (! is_dir($this->modulesPath . $dir)) {
                continue;
            }
            if (in_array($dir, $installedSlugs)) {
                continue;
            }
            $manifest = $this->getManifest($dir);
            if ($manifest) {
                $available[] = $manifest;
            }
        }

        return $available;
    }

    // -----------------------------------------------------------------------
    // Controller proxy system
    // -----------------------------------------------------------------------

    /**
     * Gera arquivos proxy para todos os controllers referenciados pelos hooks do módulo.
     * O proxy embrulha cada método público com before::/after:: hooks automáticos.
     */
    private function generateProxies(string $slug): void
    {
        foreach ($this->getHookedControllers($slug) as $className) {
            $this->generateControllerProxy($className);
        }
    }

    /**
     * Restaura controllers que não são mais hookados por nenhum módulo ativo.
     */
    private function restoreControllers(string $uninstalledSlug): void
    {
        $needed = $this->getHookedControllers($uninstalledSlug);
        if (empty($needed)) {
            return;
        }

        // Quais controllers ainda são necessários pelos outros módulos?
        $stillNeeded = [];
        $otherModules = $this->CI->db
            ->where('slug !=', $uninstalledSlug)
            ->get('modulos')
            ->result();
        foreach ($otherModules as $m) {
            foreach ($this->getHookedControllers($m->slug) as $c) {
                $stillNeeded[] = $c;
            }
        }

        foreach ($needed as $className) {
            if (! in_array($className, $stillNeeded, true)) {
                $this->restoreControllerOriginal($className);
            }
        }
    }

    /**
     * Lê o module_hooks.php do módulo e retorna os nomes únicos de controllers
     * referenciados por hooks no formato "before::Controller::method".
     */
    private function getHookedControllers(string $slug): array
    {
        $hookFile = $this->modulesPath . $slug . '/hooks/module_hooks.php';
        if (! file_exists($hookFile)) {
            return [];
        }

        $moduleControllerHooks = [];
        include $hookFile;

        $controllers = [];
        foreach (array_keys($moduleControllerHooks) as $hookName) {
            if (preg_match('/^(?:before|after)::([A-Za-z][A-Za-z0-9_]*)::/', $hookName, $m)) {
                $controllers[] = $m[1];
            }
        }

        return array_unique($controllers);
    }

    /**
     * Gera o proxy de um controller e salva o original em __originals/.
     *
     * Estrutura resultante:
     *   application/controllers/Foo.php          ← proxy gerado
     *   application/controllers/__originals/Foo.php ← original com classe renomeada para Foo__real
     */
    private function generateControllerProxy(string $className): bool
    {
        $controllerFile = APPPATH . 'controllers/' . $className . '.php';
        $originalsDir   = APPPATH . 'controllers/__originals/';
        $originalBak    = $originalsDir . $className . '.php';

        if (! file_exists($controllerFile)) {
            return false;
        }

        // Já está proxiado
        if (file_exists($originalBak)) {
            return true;
        }

        $src = file_get_contents($controllerFile);

        // Não proxia arquivos de infraestrutura do próprio sistema de módulos
        if (
            strpos($src, 'Auto-generated by Module_manager') !== false
            || strpos($src, '__real') !== false
            || strpos($src, 'Module_dispatch') !== false
        ) {
            return false;
        }

        if (! is_dir($originalsDir) && ! @mkdir($originalsDir, 0755, true)) {
            return false;
        }

        // Salva backup com a classe renomeada para Foo__real
        $realSrc = preg_replace(
            '/\bclass\s+' . preg_quote($className, '/') . '\b/',
            'class ' . $className . '__real',
            $src
        );
        if (@file_put_contents($originalBak, $realSrc) === false) {
            return false;
        }

        // Extrai todos os métodos públicos (exclui mágicos como __construct)
        preg_match_all(
            '/\bpublic\s+function\s+([a-zA-Z][a-zA-Z0-9_]*)\s*\(/',
            $src,
            $matches
        );
        $methods = $matches[1];

        $lines   = [];
        $lines[] = '<?php';
        $lines[] = '// Auto-generated by Module_manager — do not edit manually.';
        $lines[] = "if (! defined('BASEPATH')) exit('No direct script access allowed');";
        $lines[] = '';
        $lines[] = "require_once __DIR__ . '/__originals/{$className}.php';";
        $lines[] = '';
        $lines[] = "class {$className} extends {$className}__real";
        $lines[] = '{';

        foreach ($methods as $method) {
            $lines[] = "    public function {$method}()";
            $lines[] = '    {';
            $lines[] = '        $args = func_get_args();';
            $lines[] = "        \$ctx  = ['controller' => '{$className}', 'method' => '{$method}', 'args' => &\$args, 'result' => null];";
            $lines[] = "        module_controller_hook('before::{$className}::{$method}', \$ctx);";
            $lines[] = "        if (! empty(\$ctx['abort'])) {";
            $lines[] = "            if (isset(\$ctx['response'])) echo \$ctx['response'];";
            $lines[] = '            return;';
            $lines[] = '        }';
            $lines[] = "        \$ctx['result'] = parent::{$method}(...\$args);";
            $lines[] = "        module_controller_hook('after::{$className}::{$method}', \$ctx);";
            $lines[] = "        return \$ctx['result'];";
            $lines[] = '    }';
            $lines[] = '';
        }

        $lines[] = '}';

        @file_put_contents($controllerFile, implode("\n", $lines) . "\n");

        return true;
    }

    /**
     * Restaura o controller original a partir do backup em __originals/.
     */
    private function restoreControllerOriginal(string $className): bool
    {
        $controllerFile = APPPATH . 'controllers/' . $className . '.php';
        $originalBak    = APPPATH . 'controllers/__originals/' . $className . '.php';

        if (! file_exists($originalBak)) {
            return false;
        }

        $src = file_get_contents($originalBak);
        $src = preg_replace(
            '/\bclass\s+' . preg_quote($className . '__real', '/') . '\b/',
            'class ' . $className,
            $src
        );

        file_put_contents($controllerFile, $src);
        unlink($originalBak);

        // Remove __originals/ se ficar vazio
        $originalsDir = APPPATH . 'controllers/__originals/';
        if (is_dir($originalsDir) && empty(array_diff(scandir($originalsDir), ['.', '..']))) {
            rmdir($originalsDir);
        }

        return true;
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    private function createWrappers(string $slug, string $modulePath, string $type)
    {
        $created = [];
        $sourceDir = $modulePath . $type . '/';
        if (! is_dir($sourceDir)) {
            return $created;
        }

        $destDir = APPPATH . $type . '/';
        foreach (glob($sourceDir . '*.php') as $sourceFile) {
            $filename = basename($sourceFile);
            $destFile = $destDir . $filename;

            if (! file_exists($destFile)) {
                $content = "<?php\nif (!defined('BASEPATH')) exit('No direct script access allowed');\nrequire_once FCPATH . 'modules/{$slug}/{$type}/{$filename}';\n";
                file_put_contents($destFile, $content);
                $created[] = $destFile;
            }
        }

        return $created;
    }

    private function removeWrappers(string $slug, string $type)
    {
        $sourceDir = $this->modulesPath . $slug . '/' . $type . '/';
        if (! is_dir($sourceDir)) {
            return;
        }

        $destDir = APPPATH . $type . '/';
        foreach (glob($sourceDir . '*.php') as $sourceFile) {
            $filename = basename($sourceFile);
            $wrapperFile = $destDir . $filename;
            if (file_exists($wrapperFile)) {
                $content = file_get_contents($wrapperFile);
                // Só remove se for um wrapper gerado pelo sistema de módulos
                if (strpos($content, "modules/{$slug}/") !== false) {
                    unlink($wrapperFile);
                }
            }
        }
    }

    private function runSqlFile(string $path)
    {
        $sql = file_get_contents($path);
        if (empty(trim($sql))) {
            return true;
        }

        // Divide e executa query por query
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($queries as $query) {
            if (! $this->CI->db->simple_query($query)) {
                return $this->CI->db->error()['message'] ?? 'Erro desconhecido';
            }
        }

        return true;
    }

    private function sanitizeSlug(string $slug)
    {
        return preg_replace('/[^a-z0-9_]/', '', strtolower($slug));
    }

    private function deleteDir(string $path)
    {
        if (! is_dir($path)) {
            return;
        }
        $items = array_diff(scandir($path), ['.', '..']);
        foreach ($items as $item) {
            $full = $path . DIRECTORY_SEPARATOR . $item;
            is_dir($full) ? $this->deleteDir($full) : unlink($full);
        }
        rmdir($path);
    }

    private function renameDir(string $from, string $to)
    {
        rename($from, $to);
    }
}
