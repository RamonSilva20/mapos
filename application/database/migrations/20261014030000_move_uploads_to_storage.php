<?php

/**
 * Move os anexos de OS (assets/anexos) e os arquivos (assets/arquivos) para a
 * pasta de armazenamento, STORAGE_PATH (#2872), e passa `path` a ser relativo
 * a ela (`anexos/10-2026/OS-5`, `arquivos/10-10-2026/abc.pdf`).
 *
 * Só toca em registro cujo arquivo existe. Se o STORAGE_PATH não existe ou o
 * PHP não grava nele, ou se algum arquivo não puder ser movido, a migration
 * FALHA com a lista do problema (e não conclui, então "Atualizar banco" e
 * `tools migrate` tentam de novo depois que a causa for corrigida). O que já
 * foi movido fica movido e o que falhou fica como estava (o sistema ainda lê
 * os caminhos antigos). Arquivo que não existe mais no disco (registro órfão)
 * é só registrado no log e não impede a migration.
 *
 * Rollback (down): devolve os arquivos para assets/anexos e assets/arquivos e
 * grava de volta os caminhos absolutos e as URLs públicas. Antes de reverter
 * para uma versão anterior à #2872, rode o down e mantenha o STORAGE_PATH.
 */
class Migration_move_uploads_to_storage extends CI_Migration
{
    private const MOVIDO = 'movido';

    private const AUSENTE = 'ausente';

    private const FALHOU = 'falhou';

    public function up()
    {
        $this->load->helper('armazenamento');

        $anexos = $this->db->get('anexos')->result();
        $documentos = $this->db->get('documentos')->result();

        // Só exige a pasta quando há arquivo para mover: banco sem anexos migra sem STORAGE_PATH.
        if ($this->temArquivoParaMover($anexos, $documentos)) {
            $this->exigirArmazenamentoGravavel();
        }

        $falhas = [];

        foreach ($anexos as $anexo) {
            $relativo = $this->relativoAntigo((string) $anexo->path, 'anexos');
            if ($relativo === null) {
                continue;
            }

            $origem = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativo);
            $destinoRelativo = 'anexos/' . $relativo;
            $destino = armazenamentoPasta($destinoRelativo . '/thumbs');

            if ($destino === null) {
                $falhas[] = 'anexo ' . $anexo->idAnexos . ': não foi possível criar a pasta ' . $destinoRelativo;

                continue;
            }

            $situacao = $this->moverAnexo($anexo, $origem, dirname($destino));
            if ($situacao === self::AUSENTE) {
                log_message('error', 'move_uploads_to_storage: o arquivo do anexo ' . $anexo->idAnexos . ' não existe em ' . $origem . '; registro mantido como estava.');

                continue;
            }
            if ($situacao === self::FALHOU) {
                $falhas[] = 'anexo ' . $anexo->idAnexos . ': não foi possível mover ' . $anexo->anexo . ' de ' . $origem;

                continue;
            }

            $this->db->where('idAnexos', $anexo->idAnexos)->update('anexos', ['path' => $destinoRelativo, 'url' => '']);
        }

        foreach ($documentos as $documento) {
            $relativo = $this->relativoAntigo((string) $documento->path, 'arquivos');
            if ($relativo === null) {
                continue;
            }

            $origem = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativo);
            $pasta = armazenamentoPasta('arquivos/' . dirname($relativo));

            if ($pasta === null) {
                $falhas[] = 'arquivo ' . $documento->idDocumentos . ': não foi possível criar a pasta arquivos/' . dirname($relativo);

                continue;
            }

            $situacao = $this->mover($origem, $pasta . DIRECTORY_SEPARATOR . basename($relativo));
            if ($situacao === self::AUSENTE) {
                log_message('error', 'move_uploads_to_storage: o arquivo ' . $documento->idDocumentos . ' não existe em ' . $origem . '; registro mantido como estava.');

                continue;
            }
            if ($situacao === self::FALHOU) {
                $falhas[] = 'arquivo ' . $documento->idDocumentos . ': não foi possível mover ' . $origem;

                continue;
            }

