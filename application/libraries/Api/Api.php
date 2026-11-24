<?php

namespace Libraries\Api;

use Libraries\Api\Autenticacao\Sessoes;
use Libraries\Api\Autenticacao\Titular;
use Libraries\Api\Autenticacao\Tokens;
use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Requisicao;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Roteamento\Roteador;
use Throwable;

final class Api
{
    public const LIMITE_AUTENTICACAO = 30;

    private const CABECALHOS_EXPOSTOS = 'Location, Retry-After, RateLimit-Limit, RateLimit-Remaining, RateLimit-Reset, Content-Disposition';

    private const CABECALHOS_PERMITIDOS = 'Authorization, Content-Type, Accept, Accept-Language, X-Requested-With';

    private Tokens $tokens;

    private Sessoes $sessoes;

    /** @var array<string, string> */
    private array $cabecalhosDoLimite = [];

    public function __construct(
        private readonly object $ci,
        private readonly Roteador $roteador,
        private readonly Configuracao $configuracao,
        private readonly ?\Closure $migracoesPendentes = null,
    ) {
        $this->tokens = new Tokens($configuracao->chave, $configuracao->emissor, $configuracao->duracaoAcesso);
        $this->sessoes = new Sessoes($ci->db, $configuracao->duracaoRenovacao, $configuracao->duracaoSessao);
    }

    public function atender(Requisicao $requisicao): Resposta
    {
        $origem = $this->origemPermitida($requisicao->cabecalho('origin'));

        try {
            if ($requisicao->metodo === 'OPTIONS') {
                $resposta = $this->preflight($requisicao, $origem);
            } else {
                $resposta = $this->processar($requisicao);
            }
        } catch (HttpErro $erro) {
            $resposta = Resposta::erro($erro);
        } catch (Throwable $erro) {
            log_message('error', 'API: ' . $erro::class . ': ' . $erro->getMessage() . ' em ' . $erro->getFile() . ':' . $erro->getLine());
            $resposta = Resposta::erro(HttpErro::interno());
        }

        foreach ($this->cabecalhosDoLimite as $nome => $valor) {
            $resposta->comCabecalho($nome, $valor);
        }
        if ($origem !== null) {
            $resposta->comCabecalho('Access-Control-Allow-Origin', $origem);
            $resposta->comCabecalho('Access-Control-Expose-Headers', self::CABECALHOS_EXPOSTOS);
        }
        if ($this->configuracao->origensCors !== []) {
            $resposta->comCabecalho('Vary', 'Origin');
        }
        if (! isset($resposta->cabecalhos['Cache-Control'])) {
            $resposta->comCabecalho('Cache-Control', 'no-store');
        }

        return $resposta;
    }

    private function processar(Requisicao $requisicao): Resposta
    {
        if ($this->configuracao->chaveInsegura()) {
            log_message('error', 'API v2 indisponível: API_JWT_KEY vazia, com menos de 32 caracteres ou igual ao valor do .env.example.');

            throw HttpErro::indisponivel('A API está indisponível: o administrador precisa configurar a chave dos tokens (API_JWT_KEY).', 'chave_insegura');
        }

        [$rota, $parametros] = $this->roteador->encontrar($requisicao->metodo, $requisicao->caminho);

        $this->limitar($requisicao, $rota->limite === null ? 'geral' : 'autenticacao', $rota->limite === null ? $this->configuracao->limitePorMinuto : self::LIMITE_AUTENTICACAO);

        if (! $rota->liberadaComBancoDesatualizado && $this->migracoesPendentes !== null && ($this->migracoesPendentes)()) {
            throw HttpErro::indisponivel('O banco de dados precisa ser atualizado. Peça ao administrador para abrir o painel e atualizar.', 'banco_desatualizado');
        }

        $contexto = new Contexto($this->ci, $requisicao, $rota, $parametros, $this->configuracao, $this->tokens, $this->sessoes);

        if ($rota->autenticacao !== null) {
            $titular = $this->autenticar($requisicao, $rota);
            $contexto->definirTitular($titular);
            $this->autorizar($titular, $rota);
            $this->preencherSessao($titular);
        }

        [$classe, $metodo] = $rota->acao;

        ob_start();
        try {
            $resposta = (new $classe($contexto))->{$metodo}();
        } finally {
            $saidaPerdida = (string) ob_get_clean();
            if ($saidaPerdida !== '') {
                log_message('error', 'API: saída inesperada em ' . $rota->metodo . ' ' . $rota->caminho . ': ' . mb_substr(strip_tags($saidaPerdida), 0, 500));
            }
        }

        return $resposta;
    }

    private function autenticar(Requisicao $requisicao, Rota $rota): Titular
    {
        $token = $requisicao->tokenBearer();
        if ($token === null) {
            throw HttpErro::naoAutenticado();
        }

        $claims = $this->tokens->validarAcesso($token, $rota->autenticacao);
        $id = (int) $claims->sub;

        if (! $this->sessoes->ativa((string) $claims->sid, $rota->autenticacao, $id)) {
            throw HttpErro::naoAutenticado('A sessão foi encerrada. Faça o login novamente.', 'sessao_encerrada');
        }

        return $rota->autenticacao === Rota::CLIENTE
            ? $this->titularCliente($id, (string) $claims->sid)
            : $this->titularUsuario($id, (string) $claims->sid);
    }

