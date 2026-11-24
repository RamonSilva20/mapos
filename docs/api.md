# API do Map-OS (v2)

A API REST do Map-OS fica em `/api/v2` e cobre o que o painel faz: cadastros, ordens de serviço, vendas, financeiro, cobranças, relatórios, administração e a área do cliente.

**A referência completa, com todas as rotas, campos e exemplos, fica na própria instalação, em `/api/docs`** (Scalar). A especificação OpenAPI 3.1 que alimenta a página está em `/api/docs/openapi.json` e pode ser importada no Postman, Insomnia ou num gerador de cliente.

Este documento resume o que um integrador e o administrador precisam saber.

## Ligar a API

A API, a página `/api/docs` e a especificação só existem com `API_ENABLED=true` no `.env` (Configurações → API). Com a API desligada, todas essas URLs respondem `404`.

A chave dos tokens, `API_JWT_KEY`, precisa ter pelo menos 32 caracteres e não pode ser o `enter_jwt_key` do `.env.example`. Sem uma chave segura, a API responde `503` com `codigo: chave_insegura`. Para gerar uma chave nova, use Configurações → API → "Gerar uma chave nova para os tokens" ou `openssl rand -hex 32`. Trocar a chave invalida todos os tokens emitidos.

Com migrations pendentes, a API responde `503` com `codigo: banco_desatualizado`. As únicas rotas que respondem nesse estado são `GET /sistema` e `POST /sistema/banco`.

| Variável | Padrão | O que faz |
|---|---|---|
| `API_ENABLED` | `false` | Liga a API e a documentação. |
| `API_JWT_KEY` | gerada pelo instalador | Chave HMAC dos access tokens (HS256). |
| `API_TOKEN_EXPIRE_TIME` | `900` | Validade do access token, em segundos (de 60 a 3600). |
| `API_REFRESH_TOKEN_EXPIRE_TIME` | `2592000` | Por quanto tempo uma sessão parada pode ser renovada (30 dias). |
| `API_SESSION_MAX_LIFETIME` | `7776000` | Duração máxima de uma sessão, mesmo com renovações (90 dias). |
| `API_CORS_ORIGINS` | vazio | Origens que podem chamar a API de um navegador, separadas por vírgula, ou `*`. Vazio: sem CORS. App Android ou iOS não precisa. |
| `API_RATE_LIMIT` | `300` | Requisições por minuto por IP. `0` desliga. |

## Autenticação

O usuário do painel entra com `POST /auth/login` (`email`, `senha` e, opcionalmente, `dispositivo`). A resposta traz:

- `access_token`: um JWT curto. Ele vai em toda chamada, no cabeçalho `Authorization: Bearer <access_token>`.
- `refresh_token`: um token opaco de uso único, para renovar o par.
- `expires_in` e `refresh_expires_in`, em segundos, e o identificador da `sessao`.

Fluxo recomendado para um app:

1. Guarde os dois tokens em armazenamento seguro (Keychain ou Keystore).
2. Quando uma chamada responder `401` com `codigo: token_expirado`, chame `POST /auth/renovar` com o `refresh_token` e repita a chamada. A renovação devolve um par **novo**: troque os dois tokens.
3. Se a renovação responder `401`, volte para a tela de login.
4. No logout, chame `POST /auth/logout` antes de apagar os tokens.

### Regras de segurança

- **As permissões não viajam no token.** O usuário, o grupo e as permissões são lidos do banco a cada chamada. Conta desativada, expirada ou com o grupo desativado perde o acesso na hora, e uma permissão alterada vale na chamada seguinte.
- **Sessões revogáveis.** Cada login abre uma sessão, guardada na tabela `api_sessoes`. O access token carrega o id dela, e toda chamada confere se a sessão continua ativa. Logout, troca de senha e desativação da conta derrubam o access token na hora, sem esperar ele expirar.
- **Refresh token rotativo, guardado só como hash** (SHA-256) em `api_tokens_renovacao`. Cada renovação consome o token usado:
  - Reapresentar um token já usado, depois de alguns segundos, indica cópia vazada: a sessão inteira é encerrada (`401`, `codigo: refresh_reutilizado`).
  - Duas renovações simultâneas com o mesmo token: uma recebe o par novo e a outra recebe `409` (`renovacao_concorrente`), sem encerrar a sessão.
- **Limite de tentativas** no login, no segundo fator e na confirmação de senha, compartilhado com o painel: depois de várias falhas, a resposta é `429` com `Retry-After`. E-mail inexistente e senha errada têm a mesma resposta.
- `GET /auth/sessoes` lista as sessões abertas da conta. `DELETE /auth/sessoes/{id}` encerra uma delas e `DELETE /auth/sessoes` encerra todas, menos a atual.

