<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Permission::checkPermission.
 *
 * A biblioteca guarda as permissões do usuário em um JSON na tabela
 * permissoes e recebe a atividade pedida ('vOs', 'aOs', ...). O teste fixa o
 * comportamento da decisão e, quando dá, percorre o caminho real de carga a
 * partir do banco.
 */
final class PermissionTest extends MaposTestCase
{
    private const ATIVIDADES = ['vOs', 'aOs', 'eOs', 'dOs', 'rOs'];

    /**
     * Monta a biblioteca com as permissões já em memória, pulando o banco.
     *
     * É o estado em que loadPermission deixa a instância.
     */
    private function permissionCom(array $permissoes): Permission
    {
        $permission = $this->makeInstance(Permission::class);
        $this->setPrivateProperty($permission, 'permissions', [$permissoes]);

        return $permission;
    }

    public function testAtividadeConcedidaRetornaVerdadeiro(): void
    {
        $permission = $this->permissionCom(['vOs' => 1]);

        $this->assertTrue($permission->checkPermission(1, 'vOs'));
    }

    public function testAtividadeNegadaRetornaFalso(): void
    {
        $permission = $this->permissionCom(['vOs' => 0]);

        $this->assertFalse($permission->checkPermission(1, 'vOs'));
    }

    public function testAtividadeAusenteDaListaRetornaFalso(): void
    {
        $permission = $this->permissionCom(['aOs' => 1]);

        $this->assertFalse($permission->checkPermission(1, 'dOs'));
    }

    public function testPermissaoVaziaRetornaFalso(): void
    {
        $permission = $this->permissionCom([]);

        $this->assertFalse($permission->checkPermission(1, 'vOs'));
    }

    public function testSemIdDePermissaoRetornaFalso(): void
    {
        $permission = $this->permissionCom(['vOs' => 1]);

        $this->assertFalse($permission->checkPermission(null, 'vOs'));
    }

    public function testSemAtividadeRetornaFalso(): void
    {
        $permission = $this->permissionCom(['vOs' => 1]);

        $this->assertFalse($permission->checkPermission(1, null));
    }

    public function testIdDePermissaoInexistenteCausaTypeErrorEmVezDeNegar(): void
    {
        // knownIssue: loadPermission() faz count($array) sobre o retorno de
        // row_array(), que é null quando a consulta não traz linha. No PHP 8
        // count(null) é TypeError fatal, então um usuario com permissoes_id
        // apontando para uma linha removida quebra a tela em vez de receber a
        // negação. A correção é tratar o null como "sem permissões" (#2834).
        //
        // O teste fixa o comportamento atual para documentar a falha. Quando for
        // corrigida, deve passar a esperar false e a KnownIssue sai daqui.
        $this->db->query('CREATE TABLE permissoes (idPermissao INTEGER PRIMARY KEY, permissoes TEXT)');

        $permission = $this->makeInstance(Permission::class);
        $this->setPrivateProperty($permission, 'permissions', null);
        $this->setPrivateProperty($permission, 'CI', $this->fakeCiInstance($this->db));

        $this->expectException(TypeError::class);

        $permission->checkPermission(999, 'vOs');
    }

    public function testCarregaPermissoesDoBancoNaPrimeiraConsulta(): void
    {
        $this->db->query('CREATE TABLE permissoes (idPermissao INTEGER PRIMARY KEY, permissoes TEXT)');
        $this->db->query(
            'INSERT INTO permissoes (idPermissao, permissoes) VALUES (7, ?)',
            [json_encode(['vOs' => 1, 'aOs' => 1, 'eOs' => 0, 'dOs' => 0, 'rOs' => 1])]
        );

        $permission = $this->makeInstance(Permission::class);
        $this->setPrivateProperty($permission, 'permissions', null);
        $this->setPrivateProperty($permission, 'CI', $this->fakeCiInstance($this->db));

        // A carga é preguiçosa: a primeira chamada busca no banco.
        $this->assertTrue($permission->checkPermission(7, 'vOs'));
        $this->assertTrue($permission->checkPermission(7, 'rOs'));
        $this->assertFalse($permission->checkPermission(7, 'eOs'));
    }

    public function testAposACargaAsPermissoesFicamEmMemoria(): void
    {
        $this->db->query('CREATE TABLE permissoes (idPermissao INTEGER PRIMARY KEY, permissoes TEXT)');
        $this->db->query('INSERT INTO permissoes (idPermissao, permissoes) VALUES (1, ?)', [json_encode(['vOs' => 1])]);

        $permission = $this->makeInstance(Permission::class);
        $this->setPrivateProperty($permission, 'permissions', null);
        $this->setPrivateProperty($permission, 'CI', $this->fakeCiInstance($this->db));

        $this->assertTrue($permission->checkPermission(1, 'vOs'));

        // Sem a segunda consulta ao banco: apagando a conexão, a resposta
        // continua vindo porque o resultado ficou em memória.
        $this->setPrivateProperty($permission, 'CI', $this->fakeCiInstance(null));
        $this->assertTrue($permission->checkPermission(1, 'vOs'));
    }

    #[DataProvider('atividadesConhecidas')]
    public function testSomenteAtividadesConcedidasPassam(string $atividade): void
    {
        $permission = $this->permissionCom(['vOs' => 1, 'aOs' => 1, 'rOs' => 0]);

        $concedida = in_array($atividade, ['vOs', 'aOs'], true);

        $this->assertSame(
            $concedida,
            $permission->checkPermission(1, $atividade),
            sprintf('Atividade %s deveria ter retornado %s.', $atividade, $concedida ? 'true' : 'false')
        );
    }

    public static function atividadesConhecidas(): array
    {
        return array_map(static fn (string $a) => [$a], self::ATIVIDADES);
    }

    public function testValorDePermissaoDiferenteDeUmNaoConcede(): void
    {
        $permission = $this->permissionCom(['vOs' => 2, 'aOs' => true, 'rOs' => '1']);

        // A comparação é com == 1, então 2 não concede, e 1 e true concedem.
        $this->assertFalse($permission->checkPermission(1, 'vOs'));
        $this->assertTrue($permission->checkPermission(1, 'aOs'));
        $this->assertTrue($permission->checkPermission(1, 'rOs'));
    }
}
