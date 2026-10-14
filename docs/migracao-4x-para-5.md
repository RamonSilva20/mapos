# Migração do Map-OS 4.x para a v5

Este guia leva uma instalação da série 4 (4.55.x, a última) para a v5. Se você está numa versão mais antiga, atualize antes para a 4.55.x.

> A v5 ainda não tem release estável. Faça a migração primeiro numa **cópia** do servidor e do banco, confira o resultado e só então repita em produção.

Resumo do que muda:

| Área | Na 4.x | Na v5 |
| --- | --- | --- |
| PHP | 8.4 ou mais novo | **8.5 ou mais novo** (`composer.json`: `^8.5`) |
| Interface | Bootstrap, jQuery, tema Matrix | Tailwind, componentes próprios, modo claro, escuro ou sistema |
| Anexos de OS e arquivos | `assets/anexos` e `assets/arquivos`, com URL pública | `STORAGE_PATH`, fora da pasta pública, servidos por controller com permissão |
| Login | só e-mail e senha | limite de tentativas, 2FA (TOTP) opcional |
| Segurança do navegador | sem política | cabeçalhos de segurança e CSP em enforce |
| API | `reGenToken`, token sem refresh | `exp`, refresh token, `API_ENABLED=false` desliga a API de verdade |
| OS e vendas | desconto só no total | desconto também por item |

## 1. Requisitos

- PHP >= 8.5 com as extensões `curl` e `gd`.
- MySQL >= 5.7 ou >= 8.0 (ou MariaDB).
- **Composer só é necessário se você instalar pelo código-fonte** (`git clone`). O zip da release (`vX.Y.Z-release.zip`) já traz o `application/vendor` instalado.
- **Node não é necessário.** Ele só serve para quem altera views, CSS ou as bibliotecas de JavaScript (veja o [CONTRIBUTING](../CONTRIBUTING.md)). O CSS e o JS compilados vão no pacote.

Confira a versão do PHP que o **servidor web** usa (não só a da linha de comando): `php -v` e a tela de informações do PHP no painel de hospedagem.

## 2. Backup

Não pule esta etapa: a migração altera tabelas e move arquivos.

