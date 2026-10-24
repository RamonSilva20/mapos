// Filtros da listagem de lançamentos (views/financeiro/lancamentos.php, #2844).
//
// Ao escolher um período predefinido (dia, semana, mês...), as datas de
// vencimento do formulário são preenchidas com o intervalo dele; editar uma
// data à mão passa o período para "personalizado". O servidor calcula o
// intervalo do período de novo (financeiroPeriodo(), no PHP), então sem
// JavaScript o filtro continua certo: só não preenche as datas na hora.

const dois = (numero) => String(numero).padStart(2, '0');

/** Data local em AAAA-MM-DD (o formato do input type=date). */
export function formatarIso(data) {
    return `${data.getFullYear()}-${dois(data.getMonth() + 1)}-${dois(data.getDate())}`;
}

/**
 * Intervalo [de, ate] (AAAA-MM-DD) de um período predefinido, a partir de
 * hoje. A semana vai de domingo a sábado. "personalizado" e nomes
 * desconhecidos não têm intervalo (null). Espelha financeiroPeriodo().
 */
export function intervaloDoPeriodo(periodo, hoje = new Date()) {
    const ano = hoje.getFullYear();
    const mes = hoje.getMonth();
    const dia = hoje.getDate();

    switch (periodo) {
        case 'dia':
            return [formatarIso(new Date(ano, mes, dia)), formatarIso(new Date(ano, mes, dia))];
        case 'semana': {
            const domingo = dia - hoje.getDay();
            return [formatarIso(new Date(ano, mes, domingo)), formatarIso(new Date(ano, mes, domingo + 6))];
        }
        case 'mes_anterior':
            return [formatarIso(new Date(ano, mes - 1, 1)), formatarIso(new Date(ano, mes, 0))];
        case 'mes':
            return [formatarIso(new Date(ano, mes, 1)), formatarIso(new Date(ano, mes + 1, 0))];
        case 'mes_posterior':
            return [formatarIso(new Date(ano, mes + 1, 1)), formatarIso(new Date(ano, mes + 2, 0))];
        case 'ano':
            return [`${ano}-01-01`, `${ano}-12-31`];
        default:
            return null;
    }
}

export default function iniciar(formulario) {
    const periodo = formulario.querySelector('#filtro-periodo');
    const de = formulario.querySelector('#filtro-de');
    const ate = formulario.querySelector('#filtro-ate');
    if (!periodo || !de || !ate) {
        return;
    }

    periodo.addEventListener('change', () => {
        const intervalo = intervaloDoPeriodo(periodo.value);
        if (intervalo) {
            [de.value, ate.value] = intervalo;
        }
    });

    for (const campo of [de, ate]) {
        campo.addEventListener('input', () => {
            periodo.value = 'personalizado';
        });
    }
}
