<?php

require_once APPPATH . 'models/Clientes_model.php';

/**
 * Padrões das listagens (#2852): filtros da URL, paginação que os mantém e o
 * filtro de clientes no model.
 */
final class ListagemTest extends MaposTestCase
{
    private const PERMITIDOS = ['pesquisa' => 'texto', 'tipo' => ['cliente', 'fornecedor']];

    public function testFiltrosValidosDaQueryString(): void
    {
        $this->assertSame(
            ['pesquisa' => 'ana souza', 'tipo' => 'fornecedor'],
            listagemFiltros(self::PERMITIDOS, ['pesquisa' => "  ana souza\n", 'tipo' => 'fornecedor', 'outro' => 'x'])
        );
    }

    public function testDescartaValoresInvalidos(): void
    {
        $this->assertSame([], listagemFiltros(self::PERMITIDOS, ['pesquisa' => '   ', 'tipo' => 'admin']));
        $this->assertSame([], listagemFiltros(self::PERMITIDOS, ['pesquisa' => ['a'], 'tipo' => ['cliente']]));
        $this->assertSame([], listagemFiltros(self::PERMITIDOS, null));
        $this->assertSame(100, mb_strlen(listagemFiltros(self::PERMITIDOS, ['pesquisa' => str_repeat('é', 300)])['pesquisa']));
    }

    public function testQueryStringDosFiltros(): void
    {
        $this->assertSame('', listagemQuery([]));
        $this->assertSame('?pesquisa=ana%20%26%20cia&tipo=cliente', listagemQuery(['pesquisa' => 'ana & cia', 'tipo' => 'cliente']));
    }

    public function testPaginacaoMantemOsFiltros(): void
    {
        $props = paginacaoProps(['base_url' => 'http://x/clientes/gerenciar', 'total_rows' => 25, 'per_page' => 10, 'offset' => 10, 'params' => ['pesquisa' => 'ana {offset}', 'tipo' => 'cliente']]);

        $this->assertSame('http://x/clientes/gerenciar/{offset}?pesquisa=ana%20%7Boffset%7D&tipo=cliente', $props['url']);
        $this->assertSame('http://x/clientes/gerenciar/20?pesquisa=ana%20%7Boffset%7D&tipo=cliente', componentePaginaUrl($props['url'], 3, 10));
        $this->assertSame(2, $props['current']);
    }

    public function testPaginacaoComOffsetNaQueryEFiltros(): void
    {
        $props = paginacaoProps(['base_url' => 'http://x/financeiro', 'total_rows' => 25, 'per_page' => 10, 'offset' => 0, 'query_string' => 'per_page', 'params' => ['periodo' => 'mes']]);

        $this->assertSame('http://x/financeiro?periodo=mes&per_page={offset}', $props['url']);
    }

    public function testPaginacaoSemFiltrosNaoMuda(): void
    {
        $this->assertSame('http://x/clientes/gerenciar/{offset}', paginacaoProps(['base_url' => 'http://x/clientes/gerenciar', 'total_rows' => 5, 'params' => ['pesquisa' => '']])['url']);
    }

    private function clientes(): Clientes_model
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, documento TEXT, email TEXT, telefone TEXT, celular TEXT, fornecedor INTEGER)');
        $linhas = [
            ['Ana Souza', '111', 'ana@x.com', '1111', '', 0],
            ['Bruno Ana Lima', '222', 'bruno@x.com', '2222', '', 1],
            ['Carla', '333', 'carla@x.com', '3333', '9999', 0],
            ['Diego', '444', 'diego@x.com', '4444', '', 1],
        ];
        foreach ($linhas as $l) {
            $this->db->query('INSERT INTO clientes (nomeCliente, documento, email, telefone, celular, fornecedor) VALUES (?, ?, ?, ?, ?, ?)', $l);
        }

        $model = $this->makeInstance(Clientes_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        return $model;
    }

    public function testModelFiltraEContaComOsMesmosFiltros(): void
    {
        $model = $this->clientes();

        $this->assertSame(4, $model->contar([]));
        $this->assertSame(['Diego', 'Carla', 'Bruno Ana Lima', 'Ana Souza'], array_column($model->listar([], 10, 0), 'nomeCliente'));

        // O OR da pesquisa fica entre parênteses: o tipo continua valendo.
        $this->assertSame(['Bruno Ana Lima'], array_column($model->listar(['pesquisa' => 'Ana', 'tipo' => 'fornecedor'], 10, 0), 'nomeCliente'));
        $this->assertSame(1, $model->contar(['pesquisa' => 'Ana', 'tipo' => 'fornecedor']));
        $this->assertSame(2, $model->contar(['pesquisa' => 'Ana']));
        $this->assertSame(['Carla'], array_column($model->listar(['pesquisa' => '9999'], 10, 0), 'nomeCliente'));
        $this->assertSame(2, $model->contar(['tipo' => 'cliente']));

        $this->assertSame(['Bruno Ana Lima', 'Ana Souza'], array_column($model->listar([], 2, 2), 'nomeCliente'));
    }

    public function testPesquisaComCaracteresDeLikeNaoViraCuringa(): void
    {
        $model = $this->clientes();

        $this->assertSame(0, $model->contar(['pesquisa' => '%']));
        $this->assertSame(0, $model->contar(['pesquisa' => "' OR 1=1 --"]));
    }
}
