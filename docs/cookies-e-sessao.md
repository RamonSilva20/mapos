# Cookies e sessão

A partir da v5, os cookies do Map-OS saem com os atributos de segurança ligados por padrão. As variáveis ficam no `application/.env`.

| Variável | Padrão | Efeito |
|---|---|---|
| `APP_COOKIE_SECURE` | `auto` | `Secure` só quando o acesso é por HTTPS. Aceita também `true` e `false`. |
| `APP_COOKIE_HTTPONLY` | `true` | O JavaScript da página não lê o cookie. |
| `APP_COOKIE_SAMESITE` | `Lax` | Navegador não envia o cookie em requisições originadas de outro site (exceto navegação comum por link). Aceita `Lax`, `Strict` e `None`. |
| `APP_SESS_REGENERATE_DESTROY` | `true` | Quando o ID da sessão é renovado, o anterior deixa de valer na hora. |

Uma variável ausente do `.env` assume o padrão da tabela. Um valor escrito no `.env` é respeitado como está.

## Rodando sem HTTPS

Com `APP_COOKIE_SECURE=auto`, nada muda para quem acessa por `http://`: o cookie sai sem `Secure` e o login funciona normalmente. A diferença é só que, nesse caso, o cookie de sessão trafega sem criptografia, como sempre trafegou. Em produção, use HTTPS.

**Não use `APP_COOKIE_SECURE=true` sem HTTPS.** O navegador descarta cookie `Secure` recebido por HTTP, e o resultado é não conseguir fazer login, sem mensagem de erro clara.

## Atrás de proxy reverso (Nginx, Cloudflare, load balancer)

Quando o HTTPS termina no proxy, o PHP recebe a requisição em HTTP e só sabe que o acesso original foi HTTPS pelo cabeçalho `X-Forwarded-Proto`. Esse cabeçalho pode ser enviado por qualquer cliente, então o Map-OS só o aceita quando a conexão vem de um IP listado em `APP_PROXY_IPS`:

```env
APP_PROXY_IPS=10.0.0.5
# ou sub-redes, separadas por vírgula
APP_PROXY_IPS=10.0.0.0/8,172.16.0.0/12
```

Sem `APP_PROXY_IPS`, o modo `auto` trata o acesso como HTTP e o cookie sai sem `Secure`. Se o site só é acessível por HTTPS, a alternativa é fixar `APP_COOKIE_SECURE=true`.

## Cookie do CSRF

O cookie do CSRF é a exceção ao `HttpOnly`: o `assets/js/csrf.js` precisa lê-lo para enviar o token nas requisições AJAX. Isso não enfraquece a proteção, porque o mesmo token já vai no HTML dos formulários e o cookie sai com `SameSite=Strict`, que impede outro site de usá-lo. A regra fica em `application/core/MY_Security.php`.

## Atualizando de uma instalação 4.x

O `.env` das instalações 4.x tem estas linhas, copiadas do exemplo da época:

```env
APP_SESS_REGENERATE_DESTROY=false
APP_COOKIE_SECURE=false
APP_COOKIE_HTTPONLY=false
```

Como valores explícitos são respeitados, elas mantêm o comportamento antigo. Para adotar os padrões novos, troque por:

```env
APP_SESS_REGENERATE_DESTROY=true
APP_COOKIE_SECURE=auto
APP_COOKIE_HTTPONLY=true
APP_COOKIE_SAMESITE=Lax
```
