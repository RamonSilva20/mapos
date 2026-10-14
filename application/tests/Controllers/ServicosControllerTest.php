<?php

namespace Tests\Controllers;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ControllerTestCase;
use Tests\Support\Database\ClientFixtures;
use Tests\Support\Transaction\TransactsDatabase;

/**
 * Testa o CRUD de serviços: gerenciar, adicionar, editar e excluir.
 *
 * Os dados de serviços são criados dentro dos casos. Os redirects saem por
 * respond_redirect() (sem exit), então são verificáveis pelo cabeçalho Location
 * no CI_Output.
 *
 * O que não é coberto aqui, e por quê, está em AGENTS.md, na seção "Writing a
 * controller test": token CSRF inválido não é testável porque
 * Security::csrf_verify() chama show_error(403) e encerra o processo do PHPUnit.
 */
final class ServicosControllerTest extends ControllerTestCase
{
    use ClientFixtures;
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
        ];
    }

    #[DataProvider('permissionDenials')]
    public function testGerenciarAdicionarEditarAndExcluirDenyWithoutPermission(string $method): void
    {
        if ($method === 'editar') {
            $id = $this->installService('Temporario');
            $this->resetUriSegments('servicos/editar/' . $id);
        }

        $this->loginAs(999999);

        $this->callControllerRaw('Servicos', $method);

        $this->assertSame(
            base_url(),
            $this->ci()->output->get_header('Location'),
            'Sem permissão o usuário deve ser redirecionado para base_url().'
        );
        $expected = match ($method) {
            'gerenciar' => 'Você não tem permissão para visualizar serviços.',
            'adicionar' => 'Você não tem permissão para adicionar serviços.',
            'editar' => 'Você não tem permissão para editar serviços.',
            'excluir' => 'Você não tem permissão para excluir serviços.',
            default => 'Você não tem permissão para visualizar serviços.',
        };
        $this->assertSame($expected, $this->ci()->session->userdata('error'));
    }

    public function testGerenciarRendersTheServiceList(): void
    {
        $this->installService('Formatação', 'Formata o computador');
        $this->installService('Backup', 'Copia os arquivos');

        $html = $this->callControllerRaw('Servicos', 'gerenciar');

        $this->assertStringContainsString('Formatação', $html);
        $this->assertStringContainsString('Backup', $html);
        $this->assertStringContainsString('tabela', $html);
    }

    public function testGerenciarFiltersBySearchTerm(): void
    {
        // Nomes que não colidem com rótulo de menu: o layout também é renderizado,
        // e um "Backup" da barra lateral passaria na asserção de ausência.
        $this->installService('AlfaUnico123', 'Formata o computador');
        $this->installService('BetaUnico456', 'Copia os arquivos');

        $_GET['pesquisa'] = 'AlfaUnico123';
        $html = $this->callControllerRaw('Servicos', 'gerenciar');
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
            'nome vazio' => [['nome' => '', 'preco' => '150'], 'The Nome field is required.'],
            'preco vazio' => [['nome' => 'Serviço X', 'preco' => ''], 'The preco field is required.'],
        ];
    }

    #[DataProvider('adicionarRejections')]
    public function testAdicionarRejectsTheRequest(array $fields, string $expectedMessage): void
    {
        $this->postWithCsrfToken($fields);

        $body = $this->callControllerRaw('Servicos', 'adicionar');

        $this->assertNull($this->ci()->output->get_header('Location'));
        $this->assertStringContainsString($expectedMessage, $body);
        $this->assertSame(0, (int) $this->ci()->db->count_all_results('servicos'));
    }

    /**
     * Uma requisição sem corpo nenhum é diferente de uma com os campos vazios:
     * o `set_rules()` do CI3 desiste antes de registrar qualquer regra quando
     * não há POST e nenhum `validation_data` foi definido, então `run('servicos')`
     * devolve FALSE sem popular `_error_array`. O resultado observável não é uma
     * mensagem de campo obrigatório, é nenhuma mensagem — e nada gravado.
     */
    public function testAdicionarWithoutARequestBodyNeitherStoresNorReports(): void
    {
        $body = $this->callControllerRaw('Servicos', 'adicionar');

        $this->assertNull($this->ci()->output->get_header('Location'));
        $this->assertStringContainsString('id="formServico"', $body, 'O formulário vazio deveria ter sido renderizado.');
        $this->assertStringNotContainsString('form_error', $body);
        $this->assertSame(0, (int) $this->ci()->db->count_all_results('servicos'));
    }

    /**
     * O `preco` vai como dígito puro de propósito: o controller faz
     * `str_replace(',', '', $preco)`, que descarta a vírgula sem virar ponto, e
     * afirmar o formato com vírgula cristalizaria esse defeito no teste.
     */
    public function testAdicionarStoresTheService(): void
    {
        $this->postWithCsrfToken([
            'nome' => 'Formatação',
            'descricao' => 'Formata o computador',
            'preco' => '150',
        ]);

        $body = $this->callControllerRaw('Servicos', 'adicionar');

        $this->assertSame(
            site_url('servicos/adicionar/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame('', $body, 'Depois do redirect de sucesso o controller não deve renderizar a view.');
        $this->assertSame(
            'Serviço adicionado com sucesso!',
            $this->ci()->session->userdata('success')
        );

        $row = $this->ci()->db->where('nome', 'Formatação')->get('servicos')->row();
        $this->assertNotNull($row);
        $this->assertSame('Formata o computador', $row->descricao);
        $this->assertSame('150.00', (string) $row->preco);

        $log = $this->ci()->db->where('tarefa', 'Adicionou um serviço')->get('logs')->row();
        $this->assertNotNull($log, 'A inclusão deveria ter sido registrada no log.');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidEditIds(): array
    {
        return [
            'segmento não numérico' => ['abc'],
            'serviço inexistente' => ['9999999'],
        ];
    }

    #[DataProvider('invalidEditIds')]
    public function testEditarRedirectsOnInvalidOrUnknownId(string $segment): void
    {
        $this->resetUriSegments('servicos/editar/' . $segment);
        $this->callControllerRaw('Servicos', 'editar');

        $this->assertSame(
            site_url('servicos/gerenciar'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Serviço não encontrado ou parâmetro inválido.',
            $this->ci()->session->userdata('error')
        );
    }

    public function testEditarUpdatesTheService(): void
    {
        $id = $this->installService('Antigo', 'Antiga descrição', '100');

        $this->postWithCsrfToken([
            'idServicos' => (string) $id,
            'nome' => 'Novo',
            'descricao' => 'Nova descrição',
            'preco' => '200',
        ]);

        $this->resetUriSegments('servicos/editar/' . $id);
        $body = $this->callControllerRaw('Servicos', 'editar');

        $this->assertSame(
            site_url('servicos/editar/') . $id,
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame('', $body, 'Depois do redirect de sucesso o controller não deve renderizar a view.');

        $row = $this->ci()->db->where('idServicos', $id)->get('servicos')->row();
        $this->assertSame('Novo', $row->nome);
        $this->assertSame('Nova descrição', $row->descricao);
        $this->assertSame('200.00', (string) $row->preco);
    }

    /**
     * O id que autorizou a requisição é o do segmento da URL, não o do corpo.
     *
     * O guard valida `segment(3)`; antes, o UPDATE e o `log_info` liam
     * `idServicos` do POST, então uma URL autorizada para um serviço podia
     * gravar em outro. Este caso envia os dois divergentes e prova que a linha
     * endereçada pelo POST não é tocada.
     */
    public function testEditarUsesTheUrlIdAndNotThePostedId(): void
    {
        $id = $this->installService('Alvo da URL', 'Original', '100');
        $otherId = $this->installService('Alvo do POST', 'Intocado', '999');

        $this->postWithCsrfToken([
            'idServicos' => (string) $otherId,
            'nome' => 'Editado',
            'descricao' => 'Nova',
            'preco' => '200',
        ]);

        $this->resetUriSegments('servicos/editar/' . $id);
        $this->callControllerRaw('Servicos', 'editar');

        $other = $this->ci()->db->where('idServicos', $otherId)->get('servicos')->row();
        $this->assertSame('Alvo do POST', $other->nome, 'O id do POST não pode ser o alvo da edição.');
        $this->assertSame('Intocado', $other->descricao);
        $this->assertSame('999.00', (string) $other->preco);
    }

    public function testEditarRejectsInvalidPost(): void
    {
        $id = $this->installService('Antigo', 'Antiga descrição', '100');

        $this->postWithCsrfToken([
            'idServicos' => (string) $id,
            'nome' => '',
            'descricao' => 'Nova descrição',
            'preco' => '200',
        ]);

        $this->resetUriSegments('servicos/editar/' . $id);
        $html = $this->callControllerRaw('Servicos', 'editar');

        $this->assertNull($this->ci()->output->get_header('Location'));
        $this->assertStringContainsString('form_error', $html);

        $row = $this->ci()->db->where('idServicos', $id)->get('servicos')->row();
        $this->assertSame('Antigo', $row->nome, 'Um POST inválido não pode gravar.');
    }

    public function testExcluirRejectsAMissingId(): void
    {
        $this->callControllerRaw('Servicos', 'excluir');

        $this->assertSame(
            site_url('servicos/gerenciar/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Erro ao tentar excluir serviço.',
            $this->ci()->session->userdata('error')
        );
    }

    /**
     * Sem a linha em `servicos`, a segunda exclusão não afeta nada e o modelo
     * devolve false; o ramo de erro reverte a exclusão dos `servicos_os`. A
     * recusa é o par do sucesso logo abaixo.
     */
    public function testExcluirRejectsAnIdThatDoesNotExist(): void
    {
        $_POST['id'] = '9999999';
        $this->callControllerRaw('Servicos', 'excluir');
        unset($_POST['id']);

        $this->assertSame(
            site_url('servicos/gerenciar/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Erro ao tentar excluir serviço.',
            $this->ci()->session->userdata('error')
        );
    }

    public function testExcluirRemovesTheServiceAndLinkedOsItems(): void
    {
        $id = $this->installService('Para excluir', 'Será removido', '50');
        $osId = $this->installServiceOrder($this->installClient('cliente@exemplo.com'));
        $this->installServicosOs($osId, $id);

        $_POST['id'] = (string) $id;
        $this->callControllerRaw('Servicos', 'excluir');
        unset($_POST['id']);

        $this->assertSame(
            site_url('servicos/gerenciar/'),
            $this->ci()->output->get_header('Location')
        );
        $this->assertSame(
            'Serviço excluido com sucesso!',
            $this->ci()->session->userdata('success')
        );

        $this->assertSame(0, (int) $this->ci()->db->where('idServicos', $id)->count_all_results('servicos'));
        $this->assertSame(0, (int) $this->ci()->db->where('servicos_id', $id)->count_all_results('servicos_os'));
    }

    private function installService(
        string $name,
        string $description = 'Descrição',
        string $preco = '100'
    ): int {
        $this->ci()->db->insert('servicos', [
            'nome' => $name,
            'descricao' => $description,
            'preco' => $preco,
        ]);

        return (int) $this->ci()->db->insert_id('servicos');
    }

    private function installServicosOs(int $osId, int $servicoId): void
    {
        $this->ci()->db->insert('servicos_os', [
            'os_id' => $osId,
            'servicos_id' => $servicoId,
            'quantidade' => 1,
            'preco' => '50',
            'subTotal' => '50',
        ]);
    }
}
