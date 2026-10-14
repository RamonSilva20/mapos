# PR #2975 - API_ENABLED desliga a API (achado 5 da #2879)

Teste em mapos.test, checkout do PR (491cf935), 2026-10-14. Tokens omitidos.

## 1. API_ENABLED=false (GET / POST) -> tudo 404
    api/v1 404 404
    api/v1/login 404 404
    api/v1/client/login 404 404
    index.php/api/v1/ApiController/index 404 404
    index.php/api/v1/UsuariosController/login 404 404
    index.php/api/v1/ClientesController 404 404
    index.php/api/v1/OsController 404 404
    index.php/api/v1/client/ClientLoginController/login 404 404
    index.php/api/v1/client/auth (rota real do cliente) 404
    API/V1/ApiController/index, api/v1/apicontroller, .../index/x, client/ClientOsController: 404
    CLI: php index.php api/v1/ApiController/index -> "404 Not Found"
Nenhuma tentativa de login com credenciais reais nesse estado (so POST vazio, barrado antes do controller).

## 2. API_ENABLED=true (temporario)
    sem token: api/v1, clientes, usuarios, client, client/os -> 401
    URL direta sem token: ApiController/index, ClientesController, OsController, client/ClientOsController/index -> 401
    POST api/v1/login (admin) -> {"status":true,"message":"Login realizado com sucesso!", access_token/refresh_token <omitido>}
    POST api/v1/login (errado) -> 401 "Os dados de acesso estao incorretos!"
    POST api/v1/client/auth (senha errada) -> 401 "Os dados de acesso estao incorretos."
    com Bearer: api/v1 200, clientes 200, os 200, conta 200, ApiController/index 200, ClientesController 200
    POST api/v1/refresh -> {"status":true,"message":"Token renovado!"}
    token de usuario em api/v1/client/os -> 403 "Faca o login novamente!"
Lacuna: login de cliente com sucesso nao testado (sem senha conhecida de cliente local; alterar o banco foi barrado).

## 3. Painel e area do cliente (nos dois estados, API false e true)
    POST login/verificarLogin -> {"result":true}; /mapos 200; /os 200 ("Ordens de servico - Map-OS"); /clientes 200
    /mine 200 (tela de login da area do cliente), /mine/painel sem sessao -> volta ao login

## 4. Revisao
    Todos os 11 controllers de application/controllers/api/** herdam de REST_Controller; nenhum outro arquivo de controllers/models/views/helpers referencia REST_Controller/classes da API.
    Guarda em REST_Controller::__construct logo apos parent::__construct (antes de preflight_checks).
    PHPUnit ApiDesligadaTest: OK (4 tests, 33 assertions).
    .env restaurado (API_ENABLED=false).
