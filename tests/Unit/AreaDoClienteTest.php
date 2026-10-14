<?php

require_once APPPATH . 'helpers/conecte_helper.php';
require_once APPPATH . 'controllers/Mine.php';

if (! function_exists('base_url')) {
    function base_url($uri = '')
    {
        return 'http://mapos.test/' . ltrim((string) $uri, '/');
    }
}

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

/**
 * Área do cliente (#2848): regras do conecte_helper, rotas do Mine e as views
 * da moldura, das listagens, das telas de OS e compra e dos formulários.
 */
final class AreaDoClienteTest extends MaposTestCase
{
    // --- Regras (conecte_helper) --------------------------------------

    public function testMenuTemATabbarDoDesignMdEMarcaOItemAtual(): void
    {
        $menu = conecteMenu('compras');
        $tabbar = array_values(array_filter($menu, static fn ($item) => $item['tabbar']));

        $this->assertSame(['Início', 'Minhas OS', 'Compras', 'Conta'], array_column($tabbar, 'curto'), 'DESIGN.md tabbar-mobile: quatro itens.');
        $this->assertContains('mine/cobrancas', array_column($menu, 'url'), 'A Top Nav tem as cobranças.');
        $this->assertSame(['compras'], array_column(array_filter($menu, static fn ($item) => $item['atual']), 'chave'));
    }

    public function testLinkDoEmailSoAceitaONumeroDaConta(): void
    {
        $this->assertSame(12, conecteIdDoLinkDoEmail((string) (7653 * 12 + 44023)));
        $this->assertSame(1, conecteIdDoLinkDoEmail(7653 + 44023));

        foreach ([null, '', 'abc', '-5', '44023', (string) (7653 * 12 + 44024), '1.5', ['1']] as $invalido) {
            $this->assertNull(conecteIdDoLinkDoEmail($invalido), var_export($invalido, true));
        }
    }

    public function testClienteNuncaEscolheSerFornecedor(): void
    {
        $post = ['nomeCliente' => ' Ana ', 'email' => 'ana@x.com', 'documento' => '529.982.247-25', 'senha' => 'segredo123', 'fornecedor' => '1', 'estado' => 'sp', 'idClientes' => '99'];

        $novo = conecteDadosDoCliente($post, true);
        $this->assertSame(0, $novo['fornecedor']);
        $this->assertSame('Ana', $novo['nomeCliente']);
        $this->assertSame('SP', $novo['estado']);
        $this->assertSame(1, $novo['pessoa_fisica']);
        $this->assertTrue(password_verify('segredo123', $novo['senha']));
        $this->assertArrayNotHasKey('idClientes', $novo, 'Só os campos do formulário.');

        $edicao = conecteDadosDoCliente($post, false);
        $this->assertArrayNotHasKey('fornecedor', $edicao, 'Na edição o campo não muda.');

        $semSenha = conecteDadosDoCliente(['senha' => ''] + $post, false);
        $this->assertArrayNotHasKey('senha', $semSenha, 'Senha em branco mantém a atual.');
    }

    public function testRegrasExigemEmailUnicoForaDoProprioCadastro(): void
    {
        $regras = array_column(conecteRegrasDoCliente(7), 'rules', 'field');
        $this->assertStringContainsString('required', $regras['email']);
        $this->assertStringContainsString('unique[clientes.email.7.idClientes]', $regras['email']);
        $this->assertStringContainsString('unique[clientes.documento.7.idClientes]', $regras['documento']);
        $this->assertStringNotContainsString('required', $regras['senha'], 'Na edição a senha é opcional.');

        $novo = array_column(conecteRegrasDoCliente(null), 'rules', 'field');
        $this->assertStringContainsString('unique[clientes.email.0.idClientes]', $novo['email']);
        $this->assertStringContainsString('required|min_length[8]', $novo['senha']);
        $this->assertArrayNotHasKey('fornecedor', $novo);
    }

