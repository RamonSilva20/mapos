<?php

namespace Tests\Support\Database;

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
     * As três contas que a montagem do banco de teste garante: a da seed
     * Usuarios mais as duas de recusa do Login.
     *
     * O número é escrito à mão porque `count()` não é expressão de constante em
     * PHP, então uma constante não pode derivar de `EXTRA_USERS`. O que impede a
     * divergência é `TestFixturesTest`, que confere esta constante contra a lista de
     * e-mails e contra `EXTRA_USERS` — um número escrito à mão com a derivação
     * verificada em teste vale mais do que um número que ninguém confere.
     */
    public const USER_COUNT = 3;

    /**
     * As duas contas de recusa do Login, com tudo o que as distingue.
     *
     * A lista é a fonte de verdade das duas coisas que dependem dela: os e-mails que
     * `userEmails()` devolve e os INSERT que `addExtraUsers()` faz. Estavam
     * escrita em dois lugares — o nome, o cpf, o e-mail, a situação e a data de cada
     * um apareciam na constante de contagem, na lista de e-mails e no INSERT — e a
     * contagem à mão era a quarta escrita do mesmo número.
     *
     * inativo@admin.com tem situacao 0, então nem chega a ser autenticado.
     * expirado@admin.com tem dataExpiracao no passado, então passa pela consulta e
     * morre no chk_date().
     *
     * @return list<array<string, string|int>>
     */
    private const EXTRA_USERS = [
        [
            'nome' => 'Inativo',
            'cpf' => '517.565.356-38',
            'email' => 'inativo@admin.com',
            'situacao' => 0,
            'dataExpiracao' => '2030-01-01',
        ],
        [
            'nome' => 'Expirado',
            'cpf' => '517.565.356-37',
            'email' => 'expirado@admin.com',
            'situacao' => 1,
            'dataExpiracao' => '2020-01-01',
        ],
    ];

    /**
     * Os e-mails de todas as contas, na ordem em que aparecem na saída da montagem.
     *
     * Derivado, e não escrito: um quarto e-mail aqui sem um quarto INSERT é o tipo
     * de erro que a montagem do banco só denuncia quando alguma coisa já a leu.
     *
     * @return string[]
     */
    public static function userEmails(): array
    {
        return array_merge(
            ['admin@admin.com'],
            array_column(self::EXTRA_USERS, 'email')
        );
    }

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
     * Só os usuários, para a reinstalação por teste.
     *
     * Separado de install() porque os dois chamadores precisam de coisas opostas.
     * A montagem do banco quer as três seeds e nunca pode repeti-las: a seed
     * Usuarios grava um idUsuarios explícito, e um segundo INSERT no mesmo
     * AUTO_INCREMENT PRIMARY KEY aborta com 1062. A reinstalação por teste quer o
     * contrário — apaga e regrava os usuários a cada caso.
     *
     * `configuracoes` é justamente o que a reinstalação não pode tocar: 13 das suas
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
     * A reinstalação por teste roda isto dentro de um gancho do PHPUnit, então a
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
     * A senha é copiada da linha do administrador que a seed acabou de gravar, em
     * vez de repetir o hash aqui. Uma segunda cópia do hash seria um contrato
     * paralelo: mudar a senha na seed deixaria estes dois usuários com a senha
     * antiga e o teste falharia com um "Access denied" sem explicar a causa.
     *
     * Os campos que diferenciam um usuário do outro estão em `EXTRA_USERS`, e o que
     * é igual aos dois — endereço, contato, permissão, a senha copiada — fica aqui.
     * A linha inteira é montada com o `+`, que dá preferência à chave da esquerda,
     * e por isso a lista fica à esquerda: os campos de `EXTRA_USERS` vencem.
     *
     * @return string[] os e-mails gravados
     */
    private static function addExtraUsers(): array
    {
        $db = get_instance()->db;
        $password = $db->select('senha')
            ->from('usuarios')
            ->where('idUsuarios', 1)
            ->limit(1)
            ->get()
            ->row('senha');

        if ($password === null) {
            throw new \RuntimeException(
                'A seed Usuarios não gravou o administrador, então a senha dos '
                . 'usuários de teste não tem de onde vir.'
            );
        }

        $shared = [
            'rg' => 'MG-25.502.560',
            'cep' => '01024-900',
            'rua' => 'R. Cantareira',
            'numero' => '306',
            'bairro' => 'Centro Histórico de São Paulo',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'senha' => $password,
            'telefone' => '0000-0000',
            'celular' => '',
            'dataCadastro' => '2018-09-09',
            'permissoes_id' => 1,
        ];

        $emails = [];

        foreach (self::EXTRA_USERS as $user) {
            $db->insert('usuarios', $user + $shared);
            $emails[] = $user['email'];
        }

        return $emails;
    }

    private static function load(string $seed): void
    {
        // A Seeder vive em libraries/ e as seeds em database/seeds/, e nenhuma das
        // duas entra no autoload do composer: o CI3 carrega por caminho.
        require_once APPPATH . 'libraries/Seeder.php';
        require_once APPPATH . "database/seeds/{$seed}.php";
    }
}
