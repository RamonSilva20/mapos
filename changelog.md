# Changelog
Todas as alterações serão documentadas neste arquivo

Formato baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.3.1] - 2019-03-03
Por: Fábio Barbosa[fabiobarbosa@gmx.com][https://github.com/aportetecnologia]
### Added
- Adicionado a obrigatoriedade de data final na OS
    - Adicionar OS
    - Editar OS

### Fixed
- Corrigido o calendario que ficava pro detras do modal no pagamento da os no fechamento da mesma.

## [3.3] - 2019-02-28
### Added
Por: Thomas Henrique Lage Macedo [lage.thomas@gmail.com]
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

Por: Fábio Barbosa[fabiobarbosa@gmx.com][https://github.com/aportetecnologia]
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


## [3.2] - 2019-02-18
### Added
- Implementado editor WYSIWYG [Trumbowyg](https://github.com/Alex-D/Trumbowyg) nos campos (Ordem de Serviço)
    - Descrição Produto/Serviço
    - Defeito
    - Observações
    - Laudo Técnico

## [3.1.16] - 2019-01-14
### Added
- Adicionando e-mail do cliente na impressão da OS. [@RamonSilva20](https://github.com/RamonSilva20).

### Fixed
- Corrigindo alteração de senha na tela minha conta [@RamonSilva20](https://github.com/RamonSilva20).
- Corrigindo link no formulário de adicionar arquivo. [@RamonSilva20](https://github.com/RamonSilva20).

## [3.1.15] - 2018-11-24
### Fixed
- Corrigindo campo descrição na impressão e visualização de OS [@RamonSilva20](https://github.com/RamonSilva20).

## [3.1.14] - 2018-11-13
### Added
- Modelo de impressão de OS otimizando espaços [@RamonSilva20](https://github.com/RamonSilva20).
- Refatoração de views [@mariolucasdev](https://github.com/mariolucasdev).

## [3.1.13] - 2018-10-04
### Added
- Assistente de instalação [@rodrigo3d](https://github.com/rodrigo3d).
- Arquivo de changelog [@RamonSilva20](https://github.com/RamonSilva20).


## [3.1.12] - 2018-08-14
### Added
- Valor total no relatório de OS [@RamonSilva20](https://github.com/RamonSilva20).
- Status alterado automaticamente para faturado [@Pr3d4dor](https://github.com/Pr3d4dor).
- Exibir o nome do anexo na embaixo do thumbnail em anexos de OS [@Pr3d4dor](https://github.com/Pr3d4dor).

### Changed 
- Atualização de biblioteca mPDF para versão 6.1 [@Pr3d4dor](https://github.com/Pr3d4dor).

### Fixed 
- Correção de erro ao gerar relatório sem emitente estar configurado [@RamonSilva20](https://github.com/RamonSilva20).
