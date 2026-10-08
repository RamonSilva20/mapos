<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Limite de tentativas de login (#2870).
 *
 *     $this->load->library('Limite_login');
 *     if ($this->limite_login->bloqueado('usuario', $email, $ip)) { ...mensagem genérica... }
 *     ...
 *     $this->limite_login->registrarFalha('usuario', $email, $ip);   // senha errada ou e-mail inexistente
 *     $this->limite_login->registrarSucesso('usuario', $email);      // login aceito
 *
 * Escopos: `usuario` (painel e API de usuários) e `cliente` (área do cliente e
 * API do cliente). O painel e a API usam o mesmo escopo, para não somar
 * tentativas por caminhos diferentes.
 *
 * Cada falha conta em duas chaves: o e-mail informado (exista ou não a conta,
 * para o bloqueio não revelar quais e-mails têm cadastro) e o IP. Ao chegar ao
 * limite, a chave fica bloqueada por um tempo que dobra a cada nova falha
 * (backoff progressivo), até 1 hora. O limite do IP é maior, porque vários
 * usuários podem sair pelo mesmo IP (NAT de empresa). Sem falhas por
 * JANELA_SEGUNDOS, o contador recomeça.
 *
 * A tabela guarda só o SHA-256 das chaves. Se ela ainda não existir (migration
 * não rodada), o limite fica desligado e o login segue normal.
 */
class Limite_login
{
    public const LIMITE_EMAIL = 5;

    public const LIMITE_IP = 20;

    public const BLOQUEIO_INICIAL_SEGUNDOS = 60;

    public const BLOQUEIO_MAXIMO_SEGUNDOS = 3600;

    public const JANELA_SEGUNDOS = 3600;

    /** Mensagem única para qualquer bloqueio: não diz se foi pelo e-mail ou pelo IP. */
    public const MENSAGEM = 'Muitas tentativas de acesso. Aguarde alguns minutos e tente novamente.';

    private const TABELA = 'login_attempts';

    private const ESCOPOS = ['usuario', 'cliente'];

    /** @var mixed Query Builder do CodeIgniter (CI_DB_query_builder) */
    private $db;

    /** @var callable(string $tarefa, string $ip): void */
    private $auditar;

    /** @var callable(): int */
    private $relogio;

    private ?bool $ativo = null;

    /**
     * @param  array{db?: mixed, auditar?: callable, relogio?: callable}|null  $opcoes  Para os testes; no framework vem vazio.
     */
    public function __construct($opcoes = null)
    {
        $opcoes = is_array($opcoes) ? $opcoes : [];

        $this->db = $opcoes['db'] ?? get_instance()->db;
        $this->auditar = $opcoes['auditar'] ?? [$this, 'auditarNoBanco'];
        $this->relogio = $opcoes['relogio'] ?? 'time';
    }

    /**
     * Tempo de bloqueio, em segundos, para uma chave com $falhas falhas e o
     * limite $limite: 0 abaixo do limite; depois 1, 2, 4, 8... minutos, até
     * BLOQUEIO_MAXIMO_SEGUNDOS.
     */
    public static function duracaoBloqueio(int $falhas, int $limite): int
    {
        if ($falhas < $limite) {
            return 0;
        }

        $expoente = min($falhas - $limite, 16);

        return (int) min(self::BLOQUEIO_INICIAL_SEGUNDOS * (2 ** $expoente), self::BLOQUEIO_MAXIMO_SEGUNDOS);
    }

    /**
     * Diz se o e-mail ou o IP estão bloqueados agora neste escopo.
     */
    public function bloqueado(string $escopo, ?string $email, string $ip): bool
    {
        if (! $this->ativo()) {
            return false;
        }

        $agora = $this->agora();

        foreach ($this->chaves($escopo, $email, $ip) as [$tipo, $chave]) {
            $linha = $this->linha($escopo, $tipo, $chave);
            if ($linha && $linha->bloqueado_ate !== null && strtotime($linha->bloqueado_ate) > $agora) {
                return true;
            }
        }

        return false;
    }

    /**
     * Conta uma falha para o e-mail e o IP. Devolve true se a falha deixou
     * alguma das chaves bloqueada.
     */
    public function registrarFalha(string $escopo, ?string $email, string $ip): bool
    {
        if (! $this->ativo()) {
            return false;
        }

        $agora = $this->agora();
        $bloqueou = false;

        foreach ($this->chaves($escopo, $email, $ip) as [$tipo, $chave]) {
            $limite = $tipo === 'ip' ? self::LIMITE_IP : self::LIMITE_EMAIL;
            $linha = $this->linha($escopo, $tipo, $chave);

            $falhas = 1;
            if ($linha) {
                $recente = strtotime($linha->ultima_falha) > $agora - self::JANELA_SEGUNDOS;
                $aindaBloqueada = $linha->bloqueado_ate !== null && strtotime($linha->bloqueado_ate) > $agora;
                $falhas = ($recente || $aindaBloqueada) ? (int) $linha->falhas + 1 : 1;
            }

            $duracao = self::duracaoBloqueio($falhas, $limite);
            $dados = [
                'falhas' => $falhas,
                'bloqueado_ate' => $duracao > 0 ? date('Y-m-d H:i:s', $agora + $duracao) : null,
                'ultima_falha' => date('Y-m-d H:i:s', $agora),
            ];

            if ($linha) {
                $this->db->where('id', $linha->id)->update(self::TABELA, $dados);
            } else {
                $this->db->insert(self::TABELA, $dados + ['escopo' => $escopo, 'tipo' => $tipo, 'chave' => $chave]);
            }

            if ($duracao > 0) {
                $bloqueou = true;
                ($this->auditar)(
                    sprintf('Login bloqueado por %d min após %d tentativas com falha (%s, por %s)', intdiv($duracao, 60), $falhas, $escopo === 'usuario' ? 'painel' : 'área do cliente', $tipo === 'ip' ? 'IP' : 'e-mail'),
                    $ip
                );
            }
        }

        $this->limparAntigas($agora);

        return $bloqueou;
    }

    /**
     * Login aceito: zera o contador do e-mail. O do IP continua, para que um
     * login válido não libere tentativas contra outras contas.
     */
    public function registrarSucesso(string $escopo, ?string $email): void
    {
        if (! $this->ativo() || $this->normalizarEmail($email) === '') {
            return;
        }

        $this->db
            ->where('escopo', $this->escopo($escopo))
            ->where('tipo', 'email')
            ->where('chave', $this->hash('email', $this->normalizarEmail($email)))
            ->delete(self::TABELA);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function chaves(string $escopo, ?string $email, string $ip): array
    {
        $this->escopo($escopo);

        $chaves = [['ip', $this->hash('ip', trim($ip))]];
        $emailNormalizado = $this->normalizarEmail($email);
        if ($emailNormalizado !== '') {
            array_unshift($chaves, ['email', $this->hash('email', $emailNormalizado)]);
        }

        return $chaves;
    }

    private function escopo(string $escopo): string
    {
        if (! in_array($escopo, self::ESCOPOS, true)) {
            throw new InvalidArgumentException("Escopo de login desconhecido: {$escopo}");
        }

        return $escopo;
    }

    private function normalizarEmail(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }

    private function hash(string $tipo, string $valor): string
    {
        return hash('sha256', $tipo . '|' . $valor);
    }

    private function linha(string $escopo, string $tipo, string $chave): ?object
    {
        $linha = $this->db
            ->where('escopo', $escopo)
            ->where('tipo', $tipo)
            ->where('chave', $chave)
            ->limit(1)
            ->get(self::TABELA)
            ->row();

        return $linha ?: null;
    }

    /** Apaga as linhas paradas há mais de um dia e sem bloqueio vigente. */
    private function limparAntigas(int $agora): void
    {
        $this->db
            ->where('ultima_falha <', date('Y-m-d H:i:s', $agora - 86400))
            ->group_start()
            ->where('bloqueado_ate', null)
            ->or_where('bloqueado_ate <', date('Y-m-d H:i:s', $agora))
            ->group_end()
            ->delete(self::TABELA);
    }

    private function ativo(): bool
    {
        if ($this->ativo === null) {
            $this->ativo = $this->db->table_exists(self::TABELA);
            if (! $this->ativo && function_exists('log_message')) {
                log_message('error', 'Limite de login desligado: rode as migrations (tabela ' . self::TABELA . ' não existe).');
            }
        }

        return $this->ativo;
    }

    private function agora(): int
    {
        return (int) ($this->relogio)();
    }

    private function auditarNoBanco(string $tarefa, string $ip): void
    {
        $ci = get_instance();
        $ci->load->model('Audit_model');
        $ci->Audit_model->add([
            'usuario' => 'Sistema',
            'tarefa' => $tarefa,
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'ip' => $ip,
        ]);
    }
}
