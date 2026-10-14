<?php

/**
 * Armazenamento dos uploads sensíveis (#2872): anexos de OS e arquivos ficam
 * em STORAGE_PATH, fora de assets/, e só saem por controller com permissão.
 * Logo do emitente e foto de perfil continuam em assets/ (aparecem no login,
 * em e-mails e em PDFs) e não têm conteúdo sensível.
 *
 * No banco, `path` guarda o caminho relativo a STORAGE_PATH (nunca absoluto);
 * registros antigos com caminho absoluto em assets/ ainda são lidos até a
 * migration mover os arquivos.
 */
if (! defined('ARMAZENAMENTO_LIMITE_PADRAO_KB')) {
    define('ARMAZENAMENTO_LIMITE_PADRAO_KB', 10240);
}

if (! function_exists('armazenamentoRaiz')) {
    /**
     * Pasta raiz dos uploads: STORAGE_PATH (absoluto, ou relativo à raiz do
     * projeto) ou, sem ela, application/storage, que recebe um .htaccess que
     * nega o acesso web. O ideal é apontar STORAGE_PATH para fora da pasta
     * pública (docs/armazenamento-de-arquivos.md).
     */
    function armazenamentoRaiz(): string
    {
        $configurado = trim((string) ($_ENV['STORAGE_PATH'] ?? ''));

        if ($configurado === '') {
            return rtrim(APPPATH, '/\\') . DIRECTORY_SEPARATOR . 'storage';
        }

        if (! armazenamentoCaminhoAbsoluto($configurado)) {
            $configurado = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . $configurado;
        }

        return rtrim($configurado, '/\\');
    }
}

if (! function_exists('armazenamentoCaminhoAbsoluto')) {
    function armazenamentoCaminhoAbsoluto(string $caminho): bool
    {
        return (bool) preg_match('#^(/|[A-Za-z]:[\\\\/]|\\\\\\\\)#', $caminho);
    }
}

if (! function_exists('armazenamentoLimiteKb')) {
    /** Tamanho máximo de um upload em KB: UPLOAD_MAX_KB, ou 10 MB. */
    function armazenamentoLimiteKb(): int
    {
        $valor = (int) ($_ENV['UPLOAD_MAX_KB'] ?? 0);

        return $valor > 0 ? $valor : ARMAZENAMENTO_LIMITE_PADRAO_KB;
    }
}

if (! function_exists('armazenamentoTipos')) {
    /**
     * Extensões aceitas nos uploads, com os MIME que o conteúdo pode ter
     * (finfo) e se o navegador pode exibir o arquivo na própria página.
     *
     * @return array<string, array{mimes: list<string>, mime: string, inline: bool}>
     */
    function armazenamentoTipos(): array
    {
        return [
            'jpg' => ['mimes' => ['image/jpeg'], 'mime' => 'image/jpeg', 'inline' => true],
            'jpeg' => ['mimes' => ['image/jpeg'], 'mime' => 'image/jpeg', 'inline' => true],
            'png' => ['mimes' => ['image/png'], 'mime' => 'image/png', 'inline' => true],
            'gif' => ['mimes' => ['image/gif'], 'mime' => 'image/gif', 'inline' => true],
            'pdf' => ['mimes' => ['application/pdf'], 'mime' => 'application/pdf', 'inline' => true],
            'txt' => ['mimes' => ['text/plain'], 'mime' => 'text/plain', 'inline' => false],
            'docx' => ['mimes' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'], 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'inline' => false],
            'cdr' => ['mimes' => ['application/vnd.corel-draw', 'application/x-coreldraw', 'application/cdr', 'image/x-coreldraw', 'application/zip', 'application/octet-stream'], 'mime' => 'application/octet-stream', 'inline' => false],
        ];
    }
}

if (! function_exists('armazenamentoExtensoesAnexo')) {
    /**
     * Formatos aceitos nos anexos da OS (painel e API).
     *
     * @return list<string>
     */
    function armazenamentoExtensoesAnexo(): array
    {
        return ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'cdr', 'docx', 'txt'];
    }
}

