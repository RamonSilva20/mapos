<?php

/**
 * A versão mostrada no painel, no login e usada pelo atualizador vem de
 * application/config/config.php. Na v5 ela não pode ser a da 4.x: o
 * atualizador ofereceria a release 4.55.x como atualização (#2858).
 */
final class VersaoExibidaTest extends MaposTestCase
{
    public function testVersaoDaV5NaoEhADa4x(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'config/config.php');
        $this->assertSame(1, preg_match("/\\\$config\\['app_version'\\]\\s*=\\s*'([^']+)'/", $codigo, $achado));

        $this->assertMatchesRegularExpression('/^5\.\d+\.\d+(-[0-9A-Za-z.]+)?$/', $achado[1]);
        $this->assertTrue(version_compare($achado[1], '4.55.2', '>'), 'A v5 não pode ser vista como mais antiga que a última 4.x.');
    }
}
