<?php

namespace Libraries\Api\Schema;

use Closure;

final class Schema
{
    private string $tipo;

    private ?string $formato = null;

    private bool $obrigatorio = false;

    private bool $anulavel = false;

    private ?float $minimo = null;

    private ?float $maximo = null;

    /** @var list<string|int>|null */
    private ?array $enum = null;

    private ?Schema $itens = null;

    /** @var array<string, Schema> */
    private array $propriedades = [];

    private bool $temPadrao = false;

    private mixed $padrao = null;

    private ?string $descricao = null;

    private bool $temExemplo = false;

    private mixed $exemplo = null;

    /** @var list<Closure> */
    private array $regras = [];

    private ?string $nome = null;

    private ?string $expressao = null;

    private bool $somenteLeitura = false;

    private int $casas = 2;

    private ?Schema $adicionais = null;

    private ?Schema $base = null;

    private static bool $conferindoResposta = false;

    private function __construct(string $tipo)
    {
        $this->tipo = $tipo;
    }

    public static function texto(): self
    {
        return new self('string');
    }

    public static function inteiro(): self
    {
        return new self('integer');
    }

    public static function numero(): self
    {
        return new self('number');
    }

    public static function decimal(int $casas = 2): self
    {
        $schema = new self('decimal');
        $schema->casas = $casas;

        return $schema;
    }

    public static function booleano(): self
    {
        return new self('boolean');
    }

    public static function data(): self
    {
        $schema = new self('string');
        $schema->formato = 'date';

        return $schema;
    }

    public static function dataHora(): self
    {
        $schema = new self('string');
        $schema->formato = 'date-time';

        return $schema;
    }

    public static function email(): self
    {
        $schema = new self('string');
        $schema->formato = 'email';

        return $schema;
    }

    public static function senha(): self
    {
        $schema = new self('string');
        $schema->formato = 'password';

        return $schema;
    }

    public static function url(): self
    {
        $schema = new self('string');
        $schema->formato = 'uri';

        return $schema;
    }

    /** @param  list<string|int>  $valores */
    public static function enum(array $valores): self
    {
        $schema = new self(is_int($valores[0] ?? null) ? 'integer' : 'string');
        $schema->enum = $valores;

        return $schema;
    }

    public static function lista(Schema $itens): self
    {
        $schema = new self('array');
        $schema->itens = $itens;

        return $schema;
    }

    /** @param  array<string, Schema>  $propriedades */
    public static function objeto(array $propriedades = []): self
    {
        $schema = new self('object');
        $schema->propriedades = $propriedades;

        return $schema;
    }

    public static function mapa(Schema $valores): self
    {
        $schema = new self('object');
        $schema->adicionais = $valores;

        return $schema;
    }

    public static function arquivo(): self
    {
        $schema = new self('file');
        $schema->formato = 'binary';

        return $schema;
    }

    public static function qualquer(): self
    {
        return new self('any');
    }

    public function obrigatorio(bool $sim = true): self
    {
        $alvo = $this->alvo();
        $alvo->obrigatorio = $sim;

        return $alvo;
    }

    public function anulavel(bool $sim = true): self
    {
        $alvo = $this->alvo();
        $alvo->anulavel = $sim;

        return $alvo;
    }

    public function min(float $valor): self
    {
        $this->minimo = $valor;

        return $this;
    }

    public function max(float $valor): self
    {
        $this->maximo = $valor;

        return $this;
    }

    public function padrao(mixed $valor): self
    {
        $alvo = $this->alvo();
        $alvo->temPadrao = true;
        $alvo->padrao = $valor;

        return $alvo;
    }

    public function descricao(string $texto): self
    {
        $alvo = $this->alvo();
        $alvo->descricao = $texto;

        return $alvo;
    }

    public function exemplo(mixed $valor): self
    {
        $alvo = $this->alvo();
        $alvo->temExemplo = true;
        $alvo->exemplo = $valor;

        return $alvo;
    }

    private function alvo(): self
    {
        if ($this->nome === null) {
            return $this;
        }
        $copia = clone $this;
        $copia->base = $this->base ?? $this;

        return $copia;
    }

    public function regra(Closure $regra): self
    {
        $this->regras[] = $regra;

        return $this;
    }

