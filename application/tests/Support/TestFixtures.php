<?php

namespace Tests\Support;

/**
 * Dados de referência que a suíte precisa para exercitar os controllers.
 *
 * A referência (permissão, configuração e usuário administrador) vem das seeds
 * canônicas em application/database/seeds/, as mesmas que o Tools::seed() roda em
 * produção. Nada é reimplementada aqui: quando uma seed muda, a suíte muda junto.
 *
 * Só os dois usuários extras — inativo e expirado — vivem neste arquivo, porque
 * existem para exercitar os caminhos de recusa do Login e não fazem sentido em
 * produção. Por isso não vão para application/database/seeds/.
 */
final class TestFixtures
{
    /**
     * @return string[] uma entrada por grupo de fixture instalado
     */
    public static function install(): array
    {
        return array_merge(
            self::runSeeds(['Permissoes', 'Usuarios', 'Configuracoes']),
            self::addExtraUsers()
        );
    }

    /**
     * Só os usuários, para a reinstateção por teste.
     *
     * Separado de install() porque os dois chamadores precisam de coisas opostas.
     * A montagem do banco quer as três seeds e nunca pode repeti-las: a seed
     * Usuarios grava um idUsuarios explícito, e um segundo INSERT no mesmo
     * AUTO_INCREMENT PRIMARY KEY aborta com 1062. A reinstateção por teste quer o
     * contrário — apaga e regrava os usuários a cada caso.
     *
     * `configuracoes` é justamente o que a reinstateção não pode tocar: 13 das suas
     * 14 linhas vêm da seed, mas a `email_automatico` vem de uma migration e
     * nenhuma seed a recria. Apagar a tabela e rodar a seed de novo deixaria o banco
     * sem a linha, e a montagem seguinte nem perceberia, porque reconstrói tudo do
     * zero e voltaria a tê-la.
     */
    public static function installUsers(): void
    {
        self::runSeeds(['Usuarios']);

        self::addExtraUsers();
    }

    /**
     * Roda as seeds indicadas e devolve os nomes, na ordem em que rodaram.
     *
     * O corpo é único para install() e installUsers() de propósito: são as mesmas
     * classes de seed e o mesmo tratamento da saída, que elas ecoam na saída padrão.
     * A reinstateção por teste roda isto dentro de um gancho do PHPUnit, então a
     * saída também precisaria sumir ali.
     *
     * @param  string[] $seeds
     * @return string[]
     */
    private static function runSeeds(array $seeds): array
    {
        foreach ($seeds as $seed) {
            self::load($seed);
        }

        ob_start();

        try {
            foreach ($seeds as $seed) {
                (new $seed())->run();
            }
        } finally {
            ob_end_clean();
        }

        return $seeds;
    }

    /**
     * Os dois caminhos de recusa que o Login precisa cobrir.
     *
     * inativo@admin.com tem situacao 0, então nem chega a ser autenticado.
     * expirado@admin.com tem dataExpiracao no passado, então passa pela consulta
     * e morre no chk_date().
     *
     * A senha é copiada da linha do administrador que a seed acabou de gravar, em
     * vez de repetir o hash aqui. Uma segunda cópia do hash seria um contrato
     * Parallel: mudar a senha na seed deixaria estes dois usuários com a senha
     * antiga e o teste falharia com um "Access denied" sem explicar a causa.
     *
     * @return string[]
     */
    private static function addExtraUsers(): array
    {
        $db = get_instance()->db;
        $senha = $db->select('senha')
            ->from('usuarios')
            ->where('idUsuarios', 1)
            ->limit(1)
            ->get()
            ->row('senha');

        if ($senha === null) {
            throw new \RuntimeException(
                'A seed Usuarios não gravou o administrador, então a senha dos '
                . 'usuários de teste não tem de onde vir.'
            );
        }

        $comum = [
            'rg' => 'MG-25.502.560',
            'cep' => '01024-900',
            'rua' => 'R. Cantareira',
            'numero' => '306',
            'bairro' => 'Centro Histórico de São Paulo',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'senha' => $senha,
            'telefone' => '0000-0000',
            'celular' => '',
            'dataCadastro' => '2018-09-09',
            'permissoes_id' => 1,
        ];

        $db->insert('usuarios', $comum + [
            'nome' => 'Inativo',
            'cpf' => '517.565.356-38',
            'email' => 'inativo@admin.com',
            'situacao' => 0,
            'dataExpiracao' => '2030-01-01',
        ]);

        $db->insert('usuarios', $comum + [
            'nome' => 'Expirado',
            'cpf' => '517.565.356-37',
            'email' => 'expirado@admin.com',
            'situacao' => 1,
            'dataExpiracao' => '2020-01-01',
        ]);

        return ['inativo@admin.com', 'expirado@admin.com'];
    }

    private static function load(string $seed): void
    {
        // A Seeder vive em libraries/ e as seeds em database/seeds/, e nenhuma das
        // duas entra no autoload do composer: o CI3 carrega por caminho.
        require_once APPPATH . 'libraries/Seeder.php';
        require_once APPPATH . "database/seeds/{$seed}.php";
    }
}
