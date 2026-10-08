<?php

/*
 * Biblioteca de componentes da v5 (application/views/components/).
 *
 *     <?= component('button', ['label' => 'Salvar', 'type' => 'submit']) ?>
 *
 * component() valida as props contra a especificação abaixo, completa os
 * padrões e renderiza o partial. O resultado é um HtmlSeguro: um objeto
 * Stringable que marca o HTML como produzido por um componente.
 *
 * Contrato de escape
 * ------------------
 * - Todo texto que entra num componente é escapado com e(). String nunca vira
 *   HTML, nem nos "slots" (body, footer, actions, message, células de tabela).
 * - Um slot só recebe HTML quando o valor é um HtmlSeguro: a saída de outro
 *   component(), ou de html_purificado(), que passa o HTML pelo HTMLPurifier.
 * - Atributos extras (prop attrs) passam por componenteAtributos(), que
 *   escapa nome e valor, recusa nomes inválidos e handlers de evento (on*), e
 *   neutraliza URLs javascript:, data: e vbscript: em href/src/action.
 *
 * O risco que sobra é alguém construir um HtmlSeguro à mão com conteúdo do
 * usuário. Não faça isso: o construtor existe para os componentes e para
 * html_purificado().
 *
 * Por que não $this->load->view()
 * --------------------------------
 * O partial é incluído por uma closure estática com as props extraídas, que é
 * o mesmo que o load->view() faz por dentro, mas sem $this e sem depender da
 * instância do CodeIgniter. O componente fica isolado do controller (só vê as
 * props) e pode ser testado sem subir o framework.
 */

if (! class_exists('HtmlSeguro', false)) {
    /**
     * HTML produzido por um componente ou purificado, que pode ser impresso
     * sem escape. Ver o contrato no topo deste arquivo.
     */
    final class HtmlSeguro implements Stringable
    {
        public function __construct(private string $html)
        {
        }

        public function __toString(): string
        {
            return $this->html;
        }
    }
}

if (! defined('COMPONENTE_ICONE')) {
    // Nome de ícone Lucide do sprite (icone_helper.php): plus, trash-2, circle-check.
    define('COMPONENTE_ICONE', '/^[a-z0-9]+(-[a-z0-9]+)*$/');
}

