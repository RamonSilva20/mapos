export default function iniciar(elemento) {
    const scalar = window.Scalar;

    if (!scalar || typeof scalar.createApiReference !== 'function') {
        elemento.textContent = 'Não foi possível carregar a documentação. Recarregue a página.';
        return;
    }

    elemento.replaceChildren();

    scalar.createApiReference(elemento, {
        url: elemento.dataset.url,
        withDefaultFonts: false,
        hideClientButton: true,
        showDeveloperTools: 'never',
        telemetry: false,
        agent: { disabled: true },
        persistAuth: false,
        documentDownloadType: 'json',
        defaultHttpClient: { targetKey: 'shell', clientKey: 'curl' },
        authentication: { preferredSecurityScheme: 'usuario' },
        metaData: { title: document.title },
    });
}
