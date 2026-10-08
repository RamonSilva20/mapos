<?php

require_once APPPATH . 'models/Os_model.php';
require_once APPPATH . 'controllers/Os.php';

/**
 * Listagem de OS migrada (#2842): filtros, status visíveis por configuração,
 * total calculado no SQL e as regras de exclusão.
 */
final class OsListagemTest extends MaposTestCase
{
    private function model(): Os_model
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, documento TEXT)');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT)');
        $this->db->query('CREATE TABLE os (idOs INTEGER PRIMARY KEY AUTOINCREMENT, dataInicial TEXT, dataFinal TEXT, descricaoProduto TEXT, status TEXT, faturado INTEGER DEFAULT 0, desconto REAL, valor_desconto REAL, clientes_id INTEGER, usuarios_id INTEGER, lancamento INTEGER)');
        $this->db->query('CREATE TABLE produtos_os (idProdutos_os INTEGER PRIMARY KEY AUTOINCREMENT, os_id INTEGER, produtos_id INTEGER, quantidade INTEGER, preco REAL, subTotal REAL)');
        $this->db->query('CREATE TABLE servicos (idServicos INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, preco REAL)');
        $this->db->query('CREATE TABLE servicos_os (idServicos_os INTEGER PRIMARY KEY AUTOINCREMENT, os_id INTEGER, servicos_id INTEGER, quantidade REAL, preco REAL, subTotal REAL)');
        $this->db->query('CREATE TABLE lancamentos (idLancamentos INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT)');

        $this->db->query("INSERT INTO clientes (nomeCliente, documento) VALUES ('Ana Souza', '111.111.111-11'), ('Bruno 100% Lima', '222'), ('Carla', '333')");
        $this->db->query("INSERT INTO usuarios (nome) VALUES ('Técnico')");
        $this->db->query("INSERT INTO servicos (nome, preco) VALUES ('Formatação', 80)");

        $os = [
            // descricao, status, cliente, dataInicial, dataFinal, valor_desconto
            ['<p>Notebook <b>Dell</b></p>', 'Aberto', 1, '2026-10-01', '2026-10-05', 0],
            ['Celular Moto G', 'Orçamento', 2, '2026-10-02', '2026-10-10', 0],
            ['Impressora', 'Faturado', 1, '2026-09-01', '2026-09-03', 150],
            ['Notebook Acer', 'Cancelado', 3, '2026-10-03', '2026-10-04', 0],
        ];
        foreach ($os as $o) {
            $this->db->query('INSERT INTO os (descricaoProduto, status, clientes_id, usuarios_id, dataInicial, dataFinal, valor_desconto) VALUES (?, ?, ?, 1, ?, ?, ?)', [$o[0], $o[1], $o[2], $o[3], $o[4], $o[5]]);
        }

        // OS 1: produto 2 x 50 = 100 + serviço sem preço próprio (usa 80 do cadastro) x 2 = 160 → 260
        $this->db->query('INSERT INTO produtos_os (os_id, produtos_id, quantidade, preco, subTotal) VALUES (1, 1, 2, 50, 100)');
        $this->db->query('INSERT INTO servicos_os (os_id, servicos_id, quantidade, preco) VALUES (1, 1, 2, 0)');
        // OS 2: serviço com preço próprio e sem quantidade → conta 1
        $this->db->query('INSERT INTO servicos_os (os_id, servicos_id, quantidade, preco) VALUES (2, 1, 0, 45.5)');
        // OS 3: itens somam 200, mas o valor com desconto (150) vale
        $this->db->query('INSERT INTO produtos_os (os_id, produtos_id, quantidade, preco, subTotal) VALUES (3, 1, 4, 50, 200)');

        $model = $this->makeInstance(Os_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        return $model;
    }

    private function ids(array $linhas): array
    {
        return array_map('intval', array_column($linhas, 'idOs'));
    }

    public function testListaTodasComTotalEClienteEmOrdemDecrescente(): void
    {
        $model = $this->model();
        $linhas = $model->listar([], 10, 0);

        $this->assertSame([4, 3, 2, 1], $this->ids($linhas));
        $this->assertSame(4, $model->contar([]));

        $porId = array_column($linhas, null, 'idOs');
        $this->assertEqualsWithDelta(260.0, $porId[1]->total, 0.001);
        $this->assertEqualsWithDelta(45.5, $porId[2]->total, 0.001);
        $this->assertEqualsWithDelta(150.0, $porId[3]->total, 0.001);
        $this->assertEqualsWithDelta(0.0, $porId[4]->total, 0.001);
        $this->assertSame('Ana Souza', $porId[1]->nomeCliente);
        $this->assertSame('Técnico', $porId[1]->responsavel);
    }

    public function testPesquisaPorClienteDocumentoEquipamentoENumero(): void
    {
        $model = $this->model();

        $this->assertSame([3, 1], $this->ids($model->listar(['pesquisa' => 'Ana'], 10, 0)));
        $this->assertSame([3, 1], $this->ids($model->listar(['pesquisa' => '111.111'], 10, 0)));
        $this->assertSame([4, 1], $this->ids($model->listar(['pesquisa' => 'Notebook'], 10, 0)));
        $this->assertSame([2], $this->ids($model->listar(['pesquisa' => '2'], 10, 0)));
        // % e _ da busca são literais, não curingas.
        $this->assertSame([2], $this->ids($model->listar(['pesquisa' => '100%'], 10, 0)));
        $this->assertSame([], $model->listar(['pesquisa' => "x' OR '1'='1"], 10, 0));
        $this->assertSame(2, $model->contar(['pesquisa' => 'Notebook']));
    }

    public function testNumeroDaOsEntraNaPesquisaSemAnularOsOutrosFiltros(): void
    {
        $model = $this->model();

        // "3" casa com a OS 3 (Faturado); com o filtro de status Aberto, nada.
        $this->assertContains(3, $this->ids($model->listar(['pesquisa' => '3'], 10, 0)));
        $this->assertSame([], $model->listar(['pesquisa' => '3', 'status' => 'Aberto'], 10, 0));
    }

    public function testFiltrosDeStatusEDatas(): void
    {
        $model = $this->model();

        $this->assertSame([2], $this->ids($model->listar(['status' => 'Orçamento'], 10, 0)));
        $this->assertSame([4, 2, 1], $this->ids($model->listar(['de' => '2026-10-01'], 10, 0)));
        $this->assertSame([4, 3, 1], $this->ids($model->listar(['ate' => '2026-10-05'], 10, 0)));
        $this->assertSame(2, $model->contar(['de' => '2026-10-01', 'ate' => '2026-10-05']));
    }

    public function testStatusVisiveisSoValemSemPesquisaNemStatus(): void
    {
        $model = $this->model();
        $visiveis = ['Aberto', 'Orçamento'];

        $this->assertSame([2, 1], $this->ids($model->listar([], 10, 0, $visiveis)));
        $this->assertSame(2, $model->contar([], $visiveis));
        // Com pesquisa ou status explícito, a lista configurada não restringe.
        $this->assertSame([4, 1], $this->ids($model->listar(['pesquisa' => 'Notebook'], 10, 0, $visiveis)));
        $this->assertSame([4], $this->ids($model->listar(['status' => 'Cancelado'], 10, 0, $visiveis)));
    }

    public function testPaginacaoUsaLimiteEOffsetComOsFiltros(): void
    {
        $model = $this->model();

        $this->assertSame([4, 3], $this->ids($model->listar([], 2, 0)));
        $this->assertSame([2, 1], $this->ids($model->listar([], 2, 2)));
        $this->assertSame([1], $this->ids($model->listar(['pesquisa' => 'Ana'], 1, 1)));
    }

    public function testExcluirFaturaPeloVinculoEPelasDescricoes(): void
    {
        $model = $this->model();
        foreach (['Fatura de OS Nº: 3 ', 'Fatura de OS Nº: 3', 'Fatura de OS - #3', 'Fatura de OS Nº: 30 ', 'Outro lançamento'] as $descricao) {
            $this->db->query('INSERT INTO lancamentos (descricao) VALUES (?)', [$descricao]);
        }

        $model->excluirFatura(3, 5);

        $restantes = array_column($this->db->query('SELECT descricao FROM lancamentos ORDER BY idLancamentos')->result_array(), 'descricao');
        $this->assertSame(['Fatura de OS Nº: 30 '], $restantes);
    }

    public function testFiltrosDaListagemSaoOsStatusDeOs(): void
    {
        $this->assertSame(array_keys(OS_STATUS_VARIANTES), Os::FILTROS['status']);
        $this->assertSame(['pesquisa', 'status', 'de', 'ate'], array_keys(Os::FILTROS));
    }

    public function testTextoCurtoTiraHtmlECorta(): void
    {
        $this->assertSame('Notebook Dell', osTextoCurto('<p>Notebook <b>Dell</b></p>'));
        $this->assertSame('Tela & teclado', osTextoCurto('Tela &amp; teclado'));
        $this->assertSame('linha 1 linha 2', osTextoCurto("linha 1<br>linha   2\n"));
        $this->assertSame('abcd…', osTextoCurto('abcdefghij', 5));
        $this->assertSame('', osTextoCurto(null));
        $this->assertSame('alert(1)', osTextoCurto('<script>alert(1)</script>'));
    }

    public function testEditavelSegueARegraDoIsEditable(): void
    {
        $aberta = (object) ['status' => 'Aberto', 'faturado' => 0];
        $faturada = (object) ['status' => 'Faturado', 'faturado' => 1];
        $cancelada = (object) ['status' => 'Cancelado', 'faturado' => 0];
        $marcadaFaturada = (object) ['status' => 'Finalizado', 'faturado' => '1'];

        $this->assertTrue(osEditavel($aberta, true, false));
        $this->assertFalse(osEditavel($aberta, false, true));
        $this->assertFalse(osEditavel($faturada, true, false));
        $this->assertFalse(osEditavel($cancelada, true, false));
        $this->assertFalse(osEditavel($marcadaFaturada, true, false));
        $this->assertTrue(osEditavel($faturada, true, true));
    }

    public function testStatusVisiveisDaConfiguracao(): void
    {
        $this->assertSame(['Aberto', 'Orçamento'], osStatusVisiveis('["Aberto","Orçamento"]'));
        $this->assertNull(osStatusVisiveis('[]'));
        $this->assertNull(osStatusVisiveis('não é json'));
        $this->assertNull(osStatusVisiveis(null));
        $this->assertSame(['Aberto'], osStatusVisiveis('["Aberto", "", 3]'));
    }

    public function testArquivosDosAnexosSoDentroDaPastaDeAnexos(): void
    {
        $raiz = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mapos-anexos-' . bin2hex(random_bytes(4));
        $pasta = $raiz . DIRECTORY_SEPARATOR . '10-2026' . DIRECTORY_SEPARATOR . 'OS-1';
        mkdir($pasta . DIRECTORY_SEPARATOR . 'thumbs', 0777, true);
        file_put_contents($pasta . DIRECTORY_SEPARATOR . 'a.jpg', 'x');
        file_put_contents($pasta . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . 'thumb_a.jpg', 'x');
        $fora = tempnam(sys_get_temp_dir(), 'fora');

        $anexos = [
            (object) ['path' => $pasta, 'anexo' => 'a.jpg', 'thumb' => 'thumb_a.jpg'],
            // path adulterado apontando para fora da pasta de anexos
            (object) ['path' => dirname($fora), 'anexo' => basename($fora), 'thumb' => ''],
            // nome com ../ é reduzido ao basename
            (object) ['path' => $pasta, 'anexo' => '../../../' . basename($fora), 'thumb' => null],
            // arquivo que não existe mais
            (object) ['path' => $pasta, 'anexo' => 'sumiu.pdf', 'thumb' => ''],
        ];

        $arquivos = osArquivosDosAnexos($anexos, $raiz);

        $this->assertSame([
            realpath($pasta . DIRECTORY_SEPARATOR . 'a.jpg'),
            realpath($pasta . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . 'thumb_a.jpg'),
        ], $arquivos);
        $this->assertSame([], osArquivosDosAnexos($anexos, $raiz . '-nao-existe'));

        unlink($fora);
        unlink($pasta . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . 'thumb_a.jpg');
        unlink($pasta . DIRECTORY_SEPARATOR . 'a.jpg');
        rmdir($pasta . DIRECTORY_SEPARATOR . 'thumbs');
        rmdir($pasta);
        rmdir(dirname($pasta));
        rmdir($raiz);
    }

    public function testDescricoesDaFatura(): void
    {
        $this->assertSame(['Fatura de OS Nº: 7 ', 'Fatura de OS Nº: 7', 'Fatura de OS - #7'], osDescricoesDaFatura(7));
    }

    /**
     * A listagem segue o padrão das telas migradas e o excluir limpa tudo o que
     * é da OS.
     */
    public function testControllerEViewSeguemOPadrao(): void
    {
        $controller = (string) file_get_contents(APPPATH . 'controllers/Os.php');
        preg_match('/public function gerenciar\(\).*?\n    }\n/s', $controller, $gerenciar);
        preg_match('/public function excluir\(\).*?\n    }\n/s', $controller, $excluir);

        $this->assertNotEmpty($gerenciar);
        $this->assertStringContainsString("\$this->data['legacy_assets'] = false;", $gerenciar[0]);
        $this->assertStringContainsString("'Nova OS'", $gerenciar[0]);
        $this->assertNotEmpty($excluir);
        $this->assertStringContainsString("delete('anotacoes_os', 'os_id', \$id)", $excluir[0]);
        $this->assertStringContainsString('osArquivosDosAnexos(', $excluir[0]);
        $this->assertStringContainsString('excluirFatura(', $excluir[0]);
        $this->assertStringNotContainsString('${', $excluir[0]);

        $view = (string) file_get_contents(APPPATH . 'views/os/os.php');
        $this->assertStringContainsString("component('modal-confirm'", $view);
        $this->assertStringContainsString('osStatusPill(', $view);
        $this->assertStringNotContainsString('excluir_notificacao', $view);
        $this->assertStringNotContainsString('<script', $view);
        $this->assertStringNotContainsString('bx-', $view);
    }
}