if (! function_exists('componenteEspecificacoes')) {
    /**
     * Props aceitas por componente.
     *
     * - obrigatorias: não podem faltar nem vir vazias;
     * - padrao: props opcionais e seus valores padrão;
     * - opcoes: valores permitidos de uma prop;
     * - formatos: regex que o valor (quando informado) tem de casar.
     *
     * Todo componente aceita também id, class (classes extras) e attrs
     * (atributos extras, ver componenteAtributos()). Prop desconhecida é erro,
     * para que um nome digitado errado não seja ignorado em silêncio.
     *
     * @return array<string, array{obrigatorias: list<string>, padrao: array<string, mixed>, opcoes?: array<string, list<string>>, formatos?: array<string, string>}>
     */
    function componenteEspecificacoes(): array
    {
        $estados = ['info', 'success', 'warning', 'danger'];

        return [
            'button' => [
                'obrigatorias' => ['label'],
                'padrao' => [
                    'variant' => 'primary',
                    'size' => 'md',
                    'type' => 'button',
                    'href' => null,
                    'icon' => null,
                    'icon_only' => false,
                    'disabled' => false,
                    'name' => null,
                    'value' => null,
                ],
                'opcoes' => [
                    'variant' => ['primary', 'secondary', 'ghost', 'danger', 'link'],
                    'size' => ['sm', 'md', 'lg'],
                    'type' => ['button', 'submit', 'reset'],
                ],
                'formatos' => ['icon' => COMPONENTE_ICONE],
            ],
            'input' => [
                'obrigatorias' => ['name', 'label'],
                'padrao' => [
                    'type' => 'text',
                    'value' => null,
                    'placeholder' => null,
                    'help' => null,
                    'error' => null,
                    'required' => false,
                    'disabled' => false,
                    'readonly' => false,
                    'autocomplete' => null,
                    'hide_label' => false,
                ],
                'opcoes' => [
                    'type' => ['text', 'email', 'password', 'number', 'date', 'datetime-local', 'time', 'tel', 'url', 'search'],
                ],
            ],
            'select' => [
                'obrigatorias' => ['name', 'label'],
                'padrao' => [
                    'options' => [],
                    'selected' => null,
                    'placeholder' => null,
                    'multiple' => false,
                    'help' => null,
                    'error' => null,
                    'required' => false,
                    'disabled' => false,
                    'hide_label' => false,
                ],
            ],
            'textarea' => [
                'obrigatorias' => ['name', 'label'],
                'padrao' => [
                    'value' => null,
                    'rows' => 4,
                    'placeholder' => null,
                    'help' => null,
                    'error' => null,
                    'required' => false,
                    'disabled' => false,
                    'readonly' => false,
                    'hide_label' => false,
                ],
            ],
            'card' => [
                'obrigatorias' => [],
                'padrao' => [
                    'title' => null,
                    'subtitle' => null,
                    'body' => null,
                    'footer' => null,
                    'actions' => null,
                    'padded' => true,
                ],
            ],
            'table' => [
                'obrigatorias' => ['columns'],
                'padrao' => [
                    'rows' => [],
                    'empty' => 'Nenhum registro encontrado.',
                    'caption' => null,
                    'striped' => false,
                    'dense' => false,
                ],
            ],
            'modal' => [
                'obrigatorias' => ['id', 'title'],
                'padrao' => [
                    'body' => null,
                    'footer' => null,
                    'size' => 'md',
                    'close_label' => 'Fechar',
                    'open' => false,
                ],
                'opcoes' => ['size' => ['sm', 'md', 'lg', 'xl']],
            ],
            'alert' => [
                'obrigatorias' => ['message'],
                'padrao' => [
                    'variant' => 'info',
                    'title' => null,
                    'dismissible' => false,
                    'dismiss_label' => 'Fechar aviso',
                ],
                'opcoes' => ['variant' => $estados],
            ],
            'toast' => [
                'obrigatorias' => ['message'],
                'padrao' => [
                    'variant' => 'info',
                    'title' => null,
                    'duration' => 5000,
                    'dismissible' => true,
                    'dismiss_label' => 'Fechar notificação',
                ],
                'opcoes' => ['variant' => $estados],
            ],
            'badge' => [
                'obrigatorias' => ['label'],
                'padrao' => [
                    'variant' => 'neutral',
                    'size' => 'md',
                ],
                'opcoes' => [
                    'variant' => ['neutral', 'accent', 'info', 'success', 'warning', 'danger'],
                    'size' => ['sm', 'md'],
                ],
            ],
            'pagination' => [
                'obrigatorias' => ['total_pages', 'url'],
                'padrao' => [
                    'current' => 1,
                    'per_page' => null,
                    'window' => 1,
                    'label' => 'Paginação',
                    'prev_label' => 'Anterior',
                    'next_label' => 'Próxima',
                ],
            ],
            'empty-state' => [
                'obrigatorias' => ['title'],
                'padrao' => [
                    'message' => null,
                    'icon' => 'folder-open',
                    'action' => null,
                ],
                'formatos' => ['icon' => COMPONENTE_ICONE],
            ],
            'breadcrumb' => [
                'obrigatorias' => ['items'],
                'padrao' => [
                    'label' => 'Você está em',
                ],
            ],
        ];
    }
}

