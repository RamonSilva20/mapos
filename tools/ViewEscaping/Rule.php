<?php

namespace Tools\ViewEscaping;

use Closure;

/**
 * Uma regra nomeada da lista de precedência, com o corpo que a implementa.
 *
 * A lista ordenada vive em `EscapingPolicy::RULES`, e cada item dela aponta para um
 * destes. A separação existe porque a ordem e o corpo são decisões diferentes: a
 * ordem muda quando duas regras colidem num caso novo, e o corpo muda quando uma
 * regra passa a ler uma forma de expressão que ainda não lia. Juntas num array de
 * closures, a segunda mudança exigiria reescrever a primeira.
 *
 * O nome não é decoração: é o que o relatório pode citar quando reprova, e é o que
 * permite a um teste dizer qual regra venceu num caso em que duas colidem.
 */
final readonly class Rule
{
    /**
     * @param  Closure(string, EscapingPolicy): ?Verdict  $judge  devolve null quando a regra não se aplica
     */
    public function __construct(public string $name, private Closure $judge)
    {
    }

    /**
     * O que a regra decide sobre a expressão, ou null se ela não se aplica.
     *
     * O null é a parte do contrato que sustenta a lista: sem ele, uma regra que não
     * se aplica teria de devolver alguma das três decisões, e as três decidem. Uma
     * lista onde "não se aplica" é indistinguível de "aprovou" reprovaria a linha
     * errada com a regra errada.
     */
    public function judge(string $expr, EscapingPolicy $policy): ?Verdict
    {
        return ($this->judge)($expr, $policy);
    }
}