    public function testConfirmacaoDaSenha(): void
    {
        $this->assertNull(conecteErroDaConfirmacao(['senha' => '', 'senha_confirmacao' => '']));
        $this->assertNull(conecteErroDaConfirmacao(['senha' => 'abcdefgh', 'senha_confirmacao' => 'abcdefgh']));
        $this->assertSame('Repita a senha para confirmar.', conecteErroDaConfirmacao(['senha' => 'abcdefgh']));
        $this->assertSame('A confirmação não é igual à senha.', conecteErroDaConfirmacao(['senha' => 'abcdefgh', 'senha_confirmacao' => 'abcdefgX']));
        $this->assertSame('Repita a senha para confirmar.', conecteErroDaConfirmacao(['senha' => 'abcdefgh', 'senha_confirmacao' => ['x']]));
    }

    public function testCobrancaApontaParaAsTelasDoCliente(): void
    {
        $this->assertSame(['label' => 'OS #5', 'href' => 'http://mapos.test/index.php/mine/visualizarOs/5'], conecteReferenciaDaCobranca((object) ['os_id' => '5', 'vendas_id' => null]));
        $this->assertSame(['label' => 'Venda #8', 'href' => 'http://mapos.test/index.php/mine/visualizarCompra/8'], conecteReferenciaDaCobranca((object) ['os_id' => null, 'vendas_id' => '8']));
        $this->assertNull(conecteReferenciaDaCobranca((object) ['os_id' => null, 'vendas_id' => null, 'charge_id' => 'ch_1', 'idCobranca' => 3])['href']);
    }

    public function testGarantiaDaOsSoDepoisDeFinalizada(): void
    {
        $os = (object) ['dataFinal' => '2026-10-01', 'garantia' => '30', 'status' => 'Finalizado'];
        $this->assertSame(['label' => 'Até 31/10/2026', 'variant' => 'success'], conecteGarantiaOsPill($os, '2026-10-13'));
        $this->assertSame(['label' => 'Venceu em 31/10/2026', 'variant' => 'neutral'], conecteGarantiaOsPill($os, '2026-11-01'));
        $this->assertNull(conecteGarantiaOsPill((object) (['status' => 'Aberto'] + (array) $os)), 'A garantia corre só depois de finalizada.');
    }

    // --- Rotas do Mine --------------------------------------------------

    public function testSoAsTelasDoClienteSaoRotas(): void
    {
        $publicos = array_map(
            static fn (ReflectionMethod $metodo) => $metodo->getName(),
            array_filter((new ReflectionClass(Mine::class))->getMethods(ReflectionMethod::IS_PUBLIC), static fn ($metodo) => $metodo->getDeclaringClass()->getName() === Mine::class && $metodo->getName() !== '__construct'),
        );

        // Ações de cobrança do painel (dependiam das permissões do usuário do
        // sistema) e utilidades internas não são mais rotas da área do cliente.
        foreach (['atualizarcobranca', 'enviarcobranca', 'gerarPagamentoGerencianetBoleto', 'gerarPagamentoGerencianetLink', 'formatarChave', 'validarCPF', 'validarCNPJ'] as $metodo) {
            $this->assertNotContains($metodo, $publicos, $metodo);
        }

        foreach (['painel', 'os', 'visualizarOs', 'detalhesOs', 'adicionarOs', 'compras', 'visualizarCompra', 'cobrancas', 'conta', 'editarDados', 'cadastrar', 'senhaSalvar'] as $metodo) {
            $this->assertContains($metodo, $publicos, $metodo);
        }
    }

    public function testChaveDoPixFormatada(): void
    {
        $mine = $this->makeInstance(Mine::class);

        $this->assertSame('529.982.247-25', $this->invokeMethod($mine, 'formatarChave', ['52998224725']));
        $this->assertSame('(11) 98765-4321', $this->invokeMethod($mine, 'formatarChave', ['11987654321']));
        $this->assertSame('pix@loja.com', $this->invokeMethod($mine, 'formatarChave', ['pix@loja.com']));
    }

    // --- Views ----------------------------------------------------------

