<?php

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Schema\Componentes;
use Libraries\Api\Schema\Schema;

final class ApiSchemaTest extends MaposTestCase
{
    private function validar(Schema $schema, mixed $valor, bool $textual = false): array
    {
        $erros = [];
        $saida = $schema->validar($valor, '', $erros, $textual);

        return [$saida, $erros];
    }

    public function testObjetoExigeObrigatoriosAplicaPadraoEDescartaCamposDesconhecidos(): void
    {
        $schema = Schema::objeto([
            'nome' => Schema::texto()->obrigatorio()->max(5),
            'ativo' => Schema::booleano()->padrao(true),
        ]);

        [$saida, $erros] = $this->validar($schema, ['nome' => ' Ana ', 'senha' => 'x', 'admin' => true]);
        $this->assertSame([], $erros);
        $this->assertSame(['nome' => 'Ana', 'ativo' => true], $saida);

        [, $erros] = $this->validar($schema, ['nome' => 'Nome longo demais']);
        $this->assertArrayHasKey('nome', $erros);

        [, $erros] = $this->validar($schema, []);
        $this->assertSame('Campo obrigatório.', $erros['nome']);
    }

    public function testTextoVazioEmCampoObrigatorioEhRecusado(): void
    {
        [, $erros] = $this->validar(Schema::objeto(['nome' => Schema::texto()->obrigatorio()]), ['nome' => '   ']);

        $this->assertSame('Campo obrigatório.', $erros['nome']);
    }

    public function testDecimalAceitaNumeroTextoEVirgulaENormalizaParaDuasCasas(): void
    {
        $schema = Schema::decimal()->min(0);

        foreach ([[150, '150.00'], ['150.5', '150.50'], ['1.250,99', '1250.99'], ['49,9', '49.90'], [0.1 + 0.2, '0.30']] as [$entrada, $esperado]) {
            [$saida, $erros] = $this->validar($schema, $entrada);
            $this->assertSame([], $erros, (string) $entrada);
            $this->assertSame($esperado, $saida);
        }

        foreach (['abc', '1e5', '-1', [], true] as $invalido) {
            [, $erros] = $this->validar($schema, $invalido);
            $this->assertNotSame([], $erros, var_export($invalido, true));
        }
    }

    public function testInteiroEBooleanoConvertemTextoSoQuandoSaoValidos(): void
    {
        [$saida] = $this->validar(Schema::inteiro(), '42', true);
        $this->assertSame(42, $saida);

        [, $erros] = $this->validar(Schema::inteiro(), '4.2');
        $this->assertNotSame([], $erros);

        [, $erros] = $this->validar(Schema::inteiro(), '1 OR 1=1');
        $this->assertNotSame([], $erros);

        foreach (['true' => true, '1' => true, 'false' => false, '0' => false] as $texto => $esperado) {
            [$saida] = $this->validar(Schema::booleano(), $texto, true);
            $this->assertSame($esperado, $saida);
        }

        [, $erros] = $this->validar(Schema::booleano(), 'talvez');
        $this->assertNotSame([], $erros);
    }

    public function testFormatosDeDataEmailEUrl(): void
    {
        [$saida] = $this->validar(Schema::data(), '2026-02-28');
        $this->assertSame('2026-02-28', $saida);

        foreach (['2026-02-30', '28/02/2026', '2026-2-1'] as $invalida) {
            [, $erros] = $this->validar(Schema::data(), $invalida);
            $this->assertNotSame([], $erros, $invalida);
        }

        [, $erros] = $this->validar(Schema::email(), 'nao-e-email');
        $this->assertNotSame([], $erros);

        [, $erros] = $this->validar(Schema::url(), 'javascript:alert(1)');
        $this->assertNotSame([], $erros, 'Só http(s) é URL aceita.');
    }

    public function testEnumListaERegraPersonalizada(): void
    {
        [, $erros] = $this->validar(Schema::enum(['real', 'porcento']), 'dolar');
        $this->assertStringContainsString('real, porcento', $erros['']);

        [$saida, $erros] = $this->validar(Schema::lista(Schema::inteiro()->min(1))->max(3), [1, 2]);
        $this->assertSame([1, 2], $saida);
        $this->assertSame([], $erros);

        [, $erros] = $this->validar(Schema::objeto(['ids' => Schema::lista(Schema::inteiro()->min(1))]), ['ids' => [1, 0]]);
        $this->assertArrayHasKey('ids[1]', $erros, 'O erro de item aponta o índice.');

        $par = Schema::inteiro()->regra(static fn (int $v): ?string => $v % 2 === 0 ? null : 'Use um número par.');
        [, $erros] = $this->validar($par, 3);
        $this->assertSame('Use um número par.', $erros['']);
    }

    public function testAnulavelAceitaNulo(): void
    {
        [$saida, $erros] = $this->validar(Schema::data()->anulavel(), null);

        $this->assertNull($saida);
        $this->assertSame([], $erros);
    }

    public function testSchemaNomeadoViraReferenciaEModificadorNaoAlteraOOriginal(): void
    {
        $base = Schema::objeto(['id' => Schema::inteiro()->obrigatorio()])->nome('Coisa');
        $anulavel = $base->anulavel()->descricao('Pode faltar.');

        $componentes = new Componentes();
        $referencia = $anulavel->paraOpenApi($componentes);
        $original = $base->paraOpenApi($componentes);

        $this->assertSame(['$ref' => '#/components/schemas/Coisa'], $original);
        $this->assertSame([['$ref' => '#/components/schemas/Coisa'], ['type' => 'null']], $referencia['anyOf']);
        $this->assertSame('Pode faltar.', $referencia['description']);
        $this->assertSame('object', $componentes->paraOpenApi()['Coisa']['type'], 'O componente continua não anulável.');
    }

    public function testOpenApiDescreveRestricoesDaValidacao(): void
    {
        $schema = Schema::objeto([
            'nome' => Schema::texto()->obrigatorio()->max(80),
            'valor' => Schema::decimal(),
            'tipo' => Schema::enum(['a', 'b'])->padrao('a'),
        ]);

        $saida = $schema->paraOpenApi(new Componentes());

        $this->assertSame(['nome'], $saida['required']);
        $this->assertSame(80, $saida['properties']['nome']['maxLength']);
        $this->assertSame('decimal', $saida['properties']['valor']['format']);
        $this->assertSame(['a', 'b'], $saida['properties']['tipo']['enum']);
        $this->assertSame('a', $saida['properties']['tipo']['default']);
    }

    public function testErroDeValidacaoSegueARfc9457(): void
    {
        $corpo = HttpErro::validacao(['email' => 'Informe um e-mail válido.'])->corpo();

        $this->assertSame('urn:mapos:erro:validacao', $corpo['type']);
        $this->assertSame(422, $corpo['status']);
        $this->assertSame('validacao', $corpo['codigo']);
        $this->assertSame(['email' => 'Informe um e-mail válido.'], $corpo['erros']);
    }
}
