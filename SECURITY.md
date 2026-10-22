# Política de Segurança

O Map-OS é usado por empresas para gerenciar ordens de serviço, dados de clientes e informações financeiras. Levamos relatos de segurança a sério e agradecemos quem dedica tempo a encontrar e reportar falhas de forma responsável.

## Versões suportadas

Correções de segurança são publicadas na `master` (5.x) e, até 30/04/2027, também no branch `4.x`. A versão em Laravel ainda está em desenvolvimento e não tem data de lançamento.

| Versão | Branch | Situação | Suporte |
| --- | --- | --- | --- |
| Map-OS em Laravel | — | Em desenvolvimento | Sem data de lançamento |
| 5.x (atual) | `master` | Ativa: novidades e correções | Suporte ativo até a versão em Laravel sair; segurança por mais 12 meses depois |
| 4.x | `4.x` | Só segurança | Até 30/04/2027 |

Dentro de cada linha, só a última release recebe a correção: quem está numa versão anterior (na 4.x, abaixo da 4.55.5) deve atualizar seguindo as instruções de [Atualização](README.md#atualização-v5-em-diante) do README, ou migrar da 4.x pelo [guia de migração](docs/migracao-4x-para-5.md).

## Como reportar uma vulnerabilidade

**Não abra uma issue pública, Discussion ou mensagem na comunidade do WhatsApp descrevendo a falha.** Um relato público expõe todas as instalações do Map-OS antes que exista correção disponível.

Envie o relato em caráter privado para:

**contato@mapos.com.br**

### O que incluir no relato

Quanto mais completo o relato, mais rápida a correção:

- **Tipo da falha** — por exemplo SQL injection, XSS, IDOR, falha de autenticação/autorização, upload arbitrário de arquivo.
- **Localização** — arquivo, rota ou tela afetada (ex.: `application/controllers/Os.php`, rota `/os/editar/1`).
- **Passos para reproduzir** — sequência exata, incluindo requisição HTTP ou payload quando aplicável.
- **Impacto** — o que um atacante consegue fazer explorando a falha, e qual nível de acesso é necessário (anônimo, usuário autenticado comum, administrador).
- **Versão e ambiente** — versão do Map-OS, versão do PHP e forma de instalação (manual, Docker ou instalador automatizado).
- **Sugestão de correção**, se você tiver uma.

### O que acontece depois

1. O relato é confirmado e avaliado pela manutenção do projeto.
2. Sendo confirmada a falha, uma correção é desenvolvida e publicada em uma nova release.
3. O relato é divulgado publicamente **após** a correção estar disponível, para que os usuários possam atualizar antes que os detalhes sejam conhecidos.

Os prazos dependem da gravidade da falha e da disponibilidade da manutenção — este é um projeto open source. Se você não obtiver retorno, sinta-se à vontade para enviar uma cobrança pelo mesmo e-mail.

### Créditos

Quem reporta uma falha é creditado no [CHANGELOG](CHANGELOG.md) junto com a correção, como já acontece com as demais contribuições. Se você preferir não ser creditado, avise no relato.

## Escopo

São considerados relatos válidos falhas que afetem o código do Map-OS, como:

- injeção de SQL, XSS, CSRF ou injeção de comandos;
- falhas de autenticação ou de controle de acesso (incluindo IDOR e escalonamento de privilégios);
- exposição de dados sensíveis de clientes, financeiros ou de credenciais;
- upload ou sobrescrita arbitrária de arquivos;
- falhas nos gateways de pagamento integrados ao sistema.

Normalmente **não** são considerados vulnerabilidades do projeto:

- problemas causados por instalação ou configuração incorreta do servidor (permissões de arquivo, `WHOOPS_ERROR_PAGE_ENABLED` habilitado em produção, banco exposto na internet);
- falhas que exigem que o atacante já tenha acesso de administrador ao sistema;
- self-XSS, ou seja, que dependem de a própria vítima colar o payload;
- ausência de cabeçalhos de segurança sem demonstração de impacto real;
- resultados brutos de scanners automatizados, sem prova de exploração;
- vulnerabilidades em dependências de terceiros — nesses casos, reporte ao projeto de origem e, se quiser, abra uma issue aqui apenas solicitando a atualização da dependência.

## Recomendações para quem opera o Map-OS

Boa parte dos incidentes vem de configuração, não de falha no código. Em produção:

- Mantenha o sistema sempre na **última versão** publicada.
- Instale as dependências com `composer install --no-dev`.
- Mantenha `WHOOPS_ERROR_PAGE_ENABLED` **desabilitado**, para que erros detalhados não sejam exibidos ao usuário final.
- Proteja o arquivo `application/.env`, que contém as credenciais do sistema, e nunca o versione.
- Sirva a aplicação exclusivamente por **HTTPS**.
- Não exponha o banco de dados diretamente na internet.
- Faça backups regulares (Configurações > Backup) e teste a restauração.
- Revise periodicamente os usuários e as permissões cadastradas.

## Contato

**contato@mapos.com.br**

Obrigado por ajudar a manter o Map-OS seguro.
