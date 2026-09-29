<?php

/*
|--------------------------------------------------------------------------
| Controller inerte da suíte de testes
|--------------------------------------------------------------------------
|
| Não existe uma suíte de testes funcional sem subir o framework. Este
| controller dá ao bootstrap uma requisição que não faz nada: ele é
| despachado no boot, justamente para que nenhum dado real seja tocado.
|
| A guarda abaixo mantém a rota inalcançável fora do ambiente de testes,
| mesmo que o arquivo seja carregado diretamente.
|
| Ele estende CI_Controller, e não MY_Controller, porque MY_Controller
| redireciona para login() quando não há sessão.
|
*/

if (ENVIRONMENT !== 'testing') {
    show_404();
}

class Phpunit extends CI_Controller
{
    public function index()
    {
        // Intencionalmente vazio.
    }
}
