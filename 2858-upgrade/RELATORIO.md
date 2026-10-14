# #2858 - Teste de upgrade 4.55 -> v5 e instalação limpa

Data: 2026-10-14. v5 testada no commit `d6b5c88` (branch v5). PHP 8.5.10 CLI + `php -S`, MySQL 8.4, Apache 2.4 efêmero (AllowOverride All) só para o `.htaccess`. Bancos descartáveis `mapos_up4` e `mapos_clean5`, ambientes isolados.
Papel: teste; nenhum código de produção foi alterado.

## Resumo

| Parte | Resultado |
| --- | --- |
| A, upgrade 4.55.2 -> v5 | **Funciona**, mas o passo `php index.php tools migrate` do guia **falha** com `composer install --no-dev` (S1). Pelo painel (Atualizar banco de dados) as 13 migrations aplicam, dados e arquivos preservados, rollback funciona. |
| B, instalação limpa | Instalador web OK (requisitos, .env com chaves aleatórias, bloqueio posterior), mas **o banco sai no esquema 4.x** (S2): migrations v5 não rodam e o limite de login, 2FA, histórico etc. ficam sem tabela até o admin clicar em Atualizar banco. Depois disso: OS completa, desconto por item, anexo, PDF, 2FA, claro/escuro/celular OK. |
| C, inventário | Abaixo. |

## Achados (por gravidade)

### S1 - Bloqueia o passo do guia (CLI de migrations e rollback quebrados em instalação sem dev)
`composer install --no-dev` (guia 3.1 e o zip da release, que o `release.yml` gera com `--no-dev`) não instala `fakerphp/faker` (require-dev), mas `application/controllers/Tools.php` faz `Faker\Factory::create()` no construtor. Resultado: `php index.php tools migrate` e `php index.php tools migrate 20240503170400` (rollback do guia 5) **saem com código 1, sem nenhuma mensagem** (display_errors desligado; o erro só vai ao log: `Class "Faker\Factory" not found ... Tools.php 29`).
Reproduzir: clonar v5, `composer install --no-dev`, configurar `.env`, `php index.php tools migrate; echo $?` -> 1 e `application/logs/log-*.php` com a exceção.
Contorno: usar o painel (Configurações > Sistema > Atualizar banco de dados) para subir; para o rollback, `composer install` (com dev) e então `tools migrate <versão>` (funcionou).
Afeta: guia 3.4 (terminal), guia 5 (rollback), `armazenamento-de-arquivos.md#rollback`, README (linhas 113/122) e `api-autenticacao.md`. Correção sugerida: Tools não depender do Faker (carregar só em `seed`), ou documentar.

### S2 - Instalação limpa não aplica as migrations da v5 (perda de proteções silenciosa)
`banco.sql` ainda grava `migrations = 20240503170400` (a pendência já citada na issue, banco.sql:660). Logo, depois do instalador web o banco não tem `login_attempts`, `os_historico`, `api_refresh_tokens`, `api_rate_limit`, colunas de 2FA, `token_versao`, `sessao_versao`, `desconto` por item, `app_tema_modo`. Nada avisa: a tela final do instalador e o painel não mencionam, e o único aviso é uma linha no log (`Limite de login desligado: rode as migrations`). Ou seja, o limite de tentativas de login (um dos pontos de segurança da v5) fica **desligado** até o admin lembrar de clicar em Atualizar banco. O README da instalação nova não cita esse passo (só os blocos de atualização).
Reproduzir: instalar pelo `install/`, logar, `tail application/logs/log-*.php`; `select * from migrations` -> `20240503170400`.
Correção sugerida: o instalador rodar as migrations (ou `banco.sql` já no esquema v5 com a última versão em `migrations`) e o painel alertar quando houver migration pendente.

### S3 - Documentação errada: "rode `tools migrate` de novo" não refaz a migração dos anexos
Guia, seção 6, linha "Anexos antigos não abrem": manda definir permissão e rodar `php index.php tools migrate` de novo. Testado: com `STORAGE_PATH` sem permissão de escrita a migration conclui **sem erro nem log**, registra a versão como aplicada, os anexos ficam nos caminhos antigos (continuam abrindo, o que mascara o problema); após corrigir a permissão, `tools migrate` e "Atualizar banco" não fazem nada (já está na última versão). Só funciona desfazer com `tools migrate 20261014020000` (desfaz a move_uploads e as posteriores) e rodar `tools migrate` de novo. Falta o guia dizer isso, e a migration deveria avisar/falhar quando não consegue escrever.

