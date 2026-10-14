<?php

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

/**
 * Minha conta e busca geral na v5 (#2855): as duas últimas telas do painel que
 * ainda rodavam no modo legado (Bootstrap 2 e jQuery).
 */
final class ContaEBuscaTest extends MaposTestCase
{
    private function renderizar(string $view, array $variaveis): string
    {
        $contexto = new class() {
            public object $security;

            public function __construct()
            {
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
            }

            public function mostrar(string $__caminho, array $__variaveis): string
            {
                return (function () use ($__caminho, $__variaveis) {
                    extract($__variaveis);
                    ob_start();

                    try {
                        include $__caminho;
                    } catch (Throwable $erro) {
                        ob_end_clean();

                        throw $erro;
                    }

                    return (string) ob_get_clean();
                })();
            }
        };

        return $contexto->mostrar(APPPATH . 'views/' . $view . '.php', $variaveis);
    }

    private function semLegado(string $html): void
    {
        $this->assertSame(0, preg_match('/<script(?![^>]*\bsrc=)[^>]*>/', $html), 'Nenhum <script> sem src.');
        $this->assertStringNotContainsString('jquery', strtolower($html));
        $this->assertStringNotContainsString('bx-', $html);
        $this->assertStringNotContainsString('row-fluid', $html);
        $this->assertStringNotContainsString('widget-box', $html);
    }

    private function conta(array $mudancas = []): string
    {
        $xss = '<img src=x onerror=alert(1)>';

        return $this->renderizar('mapos/minhaConta', $mudancas + [
            'usuario' => (object) ['nome' => 'Ramon da Silva', 'email' => 'r@x.com', 'telefone' => '', 'permissao' => 'Administrador ' . $xss, 'dataExpiracao' => '2030-05-01'],
            'avatar' => 'http://mapos.test/assets/img/User.png',
            'tem_foto' => false,
            'pode_foto' => true,
            'dois_fatores_ativo' => false,
            'erros' => [],
        ]);
    }

