<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Configuração do ambiente de testes
|--------------------------------------------------------------------------
|
| Este arquivo é carregado DEPOIS de application/config/config.php, com o mesmo
| array $config (ver Common.php::_ci_load_class), então sobrescrever uma chave
| aqui vale para o resto da requisição.
|
| Existe para tirar a suíte do caminho de código-fonte. O config.php deixa
| log_path vazio, o que faz o CI_Log gravar em application/logs/ — dentro da
| árvore que o php-cs-fixer percorre. A suíte escreve log de verdade (o
| log_message() do Security, o log_info() do Login), então uma execução gerava
| application/logs/log-<data>.php e o composer format:check falhava em cima de um
| arquivo gerado, obrigando a uma exceção no .php-cs-fixer.php.
|
| Mandar para o diretório temporário resolve na raiz, sem exceção: o log é saída
| de runtime e não tem por que viver no repositório.
|
*/

$config['log_path'] = rtrim(sys_get_temp_dir(), '/') . '/mapos-test-logs/';
