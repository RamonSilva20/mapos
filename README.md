![MapOS](https://raw.githubusercontent.com/RamonSilva20/mapos/master/assets/img/logo.png)

#### _Versão 4.0_ | Versão em desenvolvimento - Sugestões no Issue #37

MapOS é um sistema gratuito para de controle de ordens de serviço. 
Para mais informações visite __[sistemamapos.esy.es](https://www.sistemamapos.esy.es)__ 
ou acesse a __[demo](https://www.sistemamapos.esy.es/mapos)__.  

### Instalação

1. Faça o download dos arquivos.
2. Extraia o pacote e copie para seu webserver.
3. Configure sua URL no arquivo `config.php` alterando a base_url. 
4. Crie o banco de dados e execute o arquivo `banco.sql` para criar as tabelas.
5. Configure os dados de acesso ao banco de dados no arquivo `database.php`.
6. Acesse sua URL e coloque os dados de acesso: `admin@admin.com` e `123456`.

### Roadmap
 - ~~Atualizar framework~~ 
 - Atualizar o tema para Bootstrap 4
 - Refatorar código com melhor padronização
 - Adicionar Notificações por e-mail 
 - Adicionar Rich Text Editor nos campos do tipo TEXTAREA
 - Adicionar Desconto e Parcelamento na OS e Venda
 - Adicionar quantidade em serviços no cadastro de OS
 - Adicionar Cadastro de Equipamentos e opção de vincular à OS
 - Adicionar Layout de impressão para impressora não fiscal
 - Adicionar Layout de impressão em meia folha
 - Adicionar Cadastro de Status da OS
 - Gerar guia de recebimento
 - Adicionar Tarefas à OS
 - Adicionar Novos Relatórios e Gráficos
 - Elaborar manual de uso do sistema

### Frameworks/Bibliotecas
* [bcit-ci/CodeIgniter](https://github.com/bcit-ci/CodeIgniter)
* [twbs/bootstrap](https://github.com/twbs/bootstrap) 
* [jquery/jquery](https://github.com/jquery/jquery) 
* [jquery/jquery-ui](https://github.com/jquery/jquery-ui) 

### Requerimento
* PHP >= 5.4.0
* MySQL

### Créditos
* Ramon Silva - silva018-mg@yahoo.com.br
