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
    public function generateToken($data = null)
    {
        if (! $data || ! is_array($data)) {
            return 'Token Data Undefined!';
        }

        $agora = time();
        // Início da cadeia de renovações (reGenToken): preservado nas renovações, para ter teto (#2879).
        $data['orig'] = (int) ($data['orig'] ?? $agora);
        $data['iat'] = $agora;
        $data['exp'] = $agora + (int) $this->token_expire_time;
        // Legado: apps e tokens anteriores à #2869 usam API_TIME.
        $data['API_TIME'] = $agora;

        return JWT::encode($data, $this->token_key, $this->token_algorithm);
    }

    /** Duração do access token, em segundos (expires_in da resposta de login). */
    public function expiresIn(): int
    {
        return (int) $this->token_expire_time;
    }

    /**
     * Validate Token with Header
     *
     * @return : user informations
     */
    public function validateToken($token, $regenToken = false)
    {
        $token_data = $this->tokenIsExist($this->CI->input->request_headers());
        if ($token_data['status'] !== true) {
            return ['status' => false, 'message' => $token_data['message']];
        }

        $expire_time = (int) $this->token_expire_time;

        // Na renovação (reGenToken legado) o token pode estar vencido, mas só
        // dentro de uma janela igual ao tempo de expiração: sem esse limite um
        // token vazado poderia ser renovado indefinidamente.
        $leewayAnterior = JWT::$leeway;
        JWT::$leeway = $regenToken ? $expire_time : 0;

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

        $max_age = $regenToken ? $expire_time * 2 : $expire_time;
        if (time() - (int) $token_decode->API_TIME >= $max_age) {
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
