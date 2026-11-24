<?php

namespace Libraries\Api;

use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Roteamento\Roteador;
use Libraries\Api\Schema\Componentes;
use Libraries\Api\Schema\Comuns;

final class OpenApi
{
    private const ERROS = [
        400 => ['RequisicaoInvalida', 'Requisição malformada (JSON inválido, por exemplo).'],
        401 => ['NaoAutenticado', 'Sem token, token inválido ou expirado, ou sessão encerrada. Renove com `POST /auth/renovar` ou faça o login.'],
        403 => ['Proibido', 'O usuário não tem a permissão exigida, ou a regra de negócio não deixa (conta expirada, 2FA obrigatório...).'],
        404 => ['NaoEncontrado', 'O registro não existe, ou não pertence a quem pediu.'],
        409 => ['Conflito', 'O estado atual impede a operação.'],
        413 => ['MuitoGrande', 'Arquivo ou corpo acima do limite.'],
        415 => ['TipoNaoSuportado', 'Envie o corpo como application/json (ou multipart/form-data nas rotas de upload).'],
        422 => ['Validacao', 'Campos inválidos (`erros` traz a mensagem de cada campo) ou operação não permitida pela regra de negócio.'],
        429 => ['MuitasRequisicoes', 'Limite de requisições ou de tentativas de login. Aguarde o `Retry-After` (segundos).'],
        502 => ['FalhaNoGateway', 'O gateway de pagamento recusou ou não respondeu. `detail` traz a mensagem dele.'],
        503 => ['Indisponivel', 'API sem a chave dos tokens configurada ou banco de dados desatualizado.'],
    ];

    public const GRUPOS = [
        'Acesso' => ['Autenticação', 'Minha conta'],
        'Visão geral' => ['Painel', 'Busca'],
        'Cadastros' => ['Clientes', 'Produtos', 'Serviços', 'Termos de garantia', 'Arquivos'],
        'Operação' => ['Ordens de serviço', 'Vendas'],
        'Financeiro' => ['Lançamentos', 'Cobranças'],
        'Relatórios' => ['Relatórios'],
        'Administração' => ['Usuários', 'Grupos de permissão', 'Emitente', 'Configurações', 'Auditoria', 'Fila de e-mails', 'Sistema'],
        'Área do cliente' => ['Cliente: autenticação', 'Cliente: conta', 'Cliente: ordens de serviço', 'Cliente: compras', 'Cliente: cobranças'],
    ];

