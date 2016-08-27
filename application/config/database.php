<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$active_group = 'default';
$active_record = TRUE;

if (defined('ENVIRONMENT')){
	switch (ENVIRONMENT){
		case 'production':
			$db['default']['hostname'] = 'caminho.do.servidor'; // muitas vezes é localhost
			$db['default']['username'] = 'usuario.do.servidor';
			$db['default']['password'] = 'senha.do.servidor';
			$db['default']['database'] = 'banco.do.servidor';
			break;

		case 'development':
			$db['default']['username'] = 'root';
			$db['default']['password'] = 'root';
			$db['default']['database'] = 'banco';
			break;

		default:
			$db['default']['hostname'] = 'caminho.do.servidor'; // muitas vezes é localhost
			$db['default']['username'] = 'usuario.do.servidor';
			$db['default']['password'] = 'senha.do.servidor';
			$db['default']['database'] = 'banco.do.servidor';
			break;
	}
}

$db['default']['dbdriver'] = 'mysql';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = TRUE;
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