### S4 - `.git/` fica público no Apache com `.htaccess` (instalação pelo código-fonte)
O guia 3.1 manda `git clone`. Com Apache + `AllowOverride All` o `.htaccess` bloqueia arquivos que **começam** com ponto, mas não o diretório: `curl http://host/.git/HEAD` e `/.git/config` respondem **200** (testado em Apache real). O guia e `servidor-web.md` dizem que "qualquer arquivo que comece com ." fica bloqueado. Expõe código/histórico do repositório (e `remote url`, que pode ter token). Sugestão: `RewriteRule (^|/)\.git(/|$) - [F]` / `RedirectMatch 404 /\.git`, ou orientar `rm -rf .git` e usar o zip.
Demais caminhos testados no Apache deram 403 como esperado: `application/.env`, `application/logs/`, `banco.sql`, `composer.json/lock`, `*.md`, `docs/`, `scripts/`, `tests/`, `updates/`, `docker/`, `install/settings.json`, `install/instalador.php`, `install/view/`, `assets/anexos/*.php`, `*baseline.json`, `.gitignore`.

### S5 - Requisitos documentados != requisitos reais
README e guia: "PHP >= 8.5 com `curl` e `gd`". O `install/settings.json` exige também `mysqli` e `zip`. O código e as dependências (`composer check-platform-reqs --no-dev`) exigem ainda `mbstring`, `dom`, `xml`, `zlib`, `iconv`, `json` e, pelo código, `openssl` (2FA AES-256-GCM, chaves) e `fileinfo` (conferência de MIME dos uploads). O instalador só confere cURL, MySQLi, GD e zip (e `allow_url_fopen`), então um servidor sem `mbstring`/`openssl`/`fileinfo` passa no instalador e quebra depois. Não foi possível remover extensões do PHP deste ambiente (estão compiladas), então isto vem de análise estática.
O instalador também não confere `application/vendor`: com a pasta ausente (clone sem composer) ele passa tudo verde e a aplicação responde HTTP 200 com `Fatal error: Uncaught Exception: Arquivo autoload não encontrado` mostrando caminhos absolutos do servidor (`display_errors` ativo porque `.env` ainda não existe/ambiente não configurado).

### S6 - Rollback com a v5 em pasta nova (guia 3.1) deixa os caminhos apontando para a pasta da v5
O `down` de `move_uploads_to_storage` devolve os arquivos para `assets/anexos` **da pasta onde roda o comando** (a da v5) e grava `path` absoluto e `url` dessa pasta. Se a 4.x está numa pasta antiga (como o guia recomenda), seus arquivos de anexo ficam na pasta da v5. O guia diz só "desfaça apenas a migration"; falta explicar que é preciso copiar `assets/anexos|arquivos` de volta para a pasta da 4.x e ajustar `path` (ou preferir restaurar o backup dos arquivos). Restaurar o dump do banco sobre um banco já migrado deixa tabelas órfãs da v5 (`login_attempts`, `os_historico`, `api_*`, `usuarios_codigos_recuperacao`); a segunda migração roda mas registra erros `Duplicate key name` no log (conclui com sucesso). Sugerir `DROP DATABASE` + restore completo.

### S7 - Detalhes menores de documentação/cosmético
- Versão exibida: sidebar, rodapé e login mostram **v4.55.1** (`config.php app_version`) na v5.
- Guia 3.3 não diz que os **valores antigos** do `.env` da 4.x (`APP_COOKIE_SECURE=false`, `APP_COOKIE_HTTPONLY=false`, `APP_SESS_REGENERATE_DESTROY=false`) continuam valendo se o admin só "acrescenta o que falta"; os padrões novos (`auto`, `true`, `true`) só valem em instalação nova. Recomendar ajustar.
- Guia 5: "desfaz todas as migrations da v5", mas `fix_decimal_scale_of_prices` não volta (`DECIMAL(10,2)` permanece; é intencional no código, não está no guia). Também o token de reset já invalidado não volta (esperado).
- Guia 3.1 cita o zip da release v5 e a 4.55.x como "última" mas não há zip de release da 4.x (release v4.55.2 sem assets) nem da v5 ainda; a 4.x teve de ser instalada por `git clone` + `composer install`.
- `.env` gerado pelo instalador fica 0644 (legível por outros usuários do servidor); sugerir 0600/0640. `STORAGE_PATH` fica vazio (cai em `application/storage`), o instalador não pergunta nem sugere pasta fora da pública, apesar de README/guia recomendarem.
- Anexo cujo arquivo físico não existe (registro órfão da 4.x) aparece na lista da OS como imagem quebrada (rota `os/anexo/<id>` redireciona para `/os`).
- `url_logo` do emitente é gravada absoluta; se o domínio mudar, a CSP `img-src 'self' https:` bloqueia a logo em http de outro host (vi isso por testar em portas diferentes). Não é bug da v5, só anotar no guia para quem muda de domínio.
- Dashboard avisa que a pasta `install` ainda existe, mas não avisa de migration pendente.

