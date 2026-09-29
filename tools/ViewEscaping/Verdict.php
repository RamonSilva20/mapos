<?php

namespace Tools\ViewEscaping;

/**
 * O que uma regra decidiu sobre uma expressão, em três formas e nenhuma outra.
 *
 * A decisão é um valor, e não um booleano nem uma string vazia, porque as três
 * respostas não são três variantes do mesmo retorno: `approve` encerra a
 * conferência com aprovação, `report` encerra com reprovação, e `descend` não decide
 * nada sobre a expressão inteira — devolve as partes para cada uma ser reavaliada.
 * Uma função que devolvesse `true` para aprovar e a string do achado para reprovar
 * misturaria o resultado com o método de obtê-lo, e a parte que desce não teria como
 * se anunciar.
 *
 * Existe ainda um quarto estado, que NÃO é um veredito: uma regra para a qual a
 * expressão não se aplica devolve `null`, e a regra não decide. Sem ele não há
 * como distinguir "esta regra reprovou" de "esta regra não tem nada a ver com esta
 * expressão", que é a diferença entre o `if` da pré-renderizada e o `if` do
 * literal no gate anterior.
 */
final readonly class Verdict
{
    public const APPROVE = 'approve';

    public const REPORT = 'report';

    public const DESCEND = 'descend';

    /**
     * @param  list<string>  $parts
     */
    private function __construct(
        public string $kind,
        public string $snippet = '',
        public array $parts = [],
    ) {
    }

    /**
     * A expressão está segura, e a conferência acaba aqui.
     *
     * Cobre tanto "isto é seguro por construção" — um literal, um cast — quanto
     * "isto está protegido" — um escaper em volta. As duas coisas que impedem a
     * conferência de continuar são o mesmo evento, e é por isso que há um estado só.
     */
    public static function approve(): self
    {
        return new self(self::APPROVE);
    }

    /**
     * A expressão reprova, e este é o trecho que o relatório mostra.
     */
    public static function report(string $snippet): self
    {
        return new self(self::REPORT, $snippet);
    }

    /**
     * A expressão não se julga inteira: cada parte volta para o começo da lista.
     *
     * É o que separa `esc($a) . $y` de `$y`. A primeira tem um lado protegido e
     * outro nu, e reprovar a linha inteira perderia a informação de qual dos dois é
     * o culpado; aprovar os dois seria o gate virando mentira.
     *
     * @param  list<string>  $parts
     */
    public static function descend(array $parts): self
    {
        return new self(self::DESCEND, parts: $parts);
    }

    /**
     * A regra reprovou a expressão, e o trecho do achado é o do relatório.
     */
    public function isReport(): bool
    {
        return $this->kind === self::REPORT;
    }

    /**
     * A expressão está segura, e a conferência pode parar.
     */
    public function isApproval(): bool
    {
        return $this->kind === self::APPROVE;
    }
}