    private function titularUsuario(int $id, string $sessao): Titular
    {
        $db = $this->ci->db;
        $usuario = $db->where('idUsuarios', $id)->limit(1)->get('usuarios')->row();
        if (! $usuario || (int) $usuario->situacao !== 1) {
            throw HttpErro::naoAutenticado('Sua conta está desativada.', 'conta_desativada');
        }
        if (contaExpirada($usuario->dataExpiracao ?? null)) {
            throw HttpErro::proibido('A conta do usuário está expirada. Fale com o administrador do sistema.', 'conta_expirada');
        }

        $grupo = $db->where('idPermissao', (int) $usuario->permissoes_id)->limit(1)->get('permissoes')->row();
        if (! $grupo || (int) $grupo->situacao !== 1) {
            throw HttpErro::proibido('O grupo de permissão desta conta está desativado. Fale com o administrador do sistema.', 'grupo_desativado');
        }

        $pendente = empty($usuario->totp_ativado_em) && (int) ($grupo->exigir_2fa ?? 0) === 1;

        return new Titular(
            Rota::USUARIO,
            $id,
            (string) $usuario->nome,
            $sessao,
            $usuario,
            permissoesMarcadas($grupo->permissoes ?? null),
            (int) $usuario->permissoes_id,
            $pendente,
        );
    }

    private function titularCliente(int $id, string $sessao): Titular
    {
        $cliente = $this->ci->db->where('idClientes', $id)->limit(1)->get('clientes')->row();
        if (! $cliente) {
            throw HttpErro::naoAutenticado('Faça o login novamente.', 'conta_desativada');
        }

        return new Titular(Rota::CLIENTE, $id, (string) $cliente->nomeCliente, $sessao, $cliente);
    }

    private function preencherSessao(Titular $titular): void
    {
        if (! (($this->ci->session ?? null) instanceof SessaoEmMemoria)) {
            return;
        }

        $this->ci->session->set_userdata($titular->tipo === Rota::USUARIO
            ? ['id_admin' => $titular->id, 'nome_admin' => $titular->nome, 'email_admin' => (string) $titular->registro->email, 'permissao' => $titular->grupo, 'logado' => true]
            : ['cliente_id' => $titular->id, 'nome' => $titular->nome, 'email' => (string) $titular->registro->email, 'conectado' => true, 'isCliente' => true]);
    }

    private function autorizar(Titular $titular, Rota $rota): void
    {
        if ($titular->doisFatoresPendente && ! $rota->liberadaCom2faPendente) {
            throw HttpErro::proibido('Seu grupo de permissão exige a autenticação em dois fatores. Ative em POST /conta/dois-fatores para continuar.', 'dois_fatores_obrigatorio');
        }

        if ($rota->permissao === null) {
            return;
        }

        $permissoes = $rota->permissao['permissoes'];
        $permitido = $rota->permissao['tipo'] === 'todas'
            ? count(array_filter($permissoes, $titular->pode(...))) === count($permissoes)
            : $titular->podeQualquer($permissoes);

        if (! $permitido) {
            throw HttpErro::proibido();
        }
    }

    private function limitar(Requisicao $requisicao, string $balde, int $limite): void
    {
        $db = $this->ci->db;
        if ($limite <= 0 || ! $db->table_exists('api_rate_limit')) {
            return;
        }

        $agora = time();
        $janela = intdiv($agora, 60);
        $chave = hash_hmac('sha256', $balde . '|' . $requisicao->ip, (string) config_item('encryption_key'));

        $sql = $db->dbdriver === 'sqlite3'
            ? 'INSERT INTO api_rate_limit (chave, janela, total) VALUES (?, ?, 1) ON CONFLICT (chave, janela) DO UPDATE SET total = total + 1'
            : 'INSERT INTO api_rate_limit (chave, janela, total) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE total = total + 1';
        $db->query($sql, [$chave, $janela]);
        $total = (int) $db->select('total')->where(['chave' => $chave, 'janela' => $janela])->get('api_rate_limit')->row('total');

        if (random_int(1, 100) === 1) {
            $db->where('janela <', $janela - 5)->delete('api_rate_limit');
        }

        $reinicio = 60 - ($agora % 60);
        $this->cabecalhosDoLimite = [
            'RateLimit-Limit' => (string) $limite,
            'RateLimit-Remaining' => (string) max(0, $limite - $total),
            'RateLimit-Reset' => (string) $reinicio,
        ];

        if ($total > $limite) {
            throw HttpErro::muitasRequisicoes($reinicio);
        }
    }

    private function preflight(Requisicao $requisicao, ?string $origem): Resposta
    {
        $metodos = $this->roteador->metodosDoCaminho($requisicao->caminho);
        if ($metodos === []) {
            throw HttpErro::naoEncontrado();
        }
        $metodos[] = 'OPTIONS';
        if (in_array('GET', $metodos, true)) {
            $metodos[] = 'HEAD';
        }

        $resposta = Resposta::semConteudo()->comCabecalho('Allow', implode(', ', array_unique($metodos)));
        if ($origem !== null) {
            $resposta->comCabecalho('Access-Control-Allow-Methods', implode(', ', array_unique($metodos)));
            $resposta->comCabecalho('Access-Control-Allow-Headers', self::CABECALHOS_PERMITIDOS);
            $resposta->comCabecalho('Access-Control-Max-Age', '600');
        }

        return $resposta;
    }

    private function origemPermitida(?string $origem): ?string
    {
        if ($origem === null || $origem === '' || $this->configuracao->origensCors === []) {
            return null;
        }
        if (in_array('*', $this->configuracao->origensCors, true)) {
            return '*';
        }

        return in_array(rtrim($origem, '/'), array_map(static fn (string $o): string => rtrim($o, '/'), $this->configuracao->origensCors), true) ? $origem : null;
    }
}
