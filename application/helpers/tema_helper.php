<?php

/**
 * Tema da v5: modo de cor (claro, escuro ou sistema).
 *
 * A v4 guardava uma única configuração, app_theme, que misturava modo e cor
 * em 7 temas fechados. A v5 guarda só o modo, em app_tema_modo: a cor de ação
 * é única, o laranja do DESIGN.md, e não há mais cor de destaque (#2913). O
 * CSS (assets/src/tokens.css) troca as variáveis pela classe .dark no <html>.
 *
 * Cada pessoa pode ter o próprio modo (usuarios.tema_modo, #2854), que vale mais
 * que o app_tema_modo, o tema padrão do sistema.
 *
 * O app_theme só é lido como origem do modo num banco que ainda não tem
 * app_tema_modo (instalado pelo banco.sql antes da v5). Nada aqui o altera e
 * as telas não o gravam mais: os tema-*.css saíram na #2855.
 */
if (! defined('TEMA_MODOS')) {
    define('TEMA_MODOS', ['claro', 'escuro', 'sistema']);
}

if (! defined('TEMA_PADRAO')) {
    define('TEMA_PADRAO', ['modo' => 'claro']);
}

if (! function_exists('temaDeAppTheme')) {
    /**
     * Converte um app_theme da v4 no modo de cor.
     *
     * Valor desconhecido, vazio ou null vira o padrão (claro), que é o que a
     * v4 mostra quando app_theme não casa com nenhum tema.
     *
     * @return array{modo: string}
     */
    function temaDeAppTheme($appTheme)
    {
        $mapa = [
            'default' => 'claro',
            'white' => 'claro',
            'puredark' => 'escuro',
            'darkorange' => 'escuro',
            'darkviolet' => 'escuro',
            'whitegreen' => 'claro',
            'whiteblack' => 'claro',
        ];

        $chave = strtolower(trim((string) $appTheme));

        return isset($mapa[$chave]) ? ['modo' => $mapa[$chave]] : TEMA_PADRAO;
    }
}

if (! function_exists('temaConfiguracao')) {
    /**
     * Resolve o tema a partir do array de configurações do MY_Controller.
     *
     * Usa app_tema_modo quando existe e é válido. Se faltar, o modo é derivado
     * de app_theme: é o caso de um banco instalado pelo banco.sql antes de a
     * migration rodar.
     *
     * @return array{modo: string}
     */
    function temaConfiguracao(array $configuration)
    {
        // Preferência da pessoa (#2854) vale mais que o tema padrão do sistema.
        $doUsuario = strtolower(trim((string) ($configuration['tema_usuario_modo'] ?? '')));
        if (in_array($doUsuario, TEMA_MODOS, true)) {
            return ['modo' => $doUsuario];
        }

        $modo = strtolower(trim((string) ($configuration['app_tema_modo'] ?? '')));

        if (in_array($modo, TEMA_MODOS, true)) {
            return ['modo' => $modo];
        }

        return temaDeAppTheme($configuration['app_theme'] ?? null);
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
     *   da primeira pintura.
     */
    function temaAtributosHtml(array $configuration)
    {
        $tema = temaConfiguracao($configuration);

        $atributos = '';
        if ($tema['modo'] === 'escuro') {
            $atributos .= ' class="dark"';
        }

        $atributos .= ' data-tema-modo="' . htmlspecialchars($tema['modo'], ENT_QUOTES, 'UTF-8') . '"';

        // Com preferência gravada para o usuário, ela vale em qualquer navegador:
        // o assets/js/tema.js não deixa o localStorage de um navegador mandar nela.
        if (in_array(strtolower(trim((string) ($configuration['tema_usuario_modo'] ?? ''))), TEMA_MODOS, true)) {
            $atributos .= ' data-tema-usuario="1"';
        }

        return $atributos;
    }
}
