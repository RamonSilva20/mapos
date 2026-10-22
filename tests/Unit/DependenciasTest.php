<?php

/**
 * Dependências de produção e de desenvolvimento (#2877).
 */
final class DependenciasTest extends MaposTestCase
{
    private function composer(): array
    {
        return json_decode((string) file_get_contents(MAPOS_ROOT . '/composer.json'), true);
    }

    public function testWhoopsSoEDependenciaDeDesenvolvimento(): void
    {
        $composer = $this->composer();

        $this->assertArrayNotHasKey('filp/whoops', $composer['require']);
        $this->assertArrayHasKey('filp/whoops', $composer['require-dev']);
    }

    public function testHookDoWhoopsNaoQuebraSemOPacote(): void
    {
        $hook = (string) file_get_contents(APPPATH . 'hooks/whoops.php');

        $this->assertStringContainsString('! class_exists(Run::class)', $hook);
    }

    public function testPhpWordEstaNaVersao1(): void
    {
        $this->assertSame('^1.0', $this->composer()['require']['phpoffice/phpword']);

        $lock = json_decode((string) file_get_contents(MAPOS_ROOT . '/composer.lock'), true);
        $versao = null;
        foreach ($lock['packages'] as $pacote) {
            if ($pacote['name'] === 'phpoffice/phpword') {
                $versao = ltrim($pacote['version'], 'v');
            }
        }

        $this->assertNotNull($versao);
        $this->assertTrue(version_compare($versao, '1.0.0', '>='), 'phpword ' . $versao);
    }

    public function testModeloDaReceitaBrutaMeiAbreEGeraDocxComOPhpWord1(): void
    {
        $modelo = new PhpOffice\PhpWord\TemplateProcessor(MAPOS_ROOT . '/assets/relatorios/RELATORIO_MENSAL_DAS_RECEITAS_BRUTAS_MEI.docx');
        $this->assertContains('cnpj', $modelo->getVariables());

        $modelo->setValues(['cnpj' => '12.345.678/0001-90']);
        $saida = tempnam(sys_get_temp_dir(), 'mei') . '.docx';
        $modelo->saveAs($saida);

        $this->assertGreaterThan(5000, filesize($saida));
        unlink($saida);
    }
}