if (! function_exists('armazenamentoMimePermitido')) {
    /** O conteúdo do arquivo confere com a extensão (e a extensão é aceita). */
    function armazenamentoMimePermitido(string $caminho, string $extensao): bool
    {
        $tipos = armazenamentoTipos();
        $extensao = strtolower(ltrim($extensao, '.'));

        if (! isset($tipos[$extensao]) || ! is_file($caminho)) {
            return false;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($caminho);

        return is_string($mime) && in_array($mime, $tipos[$extensao]['mimes'], true);
    }
}

if (! function_exists('armazenamentoPasta')) {
    /**
     * Garante a pasta (relativa à raiz) e a protege: index.html e, na raiz,
     * .htaccess que nega o acesso web. Devolve o caminho absoluto, ou null se
     * não conseguir criar ou se o caminho sair da raiz.
     */
    function armazenamentoPasta(string $relativo): ?string
    {
        $relativo = trim(str_replace('\\', '/', $relativo), '/');
        if (in_array('..', explode('/', $relativo), true)) {
            return null;
        }

        $raiz = armazenamentoRaiz();
        $pasta = $raiz . ($relativo === '' ? '' : DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativo));

        if (! is_dir($pasta) && ! @mkdir($pasta, 0750, true) && ! is_dir($pasta)) {
            return null;
        }

        $negar = $raiz . DIRECTORY_SEPARATOR . '.htaccess';
        if (! file_exists($negar)) {
            @file_put_contents($negar, "# Os arquivos saem só pelo controller (docs/armazenamento-de-arquivos.md).\n<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tOrder allow,deny\n\tDeny from all\n</IfModule>\n");
        }

        $caminhos = [$raiz];
        $acumulado = $raiz;
        foreach ($relativo === '' ? [] : explode('/', $relativo) as $parte) {
            $acumulado .= DIRECTORY_SEPARATOR . $parte;
            $caminhos[] = $acumulado;
        }
        if (function_exists('protegerDiretorioUpload')) {
            protegerDiretorioUpload($caminhos);
        }

        return $pasta;
    }
}

