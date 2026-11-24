<?php

namespace Libraries\Api\Autenticacao;

use Closure;
use Libraries\Api\Http\HttpErro;

final class Sessoes
{
    public const TABELA = 'api_sessoes';

    public const TABELA_TOKENS = 'api_tokens_renovacao';

    public const TOLERANCIA_REUSO = 10;

    private Closure $relogio;

    public function __construct(
        private readonly object $db,
        private readonly int $duracaoRenovacao,
        private readonly int $duracaoSessao,
        ?Closure $relogio = null,
    ) {
        $this->relogio = $relogio ?? static fn (): int => time();
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /** @return array{sessao: string, refresh_token: string, refresh_expira_em: int} */
    public function criar(string $tipo, int $titular, ?string $ip, ?string $userAgent, ?string $dispositivo = null): array
    {
        $agora = $this->agora();
        $identificador = bin2hex(random_bytes(16));

        $this->db->insert(self::TABELA, [
            'identificador' => $identificador,
            'tipo' => $tipo,
            'titular_id' => $titular,
            'dispositivo' => $dispositivo !== null && $dispositivo !== '' ? mb_substr($dispositivo, 0, 100) : null,
            'ip' => $ip !== null ? mb_substr($ip, 0, 45) : null,
            'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 255) : null,
            'criada_em' => $this->data($agora),
            'ultimo_uso_em' => $this->data($agora),
            'expira_em' => $this->data($agora + $this->duracaoSessao),
        ]);
        $id = (int) $this->db->insert_id();

        [$token, $expira] = $this->emitirToken($id, $agora, $agora + $this->duracaoSessao);

        $this->limparDeVezEmQuando($agora);

        return ['sessao' => $identificador, 'refresh_token' => $token, 'refresh_expira_em' => $expira];
    }

    /** @return array{sessao: string, tipo: string, titular_id: int, refresh_token: string, refresh_expira_em: int} */
    public function renovar(string $tipo, string $token, ?string $ip, ?string $userAgent): array
    {
        $agora = $this->agora();
        $linha = $token === '' ? null : $this->db
            ->select('t.id, t.expira_em AS token_expira_em, t.usado_em, s.id AS sessao_id, s.identificador, s.tipo, s.titular_id, s.expira_em AS sessao_expira_em, s.revogada_em')
            ->from(self::TABELA_TOKENS . ' t')
            ->join(self::TABELA . ' s', 's.id = t.sessao_id')
            ->where('t.token_hash', self::hash($token))
            ->where('s.tipo', $tipo)
            ->limit(1)
            ->get()
            ->row();

        $negar = static fn (string $codigo, string $detalhe = 'Faça o login novamente.'): HttpErro => HttpErro::naoAutenticado($detalhe, $codigo);

        if (! $linha) {
            throw $negar('refresh_invalido');
        }
        if ($linha->revogada_em !== null) {
            throw $negar('sessao_encerrada');
        }
        if (strtotime((string) $linha->sessao_expira_em) <= $agora) {
            throw $negar('sessao_expirada');
        }
        if ($linha->usado_em !== null) {
            if ($agora - strtotime((string) $linha->usado_em) > self::TOLERANCIA_REUSO) {
                $this->revogarPorId((int) $linha->sessao_id, 'reuso_de_refresh', $agora);

                throw $negar('refresh_reutilizado', 'Este refresh token já foi usado. Por segurança a sessão foi encerrada; faça o login novamente.');
            }

            throw HttpErro::conflito('Este refresh token acabou de ser usado por outra requisição. Use o par de tokens que ela recebeu.', 'renovacao_concorrente');
        }
        if (strtotime((string) $linha->token_expira_em) <= $agora) {
            throw $negar('refresh_expirado');
        }

        $this->db->set('usado_em', $this->data($agora))->where('id', (int) $linha->id)->where('usado_em IS NULL', null, false)->update(self::TABELA_TOKENS);
        if ($this->db->affected_rows() !== 1) {
            throw HttpErro::conflito('Este refresh token acabou de ser usado por outra requisição. Use o par de tokens que ela recebeu.', 'renovacao_concorrente');
        }

        [$novo, $expira] = $this->emitirToken((int) $linha->sessao_id, $agora, strtotime((string) $linha->sessao_expira_em));

        $this->db->where('id', (int) $linha->sessao_id)->update(self::TABELA, [
            'ultimo_uso_em' => $this->data($agora),
            'ip' => $ip !== null ? mb_substr($ip, 0, 45) : null,
            'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 255) : null,
        ]);

        return [
            'sessao' => (string) $linha->identificador,
            'tipo' => (string) $linha->tipo,
            'titular_id' => (int) $linha->titular_id,
            'refresh_token' => $novo,
            'refresh_expira_em' => $expira,
        ];
    }

    public function ativa(string $identificador, string $tipo, int $titular): bool
    {
        return $this->db
            ->where('identificador', $identificador)
            ->where('tipo', $tipo)
            ->where('titular_id', $titular)
            ->where('revogada_em IS NULL', null, false)
            ->where('expira_em >', $this->data($this->agora()))
            ->count_all_results(self::TABELA) === 1;
    }

    /** @return list<object> */
    public function listar(string $tipo, int $titular): array
    {
        return $this->db
            ->select('identificador, dispositivo, ip, user_agent, criada_em, ultimo_uso_em, expira_em')
            ->where('tipo', $tipo)
            ->where('titular_id', $titular)
            ->where('revogada_em IS NULL', null, false)
            ->where('expira_em >', $this->data($this->agora()))
            ->order_by('ultimo_uso_em', 'DESC')
            ->get(self::TABELA)
            ->result();
    }

    public function encerrar(string $identificador, string $tipo, int $titular, string $motivo = 'logout'): bool
    {
        $this->db
            ->where('identificador', $identificador)
            ->where('tipo', $tipo)
            ->where('titular_id', $titular)
            ->where('revogada_em IS NULL', null, false)
            ->update(self::TABELA, ['revogada_em' => $this->data($this->agora()), 'motivo' => $motivo]);

        return $this->db->affected_rows() > 0;
    }

    public function encerrarDoTitular(string $tipo, int $titular, string $motivo, ?string $exceto = null): int
    {
        $this->db->where('tipo', $tipo)->where('titular_id', $titular)->where('revogada_em IS NULL', null, false);
        if ($exceto !== null) {
            $this->db->where('identificador !=', $exceto);
        }
        $this->db->update(self::TABELA, ['revogada_em' => $this->data($this->agora()), 'motivo' => $motivo]);

        return (int) $this->db->affected_rows();
    }

    private function revogarPorId(int $id, string $motivo, int $agora): void
    {
        $this->db->where('id', $id)->where('revogada_em IS NULL', null, false)->update(self::TABELA, ['revogada_em' => $this->data($agora), 'motivo' => $motivo]);
    }

    /** @return array{0: string, 1: int} */
    private function emitirToken(int $sessao, int $agora, int $limite): array
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $expira = min($agora + $this->duracaoRenovacao, $limite);

        $this->db->insert(self::TABELA_TOKENS, [
            'sessao_id' => $sessao,
            'token_hash' => self::hash($token),
            'criado_em' => $this->data($agora),
            'expira_em' => $this->data($expira),
        ]);

        return [$token, $expira];
    }

    private function limparDeVezEmQuando(int $agora): void
    {
        if (random_int(1, 50) !== 1) {
            return;
        }

        $limite = $this->data($agora - 86400);
        $this->db->where('expira_em <', $limite)->delete(self::TABELA_TOKENS);
        $antigas = $this->data($agora - 30 * 86400);
        $this->db->group_start()->where('expira_em <', $antigas)->or_where('revogada_em <', $antigas)->group_end()->delete(self::TABELA);
    }

    private function agora(): int
    {
        return ($this->relogio)();
    }

    private function data(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}
