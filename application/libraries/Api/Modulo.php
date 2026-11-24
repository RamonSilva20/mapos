<?php

namespace Libraries\Api;

use Closure;
use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Roteamento\Rota;
use Throwable;

abstract class Modulo
{
    protected object $db;

    public function __construct(protected readonly Contexto $ctx)
    {
        $this->db = $ctx->db();
    }

    /** @return list<Rota> */
    abstract public static function rotas(): array;

    /** @param  array<string, mixed>  $filtros */
    protected function listarPeloModelo(object $modelo, array $filtros, callable $serializar): Resposta
    {
        $pagina = $this->ctx->paginacao();
        $filtros = array_filter($filtros, static fn ($v) => $v !== null && $v !== '');
        $total = (int) $modelo->contar($filtros);
        $linhas = $modelo->listar($filtros, $pagina['por_pagina'], $pagina['deslocamento']);

        return Resposta::lista(array_map($serializar, $linhas), ['pagina' => $pagina['pagina'], 'por_pagina' => $pagina['por_pagina'], 'total' => $total]);
    }

    protected function listarPelaConsulta(\Closure $consulta, string $ordem, callable $serializar): Resposta
    {
        $pagina = $this->ctx->paginacao();
        $total = (int) $consulta()->count_all_results();
        $linhas = $consulta()->order_by($ordem, 'DESC')->limit($pagina['por_pagina'], $pagina['deslocamento'])->get()->result();

        return Resposta::lista(array_map($serializar, $linhas), ['pagina' => $pagina['pagina'], 'por_pagina' => $pagina['por_pagina'], 'total' => $total]);
    }

    protected function encontrar(string $tabela, string $chave, int $id, string $mensagem = 'Registro não encontrado.'): object
    {
        $linha = $id > 0 ? $this->db->where($chave, $id)->limit(1)->get($tabela)->row() : null;
        if (! $linha) {
            throw HttpErro::naoEncontrado($mensagem);
        }

        return $linha;
    }

    protected function existe(string $tabela, string $chave, mixed $valor, ?int $exceto = null, ?string $chaveId = null): bool
    {
        $this->db->where($chave, $valor);
        if ($exceto !== null && $chaveId !== null) {
            $this->db->where($chaveId . ' !=', $exceto);
        }

        return $this->db->count_all_results($tabela) > 0;
    }

    /**
     * @template T
     * @param  Closure(): T  $operacao
     * @return T
     */
    protected function transacao(Closure $operacao): mixed
    {
        $this->db->trans_begin();
        try {
            $resultado = $operacao();
        } catch (Throwable $e) {
            $this->db->trans_rollback();

            throw $e;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            throw HttpErro::interno('Não foi possível gravar. Tente novamente.');
        }
        $this->db->trans_commit();

        return $resultado;
    }

    protected function inserirLinha(string $tabela, array $dados): int
    {
        if (! $this->db->insert($tabela, $dados)) {
            throw HttpErro::interno('Não foi possível gravar. Tente novamente.');
        }

        return (int) $this->db->insert_id();
    }

    protected function atualizarLinha(string $tabela, string $chave, int $id, array $dados): void
    {
        if ($dados !== [] && ! $this->db->where($chave, $id)->update($tabela, $dados)) {
            throw HttpErro::interno('Não foi possível gravar. Tente novamente.');
        }
    }

    protected function removerLinha(string $tabela, string $chave, int $id): void
    {
        if (! $this->db->where($chave, $id)->delete($tabela)) {
            throw HttpErro::interno('Não foi possível excluir. Tente novamente.');
        }
    }

    /**
     * @param  array<string, string>  $errosDoFormulario
     * @param  array<string, string>  $mapa  campo do formulário => campo da API
     * @return array<string, string>
     */
    protected function traduzirErros(array $errosDoFormulario, array $mapa): array
    {
        $erros = [];
        foreach ($errosDoFormulario as $campo => $mensagem) {
            $erros[$mapa[$campo] ?? ($campo === '_geral' ? '_geral' : $campo)] = (string) $mensagem;
        }

        return $erros;
    }
}
