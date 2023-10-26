# Changelog
Todas as alterações serão documentadas neste arquivo

Formato baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.42.0] - 2023-10-25
### Fixed
- Erro na aba OS. [@barretowiisk](https://github.com/barretowiisk)
- Correção nos lançamentos financeiros. [@barretowiisk](https://github.com/barretowiisk)
- Correção no formato de datas no financeiro. [@barretowiisk](https://github.com/barretowiisk)
- Correção em Clientes. [@lukasabino](https://github.com/lukasabino)
- Sobreposição mini calendário ao adicionar/editar venda. [@barretowiisk](https://github.com/barretowiisk)
- Correção no instalador do Windows. [@barretowiisk](https://github.com/barretowiisk)
- Correção em fechamento de if. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Correção do ícone de limpar na área de relatório. [@WilliamCamargo](https://github.com/WillianCamargo01)
- Correção do mini calendário que ao clicar na data para faturar uma Os ou Venda ele fica atrás do modal. [@WilliamCamargo](https://github.com/WillianCamargo01)
- Correção da instalação via docker. [@thdev-matheus](https://github.com/thdev-matheus)
- Correção em orçamento de venda. [@barretowiisk](https://github.com/barretowiisk)
- Correção em lançamento parcelado. [@lukasabino](https://github.com/lukasabino)
- Correção em cabeçalho ao faturar OS. [@barretowiisk](https://github.com/barretowiisk)
- Correção em modal de desconto de OS do calendário no dashboard. [@barretowiisk](https://github.com/barretowiisk)
- Correção de desconto em calendário. [@Wilmerson](https://github.com/willph)
- Correção em modal de editar lançamento financeiro. [@Wilmerson](https://github.com/willph)

### Added
- Auto Instalador Map-OS Ubuntu/Debian. [@barretowiisk](https://github.com/barretowiisk)

## [4.41.0] - 2023-09-16
### Fixed
- Sobreposição mini calendário ao adicionar/editar venda. [@barretowiisk](https://github.com/barretowiisk)
- Erro no upload na OS (aba Anexos). [@barretowiisk](https://github.com/barretowiisk)
- Correção para aparecer o ID da OS que o usuario adicionou em logs. [@Fesantt](https://github.com/Fesantt)
- Remoção de linhas na impressão de Vendas. [@lukasabino](https://github.com/lukasabino)
- Variável em visualizar OS na área do cliente. [@Wilmerson](https://github.com/willph)
- Variável emitente e cliente quando vazia ao resetar senha. [@Wilmerson](https://github.com/willph)
- Acessar propriedade inválida em usuário. [@Wilmerson](https://github.com/willph)

### Added
- Impressão de orçamento de venda. [@barretowiisk](https://github.com/barretowiisk)
- Aba de vendas em clientes. [@lukasabino](https://github.com/lukasabino)
- Vincula cliente/fornecedor a lançamento parcelado. [@lukasabino](https://github.com/lukasabino)

## [4.40.0] - 2023-05-14
### Fixed

- Corrigido CP não fiscal. [@Wilmerson](https://github.com/willph)
- Corrigido dashboard de estatística. [@Wilmerson](https://github.com/willph)
- Corrigido impressão no windows. [@Wilmerson](https://github.com/willph)
- Corrigido layout. [@MilsonElias](https://github.com/MilsonElias)
- Corrigido bugs de editor de texto em OS e Vendas. [@MilsonElias](https://github.com/MilsonElias)
- Corrigido valor total em layout. [@MilsonElias](https://github.com/MilsonElias)
- Corrigido botão voltar em editor de texto. [@MilsonElias](https://github.com/MilsonElias)
- Corrigido nome de usuário em log de auditoria. [@HenriqueMiranda](https://github.com/Henrique-Miranda)
- Corrigido relatórios financeiros. [@Wilmerson](https://github.com/willph)
- Corrigido problema de fullscreen e whatsapp. [@WilliamCamargo](https://github.com/WillianCamargo01)

### Added

- Implementado desconto por porcentagem e valor. [@Wilmerson](https://github.com/willph)
- Implementado opção de imprimir duas vias. [@Wilmerson](https://github.com/willph)
- Suporte ao PHP 8 pra cima. [@Wilmerson](https://github.com/willph)

## [4.39.0] - 2022-10-28
### Fixed

- Correções em editarVenda.php corrigido erro para permitir adicionar desconto em venda com valor superior a mil reais, erro de mascará, consequentemente foi atualizado imprimirVenda.php e imprimirVendaTermica.php para tratar o mesmo problema. [@Rodrigo-Paz](https://github.com/Rodrigo-Paz)
- Correções no relatorioFinanceiro.php valor total que não estava informado assim como o valor total do relatorio visto que nao puxava vendas sem desconto, foi corrigido e adicionado simbolo de % no lugar de R$ para o campo desconto. [@Rodrigo-Paz](https://github.com/Rodrigo-Paz)

### Changed

- Trocado link parta envio via Whatsapp, afim de abranger usuários do Whastapp desktop e Whatsapp mobile, já que o atual web.whatsapp.com não faz o redirecionamento para os apps citados anteriormente. Essa alteração não afeta usuários do Whatsapp Web. [@lukasabino](https://github.com/lukasabino)

## [4.38.0] - 2022-04-29

### Fixed

- Correções gerais de bugs. [@Wilmerson](https://github.com/willph)

### Added

- Desconto em OS e Vendas. [@Wilmerson](https://github.com/willph)
- Financeiro (parcelamento, desconto e melhorias). [@luizrn](https://github.com/luizrn)
- Documentação de cronjobs no Windows. [@luizrn](https://github.com/luizrn)

## [4.37.0] - 2022-03-27

### Fixed

- Correções gerais de bugs. [@Wilmerson](https://github.com/willph)
- Correções gerais de bugs de layout. [@MilsonElias](https://github.com/MilsonElias)

### Added

- Implementado login com senha e recuperação de senha em área do cliente. [@Wilmerson](https://github.com/willph)

## [4.36.2] - 2022-03-02

### Fixed

- Corrigido ícones em ações de detalhes/visualizar/atualizar cobranças. [@Wilmerson](https://github.com/willph)

## [4.36.1] - 2022-02-23

### Fixed
- Corrigido deleção de anotações e serviços da OS. [@Wilmerson](https://github.com/willph)

## [4.36.0] - 2022-02-20

### Added
- Adicionado novo layout referente a área do usuário e do cliente. Sendo o mesmo também responsivo. [@MilsonElias](https://github.com/MilsonElias)

- Adicionado novo tema claro e tema escuro referente a área do usuário. [@MilsonElias](https://github.com/MilsonElias)

- Adicionado nova logomarca. [@MilsonElias](https://github.com/MilsonElias)

- Adicionado função em os.php para facilitar a visualização da OS em garantia, sem garantia e garantia vencida. [Luccas Woiciechoski] e [@Wilmerson](https://github.com/willph)

- Adicionado função em painel.php mostrando receita dia e despesa dia. [@MilsonElias](https://github.com/MilsonElias) e [@Wilmerson](https://github.com/willph)

- Adicionado função de adicionar e alterar imagem do usuário (foto do perfil). [@MilsonElias](https://github.com/MilsonElias) e [@Wilmerson](https://github.com/willph)

## [4.35.2] - 2021-10-10

### Fixed
- Adequação para exibir mensagem de erro ao tentar faturar venda sem produtos e OS sem produtos e/ou serviços. [@visaotec](https://github.com/visaotec)

## [4.35.1] - 2021-10-02

### Fixed
- Correção de bug que faturava OS/Venda incorretamente com valores superiores a R$ 1000,00. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Corrigido configuração de controle de edição de OS. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.35.0] - 2021-09-04

### Added
- Integração com gateway de pagamento asaas. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Link para acessar cadastro do cliente de dentro da OS. [@tutibueno](https://github.com/tutibueno)

## [4.34.0] - 2021-07-11

### Added
- Envio de email para o cliente e técnicos após cadastro da OS pelo cliente. [@tutibueno](https://github.com/tutibueno)
- Envia de email de boas vindas quando o cliente se cadastra na área do cliente. [@tutibueno](https://github.com/tutibueno)
- Notifica o time técnico que um novo cliente se cadastrou pela área do cliente. [@tutibueno](https://github.com/tutibueno)

### Fixed
- Corrigido versão do PHP em dockerfile. [@mikxingu](https://github.com/mikxingu)

## [4.33.1] - 2021-05-24

### Fixed
- Corrigido valor incorreto em banco.sql. [@willph](https://github.com/willph)

## [4.33.0] - 2021-05-23

### Added
- Adicionado status "Aprovado" em OS. [@fwsund](https://github.com/fwsund)
- Sugestão de valor de desconto ao faturar OS/Vendar de acordo com percentual. [@hoshikawakun](https://github.com/hoshikawakun)
- Sugestão de valor do produto ao editar/adicionar produto de acordo com margem. [@hoshikawakun](https://github.com/hoshikawakun)

### Changed
- Melhorias gerais em relatórios. [@hoshikawakun](https://github.com/hoshikawakun)
- Melhora em cores de gráficos do painel. [@mikxingu](https://github.com/mikxingu)

## [4.32.2] - 2021-04-16

### Changed
- Alteradas as cores dos gráficos do painel para melhor entendimento e leitura dos gráficos e adicionado comentário nas linhas para que os usuários possam alterar as cores mais facilmente. [@mikxingu](https://github.com/mikxingu)

## [4.32.1] - 2021-03-27

### Fixed
- Adequação para usar versão de PHP 7.4 fixa em docker e correção no comando de instalação do composer em docker. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Adequação para usar submit handler para evitar que lançamentos financeiros sejam duplicados. [@bietez](https://github.com/bietez)

## [4.32.0] - 2021-03-23

### Added
- Criada uma forma configurável de como o padrão básico de exibição da listagem de OS.[@bulfaitelo](https://github.com/bulfaitelo)

### Fixed
- Corrigido problema ao gerar qr code de PIX. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Corrigido erro ao selecionar filtro faturado e nome do cliente, não vinha os dados existentes. [@willph](https://github.com/willph)
- Corrigido autocomplete de CEP do viacep. [@douglascoe](https://github.com/douglascoe)

## [4.31.1] - 2021-02-13

### Fixed
- Atualizado dependências. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.31.0] - 2021-02-11

### Added
- Implementado QR Code de PIX para pagamento de os e venda em imprimirOs e imprimirVenda. [@Pr3d4dor](https://github.com/Pr3d4dor)

### Changed
- Melhorado imprimir os e venda. [@Flexotron20](https://github.com/Flexotron20)

### Fixed
- Colocado "*" para mostrar que campos ao criar lançamento financeiro são obrigatórios. [@cleytonasa](https://github.com/cleytonasa)
- Adicionado método de pagamento "PIX" nos lugares faltantes. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.30.3] - 2021-02-07

### Fixed
- Corrigido bug onde ao ter valor de centavos o valor do boleto vinha errado. Ex: 5,50 vinha no boleto 55,00 reais. [@willph](https://github.com/willph)
- Corrido o valor que era salvo para o banco de dados que ocasionava o erro nas views cobranças e vizualizar cobrança. [@willph](https://github.com/willph)

## [4.30.2] - 2021-02-03

### Changed
- Alterado texto de grafico de Vendas para Financeiro, para adequar melhor a realidade do grafico. [@willph](https://github.com/willph)
- Update: Highcharts V. 9.0.0. [@willph](https://github.com/willph)
- Atualizado versão mínima do PHP em instalador. [@willph](https://github.com/willph)

### Fixed
- Corrigido erro onde ao clicar na lupa para pesquisar o cnpj o mesmo não preenchia o numero de forma automatica, pois faltava no input id="numero". [@willph](https://github.com/willph)
- Correção de javascript onde quebrava o codigo impedindo carregamento dos graficos do financeiro>lançamento e do calendario. [@willph](https://github.com/willph)
- Corrigido case "Negociação" onde faltava a cor e o break no codigo em os.php e Mapos.php. [@willph](https://github.com/willph)
- Colocado cor e o break no case de "Negociação" na view os.php. [@willph](https://github.com/willph)
- Adicionado o arquivo update_4.29.0_to_4.30.2.sql que estava faltando. [@seitbnao](https://github.com/seitbnao)
- Correção do botão pesquisar cnpj onde não trazia todos os dados de endereço. [@willph](https://github.com/willph)
- Corrigido cadastro (MIME). [@willph](https://github.com/willph)

## [4.30.1] - 2021-01-31

### Fixed
- Adequação para não salvar nome do cliente como maiúsculo ao editar. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.30.0] - 2021-01-31

### Added
- Adicionado opção de pagamento PIX em receitas e despesas. [@cleytonasa](https://github.com/cleytonasa)
- Adicionado o controle de edição de OS com status CANCELADO e/ou FATURADO. [@seitbnao](https://github.com/seitbnao)
- Adicionado opção de cliente fornecedor em clientes. [@seitbnao](https://github.com/seitbnao)
- Adiciona o controle de visualização de dataTables. [@seitbnao](https://github.com/seitbnao)

### Changed
- Adequação para renderizar PDF gerado de relatório MEI no navegador. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Alterado a tela de configurações do sistema para um formato com abas. [@seitbnao](https://github.com/seitbnao)
- Alterado a tela de inclusão de clientes. [@seitbnao](https://github.com/seitbnao)
- Alterado o nome cliente do Menu para Cliente / Fornecedor. [@seitbnao](https://github.com/seitbnao)
- Separado as permissões por colapse. [@seitbnao](https://github.com/seitbnao)
- Remove a obrigatoriedade dos campos em adicionar e editar um cliente, somente o nome é obrigatório no cadastro. [@seitbnao](https://github.com/seitbnao)

## [4.29.0] - 2021-01-24

### Added
- Adiciona novas opções de unidades de medidas em produtos, seguindo modelo da SEFAZ-PA. [@seitbnao](https://github.com/seitbnao)
- Adiciona BLOQUEIO em exclusão e edição de OS que esteja com status CANCELADO e/ou FATURADO. [@seitbnao](https://github.com/seitbnao)

### Fixed
- Corrigido quebra de código por Exception geradas nas APIs. [@willph](https://github.com/willph)
- Atualizado lib MPDF. [@willph](https://github.com/willph)
- Corrige uma possível falha na atualização do sistema usando API do GitHub. [@seitbnao](https://github.com/seitbnao)
- Corrige o valor da OS na notificação do whatsapp. [@seitbnao](https://github.com/seitbnao)
- Corrige a notificação do whatsapp para que a mesma não venha com tags HTML. [@seitbnao](https://github.com/seitbnao)
- Algumas melhorias no controle de estoque, agora quando a OS é excluída os produtos voltam para o estoque. [@seitbnao](https://github.com/seitbnao)
- Corrige o menu Financeiro em tablets e celulares. [@seitbnao](https://github.com/seitbnao)

## Changed
- Liberado edição do CPF na area administrativa, porem foi mantido o bloqueio no editar usuário. [@willph](https://github.com/willph)

## [4.28.0] - 2021-01-20

### Added
- Habilitar ou desabilitar a edição de data de pagamento retroativa ou futura na edição de lançamentos. [@seitbnao](https://github.com/seitbnao)

### Fixed
- Atualizado lib de QR code. [@seitbnao](https://github.com/seitbnao)
- Corrigido alerts gerais. [@seitbnao](https://github.com/seitbnao)
- Refatorado módulo de pagamentos. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.27.0] - 2021-01-13

### Added
- Implementado notificação whatsapp personalizada e sweetalert. [@seitbnao](https://github.com/seitbnao)

## [4.26.2] - 2021-01-09

### Fixed
- Corrigindo erro nos lancamentos, onde após update não eram listados os lancamentos sem usuarios_id. [@bulfaitelo](https://github.com/bulfaitelo)

## [4.26.1] - 2021-01-09

## Fixed
- Corrige um erro que bloqueava o preenchimento do cliente em adicionar vendas. [@seitbnao](https://github.com/seitbnao)

## [4.26.0] - 2021-01-05

## Added
- Adiciona a cobrança ao lado do cliente. [@seitbnao](https://github.com/seitbnao)
- Permite reenviar a cobrança por email. [@seitbnao](https://github.com/seitbnao)
- Permite visualizar o boleto de cobrança. [@seitbnao](https://github.com/seitbnao)
- Permite atualizar o status de cobrança. [@seitbnao](https://github.com/seitbnao)
- Bloqueia o cliente de gerar uma cobrança, sendo restrito apenas para o administrador. [@seitbnao](https://github.com/seitbnao)
- Faz uma alteração na tabela cobrancas, para que a mesma receba o id do cliente. [@seitbnao](https://github.com/seitbnao)

## [4.25.1] - 2021-01-04

## Fixed
- Corrigido as permissões para que não ocorra erros após a instalação. [@seitbnao](https://github.com/seitbnao)
- Corrigido o erro ao emitir o alerta de erro caso uma cobrança já esteja viculada a alguma venda/os. [@seitbnao](https://github.com/seitbnao)

## [4.25.0] - 2020-12-30

## Added
- Adicionado módulo para gerenciar as cobranças emitidas por boleto/link. [@seitbnao](https://github.com/seitbnao)

## Fixed
- Corrigido problema na geração da cobrança a partir da ordem de serviço. [@seitbnao](https://github.com/seitbnao)

## [4.24.0] - 2020-12-27

## Added
- Máscara em CNPJ no menu adicionar e editar emitente. [@willph](https://github.com/willph)
- Adicionado campo CEP em Emitente para facilitar preenchimento e organizado controller, model e bd para aceitar novo campo. [@willph](https://github.com/willph)
- Adaptado função javascript para buscar dados do CNPJ em Cliente e Emitente. [@willph](https://github.com/willph)
- Adicionado botão para pesquisar e preencher os dados da empresa automaticamente em cadastro/editar do emitente. [@willph](https://github.com/willph)

## Fixed
- Correção de bug na area do cliente nos campos telefone e email em visualizarOs e visualizarVenda. [@willph](https://github.com/willph)

## [4.23.0] - 2020-12-17

## Added
- Adicionado a opção de gerar o PDF do Boleto. [@willph](https://github.com/willph)
- Adicionado cor na Agenda OS. [@willph](https://github.com/willph)
- Adicionado a opção de link de pagamento em OS e Vendas, com a possibilidade de enviar por Whatsapp. [@willph](https://github.com/willph)

## Fixed
- Removido do form os client id e secret em OS e vendas. [@willph](https://github.com/willph)
- Corrigido bug que quebrava a exibição da pagina por causa de um erro de exception ao clicar gerar etiqueta. [@willph](https://github.com/willph)
- Corrigido observações e observações para cliente em vendas e melhorado visualização. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Corrigido exibição de modal de conformação de exclusão de credenciais de pagamento. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.22.0] - 2020-12-13

## Added
- Adicionado a opção de link de pagamento em OS e Vendas, com a possibilidade de enviar por Whatsapp. [@willph](https://github.com/willph)

## [4.21.0] - 2020-12-10

## Added
- Implementado pagamento via gerencianet. [@willph](https://github.com/willph)

## [4.20.2] - 2020-12-06

## Fixed
- Adequação para permitir editar observações de lançamentos financeiros. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.20.1] - 2020-11-10

## Fixed
- Correção de filtro de cliente/fornecedor que não funcionava com caracteres especiais. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.20.0] - 2020-11-08

## Added
- Adicionado observações em lançamentos financeiros. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Adicionado autocomplete de cliente/fornecedor em listagem de lançamentos financeiros. [@Pr3d4dor](https://github.com/Pr3d4dor)

## Fixed
- Adequação para excluir os lançamentos financeiros ao excluir uma OS/Venda faturada. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Adequação para permitir adicionar produtos/serviços na OS com preço zerado. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.19.0] - 2020-10-26

## Added
- Implementado possibilidade de visualizar/editar/excluir OS pelo calendário de OS. [@willph](https://github.com/willph)

## Fixed
- Removido SDK de PayPal abandonada. [@willph](https://github.com/willph)
- Atualizado SDK de mercado pago. [@willph](https://github.com/willph)

## [4.18.1] - 2020-10-18

## Fixed
- Corrigido exibição de observações para cliente em vendas. [@Flexotron20](https://github.com/Flexotron20)

## [4.18.0] - 2020-10-17

## Fixed
- Corrigido bug de visualização de sidebar em mobile. [@visaotec](https://github.com/visaotec)
- Corrigido exibição de ícones em mobile. [@visaotec](https://github.com/visaotec)

## Added
- Implementado relatório de vendas (rápido e custom) em XLS. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Adicionado campo de observações para cliente em vendas. [@Flexotron20](https://github.com/Flexotron20)

## [4.17.1] - 2020-10-05

## Fixed
- Corrigido filtro de status de lançamento. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.17.0] - 2020-10-04

## Added

- Modificado filtros de lançamentos para permitir período arbitrário de data e adicionado filtro de status. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.16.0] - 2020-10-04

## Added
- Adicionado filtro por nome de cliente/fornecedor e filtro de tipo de lançamento em lançamentos financeiros. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Adicionado totais (produtos, serviços e geral) no final do relatório de os (rápido e custom). [@Pr3d4dor](https://github.com/Pr3d4dor)
- Implementado relatório de OS (rápido e custom) em XLS. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.15.1] - 2020-09-26

## Fixed
- Corrigido colar texto em campo CPF/CNPJ (documento) em clientes. [@Pr3d4dor](https://github.com/Pr3d4dor)

## Changed
- Adicionado regra de validação unique em campo CPF/CNPJ (documento) em clientes. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.15.0] - 2020-09-21

## Added
- Adicionado observacões em vendas. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.14.1] - 2020-09-21

## Fixed
- Corrigido busca de calendário de OS. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.14.0] - 2020-09-20

## Fixed
- Implementado calendário com as OS em dashboard. [@willph](https://github.com/willph)

## [4.13.2] - 2020-09-20

## Fixed
- Corrigido problema em adicionar produtos/serviços e OS em que o último id de produto/serviço era mantido no campo hidden e assim era desconsiderado o produto/serviço sendo escolhido na segunda adição. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.13.1] - 2020-09-15

## Fixed
- Adequação para manter o estado da checkbox "pagoEditar" em lançamentos. [@nmdavi](https://github.com/nmdavi)

## [4.13.0] - 2020-08-29

## Added
- Implementado relatório SKU. [@Pr3d4dor](https://github.com/Pr3d4dor)

## Changed
- Adequação para retornar relatórios financeiros ordenados por data de vencimento do lançamento. [@Pr3d4dor](https://github.com/Pr3d4dor)

## Fixed
- Corrigido título de modal e descrição de faturamento de OS. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Corrigido bug de relatório financeiro rápido omitindo alguns lançamentos. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.12.1] - 2020-08-16

## Fixed
- Corrigido quantidade e valor de serviços/produtos em área de clientes. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.12.0] - 2020-08-15

## Added
- Implementado relatório financeiro em XLSX. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.11.2] - 2020-07-25

## Changed
- Mudança (Linhas 70,71,72) realizada para incluir nas pesquisas também o código de barras, assim poderia ser feito através de leitor e alterado o limite devido termos muitos itens com descrição similar se a pesquisa for executada pelo nome. [@FlexoSol](https://github.com/FlexoSol)
- Mudança (Linha 7 para facilitar a localização e posterior envio via e-mail, onde alguns clientes(empresas) exigem que a proposta esteja em anexo e não em corpo do e-mail. [@FlexoSol](https://github.com/FlexoSol)
- Mudança (Linha 94,106) para incluir o código do Produto na venda e facilitar a identificação. [@FlexoSol](https://github.com/FlexoSol)
- Mudança (Linha 7 para facilitar a localização e posterior envio via e-mail, onde alguns clientes(empresas) exigem que a proposta esteja em anexo. [@FlexoSol](https://github.com/FlexoSol)

## [4.11.1] - 2020-07-18

## Added
- Corrigido relatório rápido de produtos com estoque mínimo. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.11.0] - 2020-07-16

## Added
- Adicionando relatório de clientes em xls. [@RamonSilva20](https://github.com/RamonSilva20)

## [4.10.0] - 2020-07-01

## Added
- Adicionando suporte a HMVC. [@RamonSilva20](https://github.com/RamonSilva20)

## Fixed
- Corrigindo erro na listagem quando arquivo não encontrado. [@RamonSilva20](https://github.com/RamonSilva20)

## [4.9.0] - 2020-06-14

## Added
- Adequação para permitir a busca de cliente via telefone e celular. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.8.3] - 2020-06-14

## Fixed
- Corrigido download de anexo de OS em área de cliente e adequação para exibir nome do arquivo abaixo. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.8.2] - 2020-05-29

## Fixed
- Adequação para mostrar nome abaixo do ícone do anexo em OS. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Corrigido tema neve em mobile. [@willph](https://github.com/willph)
- Corrigido permissões padrão em instalação. [@willph](https://github.com/willph)

## [4.8.1] - 2020-05-23

## Changed
- Melhoria em mensagem de erro pasta faltante system. [@RamonSilva20](https://github.com/RamonSilva20)
- Melhoria em consultas de relatório (order_by e data inicial e data final). [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.8.0] - 2020-05-18

## Fixed
- Correção de alguns erros de escrita em form_validation. [@willph](https://github.com/willph)

## Changed
- Liberado todos os modos de pagamento em mercado pago. [@willph](https://github.com/willph)
- Melhoria em impressão de OS em impressora térmica. [@willph](https://github.com/willph)

## [4.7.5] - 2020-05-11

## Fixed
- Refatorado a adição de produtos/serviços na OS com a adição de form_validation. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.7.4] - 2020-05-09

## Changed
- Melhorado a limpeza de diretório extrapido de atualização para funcionar em qualquer SO. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.7.3] - 2020-05-08

## Fixed
- Corrigido versão inicial de migrations. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.7.2] - 2020-05-02

## Fixed
- Corrigido valor total de OS incorreto por conta de JOINS. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.7.1] - 2020-05-01

## Changed
- Melhoria em execução de migration e refatoração em helper de validação. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Melhoria em busca de informações na receita federal (CNPJ). [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.7.0] - 2020-04-28

### Added
- Adicionado coluna contato e coluna complemento na tabela de clientes. [@gustavol](https://github.com/gustavol)
- Implementado auto complete de dados de cliente via API da Receita Federal (CNPJ). [@gustavol](https://github.com/gustavol)

## [4.6.2] - 2020-04-29

### Fixed
- Corrigido total de receitas, despesas e saldo em lançamentos financeiros. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.6.1] - 2020-04-28

### Changed
- Melhorado extração de zip de atualização. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.6.0] - 2020-04-27

### Fixed
- Corrigido topo de todos os relatórios. [@Pr3d4dor](https://github.com/Pr3d4dor)

### Added
- Implementado gráfico de barras dinâmico de vendas por ano.[@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.5.4] - 2020-04-23

### Fixed
- Corrigido logs de OS. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Corrigido separador de diretórios em upload de anexo em OS. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.5.3] - 2020-04-21

### Fixed
- Corrigido ordem de exibição de últimas OS em painel de cliente. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Corrigido exibição e download de anexos de OS em painel de cliente. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.5.2] - 2020-04-21

### Fixed
- Corrigido soma de valor total de OS em listagem. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.5.1] - 2020-04-21

### Changed
- Adicionando Descrição na busca geral, juntamente da coluna no resultado. [@bulfaitelo](https://github.com/bulfaitelo)

## [4.5.0] - 2020-04-19

### Added
- Adequação para mostrar valor total de OS na tela de listagem (valor total e valor total (faturado)). [@Pr3d4dor](https://github.com/Pr3d4dor)

### Changed
- Melhoria em busca de tag em releases no atualizador. [@Pr3d4dor](https://github.com/Pr3d4dor)
- Melhoria em listagem de arquivos. [@hoshikawakun](https://github.com/hoshikawakun)

### Fixed
- Corrigido exibição de data de vencimento de garantia. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.4.1] - 2020-04-10

### Fixed
- Criado mascara para cpf de usuario em adicionar e editar. Evitar erro de entra CNPJ nesse campo. [@willph](https://github.com/willph)
- Validação de cpf do usuario em adicionarUsuario.php. [@willph](https://github.com/willph)
- Criado verificação de CPF e CNPJ em adicionarCliente.php e editarCliente.php. [@willph](https://github.com/willph)
- Corrigido erro em view os.php devido preenchimendo de caracteres não numericos em view adicionarOs.php e EditarOs.php. [@willph](https://github.com/willph)
- Criado Campo CEP na tabela USUÁRIOS. [@willph](https://github.com/willph)
- Corrigido erro email do Usuário e Cliente em Vendas. [@willph](https://github.com/willph)
- Otimizado configuração de nginx em docker. [@Pr3d4dor](https://github.com/Pr3d4dor)

## [4.4.0] - 2020-04-10

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
