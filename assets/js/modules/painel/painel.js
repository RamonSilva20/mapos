// Painel inicial (views/mapos/painel.php, #2847).
//
// - Gráficos (Chart.js 4): balanço do ano (receitas e despesas pagas por mês,
//   com o saldo em linha) e OS por status. Os números vêm do page_data
//   "painel-dados"; as cores, dos tokens (--color-*), lidas de novo quando o
//   modo claro/escuro muda.
// - Botão de olho (#3022): oculta o dinheiro do painel, preferência deste navegador.
// - Agenda (FullCalendar 6): as OS pela data final, de mapos/calendario, com
//   o filtro de status. Clicar numa OS abre o modal "agenda-os", preenchido
//   com textContent.
//
// As bibliotecas saem de assets/vendor e só são carregadas quando a tela tem
// gráfico ou agenda (o usuário pode não ter permissão para nenhum).

import { lerJson } from '../../lib/dados.js';
import { ErroHttp, get as getPadrao } from '../../lib/http.js';
import { carregarScript } from '../../lib/script.js';
import { mostrarToast } from '../../lib/toast.js';

const VENDOR = new URL('../../../vendor/', import.meta.url);

/** Valor de um token de cor (--color-<nome>) no momento. */
export function corDoToken(nome, estilo = getComputedStyle(document.documentElement)) {
    return estilo.getPropertyValue(`--color-${nome}`).trim();
}

/** R$ 1.234,50 */
export function moeda(valor) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(valor) || 0);
}

/** Date (dia local) → AAAA-MM-DD, o formato que mapos/calendario aceita. */
export function dataIso(data) {
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const dia = String(data.getDate()).padStart(2, '0');

    return `${data.getFullYear()}-${mes}-${dia}`;
}

/** URL de mapos/calendario com o período visível e o status escolhido. */
export function urlDaAgenda(base, inicio, fim, status = '') {
    const url = new URL(base, 'http://x');
    url.searchParams.set('start', dataIso(inicio));
    url.searchParams.set('end', dataIso(fim));
    if (status) {
        url.searchParams.set('status', status);
    }

    return base.startsWith('http') ? url.href : url.pathname + url.search;
}

/** Configuração do gráfico de balanço (Chart.js), com as cores já resolvidas. */
export function configuracaoBalanco(balanco, cores) {
    return {
        data: {
            labels: balanco.meses,
            datasets: [
                { type: 'bar', label: 'Receitas', data: balanco.receitas, backgroundColor: cores.receitasSoft, borderColor: cores.receitas, borderWidth: 1.5, borderRadius: 4, order: 2 },
                { type: 'bar', label: 'Despesas', data: balanco.despesas, backgroundColor: cores.despesasSoft, borderColor: cores.despesas, borderWidth: 1.5, borderRadius: 4, order: 2 },
                { type: 'line', label: 'Saldo', data: balanco.saldo, borderColor: cores.saldo, backgroundColor: cores.saldo, pointRadius: 3, tension: 0, order: 1 },
            ],
        },
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { grid: { display: false }, ticks: { color: cores.texto } },
                y: { grid: { color: cores.grade }, ticks: { color: cores.texto, callback: (valor) => moeda(valor) } },
            },
            plugins: {
                legend: { position: 'bottom', labels: { color: cores.texto, usePointStyle: true } },
                tooltip: { callbacks: { label: (contexto) => `${contexto.dataset.label}: ${moeda(contexto.parsed.y)}` } },
            },
        },
    };
}

/**
 * Configuração do gráfico de OS por status (rosca), uma cor por variante do
 * pill-status. O modo (#3037) escolhe a medida: 'qtd' usa a quantidade de OS,
 * 'valor' usa o valor somado (R$) de cada status.
 */
