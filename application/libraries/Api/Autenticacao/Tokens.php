<?php

namespace Libraries\Api\Autenticacao;

use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Libraries\Api\Http\HttpErro;
use Throwable;

final class Tokens
{
    public const AUDIENCIA = 'mapos-api';

    public const DESAFIO_2FA = 'desafio_2fa';

    private const ALGORITMO = 'HS256';

    private Closure $relogio;

    public function __construct(
        private readonly string $chave,
        private readonly string $emissor,
        private readonly int $duracao,
        ?Closure $relogio = null,
    ) {
        $this->relogio = $relogio ?? static fn (): int => time();
    }

    public function duracao(): int
    {
        return $this->duracao;
    }

    /** @return array{token: string, expira_em: int} */
    public function emitirAcesso(string $tipo, int $titular, string $sessao): array
    {
        return $this->emitir($tipo, $titular, $this->duracao, ['sid' => $sessao]);
    }

    /** @return array{token: string, expira_em: int} */
    public function emitirDesafio2fa(int $usuario, int $duracao = 300): array
    {
        return $this->emitir(self::DESAFIO_2FA, $usuario, $duracao);
    }

    public function validarAcesso(string $token, string $tipo): object
    {
        $claims = $this->decodificar($token);
        if (($claims->typ ?? null) !== $tipo || ! is_string($claims->sid ?? null) || $claims->sid === '') {
            throw HttpErro::naoAutenticado('Este token não vale para esta rota.', 'token_invalido');
        }

        return $claims;
    }

    public function validarDesafio2fa(string $token): int
    {
        try {
            return (int) $this->validarTemporario($token, self::DESAFIO_2FA, '', 'desafio_invalido')->sub;
        } catch (HttpErro) {
            throw HttpErro::naoAutenticado('O desafio expirou ou é inválido. Entre de novo com e-mail e senha.', 'desafio_invalido');
        }
    }

    /**
     * @param  array<string, mixed>  $dados
     * @return array{token: string, expira_em: int}
     */
    public function emitirTemporario(string $finalidade, int $titular, int $duracao, array $dados = []): array
    {
        return $this->emitir($finalidade, $titular, $duracao, $dados);
    }

    public function validarTemporario(string $token, string $finalidade, string $mensagem, string $codigo): object
    {
        try {
            $claims = $this->decodificar($token);
        } catch (HttpErro) {
            throw HttpErro::regraDeNegocio($mensagem, $codigo);
        }
        if (($claims->typ ?? null) !== $finalidade) {
            throw HttpErro::regraDeNegocio($mensagem, $codigo);
        }

        return $claims;
    }

    /**
     * @param  array<string, mixed>  $extras
     * @return array{token: string, expira_em: int}
     */
    private function emitir(string $tipo, int $titular, int $duracao, array $extras = []): array
    {
        $agora = ($this->relogio)();
        $expira = $agora + $duracao;

        $claims = [
            'iss' => $this->emissor,
            'aud' => self::AUDIENCIA,
            'sub' => (string) $titular,
            'typ' => $tipo,
            'iat' => $agora,
            'nbf' => $agora,
            'exp' => $expira,
            'jti' => bin2hex(random_bytes(12)),
        ] + $extras;

        return ['token' => JWT::encode($claims, $this->chave, self::ALGORITMO), 'expira_em' => $expira];
    }

    private function decodificar(string $token): object
    {
        $leeway = JWT::$leeway;
        $timestamp = JWT::$timestamp;
        JWT::$leeway = 5;
        JWT::$timestamp = ($this->relogio)();

        try {
            $claims = JWT::decode($token, new Key($this->chave, self::ALGORITMO));
        } catch (ExpiredException) {
            throw HttpErro::naoAutenticado('O access token expirou. Renove com POST /auth/renovar.', 'token_expirado');
        } catch (Throwable) {
            throw HttpErro::naoAutenticado('Access token inválido.', 'token_invalido');
        } finally {
            JWT::$leeway = $leeway;
            JWT::$timestamp = $timestamp;
        }

        $audiencia = $claims->aud ?? null;
        $audiencias = is_array($audiencia) ? $audiencia : [$audiencia];
        if (($claims->iss ?? null) !== $this->emissor || ! in_array(self::AUDIENCIA, $audiencias, true) || ! ctype_digit((string) ($claims->sub ?? ''))) {
            throw HttpErro::naoAutenticado('Access token inválido.', 'token_invalido');
        }

        return $claims;
    }
}
