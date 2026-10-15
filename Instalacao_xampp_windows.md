# **INSTALAÇÃO DO MAPOS (XAMPP / WINDOWS)**


* LEMBRANDO QUE ESSES PASSO A PASSO É PARA QUEM USA **XAMPP** COMO SERVIDOR LOCAL E TEM **WINDOWS** COMO SISTEMA OPERACIONAL.

* CASO NÃO TENHA O **COMPOSER** E O **XAMPP** INSTALADOS, POR FAVOR BAIXAR E INSTALAR ANTES PARA EVITAR ERRO.

* INSTALAÇÃO DO **COMPOSER** E DO **XAMPP** É SIMPLES, ESTILO _NEXT_... _NEXT_... _FINISH_.

* _SEMPRE BAIXAR A VERSÃO MAIS RECENTE DO **MAPOS**, NESTE EXEMPLO FOI USADO A VERSÃO 4.8.0_

* ESTE GUIA DE INSTALAÇÃO NÃO SERVE PARA ATUALIZAÇÃO.

**DOWNLOAD COMPOSER LINK**: ` https://getcomposer.org/Composer-Setup.exe`

**DOWNLOAD DO XAMPP LINK**: `https://www.apachefriends.org/download.html`
> **_Escolha a versão de sua preferência desde que seja superior ao php 7.1_**



### PRÓXIMOS PASSOS:

1. DEIXE O SERVIDOR RODANDO(ATIVO). EXECUTANDO O XAMPP QUE FICA EM `C:\xampp` DE **DUPLO CLIQUE** EM `xampp-control` QUE ESTA DENTRO DA PASTA. QUANDO ELE ABRIR VOCÊ CLICARA EM **START** REFERENTE A **APACHE** E A **MYSQL** FEITO IMAGEM ABAIXO

![executar-xampp](https://user-images.githubusercontent.com/17226802/82738715-3b690f80-9d10-11ea-895a-ad48d80e3d3b.jpg)


2. SE TUDO OCORRER OK FICARA **VERDE** TODOS DOIS, FEITO IMAGEM ABAIXO

![executar-xampp-ok](https://user-images.githubusercontent.com/17226802/82738728-563b8400-9d10-11ea-9e95-b0e2d8bf3390.jpg)


3. AGORA BAIXE O MAPOS ATRAVÉS DO LINK: https://github.com/RamonSilva20/mapos/releases

![mapos-down](https://user-images.githubusercontent.com/17226802/82736980-e9ba8800-9d03-11ea-8bf3-d3c10debd8c1.jpg)


4. EXTRAIR O ARQUIVO E RENOMEAR A PASTA PARA **MAPOS** E COLAR ESSA PASTA NO **HTDOCS** QUE SE ENCONTRA DENTRO DA PASTA XAMPP: `C:\xampp\htdocs`

![pasta-mapos-htdocs](https://user-images.githubusercontent.com/17226802/82737098-ad3b5c00-9d04-11ea-94d2-fcec7c7d5a26.jpg)


5. ENTRE NA PASTA **MAPOS** E DENTRO DELA FAÇA O SEGUINTE PARA ABRIR O POWER SHELL **SEGURAR SHIFT + BOTÃO DIREITO DO MOUSE**, E CLICAR EM **ABRIR JANELA DO POWER SHELL AQUI** FEITO A TELA ABAIXO.

![powerShell](https://user-images.githubusercontent.com/17226802/82736684-fa69fe80-9d01-11ea-8ec0-bf5906d552b2.jpg)


6. APÓS ABRIR A JANELA DO POWER SHELL É SO DIGITAR O COMANDO `composer install --no-dev` FEITO A IMAGEM ABAIXO

![powerShell2](https://user-images.githubusercontent.com/17226802/82736696-0ce43800-9d02-11ea-8d32-87ffcd8a3669.jpg)

7. SE O COMPOSER ESTIVER INSTALADO CORRETAMENTE VAI ACONTECER ALGO SIMILAR A IMAGEM ABAIXO, AGUARDE A FINALIZAÇÃO.

![composer-install-comand](https://user-images.githubusercontent.com/17226802/82737208-a103ce80-9d05-11ea-9b93-61c2ec28be76.jpg)


> OBS: Na imagem acima esta escrito ........CACHE porque eu já fiz esse procedimento, então ele aproveita os arquivos em CACHE para instalar as dependências.

8. AGORA É HORA DE CRIAR UM BANCO DE DADOS PARA O MAPOS PELO **PHPMYADMIN**, PARA ISSO DIGITE A URL NO NAVEGADOR: `http://localhost/phpmyadmin/` E DENTRO DELE CRIE UM BD COM O NOME DE SUA PREFERÊNCIA. CLICANDO NO BOTÃO **NOVO**  E DEPOIS DE DIGITAR O NOME DO SEU BD É SO CLICAR EM **CRIAR** FEITO IMAGEM ABAIXO.

![bd-mapos](https://user-images.githubusercontent.com/17226802/82737644-da8a0900-9d08-11ea-9b47-321843078ef5.jpg)


9. APÓS BD CRIADO ACESSAR LINK `http://localhost/mapos/` E APERTAR **ENTER** A URL MUDARA PARA `http://localhost/mapos/install/index.php` NESSA TELA DE INSTALAÇÃO SO CLICAR EM **PRÓXIMO**

![install-mapos-inicio](https://user-images.githubusercontent.com/17226802/82737718-6c921180-9d09-11ea-8d12-f63aaea2266d.jpg)


10. NA PRÓXIMA TELA VEM AS CONFIGURAÇÕES, **ENDEREÇO DO BD, NOME DE USUÁRIO DO BD, SENHA(_xampp por padrão vem sem senha, deixar em branco nesse caso_), NOME DO BANCO DE DADOS QUE DEU PARA USAR NO MAPOS, SEU NOME, SEU LOGIN E SUA SENHA PARA ACESSAR O MAPOS APÓS INSTALAÇÃO**  DEPOIS DE TUDO PREENCHIDO É SO CLICAR EM **INSTALAR** E AGUARDAR. EXEMPLO NA IMAGEM ABAIXO.

![config-mapos-instalacao](https://user-images.githubusercontent.com/17226802/82737856-2be6c800-9d0a-11ea-9154-8f32448f7b86.jpg)


11. FINALIZADA A INSTALAÇÃO SO CLICAR EM **IR PARA PAGINA DE LOGIN**

![install-mapos-full](https://user-images.githubusercontent.com/17226802/82737956-c3e4b180-9d0a-11ea-9660-16753ca1da94.jpg)


12. IRA PARA A PAGINA DE LOGIN `http://localhost/mapos/index.php/login` ONDE O EMAIL FOI **ADMIN@ADMIN.COM** E SENHA **123456**, _LEMBRANDO QUE AS CREDENCIAS FICAM A SUA ESCOLHA NA HORA DA CONFIGURAÇÃO, ESSAS FORAM AS QUE ESCOLHE PARA ESSE EXEMPLO._

![primeiro-acesso](https://user-images.githubusercontent.com/17226802/82738082-9815fb80-9d0b-11ea-8227-d7c2c82b12a3.jpg)


13. DEPOIS QUE CLICAR EM **ACESSAR**, VOCÊ ESTÁ PRONTO PARA USAR O SISTEMA, SEJA BEM-VINDO AO MAPOS :D

![acesso-concluido](https://user-images.githubusercontent.com/17226802/82738126-e1664b00-9d0b-11ea-9684-024f8a740cf8.jpg)
