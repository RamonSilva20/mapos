<?php

if (! function_exists('e')) {
    /**
     * Escapa um valor para ser impresso em HTML.
     *
     * É o padrão de saída das views da v5: `<?= e($cliente->nome) ?>`. Delega
     * para o html_escape() do CodeIgniter, então o charset segue o config, mas
     * sempre devolve string: null e false viram '' em vez de passar adiante
     * como estão, o que o html_escape() faz com qualquer valor "vazio".
     *
     * Array não é aceito de propósito: imprimir um array numa view é bug, e o
     * TypeError aparece na hora em vez de um "Array" no HTML.
     *
     * HTML confiável não passa por aqui, e sim por printSafeHtml(), que filtra
     * com o HTMLPurifier em vez de escapar.
     */
    function e(string|int|float|bool|Stringable|null $valor): string
    {
        if ($valor === null || $valor === false) {
            return '';
        }

        return html_escape((string) $valor);
    }
}