    /**
     * Renderiza uma view como o CI_Loader: variáveis compartilhadas entre a
     * view e os $this->load->view() que ela chama.
     */
    private function renderizar(string $view, array $variaveis): string
    {
        $carregador = new class($variaveis) {
            public object $load;

            public object $security;

            public object $config;

            public function __construct(private array $variaveis)
            {
                $this->load = $this;
                $this->security = new class() {
                    public function get_csrf_token_name()
                    {
                        return 'MAPOS_TOKEN';
                    }

                    public function get_csrf_hash()
                    {
                        return 'hash-csrf';
                    }
                };
                $this->config = new class() {
                    public function item($nome)
                    {
                        return ['app_name' => 'Map-OS'][$nome] ?? null;
                    }
                };
            }

            public function view(string $nome, array $mais = [], bool $retornar = false)
            {
                $this->variaveis = array_merge($this->variaveis, $mais);
                $arquivo = APPPATH . 'views' . DIRECTORY_SEPARATOR . $nome . '.php';
                $html = (function () use ($arquivo) {
                    extract($this->variaveis);
                    ob_start();

                    try {
                        include $arquivo;
                    } catch (Throwable $erro) {
                        ob_end_clean();

                        throw $erro;
                    }

                    return (string) ob_get_clean();
                })();

                if ($retornar) {
                    return $html;
                }

                echo $html;
            }
        };

        return $carregador->view($view, [], true);
    }

    private function tela(string $view, string $menu, array $dados, ?array $acao = null): string
    {
        return $this->renderizar('conecte/template', $dados + [
            'view' => $view,
            'menu' => conecteMenu($menu),
            'acao' => $acao,
            'flash' => [],
            'configuration' => ['app_name' => 'Map-OS', 'app_tema_modo' => 'escuro'],
            'cliente_nome' => 'Ana <b>Souza</b>',
        ]);
    }

    private function semScriptInline(string $html): void
    {
        $this->assertSame(0, preg_match('/<script(?![^>]*\bsrc=)[^>]*>/', $html), 'Nenhum <script> sem src.');
        $this->assertStringNotContainsString('jquery', strtolower($html));
        $this->assertStringNotContainsString('bx-', $html);
        $this->assertStringNotContainsString('<b>Souza', $html);
    }

    public function testMolduraTemTopNavTabbarEOModoDeCor(): void
    {
        $html = $this->tela('conecte/painel', 'painel', [
            'os' => [],
            'compras' => [],
            'resumo' => ['os_em_andamento' => 2, 'os' => 3, 'compras' => 0, 'cobrancas' => 1],
        ], ['label' => 'Abrir OS', 'icon' => 'plus', 'href' => 'http://mapos.test/index.php/mine/adicionarOs']);

        $this->semScriptInline($html);
        $this->assertStringContainsString('<html lang="pt-br" class="dark" data-tema-modo="escuro">', $html, 'Segue o modo de cor das Configurações.');
        $this->assertStringContainsString('assets/js/tema.js', $html);
        $this->assertStringContainsString('<header class="bg-canvas-dark text-on-dark">', $html, 'Top Nav escura.');
        $this->assertMatchesRegularExpression('#<nav class="fixed inset-x-0 bottom-0[^"]*lg:hidden" aria-label="Área do cliente">#', $html, 'tabbar-mobile no celular.');
        $this->assertSame(2, preg_match_all('#href="http://mapos.test/index.php/mine/painel"\s+class="relative#', $html), 'Início na Top Nav e na tabbar.');
        $this->assertSame(2, substr_count($html, 'aria-current="page"'), 'O item ativo nos dois menus.');
        $this->assertMatchesRegularExpression('#<nav class="ml-2 hidden lg:block"#', $html, 'Top Nav a partir de lg; abaixo, a tabbar.');
        $this->assertDoesNotMatchRegularExpression('#rounded-md px-3(?! whitespace-nowrap)[^"]*text-on-dark-muted#', $html, 'Rótulos da Top Nav numa linha só.');
        $this->assertStringNotContainsString('>Ordens de serviço</a>', $html, 'Top Nav com os rótulos curtos.');
        $this->assertStringContainsString('href="http://mapos.test/index.php/mine/sair"', $html);
        $this->assertStringNotContainsString('login/sair', $html, 'Sair da área do cliente, não do painel.');
        $this->assertSame(2, substr_count($html, 'bg-primary text-on-primary text-button-cap'), 'Só "Abrir OS" é button-primary (texto e só ícone).');
        $this->assertStringContainsString('Olá, Ana', $html);
        $this->assertStringContainsString('Nenhuma ordem de serviço ainda', $html);
    }

