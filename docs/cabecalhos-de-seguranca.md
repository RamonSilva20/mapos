# Cabeçalhos de segurança e CSP

A v5 envia cabeçalhos de segurança em toda resposta, pelo hook `pre_system`
(`application/hooks/security_headers.php`). As regras ficam em
`application/helpers/seguranca_cabecalhos_helper.php`.

| Cabeçalho | Valor | Quando |
|---|---|---|
| `X-Frame-Options` | `SAMEORIGIN` | sempre |
| `X-Content-Type-Options` | `nosniff` | sempre |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | sempre |
| `Permissions-Policy` | câmera, microfone, localização, pagamento, USB e sensores desligados | sempre |
| `Strict-Transport-Security` | `max-age=31536000` | só em acesso por HTTPS |
| `Content-Security-Policy` | política da v5, em enforce | sempre (`APP_CSP` muda o modo) |

Os cabeçalhos também saem nas respostas da API (`api/v1`) e nos downloads. Lá
eles não atrapalham: JSON e arquivos não executam script nem são exibidos em
frame, e o `nosniff` impede que um anexo seja interpretado como HTML.

## Configuração (`application/.env`)

| Variável | Padrão | Efeito |
|---|---|---|
| `APP_SECURITY_HEADERS` | `true` | `false` desliga todos os cabeçalhos |
| `APP_HSTS_MAX_AGE` | `31536000` (`0` com `APP_ENVIRONMENT=development`) | duração do HSTS em segundos; `0` desliga |
| `APP_HSTS_INCLUDE_SUBDOMAINS` | `false` | acrescenta `includeSubDomains` |
| `APP_CSP` | `enforce` | `enforce` bloqueia o que a política não permite, `report-only` só relata e `off` desliga. O antigo `APP_CSP_REPORT_ONLY` (`true` = report-only, `false` = sem CSP) continua valendo quando `APP_CSP` não existe |
| `APP_CSP_REPORT` | `true` | manda os relatórios ao endpoint do Map-OS |

O HTTPS é detectado como nos cookies (`docs/cookies-e-sessao.md`): atrás de proxy
reverso, o `X-Forwarded-Proto` só vale vindo de um IP de `APP_PROXY_IPS`.

**Cuidado com o HSTS:** depois que o navegador recebe o cabeçalho, ele recusa
acessar o domínio por HTTP pelo tempo do `max-age`. Ligue o HTTPS de forma
definitiva antes, ou use `APP_HSTS_MAX_AGE=0`.

## CSP em enforce

Desde a Beta (#2878) a política vem em **enforce**: o navegador bloqueia o que
ela não permite. Ela não tem `unsafe-inline` nem `unsafe-eval`:

- `script-src 'self'`: todo o JavaScript fica em `assets/js` (módulos ES e
  `assets/js/imprimir.js` nas views de impressão); não há `<script>` inline nem
  atributos `on*`. Os blocos `application/json` de dados não são executados.
- `style-src 'self' 'nonce-…'`: as views não usam `style=` nem `<style>`. A
  única exceção é o **FullCalendar** (agenda do painel), que cria um `<style>` por
  JavaScript e lê o nonce de `<meta name="csp-nonce">`. O nonce é gerado a cada
  requisição (`segurancaGerarNonce()`, no hook `pre_system`) e só vale para o
  elemento que o carrega.
- `img-src 'self' data: blob: https:` por causa dos QR Codes de Pix e boletos,
  `connect-src` só com ViaCEP (CEP) e BrasilAPI, Minha Receita e CNPJ.ws (CNPJ,
  nessa ordem de tentativa), `form-action 'self'`, sem iframes
  (`frame-ancestors 'self'`) e `object-src 'none'`.

Se uma integração sua precisar de algo fora disso, ajuste a política em
`segurancaPoliticaCsp()`; para investigar, use `APP_CSP=report-only` (nada é
bloqueado, tudo é relatado) e depois volte para `enforce`.

Os relatórios continuam chegando nos dois modos, em `POST /index.php/csp/report` e ficam agregados em:

```
<STORAGE_PATH>/logs/csp-violacoes.json
```

(sem `STORAGE_PATH`, `application/storage/logs/csp-violacoes.json`; veja
`docs/armazenamento-de-arquivos.md`. Até a versão anterior ele ficava em
`application/logs`, dentro da pasta pública; o Map-OS move o arquivo sozinho
no primeiro relatório.)

Cada violação distinta (diretiva + origem bloqueada + página) aparece uma vez,
com o total de ocorrências, a primeira e a última. A primeira ocorrência de cada
uma também vai para o log do CodeIgniter (`application/logs/log-AAAA-MM-DD.php`).
O arquivo guarda no máximo 500 violações distintas; apague-o para recomeçar a
medição.

O endpoint não exige login (o navegador envia o relatório sozinho), aceita só
`POST` com `Content-Type` de relatório e corpo de até 16 KB.
