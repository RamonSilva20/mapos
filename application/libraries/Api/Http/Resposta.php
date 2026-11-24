<?php

namespace Libraries\Api\Http;

use Closure;

final class Resposta
{
    /** @param  array<string, string>  $cabecalhos */
    public function __construct(
        public int $status = 200,
        public array $cabecalhos = [],
        public mixed $corpo = null,
        public ?Closure $envio = null,
    ) {
    }

    /** @param  array<string, string>  $cabecalhos */
    public static function json(mixed $corpo, int $status = 200, array $cabecalhos = []): self
    {
        return new self($status, ['Content-Type' => 'application/json; charset=utf-8'] + $cabecalhos, $corpo);
    }

    public static function dados(mixed $dados, int $status = 200): self
    {
        return self::json(['data' => $dados], $status);
    }

    public static function criado(mixed $dados, string $local): self
    {
        return self::json(['data' => $dados], 201, ['Location' => $local]);
    }

    /** @param  array{pagina: int, por_pagina: int, total: int}  $paginacao */
    public static function lista(array $itens, array $paginacao, array $extras = []): self
    {
        $paginas = $paginacao['por_pagina'] > 0 ? (int) ceil($paginacao['total'] / $paginacao['por_pagina']) : 0;

        return self::json([
            'data' => array_values($itens),
            'meta' => $paginacao + ['paginas' => $paginas] + $extras,
        ]);
    }

    public static function semConteudo(): self
    {
        return new self(204);
    }

    public static function erro(HttpErro $erro): self
    {
        return new self($erro->status, ['Content-Type' => 'application/problem+json; charset=utf-8'] + $erro->cabecalhos, $erro->corpo());
    }

    public static function arquivo(string $caminho, string $nome, ?string $mime = null, bool $baixar = true): self
    {
        $mime ??= (new \finfo(FILEINFO_MIME_TYPE))->file($caminho) ?: 'application/octet-stream';
        $nomeSeguro = preg_replace('/[^A-Za-z0-9._-]/', '_', $nome) ?: 'arquivo';

        return new self(200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) filesize($caminho),
            'Content-Disposition' => ($baixar ? 'attachment' : 'inline') . '; filename="' . $nomeSeguro . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], null, static function () use ($caminho): void {
            readfile($caminho);
        });
    }

    public static function conteudo(string $conteudo, string $nome, string $mime, bool $baixar = true): self
    {
        $nomeSeguro = preg_replace('/[^A-Za-z0-9._-]/', '_', $nome) ?: 'arquivo';

        return new self(200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) strlen($conteudo),
            'Content-Disposition' => ($baixar ? 'attachment' : 'inline') . '; filename="' . $nomeSeguro . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], null, static function () use ($conteudo): void {
            echo $conteudo;
        });
    }

    public function comCabecalho(string $nome, string $valor): self
    {
        $this->cabecalhos[$nome] = $valor;

        return $this;
    }

    public function enviar(): void
    {
        if (! headers_sent()) {
            http_response_code($this->status);
            header_remove('Set-Cookie');
            foreach ($this->cabecalhos as $nome => $valor) {
                header($nome . ': ' . $valor);
            }
        }

        if ($this->envio !== null) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            ($this->envio)();

            return;
        }

        if ($this->corpo !== null && $this->status !== 204) {
            echo json_encode($this->corpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }
}
