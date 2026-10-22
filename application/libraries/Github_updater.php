<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Atualizador do Map-OS por releases do GitHub (#2871).
 *
 *     $release = $this->github_updater->ultimaRelease();      // última release com tag, ou null
 *     $this->github_updater->temAtualizacao($release);         // versão da release > app_version
 *     $this->github_updater->atualizar($release['tag']);      // baixa, confere o SHA256, aplica
 *
 * Fluxo de atualizar():
 *  1. refaz a consulta à API e só segue se a tag pedida for a da última release
 *     (a tela mostrou o changelog daquela versão, e é ela que é aplicada);
 *  2. baixa o zip e o `.sha256` publicados pelo release.yml, só de
 *     https://github.com/<usuario>/<repo>/releases/download/<tag>/, com o TLS
 *     verificado, e confere o SHA256 do zip (e o `digest` que a API informa
 *     para o asset, quando existe). Zip sem hash, ou com hash diferente, não é aberto;
 *  3. extrai com checagem de cada entrada (sem `..`, caminho absoluto, link
 *     simbólico nem zip bomba) numa pasta de trabalho dentro de application/cache;
 *  4. antes de sobrescrever, guarda uma cópia dos arquivos que existem e, se
 *     qualquer cópia falhar, devolve tudo ao que era;
 *  5. nunca toca no .env, nos uploads, nos logs, na pasta install/ nem no que
 *     estiver em `ignored_files`.
 *
 * O `.sha256` vem da mesma release que o zip: ele protege contra download
 * corrompido ou adulterado no caminho, não contra uma release publicada com um
 * zip malicioso por quem tem acesso ao repositório. Banco de dados não muda
 * aqui: depois de atualizar, o admin roda "Atualizar banco".
 */
class Github_updater
{
    public const API_URL = 'https://api.github.com/repos/';

    public const GITHUB_URL = 'https://github.com/';

    public const CONFIG_FILE = 'application/config/config.php';

    /** Limites contra zip bomba: tamanho descompactado e número de arquivos. */
    public const MAX_BYTES_DESCOMPACTADO = 536870912; // 512 MB

    public const MAX_ARQUIVOS = 50000;

    /** Caminhos (relativos à raiz do projeto) que a atualização nunca sobrescreve. */
    public const PROTEGIDOS = [
        '.env',
        'application/.env',
        'install/',
        'assets/anexos/',
        'assets/arquivos/',
        'assets/uploads/',
        'assets/userImage/',
        'application/storage/',
        'application/logs/',
        'application/cache/',
        'ci_sessions/',
    ];

    /** @var object Instância do CodeIgniter, injetada por get_instance() no construtor */
    public $ci;

    private string $raiz;

    private string $usuario;

    private string $repositorio;

    private string $versaoAtual;

    private bool $prereleases;

    /** @var list<string> */
    private array $ignorados;

    /** @var callable(string $url, ?string $destino): string */
    private $http;

    /**
     * @param  array{raiz?: string, usuario?: string, repositorio?: string, versao_atual?: string, prereleases?: bool, ignorados?: list<string>, http?: callable}|null  $opcoes  Para os testes; no framework vem vazio.
     */
    public function __construct($opcoes = null)
    {
        $opcoes = is_array($opcoes) ? $opcoes : [];

        if (! isset($opcoes['usuario'])) {
            $this->ci = &get_instance();
            $this->ci->load->config('github_updater');
        }

        $this->raiz = rtrim((string) ($opcoes['raiz'] ?? FCPATH), '/\\');
        $this->usuario = (string) ($opcoes['usuario'] ?? $this->ci->config->item('github_user'));
        $this->repositorio = (string) ($opcoes['repositorio'] ?? $this->ci->config->item('github_repo'));
        $this->versaoAtual = (string) ($opcoes['versao_atual'] ?? config_item('app_version'));
        $this->prereleases = (bool) ($opcoes['prereleases'] ?? $this->ci->config->item('github_prereleases'));
        $this->ignorados = $opcoes['ignorados'] ?? (array) ($this->ci->config->item('ignored_files') ?? []);
        $this->http = $opcoes['http'] ?? [$this, 'requisitar'];
    }

    public function versaoAtual(): string
    {
        return $this->versaoAtual;
    }

    /**
     * Última release publicada (rascunho nunca; pré-release só com
     * UPDATER_PRERELEASES=true), ou null se não houver nenhuma.
     *
     * @return array{tag: string, versao: string, nome: string, notas: string, prerelease: bool, publicada_em: string, zip_url: string, sha_url: string, digest: ?string}|null
     */
    public function ultimaRelease(): ?array
    {
        $lista = json_decode(($this->http)(self::API_URL . $this->usuario . '/' . $this->repositorio . '/releases?per_page=20', null), true);
        if (! is_array($lista) || ! array_is_list($lista)) {
            throw new RuntimeException('Resposta inesperada do GitHub ao buscar as releases.');
        }

        $candidatas = [];
        foreach ($lista as $release) {
            if (! is_array($release) || ! empty($release['draft']) || (! $this->prereleases && ! empty($release['prerelease']))) {
                continue;
            }

            $tag = (string) ($release['tag_name'] ?? '');
            if (! preg_match('/^v?\d+\.\d+\.\d+([-.+][0-9A-Za-z.\-+]*)?$/', $tag)) {
                continue;
            }

            $candidatas[] = [$release, $tag, self::versaoDaTag($tag)];
        }

        if ($candidatas === []) {
            return null;
        }

        // A mais nova pela versão, não pela ordem em que o GitHub listou.
        usort($candidatas, static fn (array $a, array $b): int => version_compare($b[2], $a[2]));
        [$release, $tag, $versao] = $candidatas[0];

        $base = self::GITHUB_URL . $this->usuario . '/' . $this->repositorio . '/releases/download/' . $tag . '/';
        $zip = $this->asset($release, $tag . '-release.zip', $base);
        $sha = $this->asset($release, $tag . '-release.zip.sha256', $base);

        return [
            'tag' => $tag,
            'versao' => $versao,
            'nome' => (string) ($release['name'] ?? $tag),
            'notas' => (string) ($release['body'] ?? ''),
            'prerelease' => ! empty($release['prerelease']),
            'publicada_em' => (string) ($release['published_at'] ?? ''),
            'zip_url' => $zip['browser_download_url'] ?? '',
            'sha_url' => $sha['browser_download_url'] ?? '',
            'digest' => isset($zip['digest']) && preg_match('/^sha256:([0-9a-f]{64})$/i', (string) $zip['digest'], $m) ? strtolower($m[1]) : null,
        ];
    }

    /** "v5.0.0-beta.1" vira "5.0.0-beta.1". */
    public static function versaoDaTag(string $tag): string
    {
        return ltrim($tag, 'vV');
    }

    public function temAtualizacao(?array $release): bool
    {
        return $release !== null && version_compare($release['versao'], $this->versaoAtual, '>');
    }

    /**
     * Verificações que impedem a atualização, em português para a tela: vazio
     * quando está tudo certo.
     *
     * @return list<string>
     */
    public function problemasDoAmbiente(): array
    {
        $problemas = [];

        if (! class_exists('ZipArchive')) {
            $problemas[] = 'A extensão zip do PHP não está habilitada.';
        }
        if (! function_exists('curl_init')) {
            $problemas[] = 'A extensão curl do PHP não está habilitada.';
        }
        foreach (['application/cache', 'application/config', 'application/controllers', 'assets', 'index.php'] as $caminho) {
            if (! is_writable($this->raiz . '/' . $caminho)) {
                $problemas[] = 'Sem permissão de escrita em ' . $caminho . '.';
            }
        }

        return $problemas;
    }

    /**
     * Baixa, confere e aplica a release $tag, que precisa ser a última.
     *
     * @return array{versao: string, arquivos: int, backup: string}
     *
     * @throws RuntimeException com a mensagem para mostrar ao admin
     */
    public function atualizar(string $tag): array
    {
        if ($this->problemasDoAmbiente() !== []) {
            throw new RuntimeException(implode(' ', $this->problemasDoAmbiente()));
        }

        $release = $this->ultimaRelease();
        if ($release === null || $release['tag'] !== $tag) {
            throw new RuntimeException('Essa não é mais a versão mais recente. Verifique a atualização de novo.');
        }
        if (! $this->temAtualizacao($release)) {
            throw new RuntimeException('O Map-OS já está na versão ' . $this->versaoAtual . '.');
        }

        $trabalho = $this->raiz . '/application/cache/atualizacao';
        if (! is_dir($trabalho) && ! mkdir($trabalho, 0755, true) && ! is_dir($trabalho)) {
            throw new RuntimeException('Não foi possível criar a pasta de trabalho da atualização.');
        }

        $trava = fopen($trabalho . '/atualizacao.lock', 'c');
        if ($trava === false || ! flock($trava, LOCK_EX | LOCK_NB)) {
            throw new RuntimeException('Já existe uma atualização em andamento.');
        }

        $pasta = $trabalho . '/' . bin2hex(random_bytes(6));
        mkdir($pasta, 0755);

        try {
            @set_time_limit(0);

            $zip = $pasta . '/release.zip';
            $this->baixarEConferir($release, $zip);

            $extraido = $pasta . '/novo';
            $arquivos = $this->extrair($zip, $extraido);

            $backup = $trabalho . '/backup-' . preg_replace('/[^0-9A-Za-z.\-]/', '_', $this->versaoAtual) . '-' . date('Ymd-His');
            $this->aplicar($extraido, $arquivos, $backup);
            $this->gravarVersao($release['versao']);
            $this->limparBackupsAntigos($trabalho, $backup);

            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }

            return ['versao' => $release['versao'], 'arquivos' => count($arquivos), 'backup' => basename($backup)];
        } finally {
            self::apagarPasta($pasta);
            flock($trava, LOCK_UN);
            fclose($trava);
        }
    }

    /** Baixa o zip e o `.sha256` e só deixa o zip no disco se o hash bater. */
    public function baixarEConferir(array $release, string $destinoZip): void
    {
        if ($release['zip_url'] === '' || $release['sha_url'] === '') {
            throw new RuntimeException('A release ' . $release['tag'] . ' não tem o zip e o arquivo .sha256 publicados. Por segurança, a atualização foi cancelada.');
        }

        // O .sha256 é "hash  nome-do-arquivo" (saída do sha256sum).
        $linha = trim((string) ($this->http)($release['sha_url'], null));
        if (! preg_match('/^([0-9a-fA-F]{64})(?:\s+\*?(\S+))?$/', strtok($linha, "\r\n") ?: '', $m)) {
            throw new RuntimeException('O arquivo .sha256 da release está em um formato inesperado.');
        }
        $esperado = strtolower($m[1]);
        if (isset($m[2]) && $m[2] !== $release['tag'] . '-release.zip') {
            throw new RuntimeException('O arquivo .sha256 é de outro arquivo (' . $m[2] . ').');
        }
        if ($release['digest'] !== null && ! hash_equals($release['digest'], $esperado)) {
            throw new RuntimeException('O SHA256 publicado e o informado pelo GitHub não conferem. Atualização cancelada.');
        }

        ($this->http)($release['zip_url'], $destinoZip);

        $calculado = hash_file('sha256', $destinoZip);
        if ($calculado === false || ! hash_equals($esperado, $calculado)) {
            @unlink($destinoZip);

            throw new RuntimeException('O SHA256 do zip baixado não confere com o publicado. O arquivo foi descartado e nada foi alterado.');
        }
    }

    /**
     * Extrai o zip em $destino conferindo cada entrada. Devolve os caminhos
     * relativos dos arquivos extraídos.
     *
     * @return list<string>
     */
    public function extrair(string $zip, string $destino): array
    {
        $arquivo = new ZipArchive();
        if ($arquivo->open($zip) !== true) {
            throw new RuntimeException('Não foi possível abrir o zip da atualização.');
        }

        if ($arquivo->numFiles > self::MAX_ARQUIVOS) {
            $arquivo->close();

            throw new RuntimeException('O zip tem arquivos demais; atualização cancelada.');
        }

        mkdir($destino, 0755, true);
        $lista = [];
        $total = 0;

        try {
            for ($i = 0; $i < $arquivo->numFiles; $i++) {
                $info = $arquivo->statIndex($i);
                $nome = (string) $info['name'];

                if (self::caminhoInseguro($nome)) {
                    throw new RuntimeException('O zip tem um caminho inválido (' . $nome . '); atualização cancelada.');
                }

                // Link simbólico dentro do zip: pode apontar para fora da pasta.
                $arquivo->getExternalAttributesIndex($i, $sistema, $atributos);
                if ($sistema === ZipArchive::OPSYS_UNIX && (($atributos >> 16) & 0170000) === 0120000) {
                    throw new RuntimeException('O zip tem um link simbólico (' . $nome . '); atualização cancelada.');
                }

                if (str_ends_with($nome, '/')) {
                    continue;
                }

                $total += (int) $info['size'];
                if ($total > self::MAX_BYTES_DESCOMPACTADO) {
                    throw new RuntimeException('O zip descompactado é grande demais; atualização cancelada.');
                }

                $alvo = $destino . '/' . $nome;
                if (! is_dir(dirname($alvo)) && ! mkdir(dirname($alvo), 0755, true) && ! is_dir(dirname($alvo))) {
                    throw new RuntimeException('Não foi possível criar a pasta de ' . $nome . '.');
                }

                $entrada = $arquivo->getStream($info['name']);
                $saida = fopen($alvo, 'wb');
                if ($entrada === false || $saida === false || stream_copy_to_stream($entrada, $saida) === false) {
                    throw new RuntimeException('Não foi possível extrair ' . $nome . '.');
                }
                fclose($entrada);
                fclose($saida);

                $lista[] = $nome;
            }
        } finally {
            $arquivo->close();
        }

        if ($lista === []) {
            throw new RuntimeException('O zip da atualização está vazio.');
        }

        return $lista;
    }

    /** Caminho absoluto, com `..`, barra invertida, byte nulo ou letra de unidade. */
    public static function caminhoInseguro(string $nome): bool
    {
        if ($nome === '' || str_contains($nome, "\0") || str_contains($nome, '\\') || str_starts_with($nome, '/') || preg_match('/^[A-Za-z]:/', $nome)) {
            return true;
        }

        return in_array('..', explode('/', $nome), true);
    }

    public function protegido(string $relativo): bool
    {
        foreach (self::PROTEGIDOS as $protegido) {
            if ($relativo === rtrim($protegido, '/') || str_starts_with($relativo, rtrim($protegido, '/') . '/')) {
                return true;
            }
        }

        foreach ($this->ignorados as $ignorado) {
            if ($ignorado !== '' && str_contains($relativo, $ignorado)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Copia os arquivos extraídos por cima do projeto. Antes guarda o que
     * existe em $backup; se uma cópia falhar, restaura tudo.
     *
     * @param  list<string>  $arquivos
     */
    public function aplicar(string $extraido, array $arquivos, string $backup): void
    {
        $existentes = [];
        $criados = [];

        try {
            foreach ($arquivos as $relativo) {
                if ($this->protegido($relativo)) {
                    continue;
                }

                $alvo = $this->raiz . '/' . $relativo;

                if (is_link($alvo)) {
                    throw new RuntimeException('O destino ' . $relativo . ' é um link simbólico; atualização cancelada.');
                }

                $modo = is_file($alvo) ? (fileperms($alvo) & 0777) : null;

                if (is_file($alvo)) {
                    $copia = $backup . '/' . $relativo;
                    if (! is_dir(dirname($copia)) && ! mkdir(dirname($copia), 0755, true) && ! is_dir(dirname($copia))) {
                        throw new RuntimeException('Não foi possível criar o backup de ' . $relativo . '.');
                    }
                    if (! copy($alvo, $copia)) {
                        throw new RuntimeException('Não foi possível guardar o backup de ' . $relativo . '.');
                    }
                    $existentes[] = $relativo;
                } else {
                    $criados[] = $relativo;
                }

                if (! is_dir(dirname($alvo)) && ! mkdir(dirname($alvo), 0755, true) && ! is_dir(dirname($alvo))) {
                    throw new RuntimeException('Não foi possível criar a pasta de ' . $relativo . '.');
                }

                // Copia para um nome temporário e renomeia: um arquivo nunca fica pela metade.
                $temporario = $alvo . '.atualizando';
                if (! @copy($extraido . '/' . $relativo, $temporario) || ! @rename($temporario, $alvo)) {
                    @unlink($temporario);

                    throw new RuntimeException('Não foi possível gravar ' . $relativo . '.');
                }

                // O rename deixa o modo padrão; quem já existia mantém as permissões (ex.: executável).
                if ($modo !== null) {
                    @chmod($alvo, $modo);
                }
            }
        } catch (Throwable $erro) {
            foreach ($existentes as $relativo) {
                @copy($backup . '/' . $relativo, $this->raiz . '/' . $relativo);
            }
            foreach ($criados as $relativo) {
                @unlink($this->raiz . '/' . $relativo);
            }

            throw new RuntimeException('A atualização falhou e os arquivos foram restaurados. ' . $erro->getMessage(), 0, $erro);
        }
    }

    private function gravarVersao(string $versao): void
    {
        $arquivo = $this->raiz . '/' . self::CONFIG_FILE;
        $codigo = (string) file_get_contents($arquivo);
        $novo = preg_replace("/(\\\$config\\['app_version'\\]\\s*=\\s*)'[^']*';/", "\$1'" . addcslashes($versao, "\\'") . "';", $codigo, 1, $trocas);

        if ($trocas === 1 && $novo !== null && $novo !== $codigo) {
            file_put_contents($arquivo, $novo);
        }
    }

    /** Mantém só o backup mais recente (o desta atualização). */
    private function limparBackupsAntigos(string $trabalho, string $atual): void
    {
        foreach (glob($trabalho . '/backup-*', GLOB_ONLYDIR) ?: [] as $pasta) {
            if ($pasta !== $atual) {
                self::apagarPasta($pasta);
            }
        }
    }

    public static function apagarPasta(string $pasta): void
    {
        if (! is_dir($pasta) || is_link($pasta)) {
            return;
        }

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pasta, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
            $item->isDir() && ! $item->isLink() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($pasta);
    }

    /**
     * @return array<string, mixed>
     */
    private function asset(array $release, string $nome, string $prefixoEsperado): array
    {
        foreach ((array) ($release['assets'] ?? []) as $asset) {
            if (is_array($asset) && ($asset['name'] ?? null) === $nome) {
                // Só baixa do endereço oficial de download desta release.
                if (str_starts_with((string) ($asset['browser_download_url'] ?? ''), $prefixoEsperado . $nome)) {
                    return $asset;
                }
            }
        }

        return [];
    }

    /**
     * GET com TLS verificado. Com $destino, grava a resposta no arquivo (zip);
     * sem ele, devolve o corpo. Só HTTPS, inclusive nos redirecionamentos (o
     * GitHub redireciona os downloads de assets).
     */
    public function requisitar(string $url, ?string $destino = null): string
    {
        if (! str_starts_with($url, 'https://')) {
            throw new RuntimeException('Endereço de atualização inválido.');
        }

        $arquivo = $destino !== null ? fopen($destino, 'wb') : null;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 600,
            CURLOPT_FAILONERROR => true,
            CURLOPT_HTTPHEADER => ['User-Agent: Mapos', 'Accept: application/vnd.github+json'],
        ]);

        if ($arquivo !== null) {
            curl_setopt($ch, CURLOPT_FILE, $arquivo);
        } else {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }

        $resposta = curl_exec($ch);
        $erro = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);

        if ($arquivo !== null) {
            fclose($arquivo);
        }

        if ($erro !== null || $resposta === false) {
            if ($destino !== null) {
                @unlink($destino);
            }

            throw new RuntimeException('Não foi possível falar com o GitHub: ' . ($erro ?? 'resposta vazia') . '.');
        }

        return $destino !== null ? '' : (string) $resposta;
    }
}
