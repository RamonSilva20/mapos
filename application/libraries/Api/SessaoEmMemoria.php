<?php

namespace Libraries\Api;

final class SessaoEmMemoria
{
    /** @var array<string, mixed> */
    private array $dados = [];

    public function userdata($chave = null)
    {
        return $chave === null ? $this->dados : ($this->dados[$chave] ?? null);
    }

    public function set_userdata($dados, $valor = null): void
    {
        if (is_array($dados)) {
            $this->dados = array_merge($this->dados, $dados);

            return;
        }
        $this->dados[(string) $dados] = $valor;
    }

    public function unset_userdata($chaves): void
    {
        foreach ((array) $chaves as $chave) {
            unset($this->dados[$chave]);
        }
    }

    public function has_userdata($chave): bool
    {
        return array_key_exists($chave, $this->dados);
    }

    public function set_flashdata($dados, $valor = null): void
    {
    }

    public function flashdata($chave = null)
    {
        return null;
    }

    public function sess_regenerate($destruir = false): void
    {
    }

    public function sess_destroy(): void
    {
        $this->dados = [];
    }
}
