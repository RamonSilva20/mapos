<?php

namespace Libraries\Api\Http;

use RuntimeException;

final class HttpErro extends RuntimeException
{
    /**
     * @param  array<string, string>  $erros
     * @param  array<string, string>  $cabecalhos
     */
    public function __construct(
        public readonly int $status,
        public readonly string $codigo,
        public readonly string $titulo,
        public readonly ?string $detalhe = null,
        public readonly array $erros = [],
        public readonly array $cabecalhos = [],
    ) {
        parent::__construct($detalhe ?? $titulo, $status);
    }

    public static function requisicaoInvalida(string $detalhe, string $codigo = 'requisicao_invalida'): self
    {
        return new self(400, $codigo, 'Requisição inválida', $detalhe);
    }

    public static function naoAutenticado(string $detalhe = 'Envie um access token válido no cabeçalho Authorization.', string $codigo = 'nao_autenticado'): self
    {
        return new self(401, $codigo, 'Não autenticado', $detalhe, [], ['WWW-Authenticate' => 'Bearer realm="mapos"']);
    }

    public static function proibido(string $detalhe = 'Você não tem permissão para esta operação.', string $codigo = 'sem_permissao'): self
    {
        return new self(403, $codigo, 'Acesso negado', $detalhe);
    }

    public static function naoEncontrado(string $detalhe = 'Recurso não encontrado.'): self
    {
        return new self(404, 'nao_encontrado', 'Não encontrado', $detalhe);
    }

    /** @param  list<string>  $permitidos */
    public static function metodoNaoPermitido(array $permitidos): self
    {
        return new self(405, 'metodo_nao_permitido', 'Método não permitido', 'Métodos aceitos nesta rota: ' . implode(', ', $permitidos) . '.', [], ['Allow' => implode(', ', $permitidos)]);
    }

    public static function conflito(string $detalhe, string $codigo = 'conflito'): self
    {
        return new self(409, $codigo, 'Conflito', $detalhe);
    }

    public static function muitoGrande(string $detalhe): self
    {
        return new self(413, 'conteudo_muito_grande', 'Conteúdo muito grande', $detalhe);
    }

    public static function tipoNaoSuportado(string $detalhe): self
    {
        return new self(415, 'tipo_nao_suportado', 'Tipo de conteúdo não suportado', $detalhe);
    }

    /** @param  array<string, string>  $erros */
    public static function validacao(array $erros, string $detalhe = 'Confira os campos informados.'): self
    {
        return new self(422, 'validacao', 'Dados inválidos', $detalhe, $erros);
    }

    public static function regraDeNegocio(string $detalhe, string $codigo = 'regra_de_negocio'): self
    {
        return new self(422, $codigo, 'Operação não permitida', $detalhe);
    }

    public static function muitasRequisicoes(int $segundos, string $detalhe = 'Muitas requisições. Aguarde e tente novamente.'): self
    {
        return new self(429, 'muitas_requisicoes', 'Muitas requisições', $detalhe, [], ['Retry-After' => (string) max(1, $segundos)]);
    }

    public static function indisponivel(string $detalhe, string $codigo = 'indisponivel'): self
    {
        return new self(503, $codigo, 'Serviço indisponível', $detalhe);
    }

    public static function interno(string $detalhe = 'Erro interno. Tente novamente; se persistir, avise o administrador.'): self
    {
        return new self(500, 'erro_interno', 'Erro interno', $detalhe);
    }

    /** @return array<string, mixed> */
    public function corpo(): array
    {
        $corpo = [
            'type' => 'urn:mapos:erro:' . $this->codigo,
            'title' => $this->titulo,
            'status' => $this->status,
            'codigo' => $this->codigo,
        ];
        if ($this->detalhe !== null) {
            $corpo['detail'] = $this->detalhe;
        }
        if ($this->erros !== []) {
            $corpo['erros'] = $this->erros;
        }

        return $corpo;
    }
}
