<?php

namespace Libraries\Api;

use Libraries\Api\Autenticacao\Sessoes;
use Libraries\Api\Autenticacao\Titular;
use Libraries\Api\Autenticacao\Tokens;
use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Requisicao;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Schema;
use LogicException;

final class Contexto
{
    public const CONFIGURACAO_PADRAO = [
        'per_page' => 10,
        'app_name' => 'Map-OS',
        'os_notification' => 'cliente',
        'control_estoque' => '1',
        'notifica_whats' => '',
        'control_baixa' => '0',
        'control_editos' => '1',
        'pix_key' => '',
    ];

    private ?Titular $titular = null;

    private ?array $corpo = null;

    private ?array $consulta = null;

    private ?array $configuracoes = null;

    /** @param  array<string, int|string>  $parametros */
    public function __construct(
        public readonly object $ci,
        public readonly Requisicao $requisicao,
        public readonly Rota $rota,
        private readonly array $parametros,
        public readonly Configuracao $configuracao,
        public readonly Tokens $tokens,
        public readonly Sessoes $sessoes,
    ) {
    }

    public function db(): object
    {
        return $this->ci->db;
    }

    public function definirTitular(Titular $titular): void
    {
        $this->titular = $titular;
    }

    public function titular(): Titular
    {
        if ($this->titular === null) {
            throw new LogicException('Rota pública não tem titular.');
        }

        return $this->titular;
    }

    public function temTitular(): bool
    {
        return $this->titular !== null;
    }

    public function parametro(string $nome): int|string
    {
        if (! array_key_exists($nome, $this->parametros)) {
            throw new LogicException("Parâmetro {$nome} não existe na rota.");
        }

        return $this->parametros[$nome];
    }

    public function id(string $nome = 'id'): int
    {
        return (int) $this->parametro($nome);
    }

    /** @return array<string, mixed> */
    public function corpo(): array
    {
        if ($this->corpo === null) {
            if ($this->rota->corpo === null) {
                throw new LogicException('A rota não declara corpo.');
            }
            $this->corpo = $this->validar($this->rota->corpo, $this->requisicao->corpo(), $this->rota->multipart);
        }

        return $this->corpo;
    }

    /**
     * Corpo de um PATCH: o que veio sobrepõe o estado atual e o resultado passa pela validação completa.
     *
     * @param  array<string, mixed>  $atual
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    public function corpoParcial(array $atual): array
    {
        if ($this->rota->corpo === null) {
            throw new LogicException('A rota não declara corpo.');
        }
        $enviado = $this->requisicao->corpo();
        $conhecidos = array_intersect(array_keys($enviado), array_keys($this->rota->corpo->propriedades()));
        $this->corpo = $this->validar($this->rota->corpo, array_replace($atual, $enviado), $this->rota->multipart);

        return [$this->corpo, array_values($conhecidos)];
    }

    /** @return array<string, mixed> */
    public function consulta(): array
    {
        if ($this->consulta === null) {
            $this->consulta = $this->rota->consulta === null ? [] : $this->validar($this->rota->consulta, $this->requisicao->consulta, true);
        }

        return $this->consulta;
    }

    /** @return array{pagina: int, por_pagina: int, deslocamento: int} */
    public function paginacao(): array
    {
        $consulta = $this->consulta();
        $pagina = max(1, (int) ($consulta['pagina'] ?? 1));
        $porPagina = max(1, min(100, (int) ($consulta['por_pagina'] ?? 20)));

        return ['pagina' => $pagina, 'por_pagina' => $porPagina, 'deslocamento' => ($pagina - 1) * $porPagina];
    }

    /**
     * @param  array<string, mixed>  $dados
     * @return array<string, mixed>
     */
    public function validar(Schema $schema, array $dados, bool $textual = false): array
    {
        $erros = [];
        $valido = $schema->validar($dados, '', $erros, $textual);
        if ($erros !== []) {
            throw HttpErro::validacao($erros);
        }

        return (array) $valido;
    }

    public function pode(string $permissao): bool
    {
        return $this->titular !== null && $this->titular->pode($permissao);
    }

    public function exigir(string ...$permissoes): void
    {
        if ($this->titular === null || ! $this->titular->podeQualquer($permissoes)) {
            throw HttpErro::proibido();
        }
    }

    public function configuracao(string $chave, mixed $padrao = null): mixed
    {
        if ($this->configuracoes === null) {
            $this->configuracoes = self::CONFIGURACAO_PADRAO;
            foreach ($this->db()->get('configuracoes')->result() as $linha) {
                $this->configuracoes[$linha->config] = $linha->valor;
            }
        }

        return $this->configuracoes[$chave] ?? $padrao;
    }

    /** @return array<string, mixed> */
    public function configuracoes(): array
    {
        $this->configuracao('app_name');

        return $this->configuracoes;
    }

    public function auditar(string $tarefa): void
    {
        $this->db()->insert('logs', [
            'usuario' => mb_substr($this->titular !== null ? $this->titular->nome : 'API', 0, 80),
            'ip' => $this->requisicao->ip,
            'tarefa' => mb_substr($tarefa, 0, 100),
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
        ]);
    }

    public function url(string $caminho): string
    {
        return $this->configuracao->emissor . '/api/v2/' . ltrim($caminho, '/');
    }

    public function modelo(string $nome): object
    {
        $propriedade = strtolower($nome);
        if (! isset($this->ci->{$propriedade})) {
            $this->ci->load->model($nome, $propriedade);
        }

        return $this->ci->{$propriedade};
    }

    public function biblioteca(string $nome, mixed $parametros = null): object
    {
        $propriedade = strtolower($nome);
        if (! isset($this->ci->{$propriedade})) {
            $this->ci->load->library($nome, $parametros);
        }

        return $this->ci->{$propriedade};
    }

    public function helper(string ...$nomes): void
    {
        $this->ci->load->helper($nomes);
    }
}
