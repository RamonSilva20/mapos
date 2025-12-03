Esse tutorial ensinará a criar cronjobs para envio de e-mails do sistema Map-os utlizando um programa do próprio Windows.

1- Abra o menu de pesquisa do Windows e digite 'Agendador de Tarefas' conforme imagem:

![07mDyiE](https://user-images.githubusercontent.com/10822915/158883875-d688412a-a331-408f-8f17-856f0731edf3.png)






2- Na janela que se abre, clique em 'Criar Tarefa' ou nas guias em cima 'Ação > Criar Tarefa':

![iFptNra](https://user-images.githubusercontent.com/10822915/158883952-63244787-ec9c-4789-91be-c847715f2e14.png)






3- Coloque um nome para a tarefa exemplo 'Enviar e-mails map-os' (para o processo rodar em backgraund e não ficar encomodando aparecendo a tela de cmd executando o comando a cada vez no intervalo de tempo informado, marque a opção 'Executar estando o usuário conectado ou não', esta opção irá exigir seus dados de login do Windows):

![cQxX11L](https://user-images.githubusercontent.com/10822915/158884024-a0f113a9-952c-4721-9f5a-d3af29b03d48.png)






4- Vá para a guia 'Disparadores' depois 'Novo...':

![E7mqa66](https://user-images.githubusercontent.com/10822915/158884109-3d7ff889-e2a2-4426-a4b9-5172978295fe.png)






5- Deixei conforme a imagem, 'Iniciar a tarafa: Em Agendamento', 'Uma vez', marque a opção 'Repetir a tarefa a cada (coloque o tempo que deseja)', e na opção ao lado 'por um período de tempo de: Indefinidamente' e clique em 'Ok':

![bNxPRpx](https://user-images.githubusercontent.com/10822915/158884165-be3fd21f-42fc-49a4-addc-84b841bfc451.png)






6- Vá para a guia 'Ações' e clique em 'Novo...':

![N4uggns](https://user-images.githubusercontent.com/10822915/158884203-00a3da70-359a-4767-9167-86ec3728f021.png)






7- Nesta janela, deixa a opção selecionada 'Iniciar um programa', 
e em 'Programa/Script' coloque o caminho do seu php.exe, se for Xampp o caminho padrão é 'C:\xampp\php\php.exe', 
em baixo em 'Adicione argumentos' coloque o comando do map-os para processar a fila de e-mails 'index.php email/process' na próxima opção 'Iniciar em' coloque o diretório onde está a pasta do seu map-os desta forma (Para o Xampp) 'C:\xampp\htdocs\mapos' e clique em 'Ok':

![Oi07I9J](https://user-images.githubusercontent.com/10822915/158884265-64539007-95a2-4e71-b80a-7d4ae14bee49.png)






8- Feito os passos acima, clique em 'OK' e a tarefa será criada (Se for solicitado dados de login do windows siga o passo 9, se não pule para o 10):

![2JBbi0Q](https://user-images.githubusercontent.com/10822915/158884324-4d92ef4e-b849-481e-bb29-5b6fbcdced30.png)






9- Se você marcou a opção do passo 3 na primeira guia 'Geral' a opção 'Executar estando o usuário conectado ou não', ao clicar em 'Ok' vai ser solicitado uma credencial de login do Windows, selecione qualquer uma da lista ou o seu usuário do windows e preencha a senha de login (Talves tenha na lista alguma opção que não exija senha),
se mesmo assim informando os dados certos de login nao der certo e ficar dando mensagem de erro, você pode voltar na primeira guia 'Geral' e desmarcar a opção 'Executar estando o usuário conectado ou não', lembrando que está opção é somente para quando a tarefa for executada nao ficar aparecendo uma janela de cmd por uns 3 segundos executando o comando
(Se mesmo seguindo tudo do passo 9 nao aceitar o usuário, você pode clicar na opção 'Alterar usuário ou grupo', na janela que abre, no último campo escreva seu nome de USer ou 'sistema' e depois em verificar nomes ):

![T5XSXql](https://user-images.githubusercontent.com/10822915/158884385-cd5ed389-07c1-438e-9704-424f99c325ca.png)






10- Para testar se funcionou, não precisa esperar o intervalo de tempo selecionado, basta clicar com o botão direito na tarefa e 'Executar', pode tambem selecionar com um clique a tarefa e ao lado nas opções em 'Executar':

![iEY2U45](https://user-images.githubusercontent.com/10822915/158884432-ecf5efda-1974-4c3b-be1f-1e8ec8825e9e.png)






11- Para conferir se executou a fila, vá em seu Map-os 'Configurações' e em 'Emails' e veja o 'Status', se tiver com o status 'Falhou' possivelmente o problema é na sua configuração smtp, para saber se executou ou não basta nao estar em 'Pendente', lembrando que o email em Emitente em configurações precisa estar o mesmo no smtp (Para problemas com configuração de smtp procure por Issues abertas ou fechadas exemplo 'envio de email smtp')

Por fim, você pode criar outra tarefa para o comando 'index.php email/retry', que serve para reenviar e-mails com erros ou não enviados, basta seguir os mesmos passos, somente alterando conforme o passo 7 a opção 'Adicione argumentos'
