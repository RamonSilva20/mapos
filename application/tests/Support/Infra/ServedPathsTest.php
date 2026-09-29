<?php

namespace Tests\Support\Infra;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * As pastas que estão dentro da raiz do documento e não podem ser servidas.
 *
 * `docker-compose.yml` monta o repositório inteiro em `/var/www/html`, então a raiz
 * do documento é a raiz do repositório: o que está no servidor web não é só o que
 * o `index.php` alcança, é tudo que está em disco. Duas pastas não devem sair:
 *
 *   - `application/`, que o nginx já bloqueava, porque um `.php` ali é executado
 *     pelo php-fpm — inclusive `application/tests/bin/setup-db.php`, que recria um
 *     banco, e `application/database/seeds/`, que traz as credenciais de fábrica;
 *   - `tools/`, que traz `xss-baseline.txt`, o inventário de cada valor que chega
 *     à página sem escaper, com arquivo, expressão e linha. Servir esse arquivo é
 *     publicar um mapa do que não está escapado.
 *
 * O bloqueio é declarado em DOIS lugares para cada pasta, e é essa duplicação que
 * este caso existe para vigiar. Cada pasta tem um `.htaccess` para o Apache e uma
 * `location` no nginx, porque o nginx não lê `.htaccess`: a regra do nginx é a que
 * vale no `docker-compose.yml`, e a do `.htaccess` é a que vale em qualquer
 * instalação com Apache. Editar uma e não a outra reabre a pasta sem erro, sem
 * aviso e sem nenhum teste que falhe — que é o modo como uma barreira de segurança
 * desaparece em silêncio.
 *
 * O caso de concordância entre os dois arquivos do nginx é o que pega esse
 * descasamento. Os dois arquivos são quase idênticos, diferindo só nas linhas de
 * `listen` e `server_name`, e é por isso que uma mudança em um não tem nenhum
 * motivo para aparecer no outro: nada os liga, a não ser este teste.
 */
final class ServedPathsTest extends TestCase
{
    /**
     * As pastas que precisam de `location ^~` no nginx.
     *
     * O nginx bloqueia `application/` inteiro, e não só `application/tests/`, porque
     * o que está ali são `.php` executáveis e as credenciais de fábrica das seeds. O
     * `.htaccess` dessa pasta não nega nada — é a rewrite do front controller do
     * CodeIgniter, e precisa rewriting — e por isso as duas listas abaixo não são a
     * mesma: a do nginx é mais larga, e por um motivo que é do nginx.
     *
     * @return iterable<string, array{string}>
     */
    public static function provideNginxBlockedDirectories(): iterable
    {
        yield 'application/ tem php executavel e credenciais de fabrica' => ['application'];
        yield 'tools/ tem o inventario do que nao esta escapado' => ['tools'];
    }

    /**
     * As pastas que precisam de `.htaccess` que negue, para o Apache.
     *
     * O caminho do nginx e o do Apache são defesas de servidores diferentes, e quem
     * instala o Map-OS pode estar em qualquer um dos dois. A regra do nginx sozinha
     * deixa o Apache servindo, e vice-versa. `application/tests/` e `tools/` são as
     * duas com o par completo.
     *
     * @return iterable<string, array{string}>
     */
    public static function provideDenyHtaccessDirectories(): iterable
    {
        yield 'tests/ recria banco e roda setup-db.php' => ['application/tests'];
        yield 'tools/ tem o inventario do que nao esta escapado' => ['tools'];
    }

    /**
     * As duas configs do nginx bloqueiam a mesma pasta, com o `^~` de prefixo.
     *
     * O `^~` não é estilo. As regex têm prioridade sobre prefixo comum no nginx, e
     * `location ~* \.php$` existe mais abaixo nos dois arquivos: sem o `^~`, um
     * `.php` dentro de uma pasta "protegida" seria avaliado pela regex antes de a
     * pasta ser considerada, e o acesso passaria. A regra parece redundante e não é.
     *
     * @param  string  $directory
     */
    #[DataProvider('provideNginxBlockedDirectories')]
    #[Test]
    public function testBothNginxConfigsBlockTheDirectoryWithAPrefixMatch(string $directory): void
    {
        foreach ($this->nginxConfigs() as $file) {
            $this->assertStringContainsString(
                "location ^~ /{$directory}/ {",
                $this->contents($file),
                "{$file} não bloqueia /{$directory}/ com location ^~, e sem o ^~ a "
                . "location ~* \.php$ é avaliada antes e o acesso passa"
            );
        }
    }

    /**
     * Os dois arquivos do nginx bloqueiam o mesmo conjunto de pastas.
     *
     * Este é o caso que pega a edição pela metade, e o que o AGENTS.md descreve sem
     * conseguir impor: os dois arquivos são cópias com duas linhas trocadas, não
     * existem juntos em lugar nenhum, e uma regra nova em um deles é uma regra que
     * não vale no container que o `docker-compose.yml` sobe — porque ele monta
     * `default.template.conf` por cima de `default.conf` na linha de comando do
     * serviço, e é o template que está valendo.
     */
    #[Test]
    public function testBothNginxConfigsBlockTheSameDirectories(): void
    {
        $blocked = [];

        foreach ($this->nginxConfigs() as $file) {
            preg_match_all('/location \^~ (\/[a-z]+\/)/', $this->contents($file), $matches);
            sort($matches[1]);
            $blocked[$file] = $matches[1];
        }

        $files = array_keys($blocked);

        $this->assertSame(
            $blocked[$files[0]],
            $blocked[$files[1]],
            'os dois arquivos do nginx bloqueiam pastas diferentes: um deles está servindo '
            . 'o que o outro bloqueia, e o compose sobe o template por cima do conf'
        );

        $this->assertNotSame(
            [],
            $blocked[$files[0]],
            'nenhuma pasta bloqueada: a regex não casou com nada, e um teste que passa '
            . 'por não ter encontrado o padrão não prova que a regra existe'
        );
    }

    /**
     * Toda pasta com par completo tem um `.htaccess` que nega, para o Apache.
     *
     * @param  string  $directory
     */
    #[DataProvider('provideDenyHtaccessDirectories')]
    #[Test]
    public function testEachProtectedDirectoryAlsoDeniesApache(string $directory): void
    {
        $file = $this->root() . "/{$directory}/.htaccess";

        $this->assertFileExists($file, "{$file} não existe, e o nginx não lê .htaccess: sem ele o Apache serve a pasta");

        $this->assertMatchesRegularExpression(
            '/(Require all denied|Deny from all)/',
            $this->contents($file),
            "{$file} existe mas não nega"
        );
    }

    /**
     * @return list<string> caminhos absolutos
     */
    private function nginxConfigs(): array
    {
        return [
            $this->root() . '/docker/etc/nginx/default.conf',
            $this->root() . '/docker/etc/nginx/default.template.conf',
        ];
    }

    private function root(): string
    {
        return dirname(__DIR__, 4);
    }

    private function contents(string $file): string
    {
        $contents = is_file($file) ? file_get_contents($file) : false;

        if ($contents === false) {
            self::fail("Não consegui ler {$file}.");
        }

        return $contents;
    }
}