1. **Banco de dados:** no painel, menu Configurações → **Backup**, ou `mysqldump` pelo terminal. Guarde o arquivo fora do servidor.
2. **Arquivos que não vêm no pacote novo:**
   - `application/.env`;
   - `assets/anexos`, `assets/arquivos`, `assets/uploads` (logo) e `assets/userImage` (fotos de perfil);
   - qualquer personalização feita em `assets/img`;
   - arquivos de `application/config` que você alterou à mão (`config.php`, `database.php`, `email.php`);
   - views, CSS ou JavaScript que você personalizou (veja [quem personalizou o painel](#quem-personalizou-views-css-ou-javascript)).
   O backup do painel é só do banco: copie também, à parte, as pastas de arquivos abaixo.
3. Anote a versão atual (rodapé do sistema) e a URL do sistema.

## 3. Passo a passo

### 3.1. Instale a v5 numa pasta nova

Em vez de sobrescrever a pasta da 4.x, prefira colocar a v5 numa pasta nova e trazer só o que é seu. A v5 **remove** muitos arquivos da 4.x (Bootstrap, jQuery, Font Awesome, tema Matrix, bibliotecas antigas), e copiar por cima deixa esses arquivos soltos no servidor.

- **Pelo zip da release:** baixe `vX.Y.Z-release.zip` na página de releases, confira o SHA256 com o arquivo `.sha256` publicado ao lado e extraia.
- **Pelo código-fonte:** clone o repositório na branch/tag desejada e rode, na raiz:

  ```bash
  composer install --no-dev
  ```

> **Não use o botão "Atualizar Mapos" da 4.x para esta migração.** A 4.x baixa o código do branch `master`, que não traz o `application/vendor`, e só roda `composer install` se o Composer existir no servidor. Além disso, o PHP 8.5, o `.env` novo e o `STORAGE_PATH` exigem passos manuais. A atualização pelo painel da v5 é outra (veja [atualizacao.md](atualizacao.md)) e só vale a partir da v5.

### 3.2. Traga o que é seu

Copie para a pasta nova:

- `application/.env` (veja o passo 3.3);
- `assets/uploads` e `assets/userImage`;
- as personalizações de `assets/img`;
- `assets/anexos` e `assets/arquivos`, **só para que a migration os encontre** (passo 3.4). Depois da migração eles ficam vazios.

### 3.3. Ajuste o `application/.env`

Compare o seu `.env` com o `application/.env.example` da v5 e acrescente o que falta. As variáveis novas são:

| Variável | Para que serve | Padrão |
| --- | --- | --- |
| `STORAGE_PATH` | Pasta dos anexos de OS e dos arquivos. **Aponte para fora da pasta pública.** | vazio: `application/storage` |
| `UPLOAD_MAX_KB` | Tamanho máximo de cada upload, em KB. | `10240` (10 MB) |
| `APP_SECURITY_HEADERS` | Liga ou desliga os cabeçalhos de segurança. | `true` |
| `APP_HSTS_MAX_AGE` | Duração do HSTS (só em HTTPS). `0` desliga. | `31536000` |
| `APP_HSTS_INCLUDE_SUBDOMAINS` | Acrescenta `includeSubDomains` ao HSTS. | `false` |
| `APP_CSP` | `enforce`, `report-only` ou `off`. | `enforce` |
| `APP_CSP_REPORT` | Envia relatórios de violação ao Map-OS. | `true` |
| `APP_COOKIE_SECURE` | `auto` marca o cookie como `Secure` quando o acesso é HTTPS. | `auto` |
| `APP_COOKIE_SAMESITE` | `SameSite` dos cookies. | `Lax` |
| `APP_PROXY_IPS` | IPs do proxy reverso em que o Map-OS confia para ler o `X-Forwarded-Proto`. | vazio |
| `API_REFRESH_TOKEN_EXPIRE_TIME` | Duração do refresh token da API, em segundos. | `2592000` (30 dias) |
| `API_CORS_ORIGINS` | Origens que podem chamar a API de um navegador. Vazio: sem CORS. | vazio |
| `API_RATE_LIMIT` | Requisições por minuto por IP na API. `0` desliga. | `300` |
| `UPDATER_PRERELEASES` | Inclui pré-releases na busca de atualização. | `false` |

`GLOBAL_XSS_FILTERING` não existe mais na v5 e pode ser apagada do `.env`.

Pontos de atenção:

- **`APP_ENVIRONMENT`:** em produção use `production`. O catálogo de componentes (`/index.php/componentes`) e as páginas de erro detalhadas só existem em `development`.
- **Atrás de proxy reverso** (Nginx na frente, Cloudflare, load balancer), preencha `APP_PROXY_IPS`; do contrário o sistema não percebe que o acesso é HTTPS e os cookies e o HSTS não funcionam como esperado. Detalhes em [cookies-e-sessao.md](cookies-e-sessao.md) e [cabecalhos-de-seguranca.md](cabecalhos-de-seguranca.md).
- **API:** `API_JWT_KEY` precisa ter pelo menos 32 caracteres. Se a sua for mais curta, gere outra, por exemplo com `openssl rand -hex 32`. Trocar a chave invalida os tokens já emitidos, e os apps pedem novo login.
- **Não troque `APP_ENCRYPTION_KEY`.** Ela cifra os segredos do 2FA e alimenta o limite de tentativas de login.

### 3.4. Defina o `STORAGE_PATH` e rode as migrations

Defina o `STORAGE_PATH` **antes** de migrar. Crie a pasta fora da pasta pública do servidor web e dê permissão de escrita ao usuário do PHP:

```bash
sudo mkdir -p /var/mapos-storage
sudo chown www-data:www-data /var/mapos-storage
```

```
STORAGE_PATH=/var/mapos-storage
```

Depois atualize o banco, de um destes jeitos:

- **Pelo painel:** logado como administrador, menu Configurações → Sistema → **Atualizar banco de dados**.
- **Pelo terminal**, na raiz do projeto:

  ```bash
  php index.php tools migrate
  ```

As migrations da v5 (a última da 4.x é `20240503170400_add_garantia_status_to_vendas_table`):

| Migration | O que faz |
| --- | --- |
| `add_tema_modo_destaque_to_configuracoes` e `remove_tema_destaque_from_configuracoes` | Converte o `app_theme` da 4.x no modo de cor (`app_tema_modo`: claro, escuro ou sistema). A cor de destaque deixou de existir. |
| `create_login_attempts` | Tabela do limite de tentativas de login. |
| `invalidate_plain_password_reset_tokens` | Invalida os tokens de recuperação de senha do cliente que estavam em texto claro. Quem tinha um pedido pendente precisa pedir de novo. |
| `fix_decimal_scale_of_prices` | Ajusta preços de produtos e serviços e saldo das contas para `DECIMAL(10,2)`. |
| `create_os_historico` | Histórico de status da OS. |
| `create_api_tokens` | Tabelas do refresh token e do limite de requisições da API. |
| `create_two_factor` | Colunas e tabela do 2FA. |
| `add_tema_modo_to_usuarios` | Preferência de tema por usuário. |
| `move_uploads_to_storage` | Move `assets/anexos` e `assets/arquivos` para o `STORAGE_PATH` e grava no banco o caminho relativo. |
| `add_desconto_to_itens` | Colunas de desconto por item em OS e vendas. |

Sobre o `move_uploads_to_storage`: ele pode rodar de novo com segurança; arquivo que não for encontrado fica como estava. Depois de migrar, confira que os anexos abrem e que `assets/anexos` e `assets/arquivos` esvaziaram. O link direto antigo (`/assets/anexos/...`) deixa de funcionar de propósito. Mais detalhes, e o rollback, em [armazenamento-de-arquivos.md](armazenamento-de-arquivos.md).

### 3.5. Configure o servidor web

A v5 foi feita para o servidor entregar **só** `index.php` e `assets/`. Bloqueie o resto, principalmente `application/` (onde ficam o `.env`, os logs e, sem `STORAGE_PATH`, os anexos).

**Apache:** o `.htaccess` da raiz bloqueia arquivos que começam com `.`, como o `application/.env`. Isso só funciona com `AllowOverride` liberado para a pasta do sistema; sem isso o servidor ignora o `.htaccess` e o `.env` fica legível pela URL. Se não puder liberar o `AllowOverride`, bloqueie `application/` na configuração do virtual host (`<Directory>` com `Require all denied`). Teste depois de configurar:

```bash
curl -I https://seu-dominio/application/.env
```

A resposta deve ser `403` ou `404`, nunca `200`.

**Nginx** (o `docker/etc/nginx/default.conf` é um exemplo de ponto de partida, para desenvolvimento). Em produção, acrescente:

```nginx
# nunca servir arquivos ocultos, o código e os dados da aplicação
location ~ /\.(?!well-known) { deny all; }
location ^~ /application/ { deny all; }
location ^~ /updates/ { deny all; }
location ~* \.sql$ { deny all; }

# instalador: arquivos internos nunca são públicos
location ~* ^/install/(settings\.json|instalador\.php|.*\.lock)$ { deny all; }
location ^~ /install/view/ { deny all; }

# upload: nunca executar scripts nas pastas de arquivos públicas
location ~* ^/assets/(anexos|arquivos|uploads|userImage)/.*\.(php[0-9]*|phtml|phar|pht|phps)$ { deny all; }
```

Se o `STORAGE_PATH` estiver fora da pasta pública (o recomendado), nenhuma regra de nginx é necessária para os anexos. Sem `STORAGE_PATH`, o bloqueio de `/application/` é o que os protege.

Depois de testar o acesso, **apague a pasta `install/`** do servidor.

### 3.6. Tarefas agendadas

Os cron jobs de e-mail continuam os mesmos:

```
*/2 * * * * php /caminho/do/sistema/index.php email/process
*/5 * * * * php /caminho/do/sistema/index.php email/retry
```

### 3.7. Confira

- Login no painel e na área do cliente (`/mine`).
- Dashboard, uma OS (abrir, anexar arquivo, mudar o desconto de um item), uma venda e um lançamento.
- Impressão de OS e de um relatório em PDF.
- Console do navegador sem erros de CSP (se algo for bloqueado, veja [cabecalhos-de-seguranca.md](cabecalhos-de-seguranca.md)).
- Se usa a API: login, `refresh` e uma rota autenticada (veja abaixo).
- Em Meu perfil, a opção de ativar o 2FA ([dois-fatores.md](dois-fatores.md)).

## 4. O que muda para cada perfil

### Quem usa o sistema no dia a dia

- **Tema:** claro, escuro ou sistema, escolhido na barra do topo e gravado no perfil de cada usuário.
- **Busca global** com `Ctrl`/`⌘` + `K`, e os atalhos `F1` a `F4` para clientes, produtos, serviços e OS.
- **Login:** depois de 5 falhas para o mesmo e-mail (ou 20 do mesmo IP) o acesso é bloqueado por 1 minuto, e o tempo dobra a cada nova falha, até 1 hora.
- **2FA opcional** em Meu perfil. O administrador pode exigir o 2FA por grupo de permissão.
- **Desconto por item** na OS e na venda, em R$ ou em %, além do desconto geral. O desconto geral passa a incidir sobre o total já com os descontos dos itens.
- **Recuperação de senha do cliente:** o link vale 1 hora (eram 24) e uma só vez.
- **Exclusões** passam por uma janela de confirmação, e ações das cobranças (atualizar, enviar por e-mail, confirmar, cancelar, excluir) e a exclusão de usuário usam POST com token CSRF. Links salvos que apontavam para elas como GET deixam de funcionar.

### Quem administra o servidor

- Rotas do painel passam pelo mapa de permissões (`application/config/permissions_map.php`): **o que não estiver mapeado é negado**. Um controller que você criou precisa de entrada nele.
- Os cabeçalhos de segurança são enviados em toda resposta, e a CSP está em `enforce`: scripts e estilos inline são bloqueados.
- Anexos e arquivos vêm do `STORAGE_PATH` e só abrem com login e permissão. O backup do painel é só do banco: inclua o `STORAGE_PATH` no seu backup de arquivos.
- O atualizador do painel usa releases com tag, verifica o SHA256 e guarda backup dos arquivos trocados ([atualizacao.md](atualizacao.md)).
- A logo do emitente e as fotos de perfil continuam em `assets/uploads` e `assets/userImage`.

### Quem personalizou views, CSS ou JavaScript

A v5 trocou a base do front-end. Customizações da 4.x **não são aproveitadas automaticamente**:

- Saíram Bootstrap, jQuery, jQuery UI, tema Matrix, Font Awesome, Boxicons, DataTables, Select2, Trumbowyg e as demais bibliotecas antigas. Plugins que dependem de jQuery não funcionam mais.
- As telas usam Tailwind v4, os tokens de `assets/src/tokens.css` e a biblioteca de componentes (`component('button', [...])`). A fonte de verdade da interface é o [`DESIGN.md`](../DESIGN.md).
- Toda tela do painel passa por `MY_Controller::layout()`. O modo legado do layout não existe mais.
- Não há `<script>` inline: o JavaScript da página fica em módulos ES em `assets/js/modules/`, e a CSP bloqueia o inline.
- Toda saída nas views passa por `e()` (`application/helpers/escape_helper.php`).
- Não existe mais cor de destaque configurável: a marca tem uma única cor de ação. A configuração `app_tema_destaque` foi removida, e `app_theme` só é lida para converter o tema antigo no modo de cor.
- As impressões e os PDFs usam `assets/dist/imprimir.css` e `assets/dist/pdf.css`.
- Para alterar CSS ou views e gerar o resultado, é preciso Node >= 22 e `npm ci && npm run build`. Veja o [CONTRIBUTING](../CONTRIBUTING.md#front-end).

Se você mantém um fork, o caminho mais seguro é reaplicar suas mudanças sobre as telas novas, e não tentar mesclar as views antigas.

### Quem usa a API (`/api/v1`)

O fluxo antigo continua; o novo é opcional. Detalhes completos em [api-autenticacao.md](api-autenticacao.md). O que muda:

- **`API_ENABLED=false` agora desliga a API de verdade:** todas as rotas respondem `404`, inclusive por URL direta de controller. Na 4.x ela continuava acessível pelo roteamento padrão.
- **Login:** a resposta ganha `refresh_token`, `token_type` e `expires_in`. O access token passa a ter a claim `exp`.
- **Renovação:** `POST /api/v1/refresh` e `POST /api/v1/logout` (refresh token de uso único). Veja em [api-autenticacao.md](api-autenticacao.md) o estado atual da rota legada `GET /api/v1/reGenToken`.
- **2FA:** quem ativou o 2FA precisa enviar o campo `totp` no login. O app móvel atual não envia, então esses usuários só devem ligar o 2FA depois que o app for atualizado ([dois-fatores.md](dois-fatores.md)).
- **Permissões** são consultadas no banco a cada requisição; o token não as carrega.
- **Limite de requisições:** `API_RATE_LIMIT` (300 por minuto por IP). Acima disso, `429` com `Retry-After`.
- **CORS:** só com `API_CORS_ORIGINS` preenchido. App Android/iOS não precisa.
- **Anexos de OS:** a resposta deixa de trazer o caminho do arquivo e passa a trazer `url` e `thumb_url`, que exigem o token. O arquivo é baixado por `GET api/v1/os/<os>/anexos/<id>` (`?miniatura=1` para a miniatura) e, para o cliente, `GET api/v1/client/os/<os>/anexos/<id>`.
- **Login com limite de tentativas**, como no painel.

## 5. Rollback

A forma mais segura de voltar é **restaurar o backup**: o banco e os arquivos da pasta antiga. Mantenha a pasta da 4.x intacta até ter certeza de que a v5 está estável.

Se precisar voltar só o banco e manter a v5 instalada, cada migration tem `down()`, e o comando de versão desfaz as posteriores. A última migration da 4.x é `20240503170400`:

```bash
php index.php tools migrate 20240503170400
```

Isso desfaz todas as migrations da v5. Faça backup antes, porque colunas e tabelas novas (2FA, tokens, histórico da OS, desconto por item) são removidas com os dados que tinham.

Para voltar **só** os anexos para `assets/`, desfaça apenas a migration `move_uploads_to_storage`, como descrito em [armazenamento-de-arquivos.md](armazenamento-de-arquivos.md#rollback).

Depois do rollback do banco, volte também os arquivos da 4.x, o `.env` anterior e, se mudou o PHP, a versão que a 4.x usava.

## 6. Problemas comuns

| Sintoma | Causa provável |
| --- | --- |
| Tela branca ou erro 500 logo após trocar os arquivos | PHP < 8.5 no servidor web, ou `application/vendor` ausente (instalação pelo código-fonte sem `composer install --no-dev`). |
| Painel sem estilo ou sem botões | Pasta `assets/` incompleta, ou o servidor não entrega `assets/dist` e `assets/vendor`. |
| Anexos antigos não abrem | `STORAGE_PATH` não definido antes da migration, ou sem permissão de escrita. Defina, corrija a permissão e rode `php index.php tools migrate` de novo. |
| Botões e janelas não respondem; erros de CSP no console | Script inline ou biblioteca de CDN adicionada por customização. Ajuste a customização; evite desligar a CSP. |
| A API responde `404` | `API_ENABLED` está `false`. |
| O 2FA de um usuário deixou de abrir | A `APP_ENCRYPTION_KEY` foi trocada. Redefina o 2FA do usuário em Usuários → editar. |

Dúvidas e problemas: [Discussions](https://github.com/RamonSilva20/mapos/discussions) ou contato@mapos.com.br. Falhas de segurança **não** vão em issue pública: veja o [SECURITY.md](../SECURITY.md).
