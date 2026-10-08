<?php

/**
 * Ícones Lucide do sprite (#2915): helper icon() e consistência entre a lista
 * de assets/src/icones.json, o sprite gerado e os ícones usados nas views.
 */
final class IconeTest extends MaposTestCase
{
    public function testIconeDecorativoApontaParaOSprite(): void
    {
        $html = (string) icon('plus');

        $this->assertInstanceOf(HtmlSeguro::class, icon('plus'));
        $this->assertStringStartsWith('<svg class="shrink-0" width="20" height="20" fill="none" stroke="currentColor"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringNotContainsString('role="img"', $html);
        $this->assertMatchesRegularExpression('#<use href="[^"]*assets/vendor/lucide/sprite\.svg\#plus"></use></svg>$#', $html);
    }

    public function testClasseELabelSaoEscapados(): void
    {
        $html = (string) icon('x', ['class' => 'size-4 "><script>', 'label' => 'Fechar "aviso"']);

        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('aria-label="Fechar &quot;aviso&quot;"', $html);
        $this->assertStringNotContainsString('aria-hidden', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function testNomeForaDoSpriteFalha(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ícone desconhecido: bx-plus');

        icon('bx-plus');
    }

    public function testNomeComHtmlFalha(): void
    {
        $this->expectException(InvalidArgumentException::class);

        icon('plus" onload="alert(1)');
    }

    public function testOpcaoDesconhecidaFalha(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Opção de ícone desconhecida: onclick');

        icon('plus', ['onclick' => 'x']);
    }

    /**
     * O build grava em assets/vendor/lucide a mesma lista de
     * assets/src/icones.json e um <symbol> para cada nome.
     */
    public function testSpriteTemTodosOsIconesDaLista(): void
    {
        $lista = $this->lerJson('assets/src/icones.json');
        $sprite = (string) file_get_contents(MAPOS_ROOT . '/assets/vendor/lucide/sprite.svg');

        $this->assertSame($lista, $this->lerJson('assets/vendor/lucide/icones.json'), 'Rode npm run build:vendor.');
        $this->assertSame($lista, iconesDisponiveis());

        preg_match_all('/<symbol id="([^"]+)"/', $sprite, $simbolos);
        $this->assertSame($lista, $simbolos[1]);
    }

    /**
     * Todo ícone citado nas views, helpers e no JS das telas novas está na
     * lista: icon('nome'), 'icon' => 'nome', 'icone' => 'nome' e os pares
     * ['nome', 'text-...'] dos toasts e alertas. Nenhuma tela nova usa
     * mais Boxicons.
     */
    public function testIconesUsadosEstaoNoSprite(): void
    {
        $lista = $this->lerJson('assets/src/icones.json');
        $usados = [];
        $padroes = [
            "/\\bicon\\(\\s*'([a-z0-9-]+)'/",
            "/'icon'\\s*=>\\s*'([a-z0-9-]+)'/",
            "/'icone'\\s*=>\\s*'([a-z0-9-]+)'/",
            "/\\[\\s*'([a-z0-9-]+)'\\s*,\\s*'text-[a-z-]+'\\s*\\]/",
            "/criarIcone\\(\\s*'([a-z0-9-]+)'/",
        ];

        foreach ($this->arquivosDasTelasNovas() as $arquivo) {
            $codigo = (string) file_get_contents($arquivo);

            foreach ($padroes as $padrao) {
                preg_match_all($padrao, $codigo, $achados);
                foreach ($achados[1] as $nome) {
                    $usados[$nome][] = substr($arquivo, strlen(MAPOS_ROOT) + 1);
                }
            }

            $this->assertDoesNotMatchRegularExpression('/["\'\s]bx[sl]?-[a-z]/', $codigo, "Boxicons em {$arquivo}: use icon() (#2915).");
        }

        $this->assertNotEmpty($usados);
        foreach ($usados as $nome => $arquivos) {
            $this->assertContains($nome, $lista, "Ícone \"{$nome}\" (em " . implode(', ', array_unique($arquivos)) . ') fora de assets/src/icones.json.');
        }
    }

    /**
     * @return list<string>
     */
    private function arquivosDasTelasNovas(): array
    {
        $arquivos = [
            MAPOS_ROOT . '/application/views/mapos/login.php',
            MAPOS_ROOT . '/application/views/componentes/catalogo.php',
            MAPOS_ROOT . '/application/helpers/layout_helper.php',
            MAPOS_ROOT . '/application/helpers/componente_helper.php',
        ];

        foreach (['application/views/components/*.php', 'application/views/tema/*.php', 'assets/js/lib/*.js', 'assets/js/modules/*/*.js'] as $padrao) {
            $arquivos = array_merge($arquivos, glob(MAPOS_ROOT . '/' . $padrao) ?: []);
        }

        return $arquivos;
    }

    private function lerJson(string $caminho): array
    {
        return json_decode((string) file_get_contents(MAPOS_ROOT . '/' . $caminho), true, 512, JSON_THROW_ON_ERROR);
    }
}