    public function testContaMostraDadosEscapadosEIniciaisSemFoto(): void
    {
        $html = $this->conta();

        $this->semLegado($html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('01/05/2030', $html);
        $this->assertStringNotContainsString('Telefone', $html, 'Campo vazio não aparece.');
        $this->assertStringContainsString('RS', $html, 'Iniciais no lugar da foto.');
        $this->assertStringContainsString('Enviar foto', $html);
    }

    public function testContaComFotoEUsuarioSemExpiracao(): void
    {
        $html = $this->conta([
            'avatar' => 'http://mapos.test/assets/userImage/a.png',
            'tem_foto' => true,
            'usuario' => (object) ['nome' => 'Ana', 'email' => 'a@x.com', 'telefone' => '1', 'permissao' => 'Técnico', 'dataExpiracao' => null],
        ]);

        $this->assertStringContainsString('src="http://mapos.test/assets/userImage/a.png"', $html);
        $this->assertStringContainsString('Trocar foto', $html);
        $this->assertStringContainsString('Sem data de expiração', $html);
    }

    public function testContaMostraOEstadoDoDoisFatores(): void
    {
        $this->assertStringContainsString('Desativada', $this->conta());
        $this->assertStringContainsString('Configurar', $this->conta());

        $ativo = $this->conta(['dois_fatores_ativo' => true]);
        $this->assertStringContainsString('Ativada', $ativo);
        $this->assertStringContainsString('Gerenciar', $ativo);
        $this->assertStringContainsString('href="http://mapos.test/index.php/mapos/doisFatores"', $ativo);
    }

    public function testFotoSoApareceComAPermissaoDeEnviar(): void
    {
        $semPermissao = $this->conta(['pode_foto' => false]);

        $this->assertStringNotContainsString('trocar-foto', $semPermissao);
        $this->assertStringNotContainsString('uploadUserImage', $semPermissao);

        $com = $this->conta();
        $this->assertStringContainsString('action="http://mapos.test/index.php/mapos/uploadUserImage" enctype="multipart/form-data"', $com);
        $this->assertMatchesRegularExpression('/<input type="file"[^>]*name="userfile"[^>]*form="form-foto"[^>]*required/', $com);
    }

    public function testFormularioDaSenhaMostraOErroEmCadaCampo(): void
    {
        $html = $this->conta(['erros' => ['oldSenha' => 'A senha atual não confere.', 'confirmarSenha' => 'A confirmação não é igual à nova senha.']]);

        $this->assertStringContainsString('data-module="formulario/padrao"', $html);
        $this->assertStringContainsString('action="http://mapos.test/index.php/mapos/alterarSenha"', $html);
        $this->assertStringContainsString('name="MAPOS_TOKEN" value="hash-csrf"', $html);
        $this->assertStringContainsString('A senha atual não confere.', $html);
        $this->assertStringContainsString('Confira os 2 campos destacados.', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*name="novaSenha"[^>]*minlength="8"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*name="oldSenha"[^>]*autocomplete="current-password"/', $html);
        // "Alterar senha" na página e "Enviar foto" no modal (fechado, só abre ao pedido).
        $this->assertSame(2, preg_match_all('/<button[^>]*bg-primary/', $html));
        $this->assertSame(1, preg_match_all('/<button[^>]*bg-primary/', $this->conta(['pode_foto' => false])), 'Sem o modal, um único button-primary.');
    }

    public function testRegrasDaTrocaDeSenhaEControllerSemRedirecionarOsErros(): void
    {
        $regras = (string) file_get_contents(APPPATH . 'config/form_validation.php');
        $this->assertStringContainsString("'alterar_senha' => [", $regras);
        $this->assertStringContainsString("'rules' => 'required|min_length[8]|max_length[72]'", $regras);
        $this->assertStringContainsString("'rules' => 'required|matches[novaSenha]'", $regras);

        $controller = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');
        preg_match('/function alterarSenha\(\).*?\n    }\n/s', $controller, $trecho);
        $this->assertNotEmpty($trecho);
        $this->assertStringContainsString("validarFormulario('alterar_senha')", $trecho[0]);
        $this->assertStringContainsString("confirmarSenhaDoUsuario(\$current_user, (string) \$this->input->post('oldSenha')", $trecho[0]);
        $this->assertStringContainsString('return $this->telaDaConta($erros)', $trecho[0]);
        foreach (['telaDaConta', 'pesquisar'] as $metodo) {
            preg_match('/function ' . $metodo . '\\(.*?\\n    }\\n/s', $controller, $corpo);
            $this->assertNotEmpty($corpo, $metodo);
        }
    }

    public function testFotoNovaAtualizaAChaveDaSessaoQueOLayoutLe(): void
    {
        $controller = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');
        $base = (string) file_get_contents(APPPATH . 'core/MY_Controller.php');

        $this->assertStringContainsString("userdata('url_image_user_admin')", $base, 'O layout lê esta chave.');
        $this->assertStringContainsString("set_userdata('url_image_user_admin', \$imageUserPath)", $controller);
        $this->assertStringNotContainsString("set_userdata('url_image_user',", $controller);
    }

    private function busca(array $mudancas = []): string
    {
        $xss = '<img src=x onerror=alert(1)>';

        return $this->renderizar('mapos/pesquisa', $mudancas + [
            'termo' => 'ana',
            'clientes' => [(object) ['idClientes' => 3, 'nomeCliente' => 'Ana ' . $xss, 'documento' => '123', 'fornecedor' => 0]],
            'os' => [(object) ['idOs' => 41, 'descricaoProduto' => '<p>Notebook <b>Dell</b></p>', 'dataInicial' => '2026-10-01', 'status' => 'Em Andamento']],
            'produtos' => [(object) ['idProdutos' => 5, 'descricao' => 'Tela ' . $xss, 'precoVenda' => '99.9', 'estoque' => 4]],
            'servicos' => [(object) ['idServicos' => 7, 'nome' => 'Formatação', 'preco' => '120']],
            'pode' => ['clientes' => true, 'os' => true, 'produtos' => true, 'servicos' => true, 'editar_cliente' => true, 'editar_os' => true, 'editar_produto' => true, 'editar_servico' => true],
        ]);
    }

    public function testBuscaMostraOsQuatroGruposEscapandoOsDados(): void
    {
        $html = $this->busca();

        $this->semLegado($html);
        $this->assertStringNotContainsString('<img src=x', $html);
        foreach (['Clientes e fornecedores', 'Ordens de serviço', 'Produtos', 'Serviços'] as $grupo) {
            $this->assertStringContainsString($grupo, $html);
        }
        $this->assertStringContainsString('4 resultados para “ana”', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/os/visualizar/41"', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/servicos/editar/7"', $html);
        $this->assertStringContainsString('R$ 99,90', $html);
        $this->assertStringContainsString('Notebook Dell', $html, 'Texto curto da OS, sem tags.');
        $this->assertStringContainsString('Em Andamento', $html);
    }

    public function testBuscaEscondeOQueOUsuarioNaoPodeVer(): void
    {
        $html = $this->busca(['pode' => ['clientes' => true, 'os' => false, 'produtos' => false, 'servicos' => false, 'editar_cliente' => false, 'editar_os' => false, 'editar_produto' => false, 'editar_servico' => false], 'os' => [], 'produtos' => [], 'servicos' => []]);

        $this->assertStringContainsString('Clientes e fornecedores', $html);
        $this->assertStringNotContainsString('Ordens de serviço', $html);
        $this->assertStringNotContainsString('clientes/editar/3', $html, 'Sem eCliente, sem o botão de editar.');
        $this->assertStringContainsString('clientes/visualizar/3', $html);

        $nada = $this->busca(['pode' => ['clientes' => false, 'os' => false, 'produtos' => false, 'servicos' => false, 'editar_cliente' => false, 'editar_os' => false, 'editar_produto' => false, 'editar_servico' => false]]);
        $this->assertStringContainsString('Sem acesso à busca', $nada);
    }

    public function testBuscaSemTermoPedeOQueBuscarESemResultadoDizIsso(): void
    {
        $semTermo = $this->busca(['termo' => '', 'clientes' => [], 'os' => [], 'produtos' => [], 'servicos' => []]);
        $this->assertStringContainsString('O que você procura?', $semTermo);
        $this->assertStringNotContainsString('<table', $semTermo);

        $vazio = $this->busca(['termo' => 'zzz <b>', 'clientes' => [], 'os' => [], 'produtos' => [], 'servicos' => []]);
        $this->assertStringContainsString('Nada encontrado para “zzz &lt;b&gt;”', $vazio);
        $this->assertStringContainsString('Nenhum cliente encontrado.', $vazio);
    }
}
