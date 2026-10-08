<?php

/**
 * Tema da v5: modo (claro, escuro ou sistema) e cor de destaque.
 *
 * A v4 guardava uma única configuração, app_theme, que misturava as duas
 * coisas em 7 temas fechados. A v5 separa as duas escolhas em app_tema_modo e
 * app_tema_destaque, e o CSS (assets/src/app.css) troca só variáveis: a classe
 * .dark no <html> muda as cores de base, e o atributo data-accent muda a cor
 * de destaque.
 *
 * Enquanto as telas legadas existirem (até a #2855), app_theme continua sendo
 * a fonte dos tema-*.css. Por isso nada aqui altera app_theme.
 */
if (! defined('TEMA_MODOS')) {
    define('TEMA_MODOS', ['claro', 'escuro', 'sistema']);
}

if (! defined('TEMA_DESTAQUES')) {
    define('TEMA_DESTAQUES', ['laranja', 'azul', 'violeta', 'verde', 'grafite']);
}

if (! defined('TEMA_PADRAO')) {
    define('TEMA_PADRAO', ['modo' => 'claro', 'destaque' => 'laranja']);
}

if (! function_exists('temaDeAppTheme')) {
    /**
     * Converte um app_theme da v4 em modo e destaque.
     *
     * Valor desconhecido, vazio ou null vira o padrão (claro + laranja), que é
     * o que a v4 mostra quando app_theme não casa com nenhum tema.
     *
     * @return array{modo: string, destaque: string}
     */
    function temaDeAppTheme($appTheme)
    {
        $mapa = [
            'default' => ['modo' => 'claro', 'destaque' => 'laranja'],
            'white' => ['modo' => 'claro', 'destaque' => 'laranja'],
            'puredark' => ['modo' => 'escuro', 'destaque' => 'azul'],
            'darkorange' => ['modo' => 'escuro', 'destaque' => 'laranja'],
            'darkviolet' => ['modo' => 'escuro', 'destaque' => 'violeta'],
            'whitegreen' => ['modo' => 'claro', 'destaque' => 'verde'],
            'whiteblack' => ['modo' => 'claro', 'destaque' => 'grafite'],
        ];

        $chave = strtolower(trim((string) $appTheme));

        return $mapa[$chave] ?? TEMA_PADRAO;
    }
}

if (! function_exists('temaConfiguracao')) {
    /**
     * Resolve o tema a partir do array de configurações do MY_Controller.
     *
     * Usa app_tema_modo e app_tema_destaque quando existem e são válidos. Cada
     * um que faltar é derivado de app_theme: é o caso de um banco instalado
     * pelo banco.sql antes de a migration rodar.
     *
     * @return array{modo: string, destaque: string}
     */
    function temaConfiguracao(array $configuration)
    {
        $derivado = temaDeAppTheme($configuration['app_theme'] ?? null);

        $modo = strtolower(trim((string) ($configuration['app_tema_modo'] ?? '')));
        $destaque = strtolower(trim((string) ($configuration['app_tema_destaque'] ?? '')));

        return [
            'modo' => in_array($modo, TEMA_MODOS, true) ? $modo : $derivado['modo'],
            'destaque' => in_array($destaque, TEMA_DESTAQUES, true) ? $destaque : $derivado['destaque'],
        ];
    }
}

if (! function_exists('temaAtributosHtml')) {
    /**
     * Atributos do <html> do layout novo, já escapados.
     *
     *     <html lang="pt-br"<?= temaAtributosHtml($configuration) ?>>
     *
     * - modo escuro: class="dark", aplicada no servidor, sem flash na carga;
     * - modo sistema: sem a classe; o assets/js/tema.js, carregado no <head>,
     *   lê data-tema-modo e aplica .dark conforme prefers-color-scheme antes
     *   da primeira pintura;
     * - data-accent: cor de destaque, que o app.css usa para trocar as
     *   variáveis --accent.
     */
    function temaAtributosHtml(array $configuration)
    {
        $tema = temaConfiguracao($configuration);

        $atributos = '';
        if ($tema['modo'] === 'escuro') {
            $atributos .= ' class="dark"';
        }

        $atributos .= ' data-tema-modo="' . htmlspecialchars($tema['modo'], ENT_QUOTES, 'UTF-8') . '"';
        $atributos .= ' data-accent="' . htmlspecialchars($tema['destaque'], ENT_QUOTES, 'UTF-8') . '"';

        return $atributos;
    }
}
