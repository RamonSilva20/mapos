<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

if (defined('ENVIRONMENT')){
	switch (ENVIRONMENT){
		case 'production':
			$db['default']['hostname'] = 'localhost'; // muitas vezes é localhost
			$db['default']['username'] = 'root';
			$db['default']['password'] = '';
			$db['default']['database'] = 'mapos';
			break;

		case 'development':
		$db['default']['hostname'] = 'localhost'; // muitas vezes é localhost
			$db['default']['username'] = 'gus';
			$db['default']['password'] = 'Gust4v0;';
			$db['default']['database'] = 'mapos';
			break;

		default:
			$db['default']['hostname'] = 'localhost'; // muitas vezes é localhost
			$db['default']['username'] = 'root';
			$db['default']['password'] = '';
			$db['default']['database'] = 'mapos';
			break;
	}
}

$db['default']['dbdriver'] = 'mysqli';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = FALSE;
$db['default']['db_debug'] = TRUE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;


/* End of file database.php */
/* Location: ./application/config/database.php */
