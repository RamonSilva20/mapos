<?php

/*
 * Ponte entre as views e os módulos de assets/js/modules.
 *
 * O padrão da v5 é não escrever JavaScript dentro da view: a view declara qual
 * módulo usa (js_module) e entrega os dados que ele precisa (page_data ou
 * atributos data-*). O código fica em arquivo, o que permite uma CSP sem
 * 'unsafe-inline'.
 */

if (! function_exists('js_module')) {
    /**
     * Atributo que liga um elemento a um módulo de assets/js/modules.
     *
     *     <div <?= js_module('servicos/listagem') ?>> ... </div>
     *
     * O assets/js/app.js importa assets/js/modules/servicos/listagem.js e chama
     * o export default dele com o elemento. O nome só aceita minúsculas,
     * números, "-", "_" e "/" entre segmentos, então não dá para montar um
     * caminho para fora da pasta de módulos.
     */
    function js_module(string $nome): string
    {
        if (! preg_match('#^[a-z0-9_-]+(/[a-z0-9_-]+)*$#', $nome)) {
            throw new InvalidArgumentException("Nome de módulo JS inválido: {$nome}");
        }

        return 'data-module="' . $nome . '"';
    }
}

if (! function_exists('page_data')) {
    /**
     * Dados da página para os módulos, num <script type="application/json">.
     *
     *     <?= page_data('dados-servicos', ['porPagina' => 10]) ?>
     *
     * No JS: lerJson('dados-servicos'). Os flags JSON_HEX_* trocam < > & ' "
     * por \u00XX, então nenhum valor consegue fechar a tag (</script>) nem
     * abrir um comentário HTML. O bloco não é executado pelo navegador e não
     * precisa de 'unsafe-inline' na CSP.
     */
    function page_data(string $id, mixed $dados): string
    {
        $json = json_encode(
            $dados,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        return '<script type="application/json" id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">' . $json . '</script>';
    }
}