    public function nome(string $nome): self
    {
        $this->nome = $nome;
        $this->base = null;

        return $this;
    }

    public function expressao(string $regex): self
    {
        $this->expressao = $regex;

        return $this;
    }

    public function somenteLeitura(bool $sim = true): self
    {
        $this->somenteLeitura = $sim;

        return $this;
    }

    /** @param  array<string, Schema>  $propriedades */
    public function com(array $propriedades): self
    {
        $copia = clone $this;
        $copia->nome = null;
        $copia->base = null;
        $copia->propriedades = array_merge($this->propriedades, $propriedades);

        return $copia;
    }

    /** @param  list<string>  $campos */
    public function sem(array $campos): self
    {
        $copia = clone $this;
        $copia->nome = null;
        $copia->base = null;
        $copia->propriedades = array_diff_key($this->propriedades, array_flip($campos));

        return $copia;
    }

    public function semObrigatorios(): self
    {
        $copia = clone $this;
        $copia->nome = null;
        $copia->base = null;
        foreach ($copia->propriedades as $campo => $schema) {
            $copia->propriedades[$campo] = (clone $schema)->obrigatorio(false);
        }

        return $copia;
    }

    public function ehObrigatorio(): bool
    {
        return $this->obrigatorio;
    }

    /** @return array<string, Schema> */
    public function propriedades(): array
    {
        return $this->propriedades;
    }

    public function tipo(): string
    {
        return $this->tipo;
    }

    /** @return array<string, string> */
    public function conferirResposta(mixed $valor): array
    {
        $erros = [];
        self::$conferindoResposta = true;
        try {
            $this->validar($valor, '', $erros);
        } finally {
            self::$conferindoResposta = false;
        }

        return $erros;
    }

    /**
     * @param  array<string, string>  $erros
     * @param  array<string, mixed>  $irmaos
     */
    public function validar(mixed $valor, string $caminho, array &$erros, bool $textual = false, array $irmaos = []): mixed
    {
        if ($valor === null || ($valor === '' && $this->tipo !== 'string')) {
            if ($this->anulavel) {
                return null;
            }
            if (! $this->obrigatorio) {
                return $this->tipo === 'string' ? '' : null;
            }
            $erros[$caminho] = 'Campo obrigatório.';

            return null;
        }

        $antes = count($erros);
        $convertido = match ($this->tipo) {
            'string' => $this->validarTexto($valor, $caminho, $erros),
            'integer' => $this->validarInteiro($valor, $caminho, $erros),
            'number' => $this->validarNumero($valor, $caminho, $erros),
            'decimal' => $this->validarDecimal($valor, $caminho, $erros),
            'boolean' => $this->validarBooleano($valor, $caminho, $erros, $textual),
            'array' => $this->validarLista($valor, $caminho, $erros, $textual),
            'object' => $this->validarObjeto($valor, $caminho, $erros, $textual),
            'file' => $this->validarArquivo($valor, $caminho, $erros),
            default => $valor,
        };

        if (count($erros) > $antes) {
            return null;
        }

        if ($this->enum !== null && ! in_array($convertido, $this->enum, true)) {
            $erros[$caminho] = 'Use um destes valores: ' . implode(', ', $this->enum) . '.';

            return null;
        }

        foreach ($this->regras as $regra) {
            $mensagem = $regra($convertido, $irmaos);
            if (is_string($mensagem) && $mensagem !== '') {
                $erros[$caminho] = $mensagem;

                return null;
            }
        }

        return $convertido;
    }

