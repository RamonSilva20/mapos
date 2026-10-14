<?php

/**
 * Busca global Ctrl+K e atalhos F1–F4 (#2850).
 */
final class BuscaGlobalTest extends MaposTestCase
{
    private function modelo(): Mapos_model
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, documento TEXT, telefone TEXT, celular TEXT)');
        $this->db->query('CREATE TABLE os (idOs INTEGER PRIMARY KEY AUTOINCREMENT, clientes_id INTEGER, status TEXT, descricaoProduto TEXT)');
        $this->db->query('CREATE TABLE produtos (idProdutos INTEGER PRIMARY KEY AUTOINCREMENT, codDeBarra TEXT, descricao TEXT, precoVenda REAL, estoque REAL)');
        $this->db->query('CREATE TABLE servicos (idServicos INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, preco REAL)');
        $this->db->query("INSERT INTO clientes (nomeCliente, documento, celular) VALUES ('Ana Souza', '123', '9999'), ('Bruno Lima', '456', '8888'), ('100% Real', '789', '7777')");
        $this->db->query("INSERT INTO os (clientes_id, status, descricaoProduto) VALUES (1, 'Aberto', 'Notebook Dell'), (2, 'Finalizado', 'Impressora')");
        $this->db->query("INSERT INTO produtos (codDeBarra, descricao, precoVenda, estoque) VALUES ('789', 'Cabo HDMI', 25.9, 4)");
        $this->db->query("INSERT INTO servicos (nome, preco) VALUES ('Formatação', 120)");

        require_once APPPATH . 'models/Mapos_model.php';
        $modelo = $this->makeInstance(Mapos_model::class);
        $this->setPrivateProperty($modelo, 'db', $this->db);

        return $modelo;
    }

    public function testBuscaPorNomeDocumentoOsProdutoEServico(): void
    {
        $modelo = $this->modelo();

        $r = $modelo->buscaRapida('ana');
        $this->assertSame(['Ana Souza'], array_column($r['clientes'], 'nomeCliente'));
        $this->assertSame([1], array_map('intval', array_column($r['os'], 'idOs')), 'A OS vem pelo nome do cliente.');
        $this->assertSame('Ana Souza', $r['os'][0]->nomeCliente);

        $this->assertSame(['Cabo HDMI'], array_column($modelo->buscaRapida('hdmi')['produtos'], 'descricao'));
        $this->assertSame(['Formatação'], array_column($modelo->buscaRapida('format')['servicos'], 'nome'));
        $this->assertSame(['Notebook Dell'], array_column($this->db->where('idOs', $modelo->buscaRapida('notebook')['os'][0]->idOs)->get('os')->result(), 'descricaoProduto'));
    }

    public function testLimitaOsResultadosPorGrupo(): void
    {
        $modelo = $this->modelo();
        for ($i = 0; $i < 10; $i++) {
            $this->db->query("INSERT INTO clientes (nomeCliente) VALUES ('Maria $i')");
        }

        $this->assertCount(5, $modelo->buscaRapida('maria')['clientes']);
        $this->assertCount(3, $modelo->buscaRapida('maria', 3)['clientes']);
    }

    public function testPorcentoELiteralNaBusca(): void
    {
        $modelo = $this->modelo();

        $this->assertSame(['100% Real'], array_column($modelo->buscaRapida('100%')['clientes'], 'nomeCliente'));
        $this->assertSame([], $modelo->buscaRapida('%')['servicos'], '% não casa com tudo.');
        $this->assertSame(['100% Real'], array_column($modelo->buscaRapida('%')['clientes'], 'nomeCliente'));
    }

    public function testEndpointRespeitaAsPermissoesEOMinimoDeLetras(): void
    {
        $controller = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');
        preg_match('/function buscaRapida\(\).*?\n    }\n/s', $controller, $trecho);

        $this->assertNotEmpty($trecho);
        $this->assertStringContainsString('mb_strlen($termo) >= 2', $trecho[0]);
        foreach (['vCliente', 'vOs', 'vProduto', 'vServico'] as $permissao) {
            $this->assertStringContainsString("permite('$permissao')", $trecho[0]);
        }
        $this->assertStringContainsString('JSON_HEX_TAG', $trecho[0]);
        $this->assertStringNotContainsString('servicos/editar', $trecho[0], 'Quem só vê serviços não pode abrir a edição.');
        $this->assertStringContainsString("site_url('servicos') . '?pesquisa='", $trecho[0]);

        $mapa = (string) file_get_contents(APPPATH . 'config/permissions_map.php');
        $this->assertStringContainsString("'buscaRapida' => '*'", $mapa);
    }

    public function testAtalhosSaoSoDasTelasDoMenuVisivel(): void
    {
        $menu = [
            ['label' => 'Início', 'url' => ''],
            ['label' => 'Clientes', 'url' => 'clientes'],
            ['label' => 'Serviços', 'url' => 'servicos'],
            ['label' => 'Ordens de Serviço', 'url' => 'os'],
            ['label' => 'Vendas', 'url' => 'vendas'],
            ['label' => 'Config', 'itens' => [['label' => 'Usuários', 'url' => 'usuarios']]],
        ];

        $this->assertSame(
            ['F1' => 'http://x/clientes', 'F3' => 'http://x/servicos', 'F4' => 'http://x/os'],
            layoutAtalhos($menu, static fn ($caminho) => 'http://x/' . $caminho),
            'Produtos não está no menu: sem F2.'
        );
        $this->assertSame([], layoutAtalhos([], static fn ($c) => $c));
    }

    public function testPaletaEstaNoLayoutComOsGatilhos(): void
    {
        $rodape = (string) file_get_contents(APPPATH . 'views/tema/rodape.php');
        $this->assertStringContainsString("load->view('tema/busca')", $rodape);

        $busca = (string) file_get_contents(APPPATH . 'views/tema/busca.php');
        $this->assertStringContainsString("js_module('layout/busca')", $busca);
        $this->assertStringContainsString("page_data('layout-atalhos'", $busca);
        $this->assertStringContainsString('role="combobox"', $busca);
        $this->assertStringContainsString('aria-live="polite"', $busca);

        $topbar = (string) file_get_contents(APPPATH . 'views/tema/conteudo.php');
        $this->assertStringContainsString("'data-busca-abrir' => true", $topbar, 'Botão de busca do celular.');
    }
}
