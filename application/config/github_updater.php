<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * The user name of the git hub user who owns the repo
 */
$config['github_user'] = 'RamonSilva20';

/**
 * The repo on GitHub we will be updating from
 */
$config['github_repo'] = 'mapos';

/**
 * Inclui pré-releases (alpha, beta, rc) na busca da versão mais nova. Por
 * padrão só as releases estáveis; quem testa a v5 liga com UPDATER_PRERELEASES=true
 * no .env. O atualizador usa a última release com tag, nunca um branch (#2871).
 */
$config['github_prereleases'] = filter_var($_ENV['UPDATER_PRERELEASES'] ?? false, FILTER_VALIDATE_BOOLEAN);

/**
 * A list of files or folders to never perform an update on.
 * Not specifying a relative path from the webroot will apply
 * the ignore to any files with a matching segment.
 *
 * I.E. Specifying 'admin' as an ignore will ignore
 * 'application/controllers/admin.php'
 * 'application/views/admin/test.php'
 * and any other path with the term 'admin' in it.
 */
// O .env, os uploads, os logs e a pasta install/ nunca são tocados (Github_updater::PROTEGIDOS); aqui entram os extras de cada instalação.
$config['ignored_files'] = [];
