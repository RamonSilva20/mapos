<?php

namespace Tests\Support\Database;

/**
 * Fixtures de cliente, de OS e de venda, comuns aos testes de controller.
 *
 * O `installClient()` insere o conjunto completo de colunas (o dos testes de
 * Clientes), que é superconjunto do que Servicos e Mine precisam: as colunas
 * extras são nulas ou têm default, e as três versões anteriores divergiam só no
 * subconjunto. O retorno é sempre o `idClientes`; quem precisa do e-mail usa
 * `clientId()`/`clientPassword()` para reencontrar a linha.
 *
 * É a regra de "um fato mora em um só lugar" do AGENTS.md aplicada ao schema:
 * uma mudança em `clientes`, `os` ou `vendas` é corrigida aqui, uma vez, e não
 * em cada teste de controller.
 */
trait ClientFixtures
{
    private function installClient(
        string $email = 'cliente@exemplo.com',
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

    private function clientId(string $email): int
    {
        return (int) $this->ci()->db
            ->select('idClientes')
            ->where('email', $email)
            ->limit(1)
            ->get('clientes')
            ->row('idClientes');
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

    /**
     * O `usuarios_id` da OS é obrigatório e tem chave estrangeira, então a OS não
     * pode ser criada sem um técnico. A conta administrativa que a seed
     * `application/database/seeds/Usuarios.php` grava é a de menor id.
     */
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

    /**
     * A venda recebe o `$productId` do chamador, e não instala o produto: o
     * `itens_de_vendas.produtos_id` tem chave estrangeira, então o produto
     * precisa existir antes, mas quem sabe criá-lo é o `ProductFixtures`. Aqui
     * só o vínculo é montado.
     */
    private function installVenda(int $clientId, int $productId): int
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

        $this->ci()->db->insert('itens_de_vendas', [
            'vendas_id' => $vendaId,
            'quantidade' => 1,
            'subTotal' => 0,
            'preco' => 0,
            'produtos_id' => $productId,
        ]);

        return $vendaId;
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
}
