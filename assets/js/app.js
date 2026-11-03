// Carregador dos módulos de página da v5.
//
// A view marca um elemento com data-module="pasta/nome" (helper js_module() no
// PHP) e este arquivo importa assets/js/modules/pasta/nome.js, chamando o
// export default com o elemento. Sem bundler: são ES modules nativos.
//
// Incluído no layout com <script type="module">, que só roda depois de o HTML
// ser lido; o teste do readyState cobre os dois casos.

const NOME_VALIDO = /^[a-z0-9_-]+(\/[a-z0-9_-]+)*$/;

export function nomeDeModuloValido(nome) {
    return typeof nome === 'string' && NOME_VALIDO.test(nome);
}

/**
 * URL do módulo. Herda a query deste arquivo (?v=<versão>, posta pelo PHP) para
 * que, depois de uma atualização, o navegador não reuse o módulo antigo (#3028).
 */
export function urlDoModulo(nome, base = import.meta.url) {
    const url = new URL(`./modules/${nome}.js`, base);
    url.search = new URL(base).search;

    return url;
}

export async function iniciarModulos(raiz = document) {
    const elementos = raiz.querySelectorAll('[data-module]');

    await Promise.all(Array.from(elementos, async (elemento) => {
        const nome = elemento.dataset.module;

        if (!nomeDeModuloValido(nome)) {
            console.error(`[app] nome de módulo inválido: "${nome}"`);
            return;
        }

        if (elemento.dataset.moduleIniciado === 'true') {
            return;
        }
        elemento.dataset.moduleIniciado = 'true';

        try {
            const modulo = await import(urlDoModulo(nome));
            if (typeof modulo.default === 'function') {
                await modulo.default(elemento);
            }
        } catch (erro) {
            console.error(`[app] falha ao iniciar o módulo "${nome}"`, erro);
        }
    }));
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => iniciarModulos());
    } else {
        iniciarModulos();
    }
}
