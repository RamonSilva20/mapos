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
- [Front-end](#front-end)
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

- PHP >= 8.5, com as extensões `curl` e `gd`
- MySQL >= 5.7 (recomendado 8.0+)
- Composer >= 2
- Node.js >= 22, só se você for mexer em views ou em CSS (veja [Front-end](#front-end))

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
├── src/          Fontes do CSS (Tailwind), compiladas para dist/
├── dist/         CSS compilado, commitado
└── vendor/       Bibliotecas de JS copiadas do npm, commitadas (gerado)
scripts/          Scripts de build do front-end
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
application/vendor/bin/php-cs-fixer fix --dry-run --diff
```

Boas práticas adicionais:

- Escape a saída nas views para evitar XSS: use `e()` para texto (`<?= e($cliente->nome) ?>`) e o helper `printSafeHtml()` (`application/helpers/general_helper.php`, baseado no HTMLPurifier) quando precisar renderizar HTML vindo do usuário. Veja [Escape nas views](#escape-nas-views).
- Use o Query Builder do CodeIgniter ou *query bindings* nos models. **Nunca** concatene entrada do usuário em SQL.
- Valide e autorize no controller: confira o ID recebido e a permissão do usuário antes de operar sobre o registro.
- Siga o idioma já usado no arquivo que você está editando (o código do projeto mistura português e inglês; mantenha a consistência local em vez de renomear o entorno).
- Mantenha o Pull Request focado: evite reformatar arquivos inteiros junto com uma correção funcional, pois isso dificulta muito a revisão.

### Escape nas views

Toda saída dinâmica numa view passa por `e()`, que é o `html_escape()` do CodeIgniter devolvendo sempre string (`null` vira `''`, e array gera erro em vez de imprimir `Array`). HTML confiável, como uma descrição com formatação, passa por `printSafeHtml()`. Nunca imprima HTML cru.

O CI roda `php scripts/check-escape.php`, que acusa `<?= $x ?>`, `echo $x` e `print $x` sem escape em `application/views/`. Números podem sair com cast (`<?= (int) $os->idOs ?>`) ou por `number_format()`, `count()` e `date()`.

As views antigas têm ocorrências conhecidas, contadas por arquivo no `escape-baseline.json`. O CI só falha quando um arquivo passa da contagem. Ao corrigir ocorrências antigas, baixe a contagem com:

```bash
php scripts/check-escape.php --update-baseline
```

Para ver todas as ocorrências: `php scripts/check-escape.php --list`. Não suba a contagem do baseline para fazer o CI passar: escape a saída.

## Front-end

O CSS da v5 usa **[Tailwind CSS v4](https://tailwindcss.com/)**, compilado pela CLI do Tailwind. As fontes ficam em `assets/src/` (`app.css` para as telas novas, `layout.css` para a moldura do painel, `tokens.css` compartilhado) e o resultado em `assets/dist/`, que **é commitado**. Por isso quem só instala ou atualiza o Map-OS não precisa de Node.

Se você alterar uma view ou o CSS, rode o build e commite o resultado junto:

```bash
npm ci
npm run build
```

Durante o desenvolvimento, `npm run watch:css` recompila a cada alteração.

O Tailwind lê as classes de `application/views/**/*.php`. Escreva o nome da classe inteiro no PHP (`'bg-red-500'`), e não montado por concatenação (`'bg-' . $cor . '-500'`), senão ela não é encontrada e não entra no CSS.

O CI refaz o build e falha se o `assets/dist` commitado não for o resultado dele.

### Cores e modo escuro

As views novas usam os **tokens semânticos** definidos em `assets/src/app.css`, nunca uma cor fixa: `bg-bg`, `bg-surface`, `bg-surface-2`, `text-text`, `text-muted`, `border-border`, `bg-accent-600`/`text-accent-contrast` (botão primário), `text-success`, `bg-danger-soft` e afins. Assim a tela funciona nos modos claro e escuro e com qualquer cor de destaque, sem precisar de `dark:`.

O tema tem duas configurações, `app_tema_modo` (`claro`, `escuro` ou `sistema`) e `app_tema_destaque` (`laranja`, `azul`, `violeta`, `verde` ou `grafite`). O layout aplica as duas no `<html>` com `temaAtributosHtml($configuration)` e carrega `assets/js/tema.js` no `<head>` para o modo "sistema". A antiga `app_theme` continua existindo só para as telas legadas.

### Bibliotecas de JavaScript

As bibliotecas de JS da v5 (Alpine.js, Tom Select, flatpickr, IMask, SweetAlert2, Chart.js e FullCalendar) são instaladas pelo npm, com versão fixa no `package.json`. O `npm run build` também roda o `build:vendor` (`scripts/build-vendor.mjs`), que copia os arquivos de navegador e a licença de cada uma para `assets/vendor/<lib>/`, pasta commitada pelo mesmo motivo do CSS. As versões copiadas ficam em `assets/vendor/versions.json`.

O `assets/vendor` é inteiro gerado pelo script: não edite nada lá dentro à mão. Para atualizar uma biblioteca:

```bash
npm install --save-exact nome-da-lib@versao
npm run build
```

Para adicionar uma nova, ou outro arquivo de uma já existente, inclua-a na lista do `scripts/build-vendor.mjs`. Não adicione bibliotecas novas que dependam de jQuery: ele sai até o fim da Beta.

### JavaScript nas views

View nova não tem `<script>` com código. O JavaScript da página fica num ES module em `assets/js/modules/<pasta>/<nome>.js`, sem bundler, e a view só declara qual módulo usa e entrega os dados:

```php
<div <?= js_module('servicos/listagem') ?>>
    <a href="#modal-excluir" data-servico="<?= (int) $r->idServicos ?>">Excluir</a>
</div>
<?= page_data('dados-servicos', ['porPagina' => $porPagina]) ?>
```

```js
// assets/js/modules/servicos/listagem.js
import { lerJson } from '../../lib/dados.js';
import { post, ErroHttp } from '../../lib/http.js';

export default function iniciar(elemento) {
    const { porPagina } = lerJson('dados-servicos');
    // elemento é o <div data-module="servicos/listagem">
}
```

- O `assets/js/app.js`, carregado pelo layout, procura `[data-module]` e chama o `export default` de cada módulo com o elemento.
- Dado simples vai em atributo `data-*`, lido com `lerDado(elemento, 'chave')`. Estrutura maior vai em `page_data()`, que gera um `<script type="application/json">` com escape seguro, lido com `lerJson(id)`.
- AJAX usa `get()`/`post()` de `assets/js/lib/http.js`. Eles mandam o token do CSRF e o `X-Requested-With`, e transformam a negação de permissão (JSON 403) em `ErroHttp`.

Isso tira o `'unsafe-inline'` do caminho da CSP (#2878). O CI roda `php scripts/check-inline-script.php` pelo PHPUnit e falha se uma view ganhar `<script>` inline. As views antigas estão contadas por arquivo em `inline-script-baseline.json`; ao migrar uma delas, baixe a contagem com `php scripts/check-inline-script.php --update-baseline`.

Os utilitários de `assets/js` têm testes em `tests/js`, rodados com `npm run test:js`.

### Componentes

Telas novas são montadas com a biblioteca de componentes, em vez de HTML escrito à mão:

```php
<?= component('input', ['name' => 'nome', 'label' => 'Nome', 'required' => true, 'error' => form_error('nome')]) ?>
<?= component('button', ['label' => 'Salvar', 'type' => 'submit', 'icon' => 'bx-save']) ?>
```

Os partials ficam em `application/views/components/` e as props aceitas em `application/helpers/componente_helper.php`. Prop desconhecida, obrigatória faltando ou valor fora das opções gera erro na hora, em vez de ser ignorado. Componentes disponíveis: `button`, `input`, `select`, `textarea`, `card`, `table`, `modal`, `alert`, `toast`, `badge`, `pagination`, `empty-state` e `breadcrumb`.

Todo valor é escapado. Os slots (`body`, `footer`, `actions`, `message`, células da tabela) recebem texto, que também é escapado, ou um `HtmlSeguro`: a saída de outro `component()` ou de `html_purificado()`, que passa HTML do usuário pelo HTMLPurifier. Não crie um `HtmlSeguro` à mão com dado do usuário. Atributos extras vão na prop `attrs`; handlers `on*` são recusados (o JavaScript fica nos módulos).

Listagens novas paginam com o componente `pagination`, e não com o `create_links()` do CodeIgniter. O controller monta as props com `MY_Controller::paginacao()`, que usa o mesmo offset na URL que o `CI_Pagination`, então o model não muda:

```php
// controller
$this->data['paginacao'] = $this->paginacao(site_url('clientes/gerenciar'), $total, $this->uri->segment(3));
// view
<?= component('pagination', $paginacao) ?>
```

O markup do Bootstrap 2 das telas legadas fica em `application/config/pagination.php` até a remoção do frontend legado (#2855).

Para ver todos os componentes e variantes, nos modos claro e escuro e com cada cor de destaque, abra `/index.php/componentes` com `APP_ENVIRONMENT=development`. Em outros ambientes a página responde 404.

### Layout do painel e modo legado

Toda tela do painel passa por `MY_Controller::layout()`, que monta a moldura da v5 (`application/views/tema/`): sidebar recolhível (gaveta no celular), topbar com busca, troca de modo de cor e menu do usuário, breadcrumb e mensagens de flash como toast. O menu sai de `layoutMenu()` (`application/helpers/layout_helper.php`) e cada item só aparece se o mapa de permissões liberar a rota de destino.

Durante a Beta as telas são migradas aos poucos, então o layout tem um **modo legado**, ligado por padrão: ele carrega Bootstrap 2, jQuery, matrix-style e o `tema-*.css`, e a tela fica dentro de `#content` como antes. Uma tela já migrada para os componentes desliga o modo legado e passa a receber o `app.css` completo:

```php
$this->data['legacy_assets'] = false;
$this->data['view'] = 'clientes/clientes';
return $this->layout();
```

A moldura usa um CSS próprio, `assets/src/layout.css` → `assets/dist/layout.css`, montado para não interferir nas telas antigas: as utilities e o reset só valem dentro de elementos `.v5-shell`, e são `!important` para que o CSS legado também não interfira na moldura. Use `npm run watch:layout` ao mexer nela. HTML novo da moldura fica dentro de um wrapper `.v5-shell`; o conteúdo da tela nunca.

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
- [ ] A saída nova nas views passa por `e()` ou `printSafeHtml()` (`php scripts/check-escape.php`).
- [ ] Não há credenciais, `.env`, dumps de banco ou arquivos de IDE no diff.
- [ ] As pastas `application/vendor/` e `node_modules/` não foram commitadas.
- [ ] Se mexeu em view, CSS ou biblioteca de JS, rodou `npm run build` e commitou o `assets/dist` e o `assets/vendor`.
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