### Autenticação em dois fatores

Quem tem o 2FA ativo recebe `dois_fatores: true` e um `desafio` no login, em vez dos tokens. Para concluir, chame `POST /auth/login/dois-fatores` com o `desafio` e o `codigo` (do aplicativo autenticador ou um código de recuperação). O desafio vale 5 minutos.

Se o grupo do usuário exige 2FA e ele ainda não ativou, o login devolve `dois_fatores_pendente: true`. Até a ativação, só as rotas de conta e de sessão respondem; as demais retornam `403` com `codigo: dois_fatores_obrigatorio`. A ativação pela API segue estes passos:

1. `POST /conta/dois-fatores/configuracao` devolve o segredo, o QR code e uma `configuracao` válida por 10 minutos.
2. `POST /conta/dois-fatores` com a `configuracao`, o `codigo` e a `senha` ativa o 2FA e devolve os códigos de recuperação. Eles só aparecem nessa resposta.

### Área do cliente

O cliente tem login próprio em `POST /cliente/auth/login`. Os tokens de cliente só valem nas rotas `/cliente/...`, e um token de usuário não abre essas rotas. A área do cliente também tem:

- Cadastro com captcha (`GET /cliente/auth/captcha`, depois `POST /cliente/auth/cadastro`).
- Recuperação de senha (`POST /cliente/auth/senha/recuperar` e `POST /cliente/auth/senha/redefinir`).
- Consulta das próprias OS, compras e cobranças, abertura de OS e PIX.

Um cliente nunca vê o registro de outro: a resposta é `404`.

## Formato

- O JSON vem em UTF-8 e os campos em `snake_case`. Cada recurso tem um formato próprio (ver `/api/docs`) e não expõe as colunas do banco.
- Respostas de sucesso vêm em `data`. As listagens trazem também `meta`, com `pagina`, `por_pagina` (até 100), `total` e `paginas`.
- Valores em dinheiro são texto com duas casas (`"150.00"`). Na entrada, a API aceita número ou texto, inclusive no formato brasileiro (`"1.250,90"`).
- Datas vêm como `AAAA-MM-DD`; data e hora, em ISO 8601 com fuso.
- Os textos das OS e das vendas (descrição, defeito, laudo, observações) vêm como texto puro, com as quebras de linha.
- Criar responde `201` com o cabeçalho `Location`. Excluir responde `204`. `PATCH` altera só os campos enviados.
- Uploads (anexos de OS, arquivos, foto da conta, logo do emitente) usam `multipart/form-data`, com um arquivo por chamada.
- Downloads (anexos, arquivos, backup, relatórios em PDF ou XLSX) exigem o token, como as outras rotas.

## Erros

Todo erro segue a RFC 9457 (`application/problem+json`) e traz um `codigo` estável, para o app decidir o que fazer:

```json
{
  "type": "urn:mapos:erro:validacao",
  "title": "Dados inválidos",
  "status": 422,
  "codigo": "validacao",
  "detail": "Confira os campos informados.",
  "erros": { "email": "Informe um e-mail válido." }
}
```

| Status | Quando |
|---|---|
| `400` | JSON malformado (`json_invalido`). |
| `401` | Sem token, token inválido ou expirado, ou sessão encerrada. |
| `403` | Sem a permissão exigida, ou bloqueado por uma regra (conta expirada, 2FA obrigatório, OS faturada...). |
| `404` | Registro inexistente, ou de outro cliente. |
| `409` | O estado atual impede a operação (cobrança ativa, termo em uso...). |
| `415` | Corpo que não é JSON (ou multipart, nas rotas de upload). |
| `422` | Campos inválidos (`erros` traz a mensagem de cada campo) ou operação recusada pela regra de negócio. |
| `429` | Limite de requisições ou de tentativas. Respeite o `Retry-After`. |
| `502` | O gateway de pagamento recusou a operação ou não respondeu. |
| `503` | Chave dos tokens não configurada, ou banco desatualizado. |

## Limites

Cada IP tem um limite de requisições por minuto (`API_RATE_LIMIT`). Os cabeçalhos `RateLimit-Limit`, `RateLimit-Remaining` e `RateLimit-Reset` mostram a situação. Login, renovação, captcha e recuperação de senha têm um limite próprio, de 30 por minuto.

## Mudanças em relação à v1

A API v1 (`/api/v1`) foi removida e responde `404`. A v2 não é compatível com ela; a seção "Quem usa a API" do [guia de migração](migracao-4x-para-5.md) lista as diferenças.