export function configuracaoOs(fatias, corDaVariante, cores, modo = 'qtd') {
    // Total no centro da rosca (#3037): a soma das fatias (quantidade ou R$).
    const centro = {
        id: 'centroOs',
        afterDraw(chart) {
            const dados = chart.data.datasets[0]?.data ?? [];
            const total = dados.reduce((soma, valor) => soma + (Number(valor) || 0), 0);
            const arco = chart.getDatasetMeta(0).data[0];
            if (!arco) {
                return;
            }

            const { ctx } = chart;
            const fonte = getComputedStyle(chart.canvas).fontFamily;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = corDoToken('text');
            ctx.font = `600 24px ${fonte}`;
            ctx.fillText(modo === 'valor' ? moeda(total) : String(total), arco.x, arco.y - 8);
            ctx.fillStyle = corDoToken('muted');
            ctx.font = `500 12px ${fonte}`;
            ctx.fillText(modo === 'valor' ? 'total' : 'OS', arco.x, arco.y + 12);
            ctx.restore();
        },
    };

    return {
        type: 'doughnut',
        data: {
            labels: fatias.map((fatia) => fatia.status),
            datasets: [{
                data: fatias.map((fatia) => (modo === 'valor' ? Number(fatia.valor) || 0 : fatia.total)),
                backgroundColor: fatias.map((fatia) => corDaVariante(fatia.variante + '-soft')),
                borderColor: fatias.map((fatia) => corDaVariante(fatia.variante)),
                borderWidth: 2,
            }],
        },
        options: {
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { color: cores.texto, usePointStyle: true } },
                tooltip: { callbacks: { label: (contexto) => `${contexto.label}: ${modo === 'valor' ? moeda(contexto.parsed) : contexto.parsed}` } },
            },
        },
        plugins: [centro],
    };
}

