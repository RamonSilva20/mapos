<?php

namespace Libraries\Api\Http;

final class Requisicao
{
    private ?array $corpoDecodificado = null;

    /**
     * @param  array<string, mixed>  $consulta
     * @param  array<string, string>  $cabecalhos  nomes em minúsculas
     * @param  array<string, mixed>  $formulario
     * @param  array<string, mixed>  $arquivos
     */
    public function __construct(
        public readonly string $metodo,
        public readonly string $caminho,
        public readonly array $consulta = [],
        public readonly array $cabecalhos = [],
        private readonly string $corpoBruto = '',
        public readonly array $formulario = [],
        public readonly array $arquivos = [],
        public readonly string $ip = '0.0.0.0',
    ) {
    }

    public static function dosGlobais(string $caminho, string $ip): self
    {
        $cabecalhos = [];
        foreach ($_SERVER as $chave => $valor) {
            if (str_starts_with($chave, 'HTTP_')) {
                $cabecalhos[strtolower(str_replace('_', '-', substr($chave, 5)))] = (string) $valor;
            }
        }
        foreach (['CONTENT_TYPE' => 'content-type', 'CONTENT_LENGTH' => 'content-length'] as $chave => $nome) {
            if (isset($_SERVER[$chave])) {
                $cabecalhos[$nome] = (string) $_SERVER[$chave];
            }
        }
        if (! isset($cabecalhos['authorization']) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $cabecalhos['authorization'] = (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        $metodo = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $multipart = str_starts_with(strtolower($cabecalhos['content-type'] ?? ''), 'multipart/form-data');
        $formulario = $_POST;
        $arquivos = $_FILES;
        if ($multipart && $metodo !== 'POST' && function_exists('request_parse_body')) {
            try {
                [$formulario, $arquivos] = request_parse_body();
                $_FILES = $arquivos;
            } catch (\RequestParseBodyException) {
                throw HttpErro::requisicaoInvalida('Não foi possível ler o corpo multipart. Confira o limite de tamanho do servidor.', 'multipart_invalido');
            }
        }

        return new self(
            $metodo,
            '/' . trim($caminho, '/'),
            $_GET,
            $cabecalhos,
            $multipart ? '' : (string) file_get_contents('php://input'),
            $multipart ? $formulario : [],
            $multipart ? $arquivos : [],
            $ip,
        );
    }

    public function cabecalho(string $nome): ?string
    {
        return $this->cabecalhos[strtolower($nome)] ?? null;
    }

    public function tokenBearer(): ?string
    {
        $valor = trim((string) $this->cabecalho('authorization'));
        if (! preg_match('/^Bearer\s+([A-Za-z0-9\-_.~+\/]+=*)$/i', $valor, $m)) {
            return null;
        }

        return $m[1];
    }

    public function userAgent(): ?string
    {
        $valor = $this->cabecalho('user-agent');

        return $valor === null || $valor === '' ? null : mb_substr($valor, 0, 255);
    }

    public function ehMultipart(): bool
    {
        return str_starts_with(strtolower((string) $this->cabecalho('content-type')), 'multipart/form-data');
    }

    /**
     * @param  array<string, mixed>  $arquivos
     * @return array<string, mixed>
     */
    public static function normalizarArquivos(array $arquivos): array
    {
        $saida = [];
        foreach ($arquivos as $campo => $arquivo) {
            if (! is_array($arquivo) || ! isset($arquivo['name'])) {
                continue;
            }
            if (! is_array($arquivo['name'])) {
                $saida[$campo] = $arquivo;

                continue;
            }
            $lista = [];
            foreach (array_keys($arquivo['name']) as $indice) {
                if ((int) ($arquivo['error'][$indice] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $lista[] = [
                    'name' => $arquivo['name'][$indice],
                    'type' => $arquivo['type'][$indice] ?? '',
                    'tmp_name' => $arquivo['tmp_name'][$indice] ?? '',
                    'error' => $arquivo['error'][$indice] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $arquivo['size'][$indice] ?? 0,
                ];
            }
            $saida[$campo] = $lista;
        }

        return $saida;
    }

    /** @return array<string, mixed> */
    public function corpo(): array
    {
        if ($this->corpoDecodificado !== null) {
            return $this->corpoDecodificado;
        }

        if ($this->ehMultipart()) {
            return $this->corpoDecodificado = $this->formulario + self::normalizarArquivos($this->arquivos);
        }

        if (trim($this->corpoBruto) === '') {
            return $this->corpoDecodificado = [];
        }

        $tipo = strtolower(trim(explode(';', (string) $this->cabecalho('content-type'))[0]));
        if ($tipo !== 'application/json' && ! str_ends_with($tipo, '+json')) {
            throw HttpErro::tipoNaoSuportado('Envie o corpo como application/json.');
        }

        try {
            $dados = json_decode($this->corpoBruto, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw HttpErro::requisicaoInvalida('O corpo da requisição não é um JSON válido.', 'json_invalido');
        }

        if (! is_array($dados) || ($dados !== [] && array_is_list($dados))) {
            throw HttpErro::requisicaoInvalida('O corpo da requisição precisa ser um objeto JSON.', 'json_invalido');
        }

        return $this->corpoDecodificado = $dados;
    }
}
