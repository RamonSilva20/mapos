<?php

/**
 * Ícones Lucide (#2915), servidos de um sprite SVG gerado por
 * scripts/build-vendor.mjs em assets/vendor/lucide/sprite.svg.
 *
 *     <?= icon('wrench') ?>
 *     <?= icon('trash-2', ['class' => 'size-4 text-danger-ink']) ?>
 *     <?= icon('x', ['label' => 'Fechar']) ?>
 *
 * - O traço segue o currentColor: a cor vem da classe de texto do elemento
 *   (ou da passada em `class`).
 * - O tamanho padrão é 20px (width/height do SVG); uma classe de tamanho,
 *   como size-4, sobrepõe.
 * - Sem `label` o ícone é decorativo (aria-hidden). Com `label` vira
 *   role="img" com aria-label, para ícone que é o único conteúdo de algo.
 *
 * Para um ícone novo: inclua o nome (https://lucide.dev/icons) em
 * assets/src/icones.json e rode `npm run build:vendor`.
 */
if (! defined('ICONE_SPRITE')) {
    define('ICONE_SPRITE', 'assets/vendor/lucide/sprite.svg');
}

if (! function_exists('iconesDisponiveis')) {
    /**
     * Nomes presentes no sprite (cópia de assets/src/icones.json gravada pelo
     * build, porque o assets/src não vai no pacote de instalação).
     *
     * @return list<string>
     */
    function iconesDisponiveis(): array
    {
        static $icones = null;

        if ($icones === null) {
            $raiz = defined('FCPATH') ? FCPATH : dirname(APPPATH) . DIRECTORY_SEPARATOR;
            $arquivo = $raiz . 'assets' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'lucide' . DIRECTORY_SEPARATOR . 'icones.json';
            $lista = is_file($arquivo) ? json_decode((string) file_get_contents($arquivo), true) : null;
            $icones = is_array($lista) ? array_values(array_map('strval', $lista)) : [];
        }

        return $icones;
    }
}

if (! function_exists('icon')) {
    /**
     * SVG de um ícone do sprite Lucide.
     *
     * @param  array{class?: string|list<string>, label?: string}  $opcoes
     *
     * @throws InvalidArgumentException  nome fora do sprite ou opção desconhecida
     */
    function icon(string $nome, array $opcoes = []): HtmlSeguro
    {
        if (! in_array($nome, iconesDisponiveis(), true)) {
            throw new InvalidArgumentException("Ícone desconhecido: {$nome}. Inclua-o em assets/src/icones.json e rode npm run build:vendor.");
        }

        $desconhecidas = array_diff(array_keys($opcoes), ['class', 'label']);
        if ($desconhecidas !== []) {
            throw new InvalidArgumentException('Opção de ícone desconhecida: ' . implode(', ', $desconhecidas));
        }

        $label = isset($opcoes['label']) ? trim((string) $opcoes['label']) : '';

        $atributos = componenteAtributos([
            'class' => ['shrink-0', $opcoes['class'] ?? ''],
            'width' => '20',
            'height' => '20',
            'fill' => 'none',
            'stroke' => 'currentColor',
            'stroke-width' => '2',
            'stroke-linecap' => 'round',
            'stroke-linejoin' => 'round',
            'focusable' => 'false',
            'role' => $label !== '' ? 'img' : null,
            'aria-label' => $label !== '' ? $label : null,
            'aria-hidden' => $label !== '' ? null : 'true',
        ]);

        $href = htmlspecialchars(base_url(ICONE_SPRITE) . '#' . $nome, ENT_QUOTES, 'UTF-8');

        return new HtmlSeguro('<svg' . $atributos . '><use href="' . $href . '"></use></svg>');
    }
}
