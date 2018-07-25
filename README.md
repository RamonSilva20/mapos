
![MapOS](https://raw.githubusercontent.com/RamonSilva20/mapos/master/assets/img/logo.png)

![version](https://img.shields.io/badge/version-3.1.11-blue.svg?longCache=true&style=flat-square)
![license](https://img.shields.io/badge/license-MIT-green.svg?longCache=true&style=flat-square)
![theme](https://img.shields.io/badge/theme-Matrix--Admin-lightgrey.svg?longCache=true&style=flat-square)
![issues](https://img.shields.io/github/issues/RamonSilva20/mapos.svg?longCache=true&style=flat-square)
![contributors](https://img.shields.io/github/contributors/RamonSilva20/mapos.svg?longCache=true&style=flat-square)

#### _Versão 3.1.11_ | [Nova versão em desenvolvimento](https://github.com/RamonSilva20/mapos/tree/mapos4)

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

### Atualização para versão 3.*
1. Faça backup do banco de dados.
2. Remova a pasta system da instalação atual.
3. Copie os novos arquivos e substitua.
4. Execute o script update_v2_to_v3 para atualizar o banco de dados (Nenhuma informação será perdida).
5. Acesse o sistema com o usuário administrador utilizando a senha `123456`.
6. Será preciso alterar as senhas dos usuários pois o sistema na versão 3 utiliza um novo padrão de criptografia.


### Frameworks/Bibliotecas
* [bcit-ci/CodeIgniter](https://github.com/bcit-ci/CodeIgniter)
* [twbs/bootstrap](https://github.com/twbs/bootstrap) 
* [jquery/jquery](https://github.com/jquery/jquery) 
* [jquery/jquery-ui](https://github.com/jquery/jquery-ui) 
* [mpdf/mpdf](https://github.com/mpdf/mpdf) 
* [Matrix Admin](http://wrappixel.com/demos/free-admin-templates/matrix-admin/index.html)

### Requerimentos
* PHP >= 5.4.0
* MySQL

### Contribuidores
* [Ramon Silva](https://github.com/RamonSilva20) - Criador
* [Gianluca Bine](https://github.com/Pr3d4dor)
* [Henrique Miranda](https://github.com/Henrique-Miranda)
* [Mário Lucas](https://github.com/mariolucasdev)
* [Helan Allysson](https://github.com/HelanAllysson)
* [KansasMyers](https://github.com/KansasMyers)
* [Daniel Bastos](https://github.com/daniellbastos)
* [drelldeveloper](https://github.com/drelldeveloper) 
* [Samuel Fontebasso](https://github.com/fontebasso)
* [marllonferreira](https://github.com/marllonferreira)
