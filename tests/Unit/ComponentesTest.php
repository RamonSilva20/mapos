<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Biblioteca de componentes (application/views/components/ e
 * application/helpers/componente_helper.php).
 *
 * O foco é o contrato de segurança: todo valor que entra num componente sai
 * escapado, e só HtmlSeguro passa como HTML.
 */
final class ComponentesTest extends MaposTestCase
{
    private const MALICIOSO = '<script>alert("x")</script>';

    private function html(string $nome, array $props = []): string
    {
        return (string) component($nome, $props);
    }

    private function assertSemScriptCru(string $html): void
    {
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    /**
     * Um exemplo mínimo válido de cada componente.
     */
    public static function exemplos(): array
    {
        return [
            'button' => ['button', ['label' => 'Salvar']],
            'input' => ['input', ['name' => 'nome', 'label' => 'Nome']],
            'select' => ['select', ['name' => 'tipo', 'label' => 'Tipo', 'options' => ['a' => 'A']]],
            'textarea' => ['textarea', ['name' => 'obs', 'label' => 'Observações']],
            'card' => ['card', ['title' => 'Título', 'body' => 'Corpo']],
            'table' => ['table', ['columns' => ['nome' => 'Nome'], 'rows' => [['nome' => 'Maria']]]],
            'modal' => ['modal', ['id' => 'm1', 'title' => 'Título']],
            'alert' => ['alert', ['message' => 'Aviso']],
            'toast' => ['toast', ['message' => 'Feito']],
            'badge' => ['badge', ['label' => 'Ativo']],
            'pagination' => ['pagination', ['total_pages' => 3, 'url' => '/x/{page}']],
            'empty-state' => ['empty-state', ['title' => 'Nada aqui']],
            'breadcrumb' => ['breadcrumb', ['items' => [['label' => 'Início', 'url' => '/'], ['label' => 'Atual']]]],
        ];
    }

    #[DataProvider('exemplos')]
    public function testCadaComponenteRenderiza(string $nome, array $props): void
    {
        $html = $this->html($nome, $props);

        $this->assertNotSame('', $html);
        $this->assertStringNotContainsString('Warning', $html);
    }

    public function testTodoComponenteEspecificadoTemPartialEViceVersa(): void
    {
        $partials = array_map(
            static fn ($arquivo) => basename($arquivo, '.php'),
            glob(APPPATH . 'views/components/*.php')
        );
        sort($partials);
        $especificados = array_keys(componenteEspecificacoes());
        sort($especificados);

        $this->assertSame($especificados, $partials);
        $this->assertCount(13, $especificados);
    }

    // ------------------------------------------------------------ validação

    public function testComponenteDesconhecido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Componente desconhecido');
        component('../tema/topo');
    }

    #[DataProvider('exemplos')]
    public function testPropObrigatoriaFaltando(string $nome, array $props): void
    {
        $obrigatorias = componenteEspecificacoes()[$nome]['obrigatorias'];
        if ($obrigatorias === []) {
            $this->assertSame([], $obrigatorias);

            return;
        }

        unset($props[$obrigatorias[0]]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Prop obrigatória faltando');
        component($nome, $props);
    }

    public function testPropObrigatoriaVazia(): void
    {
        $this->expectException(InvalidArgumentException::class);
        component('button', ['label' => '']);
    }

    public function testPropDesconhecidaEhErro(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Prop desconhecida em button: lable');
        component('button', ['label' => 'x', 'lable' => 'y']);
    }

    public function testValorForaDasOpcoes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Valor inválido para button.variant');
        component('button', ['label' => 'x', 'variant' => 'gigante']);
    }

