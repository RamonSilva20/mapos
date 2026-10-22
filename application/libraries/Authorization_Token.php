<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Authorization_Token
 * ----------------------------------------------------------
 * Gera e valida o access token (JWT HS256, firebase/php-jwt) da API.
 *
 * O token leva a claim padrão `exp` (iat + API_TOKEN_EXPIRE_TIME) e, por
 * compatibilidade com os apps já instalados e com tokens emitidos antes da
 * #2869, continua levando `API_TIME`. Tokens antigos, sem `exp`, são validados
 * por `API_TIME`, com a mesma regra de antes.
 *
 * As permissões NÃO são lidas do token: o REST_Controller consulta o usuário e
 * a permissão no banco a cada requisição.
 */
class Authorization_Token
{
    /** @var object Instância do CodeIgniter, injetada por get_instance() no construtor */
    protected $CI;

    /**
     * Token Key
     */
    protected $token_key;

    /**
     * Token algorithm
     */
    protected $token_algorithm;

    /**
     * Token Request Header Name
     */
    protected $token_header;

    /**
     * Token Expire Time
     */
    protected $token_expire_time;

    public function __construct()
    {
        $this->CI = &get_instance();

        /**
         * jwt config file load
         */
        $this->CI->load->config('jwt');

        /**
         * Load Config Items Values
         */
        $this->token_key = $this->CI->config->item('jwt_key');
        $this->token_algorithm = $this->CI->config->item('jwt_algorithm');
        $this->token_header = $this->CI->config->item('token_header');
        $this->token_expire_time = $this->CI->config->item('token_expire_time');
    }

    /**
     * Generate Token
     *
     * @param  array  $data
     */
    public function generateToken($data = null, ?int $tetoExpiracao = null)
    {
        if (! $data || ! is_array($data)) {
            return 'Token Data Undefined!';
        }

        $agora = time();
        $data['iat'] = $agora;
        $data['exp'] = $agora + $this->expiresIn($tetoExpiracao);
        // Legado: apps e tokens anteriores à #2869 usam API_TIME.
        $data['API_TIME'] = $agora;

        return JWT::encode($data, $this->token_key, $this->token_algorithm);
    }

    /**
     * Duração do access token, em segundos (expires_in da resposta de login).
     * `$teto` limita a configurada: o token de operador, que tem refresh token,
     * dura no máximo TETO_COM_REFRESH.
     */
    public function expiresIn(?int $teto = null): int
    {
        $duracao = (int) $this->token_expire_time;

        return $teto !== null ? min($duracao, $teto) : $duracao;
    }

    /** Teto do access token que pode ser renovado pelo refresh token: 1 hora. */
    public const TETO_COM_REFRESH = 3600;

    /**
     * O token continua valendo? A versão gravada no token (`tv`, 0 nos tokens
     * anteriores à coluna) precisa ser a do registro: trocar a senha ou
     * desativar a conta incrementa a versão e derruba os access tokens vivos.
     */
    public static function versaoVigente(object $dadosDoToken, int $versaoAtual): bool
    {
        return (int) ($dadosDoToken->tv ?? 0) === $versaoAtual;
    }

    /** Versão atual dos tokens do registro; 0 se a coluna ainda não existe (migration não rodada). */
    public function versaoAtual(string $tabela, string $campoId, int $id): int
    {
        if (! in_array($tabela, ['usuarios', 'clientes'], true) || ! $this->CI->db->field_exists('token_versao', $tabela)) {
            return 0;
        }

        $linha = $this->CI->db->select('token_versao')->where($campoId, $id)->limit(1)->get($tabela)->row();

        return (int) ($linha->token_versao ?? 0);
    }

    /** Invalida os access tokens vivos do registro (usuarios ou clientes). Sem a coluna (migration não rodada), não faz nada. */
    public function incrementarVersao(string $tabela, string $campoId, int $id): void
    {
        if (! in_array($tabela, ['usuarios', 'clientes'], true) || ! $this->CI->db->field_exists('token_versao', $tabela)) {
            return;
        }

        $this->CI->db->set('token_versao', 'token_versao + 1', false)->where($campoId, $id)->update($tabela);
    }

    /**
     * Validate Token with Header
     *
     * @return : user informations
     */
    public function validateToken($token)
    {
        $token_data = $this->tokenIsExist($this->CI->input->request_headers());
        if ($token_data['status'] !== true) {
            return ['status' => false, 'message' => $token_data['message']];
        }

        $expire_time = (int) $this->token_expire_time;

        $leewayAnterior = JWT::$leeway;
        JWT::$leeway = 0;

        try {
            $token_decode = JWT::decode((string) $token, new Key($this->token_key, $this->token_algorithm));
        } catch (ExpiredException $e) {
            return ['status' => false, 'message' => 'Token Time Expire.'];
        } catch (Throwable $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        } finally {
            JWT::$leeway = $leewayAnterior;
        }

        // Token novo: o exp já foi conferido pela biblioteca.
        if (isset($token_decode->exp)) {
            return ['status' => true, 'data' => $token_decode];
        }

        // Token anterior à #2869: só tem API_TIME.
        if (empty($token_decode->API_TIME) || ! is_numeric($token_decode->API_TIME)) {
            return ['status' => false, 'message' => 'Token Time Not Define!'];
        }

        if (time() - (int) $token_decode->API_TIME >= $expire_time) {
            return ['status' => false, 'message' => 'Token Time Expire.'];
        }

        return ['status' => true, 'data' => $token_decode];
    }

    /**
     * Token Header Check
     *
     * @param  array  $headers
     */
    private function tokenIsExist($headers)
    {
        if (! empty($headers) and is_array($headers)) {
            foreach ($headers as $header_name => $header_value) {
                if (strtolower(trim($header_name)) == strtolower(trim($this->token_header))) {
                    return ['status' => true, 'token' => $header_value];
                }
            }
        }

        return ['status' => false, 'message' => 'Token is not defined.'];
    }
}