if (! function_exists('armazenamentoRelativo')) {
    /** Caminho relativo à raiz de um arquivo/pasta dentro dela (com "/"), ou null. */
    function armazenamentoRelativo(string $absoluto): ?string
    {
        $raiz = realpath(armazenamentoRaiz());
        $real = realpath($absoluto);

        if ($raiz === false || $real === false || ! str_starts_with($real, $raiz . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return str_replace(DIRECTORY_SEPARATOR, '/', substr($real, strlen($raiz) + 1));
    }
}

if (! function_exists('armazenamentoLocalizar')) {
    /**
     * Arquivo real de um caminho guardado no banco: relativo (a STORAGE_PATH)
     * ou, nos registros antigos, absoluto. Só devolve arquivos que existem e
     * ficam dentro da raiz ou de uma das $raizesLegadas; um registro
     * adulterado nunca aponta para fora delas.
     *
     * @param  list<string>  $raizesLegadas
     */
    function armazenamentoLocalizar(?string $caminho, array $raizesLegadas = []): ?string
    {
        $caminho = (string) $caminho;
        if ($caminho === '') {
            return null;
        }

        if (! armazenamentoCaminhoAbsoluto($caminho)) {
            $caminho = armazenamentoRaiz() . DIRECTORY_SEPARATOR . $caminho;
        }

        $real = realpath($caminho);
        if ($real === false || ! is_file($real)) {
            return null;
        }

        foreach (array_merge([armazenamentoRaiz()], $raizesLegadas) as $raiz) {
            $raiz = realpath($raiz);
            if ($raiz !== false && str_starts_with($real, $raiz . DIRECTORY_SEPARATOR)) {
                return $real;
            }
        }

        return null;
    }
}

if (! function_exists('armazenamentoArquivoDoAnexo')) {
    /** Arquivo (ou miniatura) de uma linha da tabela anexos, ou null. */
    function armazenamentoArquivoDoAnexo(object $anexo, bool $miniatura = false, ?string $raizLegada = null): ?string
    {
        $pasta = rtrim((string) ($anexo->path ?? ''), '/\\');
        $nome = $miniatura ? (string) ($anexo->thumb ?? '') : (string) ($anexo->anexo ?? '');

        if ($pasta === '' || $nome === '') {
            return null;
        }

        $arquivo = $pasta . DIRECTORY_SEPARATOR . ($miniatura ? 'thumbs' . DIRECTORY_SEPARATOR : '') . basename($nome);

        return armazenamentoLocalizar($arquivo, [$raizLegada ?? FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos']);
    }
}

if (! function_exists('armazenamentoConfigUpload')) {
    /**
     * Configuração da biblioteca de upload: pasta, extensões e limite de
     * tamanho vindos do armazenamento, nome aleatório.
     *
     * @param  list<string>  $extensoes
     */
    function armazenamentoConfigUpload(string $pasta, array $extensoes): array
    {
        return [
            'upload_path' => $pasta,
            'allowed_types' => implode('|', $extensoes),
            'max_size' => armazenamentoLimiteKb(),
            'encrypt_name' => true,
            'file_ext_tolower' => true,
        ];
    }
}

if (! function_exists('armazenamentoValidarEnvio')) {
    /**
     * Confere o arquivo recém-gravado pelo upload (CI_Upload::data()): o
     * conteúdo precisa bater com a extensão. Em caso de divergência apaga o
     * arquivo e devolve a mensagem; senão null.
     */
    function armazenamentoValidarEnvio(array $dados): ?string
    {
        $extensao = strtolower(ltrim((string) ($dados['file_ext'] ?? ''), '.'));

        if (armazenamentoMimePermitido((string) ($dados['full_path'] ?? ''), $extensao)) {
            return null;
        }

        if (is_file((string) ($dados['full_path'] ?? ''))) {
            @unlink($dados['full_path']);
        }

        return 'O conteúdo do arquivo não confere com a extensão.';
    }
}

if (! function_exists('armazenamentoCabecalhos')) {
    /**
     * Cabeçalhos da resposta de um arquivo: tipo fixo pela extensão (nunca
     * pelo conteúdo), exibição na página só para imagens e PDF, e o resto como
     * download. nosniff e CSP restrita impedem que um arquivo vire script.
     *
     * @return array<string, string>
     */
    function armazenamentoCabecalhos(string $nomeDeDownload, string $extensao, int $tamanho, bool $forcarDownload = false): array
    {
        $tipos = armazenamentoTipos();
        $extensao = strtolower(ltrim($extensao, '.'));
        $tipo = $tipos[$extensao] ?? ['mime' => 'application/octet-stream', 'inline' => false];

        $nome = preg_replace('/[^\w. -]+/u', '_', $nomeDeDownload) ?: 'arquivo';
        $disposicao = ($tipo['inline'] && ! $forcarDownload) ? 'inline' : 'attachment';

        return [
            'Content-Type' => $tipo['mime'],
            'Content-Length' => (string) $tamanho,
            'Content-Disposition' => $disposicao . '; filename="' . $nome . '"; filename*=UTF-8\'\'' . rawurlencode($nome),
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'",
            'Cache-Control' => 'private, max-age=3600',
        ];
    }
}

if (! function_exists('armazenamentoEnviar')) {
    /** Envia o arquivo ao navegador e encerra a requisição. */
    function armazenamentoEnviar(string $arquivo, string $nomeDeDownload, bool $forcarDownload = false): void
    {
        $cabecalhos = armazenamentoCabecalhos($nomeDeDownload, pathinfo($arquivo, PATHINFO_EXTENSION), (int) filesize($arquivo), $forcarDownload);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        foreach ($cabecalhos as $nome => $valor) {
            header($nome . ': ' . $valor);
        }

        readfile($arquivo);
        exit;
    }
}
