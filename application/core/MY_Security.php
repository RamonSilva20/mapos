<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Cookie do CSRF fora do cookie_httponly.
 *
 * O CI_Security aplica o cookie_httponly da configuração também ao cookie do
 * CSRF. Com o padrão novo (httponly ligado), o assets/js/csrf.js deixaria de
 * conseguir ler o token, e todo formulário e requisição AJAX passaria a falhar
 * na validação do CSRF.
 *
 * O cookie do CSRF não é segredo para o JavaScript da própria página: é o
 * padrão "double submit", e o mesmo valor já vai no HTML dos formulários. A
 * proteção vem de outro site não conseguir lê-lo nem enviá-lo, o que o
 * SameSite=Strict garante. Por isso só ele fica sem HttpOnly.
 */
class MY_Security extends CI_Security
{
    public function csrf_set_cookie()
    {
        $secure = (bool) config_item('cookie_secure');

        // Mesma regra do CI_Security: um cookie Secure enviado por HTTP seria
        // descartado pelo navegador.
        if ($secure && ! is_https()) {
            return false;
        }

        setcookie(
            $this->_csrf_cookie_name,
            $this->_csrf_hash,
            [
                'expires' => time() + $this->_csrf_expire,
                'path' => config_item('cookie_path'),
                'domain' => config_item('cookie_domain'),
                'secure' => $secure,
                'httponly' => false,
                'samesite' => 'Strict',
            ]
        );

        log_message('info', 'CSRF cookie sent');

        return $this;
    }
}
