<?php

namespace Libraries\Api\Roteamento;

use Libraries\Api\Http\HttpErro;
use LogicException;

final class Roteador
{
    /** @var list<Rota> */
    private array $rotas = [];

    /** @param  iterable<Rota>  $rotas */
    public function __construct(iterable $rotas = [])
    {
        foreach ($rotas as $rota) {
            $this->adicionar($rota);
        }
    }

    public function adicionar(Rota $rota): void
    {
        foreach ($this->rotas as $existente) {
            if ($existente->metodo === $rota->metodo && $existente->caminho === $rota->caminho) {
                throw new LogicException("Rota repetida: {$rota->metodo} {$rota->caminho}");
            }
        }
        $this->rotas[] = $rota;
    }

    /** @return list<Rota> */
    public function rotas(): array
    {
        return $this->rotas;
    }

    /** @return array{0: Rota, 1: array<string, int|string>} */
    public function encontrar(string $metodo, string $caminho): array
    {
        $caminho = '/' . trim($caminho, '/');
        $metodo = $metodo === 'HEAD' ? 'GET' : $metodo;
        $permitidos = [];

        foreach ($this->rotas as $rota) {
            if (! preg_match($rota->expressao(), $caminho, $m)) {
                continue;
            }
            if ($rota->metodo !== $metodo) {
                $permitidos[] = $rota->metodo;

                continue;
            }

            $parametros = [];
            foreach ($rota->parametrosDoCaminho() as $nome) {
                $parametros[$nome] = $rota->parametroTextual($nome) ? (string) $m[$nome] : (int) $m[$nome];
            }

            return [$rota, $parametros];
        }

        if ($permitidos !== []) {
            throw HttpErro::metodoNaoPermitido(array_values(array_unique($permitidos)));
        }

        throw HttpErro::naoEncontrado('Rota inexistente: ' . $metodo . ' ' . $caminho);
    }

    /** @return list<string> */
    public function metodosDoCaminho(string $caminho): array
    {
        $caminho = '/' . trim($caminho, '/');
        $metodos = [];
        foreach ($this->rotas as $rota) {
            if (preg_match($rota->expressao(), $caminho)) {
                $metodos[] = $rota->metodo;
            }
        }

        return array_values(array_unique($metodos));
    }
}
