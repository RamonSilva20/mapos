<?php

namespace Tests\Controllers;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ControllerTestCase;
use Tests\Support\Transaction\TransactsDatabase;

/**
 * Testa o controller Clientes: gerenciar, adicionar, editar, visualizar e excluir.
 *
 * Os dados de clientes são criados dentro dos casos e não dependem da linha de
 * base dos usuários. Os redirects usam respond_redirect() (sem exit), portanto são
 * verificáveis pelo cabeçalho Location no CI_Output.
 *
 * O que não é coberto aqui, e por quê, está em AGENTS.md, na seção "Writing a
 * controller test": token CSRF inválido não é testável porque Security::csrf_verify()
 * chama show_error(403) e encerra o processo do PHPUnit.
 */
final class ClientesControllerTest extends ControllerTestCase
{
    use TransactsDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginAs();
        $_SESSION['nome_admin'] = 'Admin';
    }

    /**
     * @return array<string, array{string}>
     */
    public static function permissionDenials(): array
    {
        return [
            'gerenciar sem permissão' => ['gerenciar'],
            'adicionar sem permissão' => ['adicionar'],
            'editar sem permissão' => ['editar'],
            'excluir sem permissão' => ['excluir'],
            'visualizar sem permissão' => ['visualizar'],
        ];
    }

    #[DataProvider('permissionDenials')]
    public function testPermissionDeniesAccess(string $method): void
    {
        $clientId = null;
        if ($method === 'editar' || $method === 'visualizar') {
            $clientId = $this->installClient('temp@exemplo.com', 'Temporario');
        }

        $this->loginAs(999999);

        if ($method === 'gerenciar') {
            $this->callControllerRaw('Clientes', 'gerenciar');
        } elseif ($method === 'adicionar') {
            $this->callControllerRaw('Clientes', 'adicionar');
        } elseif ($method === 'editar') {
            if ($clientId !== null) {
                $this->resetUriSegments('clientes/editar/' . $clientId);
            }
            $this->callControllerRaw('Clientes', 'editar');
        } elseif ($method === 'excluir') {
            $_POST['id'] = (string) ($clientId ?? 1);
            $this->resetUriSegments('clientes/excluir');
            $this->callControllerRaw('Clientes', 'excluir');
            unset($_POST['id']);
        } elseif ($method === 'visualizar') {
            if ($clientId !== null) {
                $this->resetUriSegments('clientes/visualizar/' . $clientId);
            }
            $this->callControllerRaw('Clientes', 'visualizar');
        }

        $this->assertSame(
            base_url(),
            $this->ci()->output->get_header('Location'),
            'Sem permissão o usuário deve ser redirecionado para base_url().'
        );
        $expected = match ($method) {
            'gerenciar' => 'Você não tem permissão para visualizar clientes.',
            'adicionar' => 'Você não tem permissão para adicionar clientes.',
            'editar' => 'Você não tem permissão para editar clientes.',
            'excluir' => 'Você não tem permissão para excluir clientes.',
            'visualizar' => 'Você não tem permissão para visualizar clientes.',
            default => 'Você não tem permissão para visualizar clientes.',
        };
        $this->assertSame($expected, $this->ci()->session->userdata('error'));
    }

    /**
     * @return array<string, array{?array<string, string|null>, array<string>}>
     */
    public static function adicionarRejections(): array
    {
        return [
            'sem corpo de POST' => [null, ['O campo Nome é obrigatório.', 'The Nome field is required.']],
            'nome vazio' => [['nomeCliente' => ''], ['O campo Nome é obrigatório.', 'The Nome field is required.']],
            'email inválido' => [['nomeCliente' => 'Cliente X', 'email' => 'não-é-email'], ['O campo Email não contém um endereço de email válido.', 'The Email field must contain a valid email address.']],
            'documento inválido' => [['nomeCliente' => 'Cliente X', 'documento' => '123'], ['O campo CPF/CNPJ não é um CPF ou CNPJ válido.', 'O CPF ou CNPJ informado não é válido.']],
        ];
    }

    #[DataProvider('adicionarRejections')]
    public function testAdicionarRejectsTheRequest(?array $fields, array $expectedMessages): void
    {
        if ($fields === null) {
            $this->postWithCsrfToken([]);
        } else {
            $this->postWithCsrfToken($fields);
        }

        $response = $this->callControllerRaw('Clientes', 'adicionar');
        $body = (string) $response;

        $this->assertNull($this->ci()->output->get_header('Location'));
        $found = false;
        foreach ($expectedMessages as $m) {
            if (str_contains($body, $m)) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected one of ' . implode(', ', $expectedMessages) . ' in body');
        $this->assertSame(0, (int) $this->ci()->db->count_all_results('clientes'));
    }

    public function testGerenciarRendersTheClientList(): void
    {
        $this->installClient('admin@exemplo.com', 'Admin Cliente');
        $this->installClient('outro@exemplo.com', 'Outro Cliente');

        $html = $this->callControllerRaw('Clientes', 'gerenciar');

        $this->assertStringContainsString('Admin Cliente', $html);
        $this->assertStringContainsString('outro@exemplo.com', $html);
        $this->assertStringContainsString('tabela', $html);
    }

    public function testGerenciarFiltersBySearchTerm(): void
    {
        $this->installClient('admin@exemplo.com', 'Admin Cliente');
        $this->installClient('outro@exemplo.com', 'Outro Cliente');

        $_GET['pesquisa'] = 'Admin';
        $html = $this->callControllerRaw('Clientes', 'gerenciar');
        unset($_GET['pesquisa']);

        $this->assertStringContainsString('Admin Cliente', $html);
        $this->assertStringNotContainsString('Outro Cliente', $html);
    }

    public function testAdicionarStoresTheClient(): void
    {
        $this->postWithCsrfToken([
            'nomeCliente' => 'Novo Cliente',
            'documento' => '52998224725',
            'email' => 'novo@exemplo.com',
            'telefone' => '12345678',
            'senha' => 'segredo',
        ]);

        $this->callControllerRaw('Clientes', 'adicionar');

        $this->assertSame(
            site_url('clientes/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Cliente adicionado com sucesso!',
            $this->ci()->session->userdata('success')
        );

        $row = $this->ci()->db->where('email', 'novo@exemplo.com')->get('clientes')->row();
        $this->assertNotNull($row);
        $this->assertSame('Novo Cliente', $row->nomeCliente);
        $this->assertTrue(password_verify('segredo', (string) $row->senha));
        $this->assertSame('1', (string) $row->pessoa_fisica);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function duplicateValues(): array
    {
        return [
            'email duplicado' => ['email'],
            'documento duplicado' => ['documento'],
        ];
    }

    #[DataProvider('duplicateValues')]
    public function testAdicionarRejectsDuplicates(string $field): void
    {
        $email = 'dup@exemplo.com';
        $doc = '52998224725';
        $this->installClient($email, 'Existente', '123456', $doc);

        $payload = [
            'nomeCliente' => 'Outro',
            'documento' => '11144477735',
            'email' => 'outro@exemplo.com',
        ];
        if ($field === 'email') {
            $payload['email'] = $email;
        }
        if ($field === 'documento') {
            $payload['documento'] = $doc;
        }

        $this->postWithCsrfToken($payload);
        $html = $this->callControllerRaw('Clientes', 'adicionar');

        $this->assertNull($this->ci()->output->get_header('Location'));
        $this->assertStringContainsString('O campo', $html);
        $this->assertStringContainsString('já está cadastrado.', $html);
        $this->assertSame(1, (int) $this->ci()->db->count_all_results('clientes'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidEditIds(): array
    {
        return [
            'segmento não numérico' => ['abc'],
            'cliente inexistente' => ['9999999'],
        ];
    }

    #[DataProvider('invalidEditIds')]
    public function testEditarRedirectsOnInvalidOrUnknownId(string $segment): void
    {
        $this->resetUriSegments('clientes/editar/' . $segment);
        $this->callControllerRaw('Clientes', 'editar');

        $this->assertSame(
            site_url('clientes/gerenciar'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Cliente não encontrado ou parâmetro inválido.',
            $this->ci()->session->userdata('error')
        );
    }

    public function testEditarRejectsDuplicateEmailOfAnotherClient(): void
    {
        $a = $this->installClient('a@exemplo.com', 'A');
        $b = $this->installClient('b@exemplo.com', 'B');

        $this->postWithCsrfToken([
            'idClientes' => (string) $b,
            'nomeCliente' => 'B Editado',
            'email' => 'a@exemplo.com',
            'documento' => '11144477735',
        ]);

        $this->resetUriSegments('clientes/editar/' . $b);
        $html = $this->callControllerRaw('Clientes', 'editar');

        $this->assertNull($this->ci()->output->get_header('Location'));
        $this->assertTrue(
            str_contains($html, 'Este e-mail já está sendo utilizado por outro cliente.') ||
            str_contains($html, 'já está cadastrado'),
            'Deve conter mensagem de duplicidade'
        );
    }

    public function testEditarKeepsThePasswordWhenBlank(): void
    {
        $id = $this->installClient('keep@exemplo.com', 'Keep', 'antiga123');

        $before = $this->clientPassword('keep@exemplo.com');

        $this->postWithCsrfToken([
            'idClientes' => (string) $id,
            'nomeCliente' => 'Keep Editado',
            'email' => 'keep@exemplo.com',
            'documento' => '52998224725',
            'senha' => null,
        ]);

        $this->resetUriSegments('clientes/editar/' . $id);
        $this->callControllerRaw('Clientes', 'editar');

        $after = $this->clientPassword('keep@exemplo.com');
        $this->assertSame($before, $after, 'Senha não deve mudar quando vazia.');
    }

    public function testEditarUpdatesThePasswordWhenProvided(): void
    {
        $id = $this->installClient('change@exemplo.com', 'Change', 'antiga123');

        $this->postWithCsrfToken([
            'idClientes' => (string) $id,
            'nomeCliente' => 'Change Editado',
            'email' => 'change@exemplo.com',
            'documento' => '52998224725',
            'senha' => 'novasenha',
        ]);

        $this->resetUriSegments('clientes/editar/' . $id);
        $this->callControllerRaw('Clientes', 'editar');

        $hash = $this->clientPassword('change@exemplo.com');
        $this->assertTrue(password_verify('novasenha', (string) $hash));
    }

    public function testEditarUpdatesTheClientData(): void
    {
        $id = $this->installClient('edit@exemplo.com', 'Edit', '123456');

        $this->postWithCsrfToken([
            'idClientes' => (string) $id,
            'nomeCliente' => 'Editado',
            'email' => 'edit@exemplo.com',
            'documento' => '52998224725',
            'telefone' => '99999999',
        ]);

        $this->resetUriSegments('clientes/editar/' . $id);
        $this->callControllerRaw('Clientes', 'editar');

        $this->assertSame(
            site_url('clientes/editar/') . $id,
            $this->ci()->output->get_header('Location')
        );

        $row = $this->ci()->db->where('idClientes', $id)->get('clientes')->row();
        $this->assertSame('Editado', $row->nomeCliente);
        $this->assertSame('99999999', $row->telefone);
    }

    public function testVisualizarRendersTheClientsData(): void
    {
        $id = $this->installClient('view@exemplo.com', 'View Test');
        $osId = $this->installServiceOrder($id);

        $this->resetUriSegments('clientes/visualizar/' . $id);
        $html = $this->callControllerRaw('Clientes', 'visualizar');

        $this->assertStringContainsString('View Test', $html);
        $this->assertStringContainsString('N° OS', $html);
        $this->assertNull($this->ci()->output->get_header('Location'));
    }

    public function testVisualizarRedirectsOnInvalidId(): void
    {
        $this->resetUriSegments('clientes/visualizar/abc');
        $this->callControllerRaw('Clientes', 'visualizar');

        $this->assertSame(
            site_url('mapos'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Item não pode ser encontrado, parâmetro não foi passado corretamente.',
            $this->ci()->session->userdata('error')
        );
    }

    public function testExcluirRejectsAMissingId(): void
    {
        $this->callControllerRaw('Clientes', 'excluir');

        $this->assertSame(
            site_url('clientes/gerenciar/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Erro ao tentar excluir cliente.',
            $this->ci()->session->userdata('error')
        );
    }

    public function testExcluirRemovesTheClientAndLinkedOsAndVendas(): void
    {
        $id = $this->installClient('del@exemplo.com', 'Del');
        $osId = $this->installServiceOrder($id);
        $vendaId = $this->installVenda($id);

        $_POST['id'] = (string) $id;
        $this->callControllerRaw('Clientes', 'excluir');
        unset($_POST['id']);

        $this->assertSame(
            site_url('clientes/gerenciar/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Cliente excluido com sucesso!',
            $this->ci()->session->userdata('success')
        );

        $this->assertSame(0, (int) $this->ci()->db->where('idClientes', $id)->count_all_results('clientes'));
        $this->assertSame(0, (int) $this->ci()->db->where('idOs', $osId)->count_all_results('os'));
        $this->assertSame(0, (int) $this->ci()->db->where('vendas_id', $vendaId)->count_all_results('itens_de_vendas'));
        $this->assertSame(0, (int) $this->ci()->db->where('idVendas', $vendaId)->count_all_results('vendas'));
    }

    private function installClient(
        string $email,
        string $name = 'Cliente Teste',
        string $password = 'antiga',
        string $documento = '12345678909'
    ): int {
        $this->ci()->db->insert('clientes', [
            'nomeCliente' => $name,
            'contato' => 'Contato',
            'pessoa_fisica' => 1,
            'documento' => $documento,
            'telefone' => '00000000',
            'celular' => '000000000',
            'email' => $email,
            'senha' => password_hash($password, PASSWORD_DEFAULT),
            'rua' => 'Rua',
            'numero' => '1',
            'bairro' => 'Centro',
            'cidade' => 'Cidade',
            'estado' => 'SP',
            'cep' => '00000000',
            'dataCadastro' => date('Y-m-d'),
            'fornecedor' => 0,
        ]);

        return (int) $this->ci()->db->insert_id('clientes');
    }

    private function clientPassword(string $email): string
    {
        return (string) $this->ci()->db
            ->select('senha')
            ->where('email', $email)
            ->limit(1)
            ->get('clientes')
            ->row('senha');
    }

    private function firstUserId(): int
    {
        $id = (int) $this->ci()->db
            ->select('idUsuarios')
            ->order_by('idUsuarios', 'ASC')
            ->limit(1)
            ->get('usuarios')
            ->row('idUsuarios');

        $this->assertGreaterThan(0, $id, 'Nenhuma conta em `usuarios` para ser o técnico da OS.');

        return $id;
    }

    private function installServiceOrder(int $clientId): int
    {
        $this->ci()->db->insert('os', [
            'dataInicial' => date('Y-m-d'),
            'status' => 'Aberto',
            'valorTotal' => 0,
            'clientes_id' => $clientId,
            'usuarios_id' => $this->firstUserId(),
            'faturado' => 0,
        ]);

        return (int) $this->ci()->db->insert_id('os');
    }

    private function installVenda(int $clientId): int
    {
        $this->ci()->db->insert('vendas', [
            'dataVenda' => date('Y-m-d'),
            'valorTotal' => 0,
            'desconto' => 0,
            'faturado' => 0,
            'clientes_id' => $clientId,
            'usuarios_id' => $this->firstUserId(),
            'valor_desconto' => 0,
        ]);

        $vendaId = (int) $this->ci()->db->insert_id('vendas');

        $produtoId = $this->installProduto();
        $this->ci()->db->insert('itens_de_vendas', [
            'vendas_id' => $vendaId,
            'quantidade' => 1,
            'subTotal' => 0,
            'preco' => 0,
            'produtos_id' => $produtoId,
        ]);

        return $vendaId;
    }

    private function installProduto(): int
    {
        $this->ci()->db->insert('produtos', [
            'descricao' => 'Produto Teste',
            'unidade' => 'UN',
            'precoCompra' => 10.00,
            'precoVenda' => 15.00,
            'estoque' => 100,
            'estoqueMinimo' => 1,
        ]);

        return (int) $this->ci()->db->insert_id('produtos');
    }
}