## O que está ok (Parte A)
- 4.55.2 instalada pelo instalador 4.x (banco novo) e populada: 4 clientes, 5 produtos, 4 serviços, 12 OS (24 produtos_os, 12 servicos_os, descontos), 6 anexos (png, pdf, 1 órfão, com thumbs) em `assets/anexos`, 5 vendas, 8 lançamentos, 2 arquivos em `assets/arquivos`, 3 usuários/3 grupos (1 inativo), emitente com logo, `app_theme`, token de reset em texto claro, colunas `DECIMAL(10,0)` simulando banco montado por migrations.
- Backup mysqldump + cópia de arquivos feitos antes. v5 instalada em pasta nova, `.env` ajustado conforme 3.3, `STORAGE_PATH` fora da pasta pública.
- As **13 migrations** aplicam sem erro (versão final `20261014060000`). Contagens e somas idênticas (`saidas/up4-baseline.txt` x `up4-after.txt`); preços/saldo passam a `DECIMAL(10,2)` sem alterar valores.
- Anexos e arquivos movidos para `STORAGE_PATH`, banco com caminho relativo, `assets/anexos` e `assets/arquivos` ficam só com `index.html`/`.htaccess`. Abrem pelo controller (painel `os/anexo/<id>`, miniatura, `downloadanexo`, `arquivos/download/<id>`; cliente dono em `mine/anexo/<id>`; cliente de outra OS é negado; API `api/v1/os/<os>/anexos/<id>`). Link público antigo `/assets/anexos/...` dá 404.
- Login com hashes antigos (admin, grupo "Atendimento", cliente); usuário desativado recusado; sem migration, o login também funciona (avisa no log). Permissões: o grupo restrito vê clientes/OS/vendas/produtos/lançamentos e é barrado em usuários, permissões, configurar, relatórios e arquivos. Token de reset em claro marcado como usado. `app_theme` puredark -> `app_tema_modo=escuro`; valores fora do mapa -> claro.
- 29 telas do painel (dashboard, clientes, produtos, serviços, OS, vendas, financeiro, arquivos, garantias, usuários, permissões, configurações, relatórios, impressões) respondem 200 sem erro de JS; 7 relatórios PDF e a impressão da OS geram PDF correto com a logo e totais iguais aos do banco (`pdf/`). Desconto por item e upload de anexo funcionam na OS migrada e o arquivo vai para o `STORAGE_PATH`.
- API: login, rota autenticada, `refresh`, `reGenToken` 404, token emitido pela 4.x ainda aceito (sem `exp`), `API_ENABLED=false` -> 404 em login, rotas e URL direta de controller. `email/process` e `email/retry` rodam.
- Rollback: `tools migrate 20261014020000` devolve os arquivos para `assets/` e as URLs; `tools migrate 20240503170400` volta o esquema ao dump original (única diferença: escala decimal) e os dados idênticos; restaurar o dump também funciona (ver S6). Migrar de novo depois funciona.
- Instalador bloqueado depois de instalado: `install/` responde 403 (página curta, sem versão do PHP), `do_install.php` 403 JSON, `.htaccess` gerado, `instalado.lock`.

## O que está ok (Parte B)
- Instalador web (Playwright): checagem de PHP 8.5, allow_url_fopen, cURL, MySQLi, GD, zip, `/updates` gravável; formulário valida; `.env` sem marcadores `enter_*`, `APP_ENVIRONMENT=production`, `APP_ENCRYPTION_KEY` 32 caracteres aleatórios, `API_JWT_KEY` 44 caracteres (>= 32), `API_ENABLED`/expiração conforme escolhido, `APP_COOKIE_SECURE=auto`, `APP_COOKIE_HTTPONLY=true`, CSP `enforce`.
- Pós-instalação (e depois de rodar "Atualizar banco de dados"): primeiro login; limite de login bloqueia na 6a tentativa e mantém a mensagem genérica; 2FA (ativar com QR/chave, 10 códigos de recuperação, login com código errado recusado e certo aceito); cliente com senha, produto, serviço, OS (cliente/técnico por busca), itens, desconto por item 10 % (R$ 39,98 sobre 2 x R$ 199,90, total R$ 539,62 correto), anexo em `application/storage`, impressão A4 em PDF; claro, escuro e celular sem quebra; login da área do cliente.
- Docker não foi testado (escopo local).

## Evidências
`prints-upgrade/` (telas após o upgrade), `prints-limpa/` (instalador, dashboard sem migrar, OS, desconto, anexo, impressão, 2FA, escuro, celular), `pdf/`, `saidas/`.
