<?php

defined('BASEPATH') or exit('No direct script access allowed');

$active_group = 'default';
$query_builder = true;
$db['default'] = [
    'dsn' => $_ENV['DB_DSN'] ?? '',
    'hostname' => $_ENV['DB_HOSTNAME'] ?? 'enter_hostname',
    'port' => $_ENV['DB_PORT'] ?? '',
    'username' => $_ENV['DB_USERNAME'] ?? 'enter_db_username',
    'password' => $_ENV['DB_PASSWORD'] ?? 'enter_db_password',
    'database' => $_ENV['DB_DATABASE'] ?? 'enter_database_name',
    'dbdriver' => $_ENV['DB_DRIVER'] ?? 'pdo',
    'dbprefix' => $_ENV['DB_PREFIX'] ?? '',
    'subdriver' => 'mysql',
    'pconnect' => false,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => false,
    'cachedir' => '',
    // utf8mb4, e não utf8: no MySQL 8 `utf8` é apelido de utf8mb3, que não
    // tem os 4 bytes do emoji. Este par de valores tem duas consequências, e as
    // duas importam. O mysql_forge::_create_table_attr() acrescenta
    // `DEFAULT CHARACTER SET = char_set COLLATE = dbcollat` em toda tabela criada
    // por dbforge, então o valor daqui é o charset de toda tabela que a cadeia de
    // migrations constrói. E o DB_driver::_initialize() chama
    // db_set_charset($this->char_set), então o valor daqui também é o charset
    // negociado por toda query — mesmo com a tabela em utf8mb4, um caractere de
    // 4 bytes não sobrevive à conexão se ela ainda é utf8mb3.
    'char_set' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    'dbcollat' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_general_ci',
    'swap_pre' => '',
    'encrypt' => false,
    'compress' => false,
    'stricton' => false,
    'failover' => [],
    'save_queries' => true,
];
