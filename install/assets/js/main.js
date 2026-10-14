// Instalador do Map-OS: troca de etapas e envio do formulário por fetch.
// O servidor (do_install.php) valida tudo de novo.
(function () {
  const painel = (nome) => document.getElementById('painel-' + nome);
  const etapa = (nome) => document.getElementById('etapa-' + nome);
  const avisos = document.getElementById('avisos');

  function mostrar(nome) {
    document.querySelectorAll('[data-painel]').forEach((el) => {
      el.hidden = el !== painel(nome);
    });
    document.querySelectorAll('[data-etapa]').forEach((el) => {
      el.toggleAttribute('data-atual', el === etapa(nome));
    });
  }

  function concluir(nome) {
    const item = etapa(nome);
    if (item) item.querySelector('svg').style.color = 'var(--color-success)';
  }

  function aviso(mensagem) {
    avisos.replaceChildren();
    if (!mensagem) return;
    const caixa = document.createElement('p');
    caixa.className = 'border-b border-border bg-danger-soft p-3 text-danger-ink';
    caixa.textContent = mensagem;
    avisos.append(caixa);
  }

  if (document.body.dataset.instalado === '1') {
    mostrar('finalizacao');
    return;
  }

  mostrar('pre-instalacao');

  document.querySelector('[data-proxima]')?.addEventListener('click', () => {
    concluir('pre-instalacao');
    mostrar('configuracao');
    document.getElementById('host')?.focus();
  });

  const formulario = document.getElementById('formulario-instalacao');
  formulario?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    const botao = formulario.querySelector('[type="submit"]');
    const alternar = (carregando) => {
      botao.disabled = carregando;
      botao.querySelector('[data-carregando]').hidden = !carregando;
      botao.querySelector('[data-rotulo]').hidden = carregando;
    };

    aviso('');
    alternar(true);
    try {
      const resposta = await fetch(formulario.action, { method: 'POST', body: new FormData(formulario) });
      const resultado = await resposta.json();
      if (resultado.success) {
        concluir('configuracao');
        concluir('finalizacao');
        mostrar('finalizacao');
      } else {
        aviso(resultado.message);
      }
    } catch (erro) {
      aviso('Não foi possível concluir a instalação. Tente de novo.');
    } finally {
      alternar(false);
    }
  });
})();
