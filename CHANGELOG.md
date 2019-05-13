# Changelog
Todas as alterações serão documentadas neste arquivo

Formato baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [3.5.1] - 2019-05-13

### Fixed
- Removendo obrigatoriedade do campo de garantias na tabela de OS para evitar erro ao não preencher no cadastro. [@RamonSilva20](https://github.com/RamonSilva20).

### Changed
- Alterando insert de usuário com permissão para o módulo de auditoria no arquivo sql. [@RamonSilva20](https://github.com/RamonSilva20)

## [3.5.0] - 2019-05-12

### Added
- Adicionando modulo de auditoria para monitoramento de atividades no sistema. [@RamonSilva20](https://github.com/RamonSilva20).

## [3.4.0] - 2019-05-10
Por: Wilmerson Felipe[will.phelipe@gmail.com][https://github.com/willph]

### Fixed
- Corrigido exibição do Telefone em VizualizarOS e ImprimirOS

### Changed
- Padronização das Páginas para que todas tenha a palavra "Ações" referente aos botões vizualizar, adicionar, editar, excluir.
- Adicionado campo Termo garantia em adicionarOS, editarOS referenciando ao termo garantia cadastrado.
- Alterado Telefone para Celular do Cliente.

### Added
- Termo de Garantia referente ao request feature #253


## [3.3.2] - 2019-04-13
### Fixed
- Corrigido exibição da label de status faturado em painel de visualização de cliente [@Pr3d4dor](https://github.com/Pr3d4dor).

## [3.3.1] - 2019-03-03
Por: Fábio Barbosa[fabiobarbosa@gmx.com][https://github.com/aportetecnologia]

### Changed 
- Modificado a cor de fundo para branco do box de texto do trumbowyg
- Modificado a disposicao dos campos trumbowyg para evitar rolagem prolongada da tela desnecessáriamente.
- Reestilizado a tela de login para uma tela transparente e fundo dinamico
- Obrigatoriedade de data final na OS
    - Adicionar OS
    - Editar OS

### Fixed
- Corrigido o calendario que ficava pro detras do modal no pagamento da os no fechamento da mesma.
## [3.3] - 2019-02-28
### Added

- Adicionando máscaras 'CPF, CNPJ, TELEFONE, CEP, CELULAR, RG. [Thomas Henrique Lage Macedo](https://github.com/aportetecnologia) [lage.thomas@gmail.com]
- Adicionando data de expiração de acesso. [Thomas Henrique Lage Macedo](https://github.com/aportetecnologia) [lage.thomas@gmail.com]
Adicionado o campo valorTotal dentro do $this->data['results']. [Fábio Barbosa](https://github.com/aportetecnologia) - [fabiobarbosa@gmx.com]
- Adicionado a Funcao para incluir na os a opcao "Aguardando Pecas". [Fábio Barbosa](https://github.com/aportetecnologia) - [fabiobarbosa@gmx.com]
- Adicionado o campo Valor Total para aparecer o valor total da os quando estiver fechado. [Fábio Barbosa](https://github.com/aportetecnologia) - [fabiobarbosa@gmx.com]
- Adicionado a div Ordens de Servicos Aguardando Pecas baseado no status da os. [Fábio Barbosa](https://github.com/aportetecnologia) - [fabiobarbosa@gmx.com]

### Changed 
- Alterando layout da tela de login de clientes. [Thomas Henrique Lage Macedo](https://github.com/aportetecnologia) [lage.thomas@gmail.com]
- Formatado o campo Valor total com 2 casas decimais e R$ (cifrao) - [Fábio Barbosa](https://github.com/aportetecnologia) - [fabiobarbosa@gmx.com]

## [3.2] - 2019-02-18
### Added
- Implementado editor WYSIWYG [Trumbowyg](https://github.com/Alex-D/Trumbowyg) nos campos (Ordem de Serviço). [@Pr3d4dor](https://github.com/Pr3d4dor).
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
