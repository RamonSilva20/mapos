<?php
defined('INSTALADOR') || exit;

/**
 * Ícones do instalador (Lucide, ISC), embutidos em SVG: o instalador não
 * carrega o CMS nem fontes de ícones.
 */
function instalador_icone(string $nome, string $classes = 'size-5'): string
{
    $caminhos = [
        'circulo' => '<circle cx="12" cy="12" r="10" />',
        'circulo-ok' => '<circle cx="12" cy="12" r="10" /><path d="m16 9-5.5 5.5L8 12" />',
        'circulo-erro' => '<circle cx="12" cy="12" r="10" /><path d="m15 9-6 6" /><path d="m9 9 6 6" />',
        'seta' => '<path d="m9 18 6-6-6-6" />',
        'monitor' => '<rect width="20" height="14" x="2" y="3" rx="2" /><path d="M8 21h8" /><path d="M12 17v4" />',
    ];

    return '<svg class="' . htmlspecialchars($classes, ENT_QUOTES) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $caminhos[$nome] . '</svg>';
}

/** Ícone de requisito atendido ou não, com o texto para leitores de tela. */
function instalador_status(bool $ok): string
{
    return $ok
        ? '<span class="inline-flex text-success">' . instalador_icone('circulo-ok') . '<span class="sr-only">Atende</span></span>'
        : '<span class="inline-flex text-danger">' . instalador_icone('circulo-erro') . '<span class="sr-only">Não atende</span></span>';
}
