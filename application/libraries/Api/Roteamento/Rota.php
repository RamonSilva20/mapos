<?php

namespace Libraries\Api\Roteamento;

use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;

final class Rota
{
    public const USUARIO = 'usuario';

    public const CLIENTE = 'cliente';

    public ?string $autenticacao = self::USUARIO;

    /** @var array{tipo: 'qualquer'|'todas', permissoes: list<string>}|null */
    public ?array $permissao = null;

    public string $resumo = '';

    public string $descricao = '';

    public string $tag = '';

    public ?Schema $consulta = null;

    public ?Schema $corpo = null;

    public bool $multipart = false;

    /** @var array<int, array{descricao: string, schema: ?Schema, tipo: string}> */
    public array $respostas = [];

    /** @var list<int> */
    public array $erros = [];

    public bool $liberadaCom2faPendente = false;

    public bool $liberadaComBancoDesatualizado = false;

    public ?string $limite = null;

    public string $operacao = '';

    /** @param  array{0: class-string, 1: string}  $acao */
    private function __construct(
        public readonly string $metodo,
        public readonly string $caminho,
        public readonly array $acao,
    ) {
        $this->operacao = lcfirst(substr(strrchr($acao[0], '\\') ?: $acao[0], 1)) . ucfirst($acao[1]);
    }

    /** @param  array{0: class-string, 1: string}  $acao */
    public static function get(string $caminho, array $acao): self
    {
        return new self('GET', $caminho, $acao);
    }

    /** @param  array{0: class-string, 1: string}  $acao */
    public static function post(string $caminho, array $acao): self
    {
        return new self('POST', $caminho, $acao);
    }

    /** @param  array{0: class-string, 1: string}  $acao */
    public static function put(string $caminho, array $acao): self
    {
        return new self('PUT', $caminho, $acao);
    }

    /** @param  array{0: class-string, 1: string}  $acao */
    public static function patch(string $caminho, array $acao): self
    {
        return new self('PATCH', $caminho, $acao);
    }

    /** @param  array{0: class-string, 1: string}  $acao */
    public static function delete(string $caminho, array $acao): self
    {
        return new self('DELETE', $caminho, $acao);
    }

    public function tag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function resumo(string $resumo, string $descricao = ''): self
    {
        $this->resumo = $resumo;
        $this->descricao = $descricao;

        return $this;
    }

    public function publica(): self
    {
        $this->autenticacao = null;

        return $this;
    }

    public function doCliente(): self
    {
        $this->autenticacao = self::CLIENTE;

        return $this;
    }

    public function permissao(string ...$permissoes): self
    {
        $this->permissao = ['tipo' => 'qualquer', 'permissoes' => array_values($permissoes)];

        return $this;
    }

    public function todasAsPermissoes(string ...$permissoes): self
    {
        $this->permissao = ['tipo' => 'todas', 'permissoes' => array_values($permissoes)];

        return $this;
    }

    public function consulta(Schema $schema): self
    {
        $this->consulta = $schema;

        return $this;
    }

    public function corpo(Schema $schema, bool $multipart = false): self
    {
        $this->corpo = $schema;
        $this->multipart = $multipart;

        return $this;
    }

    public function resposta(int $status, ?Schema $schema = null, string $descricao = '', string $tipo = 'application/json'): self
    {
        $this->respostas[$status] = ['descricao' => $descricao, 'schema' => $schema, 'tipo' => $tipo];

        return $this;
    }

    public function dados(Schema $schema, string $descricao = 'Sucesso.', int $status = 200): self
    {
        return $this->resposta($status, Schema::objeto(['data' => $schema->obrigatorio()]), $descricao);
    }

    public function lista(Schema $item, string $descricao = 'Página da listagem.'): self
    {
        return $this->resposta(200, Schema::objeto([
            'data' => Schema::lista($item)->obrigatorio(),
            'meta' => Comuns::paginacao()->obrigatorio(),
        ]), $descricao);
    }

    public function criado(Schema $schema, string $descricao = 'Criado.'): self
    {
        return $this->dados($schema, $descricao, 201);
    }

    public function semConteudo(string $descricao = 'Feito.'): self
    {
        return $this->resposta(204, null, $descricao);
    }

    public function arquivo(string $tipo = 'application/octet-stream', string $descricao = 'O arquivo.'): self
    {
        return $this->resposta(200, Schema::arquivo(), $descricao, $tipo);
    }

    public function erros(int ...$status): self
    {
        $this->erros = array_values(array_unique(array_merge($this->erros, $status)));

        return $this;
    }

    public function liberadaCom2faPendente(): self
    {
        $this->liberadaCom2faPendente = true;

        return $this;
    }

    public function liberadaComBancoDesatualizado(): self
    {
        $this->liberadaComBancoDesatualizado = true;

        return $this;
    }

    public function limite(string $balde): self
    {
        $this->limite = $balde;

        return $this;
    }

    public function expressao(): string
    {
        $padrao = preg_replace_callback('/\{([a-z_]+)(?::(s))?\}/', static function (array $m): string {
            return '(?P<' . $m[1] . '>' . (($m[2] ?? '') === 's' ? '[A-Za-z0-9_-]+' : '[1-9][0-9]{0,9}') . ')';
        }, $this->caminho);

        return '#^' . $padrao . '$#';
    }

    public function caminhoOpenApi(): string
    {
        return (string) preg_replace('/\{([a-z_]+)(?::s)?\}/', '{$1}', $this->caminho);
    }

    /** @return list<string> */
    public function parametrosDoCaminho(): array
    {
        preg_match_all('/\{([a-z_]+)(?::(s))?\}/', $this->caminho, $m);

        return $m[1];
    }

    public function parametroTextual(string $nome): bool
    {
        return str_contains($this->caminho, '{' . $nome . ':s}');
    }
}
