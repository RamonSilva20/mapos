<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Refresh tokens revogáveis da API (#2869).
 *
 *     $token = $this->refresh_token->emitir($usuarioId, $ip, $userAgent);
 *     $r = $this->refresh_token->girar($token);   // ['ok' => true, 'usuario_id' => 7, 'refresh_token' => '...']
 *     $this->refresh_token->revogar($token);       // logout
 *     $this->refresh_token->revogarDoUsuario(7);   // troca de senha
 *
 * O token é aleatório (32 bytes) e só o SHA-256 dele vai para o banco.
 * Cada uso gira o token: o antigo é marcado como usado e nasce outro na mesma
 * familia. Um token já usado que volta depois da tolerância de rede
 * (TOLERANCIA_SEGUNDOS) indica cópia vazada: a familia inteira é revogada.
 * Sem a tabela (migration não rodada), nenhum refresh token é emitido e o
 * login segue com o access token, como antes.
 */
class Refresh_token
{
    /** Duração padrão: 30 dias (API_REFRESH_TOKEN_EXPIRE_TIME no .env). */
    public const DURACAO_PADRAO = 2592000;

    /** Repetição legítima (resposta perdida na rede) logo após o uso: recusa sem revogar. */
    public const TOLERANCIA_SEGUNDOS = 10;

    private const TABELA = 'api_refresh_tokens';

    /** @var mixed Query Builder do CodeIgniter */
    private $db;

    /** @var callable(): int */
    private $relogio;

    private int $duracao;

    private ?bool $ativo = null;

    /**
     * @param  array{db?: mixed, relogio?: callable, duracao?: int}|null  $opcoes  Para os testes.
     */
    public function __construct($opcoes = null)
    {
        $opcoes = is_array($opcoes) ? $opcoes : [];

        $this->db = $opcoes['db'] ?? get_instance()->db;
        $this->relogio = $opcoes['relogio'] ?? 'time';
        $this->duracao = (int) ($opcoes['duracao'] ?? ($_ENV['API_REFRESH_TOKEN_EXPIRE_TIME'] ?? self::DURACAO_PADRAO));
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public function duracao(): int
    {
        return $this->duracao;
    }

    /** Emite o primeiro token de uma sessão nova (login). Null se a tabela não existe. */
    public function emitir(int $usuarioId, ?string $ip = null, ?string $userAgent = null, ?string $familia = null): ?string
    {
        if (! $this->ativo()) {
            return null;
        }

        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $agora = $this->agora();

        $this->db->insert(self::TABELA, [
            'usuario_id' => $usuarioId,
            'familia' => $familia ?? bin2hex(random_bytes(16)),
            'token_hash' => self::hash($token),
            'criado_em' => $this->data($agora),
            'expira_em' => $this->data($agora + $this->duracao),
            'ip' => $ip !== null ? mb_substr($ip, 0, 45) : null,
            'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 255) : null,
        ]);

        return $token;
    }

    /**
     * Valida o token e o gira.
     *
     * @return array{ok: bool, motivo?: string, usuario_id?: int, familia?: string, refresh_token?: string}
     */
    public function girar(string $token, ?string $ip = null, ?string $userAgent = null): array
    {
        if (! $this->ativo() || $token === '') {
            return ['ok' => false, 'motivo' => 'invalido'];
        }

        $linha = $this->db->where('token_hash', self::hash($token))->limit(1)->get(self::TABELA)->row();
        if (! $linha) {
            return ['ok' => false, 'motivo' => 'invalido'];
        }

        $agora = $this->agora();

        if ($linha->revogado_em !== null) {
            return ['ok' => false, 'motivo' => 'revogado'];
        }

        if ($linha->usado_em !== null) {
            // Já girado: se passou da tolerância, alguém guardou uma cópia. Revoga tudo da sessão.
            if ($agora - strtotime($linha->usado_em) > self::TOLERANCIA_SEGUNDOS) {
                $this->revogarFamilia($linha->familia);
            }

            return ['ok' => false, 'motivo' => 'reutilizado'];
        }

        if (strtotime($linha->expira_em) <= $agora) {
            return ['ok' => false, 'motivo' => 'expirado'];
        }

        // Marca como usado só se ninguém marcou antes (duas renovações simultâneas).
        $this->db->where('id', $linha->id)->where('usado_em IS NULL', null, false)->update(self::TABELA, ['usado_em' => $this->data($agora)]);
        if ($this->db->affected_rows() !== 1) {
            return ['ok' => false, 'motivo' => 'reutilizado'];
        }

        return [
            'ok' => true,
            'usuario_id' => (int) $linha->usuario_id,
            'familia' => (string) $linha->familia,
            'refresh_token' => (string) $this->emitir((int) $linha->usuario_id, $ip, $userAgent, $linha->familia),
        ];
    }

    /** Sessão (familia) a que o refresh token pertence, para o access token carregá-la (`sid`). */
    public function familiaDoToken(string $token): ?string
    {
        if (! $this->ativo() || $token === '') {
            return null;
        }

        $linha = $this->db->select('familia')->where('token_hash', self::hash($token))->limit(1)->get(self::TABELA)->row();

        return $linha ? (string) $linha->familia : null;
    }

    /** A sessão ainda não foi revogada (logout, troca de senha, reuso)? Sem a tabela, não há o que revogar. */
    public function familiaAtiva(string $familia): bool
    {
        if (! $this->ativo()) {
            return true;
        }

        return $this->db->where('familia', $familia)->where('revogado_em IS NOT NULL', null, false)->count_all_results(self::TABELA) === 0;
    }

    /** Logout: revoga a sessão (familia) a que o token pertence. */
    public function revogar(string $token): bool
    {
        if (! $this->ativo() || $token === '') {
            return false;
        }

        $linha = $this->db->select('familia')->where('token_hash', self::hash($token))->limit(1)->get(self::TABELA)->row();
        if (! $linha) {
            return false;
        }

        $this->revogarFamilia($linha->familia);

        return true;
    }

    /** Troca de senha ou usuário desativado: nenhuma sessão do usuário renova mais. */
    public function revogarDoUsuario(int $usuarioId): void
    {
        if (! $this->ativo()) {
            return;
        }

        $this->db->where('usuario_id', $usuarioId)->where('revogado_em IS NULL', null, false)->update(self::TABELA, ['revogado_em' => $this->data($this->agora())]);
    }

    private function revogarFamilia(string $familia): void
    {
        $this->db->where('familia', $familia)->where('revogado_em IS NULL', null, false)->update(self::TABELA, ['revogado_em' => $this->data($this->agora())]);
    }

    private function ativo(): bool
    {
        if ($this->ativo === null) {
            $this->ativo = $this->db->table_exists(self::TABELA);
            if (! $this->ativo && function_exists('log_message')) {
                log_message('error', 'Refresh token desligado: rode as migrations (tabela ' . self::TABELA . ' não existe).');
            }
        }

        return $this->ativo;
    }

    private function agora(): int
    {
        return (int) ($this->relogio)();
    }

    private function data(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}
