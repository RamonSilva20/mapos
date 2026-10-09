// Carrega um script clássico (UMD/global) de assets/vendor sob demanda, uma
// vez só por URL. Usado pelas telas que precisam de uma biblioteca pesada só
// em parte das visitas (Chart.js e FullCalendar no painel, #2847): o arquivo
// vem do próprio Map-OS, então a CSP (script-src 'self') aceita.

const carregando = new Map();

export function carregarScript(url, doc = document) {
    if (!carregando.has(url)) {
        carregando.set(url, new Promise((resolver, rejeitar) => {
            const script = doc.createElement('script');
            script.src = url;
            script.async = true;
            script.addEventListener('load', () => resolver());
            script.addEventListener('error', () => {
                carregando.delete(url);
                rejeitar(new Error(`Não foi possível carregar ${url}`));
            });
            doc.head.append(script);
        }));
    }

    return carregando.get(url);
}
