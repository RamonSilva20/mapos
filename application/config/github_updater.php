<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
* The user name of the git hub user who owns the repo
* https://api.github.com/repos/hoshikawakun/MasterOS/releases
*/
$config['github_user'] = 'hoshikawakun';

/**
* The repo on GitHub we will be updating from
*/
$config['github_repo'] = 'MasterOS';

/**
* The branch to update from
*/
$config['github_branch'] = 'master';

/**
* The current commit the files are on.
*
* NOTE: You should only need to set this initially it will be
* automatically set by the library after subsequent updates.
*/
$config['current_commit'] = '9f7c50f0c410980be8db3d01e1825e963e081247';

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
$config['ignored_files'] = [
    'application/database',
    'application/email',
];

/**
* Flag to indicate if the downloaded and extracted update files
* should be removed
*/
$config['clean_update_files'] = true;