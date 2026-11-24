<?php

namespace Libraries\Api\Schema;

use LogicException;

final class Componentes
{
    /** @var array<string, Schema> */
    private array $schemas = [];

    /** @var array<string, array<string, mixed>> */
    private array $gerados = [];

    public function registrar(string $nome, Schema $schema): void
    {
        if (isset($this->schemas[$nome])) {
            if ($this->schemas[$nome] !== $schema && $this->schemas[$nome]->paraOpenApi(new self(), true) !== $schema->paraOpenApi(new self(), true)) {
                throw new LogicException("Dois schemas diferentes com o nome {$nome}.");
            }

            return;
        }

        $this->schemas[$nome] = $schema;
        $this->gerados[$nome] = [];
        $this->gerados[$nome] = $schema->paraOpenApi($this, true);
    }

    /** @return array<string, array<string, mixed>> */
    public function paraOpenApi(): array
    {
        ksort($this->gerados);

        return $this->gerados;
    }
}
