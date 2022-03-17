Esse tutorial ensinará a criar cronjobs no Windows utlizando um programa do próprio sistema.

1- Abra o menu de pesquisa do Windows e digite "Agendador de Tarefas" conforme imagem:
!(https://i.imgur.com/07mDyiE.png)

2- Na janela que se abre, clique em "Criar Tarefa" ou nas guias em cima "Ação > Criar Tarefa":
<blockquote class="imgur-embed-pub" lang="en" data-id="a/GI6NYg8" data-context="false" ><a href="//imgur.com/a/GI6NYg8"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>

3- Coloque um nome para a tarefa exemplo "Enviar e-mails map-os" (para o processo rodar em backgraund e nao ficar aparecendo a tela de cmd executando o comando á cada vez no tempo informado, marque a opção "Executar estando o usuário conectado ou não"):
<blockquote class="imgur-embed-pub" lang="en" data-id="a/f6r3lwJ" data-context="false" ><a href="//imgur.com/a/f6r3lwJ"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>

4- Vá para a guia "Disparadores" depois "Novo...":
<blockquote class="imgur-embed-pub" lang="en" data-id="a/Jhgl7TN" data-context="false" ><a href="//imgur.com/a/Jhgl7TN"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>

5- Deixei conforme a imagem, 'Iniciar a tarafa: Em Agendamento', 'Uma vez', marque a opção 'Repetir a tarefa a cada (coloque o tempo que deseja)', e na opção ao lado 'por um período de tempo de: Indefinidamente' e clique em 'Ok':
<blockquote class="imgur-embed-pub" lang="en" data-id="a/C4NHh9l" data-context="false" ><a href="//imgur.com/a/C4NHh9l"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>

6- Vá para a guia 'Ações' e clique em 'Novo...':
<blockquote class="imgur-embed-pub" lang="en" data-id="a/mWvTMkY" data-context="false" ><a href="//imgur.com/a/mWvTMkY"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>

7- Nesta janela, deixa a opção selecionada 'Iniciar um programa', 
e em 'Programa/Script' coloque o caminho do seu php.exe, se for Xampp o caminho padrão é 'C:\xampp\php\php.exe', 
em baixo em 'Adicione argumentos' coloque o comando do map-os para processar a fila de e-mails 'index.php email/process' na próxima opção 'Iniciar em' coloque o diretório onde está a pasta do seu map-os desta forma (Para o Xampp) 'C:\xampp\htdocs\mapos' e clique em 'Ok'
<blockquote class="imgur-embed-pub" lang="en" data-id="a/sUTA0Sm" data-context="false" ><a href="//imgur.com/a/sUTA0Sm"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>

8- Feito os passos acima, clique em 'OK' e a tarefa será criada (Se for solicitado dados de login do windows siga o passo 9, se não pule para o 10):
<blockquote class="imgur-embed-pub" lang="en" data-id="a/HZBSj7t" data-context="false" ><a href="//imgur.com/a/HZBSj7t"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>

9- Se você marcou a opção do passo 3 na primeira guia 'Geral' a opção 'Executar estando o usuário conectado ou não', ao clicar em 'Ok' vai ser solicitado uma credencial de login do Windows, selecione qualquer uma da lista ou o seu User do windows e preencha a senha de login (Talves tenha na lista alguma opção que nao exige a senha),
se mesmo assim informando os dados certos de login nao der certo e ficar dando mensagem de erro, você pode voltar na primeira guia 'Geral' e desmarcar a opção 'Executar estando o usuário conectado ou não', lembrando que está opção é somente para quando a tarefa for executada nao ficar aparecendo uma janela de cmd por uns 3 segundos executando o comando
(Se mesmo seguindo tudo do passo 9 nao aceitar o usuário, você pode clicar na opção 'Alterar usuário ou grupo', na janela que abre, no último campo escreva seu nome de USer ou 'sistema' e depois em verificar nomes ):
<blockquote class="imgur-embed-pub" lang="en" data-id="a/wBYqkSf" data-context="false" ><a href="//imgur.com/a/wBYqkSf"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>

10- Para testar se funcionou, não precisa esperar o intervalor de tempo selecionado, basta clicar com o botão direito na tarefa e 'Executar', pode tambem selecionar com um clique a tarefa e ao lado nas opções em 'Executar':
<blockquote class="imgur-embed-pub" lang="en" data-id="a/XzR65ns" data-context="false" ><a href="//imgur.com/a/XzR65ns"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>

11- Para conferir se executou a fila, vá em seu Map-os 'Configurações' e em 'Emails' e veja o 'Status', se tiver com o status 'Falhou' possivelmente o problema é na sua configuração smtp, para saber se executou ou não basta nao estar em 'Pendente'

Por fim, você criar outra tarefa para o comando 'index.php email/retry', para e-mails com erros ou não enviado, basta seguir os mesmos passos, somente alterando conforme o passo 7 a opção 'Adicione argumentos'
