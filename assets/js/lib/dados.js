// Leitura dos dados que a view entrega aos módulos.

/**
 * Lê o JSON de um <script type="application/json" id="..."> gerado por
 * page_data() no PHP. Devolve `padrao` se o bloco não existir.
 */
export function lerJson(id, padrao = null, raiz = document) {
    const bloco = raiz.getElementById(id);

    if (!bloco) {
        return padrao;
    }

    return JSON.parse(bloco.textContent);
}

/**
 * Lê um atributo data-* do elemento, convertendo números, booleanos e JSON.
 * `chave` usa o nome do dataset: data-por-pagina vira "porPagina".
 */
export function lerDado(elemento, chave, padrao = null) {
    const valor = elemento.dataset[chave];

    if (valor === undefined) {
        return padrao;
    }

    return converter(valor);
}

export function converter(valor) {
    if (valor === 'true') return true;
    if (valor === 'false') return false;
    if (valor.trim() !== '' && !Number.isNaN(Number(valor))) return Number(valor);

    if (/^[[{]/.test(valor)) {
        try {
            return JSON.parse(valor);
        } catch {
            return valor;
        }
    }

    return valor;
}