if (! function_exists('component')) {
    /**
     * Renderiza application/views/components/<nome>.php com as props.
     *
     * @throws InvalidArgumentException componente desconhecido, prop
     *                                  obrigatória faltando, prop desconhecida
     *                                  ou valor fora das opções
     */
    function component(string $nome, array $props = []): HtmlSeguro
    {
        $especificacoes = componenteEspecificacoes();

        if (! isset($especificacoes[$nome])) {
            throw new InvalidArgumentException("Componente desconhecido: {$nome}");
        }

        $spec = $especificacoes[$nome];
        $padrao = $spec['padrao'] + ['id' => null, 'class' => '', 'attrs' => []];

        foreach (array_keys($props) as $prop) {
            if (! array_key_exists($prop, $padrao) && ! in_array($prop, $spec['obrigatorias'], true)) {
                throw new InvalidArgumentException("Prop desconhecida em {$nome}: {$prop}");
            }
        }

        foreach ($spec['obrigatorias'] as $prop) {
            if (! array_key_exists($prop, $props) || $props[$prop] === null || $props[$prop] === '' || $props[$prop] === []) {
                throw new InvalidArgumentException("Prop obrigatória faltando em {$nome}: {$prop}");
            }
        }

        $props += $padrao;

        foreach ($spec['opcoes'] ?? [] as $prop => $opcoes) {
            if (! in_array($props[$prop], $opcoes, true)) {
                $valor = is_scalar($props[$prop]) ? (string) $props[$prop] : gettype($props[$prop]);

                throw new InvalidArgumentException("Valor inválido para {$nome}.{$prop}: {$valor} (aceitos: " . implode(', ', $opcoes) . ')');
            }
        }

        foreach ($spec['formatos'] ?? [] as $prop => $regex) {
            if ($props[$prop] !== null && ! preg_match($regex, (string) $props[$prop])) {
                throw new InvalidArgumentException("Formato inválido para {$nome}.{$prop}");
            }
        }

        if (! is_array($props['attrs'])) {
            throw new InvalidArgumentException("A prop attrs de {$nome} deve ser um array.");
        }

        $arquivo = APPPATH . 'views' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $nome . '.php';

        $renderizar = static function (string $__arquivo, array $__props): string {
            extract($__props, EXTR_SKIP);
            ob_start();

            try {
                include $__arquivo;
            } catch (Throwable $erro) {
                ob_end_clean();

                throw $erro;
            }

            return (string) ob_get_clean();
        };

        return new HtmlSeguro(trim($renderizar($arquivo, $props)));
    }
}

if (! function_exists('componenteAtributos')) {
    /**
     * Monta atributos HTML escapados, com espaço à esquerda.
     *
     *     componenteAtributos(['id' => 'x', 'disabled' => true, 'title' => null])
     *     // ' id="x" disabled'
     *
     * true vira atributo sem valor, false e null somem, array vira lista
     * separada por espaço (para class). Nome fora de [a-zA-Z_:][-a-zA-Z0-9_:.]*
     * e handler de evento (onclick, onload...) são recusados: JavaScript fica
     * em assets/js/modules (#2838). Em href, src, action e formaction, URL com
     * esquema javascript:, data: ou vbscript: vira "#".
     */
    function componenteAtributos(array $atributos): string
    {
        $saida = '';

        foreach ($atributos as $nome => $valor) {
            $nome = (string) $nome;

            if (! preg_match('/^[a-zA-Z_:][-a-zA-Z0-9_:.]*$/', $nome)) {
                throw new InvalidArgumentException("Nome de atributo inválido: {$nome}");
            }

            if (stripos($nome, 'on') === 0) {
                throw new InvalidArgumentException("Atributo de evento não é permitido: {$nome}. Use um módulo de assets/js/modules.");
            }

            if ($valor === null || $valor === false) {
                continue;
            }

            if ($valor === true) {
                $saida .= ' ' . $nome;

                continue;
            }

            if (is_array($valor)) {
                $valor = implode(' ', array_filter(array_map('strval', $valor), static fn (string $item): bool => $item !== ''));
            }

            if (in_array(strtolower($nome), ['href', 'src', 'action', 'formaction'], true)) {
                $valor = componenteUrl((string) $valor);
            }

            $saida .= ' ' . $nome . '="' . e($valor) . '"';
        }

        return $saida;
    }
}