    /** @param  array<string, string>  $erros */
    private function validarTexto(mixed $valor, string $caminho, array &$erros): ?string
    {
        if (is_int($valor) || is_float($valor)) {
            $valor = (string) $valor;
        }
        if (! is_string($valor)) {
            $erros[$caminho] = 'Informe um texto.';

            return null;
        }

        $valor = $this->formato === 'password' ? $valor : trim($valor);
        if ($valor === '') {
            if ($this->obrigatorio && ! self::$conferindoResposta) {
                $erros[$caminho] = 'Campo obrigatório.';
            }

            return $this->anulavel ? null : '';
        }

        $tamanho = mb_strlen($valor);
        if ($this->minimo !== null && $tamanho < $this->minimo) {
            $erros[$caminho] = 'Use pelo menos ' . (int) $this->minimo . ' caracteres.';

            return null;
        }
        if ($this->maximo !== null && $tamanho > $this->maximo) {
            $erros[$caminho] = 'Use no máximo ' . (int) $this->maximo . ' caracteres.';

            return null;
        }
        if ($this->expressao !== null && ! preg_match($this->expressao, $valor)) {
            $erros[$caminho] = 'Formato inválido.';

            return null;
        }

        return match ($this->formato) {
            'email' => filter_var($valor, FILTER_VALIDATE_EMAIL) !== false ? $valor : $this->falhar($erros, $caminho, 'Informe um e-mail válido.'),
            'date' => self::dataValida($valor) ?? $this->falhar($erros, $caminho, 'Informe uma data válida no formato AAAA-MM-DD.'),
            'date-time' => self::dataHoraValida($valor) ?? $this->falhar($erros, $caminho, 'Informe data e hora válidas (ISO 8601).'),
            'uri' => filter_var($valor, FILTER_VALIDATE_URL) !== false && preg_match('#^https?://#i', $valor) ? $valor : $this->falhar($erros, $caminho, 'Informe uma URL http(s) válida.'),
            default => $valor,
        };
    }

    /** @param  array<string, string>  $erros */
    private function falhar(array &$erros, string $caminho, string $mensagem): null
    {
        $erros[$caminho] = $mensagem;

        return null;
    }

