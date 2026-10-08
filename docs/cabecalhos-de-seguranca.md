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
| `Content-Security-Policy-Report-Only` | política alvo da v5 | sempre (desligável) |

Os cabeçalhos também saem nas respostas da API (`api/v1`) e nos downloads. Lá
eles não atrapalham: JSON e arquivos não executam script nem são exibidos em
frame, e o `nosniff` impede que um anexo seja interpretado como HTML.

## Configuração (`application/.env`)

| Variável | Padrão | Efeito |
|---|---|---|
| `APP_SECURITY_HEADERS` | `true` | `false` desliga todos os cabeçalhos |
| `APP_HSTS_MAX_AGE` | `31536000` | duração do HSTS em segundos; `0` desliga |
| `APP_HSTS_INCLUDE_SUBDOMAINS` | `false` | acrescenta `includeSubDomains` |
| `APP_CSP_REPORT_ONLY` | `true` | envia a CSP em modo report-only |
| `APP_CSP_REPORT` | `true` | manda os relatórios ao endpoint do Map-OS |

O HTTPS é detectado como nos cookies (`docs/cookies-e-sessao.md`): atrás de proxy
reverso, o `X-Forwarded-Proto` só vale vindo de um IP de `APP_PROXY_IPS`.

**Cuidado com o HSTS:** depois que o navegador recebe o cabeçalho, ele recusa
acessar o domínio por HTTP pelo tempo do `max-age`. Ligue o HTTPS de forma
definitiva antes, ou use `APP_HSTS_MAX_AGE=0`.

## CSP em report-only

A política é a meta da v5, sem `unsafe-inline` em script e estilo. Em
report-only o navegador **não bloqueia nada**: ele só avisa o que seria
bloqueado. As telas legadas geram violações de propósito (scripts inline,
CDNs, Google Fonts), e os relatórios medem o que falta migrar até o modo
enforce (#2878).

Os relatórios chegam em `POST /index.php/csp/report` e ficam agregados em:

```
application/logs/csp-violacoes.json
```

Cada violação distinta (diretiva + origem bloqueada + página) aparece uma vez,
com o total de ocorrências, a primeira e a última. A primeira ocorrência de cada
uma também vai para o log do CodeIgniter (`application/logs/log-AAAA-MM-DD.php`).
O arquivo guarda no máximo 500 violações distintas; apague-o para recomeçar a
medição.

O endpoint não exige login (o navegador envia o relatório sozinho), aceita só
`POST` com `Content-Type` de relatório e corpo de até 16 KB.
