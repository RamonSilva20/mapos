# Migrando do Map-OS 4.x para a v5

A v5 troca o visual inteiro do painel, endurece a segurança e exige PHP 8.5. O banco de dados e os seus dados (clientes, OS, vendas, anexos, usuários) continuam os mesmos: as migrations só acrescentam tabelas e colunas, e ajustam as casas decimais de preços e saldos.

> A v5 está em pré-lançamento. Faça o teste numa **cópia** da sua instalação antes de migrar a de produção.

## Antes de começar

1. **PHP 8.5 ou mais novo**, com as extensões `curl`, `gd`, `mysqli` e `zip`. Muitas hospedagens compartilhadas ainda não oferecem o 8.5: confira no painel da hospedagem (seletor de versão do PHP) antes de seguir. Se não houver, fique na 4.x.
2. **Backup do banco**: Configurações → Backup, ou `mysqldump` pelo terminal.
3. **Backup dos arquivos**: copie a pasta inteira do sistema, em especial `application/.env`, `assets/anexos`, `assets/arquivos`, `assets/uploads` e `assets/userImage`.
4. Anote a versão atual (rodapé do painel) e quais gateways de pagamento e apps móveis você usa.

## Passo a passo

1. Baixe o zip da release `vX.Y.Z-release.zip` (e confira o `.sha256`) em [Releases](https://github.com/RamonSilva20/mapos/releases).
2. Extraia por cima da instalação, **sem apagar** `application/.env` e as pastas de upload. O zip não traz nem sobrescreve esses arquivos.
3. Na raiz, rode `composer install --no-dev`. Sem o terminal, envie a pasta `application/vendor` já instalada de uma cópia local.
4. Compare o seu `application/.env` com `application/.env.example` e acrescente o que faltar (veja [O que mudou no `.env`](#o-que-mudou-no-env)).
5. Entre no painel como administrador e vá em **Configurações → Atualizar banco de dados**. As migrations não rodam sozinhas.
6. Limpe o cache do navegador (Ctrl+F5): o CSS e o JS são outros.
7. Confira o roteiro de [verificação](#verificação-depois-de-migrar).

Para voltar atrás: restaure os arquivos e o banco do backup. Não há migração de volta automática.

## O que mudou no `.env`

Chaves novas (todas têm valor padrão seguro; só mexa se precisar):

| Chave | Para quê |
|---|---|
| `APP_CSP` | `enforce` (padrão), `report-only` ou `off`. Veja [cabeçalhos de segurança](cabecalhos-de-seguranca.md). |
| `APP_CSP_REPORT` | Registra violações em `application/logs/csp-violacoes.json`. |
| `APP_SECURITY_HEADERS`, `APP_HSTS_MAX_AGE`, `APP_HSTS_INCLUDE_SUBDOMAINS` | Cabeçalhos de segurança e HSTS (só em HTTPS). |
| `APP_COOKIE_SECURE`, `APP_COOKIE_HTTPONLY`, `APP_COOKIE_SAMESITE`, `APP_PROXY_IPS` | Cookies e sessão. Atrás de proxy reverso, veja [cookies e sessão](cookies-e-sessao.md). |
| `UPDATER_PRERELEASES` | `true` para o atualizador oferecer alpha, beta e rc. |

Atenção a duas chaves que já existiam:

- **`API_JWT_KEY` precisa ter pelo menos 32 caracteres.** Se a sua for menor, a API deixa de funcionar. Gere outra com `php -r "echo bin2hex(random_bytes(32));"` e **avise os usuários do app móvel**: trocar a chave desconecta todos.
- **`API_ENABLED=false` agora desliga a API de verdade** (responde 404). Antes só escondia as rotas.

## O que muda para quem usa o sistema

- **Visual novo**: menu lateral recolhível, busca global (Ctrl/⌘ K), atalhos F1 a F4 e modo claro, escuro ou sistema, escolhido por usuário na barra superior.
- **Cor de destaque**: o seletor foi removido. A marca tem uma cor só (laranja). A configuração antiga é apagada pela migration.
- **Telas e fluxos reescritos**: OS (tela única com abas e histórico de status), vendas, lançamentos, cobranças, usuários, permissões e configurações. Os dados são os mesmos; só mudam os botões de lugar.
- **Login**: depois de 5 falhas para o mesmo e-mail, bloqueio de 1 minuto que dobra a cada nova falha, até 1 hora. O mesmo vale para a área do cliente e para a API.
- **Autenticação em dois fatores** (opcional): Meu perfil → Autenticação em dois fatores. O administrador pode exigir por grupo de permissão. Veja [dois fatores](dois-fatores.md).
- **Senhas**: mínimo de 8 caracteres ao cadastrar. A recuperação de senha do cliente vale 1 hora, e os links pendentes antes da migração deixam de valer.
- **Sessão**: usuário desativado ou com conta expirada sai na próxima requisição, em vez de continuar logado.
- **Permissões**: quem tem `cUsuario` ou `cPermissao` só concede o que ele mesmo tem. Só o administrador concede tudo.
- **Backup**: não leva mais sessões e tokens, e só é baixado (nunca fica numa pasta pública).
- **Exclusão de OS faturada** com pagamento já baixado é bloqueada: estorne o recebimento antes.
- **E-mail da OS** exige permissão de editar OS e é enviado por botão (POST), não mais por link.
- **Impressões e PDFs** têm CSS próprio em Tailwind. Se você personalizou o HTML das impressões, refaça a personalização sobre os arquivos novos.

## Se você personalizou o código

- **CSS e JS do tema antigo foram removidos**: Bootstrap, jQuery, Matrix, Font Awesome, Boxicons, Select2, DataTables e afins. Telas ou plugins seus que dependem deles precisam ser reescritos com os componentes da v5 (veja `AGENTS.md` e `DESIGN.md`).
- **Mapa de permissões**: toda rota do painel precisa estar em `application/config/permissions_map.php`. O que não estiver mapeado é **negado**. Controllers próprios devem ser incluídos lá.
- **CSP em enforce**: não há `unsafe-inline` nem `unsafe-eval`. Scripts e `style=` inline, e CDNs externos, são bloqueados. Para migrar com calma, comece com `APP_CSP=report-only` e leia o log de violações.
- **Saída escapada**: use `e()` nas views. O CI recusa saída sem escape nova.
- **Banco de dados**: mudanças de esquema só por migration em `application/database/migrations/`.

## Aplicativo móvel

A API continua compatível com o app atual (`POST /api/v1/login` e o token que ele já usa). Novidades, todas opcionais para o app:

- o token tem expiração padrão (`exp`) e há refresh token de uso único (`POST /api/v1/refresh` e `/logout`);
- quem ativar o 2FA precisa mandar o campo `totp` no login;
- a API só funciona com `API_ENABLED=true`.

Detalhes e exemplos em [autenticação da API](api-autenticacao.md).

## Servidor web

- **Apache**: o `.htaccess` da raiz nega `application/`, `tests/`, `docs/`, `docker/`, `scripts/`, `updates/` e arquivos como `banco.sql`, `composer.lock` e `*.md`. Mantenha `AllowOverride All`. Sem isso, essas regras não valem.
- **Nginx**: o modelo em `docker/etc/nginx/default.conf` tem as regras equivalentes. Copie-as para o seu `server {}`.
- **Pasta `install/`**: depois de instalar, ela se bloqueia sozinha, mas **apague a pasta** do servidor.

## Atualizações futuras

A partir da v5, **Configurações → Atualizar** usa as releases com tag e confere o SHA256 do zip. Veja [atualização](atualizacao.md). A 4.x continua com o seu próprio atualizador, então a primeira passagem 4.x → v5 é manual, como acima.

## Verificação depois de migrar

- [ ] Login do painel e da área do cliente.
- [ ] Listagens de clientes, produtos, serviços, OS, vendas e lançamentos mostram os dados antigos.
- [ ] Abrir uma OS antiga: itens, anexos, anotações e totais.
- [ ] Faturar uma OS de teste e conferir o lançamento.
- [ ] Imprimir uma OS e uma venda (A4 e cupom).
- [ ] Anexar e baixar um arquivo.
- [ ] Enviar um e-mail de teste e rodar o cron (`email/process`).
- [ ] Cobrança no gateway que você usa, em ambiente de teste.
- [ ] App móvel: login e listagem de OS.
- [ ] Sem erros em `application/logs`. Se o painel ficar sem estilo ou um botão não responder, veja o log de violações do CSP.

## Problemas comuns

| Sintoma | Causa provável |
|---|---|
| Tela branca ou erro 500 | PHP abaixo de 8.5, ou `composer install` não rodado. |
| "Classe não encontrada" | `application/vendor` incompleto: rode `composer install --no-dev`. |
| Painel sem estilo | Cache do navegador, ou a pasta `assets/dist` não veio no zip. |
| Botão que não responde | CSP bloqueou um script seu: use `APP_CSP=report-only` e leia o log. |
| App móvel não entra | `API_ENABLED` está `false`, ou a `API_JWT_KEY` foi trocada. |
| "Permissão negada" numa tela própria | Rota fora de `permissions_map.php`. |
| Mensagem pedindo para atualizar o banco | Falta rodar **Atualizar banco de dados**. |

Dúvidas e relatos: [Discussions](https://github.com/RamonSilva20/mapos/discussions). Falha de segurança: veja o `SECURITY.md`.
