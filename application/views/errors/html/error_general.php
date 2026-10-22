<?php
defined('BASEPATH') or exit('No direct script access allowed');

// show_error(): erros 4xx trazem uma mensagem pensada para quem a lê; do 500
// em diante o texto pode revelar o sistema, e só aparece em development.
$codigo = (string) ($status_code ?? 500);
$mensagemTecnica = trim(html_entity_decode(strip_tags(str_ireplace(['</p>', '<br>', '<br />', '<br/>'], "\n", (string) ($message ?? ''))), ENT_QUOTES, 'UTF-8'));

if (($status_code ?? 500) >= 500) {
    $titulo = 'Algo deu errado';
    $texto = 'Não foi possível concluir o que você pediu. Tente de novo em alguns instantes; se continuar, avise o administrador do sistema.';
    $detalhes = $mensagemTecnica;
} else {
    $titulo = trim(strip_tags((string) ($heading ?? ''))) ?: 'Não foi possível continuar';
    $texto = $mensagemTecnica !== '' ? $mensagemTecnica : 'Confira o endereço ou volte ao início.';
    $detalhes = null;
}

include __DIR__ . '/_pagina.php';