    /** @param  array<string, string>  $tags */
    public static function gerar(Roteador $roteador, string $servidor, string $versao, array $tags): array
    {
        $componentes = new Componentes();
        $componentes->registrar('Erro', Comuns::erro());

        $caminhos = [];
        foreach ($roteador->rotas() as $rota) {
            $caminhos[$rota->caminhoOpenApi()][strtolower($rota->metodo)] = self::operacao($rota, $componentes);
        }

        $respostas = [];
        foreach (self::ERROS as $status => [$nome, $descricao]) {
            $respostas[$nome] = [
                'description' => $descricao,
                'content' => ['application/problem+json' => ['schema' => ['$ref' => '#/components/schemas/Erro']]],
            ];
        }

        $usadas = [];
        foreach ($roteador->rotas() as $rota) {
            $usadas[$rota->tag] = true;
        }

        $grupos = [];
        foreach (self::GRUPOS as $grupo => $nomes) {
            $presentes = array_values(array_filter($nomes, static fn (string $t): bool => isset($usadas[$t])));
            if ($presentes !== []) {
                $grupos[] = ['name' => $grupo, 'tags' => $presentes];
            }
        }

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'API do Map-OS',
                'version' => $versao,
                'description' => self::introducao(),
                'license' => ['name' => 'Apache-2.0', 'identifier' => 'Apache-2.0'],
            ],
            'servers' => [['url' => rtrim($servidor, '/') . '/api/v2', 'description' => 'Esta instalação']],
            'tags' => array_map(
                static fn (string $nome, string $descricao): array => ['name' => $nome, 'description' => $descricao],
                array_keys(array_intersect_key($tags, $usadas)),
                array_values(array_intersect_key($tags, $usadas)),
            ),
            'x-tagGroups' => $grupos,
            'paths' => $caminhos,
            'components' => [
                'securitySchemes' => [
                    'usuario' => ['type' => 'http', 'scheme' => 'bearer', 'bearerFormat' => 'JWT', 'description' => 'Access token de um usuário do painel (`POST /auth/login`).'],
                    'cliente' => ['type' => 'http', 'scheme' => 'bearer', 'bearerFormat' => 'JWT', 'description' => 'Access token de um cliente da área do cliente (`POST /cliente/auth/login`).'],
                ],
                'schemas' => $componentes->paraOpenApi(),
                'responses' => $respostas,
            ],
        ];
    }

    private static function operacao(Rota $rota, Componentes $componentes): array
    {
        $descricao = $rota->descricao;
        if ($rota->permissao !== null) {
            $lista = implode(', ', array_map(static fn (string $p): string => '`' . $p . '`', $rota->permissao['permissoes']));
            $regra = count($rota->permissao['permissoes']) === 1 ? 'Permissão: ' . $lista : ($rota->permissao['tipo'] === 'todas' ? 'Permissões (todas): ' . $lista : 'Permissões (qualquer uma): ' . $lista);
            $descricao = trim($descricao . "\n\n**" . $regra . '**');
        }

        $operacao = [
            'operationId' => $rota->operacao,
            'tags' => [$rota->tag],
            'summary' => $rota->resumo,
        ];
        if ($descricao !== '') {
            $operacao['description'] = $descricao;
        }
        if ($rota->permissao !== null) {
            $operacao['x-permissoes'] = $rota->permissao;
        }

        $operacao['security'] = $rota->autenticacao === null ? [] : [[$rota->autenticacao => []]];

        $parametros = [];
        foreach ($rota->parametrosDoCaminho() as $nome) {
            $parametros[] = [
                'name' => $nome,
                'in' => 'path',
                'required' => true,
                'schema' => $rota->parametroTextual($nome) ? ['type' => 'string'] : ['type' => 'integer', 'minimum' => 1],
            ];
        }
        if ($rota->consulta !== null) {
            array_push($parametros, ...$rota->consulta->parametrosDeConsulta($componentes));
        }
        if ($parametros !== []) {
            $operacao['parameters'] = $parametros;
        }

        if ($rota->corpo !== null) {
            $operacao['requestBody'] = [
                'required' => true,
                'content' => [($rota->multipart ? 'multipart/form-data' : 'application/json') => ['schema' => $rota->corpo->paraOpenApi($componentes)]],
            ];
        }

        $respostas = [];
        foreach ($rota->respostas as $status => $resposta) {
            $definicao = ['description' => $resposta['descricao'] !== '' ? $resposta['descricao'] : 'Sucesso.'];
            if ($resposta['schema'] !== null) {
                $definicao['content'] = [$resposta['tipo'] => ['schema' => $resposta['schema']->paraOpenApi($componentes)]];
            }
            $respostas[(string) $status] = $definicao;
        }

        $erros = $rota->erros;
        if ($rota->autenticacao !== null) {
            $erros[] = 401;
            $erros[] = 403;
        }
        if ($rota->corpo !== null || $rota->consulta !== null) {
            $erros[] = 422;
        }
        if ($rota->corpo !== null && ! $rota->multipart) {
            $erros[] = 400;
            $erros[] = 415;
        }
        if ($rota->limite !== null) {
            $erros[] = 429;
        }
        $erros = array_unique($erros);
        sort($erros);
        foreach ($erros as $status) {
            if (isset(self::ERROS[$status]) && ! isset($respostas[(string) $status])) {
                $respostas[(string) $status] = ['$ref' => '#/components/responses/' . self::ERROS[$status][0]];
            }
        }
        $operacao['responses'] = $respostas;

        return $operacao;
    }

    private static function introducao(): string
    {
        return <<<'MD'
API REST do Map-OS. Cobre o que o painel faz (cadastros, ordens de serviço, vendas, financeiro, cobranças, relatórios e administração) e a área do cliente.

## Autenticação

1. `POST /auth/login` com e-mail e senha devolve um **access token** (JWT, curto) e um **refresh token** (opaco, de uso único).
2. Envie o access token em toda chamada: `Authorization: Bearer <access_token>`.
3. Quando o access token expirar (`401` com `codigo: token_expirado`), chame `POST /auth/renovar` com o refresh token. A resposta traz um par **novo**: guarde os dois e descarte os anteriores.
4. `POST /auth/logout` encerra a sessão. `GET /auth/sessoes` lista as sessões abertas da conta.

Quem tem a autenticação em dois fatores ativa recebe um `desafio` no login e conclui em `POST /auth/login/dois-fatores`.

As permissões **não** viajam no token: são lidas do banco a cada chamada. Desativar um usuário, trocar a senha ou mudar o grupo vale na próxima requisição. Trocar a senha encerra as sessões da conta.

A área do cliente tem login próprio (`POST /cliente/auth/login`) e seus tokens só valem nas rotas `/cliente/...`.

## Formato

- JSON em UTF-8. Campos em `snake_case`.
- Respostas de sucesso vêm em `data`; listagens trazem também `meta` com `pagina`, `por_pagina`, `total` e `paginas`.
- Valores em dinheiro são **texto** com duas casas (`"150.00"`), para não perder precisão. Na entrada, número ou texto são aceitos.
- Datas no formato `AAAA-MM-DD`; data e hora em ISO 8601.
- Criar responde `201` com o cabeçalho `Location`. Excluir responde `204`.
- `PATCH` altera só os campos enviados.

## Erros

Todo erro segue a RFC 9457 (`application/problem+json`) e traz um `codigo` estável para o app decidir o que fazer. Erros de validação (`422`) trazem `erros` com a mensagem de cada campo.

```json
{
  "type": "urn:mapos:erro:validacao",
  "title": "Dados inválidos",
  "status": 422,
  "codigo": "validacao",
  "detail": "Confira os campos informados.",
  "erros": { "email": "Informe um e-mail válido." }
}
```

## Limites

Cada IP tem um limite de requisições por minuto (`API_RATE_LIMIT`). Os cabeçalhos `RateLimit-Limit`, `RateLimit-Remaining` e `RateLimit-Reset` mostram a situação; acima do limite a resposta é `429` com `Retry-After`. Login e renovação têm um limite próprio, mais baixo.
MD;
    }
}
