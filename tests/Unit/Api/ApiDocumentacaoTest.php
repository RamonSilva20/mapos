<?php

use Libraries\Api\OpenApi;
use Libraries\Api\Rotas;
use Libraries\Api\Roteamento\Rota;

final class ApiDocumentacaoTest extends MaposTestCase
{
    private const ROTAS_PUBLICAS = [
        'POST /auth/login',
        'POST /auth/login/dois-fatores',
        'POST /auth/renovar',
        'POST /cliente/auth/login',
        'POST /cliente/auth/renovar',
        'GET /cliente/auth/captcha',
        'POST /cliente/auth/cadastro',
        'POST /cliente/auth/senha/recuperar',
        'POST /cliente/auth/senha/redefinir',
    ];

    private const SEM_PERMISSAO_ESPECIFICA = [
        'POST /auth/logout', 'GET /auth/sessoes', 'DELETE /auth/sessoes', 'DELETE /auth/sessoes/{sessao}',
        'GET /conta', 'PUT /conta/senha', 'PATCH /conta/tema',
        'POST /conta/dois-fatores/configuracao', 'POST /conta/dois-fatores', 'POST /conta/dois-fatores/codigos', 'POST /conta/dois-fatores/desativar',
        'GET /painel', 'GET /busca', 'GET /emitente',
    ];

    /** @return list<Rota> */
    private function rotas(): array
    {
        return Rotas::todas();
    }

    private function especificacao(): array
    {
        return OpenApi::gerar(Rotas::roteador(), 'http://mapos.test/', '5.5.0', Rotas::TAGS);
    }

    public function testSoAsRotasDeEntradaSaoPublicas(): void
    {
        $publicas = [];
        foreach ($this->rotas() as $rota) {
            if ($rota->autenticacao === null) {
                $publicas[] = $rota->metodo . ' ' . $rota->caminhoOpenApi();
            }
        }

        sort($publicas);
        $esperadas = self::ROTAS_PUBLICAS;
        sort($esperadas);
        $this->assertSame($esperadas, $publicas);
    }

    public function testTodaRotaDeUsuarioExigeUmaPermissaoSalvoAsDaPropriaConta(): void
    {
        foreach ($this->rotas() as $rota) {
            $chave = $rota->metodo . ' ' . $rota->caminhoOpenApi();
            if ($rota->autenticacao !== Rota::USUARIO || in_array($chave, self::SEM_PERMISSAO_ESPECIFICA, true)) {
                continue;
            }
            $this->assertNotNull($rota->permissao, "{$chave} precisa declarar a permissão.");
            foreach ($rota->permissao['permissoes'] as $codigo) {
                $this->assertContains($codigo, permissoesCodigos(), "{$chave}: permissão desconhecida {$codigo}.");
            }
        }
    }

    public function testRotasDoClienteSoComTokenDeCliente(): void
    {
        foreach ($this->rotas() as $rota) {
            if (str_starts_with($rota->caminho, '/cliente/') && $rota->autenticacao !== null) {
                $this->assertSame(Rota::CLIENTE, $rota->autenticacao, $rota->caminho);
                $this->assertNull($rota->permissao, $rota->caminho);
            }
            if (! str_starts_with($rota->caminho, '/cliente/')) {
                $this->assertNotSame(Rota::CLIENTE, $rota->autenticacao, $rota->caminho);
            }
        }
    }

    public function testCadaRotaTemResumoTagDocumentadaEHandlerPublico(): void
    {
        $tagsAgrupadas = array_merge(...array_values(OpenApi::GRUPOS));

        foreach ($this->rotas() as $rota) {
            $chave = $rota->metodo . ' ' . $rota->caminho;
            $this->assertNotSame('', $rota->resumo, "{$chave} sem resumo.");
            $this->assertArrayHasKey($rota->tag, Rotas::TAGS, "{$chave}: tag sem descrição em Rotas::TAGS.");
            $this->assertContains($rota->tag, $tagsAgrupadas, "{$chave}: tag fora de OpenApi::GRUPOS.");
            $this->assertNotEmpty($rota->respostas, "{$chave} sem resposta documentada.");

            [$classe, $metodo] = $rota->acao;
            $this->assertTrue(method_exists($classe, $metodo), "{$chave}: {$classe}::{$metodo} não existe.");
            $this->assertTrue((new ReflectionMethod($classe, $metodo))->isPublic(), "{$chave}: {$metodo} precisa ser público.");
        }
    }

    public function testEspecificacaoOpenApiConsistente(): void
    {
        $spec = $this->especificacao();

        $this->assertSame('3.1.0', $spec['openapi']);
        $this->assertSame('http://mapos.test/api/v2', $spec['servers'][0]['url']);
        $this->assertNotFalse(json_encode($spec));

        $operacoes = [];
        foreach ($spec['paths'] as $caminho => $metodos) {
            preg_match_all('/\{([a-z_]+)\}/', $caminho, $parametros);
            foreach ($metodos as $metodo => $operacao) {
                $operacoes[] = $operacao['operationId'];
                $declarados = array_column(array_filter($operacao['parameters'] ?? [], static fn ($p) => $p['in'] === 'path'), 'name');
                $this->assertEqualsCanonicalizing($parametros[1], $declarados, "{$metodo} {$caminho}: parâmetros do caminho.");

                $sucesso = array_filter(array_keys($operacao['responses']), static fn ($s) => (int) $s >= 200 && (int) $s < 300);
                $this->assertNotEmpty($sucesso, "{$metodo} {$caminho} sem resposta de sucesso.");

                if ($operacao['security'] !== []) {
                    $this->assertArrayHasKey('401', $operacao['responses'], "{$metodo} {$caminho}");
                }
            }
        }

        $this->assertSame(count($operacoes), count(array_unique($operacoes)), 'operationId repetido.');
        $this->assertSame(count($operacoes), count($this->rotas()));
    }

    public function testTodaReferenciaApontaParaUmComponenteExistente(): void
    {
        $spec = $this->especificacao();
        $json = (string) json_encode($spec, JSON_UNESCAPED_SLASHES);
        preg_match_all('~"\$ref":"#/components/(schemas|responses)/([A-Za-z]+)"~', $json, $refs, PREG_SET_ORDER);

        $this->assertNotEmpty($refs);
        foreach ($refs as [, $tipo, $nome]) {
            $this->assertArrayHasKey($nome, $spec['components'][$tipo], "Referência quebrada: {$tipo}/{$nome}");
        }
    }

    public function testRespostasNaoDocumentamSenhaNemHashDeToken(): void
    {
        $esquemas = (string) json_encode($this->especificacao()['components']['schemas']);

        foreach (['"senha_hash"', '"token_hash"', '"totp_secreto"', '"token_versao"'] as $proibido) {
            $this->assertStringNotContainsString($proibido, $esquemas);
        }
    }
}