if (! function_exists('componenteMesclarAtributos')) {
    /**
     * Junta os atributos do componente com os extras da prop attrs. Os extras
     * substituem os do componente, menos class, que é somada.
     */
    function componenteMesclarAtributos(array $doComponente, array $extras): array
    {
        if (isset($extras['class'])) {
            $doComponente['class'] = componenteClasses($doComponente['class'] ?? '', $extras['class']);
            unset($extras['class']);
        }

        return array_merge($doComponente, $extras);
    }
}

if (! function_exists('componenteUrl')) {
    /**
     * Neutraliza URLs com esquema executável. Não escapa: o escape acontece
     * na saída do atributo.
     */
    function componenteUrl(?string $url): string
    {
        $url = (string) $url;

        // O navegador ignora espaços e caracteres de controle no esquema:
        // "java\tscript:" ainda executa.
        $limpa = strtolower((string) preg_replace('/[\x00-\x20]+/', '', $url));

        if (preg_match('/^(javascript|data|vbscript):/', $limpa)) {
            return '#';
        }

        return $url;
    }
}

if (! function_exists('componenteClasses')) {
    /**
     * Junta classes CSS. Aceita strings e arrays ['classe' => condição].
     */
    function componenteClasses(...$partes): string
    {
        $classes = [];

        foreach ($partes as $parte) {
            if (is_array($parte)) {
                foreach ($parte as $classe => $ligada) {
                    if (is_int($classe)) {
                        $classes[] = (string) $ligada;
                    } elseif ($ligada) {
                        $classes[] = $classe;
                    }
                }
            } elseif ($parte !== null && $parte !== false) {
                $classes[] = (string) $parte;
            }
        }

        $lista = preg_split('/\s+/', implode(' ', $classes), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return implode(' ', array_values(array_unique($lista)));
    }
}

if (! function_exists('componenteConteudo')) {
    /**
     * Imprime um slot: texto é escapado, HtmlSeguro passa como está e lista
     * é concatenada item a item.
     */
    function componenteConteudo($valor): string
    {
        if ($valor === null || $valor === false) {
            return '';
        }

        if ($valor instanceof HtmlSeguro) {
            return (string) $valor;
        }

        if (is_array($valor)) {
            return implode('', array_map('componenteConteudo', $valor));
        }

        if (is_scalar($valor) || $valor instanceof Stringable) {
            return e($valor);
        }

        throw new InvalidArgumentException('Conteúdo de slot inválido: ' . gettype($valor));
    }
}

if (! function_exists('componenteId')) {
    /**
     * id derivado do name de um campo: "cliente[nome]" vira "campo-cliente-nome".
     */
    function componenteId(string $nome): string
    {
        $base = trim((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $nome), '-');

        return 'campo-' . ($base !== '' ? $base : 'sem-nome');
    }
}

if (! function_exists('componentePaginas')) {
    /**
     * Páginas exibidas na paginação: a primeira, a última e uma janela em
     * volta da atual. null marca um salto (reticências).
     *
     *     componentePaginas(6, 20, 1) // [1, null, 5, 6, 7, null, 20]
     *
     * @return list<int|null>
     */
    function componentePaginas(int $atual, int $total, int $janela = 1): array
    {
        if ($total < 1) {
            return [];
        }

        $janela = max(0, $janela);
        $atual = min(max(1, $atual), $total);

        $numeros = [1, $total];
        for ($n = $atual - $janela; $n <= $atual + $janela; $n++) {
            if ($n >= 1 && $n <= $total) {
                $numeros[] = $n;
            }
        }

        $numeros = array_values(array_unique($numeros));
        sort($numeros);

        $paginas = [];
        $anterior = 0;
        foreach ($numeros as $n) {
            if ($n - $anterior === 2) {
                // Um só número escondido: mostra o número em vez de "…".
                $paginas[] = $n - 1;
            } elseif ($n - $anterior > 2) {
                $paginas[] = null;
            }
            $paginas[] = $n;
            $anterior = $n;
        }

        return $paginas;
    }
}

if (! function_exists('componentePaginaUrl')) {
    /**
     * URL de uma página. {page} vira o número da página e {offset} vira o
     * deslocamento (página - 1) × per_page, que é o que os models do Map-OS
     * recebem hoje (o mesmo valor que o CI_Pagination põe na URL).
     */
    function componentePaginaUrl(string $modelo, int $pagina, ?int $porPagina = null): string
    {
        if (str_contains($modelo, '{offset}')) {
            if ($porPagina === null || $porPagina < 1) {
                throw new InvalidArgumentException('A URL usa {offset}, então a paginação precisa de per_page.');
            }

            $modelo = str_replace('{offset}', (string) (($pagina - 1) * $porPagina), $modelo);
        }

        return str_replace('{page}', (string) $pagina, $modelo);
    }
}

if (! function_exists('paginacaoProps')) {
    /**
     * Monta as props do componente pagination a partir dos números que as
     * listagens já usam com o CI_Pagination.
     *
     * Opções:
     * - base_url (obrigatória): URL da listagem, sem o offset;
     * - total_rows (obrigatória): total de registros;
     * - per_page: itens por página (padrão 10);
     * - offset: deslocamento atual, como veio da URL. Valor não numérico ou
     *   negativo vira 0; além do fim, cai na última página;
     * - query_string: nome do parâmetro quando o offset vai na query string
     *   (ex. 'per_page', o padrão do page_query_string do CI). Sem ele, o
     *   offset vai como último segmento da URL.
     *
     * @return array{total_pages: int, current: int, url: string, per_page: int}
     */
    function paginacaoProps(array $opcoes): array
    {
        if (! isset($opcoes['base_url']) || ! is_string($opcoes['base_url']) || $opcoes['base_url'] === '') {
            throw new InvalidArgumentException('paginacaoProps precisa de base_url.');
        }

        $porPagina = (int) ($opcoes['per_page'] ?? 10);
        if ($porPagina < 1) {
            throw new InvalidArgumentException('per_page da paginação deve ser maior que zero.');
        }

        $total = max(0, (int) ($opcoes['total_rows'] ?? 0));
        $totalPaginas = (int) ceil($total / $porPagina);

        $offset = $opcoes['offset'] ?? 0;
        $offset = is_numeric($offset) ? max(0, (int) $offset) : 0;
        $atual = min(intdiv($offset, $porPagina) + 1, max(1, $totalPaginas));

        $base = $opcoes['base_url'];
        $parametro = $opcoes['query_string'] ?? null;

        if ($parametro !== null && $parametro !== '') {
            if (! preg_match('/^[A-Za-z0-9_-]+$/', (string) $parametro)) {
                throw new InvalidArgumentException('Nome de parâmetro da paginação inválido.');
            }
            $separador = str_contains($base, '?') ? (preg_match('/[?&]$/', $base) ? '' : '&') : '?';
            $url = $base . $separador . $parametro . '={offset}';
        } else {
            $url = rtrim($base, '/') . '/{offset}';
        }

        return [
            'total_pages' => $totalPaginas,
            'current' => $atual,
            'url' => $url,
            'per_page' => $porPagina,
        ];
    }
}

if (! function_exists('html_purificado')) {
    /**
     * HTML de origem não confiável (ex.: descrição com formatação) filtrado
     * pelo HTMLPurifier, pronto para ir num slot de componente.
     */
    function html_purificado(string $html): HtmlSeguro
    {
        return new HtmlSeguro(printSafeHtml($html));
    }
}
