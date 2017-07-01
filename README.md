![MapOS](https://raw.githubusercontent.com/RamonSilva20/mapos/master/assets/img/logo.png)

#### _Versão 3.0_ | Nova versão em desenvolvimento - Sugestões no Issue #37

MapOS é um sistema gratuito para de controle de ordens de serviço. 
Para mais informações visite __[sistemamapos.esy.es](https://www.sistemamapos.esy.es)__ 
ou acesse a __[demo](https://www.sistemamapos.esy.es/mapos)__.  

### Instalação

1. Faça o download dos arquivos
2. Extraia o pacote e copie para seu webserver.
3. Configure sua URL no arquivo `config.php` alterando a base_url. 
4. Crie o banco de dados e execute o arquivo `banco.sql` para criar as tabelas.
5. Configure os dados de acesso ao banco de dados no arquivo `database.php`.
6. Acesse sua URL e coloque os dados de acesso: `admin@admin.com` e `123456`.

### Atualização para versão 3.1

1. Substitua a todos os arquivos.
2. Execute o script update_v2_to_v3 para atualizar o banco de dados (Nenhuma informação será perdida).
3. Acesse o sistema com o usuário administrador utilizando a senha `123456`.
4. Será preciso alterar as senhas dos usuários pois o sistema na versão 3 utiliza um novo padrão de criptografia.


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
