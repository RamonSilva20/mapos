# Autenticação da API (v5)

Quem integra com a API (`/api/v1`), como o app móvel, precisa saber o que mudou na v5. **Nada do que já funcionava deixou de funcionar**: o fluxo antigo continua e o novo é opcional. A remoção do antigo será anunciada no CHANGELOG com uma versão de antecedência.

Rode as migrations (Configurações → Sistema → Atualizar banco) para criar `api_refresh_tokens` e `api_rate_limit`. Sem elas o login funciona como antes, só não emite refresh token e o limite de requisições fica desligado.

## Login

`POST /api/v1/login` com `{"email": "...", "password": "..."}`.

```json
{
  "status": true,
  "message": "Login realizado com sucesso!",
  "result": {
    "access_token": "eyJ...",
    "refresh_token": "Zk3...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "permissions": [ { "...": "..." } ]
  }
}
```

`refresh_token`, `token_type` e `expires_in` são **campos novos**. `access_token` e `permissions` são os de sempre. O access token vai no header `Authorization: Bearer <access_token>`.

## Access token

- JWT HS256 (biblioteca `firebase/php-jwt`, instalada pelo Composer; a cópia em `application/third_party/php-jwt` foi removida).
- Tem a claim padrão `exp` (`API_TOKEN_EXPIRE_TIME`, em segundos) e `iat`. A claim `API_TIME` continua por compatibilidade e será removida; não use. Tokens emitidos antes da v5 (sem `exp`) seguem valendo até vencer pelo `API_TIME`.
- A chave `API_JWT_KEY` precisa ter pelo menos 32 caracteres (256 bits), exigência da biblioteca. As instalações novas já geram uma assim.
- **O token não carrega permissões.** O usuário e a permissão são consultados no banco a cada requisição: quem foi desativado ou removido recebe `403` na hora, e uma permissão alterada vale na próxima chamada. O app não deve decidir permissões só pelo que veio no login.

## Renovar: refresh token (novo)

`POST /api/v1/refresh` com `{"refresh_token": "..."}`. Responde como o login (novo `access_token`, **novo** `refresh_token`, `expires_in`, `permissions`) ou `401` com `"Faça o login novamente!"`.

- O refresh token é de **uso único**: guarde o novo e descarte o anterior. Dura `API_REFRESH_TOKEN_EXPIRE_TIME` (padrão 30 dias).
- Se a resposta se perder na rede, repetir o mesmo token em até 10 segundos só devolve `401`, sem derrubar a sessão. Reutilizar um token já usado depois disso revoga a sessão inteira (cópia vazada).
- Trocar a senha ou desativar o usuário revoga todos os refresh tokens dele.

## Logout (novo)

`POST /api/v1/logout` com `{"refresh_token": "..."}` revoga a sessão. Sempre responde `200`.

## Renovar: `GET /api/v1/reGenToken` (legado)

Continua igual: aceita o access token atual, mesmo vencido há até o tempo de expiração, e devolve `{status, result: {access_token, permissions}}`. Passa a responder com os headers `Deprecation: true` e `Link: <.../api/v1/refresh>; rel="successor-version"`. Token inválido ou vencido continua dando **`403`** (não `401`), porque o app atual só renova nesse caso.

## CORS e limite de requisições

Configurados no `.env` (lidos por `application/config/rest.php`):

| Variável | Padrão | O que faz |
|---|---|---|
| `API_CORS_ORIGINS` | vazio | Origens que podem chamar a API de um navegador, separadas por vírgula, ou `*`. Vazio: sem CORS, como na v4. App Android/iOS não precisa. Os headers liberados incluem `Authorization`, `Content-Type` e `App-Version`. |
| `API_RATE_LIMIT` | `300` | Requisições por minuto por IP. `0` desliga. Acima disso a API responde `429` em JSON (`{"status": false, "message": "..."}`) com `Retry-After`. |
| `API_REFRESH_TOKEN_EXPIRE_TIME` | `2592000` | Duração do refresh token, em segundos. |

## O que um app novo deve fazer

1. Guardar `access_token` e `refresh_token` (de preferência em armazenamento seguro).
2. Ao receber `403` ou `401` com o access token, chamar `POST /refresh` uma vez e repetir a requisição.
3. Se o refresh der `401`, voltar para a tela de login.
4. No logout, chamar `POST /logout` antes de apagar os tokens.
5. Tratar `429` esperando o `Retry-After`.
