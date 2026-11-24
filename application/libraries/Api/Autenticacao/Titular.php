<?php

namespace Libraries\Api\Autenticacao;

final class Titular
{
    /** @param  list<string>  $permissoes */
    public function __construct(
        public readonly string $tipo,
        public readonly int $id,
        public readonly string $nome,
        public readonly string $sessao,
        public readonly object $registro,
        public readonly array $permissoes = [],
        public readonly int $grupo = 0,
        public readonly bool $doisFatoresPendente = false,
    ) {
    }

    public function pode(string $permissao): bool
    {
        return in_array($permissao, $this->permissoes, true);
    }

    /** @param  list<string>  $permissoes */
    public function podeQualquer(array $permissoes): bool
    {
        foreach ($permissoes as $permissao) {
            if ($this->pode($permissao)) {
                return true;
            }
        }

        return false;
    }

    public function ehAdministrador(): bool
    {
        return $this->id === USUARIO_SUPER_ADMIN || $this->grupo === PERMISSAO_ADMIN;
    }
}
