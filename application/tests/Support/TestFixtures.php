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
        $installed = [];

        foreach (['Permissoes', 'Usuarios', 'Configuracoes'] as $seed) {
            self::load($seed);
        }

        // As seeds ecoam o progresso na saída padrão, e esta função roda no meio
        // do setup-db.php.
        ob_start();

        try {
            foreach (['Permissoes', 'Usuarios', 'Configuracoes'] as $seed) {
                (new $seed())->run();
                $installed[] = $seed;
            }

            $installed = array_merge($installed, self::addExtraUsers());
        } finally {
            ob_end_clean();
        }

        return $installed;
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
