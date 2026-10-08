<?php

/**
 * Formulário de cliente (#2851): dados salvos, valores da tela e o fluxo do
 * controller.
 */
final class ClientesFormularioTest extends MaposTestCase
{
    private const POST = [
        'nomeCliente' => '  Ana Souza ',
        'contato' => '',
        'documento' => '123.456.789-09',
        'telefone' => '(11) 3333-4444',
        'celular' => '',
        'email' => 'ana@x.com',
        'cep' => '01001-000',
        'rua' => 'Praça da Sé',
        'numero' => '1',
        'complemento' => '',
        'bairro' => 'Sé',
        'cidade' => 'São Paulo',
        'estado' => 'sp',
        'fornecedor' => '0',
        'senha' => '',
        'idClientes' => '999',
        'asaas_id' => 'x',
    ];

    public function testDadosDeUmCadastroNovo(): void
    {
        $dados = clienteDadosDoFormulario(self::POST, true);

        $this->assertSame('Ana Souza', $dados['nomeCliente']);
        $this->assertSame('SP', $dados['estado']);
        $this->assertSame(1, $dados['pessoa_fisica']);
        $this->assertSame(0, $dados['fornecedor']);
        // Sem senha, o cadastro novo usa o CPF/CNPJ sem pontuação.
        $this->assertTrue(password_verify('12345678909', $dados['senha']));
        // Só os campos do formulário: nada de id, asaas_id ou dataCadastro vindo do POST.
        $this->assertArrayNotHasKey('idClientes', $dados);
        $this->assertArrayNotHasKey('asaas_id', $dados);
        $this->assertArrayNotHasKey('dataCadastro', $dados);
    }

    public function testEdicaoSemSenhaNaoMudaASenha(): void
    {
        $this->assertArrayNotHasKey('senha', clienteDadosDoFormulario(self::POST, false));

        $comSenha = clienteDadosDoFormulario(['senha' => 'nova-senha'] + self::POST, false);
        $this->assertTrue(password_verify('nova-senha', $comSenha['senha']));
    }

    public function testCnpjFornecedorEUfInvalida(): void
    {
        $dados = clienteDadosDoFormulario(['documento' => '11.222.333/0001-81', 'fornecedor' => '1', 'estado' => 'XX'] + self::POST, true);

        $this->assertSame(0, $dados['pessoa_fisica']);
        $this->assertSame(1, $dados['fornecedor']);
        $this->assertSame('', $dados['estado']);
    }

    public function testCadastroSemDocumentoNemSenhaGanhaSenhaAleatoria(): void
    {
        $dados = clienteDadosDoFormulario(['documento' => '', 'senha' => ''] + self::POST, true);

        $this->assertFalse(password_verify('', $dados['senha']));
        $this->assertStringStartsWith('$2y$', $dados['senha']);
    }

    public function testValoresSemArrayNoPost(): void
    {
        $dados = clienteDadosDoFormulario(['nomeCliente' => ['x'], 'email' => ['a@x.com']] + self::POST, true);

        $this->assertSame('', $dados['nomeCliente']);
        $this->assertSame('', $dados['email']);
    }

    public function testValoresDaTela(): void
    {
        $cliente = (object) ['nomeCliente' => 'Ana', 'email' => 'ana@x.com', 'estado' => 'SP', 'fornecedor' => '1', 'senha' => '$2y$hash'];

        $edicao = clienteValoresDoFormulario(null, $cliente);
        $this->assertSame('Ana', $edicao['nomeCliente']);
        $this->assertSame('', $edicao['rua']);
        $this->assertTrue($edicao['fornecedor']);
        $this->assertArrayNotHasKey('senha', $edicao);

        // Com erro, a tela volta com o que foi enviado, não com o banco.
        $reenvio = clienteValoresDoFormulario(['nomeCliente' => 'Ana Editada', 'fornecedor' => '0'], $cliente);
        $this->assertSame('Ana Editada', $reenvio['nomeCliente']);
        $this->assertSame('', $reenvio['email']);
        $this->assertFalse($reenvio['fornecedor']);

        $novo = clienteValoresDoFormulario(null, null);
        $this->assertSame('', $novo['nomeCliente']);
        $this->assertFalse($novo['fornecedor']);
    }

    public function testUfsDoBrasil(): void
    {
        $this->assertCount(27, ufsDoBrasil());
        $this->assertSame('São Paulo', ufsDoBrasil()['SP']);
    }

    /**
     * O controller usa o id da URL (nunca o idClientes do POST), valida com
     * validarFormulario() e redireciona com o toast.
     */
    public function testControllerUsaOIdDaUrlEOPadraoDeFormulario(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Clientes.php');
        preg_match('/private function formulario\(.*?\n    }\n/s', $codigo, $trecho);

        $this->assertNotEmpty($trecho);
        $this->assertStringContainsString("\$this->validarFormulario('clientes')", $trecho[0]);
        $this->assertStringContainsString('clienteDadosDoFormulario($this->input->post(), $cliente === null)', $trecho[0]);
        $this->assertStringContainsString("'idClientes', (int) \$cliente->idClientes", $trecho[0]);
        $this->assertStringNotContainsString("post('idClientes')", $codigo);
        $this->assertStringContainsString("\$this->data['legacy_assets'] = false;", $trecho[0]);
    }

    public function testMensagensDeValidacaoEmPortugues(): void
    {
        $lang = [];
        include APPPATH . 'language/pt-br/form_validation_lang.php';

        $this->assertSame('O campo %s é obrigatório.', $lang['form_validation_required']);
        $ingles = [];
        (static function () use (&$ingles) {
            $lang = [];
            include BASEPATH . 'language/english/form_validation_lang.php';
            $ingles = $lang;
        })();
        $this->assertSame([], array_diff(array_keys($ingles), array_keys($lang)), 'Faltam chaves em pt-br/form_validation_lang.php.');
    }
}
