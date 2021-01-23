<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Github_updater
{
    const API_URL = 'https://api.github.com/repos/';
    const GITHUB_URL = 'https://github.com/';
    const GITHUB_UPDATER_CONFIG_FILE = 'application/config/github_updater.php';
    const CONFIG_FILE = 'application/config/config.php';

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->config('github_updater');
        ini_set('MAX_EXECUTION_TIME', '-1');
    }

    /**
    * Checks if the current version is up to date
    *
    * @return bool true if there is an update and false otherwise
    */
    public function has_update()
    {
        $branches = json_decode(
            $this->_connect(self::API_URL.$this->ci->config->item('github_user').'/'.$this->ci->config->item('github_repo').'/branches')
        );

        $branchToUpdateFrom = current(
            array_filter($branches, function ($branch) {
                return $branch->name === $this->ci->config->item('github_branch');
            })
        );

        if (!$branchToUpdateFrom) {
            throw new Exception('The branch to update from GitHub does not exist!');
        }

        return $branchToUpdateFrom->commit->sha !== $this->ci->config->item('current_commit');
    }

    /**
    * If there is an update available get an array of all of the
    * commit messages between the versions
    *
    * @return array of the messages or false if no update
    */
    public function get_update_comments()
    {
        $hash = $this->getCurrentCommitHash();

        if ($hash !== $this->ci->config->item('current_commit')) {
            $messages = [];
            $response = json_decode($this->_connect(self::API_URL.$this->ci->config->item('github_user').'/'.$this->ci->config->item('github_repo').'/compare/'.$this->ci->config->item('current_commit').'...'.$hash));
            $commits = $response->commits;

            foreach ($commits as $commit) {
                $messages[] = $commit->commit->message;
            }

            return $messages;
        }

        return false;
    }

    /**
    * Performs an update if one is available.
    *
    * @return bool true on success, false on failure
    */
    public function update()
    {
        $hash = $this->getCurrentCommitHash();
        $currentVersion = $this->getCurrentVersion();

        if ($hash !== $this->ci->config->item('current_commit')) {
            $commits = json_decode($this->_connect(self::API_URL.$this->ci->config->item('github_user').'/'.$this->ci->config->item('github_repo').'/compare/'.$this->ci->config->item('current_commit').'...'.$hash));
            $files = $commits->files;

            if ($dir = $this->_get_and_extract($hash)) {
                // Loop through the list of changed files for this commit
                foreach ($files as $file) {
                    // If the file isn't in the ignored list then perform the update
                    if (!$this->_is_ignored($file->filename)) {
                        // If the status is removed then delete the file
                        if ($file->status === 'removed') {
                            if (file_exists($file->filename)) {
                                unlink($file->filename);
                            }
                        }
                        // Otherwise copy the file from the update.
                        else {
                            copy($dir.'/'.$file->filename, $file->filename);
                        }
                    }
                }

                // Clean up
                if ($this->ci->config->item('clean_update_files')) {
                    $this->deleteDirectory($dir);
                    unlink("{$hash}.zip");
                }

                if ($this->commandExists('composer')) {
                    shell_exec("cd ../../ && composer install --no-dev");
                }

                // Update the current commit hash
                $this->_set_github_updater_config_hash($hash);

                // Update the mapos version in the config filename
                $this->_set_config_app_version($currentVersion);

                return true;
            }
        }

        return false;
    }

    private function _is_ignored($filename)
    {
        $ignored = $this->ci->config->item('ignored_files');

        foreach ($ignored as $ignore) {
            if (strpos($filename, $ignore) !== false) {
                return true;
            }
        }

        return false;
    }

    private function _set_github_updater_config_hash($hash)
    {
        $lines = file(self::GITHUB_UPDATER_CONFIG_FILE, FILE_IGNORE_NEW_LINES);
        $count = count($lines);

        for ($i=0; $i < $count; $i++) {
            $configline = '$config[\'current_commit\']';

            if (strstr($lines[$i], $configline)) {
                $lines[$i] = $configline.' = \''.$hash.'\';';
                $file = implode(PHP_EOL, $lines);
                $handle = @fopen(self::GITHUB_UPDATER_CONFIG_FILE, 'w');
                fwrite($handle, $file);
                fclose($handle);

                return true;
            }
        }

        return false;
    }

    private function _set_config_app_version($version)
    {
        $lines = file(self::CONFIG_FILE, FILE_IGNORE_NEW_LINES);
        $count = count($lines);

        for ($i=0; $i < $count; $i++) {
            $configline = '$config[\'app_version\']';

            if (strstr($lines[$i], $configline)) {
                $lines[$i] = $configline.' = \''.$version.'\';';
                $file = implode(PHP_EOL, $lines);
                $handle = @fopen(self::CONFIG_FILE, 'w');
                fwrite($handle, $file);
                fclose($handle);

                return true;
            }
        }

        return false;
    }

    private function _get_and_extract($hash)
    {
        copy(self::GITHUB_URL.$this->ci->config->item('github_user').'/'.$this->ci->config->item('github_repo').'/zipball/'.$this->ci->config->item('github_branch'), "{$hash}.zip");

        $unzip = new ZipArchive();

        $output = $unzip->open("{$hash}.zip");
        if ($output) {
            $unzip->extractTo(getcwd());
            $unzip->close();
        } else {
            throw new Error('Error opening zip file!');
        }

        $files = scandir('.');

        foreach ($files as $file) {
            if (strpos($file, $this->ci->config->item('github_user').'-'.$this->ci->config->item('github_repo')) !== false) {
                return $file;
            }
        }

        return false;
    }

    private function _connect($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC) ;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mapos'
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);

        return $response;
    }

    /**
    * Get the latest commit hash of the branch that it needs to update from.
    *
    * @return string The branch to update from latest commit hash.
    */
    private function getCurrentCommitHash()
    {
        $branches = json_decode(
            $this->_connect(self::API_URL.$this->ci->config->item('github_user').'/'.$this->ci->config->item('github_repo').'/branches')
        );

        $branchToUpdateFrom = current(
            array_filter($branches, function ($branch) {
                return $branch->name === $this->ci->config->item('github_branch');
            })
        );

        if (!$branchToUpdateFrom) {
            throw new Exception('The branch to update from GitHub does not exist!');
        }

        return $branchToUpdateFrom->commit->sha;
    }

    /**
    * Get mapos current version.
    *
    * @return string Mapos current version.
    */
    private function getCurrentVersion()
    {
        $latestRelease = json_decode(
            $this->_connect(self::API_URL.$this->ci->config->item('github_user').'/'.$this->ci->config->item('github_repo').'/releases/latest')
        );

        $version = $latestRelease->tag_name;

        if (!$version) {
            throw new Exception('Error getting mapos version from GitHub!');
        }

        return str_replace("v", "", $version);
    }

    /**
    * Determines if a command exists on the current environment
    *
    * @param string $command The command to check
    * @return bool True if the command has been found ; otherwise, false.
    */
    private function commandExists($command)
    {
        $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';

        $process = proc_open(
            "$whereIsCommand $command",
            [
                0 => ["pipe", "r"], //STDIN
                1 => ["pipe", "w"], //STDOUT
                2 => ["pipe", "w"], //STDERR
            ],
            $pipes
        );

        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);

            return $stdout != '';
        }

        return false;
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}
