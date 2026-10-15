<?php

namespace Tests\Support\Database;

/**
 * Fixtures de produto e dos vínculos que o tornam excluível em cascata.
 *
 * O `installProduct()` insere o conjunto de colunas não nulas de `produtos`; o
 * `installProdutosOs()` cria o vínculo que a chave estrangeira de `produtos_os`
 * exige, para que o teste de exclusão exercite a cascata de verdade e não um
 * produto solto. O vínculo de venda vem do `ClientFixtures::installVenda()`, que
 * já monta o `itens_de_vendas` e por isso não se repete aqui.
 *
 * É a regra de "um fato mora em um só lugar" do AGENTS.md aplicada ao schema:
 * uma mudança em `produtos`, `produtos_os` ou `itens_de_vendas` é corrigida
 * aqui, uma vez, e não em cada teste de controller que precise dela.
 */
trait ProductFixtures
{
    private function installProduct(
        string $description = 'Produto Teste',
        string $unit = 'UN',
        string $precoCompra = '10',
        string $precoVenda = '15',
        string $estoque = '100',
        string $estoqueMinimo = '1'
    ): int {
        $this->ci()->db->insert('produtos', [
            'descricao' => $description,
            'unidade' => $unit,
            'precoCompra' => $precoCompra,
            'precoVenda' => $precoVenda,
            'estoque' => $estoque,
            'estoqueMinimo' => $estoqueMinimo,
        ]);

        return (int) $this->ci()->db->insert_id('produtos');
    }

    private function installProdutosOs(int $osId, int $productId): void
    {
        $this->ci()->db->insert('produtos_os', [
            'os_id' => $osId,
            'produtos_id' => $productId,
            'quantidade' => 1,
            'preco' => '15',
            'subTotal' => '15',
        ]);
    }
}