async function iniciarGraficos(raiz, dados, doc, get) {
    const telas = [...raiz.querySelectorAll('[data-grafico]')];
    if (telas.length === 0 || !dados) {
        return;
    }

    await carregarScript(new URL('chart.js/chart.umd.min.js', VENDOR).href, doc);
    const { Chart } = globalThis;
    const graficos = [];
    const html = doc.documentElement;
    let modoOs = 'qtd';

    const desenhar = () => {
        graficos.splice(0).forEach((grafico) => grafico.destroy());

        const estilo = getComputedStyle(doc.documentElement);
        const cor = (nome) => corDoToken(nome, estilo);
        const cores = { texto: cor('muted'), grade: cor('border'), fundo: cor('surface'), receitas: cor('success'), despesas: cor('warning'), saldo: cor('info'), receitasSoft: cor('success-soft'), despesasSoft: cor('warning-soft') };
        Chart.defaults.font.family = getComputedStyle(doc.body).fontFamily;

        for (const tela of telas) {
            if (tela.dataset.grafico === 'balanco' && dados.balanco) {
                graficos.push(new Chart(tela, configuracaoBalanco(dados.balanco, cores)));
            } else if (tela.dataset.grafico === 'os') {
                // Mês sem OS (#3037): esconde a rosca e mostra o aviso.
                const area = tela.closest('[data-grafico-os-area]');
                const vazio = area?.querySelector('[data-grafico-os-vazio]');
                const temDados = Array.isArray(dados.os) && dados.os.length > 0;
                tela.hidden = !temDados;
                if (vazio) {
                    vazio.hidden = temDados;
                }
                if (temDados) {
                    graficos.push(new Chart(tela, configuracaoOs(dados.os, cor, cores, modoOs)));
                }
            }
        }
    };

    desenhar();

    // Toggle Qtd. ⇄ R$ do gráfico de OS (#3037); o botão de olho desabilita o R$.
    const toggle = raiz.querySelector('[data-grafico-toggle="os"]');
    if (toggle) {
        const botoes = [...toggle.querySelectorAll('[data-grafico-modo]')];
        const botaoValor = toggle.querySelector('[data-grafico-modo="valor"]');

        const aplicarModo = (modo) => {
            modoOs = modo;
            for (const botao of botoes) {
                botao.setAttribute('aria-pressed', botao.dataset.graficoModo === modo ? 'true' : 'false');
            }
            desenhar();
        };

        toggle.addEventListener('click', (evento) => {
            const botao = evento.target.closest('[data-grafico-modo]');
            if (botao && !botao.disabled && botao.dataset.graficoModo !== modoOs) {
                aplicarModo(botao.dataset.graficoModo);
            }
        });

        // O botão de olho oculta valores: o R$ fica indisponível e volta para Qtd.
        const sincronizarValores = () => {
            const ocultos = html.getAttribute('data-valores') === 'ocultos';
            if (botaoValor) {
                botaoValor.disabled = ocultos;
            }
            if (ocultos && modoOs === 'valor') {
                aplicarModo('qtd');
            }
        };

        sincronizarValores();
        new MutationObserver(sincronizarValores).observe(html, { attributes: true, attributeFilter: ['data-valores'] });
    }

    // Filtro Mês atual ⇄ Mês passado do gráfico de OS (#3037): recarrega a série
    // e a tabela de mapos/painelGraficoOs, sem recarregar a página.
    const mesGrupo = raiz.querySelector('[data-grafico-mes]');
    if (mesGrupo && typeof get === 'function') {
        const url = mesGrupo.dataset.graficoMesUrl;
        const opcoes = [...mesGrupo.querySelectorAll('[data-grafico-mes-opcao]')];
        const tabela = raiz.querySelector('[data-grafico-os-tabela]');
        let carregando = false;

        const trocarMes = (mes, botao) => {
            if (carregando || !url) {
                return;
            }

            carregando = true;
            const alvo = new URL(url, doc.location.href);
            alvo.searchParams.set('mes', mes);

            get(alvo.pathname + alvo.search)
                .then((resposta) => {
                    if (resposta && Array.isArray(resposta.fatias)) {
                        dados.os = resposta.fatias;
                        desenhar();
                    }
                    if (tabela && resposta && resposta.html !== undefined) {
                        tabela.innerHTML = resposta.html;
                    }
                    for (const opcao of opcoes) {
                        opcao.setAttribute('aria-pressed', opcao === botao ? 'true' : 'false');
                    }
                    // Avisa a seção de OS por status (#3036) para seguir o mês:
                    // atualiza as contagens das abas e recarrega a lista ativa.
                    raiz.dispatchEvent(new CustomEvent('painel:mes', { detail: { mes, fatias: resposta?.fatias ?? [] } }));
                })
                .catch((erro) => {
                    mostrarToast({
                        mensagem: erro instanceof ErroHttp && erro.status === 403
                            ? 'Sua sessão expirou. Recarregue a página.'
                            : 'Não foi possível carregar o gráfico. Tente de novo.',
                        variante: 'danger',
                    }, doc);
                })
                .finally(() => {
                    carregando = false;
                });
        };

        mesGrupo.addEventListener('click', (evento) => {
            const botao = evento.target.closest('[data-grafico-mes-opcao]');
            if (botao && botao.getAttribute('aria-pressed') !== 'true') {
                trocarMes(botao.dataset.graficoMesOpcao, botao);
            }
        });
    }

    // O modo claro/escuro troca a classe "dark" do <html>.
    new MutationObserver(desenhar).observe(doc.documentElement, { attributes: true, attributeFilter: ['class'] });
}

function abrirOs(modal, evento) {
    const dados = evento.extendedProps;
    modal.querySelector('h2').textContent = `OS #${dados.numero}`;

    for (const campo of modal.querySelectorAll('[data-agenda-campo]')) {
        const nome = campo.dataset.agendaCampo;
        campo.textContent = nome === 'faturado' ? (dados.faturado ? 'Sim' : 'Não') : (dados[nome] || '—');
    }

    for (const pill of modal.querySelectorAll('[data-agenda-pill]')) {
        pill.hidden = pill.dataset.agendaPill !== dados.status;
    }

    for (const link of modal.querySelectorAll('[data-agenda-link]')) {
        const url = dados[link.dataset.agendaLink];
        link.hidden = !url;
        if (url) {
            link.href = url;
        }
    }

    modal.showModal();
}

