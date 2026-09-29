# Contribuindo com o Map-OS

Obrigado pelo interesse em contribuir! O Map-OS é um projeto open source e toda ajuda é bem-vinda — desde correções de typo até novas funcionalidades.

Este guia descreve como preparar o ambiente, o padrão de código adotado e como abrir um Pull Request que possa ser revisado rapidamente.

Ao participar deste projeto, você concorda em seguir o nosso [Código de Conduta](CODE_OF_CONDUCT.md).

## Sumário

- [Formas de contribuir](#formas-de-contribuir)
- [Reportando bugs](#reportando-bugs)
- [Sugerindo funcionalidades](#sugerindo-funcionalidades)
- [Vulnerabilidades de segurança](#vulnerabilidades-de-segurança)
- [Ambiente de desenvolvimento](#ambiente-de-desenvolvimento)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Padrão de código](#padrão-de-código)
- [Alterações no banco de dados](#alterações-no-banco-de-dados)
- [Comandos de terminal](#comandos-de-terminal)
- [Mensagens de commit](#mensagens-de-commit)
- [Abrindo um Pull Request](#abrindo-um-pull-request)
- [Onde pedir ajuda](#onde-pedir-ajuda)

## Formas de contribuir

- **Reportando bugs** — abra uma [issue](https://github.com/RamonSilva20/mapos/issues/new/choose) usando o template de bug.
- **Sugerindo melhorias** — use o template de solicitação de feature ou participe das [Discussions](https://github.com/RamonSilva20/mapos/discussions).
- **Enviando código** — correções, funcionalidades, testes ou refatorações via Pull Request.
- **Melhorando a documentação** — README, este guia, comentários de código e tutoriais.
- **Ajudando a comunidade** — respondendo dúvidas nas Discussions ou na comunidade do WhatsApp.

Antes de começar a codar algo grande, verifique se já não existe uma issue, Discussion ou Pull Request sobre o assunto. Para mudanças estruturais, abra uma issue antes para alinhar a abordagem com os mantenedores — assim você evita trabalho que pode não ser aceito.

## Reportando bugs

Use o template de bug e inclua:

- versão do Map-OS (exibida no rodapé do sistema);
- versão do PHP e do MySQL/MariaDB;
- forma de instalação (manual, Docker ou instalador automatizado);
- passos para reproduzir, comportamento esperado e comportamento observado;
- mensagens de erro relevantes (verifique `application/logs/`);
- capturas de tela, quando o problema for visual.

Quanto mais fácil for reproduzir o bug, mais rápido ele será corrigido.

## Sugerindo funcionalidades

Descreva **o problema** que você quer resolver, não apenas a solução imaginada. Explique o cenário de uso real (que tipo de empresa, que fluxo de trabalho) — isso ajuda a avaliar se a funcionalidade faz sentido para a base de usuários do Map-OS como um todo.

## Vulnerabilidades de segurança

**Não abra issue pública para falhas de segurança.** Relate em caráter privado para **contato@mapos.com.br**, incluindo descrição da falha, impacto e passos de reprodução. Assim a correção pode ser publicada antes que a falha se torne conhecida.

## Ambiente de desenvolvimento

### Requisitos

- PHP >= 8.4, com as extensões `curl` e `gd`
- MySQL >= 5.7 (recomendado 8.0+)
- Composer >= 2

### 1. Fork e clone

```bash
git clone https://github.com/SEU_USUARIO/mapos.git
cd mapos
git remote add upstream https://github.com/RamonSilva20/mapos.git
```

### 2a. Com Docker (recomendado)

```bash
cd docker
docker-compose up --force-recreate
```

Acesse `http://localhost:8000/` e siga o assistente de instalação com estes dados:

| Campo | Valor |
| --- | --- |
| Host | `mysql` |
| Usuário | `mapos` |
| Senha | `mapos` |
| Banco de dados | `mapos` |
| URL | `http://localhost:8000/` |

O phpMyAdmin fica disponível em `http://localhost:8080/`.

> **Atenção:** a pasta `docker/data` guarda os arquivos do MySQL. Se ela for apagada, você perde o banco de dados local.

### 2b. Instalação manual

```bash
composer install
```

Aponte o document root do seu webserver para a raiz do projeto, acesse a URL e siga o assistente de instalação.

> Em desenvolvimento use `composer install` (sem `--no-dev`), para que as ferramentas de desenvolvimento como o `php-cs-fixer` sejam instaladas. O `--no-dev` do README é orientado a ambientes de produção.

### Observações importantes

- O `vendor-dir` do projeto é **`application/vendor`**, e não `./vendor` (definido em `composer.json`). Os binários ficam em `application/vendor/bin/`.
- As configurações de ambiente ficam em `application/.env`, que é ignorado pelo Git. **Nunca** faça commit dele nem de credenciais.
- A página de erro detalhada (Whoops) só é exibida quando `APP_ENVIRONMENT=development` **e** `WHOOPS_ERROR_PAGE_ENABLED=true` no `application/.env`. Em qualquer outro ambiente ela fica desabilitada.

## Estrutura do projeto

O Map-OS é construído sobre o **CodeIgniter 3** e segue o padrão MVC do framework:

```
application/
├── config/       Configurações (rotas, banco, validações, gateways de pagamento)
├── controllers/  Controllers da aplicação e da API (subpasta api/)
├── core/         Classes base (MY_Controller, MY_Model etc.)
├── database/
│   ├── migrations/  Alterações de schema
│   └── seeds/       Seeders para dados de teste
├── helpers/      Funções auxiliares
├── libraries/    Bibliotecas próprias (Permission, Gateways, REST_Controller etc.)
├── models/       Acesso a dados
└── views/        Templates das telas
assets/           CSS, JS, imagens e uploads
docker/           Ambiente de desenvolvimento com Docker
install/          Assistente de instalação
banco.sql         Schema base usado na instalação inicial
```

## Padrão de código

O projeto usa **[PHP-CS-Fixer](https://cs.symfony.com/)** com as regras definidas em `.php-cs-fixer.php`:

- `@PSR2` como base;
- sintaxe curta de arrays (`[]` em vez de `array()`);
- imports ordenados alfabeticamente;
- sem imports não utilizados.

Antes de commitar, formate o código:

```bash
composer format
```

Para apenas verificar, sem alterar os arquivos:

```bash
composer format:check
```

Boas práticas adicionais:

- Escape a saída nas views para evitar XSS: use o helper que corresponde ao contexto da saída — `esc()` para texto e atributos HTML, `esc_json()` dentro de `<script>`, `esc_url()` em `href`, `esc_img_src()` em `<img src>` (os QR codes de pagamento são data URIs, que `esc_url()` recusa), `esc_css()` em `style` e `esc_msg()` para mensagens do SweetAlert2. Quando precisar renderizar HTML vindo do usuário, use `printSafeHtml()` (também em `application/helpers/general_helper.php`, baseado no HTMLPurifier). Nunca use `echo` direto em uma linha do banco, valor de sessão ou de configuração.
- Rode `composer xss:check` depois de editar views: o comando falha se algum valor chegar à página sem escape. A tabela completa de helpers está em `AGENTS.md`.
- Use o Query Builder do CodeIgniter ou *query bindings* nos models. **Nunca** concatene entrada do usuário em SQL.
- Valide e autorize no controller: confira o ID recebido e a permissão do usuário antes de operar sobre o registro.
- Siga o idioma já usado no arquivo que você está editando (o código do projeto mistura português e inglês; mantenha a consistência local em vez de renomear o entorno).
- Mantenha o Pull Request focado: evite reformatar arquivos inteiros junto com uma correção funcional, pois isso dificulta muito a revisão.

## Alterações no banco de dados

Alterações de schema **devem** ser feitas por migration — assim quem já usa o sistema consegue atualizar sem perder dados. Não altere o `banco.sql` no lugar de criar uma migration.

Criar uma nova migration:

```bash
php index.php tools migration "add_campo_x_na_tabela_y"
```

O arquivo é gerado em `application/database/migrations/` com prefixo de timestamp. Implemente **sempre** os dois métodos:

```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_add_campo_x_na_tabela_y extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('tabela_y', [
            'campo_x' => [
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_column('tabela_y', 'campo_x');
    }
}
```

Aplicar as migrations pendentes:

```bash
php index.php tools migrate
```

Regras:

- **Nunca edite uma migration que já foi publicada em uma release.** Crie outra para ajustar.
- Teste tanto a instalação nova quanto a atualização de uma base existente.
- O `down()` deve realmente reverter o `up()`.

Para dados de teste, use seeders:

```bash
php index.php tools seeder "nome_do_seeder"
php index.php tools seed nome_do_seeder
```

## Comandos de terminal

Todos os comandos disponíveis podem ser listados a partir da raiz do projeto:

```bash
php index.php tools
```

| Comando | Descrição |
| --- | --- |
| `php index.php tools migration "nome"` | Cria um novo arquivo de migration |
| `php index.php tools migrate ["versao"]` | Executa as migrations (versão é opcional) |
| `php index.php tools seeder "nome"` | Cria um novo arquivo de seed |
| `php index.php tools seed "nome"` | Executa o seed informado |
| `php index.php email/process` | Envia os e-mails pendentes da fila |
| `php index.php email/retry` | Reenvia os e-mails que falharam |

## Mensagens de commit

Use [Conventional Commits](https://www.conventionalcommits.org/pt-br/v1.0.0/), padrão já adotado no histórico do projeto:

```
<tipo>: <descrição no imperativo>
```

Tipos mais usados:

| Tipo | Quando usar |
| --- | --- |
| `feat` | Nova funcionalidade |
| `fix` | Correção de bug |
| `docs` | Apenas documentação |
| `refactor` | Refatoração sem mudança de comportamento |
| `test` | Adição ou ajuste de testes |
| `chore` | Manutenção, dependências, build |

Exemplos:

```
fix: corrige cálculo de desconto ao editar lançamento
feat: adiciona filtro por período no relatório de vendas
docs: atualiza instruções de instalação via Docker
```

A descrição pode ser em português ou inglês — o histórico aceita ambos. Prefira mensagens que expliquem **o que muda para o usuário**, e não o detalhe da implementação.

## Abrindo um Pull Request

1. Atualize seu `master` a partir do upstream:

   ```bash
   git checkout master
   git pull upstream master
   ```

2. Crie uma branch descritiva:

   ```bash
   git checkout -b fix/calculo-desconto-vendas
   ```

3. Faça as alterações e rode `composer format`.

4. Teste manualmente o fluxo afetado. Se a mudança envolveu o banco, teste também a migration nos dois sentidos.

5. Faça o push e abra o Pull Request contra a branch `master` do repositório oficial.

### Checklist antes de enviar

- [ ] O PR resolve **um** problema (evite juntar assuntos diferentes).
- [ ] O código está formatado (`composer format`).
- [ ] Se você editou views, `composer xss:check` passa.
- [ ] Não há credenciais, `.env`, dumps de banco ou arquivos de IDE no diff.
- [ ] A pasta `application/vendor/` não foi commitada.
- [ ] Alterações de schema têm migration com `up()` e `down()`.
- [ ] A descrição explica o problema, a solução e como testar.
- [ ] Há capturas de tela (antes/depois) quando a mudança é visual.
- [ ] Issues relacionadas foram referenciadas (ex.: `Closes #123`).

### O que esperar

Os mantenedores podem pedir ajustes antes do merge — isso é parte normal do processo, não uma rejeição. PRs muito grandes ou sem descrição demoram mais para serem revisados; se a mudança for extensa, considere quebrá-la em partes menores e independentes.

## Onde pedir ajuda

- **Discussions:** https://github.com/RamonSilva20/mapos/discussions
- **Comunidade no WhatsApp:** https://chat.whatsapp.com/GVSg8tPQzXy0grfYpRfQps
- **E-mail:** contato@mapos.com.br

Obrigado por contribuir com o Map-OS!
