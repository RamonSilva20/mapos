
![MapOS](https://raw.githubusercontent.com/RamonSilva20/mapos/master/assets/img/logo.png)

![version](https://img.shields.io/badge/version-4.52.0-blue.svg?longCache=true&style=flat-square)
![license](https://img.shields.io/badge/license-Apache-green.svg?longCache=true&style=flat-square)
![theme](https://img.shields.io/badge/theme-Matrix--Admin-lightgrey.svg?longCache=true&style=flat-square)
![issues](https://img.shields.io/github/issues/RamonSilva20/mapos.svg?longCache=true&style=flat-square)
![contributors](https://img.shields.io/github/contributors/RamonSilva20/mapos.svg?longCache=true&style=flat-square)

### Contato: contato@mapos.com.br
### [Feedback](https://github.com/RamonSilva20/mapos/discussions) - Vote ou sugira melhorias

![Map-OS](https://raw.githubusercontent.com/RamonSilva20/mapos/master/docs/dashboard.png)

### [Instalação](Instalacao_xampp_windows.md)

1. Faça o download dos arquivos.
2. Extraia o pacote e copie para seu webserver.
3. Rode o comando `composer install --no-dev` a partir da raiz do projeto.
4. Acesse sua URL e inicie a instalação, é bem simples, basta preencher as informações no assistente de instalação **MAPOS**.
5. Configure o email de envio em Configurações > Sistema > E-mail .
6. Configurar cron jobs para envio de e-mail:
    ##### Enviar emails pendentes a cada 2 minutos.
    - */2 * * * * php /var/www/index.php email/process
    ##### Enviar emails com falha a cada 5 minutos.
    - */5 * * * * php /var/www/index.php email/retry

    ##### Obs: O path até o index.php (/var/www/) deve ser configurado conforme o seu ambiente


### Instalação (Docker)

1. Faça o download dos arquivos.
2. Instale o [Docker](https://docs.docker.com/install/) e o [Docker Compose](https://docs.docker.com/compose/install/).
3. Entre na pasta `docker` no seu terminal e rode o comando `docker-compose up --force-recreate`.
4. Acesse a URL `http://localhost:8000/` no navegador e inicie a instalação.
5. Na etapa de configuração use as seguintes configurações:
```
1. Por favor, insira as informações da sua conexão de banco de dados.
Host: mysql
Usuário: mapos
Senha: mapos
Banco de Dados: mapos

2. Por favor, insira as informações para sua conta de administrador.
Configure do jeito que quiser.

3. Por favor, insira a URL.
URL: http://localhost:8000/
```
6. Configure o email de envio em Configurações > Sistema > E-mail .

    ##### Obs: Cuide da pasta `docker/data`, onde é pasta que o mysql do docker salva os arquivos. Se for deletada você perderá seu banco de dados.
    ##### Obs2: O PhpMyAdmin também e instalado e pode ser acessado em `http://localhost:8080/`.

### Instalação Automatizada
Tutorial Instalação: [https://youtu.be/NgXzzBB_2bM?si=FS_R2xq_W0Jnfn33](https://www.youtube.com/watch?v=aZE-LW_YOE4)
#### Windows 10/11
1. Execute o Prompt de Comando ou PowerShell como Administrador;
2. Execute o comando: `PowerShell -command "& { iwr https://raw.githubusercontent.com/RamonSilva20/mapos/master/install.bat -OutFile MapOS_Install.bat }; .\MapOS_Install.bat"`
3. Siga as instrunções na tela.

#### Linux (Ubuntu/Debian)
1. Abra o Terminal ou acesse seu servidor via SSH;
2. Eleve o privilégio aplicando `sudo su` (Recomendado);
3. Execute o comando: `curl -o MapOS_Install.sh -L https://raw.githubusercontent.com/RamonSilva20/mapos/master/install.sh && chmod +x MapOS_Install.sh && ./MapOS_Install.sh`
4. Siga as instruções na tela.

### Atualização

1. Faça o backup dos arquivos e do banco de dados:
2. Logado como administrador vá em `configurações > backup`.
3. Dentro da pasta `Assets` copie as pastas `anexos`, `arquivos`, `uploads`, `userimage` e qualquer personalização feita dentro da pasta `img`.
4. Dentro da pasta `application` copie o arquivo `.env`.;
5. Substitua os arquivos pelos da nova versão.
6. Rode o comando `composer install --no-dev` a partir da raiz do projeto.
7. Restaure os backups para seus locais devidos.
8. Logue no sistema como administrador e navegue até Configurações -> Sistema e clique no botão `Atualizar Banco de Dados` para atualizar seu banco de dados.
    Obs.: Também é possível atualizar o banco de dados via terminal rodando o comando `php index.php tools migrate` a partir da raiz do projeto;
9. Pronto, sua atualização está concluída;

### Atualização (Docker)

1. Pare o docker de rodar;
2. Faça o backup dos arquivos e do banco de dados:
3. Logado como administrador vá em `configurações > backup`.
4. Dentro da pasta `Assets` copie as pastas `anexos`, `arquivos`, `uploads`, `userimage` e qualquer personalização feita dentro da pasta `img`.
5. Dentro da pasta `application` copie o arquivo `.env`.
6. Substitua os arquivos pelos da nova versão;
7. Entre na pasta `docker` no seu terminal e rode o comando `docker-compose up --force-recreate`;
8. Logue no sistema como administrador e navegue até Configurações -> Sistema e clique no botão `Atualizar Banco de Dados` para atualizar seu banco de dados.
    Obs.: Também é possível atualizar o banco de dados via terminal rodando o comando `php index.php tools migrate` a partir da raiz do projeto;
9. Restaure os backups para seus locais devidos;
10. Pronto, sua atualização está concluída;

### Atualização via sistema

1. Primeiro é necessário atualizar manualmente o sistema para a versão v4.4.0;
2. Quando estiver nessa versão é possível atualizar o sistema clicando no botão "Atualizar Mapos" em Sistema >> Configurações;
3. Serão baixados e atualizados todos os arquivos exceto: `config.php`, `database.php` e `email.php`;

### Novas Funcionalidades

#### Módulo de Notas de Entrada
- **Upload de XML**: Importe notas fiscais (NFe e NFSe) através de upload de arquivo XML
- **Busca SEFAZ com Certificado Digital**: 
  - Busque notas fiscais diretamente na SEFAZ usando certificado digital A1
  - Suporte para certificados .p12, .pfx e .pem
  - Download automático do XML completo da nota
  - Processamento automático após download
- **Consulta de Fila de Distribuição**:
  - Consulte todas as notas disponíveis na fila da SEFAZ pelo CNPJ do emitente
  - Lista todas as notas pendentes de download
  - Baixe e processe múltiplas notas de uma vez
  - Controle de NSU para consultas incrementais
- **Processamento Automático**: Extração automática de dados da nota e itens
- **Adicionar ao Estoque**: Adicione itens da nota diretamente ao estoque com:
  - Cálculo automático de preço de venda por markup (%)
  - Edição individual de preço de venda por item
  - Criação automática de produtos não cadastrados
  - Atualização de estoque de produtos existentes
- **Visualização Completa**: Visualize todos os dados da nota fiscal e seus itens
- **Configuração de Certificado Digital**: 
  - Upload e configuração de certificado digital nas configurações do sistema
  - Suporte para certificados A1 (arquivo)
  - Armazenamento seguro (certificados não são versionados no Git)

#### Campos Fiscais em Produtos
- **NCM** (Nomenclatura Comum do Mercosul): Código de 8 dígitos
- **CEST** (Código Especificador da Substituição Tributária): Código de 7 dígitos
- **CFOP** (Código Fiscal de Operações e Prestações): Código de 4 dígitos
- **Origem**: Classificação da origem da mercadoria (0-8)
- **Tributação ICMS**: Códigos de tributação do ICMS (00-90)
- Disponível em: Cadastro, Edição e Visualização de Produtos

#### Melhorias em Produtos
- **Clonar Produto**: Duplique produtos existentes com um clique
  - Mantém todos os dados do produto original
  - Estoque zerado na cópia
  - Código de barras e descrição marcados como cópia
  - Redireciona para edição do novo produto
- **Botão Editar na Visualização**: Acesso rápido à edição diretamente da página de visualização
- **Nome Clicável na Lista**: 
  - Clique no nome do produto para visualizar
  - Melhor experiência em dispositivos móveis
  - Área de clique ampliada para facilitar o toque
- **Interface Responsiva**: Otimizada para uso em celulares e tablets

#### Importação em Massa
- **Clientes/Fornecedores**: Importe múltiplos clientes via arquivo CSV
- **Produtos**: Importe múltiplos produtos via arquivo CSV com campos fiscais
- **Modelos para Download**: Templates CSV disponíveis para download
- **Validação Automática**: Validação de dados e tratamento de duplicatas
- **Relatório de Importação**: Feedback detalhado sobre sucessos, erros e duplicatas

#### Melhorias na Ordem de Serviço
- **Criação Rápida de Cliente**: Cadastre clientes pessoa física diretamente na tela de criação de OS
  - Cadastro simplificado com informações mínimas
  - Permite cadastro incompleto para edição posterior
  - Ideal para atendimentos rápidos
- **Criação Rápida de Serviços**: Crie novos serviços diretamente na tela de edição de OS
- **Edição de Preço de Serviço**: Altere o preço de serviços dentro da OS sem afetar o preço original
- **Personalização por Cliente**: Ajuste valores de serviços conforme necessário
- **Campo Detalhes em Serviços**: 
  - Adicione detalhes específicos para cada serviço na OS
  - Detalhes são exibidos na impressão da OS
  - Permite personalização de informações por serviço

### Comandos de terminal

Para listar todos os comandos de terminal disponíveis, basta executar o comando `php index.php tools` a partir da raiz do projeto, após feita todo o processo de instalação.

### Hospedagem Parceira
Em parceria com o Projeto Map-OS a SysGO oferece hospedagem de qualidade e suporte personalizado para usuários dos Map-OS com custo justo e confiabilidade.
Solicite sua hospedagem agora [Clique Aqui!](https://sysgo.com.br/mapos)

<p><img src="https://sysgo.com.br/img-externo/mapos-github.jpg" alt="SysGO - MAP-OS Cloud Hosting" style="width:50%;"></p>

### Frameworks/Bibliotecas
* [bcit-ci/CodeIgniter](https://github.com/bcit-ci/CodeIgniter)
* [twbs/bootstrap](https://github.com/twbs/bootstrap)
* [jquery/jquery](https://github.com/jquery/jquery)
* [jquery/jquery-ui](https://github.com/jquery/jquery-ui)
* [mpdf/mpdf](https://github.com/mpdf/mpdf)
* [Matrix Admin](http://wrappixel.com/demos/free-admin-templates/matrix-admin/index.html)
* [filp/whoops](https://github.com/filp/whoops)

### Requerimentos
* PHP >= 8.3
* MySQL >= 5.7 ou >= 8.0
* Composer >= 2

### Doações
Gosta do mapos e gostaria de contribuir com seu desenvolvimento?

Doações podem ser realizadas nos links:
* [catarse/mapos](https://www.catarse.me/mapos) - Mensal
* [kofi/mapos](https://ko-fi.com/mapos) - Exporádica

### Estrelas
[![Estrelas](https://api.star-history.com/svg?repos=RamonSilva20/mapos&type=Date)](https://star-history.com/#RamonSilva20/mapos&Date)

### Contribuidores
[![Contribuidores](https://contrib.rocks/image?repo=RamonSilva20/mapos)](https://github.com/RamonSilva20/mapos/graphs/contributors)

## Autor
| [<img src="https://avatars.githubusercontent.com/RamonSilva20?s=115"><br><sub>Ramon Silva</sub>](https://github.com/RamonSilva20) |
| :---: |