async function iniciarAgenda(raiz, { doc, get }) {
    const alvo = raiz.querySelector('[data-agenda]');
    if (!alvo) {
        return;
    }

    await carregarScript(new URL('fullcalendar/fullcalendar.global.min.js', VENDOR).href, doc);
    await carregarScript(new URL('fullcalendar/locales/pt-br.global.min.js', VENDOR).href, doc);

    const filtro = raiz.querySelector('[data-agenda-status]');
    const modal = doc.getElementById('agenda-os');
    const celular = globalThis.matchMedia?.('(max-width: 639px)').matches ?? false;

    alvo.querySelector('[data-agenda-carregando]')?.remove();

    const agenda = new globalThis.FullCalendar.Calendar(alvo, {
        locale: 'pt-br',
        initialView: celular ? 'listMonth' : 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
        buttonText: { today: 'Hoje', month: 'Mês', list: 'Lista' },
        buttonHints: { prev: 'Mês anterior', next: 'Próximo mês', today: 'Ir para hoje', dayGridMonth: 'Ver o mês', listMonth: 'Ver a lista do mês' },
        height: 'auto',
        dayMaxEvents: 3,
        displayEventTime: false,
        eventClassNames: (info) => [`evento-${info.event.extendedProps.variante || 'neutral'}`],
        events: (periodo, sucesso, falha) => {
            get(urlDaAgenda(alvo.dataset.agenda, periodo.start, periodo.end, filtro?.value ?? ''))
                .then((eventos) => sucesso(Array.isArray(eventos) ? eventos : []))
                .catch((erro) => {
                    mostrarToast({
                        mensagem: erro instanceof ErroHttp && erro.status === 403
                            ? 'Sua sessão expirou. Recarregue a página.'
                            : 'Não foi possível carregar a agenda. Tente de novo.',
                        variante: 'danger',
                    }, doc);
                    falha(erro);
                });
        },
        // Os ícones das setas vêm com role=img sem texto: o nome do botão já está no title (buttonHints).
        datesSet: () => {
            for (const icone of alvo.querySelectorAll('.fc-icon[role="img"]')) {
                icone.setAttribute('aria-hidden', 'true');
                icone.removeAttribute('role');
            }
        },
        eventClick: (info) => {
            info.jsEvent.preventDefault();
            if (modal) {
                abrirOs(modal, info.event);
            }
        },
    });

    agenda.render();
    filtro?.addEventListener('change', () => agenda.refetchEvents());
}

/** Mês escolhido no filtro do gráfico de OS (#3037): 'passado' ou 'atual'. */
function mesSelecionado(raiz) {
    const sel = raiz.querySelector('[data-grafico-mes-opcao][aria-pressed="true"]');

    return sel?.dataset.graficoMesOpcao === 'passado' ? 'passado' : 'atual';
}

/**
 * Seções de OS e Vendas por status (#3036): clicar numa aba carrega a lista
 * daquele status de mapos/painelOs|painelVendas (JSON { result, html }) e troca
 * o tabpanel, sem recarregar. Setas ← → andam pelas abas (padrão APG). Sem
 * JavaScript cada aba é só a contagem e o servidor já abre a primeira.
 *
 * A seção de OS segue o filtro Mês atual/passado (#3037): manda o mês em cada
 * carga e, quando o mês muda (evento 'painel:mes'), atualiza as contagens das
 * abas e recarrega a lista ativa — assim não lista OS antigas.
 */