    public static function dataValida(string $valor): ?string
    {
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $m) || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return null;
        }

        return $valor;
    }

    public static function dataHoraValida(string $valor): ?string
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?(\.\d+)?(Z|[+-]\d{2}:?\d{2})?$/', $valor)) {
            return null;
        }
        $data = date_create($valor);

        return $data === false ? null : $data->format('Y-m-d H:i:s');
    }

    /** @param  array<string, string>  $erros */
    private function validarInteiro(mixed $valor, string $caminho, array &$erros): ?int
    {
        if (is_string($valor) && preg_match('/^-?\d{1,18}$/', trim($valor))) {
            $valor = (int) trim($valor);
        }
        if (is_float($valor) && floor($valor) === $valor && abs($valor) < PHP_INT_MAX) {
            $valor = (int) $valor;
        }
        if (! is_int($valor)) {
            $erros[$caminho] = 'Informe um número inteiro.';

            return null;
        }

        return $this->conferirFaixa($valor, $caminho, $erros) ? $valor : null;
    }

    /** @param  array<string, string>  $erros */
    private function validarNumero(mixed $valor, string $caminho, array &$erros): ?float
    {
        if (is_string($valor) && is_numeric(trim($valor))) {
            $valor = (float) trim($valor);
        }
        if (! is_int($valor) && ! is_float($valor)) {
            $erros[$caminho] = 'Informe um número.';

            return null;
        }
        $valor = (float) $valor;

        return $this->conferirFaixa($valor, $caminho, $erros) ? $valor : null;
    }

    /** @param  array<string, string>  $erros */
    private function validarDecimal(mixed $valor, string $caminho, array &$erros): ?string
    {
        if (is_string($valor)) {
            $limpo = trim($valor);
            if (preg_match('/^-?\d{1,3}(\.\d{3})+,\d+$/', $limpo) || preg_match('/^-?\d+,\d+$/', $limpo)) {
                $limpo = str_replace(['.', ','], ['', '.'], $limpo);
            }
            $valor = is_numeric($limpo) && ! preg_match('/[eE]/', $limpo) ? $limpo : null;
        }
        if (! is_int($valor) && ! is_float($valor) && ! is_string($valor)) {
            $erros[$caminho] = 'Informe um valor numérico, como 150.00.';

            return null;
        }

        $numero = round((float) $valor, $this->casas);
        if (! is_finite($numero) || abs($numero) >= 1e10) {
            $erros[$caminho] = 'Valor fora do limite permitido.';

            return null;
        }

        return $this->conferirFaixa($numero, $caminho, $erros) ? number_format($numero, $this->casas, '.', '') : null;
    }

    /** @param  array<string, string>  $erros */
    private function conferirFaixa(int|float $valor, string $caminho, array &$erros): bool
    {
        if ($this->minimo !== null && $valor < $this->minimo) {
            $erros[$caminho] = 'O valor mínimo é ' . self::numeroLegivel($this->minimo) . '.';

            return false;
        }
        if ($this->maximo !== null && $valor > $this->maximo) {
            $erros[$caminho] = 'O valor máximo é ' . self::numeroLegivel($this->maximo) . '.';

            return false;
        }

        return true;
    }

    private static function numeroLegivel(float $valor): string
    {
        return floor($valor) === $valor ? (string) (int) $valor : (string) $valor;
    }

    /** @param  array<string, string>  $erros */
    private function validarBooleano(mixed $valor, string $caminho, array &$erros, bool $textual): ?bool
    {
        if (is_bool($valor)) {
            return $valor;
        }
        if ($valor === 0 || $valor === 1) {
            return (bool) $valor;
        }
        if (is_string($valor)) {
            $normalizado = strtolower(trim($valor));
            if (in_array($normalizado, ['true', '1'], true)) {
                return true;
            }
            if (in_array($normalizado, ['false', '0'], true)) {
                return false;
            }
        }
        $erros[$caminho] = 'Informe true ou false.';

        return null;
    }

    /** @param  array<string, string>  $erros */
    private function validarLista(mixed $valor, string $caminho, array &$erros, bool $textual): ?array
    {
        if (! is_array($valor) || ! array_is_list($valor)) {
            $erros[$caminho] = 'Informe uma lista.';

            return null;
        }
        if ($this->minimo !== null && count($valor) < $this->minimo) {
            $erros[$caminho] = 'Informe pelo menos ' . (int) $this->minimo . ' item(ns).';

            return null;
        }
        if ($this->maximo !== null && count($valor) > $this->maximo) {
            $erros[$caminho] = 'Informe no máximo ' . (int) $this->maximo . ' itens.';

            return null;
        }

        $saida = [];
        foreach ($valor as $indice => $item) {
            $saida[] = $this->itens === null ? $item : $this->itens->validar($item, $caminho . '[' . $indice . ']', $erros, $textual);
        }

        return $saida;
    }

    /** @param  array<string, string>  $erros */
    private function validarObjeto(mixed $valor, string $caminho, array &$erros, bool $textual): ?array
    {
        if (! is_array($valor) || ($valor !== [] && array_is_list($valor))) {
            $erros[$caminho === '' ? '_corpo' : $caminho] = 'Informe um objeto.';

            return null;
        }

        if ($this->adicionais !== null) {
            $saida = [];
            foreach ($valor as $chave => $item) {
                $saida[(string) $chave] = $this->adicionais->validar($item, ltrim($caminho . '.' . $chave, '.'), $erros, $textual);
            }

            return $saida;
        }

        $saida = [];
        foreach ($this->propriedades as $campo => $schema) {
            $caminhoCampo = $caminho === '' ? $campo : $caminho . '.' . $campo;
            if (! array_key_exists($campo, $valor)) {
                if ($schema->obrigatorio) {
                    $erros[$caminhoCampo] = 'Campo obrigatório.';
                } elseif ($schema->temPadrao) {
                    $saida[$campo] = $schema->padrao;
                }

                continue;
            }
            if ($schema->somenteLeitura) {
                continue;
            }
            $saida[$campo] = $schema->validar($valor[$campo], $caminhoCampo, $erros, $textual, $valor);
        }

        return $saida;
    }

    /** @param  array<string, string>  $erros */
    private function validarArquivo(mixed $valor, string $caminho, array &$erros): ?array
    {
        if (! is_array($valor) || ! isset($valor['tmp_name'], $valor['error'])) {
            $erros[$caminho] = 'Envie um arquivo (multipart/form-data).';

            return null;
        }
        if ((int) $valor['error'] === UPLOAD_ERR_INI_SIZE || (int) $valor['error'] === UPLOAD_ERR_FORM_SIZE) {
            $erros[$caminho] = 'O arquivo é maior que o limite do servidor.';

            return null;
        }
        if ((int) $valor['error'] !== UPLOAD_ERR_OK) {
            $erros[$caminho] = 'O arquivo não chegou completo. Envie de novo.';

            return null;
        }

        return $valor;
    }

    /**
     * @param  Componentes  $componentes
     * @return array<string, mixed>
     */
    public function paraOpenApi(Componentes $componentes, bool $expandir = false): array
    {
        if ($this->nome !== null && ! $expandir) {
            $base = $this->base ?? $this;
            $componentes->registrar($this->nome, $base);
            $referencia = ['$ref' => '#/components/schemas/' . $this->nome];
            if ($this->anulavel && ! $base->anulavel) {
                $referencia = ['anyOf' => [$referencia, ['type' => 'null']]];
            }
            if ($this->descricao !== null && $this->descricao !== $base->descricao) {
                $referencia['description'] = $this->descricao;
            }

            return $referencia;
        }

        $saida = match ($this->tipo) {
            'decimal' => ['type' => 'string', 'format' => 'decimal', 'pattern' => '^-?\d+(\.\d{' . $this->casas . '})?$'],
            'file' => ['type' => 'string', 'format' => 'binary'],
            'any' => [],
            default => ['type' => $this->tipo],
        };

        if ($this->formato !== null && $this->tipo === 'string') {
            $saida['format'] = $this->formato;
        }
        if ($this->anulavel && isset($saida['type'])) {
            $saida['type'] = [$saida['type'], 'null'];
        }
        if ($this->descricao !== null) {
            $saida['description'] = $this->descricao;
        }
        if ($this->enum !== null) {
            $saida['enum'] = $this->enum;
        }
        if ($this->expressao !== null) {
            $saida['pattern'] = trim($this->expressao, '/');
        }

        [$chaveMin, $chaveMax] = match ($this->tipo) {
            'string' => ['minLength', 'maxLength'],
            'array' => ['minItems', 'maxItems'],
            default => ['minimum', 'maximum'],
        };
        if ($this->minimo !== null) {
            $saida[$chaveMin] = $this->tipo === 'number' || $this->tipo === 'decimal' ? $this->minimo : (int) $this->minimo;
        }
        if ($this->maximo !== null) {
            $saida[$chaveMax] = $this->tipo === 'number' || $this->tipo === 'decimal' ? $this->maximo : (int) $this->maximo;
        }
        if ($this->tipo === 'decimal') {
            unset($saida['minimum'], $saida['maximum']);
            $limites = array_filter(['mínimo ' . $this->minimo => $this->minimo !== null, 'máximo ' . $this->maximo => $this->maximo !== null]);
            if ($limites !== []) {
                $saida['description'] = trim(($saida['description'] ?? '') . ' Valor ' . implode(', ', array_keys($limites)) . '.');
            }
        }
        if ($this->temPadrao && $this->padrao !== null) {
            $saida['default'] = $this->padrao;
        }
        if ($this->temExemplo) {
            $saida['examples'] = [$this->exemplo];
        }
        if ($this->somenteLeitura) {
            $saida['readOnly'] = true;
        }

        if ($this->tipo === 'array' && $this->itens !== null) {
            $saida['items'] = $this->itens->paraOpenApi($componentes);
        }

        if ($this->tipo === 'object') {
            if ($this->adicionais !== null) {
                $saida['additionalProperties'] = $this->adicionais->paraOpenApi($componentes);
            } else {
                $propriedades = [];
                $obrigatorios = [];
                foreach ($this->propriedades as $campo => $schema) {
                    $propriedades[$campo] = $schema->paraOpenApi($componentes);
                    if ($schema->obrigatorio) {
                        $obrigatorios[] = $campo;
                    }
                }
                if ($propriedades !== []) {
                    $saida['properties'] = $propriedades;
                }
                if ($obrigatorios !== []) {
                    $saida['required'] = $obrigatorios;
                }
            }
        }

        return $saida;
    }

    /** @return list<array<string, mixed>> */
    public function parametrosDeConsulta(Componentes $componentes): array
    {
        $parametros = [];
        foreach ($this->propriedades as $campo => $schema) {
            $definicao = $schema->paraOpenApi($componentes);
            $descricao = $definicao['description'] ?? null;
            unset($definicao['description']);
            $parametros[] = array_filter([
                'name' => $campo,
                'in' => 'query',
                'required' => $schema->obrigatorio ?: null,
                'description' => $descricao,
                'schema' => $definicao,
            ], static fn ($v) => $v !== null);
        }

        return $parametros;
    }
}
