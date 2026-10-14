<?php
defined('BASEPATH') or exit('No direct script access allowed');

// A mensagem do banco traz o SQL e nomes de tabelas: só em development.
$codigo = '500';
$titulo = 'Banco de dados indisponível';
$texto = 'Não foi possível acessar os dados agora. Tente de novo em alguns instantes; se continuar, avise o administrador do sistema.';
$detalhes = trim(html_entity_decode(strip_tags(str_ireplace(['</p>', '<br>', '<br />', '<br/>'], "\n", (string) ($message ?? ''))), ENT_QUOTES, 'UTF-8'));

include __DIR__ . '/_pagina.php';
