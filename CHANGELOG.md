# Changelog
Todas as alterações serão documentadas neste arquivo

Formato baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