function iniciarListas(raiz, { doc, get }) {
    for (const abas of raiz.querySelectorAll('[data-painel-abas]')) {
        const url = abas.dataset.painelUrl;
        const painel = raiz.querySelector(`[data-painel-lista="${abas.dataset.painelAbas}"]`);
        if (!url || !painel) {
            continue;
        }

        const seguirMes = abas.dataset.painelAbas === 'os';
        const tabs = [...abas.querySelectorAll('[data-painel-aba]')];

        const trocar = (aba) => {
            for (const t of tabs) {
                const ativa = t === aba;
                t.setAttribute('aria-selected', ativa ? 'true' : 'false');
                t.tabIndex = ativa ? 0 : -1;
            }

            painel.setAttribute('aria-busy', 'true');
            const alvo = new URL(url, doc.location.href);
            alvo.searchParams.set('status', aba.dataset.status);
            if (seguirMes) {
                alvo.searchParams.set('mes', mesSelecionado(raiz));
            }

            get(alvo.pathname + alvo.search)
                .then((resposta) => {
                    if (resposta && resposta.html !== undefined) {
                        painel.innerHTML = resposta.html;
                    }
                })
                .catch((erro) => {
                    mostrarToast({
                        mensagem: erro instanceof ErroHttp && erro.status === 403
                            ? 'Sua sessão expirou. Recarregue a página.'
                            : 'Não foi possível carregar a lista. Tente de novo.',
                        variante: 'danger',
                    }, doc);
                })
                .finally(() => painel.removeAttribute('aria-busy'));
        };

        abas.addEventListener('click', (evento) => {
            const aba = evento.target.closest('[data-painel-aba]');
            if (aba && aba.getAttribute('aria-selected') !== 'true') {
                trocar(aba);
            }
        });

        abas.addEventListener('keydown', (evento) => {
            if (evento.key !== 'ArrowRight' && evento.key !== 'ArrowLeft') {
                return;
            }

            evento.preventDefault();
            const atual = tabs.findIndex((t) => t.getAttribute('aria-selected') === 'true');
            const passo = evento.key === 'ArrowRight' ? 1 : -1;
            const proxima = tabs[(atual + passo + tabs.length) % tabs.length];
            if (proxima) {
                proxima.focus();
                trocar(proxima);
            }
        });

        // Mês trocado no gráfico (#3037): atualiza as contagens das abas (badge)
        // com os totais do mês e recarrega a lista da aba ativa.
        if (seguirMes) {
            raiz.addEventListener('painel:mes', (evento) => {
                const fatias = evento.detail?.fatias;
                if (Array.isArray(fatias)) {
                    const total = new Map(fatias.map((f) => [f.status, f.total]));
                    for (const t of tabs) {
                        const badge = t.querySelector('[data-painel-aba-contagem]');
                        if (badge) {
                            badge.textContent = String(total.get(t.dataset.status) ?? 0);
                        }
                    }
                }

                const ativa = tabs.find((t) => t.getAttribute('aria-selected') === 'true');
                if (ativa) {
                    trocar(ativa);
                }
            });
        }
    }
}

export const CHAVE_VALORES = 'mapos:valores-ocultos';

/** Próximo estado do botão de olho: ocultar ↔ mostrar. */
export function alternarValores(ocultos) {
    return !ocultos;
}

/**
 * Botão de olho (#3022): oculta ou mostra o dinheiro do painel. A escolha vale
 * só neste navegador (localStorage); o tema.js a aplica antes da primeira
 * pintura. Com o localStorage bloqueado, vale até a próxima página.
 */
function iniciarValores(doc, armazem = globalThis.localStorage) {
    const html = doc.documentElement;

    for (const botao of doc.querySelectorAll('[data-valores-alternar]')) {
        botao.addEventListener('click', () => {
            const ocultos = alternarValores(html.getAttribute('data-valores') === 'ocultos');
            if (ocultos) {
                html.setAttribute('data-valores', 'ocultos');
            } else {
                html.removeAttribute('data-valores');
            }

            try {
                if (ocultos) {
                    armazem?.setItem(CHAVE_VALORES, '1');
                } else {
                    armazem?.removeItem(CHAVE_VALORES);
                }
            } catch {
                // localStorage bloqueado: a escolha vale só até a próxima página.
            }
        });
    }
}

export default function iniciar(raiz, { doc = document, get = getPadrao } = {}) {
    const dados = lerJson('painel-dados', null, doc);

    const avisar = (erro) => {
        console.error('[painel]', erro);
    };

    iniciarValores(doc);
    iniciarGraficos(raiz, dados, doc, get).catch(avisar);
    iniciarAgenda(raiz, { doc, get }).catch(avisar);
    iniciarListas(raiz, { doc, get });
}
