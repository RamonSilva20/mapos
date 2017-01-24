<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

if (defined('ENVIRONMENT')){
	switch (ENVIRONMENT){
		case 'production':
			$db['default']['hostname'] = 'localhost'; // muitas vezes é localhost
			$db['default']['username'] = 'usuario';
			$db['default']['password'] = 'senha';
			$db['default']['database'] = 'nome_base_dados';
			break;

		case 'development':
		$db['default']['hostname'] = 'localhost'; // muitas vezes é localhost
			$db['default']['username'] = 'usuario';
			$db['default']['password'] = 'senha';
			$db['default']['database'] = 'nome_base_dados';
			break;

		default:
			$db['default']['hostname'] = 'localhost'; // muitas vezes é localhost
			$db['default']['username'] = 'usuario';
			$db['default']['password'] = 'senha';
			$db['default']['database'] = 'nome_base_dados';
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
