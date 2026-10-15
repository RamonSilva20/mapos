<?php

namespace Tests\Controllers;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ControllerTestCase;
use Tests\Support\Database\ClientFixtures;
use Tests\Support\Database\ProductFixtures;
use Tests\Support\Transaction\TransactsDatabase;

/**
 * Testa o controller Produtos: gerenciar, adicionar, editar, visualizar,
 * excluir e atualizar estoque.
 *
 * Os dados de produtos são criados dentro dos casos. Os redirects usam
 * respond_redirect() (sem exit), então são verificáveis pelo cabeçalho Location
 * no CI_Output.
 *
 * O que não é coberto aqui, e por quê, está em AGENTS.md, na seção "Writing a
 * controller test": token CSRF inválido não é testável porque
 * Security::csrf_verify() chama show_error(403) e encerra o processo do PHPUnit.
 */
final class ProdutosControllerTest extends ControllerTestCase
{
    use ClientFixtures;
    use ProductFixtures;
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
            'visualizar sem permissão' => ['visualizar'],
            'excluir sem permissão' => ['excluir'],
            'atualizar estoque sem permissão' => ['atualizar_estoque'],
        ];
    }

    #[DataProvider('permissionDenials')]
    public function testPermissionDeniesAccess(string $method): void
    {
        if ($method === 'editar' || $method === 'visualizar') {
            $id = $this->installProduct('Temporario');
            $this->resetUriSegments('produtos/' . $method . '/' . $id);
        }

        $this->loginAs(999999);

        $this->callControllerRaw('Produtos', $method);

        $this->assertSame(
            base_url(),
            $this->ci()->output->get_header('Location'),
            'Sem permissão o usuário deve ser redirecionado para base_url().'
        );
        $expected = match ($method) {
            'gerenciar' => 'Você não tem permissão para visualizar produtos.',
            'adicionar' => 'Você não tem permissão para adicionar produtos.',
            'editar' => 'Você não tem permissão para editar produtos.',
            'visualizar' => 'Você não tem permissão para visualizar produtos.',
            'excluir' => 'Você não tem permissão para excluir produtos.',
            'atualizar_estoque' => 'Você não tem permissão para atualizar estoque de produtos.',
            default => 'Você não tem permissão para visualizar produtos.',
        };
        $this->assertSame($expected, $this->ci()->session->userdata('error'));
    }

    public function testGerenciarRendersTheProductList(): void
    {
        $this->installProduct('Teclado Mecanico');
        $this->installProduct('Mouse Sem Fio');

        $html = $this->callControllerRaw('Produtos', 'gerenciar');

        $this->assertStringContainsString('Teclado Mecanico', $html);
        $this->assertStringContainsString('Mouse Sem Fio', $html);
        $this->assertStringContainsString('tabela', $html);
    }

    public function testGerenciarFiltersBySearchTerm(): void
    {
        // Nomes que não colidem com rótulo de menu: o layout também é renderizado,
        // e o "Produtos" da barra lateral passaria na asserção de ausência.
        $this->installProduct('AlfaUnico123');
        $this->installProduct('BetaUnico456');

        $_GET['pesquisa'] = 'AlfaUnico123';
        $html = $this->callControllerRaw('Produtos', 'gerenciar');
        unset($_GET['pesquisa']);

        $this->assertStringContainsString('AlfaUnico123', $html);
        $this->assertStringNotContainsString('BetaUnico456', $html);
    }

    /**
     * @return array<string, array{array<string, string>, string}>
     */
    public static function adicionarRejections(): array
    {
        return [
            'descricao vazia' => [
                ['descricao' => '', 'unidade' => 'UN', 'precoCompra' => '10', 'precoVenda' => '15', 'estoque' => '5'],
                'The descricao field is required.',
            ],
            'unidade vazia' => [
                ['descricao' => 'Produto X', 'unidade' => '', 'precoCompra' => '10', 'precoVenda' => '15', 'estoque' => '5'],
                'The Unidade field is required.',
            ],
            'preco de compra vazio' => [
                ['descricao' => 'Produto X', 'unidade' => 'UN', 'precoCompra' => '', 'precoVenda' => '15', 'estoque' => '5'],
                'The Preço de Compra field is required.',
            ],
            'preco de venda vazio' => [
                ['descricao' => 'Produto X', 'unidade' => 'UN', 'precoCompra' => '10', 'precoVenda' => '', 'estoque' => '5'],
                'The Preço de Venda field is required.',
            ],
            'estoque vazio' => [
                ['descricao' => 'Produto X', 'unidade' => 'UN', 'precoCompra' => '10', 'precoVenda' => '15', 'estoque' => ''],
                'The Estoque field is required.',
            ],
        ];
    }

    #[DataProvider('adicionarRejections')]
    public function testAdicionarRejectsTheRequest(array $fields, string $expectedMessage): void
    {
        $this->postWithCsrfToken($fields);

        $body = $this->callControllerRaw('Produtos', 'adicionar');

        $this->assertNull($this->ci()->output->get_header('Location'));
        $this->assertStringContainsString($expectedMessage, $body);
        $this->assertSame(0, (int) $this->ci()->db->count_all_results('produtos'));
    }

    /**
     * Uma requisição sem corpo nenhum é diferente de uma com os campos vazios:
     * o `set_rules()` do CI3 desiste antes de registrar qualquer regra quando
     * não há POST e nenhum `validation_data` foi definido, então `run('produtos')`
     * devolve FALSE sem popular `_error_array`. O resultado observável não é uma
     * mensagem de campo obrigatório, é nenhuma mensagem — e nada gravado.
     */
    public function testAdicionarWithoutARequestBodyNeitherStoresNorReports(): void
    {
        $body = $this->callControllerRaw('Produtos', 'adicionar');

        $this->assertNull($this->ci()->output->get_header('Location'));
        $this->assertStringContainsString('id="formProduto"', $body, 'O formulário vazio deveria ter sido renderizado.');
        $this->assertStringNotContainsString('form_error', $body);
        $this->assertSame(0, (int) $this->ci()->db->count_all_results('produtos'));
    }

    public function testAdicionarStoresTheProduct(): void
    {
        $this->postWithCsrfToken([
            'codDeBarra' => '789123',
            'descricao' => 'Teclado Mecanico',
            'unidade' => 'UN',
            'precoCompra' => '100',
            'precoVenda' => '150',
            'estoque' => '7',
            'estoqueMinimo' => '2',
        ]);

        $body = $this->callControllerRaw('Produtos', 'adicionar');

        $this->assertSame(
            site_url('produtos/adicionar/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame('', $body, 'Depois do redirect de sucesso o controller não deve renderizar a view.');
        $this->assertSame(
            'Produto adicionado com sucesso!',
            $this->ci()->session->userdata('success')
        );

        $row = $this->ci()->db->where('descricao', 'Teclado Mecanico')->get('produtos')->row();
        $this->assertNotNull($row);
        $this->assertSame('789123', $row->codDeBarra);
        $this->assertSame('UN', $row->unidade);
        $this->assertSame('100.00', (string) $row->precoCompra);
        $this->assertSame('150.00', (string) $row->precoVenda);
        $this->assertSame('7', (string) $row->estoque);

        $log = $this->ci()->db->where('tarefa', 'Adicionou um produto')->get('logs')->row();
        $this->assertNotNull($log, 'A inclusão deveria ter sido registrada no log.');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidEditIds(): array
    {
        return [
            'segmento não numérico' => ['abc'],
            'produto inexistente' => ['9999999'],
        ];
    }

    #[DataProvider('invalidEditIds')]
    public function testEditarRedirectsOnInvalidOrUnknownId(string $segment): void
    {
        $this->resetUriSegments('produtos/editar/' . $segment);
        $this->callControllerRaw('Produtos', 'editar');

        $this->assertSame(
            site_url('produtos/gerenciar'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Produto não encontrado ou parâmetro inválido.',
            $this->ci()->session->userdata('error')
        );
    }

    public function testEditarUpdatesTheProduct(): void
    {
        $id = $this->installProduct('Antigo', 'UN', '10', '20', '5');

        $this->postWithCsrfToken([
            'idProdutos' => (string) $id,
            'descricao' => 'Novo',
            'unidade' => 'CX',
            'precoCompra' => '30',
            'precoVenda' => '40',
            'estoque' => '9',
            'estoqueMinimo' => '3',
        ]);

        $this->resetUriSegments('produtos/editar/' . $id);
        $body = $this->callControllerRaw('Produtos', 'editar');

        $this->assertSame(
            site_url('produtos/editar/') . $id,
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame('', $body, 'Depois do redirect de sucesso o controller não deve renderizar a view.');

        $row = $this->ci()->db->where('idProdutos', $id)->get('produtos')->row();
        $this->assertSame('Novo', $row->descricao);
        $this->assertSame('CX', $row->unidade);
        $this->assertSame('30.00', (string) $row->precoCompra);
        $this->assertSame('40.00', (string) $row->precoVenda);
        $this->assertSame('9', (string) $row->estoque);
    }

    /**
     * O id que autorizou a requisição é o do segmento da URL, não o do corpo.
     *
     * O guard valida `segment(3)`; antes, o UPDATE e o `log_info` liam
     * `idProdutos` do POST, então uma URL autorizada para um produto podia
     * gravar em outro. Este caso envia os dois divergentes e prova que a linha
     * endereçada pelo POST não é tocada.
     */
    public function testEditarUsesTheUrlIdAndNotThePostedId(): void
    {
        $id = $this->installProduct('Alvo da URL', 'UN', '10', '20', '5');
        $otherId = $this->installProduct('Alvo do POST', 'UN', '10', '20', '5');

        $this->postWithCsrfToken([
            'idProdutos' => (string) $otherId,
            'descricao' => 'Editado',
            'unidade' => 'CX',
            'precoCompra' => '30',
            'precoVenda' => '40',
            'estoque' => '9',
        ]);

        $this->resetUriSegments('produtos/editar/' . $id);
        $this->callControllerRaw('Produtos', 'editar');

        $target = $this->ci()->db->where('idProdutos', $id)->get('produtos')->row();
        $other = $this->ci()->db->where('idProdutos', $otherId)->get('produtos')->row();

        $this->assertSame('Editado', $target->descricao, 'A URL deve ser o alvo da edição.');
        $this->assertSame('Alvo do POST', $other->descricao, 'O id do POST não pode ser o alvo da edição.');
    }

    public function testEditarRejectsInvalidPost(): void
    {
        $id = $this->installProduct('Antigo', 'UN', '10', '20', '5');

        $this->postWithCsrfToken([
            'idProdutos' => (string) $id,
            'descricao' => '',
            'unidade' => 'UN',
            'precoCompra' => '30',
            'precoVenda' => '40',
            'estoque' => '9',
        ]);

        $this->resetUriSegments('produtos/editar/' . $id);
        $html = $this->callControllerRaw('Produtos', 'editar');

        $this->assertNull($this->ci()->output->get_header('Location'));
        $this->assertStringContainsString('form_error', $html);

        $row = $this->ci()->db->where('idProdutos', $id)->get('produtos')->row();
        $this->assertSame('Antigo', $row->descricao, 'Um POST inválido não pode gravar.');
    }

    public function testVisualizarRendersTheProductData(): void
    {
        $id = $this->installProduct('Produto Visivel', 'UN', '11', '22', '33');

        $this->resetUriSegments('produtos/visualizar/' . $id);
        $html = $this->callControllerRaw('Produtos', 'visualizar');

        $this->assertStringContainsString('Produto Visivel', $html);
        $this->assertStringContainsString('Estoque', $html);
        $this->assertNull($this->ci()->output->get_header('Location'));
    }

    public function testVisualizarRedirectsOnUnknownId(): void
    {
        $this->resetUriSegments('produtos/visualizar/9999999');
        $this->callControllerRaw('Produtos', 'visualizar');

        $this->assertSame(
            site_url('produtos/gerenciar'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame('Produto não encontrado.', $this->ci()->session->userdata('error'));
    }

    public function testExcluirRejectsAMissingId(): void
    {
        $this->callControllerRaw('Produtos', 'excluir');

        $this->assertSame(
            site_url('produtos/gerenciar/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Erro ao tentar excluir produto.',
            $this->ci()->session->userdata('error')
        );
    }

    /**
     * A exclusão de um id inexistente não afeta nada e o modelo devolve false,
     * então o ramo de erro grava a mensagem e volta para a lista. É o par de
     * recusa do sucesso logo abaixo.
     *
     * O `trans_rollback()` do ramo de erro não é verificado aqui, e não teria o
     * que reverter: um id sem linha em `produtos` não tem vínculos, e a chave
     * estrangeira garante isso.
     */
    public function testExcluirRejectsAnIdThatDoesNotExist(): void
    {
        $_POST['id'] = '9999999';
        $this->callControllerRaw('Produtos', 'excluir');
        unset($_POST['id']);

        $this->assertSame(
            site_url('produtos/gerenciar/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Erro ao tentar excluir produto.',
            $this->ci()->session->userdata('error')
        );
    }

    public function testExcluirRemovesTheProductAndLinkedOsAndVendaItems(): void
    {
        $id = $this->installProduct('Para excluir', 'UN', '10', '20', '5');
        $osId = $this->installServiceOrder($this->installClient('cliente@exemplo.com'));
        $this->installProdutosOs($osId, $id);
        $vendaId = $this->installVenda($this->installClient('venda@exemplo.com'), $id);

        $_POST['id'] = (string) $id;
        $this->callControllerRaw('Produtos', 'excluir');
        unset($_POST['id']);

        $this->assertSame(
            site_url('produtos/gerenciar/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Produto excluido com sucesso!',
            $this->ci()->session->userdata('success')
        );

        $this->assertSame(0, (int) $this->ci()->db->where('idProdutos', $id)->count_all_results('produtos'));
        $this->assertSame(0, (int) $this->ci()->db->where('produtos_id', $id)->count_all_results('produtos_os'));
        $this->assertSame(0, (int) $this->ci()->db->where('produtos_id', $id)->count_all_results('itens_de_vendas'));
    }

    public function testAtualizarEstoqueAddsToCurrentStock(): void
    {
        $id = $this->installProduct('Com estoque', 'UN', '10', '20', '100');

        $_POST['id'] = (string) $id;
        $_POST['estoque'] = '5';
        $_POST['estoqueAtual'] = '100';

        $this->callControllerRaw('Produtos', 'atualizar_estoque');
        unset($_POST['id'], $_POST['estoque'], $_POST['estoqueAtual']);

        $this->assertSame(
            site_url('produtos/visualizar/') . $id,
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Estoque de Produto atualizado com sucesso!',
            $this->ci()->session->userdata('success')
        );

        $row = $this->ci()->db->where('idProdutos', $id)->get('produtos')->row();
        $this->assertSame('105', (string) $row->estoque);
    }

    /**
     * Um valor não numérico ou vazio vira `TypeError` no `+` do PHP 8 e derruba
     * a requisição com 500. O guard recusa antes da soma e não grava nada.
     *
     * @return array<string, array{string, string}>
     */
    public static function nonNumericStock(): array
    {
        return [
            'quantidade vazia' => ['', '100'],
            'quantidade com letras' => ['abc', '100'],
            'estoque atual vazio' => ['5', ''],
        ];
    }

    #[DataProvider('nonNumericStock')]
    public function testAtualizarEstoqueRejectsNonNumericInput(string $estoque, string $estoqueAtual): void
    {
        $id = $this->installProduct('Com estoque', 'UN', '10', '20', '100');

        $_POST['id'] = (string) $id;
        $_POST['estoque'] = $estoque;
        $_POST['estoqueAtual'] = $estoqueAtual;

        $this->callControllerRaw('Produtos', 'atualizar_estoque');
        unset($_POST['id'], $_POST['estoque'], $_POST['estoqueAtual']);

        $this->assertSame(
            site_url('produtos/gerenciar/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame('Estoque inválido.', $this->ci()->session->userdata('error'));

        $row = $this->ci()->db->where('idProdutos', $id)->get('produtos')->row();
        $this->assertSame('100', (string) $row->estoque, 'Um estoque inválido não pode gravar.');
    }
}
