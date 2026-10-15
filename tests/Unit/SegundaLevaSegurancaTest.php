<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Segunda leva da revisão de segurança (#2879): planilhas, relatório MEI,
 * e-mail da OS, release e Docker.
 */
final class SegundaLevaSegurancaTest extends TestCase
{
    private function fonte(string $arquivo): string
    {
        return (string) file_get_contents(dirname(__DIR__, 2) . '/' . $arquivo);
    }

    public function testPlanilhaNeutralizaFormulas(): void
    {
        require_once dirname(__DIR__, 2) . '/application/helpers/relatorios_helper.php';

        $this->assertSame(
            ["'=HYPERLINK(\"http://x\")", "'+1+1", "'@SUM(A1)", 'Ana', 12.5, '-3', '', null],
            planilhaCelulasSeguras(['=HYPERLINK("http://x")', '+1+1', '@SUM(A1)', 'Ana', 12.5, '-3', '', null])
        );
        $this->assertStringContainsString('planilhaCelulasSeguras($cliente)', $this->fonte('application/controllers/Relatorios.php'));
    }

    public function testRelatorioMeiUsaArquivosTemporariosForaDoWebroot(): void
    {
        $fonte = $this->fonte('application/controllers/Relatorios.php');

        $this->assertStringNotContainsString("'temp.docx'", $fonte);
        $this->assertStringContainsString('sys_get_temp_dir()', $fonte);
    }

    public function testEmailDaOsSoPorPostComPermissaoDeEdicao(): void
    {
        $fonte = $this->fonte('application/controllers/Os.php');
        $metodo = substr($fonte, (int) strpos($fonte, 'function enviar_email'), 600);

        $this->assertStringContainsString("method() !== 'post'", $metodo);
        $this->assertStringContainsString("'eOs'", $metodo);
        $this->assertStringContainsString("'enviar_email' => 'eOs'", $this->fonte('application/config/permissions_map.php'));
    }

    public function testReleaseValidaATagSemInterpolarNoScript(): void
    {
        $fonte = $this->fonte('.github/workflows/release.yml');

        $this->assertStringContainsString('Validate tag name', $fonte);
        $this->assertStringNotContainsString('git tag "${{ inputs.tag }}"', $fonte);
        $this->assertStringContainsString("--exclude './tests/*'", $fonte);
    }

    public function testDockerSoEscutaNoLocalhostESemPmaArbitrario(): void
    {
        $fonte = $this->fonte('docker/docker-compose.yml');

        $this->assertStringNotContainsString('PMA_ARBITRARY', $fonte);
        $this->assertStringContainsString('"127.0.0.1:${MYSQL_MAPOS_PORT}:3306"', $fonte);
    }

    public function testValorDoEnvEscapaCifrao(): void
    {
        require_once dirname(__DIR__, 2) . '/application/helpers/configuracoes_helper.php';

        $this->assertSame('"a\\$b\'c"', configuracaoValorEnv("a\$b'c"));
    }
}
