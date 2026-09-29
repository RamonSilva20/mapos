<?php

namespace Tools\ViewEscaping;

/**
 * Os corpos das regras, um método por regra, sem a ordem.
 *
 * A ordem mora em `EscapingPolicy::RULES`, que é uma lista de NOMES. Este arquivo
 * não sabe em que ordem suas regras vão ser lidas, e é por isso que ele não tem
 * lista nenhuma: tem métodos, e o nome do método é o nome da regra. As duas metades
 * se encaixam em `EscapingPolicy::rules()`, que transforma cada nome num `Rule` com
 * este método como corpo — e que falha se um nome da lista não tiver método, que é a
 * direção segura, porque uma regra sem corpo reprovaria a expressão e uma regra sem
 * nome na lista jamais seria lida.
 *
 * Cada método devolve `null` quando a regra não se aplica à expressão, e um
 * `Verdict` quando se aplica. Um método que devolvesse um veredito sempre transformaria
 * a lista numa função disfarçada de tabela, com a primeira regra decidindo tudo.
 */
final class Rules
{
    /**
     * Markup pronto é emitido cru por desenho, então a regra o aprova antes de
     * qualquer outra examinar a expressão.
     *
     * Fica antes de `bareValue` por construção: `$topo` é uma variável crua, e sem
     * esta regra ela reprovaria. Fica antes de `literal` por coherência com a
     * intenção, e não por necessidade, que é o que a torna uma posição escolhida em
     * vez de uma herdada: se alguém a mover para depois, o resultado não muda, e o
     * teste de precedência que fixa esta paragem continua verde. O que muda é a
     * razão de existir dela, e ela só existe por causa de `bareValue`.
     *
     * A lista é a mesma que `preRenderedEscaped()` usa, então a mesma variável não
     * pode ser crua num check e escapada no outro.
     */
    public static function preRendered(string $expr, EscapingPolicy $policy): ?Verdict
    {
        return $policy->isPreRendered($expr) ? Verdict::approve() : null;
    }

    /**
     * Um literal e um cast não têm nada a escapar: o que sai deles é o que o
     * próprio PHP escreveu.
     *
     * `isScalarCast()` vem no mesmo ramo porque `(int) $this->input->get('id')` é o
     * caso em que a fonte é reprovada por outra regra, e o gate precisa aprovar a
     * linha sem que o reprovamento da fonte dentro dela anule a coerção que a torna
     * segura.
     */
    public static function literalOrCast(string $expr, EscapingPolicy $policy): ?Verdict
    {
        $safe = PhpExpression::isLiteral($expr) || PhpExpression::isScalarCast($expr);

        return $safe ? Verdict::approve() : null;
    }

    /**
     * Uma variável crua é o achado mais simples que existe, e o mais difícil de
     * argumentar contra: não há escaper, não há função, não há nada entre o valor e
     * a página.
     */
    public static function bareValue(string $expr, EscapingPolicy $policy): ?Verdict
    {
        return PhpExpression::isBareValue($expr) ? Verdict::report($expr) : null;
    }

    /**
     * Um ternário é seguro se os dois lados forem, e não se um deles for.
     *
     * `[$then, $else]` em vez de `$ternary` inteiro porque o `$condition` é o que
     * decide qual lado sair, e reprová-lo reprovaria a linha mesmo com os dois lados
     * escapados — que é o oposto do que a regra deve fazer.
     */
    public static function ternary(string $expr, EscapingPolicy $policy): ?Verdict
    {
        $ternary = PhpExpression::splitTernary($expr);

        if ($ternary === null) {
            return null;
        }

        [, $then, $else] = $ternary;

        return Verdict::descend([$then, $else]);
    }

    /**
     * Uma concatenação é segura se cada operando for, e a ordem importa: `ternary` vem
     * antes, porque em `$a ? $b . $c : $d` o ponto está no ramo do ternário, e o corte
     * por operador partiria a linha no meio de uma escolha — reprovando o `$a` e
     * devolvendo `$a ? $b , $c : $d` no relatório, que é uma linha que ninguém sabe
     * corrigir.
     */
    public static function concatenation(string $expr, EscapingPolicy $policy): ?Verdict
    {
        $operands = PhpExpression::splitTopLevel($expr);

        return $operands === null ? null : Verdict::descend($operands);
    }

    /**
     * A chamada é a única regra com três decisões dentro, e as três são diferentes
     * entre si por isso.
     *
     * A fonte é conferida antes da allowlist, e reprova a EXPRESSÃO: os argumentos de
     * `$this->input->get('campo')` são o nome do campo, um literal, e inspecioná-los
     * aprovaria a linha sem olhar para dentro uma vez sequer. Um escaper em volta
     * continua vencendo, porque `esc($this->input->get('campo'))` está protegido
     * pelo escaper e não pelo nome do que está dentro.
     *
     * Vem depois da concatenação por uma razão concreta e medida: `esc($a) . foo($b)`
     * também termina em parêntese, e `isFunctionCall()` devolve `esc` como o nome da
     * chamada. Lidas na ordem contrária, a linha inteira é aprovada pelo escaper da
     * frente — `$b` sai cru para a página e o gate não diz nada. O exemplo `esc($a) . $y`
     * que a versão anterior deste arquivo usava não prova nada: `$y` faz a expressão
     * não terminar em parêntese, e nenhuma das duas ordens muda o resultado nele.
     */
    public static function functionCall(string $expr, EscapingPolicy $policy): ?Verdict
    {
        $call = PhpExpression::isFunctionCall($expr);

        if ($call === null) {
            return null;
        }

        [$ref, $rawArgs] = $call;

        if ($policy->isSource($ref)) {
            return Verdict::report($expr);
        }

        if ($policy->approvesCall($ref)) {
            return Verdict::approve();
        }

        $args = PhpExpression::splitCallArgs($rawArgs);

        // `foo()` devolve conteúdo que não foi escapado e não tem argumento para
        // inspecionar, então não há nada que a aprove: a ausência de argumento é um
        // achado, não uma ausência de prova.
        if ($args === []) {
            return Verdict::report($expr);
        }

        return Verdict::descend($args);
    }

    /**
     * O fim da lista reprova, e é o que torna a lista inteira fail-closed.
     *
     * Chega aqui o que este gate não sabe ler: expressão com palavra de controle, com
     * `<`/`>` ou com tag PHP dentro, ou que o analisador não deu conta de abrir. Nenhum
     * desses é uma prova de que o valor está escapado — são apenas casos em que o
     * analisador não deu conta. Reportar é a única leitura honesta; a alternativa
     * deixava a linha passar em silêncio.
     *
     * A regra não devolve `null` nunca, e é isso que a torna a última: ela é a rede,
     * e uma rede que pode não se aplicar não é rede.
     */
    public static function unreadable(string $expr, EscapingPolicy $policy): ?Verdict
    {
        return Verdict::report($expr);
    }
}
