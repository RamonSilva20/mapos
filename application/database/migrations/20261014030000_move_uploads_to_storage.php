<?php

/**
 * Move os anexos de OS (assets/anexos) e os arquivos (assets/arquivos) para a
 * pasta de armazenamento, STORAGE_PATH (#2872), e passa `path` a ser relativo
 * a ela (`anexos/10-2026/OS-5`, `arquivos/10-10-2026/abc.pdf`).
 *
 * Só toca em registro cujo arquivo existe; o que não puder ser movido fica
 * como estava (o sistema ainda lê os caminhos antigos). Pode rodar de novo.
 *
 * Rollback (down): devolve os arquivos para assets/anexos e assets/arquivos e
 * grava de volta os caminhos absolutos e as URLs públicas. Antes de reverter
 * para uma versão anterior à #2872, rode o down e mantenha o STORAGE_PATH.
 */
class Migration_move_uploads_to_storage extends CI_Migration
{
    public function up()
    {
        $this->load->helper('armazenamento');

        foreach ($this->db->get('anexos')->result() as $anexo) {
            $relativo = $this->relativoAntigo((string) $anexo->path, 'anexos');
            if ($relativo === null) {
                continue;
            }

            $origem = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativo);
            $destinoRelativo = 'anexos/' . $relativo;
            $destino = armazenamentoPasta($destinoRelativo . '/thumbs');

            if ($destino === null || ! $this->moverAnexo($anexo, $origem, dirname($destino))) {
                continue;
            }

            $this->db->where('idAnexos', $anexo->idAnexos)->update('anexos', ['path' => $destinoRelativo, 'url' => '']);
        }

        foreach ($this->db->get('documentos')->result() as $documento) {
            $relativo = $this->relativoAntigo((string) $documento->path, 'arquivos');
            if ($relativo === null) {
                continue;
            }

            $origem = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativo);
            $pasta = armazenamentoPasta('arquivos/' . dirname($relativo));
            $destino = $pasta === null ? null : $pasta . DIRECTORY_SEPARATOR . basename($relativo);

            if ($destino === null || ! $this->mover($origem, $destino)) {
                continue;
            }

            $this->db->where('idDocumentos', $documento->idDocumentos)->update('documentos', ['path' => 'arquivos/' . $relativo, 'url' => '']);
        }
    }

    public function down()
    {
        $this->load->helper('armazenamento');
        $base = rtrim((string) $this->config->item('base_url'), '/') . '/';

        foreach ($this->db->get('anexos')->result() as $anexo) {
            $path = (string) $anexo->path;
            if ($path === '' || armazenamentoCaminhoAbsoluto($path) || ! str_starts_with($path, 'anexos/')) {
                continue;
            }

            $relativo = substr($path, strlen('anexos/'));
            $origem = armazenamentoRaiz() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
            $destino = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativo);

            if (! is_dir($destino . DIRECTORY_SEPARATOR . 'thumbs') && ! @mkdir($destino . DIRECTORY_SEPARATOR . 'thumbs', 0755, true)) {
                continue;
            }

            if (! $this->moverAnexo($anexo, $origem, $destino)) {
                continue;
            }

            $this->db->where('idAnexos', $anexo->idAnexos)->update('anexos', [
                'path' => $destino,
                'url' => $base . 'assets/anexos/' . $relativo,
            ]);
        }

        foreach ($this->db->get('documentos')->result() as $documento) {
            $path = (string) $documento->path;
            if ($path === '' || armazenamentoCaminhoAbsoluto($path) || ! str_starts_with($path, 'arquivos/')) {
                continue;
            }

            $relativo = substr($path, strlen('arquivos/'));
            $origem = armazenamentoRaiz() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
            $destino = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativo);

            if (! is_dir(dirname($destino)) && ! @mkdir(dirname($destino), 0755, true)) {
                continue;
            }

            if (! $this->mover($origem, $destino)) {
                continue;
            }

            $this->db->where('idDocumentos', $documento->idDocumentos)->update('documentos', [
                'path' => $destino,
                'url' => $base . 'assets/arquivos/' . $relativo,
            ]);
        }
    }

    /**
     * Caminho (com "/") relativo a assets/<pasta>/ de um path antigo, ou null
     * se o path é vazio, já é relativo (migrado) ou não está em assets/<pasta>.
     */
    private function relativoAntigo(string $path, string $pasta): ?string
    {
        $path = str_replace('\\', '/', $path);
        $marcador = 'assets/' . $pasta . '/';
        $posicao = strpos($path, $marcador);

        if ($path === '' || ! armazenamentoCaminhoAbsoluto($path) && ! str_starts_with($path, './') || $posicao === false) {
            return null;
        }

        $relativo = trim(substr($path, $posicao + strlen($marcador)), '/');

        return $relativo === '' || in_array('..', explode('/', $relativo), true) ? null : $relativo;
    }

    /** Move o arquivo e a miniatura de um anexo de $pastaOrigem para $pastaDestino. */
    private function moverAnexo(object $anexo, string $pastaOrigem, string $pastaDestino): bool
    {
        $arquivo = basename((string) $anexo->anexo);
        $ok = $this->mover($pastaOrigem . DIRECTORY_SEPARATOR . $arquivo, $pastaDestino . DIRECTORY_SEPARATOR . $arquivo);

        if ($ok && (string) $anexo->thumb !== '') {
            $thumb = basename((string) $anexo->thumb);
            $this->mover($pastaOrigem . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $thumb, $pastaDestino . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $thumb);
        }

        return $ok;
    }

    /** Move um arquivo (cópia + remoção se estiver em outro volume); não sobrescreve. */
    private function mover(string $origem, string $destino): bool
    {
        if (! is_file($origem)) {
            // Já está no destino (execução repetida): conta como movido.
            return is_file($destino);
        }

        if (is_file($destino)) {
            return false;
        }

        if (@rename($origem, $destino)) {
            return true;
        }

        if (@copy($origem, $destino)) {
            @unlink($origem);

            return true;
        }

        return false;
    }
}
