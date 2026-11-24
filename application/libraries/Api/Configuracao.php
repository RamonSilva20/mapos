<?php

namespace Libraries\Api;

final class Configuracao
{
    public const DURACAO_ACESSO_PADRAO = 900;

    public const DURACAO_RENOVACAO_PADRAO = 2592000;

    public const DURACAO_SESSAO_PADRAO = 7776000;

    /** @param  list<string>  $origensCors */
    public function __construct(
        public readonly string $chave,
        public readonly string $emissor,
        public readonly int $duracaoAcesso = self::DURACAO_ACESSO_PADRAO,
        public readonly int $duracaoRenovacao = self::DURACAO_RENOVACAO_PADRAO,
        public readonly int $duracaoSessao = self::DURACAO_SESSAO_PADRAO,
        public readonly array $origensCors = [],
        public readonly int $limitePorMinuto = 300,
        public readonly bool $producao = false,
    ) {
    }

    /** @param  array<string, mixed>  $env */
    public static function doAmbiente(array $env, string $emissor, bool $producao): self
    {
        $inteiro = static fn (string $nome, int $padrao, int $min, int $max): int => max($min, min($max, (int) (($env[$nome] ?? '') !== '' ? $env[$nome] : $padrao)));

        return new self(
            (string) ($env['API_JWT_KEY'] ?? ''),
            rtrim($emissor, '/'),
            $inteiro('API_TOKEN_EXPIRE_TIME', self::DURACAO_ACESSO_PADRAO, 60, 3600),
            $inteiro('API_REFRESH_TOKEN_EXPIRE_TIME', self::DURACAO_RENOVACAO_PADRAO, 3600, 31536000),
            $inteiro('API_SESSION_MAX_LIFETIME', self::DURACAO_SESSAO_PADRAO, 3600, 63072000),
            array_values(array_filter(array_map('trim', explode(',', (string) ($env['API_CORS_ORIGINS'] ?? ''))))),
            max(0, (int) ($env['API_RATE_LIMIT'] ?? 300)),
            $producao,
        );
    }

    public function chaveInsegura(): bool
    {
        $chave = trim($this->chave);

        return $chave === '' || $chave === 'enter_jwt_key' || strlen($chave) < 32;
    }
}
