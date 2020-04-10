# Changelog
Todas as alterações serão documentadas neste arquivo

Formato baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.4.0] - 2020-05-10

### Added
- Implementado atualizador automático via GitHub. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.3] - 2020-04-05

### Added
- Implementado docker e docker-compose. [@Pr3d4dor](https://github.com/Pr3d4dor)

### Changed
- Removido verificação de timezone em instalação. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.2.2] - 2020-03-28

### Fixed
- Otimizado e flexibilizado relatório financeiro. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Implementado função dump and die para faciltar desenvolvimento. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Adicionado dependência de desenvolvimento dumper de Symfony. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.2.1] - 2020-03-28

### Fixed
- Reformatação do código utilizando linter PSR-2. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.2.0] - 2020-03-28

### Added
- Implementado gestor de erros Whoops, que exibe páginas de erros formatadas. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Atualizado bibliotecas do composer. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.1.2] - 2020-03-26

### Fixed

- Corrigido problema em cálculo exibição de data de vencimento da garantia de OS finalizada. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Atualizado bibliotecas do composer. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.1.1] - 2020-03-22

### Fixed
- Corrigido email de cliente no lugar de vendedor. [@RamonSilva20](https://github.com/RamonSilva20)
- Corrigido problema na tela de listagem de OS com garantia não numérica. [@RamonSilva20](https://github.com/RamonSilva20)

## [4.1.0] - 2020-03-04

### Fixed
- Corrigido layout e links de anexos de OS. [@RamonSilva20](https://github.com/RamonSilva20)
- Corrigido problema no layout de etiquetas. [@RamonSilva20](https://github.com/RamonSilva20)
- Corrigido layout e problema ao salvar pagamento. [@RamonSilva20](https://github.com/RamonSilva20)

## Changed
- Anexos de OS estruturados dentro de pastas MM-AAAA/OS-#ID. [@RamonSilva20](https://github.com/RamonSilva20)

## [4.0.0] - 2020-03-03

### Added
- Adicionado o recibo para impressora não fiscal em OS e Vendas. [@willph](https://github.com/willph)
- Adicionado opção de pagamento com Mercado Pago. [@willph](https://github.com/willph)
- Adicionado informação de vencimento de prazo de garantia. [@willph](https://github.com/willph)
- Adicionado composer ao projeto. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Adicionado biblioteca de migrations do CodeIgniter. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Implementado seeders para inserção de dados no banco de dados.
- Botão para atualizar banco de dados com as migrations em: Configurações -> Sistema. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Adicionado controller `Tools` para permitir a criação de migrations e seeders pelo terminal. [@Pr3d4dor](https://github.com/Pr3d4dor)

### Fixed
- Adicionado permissão de sistema e email ao usuário admin criado inicialmente. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Corrigido valor de `dataCadastro` incorreta em admin criado inicialmente [@Pr3d4dor](https://github.com/Pr3d4dor)
- Corrigido erro no relatório de clientes com datas não preenchidas. [@Pr3d4dor](https://github.com/Pr3d4dor)

### Changed
- Banco de dados agora será gerenciado com migrations pela biblioteca do CodeIgniter, onde o último arquivo de atualização manual é o `update_3.15.0_to_4.0.0.sql`. Ainda será necessário atualizar sempre o arquivo `banco.sql` com as novas tabelas para que o script de instalação continue funcionando [@Pr3d4dor](https://github.com/Pr3d4dor)
- Ativado Logs para permitir melhor debug. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [3.15.0] - 2020-02-15

### Added
- Adicionado opções de configuração do sistema. [@RamonSilva20](https://github.com/RamonSilva20)
- Adicionado status faturado no filtro de relatório de OS. [@ZanzouShio](https://github.com/ZanzouShio)
- Adicionado opção de atualização de estoque pelo listagem e dashboard. [@TiagoOliveira](https://github.com/trollfalgar)

### Fixed
- Corrigido arquivo de configurações. [@RamonSilva20](https://github.com/RamonSilva20)
- Correção de bug na área do cliente ao efetuar login. [@GiovanneOliveira](https://github.com/giovanne-oliveira)
- Correção de exibição de produtos com estoque mínimo. [@MikeAlves](https://github.com/mikxingu)

### Changed
- Atualização da versão do Codeigniter para 3.1.11. [@RamonSilva20](https://github.com/RamonSilva20)
- Refatoramento de controllers para diminuir repetição de código. [@RamonSilva20](https://github.com/RamonSilva20)
- Simplificado exibição de mensagem de acesso expirado. [@RamonSilva20](https://github.com/RamonSilva20)
- Remoção de data e hora no menu superior. [@RamonSilva20](https://github.com/RamonSilva20)
- Nome do app dinâmico na área do cliente. [@TiagoOliveira](https://github.com/trollfalgar)

## [3.14.2] - 2019-12-18

### Fixed
- Corrigido data por extenso no topo do tema. [@ZanzouShio](https://github.com/ZanzouShio)
- Corrigido icons que não apareciam. [@willph](https://github.com/willph)
- Corrigido erro no botão de envio por WhatsApp. [@willph](https://github.com/willph)
- Corrigido erro de comentário no arquivo config. [@willph](https://github.com/willph)

### Changed
- Refatoramento de listagens de cadastros. [@RamonSilva20](https://github.com/RamonSilva20)

## [3.14.1] - 2019-12-02

### Fixed
- Corrigido erro ao exibir logo na visualização de OS. [@willph](https://github.com/willph)
- Corrigido icon de pesquisar na tela de OS. [@willph](https://github.com/willph)
- Corrigido problema ao imprimir etiquetas. [@willph](https://github.com/willph)
- Corrigido problema de return-path ao utilizar SMTP. [@RamonSilva20](https://github.com/RamonSilva20)
- Corrigido erro no link de WhatsApp ao não ter cadastro de emitente. [@RamonSilva20](https://github.com/RamonSilva20)

## [3.14.0] - 2019-11-22

### Changed
- Adicionado impressão de etiquetas nos padrões: EAN13, UPCA, CODE 93, CODE 128, CODABAR, QR-CODE. [@willph](https://github.com/willph) e [@Marco](https://github.com/marcotuliomtb)
- Adicionado campo de cadastro de código de barra. [@willph](https://github.com/willph)
- Adicionado teclas de atalho. [@willph](https://github.com/willph)
- Adicionado botão de logout no menu lateral. [@mvnp](https://github.com/mvnp)

### Changed
- Atualização da versão do mpdf para 6.1.4. [@willph](https://github.com/willph)
- Visualização e impressão de saldo total das OS independente do status. [@bulfaitelo](https://github.com/bulfaitelo)


## [3.12.0] - 2019-11-06

### Changed
- Organização do Menu. [@willph](https://github.com/willph)
- Remoção de arquivo desnecessário. [@willph](https://github.com/willph)

## [3.11.0] - 2019-10-22

### Added
- Adicionado alerta de cadastro com sucesso de cliente. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [3.10.0] - 2019-10-08

### Added
- Adicionado anotações na OS. [@RamonSilva20](https://github.com/RamonSilva20)

## [3.9.0] - 2019-10-05

### Added
- Adicionado envio de email de OS automaticamente na criação e edição (email é enviado para o cliente da OS,  para o emitente e para o técnico da OS). [@Pr3d4dor](https://github.com/Pr3d4dor)
- Adicionado novas opções (M² e Outros) no select de unidade na criação e edição de produto. [@Pr3d4dor](https://github.com/Pr3d4dor)

### Changed
- Alterado alert padrão para sweet alert. [@David Vilaça](https://github.com/davidpvilaca)

### Fixed
- Corrigido bug de máscara CPF/CNPJ. [will.phelipe@gmail.com](https://github.com/willph).
- Corrigido arquivo de update de data de expiração com data futura. [@RamonSilva20](https://github.com/RamonSilva20)

## [3.8.0] - 2019-07-25

### Added
- Adicionada pesquisa por múltiplos status em OS. [@RamonSilva20](https://github.com/RamonSilva20)
- Ao adicionar OS e Vendas usuário logado preenchido por padrão como responsável. [@RamonSilva20](https://github.com/RamonSilva20)

### Fixed
- Correção tabela e botões OS. [@bulfaitelo](https://github.com/bulfaitelo)
- Corrigido a tabela para torna-la responsiva, e os botões que não funcionavam em dispositivos móveis. [@bulfaitelo](https://github.com/bulfaitelo)

## [3.7.0] - 2019-07-08

### Added
- Adicionada funcionalidade de envio de OS por email. [@RamonSilva20](https://github.com/RamonSilva20)

## [3.6.0] - 2019-06-29

### Added
- Adicionada verificação de permissão para exibição de estatísticas no painel. [@RamonSilva20](https://github.com/RamonSilva20)
- Adicionada possibilidade de alteração de preços de produtos e serviços em OS. [@RamonSilva20](https://github.com/RamonSilva20)
- Adicionada possibilidade de adicionar quantidade de serviços em OS. [@RamonSilva20](https://github.com/RamonSilva20)
- Adicionada possibilidade de alterar preços de produtos em Vendas. [@RamonSilva20](https://github.com/RamonSilva20)

### Changed
- Modificados alguns elementos de estilização. [@RamonSilva20](https://github.com/RamonSilva20)

## [3.5.3] - 2019-05-18

### Fixed
- Corrigido remoção de garantias ao editar OS. [@RamonSilva20](https://github.com/RamonSilva20) e [@willph](https://github.com/willph)
- Corrigido erro no arquivo matrix.js e funcoes.js. [will.phelipe@gmail.com](https://github.com/willph).

### Changed
- Alterado exibição de mensagem para esconder depois de 2.5 segundos. [will.phelipe@gmail.com](https://github.com/willph)

## [3.5.2] - 2019-05-14

### Fixed
- Corrigido pesquisa de termos de garantia. [@RamonSilva20](https://github.com/RamonSilva20).
- Corrigido erro no cadastro de garantias ao buscar nome do usuário logado. [@RamonSilva20](https://github.com/RamonSilva20).

### Changed
- Removendo textos desnecessários [will.phelipe@gmail.com](https://github.com/willph)

## [3.5.1] - 2019-05-13

### Fixed
- Removendo obrigatoriedade do campo de garantias na tabela de OS para evitar erro ao não preencher no cadastro. [@RamonSilva20](https://github.com/RamonSilva20).

### Changed
- Alterado insert de usuário com permissão para o módulo de auditoria no arquivo sql. [@RamonSilva20](https://github.com/RamonSilva20)

## [3.5.0] - 2019-05-12

### Added
- Adicionado modulo de auditoria para monitoramento de atividades no sistema. [@RamonSilva20](https://github.com/RamonSilva20).

## [3.4.0] - 2019-05-10
Por: Wilmerson Felipe[will.phelipe@gmail.com](https://github.com/willph)

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
Por: Fábio Barbosa[fabiobarbosa@gmx.com](https://github.com/aportetecnologia)

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

- Adicionado máscaras 'CPF, CNPJ, TELEFONE, CEP, CELULAR, RG. [Thomas Henrique Lage Macedo](https://github.com/aportetecnologia) [lage.thomas@gmail.com]
- Adicionado data de expiração de acesso. [Thomas Henrique Lage Macedo](https://github.com/aportetecnologia) [lage.thomas@gmail.com]
Adicionado o campo valorTotal dentro do $this->data['results']. [Fábio Barbosa](https://github.com/aportetecnologia) - [fabiobarbosa@gmx.com]
- Adicionado a Funcao para incluir na os a opcao "Aguardando Pecas". [Fábio Barbosa](https://github.com/aportetecnologia) - [fabiobarbosa@gmx.com]
- Adicionado o campo Valor Total para aparecer o valor total da os quando estiver fechado. [Fábio Barbosa](https://github.com/aportetecnologia) - [fabiobarbosa@gmx.com]
- Adicionado a div Ordens de Servicos Aguardando Pecas baseado no status da os. [Fábio Barbosa](https://github.com/aportetecnologia) - [fabiobarbosa@gmx.com]

### Changed
- Alterado layout da tela de login de clientes. [Thomas Henrique Lage Macedo](https://github.com/aportetecnologia) [lage.thomas@gmail.com]
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
- Adicionado e-mail do cliente na impressão da OS. [@RamonSilva20](https://github.com/RamonSilva20).

### Fixed
- Corrigido alteração de senha na tela minha conta [@RamonSilva20](https://github.com/RamonSilva20).
- Corrigido link no formulário de adicionar arquivo. [@RamonSilva20](https://github.com/RamonSilva20).

## [3.1.15] - 2018-11-24
### Fixed
- Corrigido campo descrição na impressão e visualização de OS [@RamonSilva20](https://github.com/RamonSilva20).

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
