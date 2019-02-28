
![MapOS](https://raw.githubusercontent.com/RamonSilva20/mapos/master/assets/img/logo.png)

![version](https://img.shields.io/badge/version-3.2-blue.svg?longCache=true&style=flat-square)
![license](https://img.shields.io/badge/license-MIT-green.svg?longCache=true&style=flat-square)
![theme](https://img.shields.io/badge/theme-Matrix--Admin-lightgrey.svg?longCache=true&style=flat-square)
![issues](https://img.shields.io/github/issues/RamonSilva20/mapos.svg?longCache=true&style=flat-square)
![contributors](https://img.shields.io/github/contributors/RamonSilva20/mapos.svg?longCache=true&style=flat-square)

#### _Versão 3.3_ | 
#### Modificações
-- MASCARAS 'CPF, CNPJ, TELEFONE, CEP, CELULAR, RG --

mapos/assets/js/funcoes.js -->
mapos/assets/js/jquery-3.3.1.min.js -->
mapos/assets/jquery.mask.min.js -->

#Aplicar as IDs nos campos correspondentes para que a mascara funcione#

-- CONTROLLER --

mapos/index.php --> (59)define('ENVIRONMENT', 'production');
mapos/application/controller/mapos.php --> (21 - 33)
mapos/application/controller/mapos.php --> (143)'expirado'=>$this->chk_date($user->valida)
mapos/application/controller/mapos.php --> (159- 166)

mapos/application/controller/usuarios.php --> (99)'valida' => set_value('valida'),
mapos/application/controller/usuarios.php --> (173) 'valida' => $this->input->post('valida'),
mapos/application/controller/usuarios.php --> (191) 'valida' => $this->input->post('valida'),

-- VIEWER --

.:: USUARIOS - ADICIONAR ::.

mapos/application/viewer/usuarios/adicionarUsuario.php --> (1 - 15)
mapos/application/viewer/usuarios/adicionarUsuario.php --> (114)

.:: USUARIOS - EDITAR ::.

mapos/application/viewer/usuarios/editarUsuario.php --> (1-16)
mapos/application/viewer/usuarios/editarUsuario.php --> (116)
mapos/application/viewer/usuarios/editarUsuario.php --> (186-199)

.:: USUARIOS - LISTAR ::.

mapos/application/views/usuarios/usuarios.php --> (24) <th> Validade </th>
mapos/application/views/usuarios/usuarios.php --> (60) <th> Validade </th>
mapos/application/views/usuarios/usuarios.php --> (73) echo '<td>'.$r->valida.'</td>';

.:: CLIENTES - ADICIONAR ::.

mapos/application/views/clientes/adicionarCliente.php --> (1-16) 

.:: CLIENTES - EDITAR ::.
mapos/application/views/clientes/editarCliente.php --> (1-17)

.:: CLIENTES ::.
mapos/application/views/clientes/clientes.php --> (25-61-72)

.:: CONECTE ::.

mapos/application/views/conecte/login.php --> (14-16; 18-29)

.:: MAPOS ::.


mapos/application/views/mapos/login.php

.:: OS ::.

mapos/application/views/os/imprimirOs.php --> (151)
mapos/application/views/os/imprimirOs.php --> (164)

mapos/application/views/os/visualizarOs.php --> (145; 158)

.:: Controllers ::.
mapos/application/controllers/Os.php
Linha 86
Adicionado o campo valorTotal dentro do $this->data['results']


.:: Moddels ::.
mapos/application/models/Mapos_model.php 
Linha 132
Aidicionado a Funcao para incluir na os a opcao "Aguardando Pecas"

.:: Views ::.

mapos/application/os/views/os.php
Linha 24
Adicionado ao option "Aguardando Pecas"

Linhas 61 e 97
Adicionado o campo Valor Total para aparecer o valor total da os quando estiver fechado

Linha 140
Formatado o campo Valor total com 2 casas decimais e R$ (cifrao)

mapos/application/os/editarOs.php
Linha 71
Adicionado a option "Aguardando Pecas"

mapos/application/mapos/painel.php
Linhas 149 a 192
Adicionado a div Ordens de Servicos Aguardando Pecas baseado no status da os

MapOS é um sistema gratuito para de controle de ordens de serviço. 

### Instalação

1. Faça o download dos arquivos.
2. Extraia o pacote e copie para seu webserver.
3. Acesse sua URL e inicie a instalação, é bem simples, basta preencher as informações no assistente de instalação **MAPOS**.

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
* PHP >= 5.5.0
* MySQL

### Contribuidores
| [<img src="https://avatars.githubusercontent.com/Pr3d4dor?s=115"><br><sub>Gianluca Bine</sub>](https://github.com/Pr3d4dor) | [<img src="https://avatars.githubusercontent.com/Henrique-Miranda?s=115"><br><sub>Henrique Miranda</sub>](https://github.com/Henrique-Miranda) | [<img src="https://avatars.githubusercontent.com/mariolucasdev?s=115"><br><sub>Mário Lucas</sub>](https://github.com/mariolucasdev) | [<img src="https://avatars.githubusercontent.com/HelanAllysson?s=115"><br><sub>Helan Allysson</sub>](https://github.com/HelanAllysson) | [<img src="https://avatars.githubusercontent.com/KansasMyers?s=115"><br><sub>KansasMyers</sub>](https://github.com/KansasMyers)
|:-:|:-:|:-:|:-:|:-:|
| [<img src="https://avatars.githubusercontent.com/daniellbastos?s=115"><br><sub>Daniel Bastos</sub>](https://github.com/daniellbastos) | [<img src="https://avatars.githubusercontent.com/github?s=115"><br><sub>drelldeveloper</sub>](https://github.com/drelldeveloper) | [<img src="https://avatars.githubusercontent.com/fontebasso?s=115"><br><sub>Samuel Fontebasso</sub>](https://github.com/fontebasso) | [<img src="https://avatars.githubusercontent.com/marllonferreira?s=115"><br><sub>marllonferreira</sub>](https://github.com/marllonferreira) | [<img src="https://avatars.githubusercontent.com/rodrigo3d?s=115"><br><sub>Rodrigo Ribeiro</sub>](https://github.com/rodrigo3d) 

## Autor
| [<img src="https://avatars.githubusercontent.com/RamonSilva20?s=115"><br><sub>Ramon Silva</sub>](https://github.com/RamonSilva20) |
| :---: |
