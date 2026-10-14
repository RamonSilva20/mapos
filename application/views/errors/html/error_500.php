<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Exceção não tratada em produção (ver application/hooks/pagina_de_erro.php).
$codigo = '500';
$titulo = 'Algo deu errado';
$texto = 'Não foi possível concluir o que você pediu. O erro foi registrado: tente de novo em alguns instantes e, se continuar, avise o administrador do sistema.';
$detalhes = isset($exception) && $exception instanceof Throwable
    ? get_class($exception) . ': ' . $exception->getMessage() . "\n" . $exception->getFile() . ':' . $exception->getLine()
    : null;

include __DIR__ . '/_pagina.php';