            $this->db->where('idDocumentos', $documento->idDocumentos)->update('documentos', ['path' => 'arquivos/' . $relativo, 'url' => '']);
        }

        if ($falhas !== []) {
            foreach ($falhas as $falha) {
                log_message('error', 'move_uploads_to_storage: ' . $falha);
            }

            throw new RuntimeException(count($falhas) . ' arquivo(s) não puderam ser movidos para ' . armazenamentoRaiz() . ' (primeiro: ' . $falhas[0] . '). Confira as permissões da pasta e rode a atualização do banco de novo; o que já foi movido não se repete. A lista completa está no log.');
        }
    }

    public function down()
    {
        $this->load->helper('armazenamento');
        // APP_BASEURL é a URL pública (a base_url do CI no terminal pode vir como http://).
        $base = rtrim((string) ($_ENV['APP_BASEURL'] ?? $this->config->item('base_url')), '/') . '/';

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

            if ($this->moverAnexo($anexo, $origem, $destino) !== self::MOVIDO) {
                log_message('error', 'move_uploads_to_storage (down): o anexo ' . $anexo->idAnexos . ' não foi devolvido de ' . $origem . '; registro mantido.');

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

            if ($this->mover($origem, $destino) !== self::MOVIDO) {
                log_message('error', 'move_uploads_to_storage (down): o arquivo ' . $documento->idDocumentos . ' não foi devolvido de ' . $origem . '; registro mantido.');

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

    /** Há registro antigo cujo arquivo ainda está em assets/anexos ou assets/arquivos? */
    private function temArquivoParaMover(array $anexos, array $documentos): bool
    {
        foreach ($anexos as $anexo) {
            $relativo = $this->relativoAntigo((string) $anexo->path, 'anexos');
            if ($relativo !== null && is_file(FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativo) . DIRECTORY_SEPARATOR . basename((string) $anexo->anexo))) {
                return true;
            }
        }

        foreach ($documentos as $documento) {
            $relativo = $this->relativoAntigo((string) $documento->path, 'arquivos');
            if ($relativo !== null && is_file(FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativo))) {
                return true;
            }
        }

        return false;
    }

    /** Falha com mensagem clara se o STORAGE_PATH não existe ou o PHP não grava nele. */
    private function exigirArmazenamentoGravavel(): void
    {
        $raiz = armazenamentoRaiz();
        $origem = trim((string) ($_ENV['STORAGE_PATH'] ?? '')) === '' ? 'STORAGE_PATH não está definida e vale application/storage' : 'STORAGE_PATH=' . $raiz;

        if (! is_dir($raiz) && ! @mkdir($raiz, 0750, true) && ! is_dir($raiz)) {
            log_message('error', 'move_uploads_to_storage: ' . $raiz . ' não existe e não pôde ser criada.');

            throw new RuntimeException('A pasta de armazenamento ' . $raiz . ' não existe e o PHP não consegue criá-la (' . $origem . '). Crie a pasta, dê ao usuário do servidor web permissão de escrita e rode a atualização do banco de novo.');
        }

        $teste = @tempnam($raiz, 'mapos-');
        if ($teste === false || ! is_writable($raiz)) {
            log_message('error', 'move_uploads_to_storage: ' . $raiz . ' não é gravável pelo PHP.');

            throw new RuntimeException('O PHP não consegue gravar em ' . $raiz . ' (' . $origem . '). Dê ao usuário do servidor web permissão de escrita nessa pasta e rode a atualização do banco de novo; nenhum arquivo foi movido.');
        }

        @unlink($teste);
    }

    /** Move o arquivo e a miniatura de um anexo de $pastaOrigem para $pastaDestino. */
    private function moverAnexo(object $anexo, string $pastaOrigem, string $pastaDestino): string
    {
        $arquivo = basename((string) $anexo->anexo);
        $situacao = $this->mover($pastaOrigem . DIRECTORY_SEPARATOR . $arquivo, $pastaDestino . DIRECTORY_SEPARATOR . $arquivo);

        if ($situacao === self::MOVIDO && (string) $anexo->thumb !== '') {
            $thumb = basename((string) $anexo->thumb);
            $this->mover($pastaOrigem . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $thumb, $pastaDestino . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $thumb);
        }

        return $situacao;
    }

    /**
     * Move um arquivo (cópia + remoção se estiver em outro volume); não
     * sobrescreve. MOVIDO também vale para a execução repetida (já está no
     * destino); AUSENTE é arquivo que não existe nos dois lugares.
     */
    private function mover(string $origem, string $destino): string
    {
        if (! is_file($origem)) {
            return is_file($destino) ? self::MOVIDO : self::AUSENTE;
        }

        if (is_file($destino)) {
            // Sobra de uma execução interrompida (cópia feita, origem não apagada): idêntico, limpa a origem.
            if (filesize($origem) === filesize($destino) && hash_file('sha256', $origem) === hash_file('sha256', $destino)) {
                @unlink($origem);

                return self::MOVIDO;
            }

            return self::FALHOU;
        }

        if (@rename($origem, $destino)) {
            return self::MOVIDO;
        }

        if (@copy($origem, $destino)) {
            @unlink($origem);

            return self::MOVIDO;
        }

        return self::FALHOU;
    }
}
