<?php

/**
 * Backup do banco (#2856): só download, sem gravar em URL ou em pasta
 * pública, e com nome de arquivo válido.
 */
final class BackupTest extends MaposTestCase
{
    public function testNomeDoArquivoTemDataHoraEMinutosSemDoisPontos(): void
    {
        $nome = backupNomeArquivo(mktime(14, 5, 9, 10, 8, 2026));

        $this->assertSame('backup-2026-10-08-14h05.zip', $nome);
        $this->assertDoesNotMatchRegularExpression('#[:\\\\/*?"<>|]#', $nome);
    }

    public function testBackupNaoGravaArquivo(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');
        preg_match('/function backup\(\).*?\n    }\n/s', $codigo, $trecho);

        $this->assertNotEmpty($trecho);
        $this->assertStringNotContainsString('write_file(', $trecho[0]);
        $this->assertStringContainsString('force_download(backupNomeArquivo(', $trecho[0]);
        $this->assertStringContainsString("checkPermission(\$this->session->userdata('permissao'), 'cBackup')", $trecho[0]);
    }
}