    public function testIconeComFormatoInvalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        component('button', ['label' => 'x', 'icon' => 'plus" onmouseover="alert(1)']);
    }

    public function testAttrsPrecisaSerArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        component('badge', ['label' => 'x', 'attrs' => 'onclick=alert(1)']);
    }

    // ------------------------------------------------------------ atributos

    public function testAtributosEscapadosEBooleanos(): void
    {
        $saida = componenteAtributos([
            'title' => '"><script>',
            'disabled' => true,
            'hidden' => false,
            'data-x' => null,
            'class' => ['a', '', 'b'],
        ]);

        $this->assertSame(' title="&quot;&gt;&lt;script&gt;" disabled class="a b"', $saida);
    }

    public static function nomesRecusados(): array
    {
        return [
            'handler' => ['onclick'],
            'handler maiusculo' => ['OnMouseOver'],
            'aspas no nome' => ['x"y'],
            'espaco' => ['data x'],
            'maior que' => ['a>b'],
        ];
    }

    #[DataProvider('nomesRecusados')]
    public function testNomeDeAtributoRecusado(string $nome): void
    {
        $this->expectException(InvalidArgumentException::class);
        componenteAtributos([$nome => 'x']);
    }

    public static function urlsPerigosas(): array
    {
        return [
            ['javascript:alert(1)'],
            [' JavaScript:alert(1)'],
            ["java\tscript:alert(1)"],
            ["\x01javascript:alert(1)"],
            ['data:text/html,<script>alert(1)</script>'],
            ['vbscript:msgbox(1)'],
        ];
    }

    #[DataProvider('urlsPerigosas')]
    public function testUrlPerigosaViraCerquilha(string $url): void
    {
        $this->assertSame('#', componenteUrl($url));
        $this->assertSame(' href="#"', componenteAtributos(['href' => $url]));
    }

    public function testUrlNormalPassaEscapada(): void
    {
        $this->assertSame(' href="/clientes?a=1&amp;b=2"', componenteAtributos(['href' => '/clientes?a=1&b=2']));
        $this->assertSame('https://exemplo.com/javascript:x', componenteUrl('https://exemplo.com/javascript:x'));
    }

    public function testMesclarSomaClassesESubstituiOResto(): void
    {
        $this->assertSame(
            ['class' => 'a b c', 'type' => 'submit', 'data-x' => '1'],
            componenteMesclarAtributos(['class' => 'a b', 'type' => 'button'], ['class' => 'c', 'type' => 'submit', 'data-x' => '1'])
        );
    }

    public function testConteudoDeSlot(): void
    {
        $this->assertSame('&lt;b&gt;', componenteConteudo('<b>'));
        $this->assertSame('<b>ok</b>', componenteConteudo(new HtmlSeguro('<b>ok</b>')));
        $this->assertSame('&lt;i&gt;<b>ok</b>', componenteConteudo(['<i>', new HtmlSeguro('<b>ok</b>')]));
        $this->assertSame('', componenteConteudo(null));
        $this->assertSame('42', componenteConteudo(42));

        $this->expectException(InvalidArgumentException::class);
        componenteConteudo(new stdClass());
    }

    public function testHtmlPurificadoRemoveScript(): void
    {
        $html = (string) html_purificado('<p>Olá <strong>mundo</strong><script>alert(1)</script><a href="javascript:alert(1)">x</a></p>');

        $this->assertStringContainsString('<strong>mundo</strong>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function testComponenteId(): void
    {
        $this->assertSame('campo-cliente-nome', componenteId('cliente[nome]'));
        $this->assertSame('campo-sem-nome', componenteId('[]'));
    }

    // ------------------------------------------------------------ escape por componente

    public static function valoresMaliciosos(): array
    {
        $m = self::MALICIOSO;

        return [
            'button label' => ['button', ['label' => $m]],
            'input label' => ['input', ['name' => 'n', 'label' => $m]],
            'input help' => ['input', ['name' => 'n', 'label' => 'L', 'help' => $m]],
            'input error' => ['input', ['name' => 'n', 'label' => 'L', 'error' => $m]],
            'select opção' => ['select', ['name' => 'n', 'label' => 'L', 'options' => ['a' => $m]]],
            'select placeholder' => ['select', ['name' => 'n', 'label' => 'L', 'placeholder' => $m]],
            'textarea valor' => ['textarea', ['name' => 'n', 'label' => 'L', 'value' => '</textarea>' . $m]],
            'card título' => ['card', ['title' => $m]],
            'card corpo texto' => ['card', ['body' => $m]],
            'card rodapé' => ['card', ['footer' => $m]],
            'table célula' => ['table', ['columns' => ['n' => 'Nome'], 'rows' => [['n' => $m]]]],
            'table cabeçalho' => ['table', ['columns' => ['n' => $m], 'rows' => [['n' => 'x']]]],
            'table vazia' => ['table', ['columns' => ['n' => 'Nome'], 'empty' => $m]],
            'modal título' => ['modal', ['id' => 'm', 'title' => $m]],
            'modal corpo' => ['modal', ['id' => 'm', 'title' => 'T', 'body' => $m]],
            'alert mensagem' => ['alert', ['message' => $m]],
            'alert título' => ['alert', ['message' => 'x', 'title' => $m]],
            'toast mensagem' => ['toast', ['message' => $m]],
            'badge' => ['badge', ['label' => $m]],
            'empty-state título' => ['empty-state', ['title' => $m]],
            'empty-state mensagem' => ['empty-state', ['title' => 'T', 'message' => $m]],
            'breadcrumb' => ['breadcrumb', ['items' => [['label' => $m]]]],
            'pagination rótulo' => ['pagination', ['total_pages' => 3, 'url' => '/p/{page}', 'next_label' => $m]],
        ];
    }

    #[DataProvider('valoresMaliciosos')]
    public function testValorMaliciosoSaiEscapado(string $nome, array $props): void
    {
        $this->assertSemScriptCru($this->html($nome, $props));
    }

    public static function aspasEmAtributo(): array
    {
        $a = '" autofocus onfocus="alert(1)';

        return [
            'input value' => ['input', ['name' => 'n', 'label' => 'L', 'value' => $a]],
            'input placeholder' => ['input', ['name' => 'n', 'label' => 'L', 'placeholder' => $a]],
            'input name' => ['input', ['name' => $a, 'label' => 'L']],
            'select opção valor' => ['select', ['name' => 'n', 'label' => 'L', 'options' => [$a => 'x']]],
            'button value' => ['button', ['label' => 'x', 'value' => $a]],
            'button href' => ['button', ['label' => 'x', 'href' => '/a' . $a]],
            'attrs extra' => ['badge', ['label' => 'x', 'attrs' => ['title' => $a]]],
            'modal id' => ['modal', ['id' => $a, 'title' => 'T']],
            'pagination url' => ['pagination', ['total_pages' => 2, 'url' => '/p/{page}' . $a]],
            'breadcrumb url' => ['breadcrumb', ['items' => [['label' => 'a', 'url' => '/x' . $a], ['label' => 'b']]]],
        ];
    }

    #[DataProvider('aspasEmAtributo')]
    public function testAspasNaoFechamAtributo(string $nome, array $props): void
    {
        $html = $this->html($nome, $props);

        $this->assertStringNotContainsString('onfocus="', $html);
        $this->assertStringContainsString('&quot; autofocus onfocus=&quot;', $html);
    }

    // ------------------------------------------------------------ button

    public function testBotaoPadrao(): void
    {
        $html = $this->html('button', ['label' => 'Salvar']);

        $this->assertMatchesRegularExpression('#^<button type="button"#', preg_replace('/ (id|class)="[^"]*"/', '', $html));
        $this->assertStringContainsString('bg-primary text-on-primary', $html);
        $this->assertStringContainsString('focus-visible:outline-2', $html);
    }

    public function testBotaoVariantesETamanhos(): void
    {
        $this->assertStringContainsString('bg-danger', $this->html('button', ['label' => 'x', 'variant' => 'danger']));
        $this->assertStringContainsString('border-border bg-surface', $this->html('button', ['label' => 'x', 'variant' => 'secondary']));
        $this->assertStringContainsString('h-12', $this->html('button', ['label' => 'x', 'size' => 'lg']));
        $this->assertStringContainsString('type="submit"', $this->html('button', ['label' => 'x', 'type' => 'submit']));
    }

    public function testBotaoComHrefViraLink(): void
    {
        $html = $this->html('button', ['label' => 'Ir', 'href' => '/clientes']);

        $this->assertStringStartsWith('<a ', $html);
        $this->assertStringContainsString('href="/clientes"', $html);
        $this->assertStringNotContainsString('type=', $html);
    }

    public function testLinkDesabiladoNaoTemHref(): void
    {
        $html = $this->html('button', ['label' => 'Ir', 'href' => '/clientes', 'disabled' => true]);

        $this->assertStringNotContainsString('href=', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
    }

    public function testBotaoSoIconeMantemTextoAcessivel(): void
    {
        $html = $this->html('button', ['label' => 'Excluir', 'icon' => 'trash-2', 'icon_only' => true]);

        $this->assertStringContainsString('class="sr-only">Excluir</span>', $html);
        $this->assertStringContainsString('title="Excluir"', $html);
        $this->assertStringContainsString('sprite.svg#trash-2"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }

    public function testAttrsExtrasEClasseSomada(): void
    {
        $html = $this->html('button', ['label' => 'x', 'class' => 'w-full', 'attrs' => ['data-modal-abrir' => 'm1', 'class' => 'mt-2']]);

        $this->assertStringContainsString('data-modal-abrir="m1"', $html);
        $this->assertMatchesRegularExpression('/class="[^"]*w-full[^"]*mt-2"/', $html);
    }

    public function testHandlerDeEventoEmAttrsEhRecusado(): void
    {
        $this->expectException(InvalidArgumentException::class);
        component('button', ['label' => 'x', 'attrs' => ['onclick' => 'alert(1)']]);
    }

    // ------------------------------------------------------------ campos

    public function testInputLabelAssociadoEAria(): void
    {
        $html = $this->html('input', ['name' => 'cliente[email]', 'label' => 'E-mail', 'help' => 'Ajuda', 'error' => 'Inválido', 'required' => true]);

        $this->assertStringContainsString('<label for="campo-cliente-email"', $html);
        $this->assertStringContainsString('id="campo-cliente-email"', $html);
        $this->assertStringContainsString('aria-describedby="campo-cliente-email-ajuda campo-cliente-email-erro"', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('id="campo-cliente-email-erro"', $html);
        $this->assertStringContainsString(' required', $html);
        $this->assertStringContainsString('border-danger', $html);
    }

    public function testInputSemErroNaoMarcaInvalido(): void
    {
        $html = $this->html('input', ['name' => 'n', 'label' => 'L', 'id' => 'meu-id', 'value' => 0]);

        $this->assertStringNotContainsString('aria-invalid', $html);
        $this->assertStringNotContainsString('aria-describedby', $html);
        $this->assertStringContainsString('for="meu-id"', $html);
        $this->assertStringContainsString('value="0"', $html);
    }

    public function testInputLabelEscondido(): void
    {
        $this->assertStringContainsString('class="sr-only"', $this->html('input', ['name' => 'busca', 'label' => 'Buscar', 'hide_label' => true]));
    }

    public function testSelectFormatosESelecao(): void
    {
        $html = $this->html('select', [
            'name' => 'tipo',
            'label' => 'Tipo',
            'placeholder' => 'Selecione...',
            'options' => [['value' => '0', 'label' => 'Zero'], ['value' => 1, 'label' => 'Um', 'disabled' => true]],
            'selected' => 0,
        ]);

        $this->assertStringContainsString('<option value="">Selecione...</option>', $html);
        $this->assertStringContainsString('<option value="0" selected>Zero</option>', $html);
        $this->assertStringContainsString('<option value="1" disabled>Um</option>', $html);
    }

    public function testSelectSemSelecaoMarcaOPlaceholder(): void
    {
        $html = $this->html('select', ['name' => 't', 'label' => 'T', 'placeholder' => '...', 'options' => ['' => 'Vazio', 'a' => 'A']]);

        $this->assertStringContainsString('<option value="" selected>...</option>', $html);
    }

    public function testSelectMultiplo(): void
    {
        $html = $this->html('select', ['name' => 'tags', 'label' => 'Tags', 'multiple' => true, 'options' => ['a' => 'A', 'b' => 'B', 'c' => 'C'], 'selected' => ['a', 'c']]);

        $this->assertStringContainsString('name="tags[]"', $html);
        $this->assertStringContainsString(' multiple', $html);
        $this->assertSame(2, substr_count($html, ' selected'));
    }

    public function testSelectOpcaoSemLabelEhErro(): void
    {
        $this->expectException(InvalidArgumentException::class);
        component('select', ['name' => 'n', 'label' => 'L', 'options' => [['value' => 'a']]]);
    }

    public function testTextareaNaoDeixaFecharATag(): void
    {
        $html = $this->html('textarea', ['name' => 'obs', 'label' => 'Obs', 'value' => '</textarea><b>x</b>', 'rows' => 0]);

        $this->assertSame(1, substr_count($html, '</textarea>'));
        $this->assertStringContainsString('rows="1"', $html);
    }

    // ------------------------------------------------------------ slots

    public function testCardSlotsAceitamComponentes(): void
    {
        $html = $this->html('card', [
            'id' => 'c1',
            'title' => 'Resumo',
            'body' => [component('badge', ['label' => 'Ativo']), ' & texto'],
            'actions' => component('button', ['label' => 'Editar']),
            'footer' => 'Rodapé',
        ]);

        $this->assertStringContainsString('aria-labelledby="c1-titulo"', $html);
        $this->assertStringContainsString('<span class="', $html);
        $this->assertStringContainsString('>Ativo</span> &amp; texto', $html);
        $this->assertStringContainsString('<button', $html);
        $this->assertStringContainsString('<footer', $html);
    }

    public function testCardSemTituloNaoTemHeader(): void
    {
        $this->assertStringNotContainsString('<header', $this->html('card', ['body' => 'x']));
    }

    // ------------------------------------------------------------ table

    public function testTabelaComObjetosERender(): void
    {
        $linha = (object) ['id' => 7, 'nome' => 'Maria'];
        $html = $this->html('table', [
            'caption' => 'Clientes',
            'columns' => [
                ['key' => 'nome', 'label' => 'Nome'],
                ['label' => 'Ações', 'align' => 'right', 'render' => static fn ($l) => component('button', ['label' => 'Editar ' . $l->id])],
                ['label' => 'Texto', 'render' => static fn ($l) => '<i>' . $l->nome . '</i>'],
            ],
            'rows' => [$linha],
        ]);

        $this->assertStringContainsString('<caption class="sr-only">Clientes</caption>', $html);
        $this->assertStringContainsString('<th scope="col"', $html);
        $this->assertStringContainsString('>Maria</td>', $html);
        $this->assertStringContainsString('Editar 7</span>', $html);
        $this->assertStringContainsString('&lt;i&gt;Maria&lt;/i&gt;', $html);
        $this->assertStringContainsString('text-right', $html);
    }

    public function testTabelaVaziaMostraEmptyState(): void
    {
        $html = $this->html('table', ['columns' => ['a' => 'A', 'b' => 'B'], 'empty' => 'Nada encontrado']);

        $this->assertStringContainsString('colspan="2"', $html);
        $this->assertStringContainsString('Nada encontrado', $html);
        $this->assertStringContainsString('border-dashed', $html);
    }

    public function testTabelaAceitaIterador(): void
    {
        $html = $this->html('table', ['columns' => ['a' => 'A'], 'rows' => new ArrayIterator([['a' => 'um'], ['a' => 'dois']])]);

        $this->assertStringContainsString('>dois</td>', $html);
    }

    public function testTabelaColunaSemKeyNemRenderEhErro(): void
    {
        $this->expectException(InvalidArgumentException::class);
        component('table', ['columns' => [['label' => 'X']]]);
    }

    // ------------------------------------------------------------ modal, alert, toast

    public function testModalUsaDialogComModulo(): void
    {
        $html = $this->html('modal', ['id' => 'modal-x', 'title' => 'Excluir?', 'footer' => component('button', ['label' => 'Ok', 'attrs' => ['data-modal-fechar' => true]])]);

        $this->assertStringStartsWith('<dialog ', $html);
        $this->assertStringContainsString('id="modal-x"', $html);
        $this->assertStringContainsString('data-module="componentes/modal"', $html);
        $this->assertStringContainsString('aria-labelledby="modal-x-titulo"', $html);
        $this->assertStringContainsString('<h2 id="modal-x-titulo"', $html);
        $this->assertStringContainsString('class="sr-only">Fechar</span>', $html);
        $this->assertStringNotContainsString('data-modal-aberto', $html);
        $this->assertStringContainsString('data-modal-aberto="true"', $this->html('modal', ['id' => 'm', 'title' => 'T', 'open' => true]));
    }

    public static function papeisDoAviso(): array
    {
        return [['info', 'status'], ['success', 'status'], ['warning', 'alert'], ['danger', 'alert']];
    }

    #[DataProvider('papeisDoAviso')]
    public function testPapelDoAlertEDoToast(string $variante, string $papel): void
    {
        $this->assertStringContainsString('role="' . $papel . '"', $this->html('alert', ['message' => 'x', 'variant' => $variante]));
        $this->assertStringContainsString('role="' . $papel . '"', $this->html('toast', ['message' => 'x', 'variant' => $variante]));
    }

    public function testAlertDispensavel(): void
    {
        $fixo = $this->html('alert', ['message' => 'x']);
        $dispensavel = $this->html('alert', ['message' => 'x', 'dismissible' => true]);

        $this->assertStringNotContainsString('data-dispensar', $fixo);
        $this->assertStringContainsString('data-module="componentes/dispensavel"', $dispensavel);
        $this->assertStringContainsString('data-dispensar', $dispensavel);
        $this->assertStringContainsString('Fechar aviso', $dispensavel);
    }

    public function testToastDuracao(): void
    {
        $this->assertStringContainsString('data-duracao="5000"', $this->html('toast', ['message' => 'x']));
        $this->assertStringContainsString('data-duracao="0"', $this->html('toast', ['message' => 'x', 'duration' => -10]));
        $this->assertStringContainsString('data-module="componentes/toast"', $this->html('toast', ['message' => 'x']));
    }

    // ------------------------------------------------------------ badge, empty-state, breadcrumb

    public function testBadgeVariantes(): void
    {
        $this->assertStringContainsString('bg-success-soft text-success-ink', $this->html('badge', ['label' => 'Ok', 'variant' => 'success']));
        $this->assertStringContainsString('text-[0.6875rem]', $this->html('badge', ['label' => 'Ok', 'size' => 'sm']));
    }

    public function testEmptyStateComAcao(): void
    {
        $html = $this->html('empty-state', ['title' => 'Vazio', 'icon' => 'wrench', 'action' => component('button', ['label' => 'Nova OS'])]);

        $this->assertStringContainsString('sprite.svg#wrench"', $html);
        $this->assertStringContainsString('Nova OS</span>', $html);
        $this->assertStringNotContainsString('<svg', $this->html('empty-state', ['title' => 'Vazio', 'icon' => null]));
    }

    public function testBreadcrumb(): void
    {
        $html = $this->html('breadcrumb', ['items' => [
            ['label' => 'Início', 'url' => '/'],
            ['label' => 'Perigoso', 'url' => 'javascript:alert(1)'],
            ['label' => 'Editar', 'url' => '/ignorado'],
        ]]);

        $this->assertStringContainsString('aria-label="Você está em"', $html);
        $this->assertStringContainsString('<a href="/"', $html);
        $this->assertStringContainsString('<a href="#"', $html);
        $this->assertStringContainsString('<span aria-current="page" class="font-medium text-text">Editar</span>', $html);
        $this->assertStringNotContainsString('/ignorado', $html);
    }

    public function testBreadcrumbItemSemLabel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        component('breadcrumb', ['items' => [['url' => '/']]]);
    }

    // ------------------------------------------------------------ pagination

    public static function janelas(): array
    {
        return [
            'meio' => [6, 20, 1, [1, null, 5, 6, 7, null, 20]],
            'inicio' => [1, 20, 1, [1, 2, null, 20]],
            'fim' => [20, 20, 1, [1, null, 19, 20]],
            'um escondido vira numero' => [4, 20, 1, [1, 2, 3, 4, 5, null, 20]],
            'poucas paginas' => [2, 3, 1, [1, 2, 3]],
            'uma pagina' => [1, 1, 1, [1]],
            'nenhuma' => [1, 0, 1, []],
            'atual fora da faixa' => [99, 5, 1, [1, null, 4, 5]],
            'janela 2' => [10, 20, 2, [1, null, 8, 9, 10, 11, 12, null, 20]],
            'janela 0' => [10, 20, 0, [1, null, 10, null, 20]],
        ];
    }

    #[DataProvider('janelas')]
    public function testPaginasExibidas(int $atual, int $total, int $janela, array $esperado): void
    {
        $this->assertSame($esperado, componentePaginas($atual, $total, $janela));
    }

    public function testUrlDaPagina(): void
    {
        $this->assertSame('/c/30', componentePaginaUrl('/c/{offset}', 4, 10));
        $this->assertSame('/c/0', componentePaginaUrl('/c/{offset}', 1, 10));
        $this->assertSame('/c?p=4', componentePaginaUrl('/c?p={page}', 4));

        $this->expectException(InvalidArgumentException::class);
        componentePaginaUrl('/c/{offset}', 2);
    }

    public function testPaginacaoRenderizada(): void
    {
        $html = $this->html('pagination', ['total_pages' => 20, 'current' => 6, 'url' => '/clientes/gerenciar/{offset}', 'per_page' => 10]);

        $this->assertStringContainsString('<nav aria-label="Paginação"', $html);
        $this->assertStringContainsString('href="/clientes/gerenciar/50" aria-current="page"', $html);
        $this->assertStringContainsString('href="/clientes/gerenciar/40" rel="prev"', $html);
        $this->assertStringContainsString('href="/clientes/gerenciar/60" rel="next"', $html);
        $this->assertStringContainsString('href="/clientes/gerenciar/190"', $html);
        $this->assertSame(2, substr_count($html, '&hellip;'));
    }

    public function testPaginacaoNasPontas(): void
    {
        $primeira = $this->html('pagination', ['total_pages' => 3, 'current' => 1, 'url' => '/p/{page}']);
        $ultima = $this->html('pagination', ['total_pages' => 3, 'current' => 3, 'url' => '/p/{page}']);

        $this->assertStringNotContainsString('rel="prev"', $primeira);
        $this->assertStringContainsString('aria-disabled="true"', $primeira);
        $this->assertStringNotContainsString('rel="next"', $ultima);
    }

    public function testPaginacaoComUmaPaginaNaoRenderiza(): void
    {
        $this->assertSame('', $this->html('pagination', ['total_pages' => 1, 'url' => '/p/{page}']));
        $this->assertSame('', $this->html('pagination', ['total_pages' => 0, 'url' => '/p/{page}']));
    }

    public function testPaginacaoComOffsetSemPerPageEhErro(): void
    {
        $this->expectException(InvalidArgumentException::class);
        component('pagination', ['total_pages' => 3, 'url' => '/p/{offset}']);
    }
}