    public function testListaDeOsComStatusEAcoes(): void
    {
        $os = (object) ['idOs' => 41, 'descricaoProduto' => '<p>Notebook <script>x</script></p>', 'dataInicial' => '2026-10-01', 'dataFinal' => '2026-10-05', 'garantia' => '90', 'status' => 'Faturado'];
        $html = $this->tela('conecte/os', 'os', [
            'results' => [$os],
            'total' => 1,
            'paginacao' => paginacaoProps(['base_url' => 'http://mapos.test/index.php/mine/os', 'total_rows' => 1]),
        ]);

        $this->semScriptInline($html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/mine/visualizarOs/41"', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/mine/imprimirOs/41"', $html);
        $this->assertStringContainsString('Faturado', $html);
        $this->assertStringContainsString('Até 03/01/2027', $html, 'Garantia de 90 dias a partir da data final.');
        $this->assertStringNotContainsString('<script>x', $html);
        $this->assertStringNotContainsString('os/visualizar/', $html, 'Nenhum link para as telas do painel.');
    }

    public function testCobrancasLinkamParaOClienteESoPagamDoGateway(): void
    {
        $base = ['charge_id' => 'c1', 'expire_at' => '2026-10-20', 'total' => '12345', 'payment_gateway' => 'Asaas', 'pdf' => null, 'payment_url' => null];
        $html = $this->tela('conecte/cobrancas', 'cobrancas', [
            'results' => [
                (object) (['idCobranca' => 1, 'os_id' => 5, 'vendas_id' => null, 'status' => 'pending', 'link' => 'https://pagar.exemplo/1'] + $base),
                (object) (['idCobranca' => 2, 'os_id' => null, 'vendas_id' => 9, 'status' => 'paid', 'link' => 'https://pagar.exemplo/2'] + $base),
                (object) (['idCobranca' => 3, 'os_id' => 6, 'vendas_id' => null, 'status' => 'waiting', 'link' => 'javascript:alert(1)'] + $base),
            ],
            'total' => 3,
            'paginacao' => paginacaoProps(['base_url' => 'http://mapos.test/index.php/mine/cobrancas', 'total_rows' => 3]),
        ]);

        $this->semScriptInline($html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/mine/visualizarOs/5"', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/mine/visualizarCompra/9"', $html);
        $this->assertStringContainsString('R$ 123,45', $html);
        $this->assertStringContainsString('href="https://pagar.exemplo/1"', $html);
        $this->assertStringNotContainsString('https://pagar.exemplo/2', $html, 'Cobrança paga não mostra "Pagar".');
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('atualizarcobranca', $html);
        $this->assertStringContainsString('Paga', $html);
    }

    public function testTelaDaOsSoLeituraComPix(): void
    {
        // O anexo precisa existir no disco: sem o arquivo a tela mostra "Arquivo não encontrado".
        $raiz = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mapos-cliente-anexo-' . bin2hex(random_bytes(4));
        $anterior = $_ENV['STORAGE_PATH'] ?? null;
        $_ENV['STORAGE_PATH'] = $raiz;
        mkdir($raiz . '/anexos/7', 0750, true);
        file_put_contents($raiz . '/anexos/7/foto.jpg', 'x');

        try {
            $this->verificarTelaDaOsSoLeituraComPix();
        } finally {
            if ($anterior === null) {
                unset($_ENV['STORAGE_PATH']);
            } else {
                $_ENV['STORAGE_PATH'] = $anterior;
            }
            @unlink($raiz . '/anexos/7/foto.jpg');
            @unlink($raiz . '/.htaccess');
            @rmdir($raiz . '/anexos/7');
            @rmdir($raiz . '/anexos');
            @rmdir($raiz);
        }
    }

    private function verificarTelaDaOsSoLeituraComPix(): void
    {
        $xss = '<img src=x onerror=alert(1)>';
        $html = $this->tela('conecte/visualizar_os', 'os', [
            'os' => (object) ['idOs' => 7, 'status' => 'Em Andamento', 'dataInicial' => '2026-10-01', 'dataFinal' => '2026-10-10', 'garantia' => '0', 'nome' => 'Técnico ' . $xss,
                'descricaoProduto' => 'Celular<br>' . $xss, 'defeito' => '', 'observacoes' => '', 'laudoTecnico' => ''],
            'totais' => ['produtos' => 100.0, 'servicos' => 50.0, 'bruto' => 150.0, 'desconto' => 0.0, 'total' => 150.0, 'tipo' => null, 'informado' => 0.0, 'descontoItens' => 0.0],
            'produtos' => [(object) ['idProdutos_os' => 1, 'descricao' => 'Tela ' . $xss, 'quantidade' => 1, 'preco' => 100, 'subTotal' => 100]],
            'servicos' => [],
            'anexos' => [(object) ['idAnexos' => 3, 'anexo' => 'foto.jpg', 'thumb' => null, 'path' => 'anexos/7']],
            'pode' => ['editar' => false],
            'garantia_ate' => null,
            'pix' => ['payload' => '000201PIX', 'qr' => 'data:image/png;base64,AAA', 'chave' => 'pix@loja.com'],
        ]);

        $this->semScriptInline($html);
        $this->assertStringNotContainsString($xss, $html);
        $this->assertStringContainsString('OS #7', $html);
        $this->assertStringContainsString('Em Andamento', $html);
        $this->assertStringContainsString('R$ 150,00', $html);
        $this->assertStringNotContainsString('trash-2', $html, 'Sem as ações de edição do painel.');
        $this->assertStringNotContainsString('data-modal-abrir', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/mine/downloadanexo/3"', $html);
        $this->assertStringNotContainsString('os/downloadanexo', $html);
        $this->assertStringContainsString('data-module="conecte/copiar"', $html);
        $this->assertStringContainsString('data-copiar="pix-copia-cola"', $html);
        $this->assertStringContainsString('value="000201PIX"', $html);
    }

    public function testTelaDaCompraNaoMostraObservacoesInternas(): void
    {
        $html = $this->tela('conecte/visualizar_compra', 'compras', [
            'venda' => (object) ['idVendas' => 9, 'status' => 'Faturado', 'dataVenda' => '2026-10-01', 'garantia' => '30', 'nome' => 'Vendedor',
                'observacoes' => 'Margem baixa, cliente chato', 'observacoes_cliente' => 'Obrigado pela compra'],
            'totais' => ['produtos' => 80.0, 'servicos' => 0.0, 'bruto' => 80.0, 'desconto' => 0.0, 'total' => 80.0, 'tipo' => null, 'informado' => 0.0, 'descontoItens' => 0.0],
            'produtos' => [],
            'pode' => ['itens' => false],
            'garantia_ate' => '2026-10-31',
            'pix' => null,
        ]);

        $this->semScriptInline($html);
        $this->assertStringContainsString('Venda #9', $html);
        $this->assertStringContainsString('Obrigado pela compra', $html);
        $this->assertStringNotContainsString('Margem baixa', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/mine/imprimirCompra/9"', $html);
        $this->assertStringNotContainsString('secao-pix', $html, 'Sem chave PIX, sem a seção.');
    }

    public function testMeusDadosTemCamposDoClienteSemFornecedor(): void
    {
        $valores = clienteValoresDoFormulario(['nomeCliente' => 'Ana "A"', 'email' => 'ana@x.com', 'estado' => 'sp'], null);
        $html = $this->tela('conecte/editar_dados', 'conta', [
            'valores' => $valores,
            'erros' => ['email' => 'Este e-mail já tem cadastro.', 'senha_confirmacao' => 'A confirmação não é igual à senha.'],
        ]);

        $this->semScriptInline($html);
        $this->assertStringContainsString('action="http://mapos.test/index.php/mine/editarDados"', $html);
        $this->assertStringContainsString('data-module="formulario/padrao"', $html);
        $this->assertStringContainsString('data-module="clientes/formulario"', $html, 'Busca do CEP.');
        $this->assertStringContainsString('name="MAPOS_TOKEN" value="hash-csrf"', $html);
        $this->assertStringContainsString('value="Ana &quot;A&quot;"', $html);
        $this->assertStringContainsString('Este e-mail já tem cadastro.', $html);
        $this->assertStringNotContainsString('recupere a senha', conecteRegrasDoCliente(7)[2]['errors']['unique'], 'Logado, a mensagem não manda recuperar a senha.');
        $this->assertStringContainsString('Confira os 2 campos destacados.', $html);
        $this->assertStringNotContainsString('name="fornecedor"', $html);
        $this->assertStringNotContainsString('name="idClientes"', $html, 'O cadastro editado vem da sessão.');
        $this->assertMatchesRegularExpression('/<option value="SP"[^>]*selected/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*name="senha"[^>]*required/', $html, 'Senha opcional na edição.');
    }

    public function testAbrirOsGuardaOsValoresEOsErros(): void
    {
        $html = $this->tela('conecte/adicionarOs', 'os', [
            'valores' => ['descricaoProduto' => '', 'defeito' => 'Não liga </textarea><script>x</script>', 'observacoes' => ''],
            'erros' => ['descricaoProduto' => 'O campo Equipamento ou serviço é obrigatório.'],
        ]);

        $this->semScriptInline($html);
        $this->assertStringContainsString('action="http://mapos.test/index.php/mine/adicionarOs"', $html);
        $this->assertStringContainsString('Não liga &lt;/textarea&gt;&lt;script&gt;x&lt;/script&gt;</textarea>', $html);
        $this->assertStringContainsString('O campo Equipamento ou serviço é obrigatório.', $html);
        $this->assertMatchesRegularExpression('/<textarea[^>]*name="descricaoProduto"[^>]*required/', $html);
        $this->assertMatchesRegularExpression('/<textarea[^>]*name="defeito"[^>]*maxlength="2000"/', $html);
    }

    public function testTelasDeEntradaUsamASuperficieEscura(): void
    {
        $telas = [
            'conecte/cadastrar' => ['valores' => clienteValoresDoFormulario(null, null), 'erros' => ['captcha' => 'Os caracteres não conferem.']],
            'conecte/resetar_senha' => ['erro' => null],
            'conecte/token_digita' => ['erro' => 'Cole o código.'],
            'conecte/nova_senha' => ['token' => str_repeat('a', 64), 'erros' => ['senha' => 'Curta demais.']],
        ];

        foreach ($telas as $view => $dados) {
            $html = $this->renderizar($view, $dados);

            $this->semScriptInline($html);
            $this->assertStringContainsString('<html lang="pt-br" class="scheme-dark">', $html, $view);
            $this->assertMatchesRegularExpression('/class="superficie-entrada /', $html, $view);
            $this->assertStringContainsString('name="MAPOS_TOKEN" value="hash-csrf"', $html, $view);
            $this->assertStringContainsString('data-module="formulario/padrao"', $html, $view);
        }

        $cadastro = $this->renderizar('conecte/cadastrar', $telas['conecte/cadastrar']);
        $this->assertStringContainsString('src="http://mapos.test/index.php/mine/captcha?', $cadastro);
        $this->assertStringContainsString('Os caracteres não conferem.', $cadastro);
        $this->assertMatchesRegularExpression('/<input[^>]*name="senha"[^>]*required/', $cadastro);
        $this->assertStringNotContainsString('name="fornecedor"', $cadastro);
        $this->assertStringContainsString('<section class="" aria-labelledby="secao-identificacao">', $cadastro, 'Sem divisor antes da primeira seção.');

        $novaSenha = $this->renderizar('conecte/nova_senha', $telas['conecte/nova_senha']);
        $this->assertStringContainsString('action="http://mapos.test/index.php/mine/senhaSalvar"', $novaSenha);
        $this->assertStringContainsString('name="token" value="' . str_repeat('a', 64) . '"', $novaSenha);
        $this->assertStringContainsString('Curta demais.', $novaSenha);
    }
}
