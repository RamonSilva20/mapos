// Registro do Service Worker para suporte offline (PWA)
// Anexado via <?= js_module('login/service-worker') ?> nas entry surfaces

if ('serviceWorker' in navigator) {
    const swUrl = new URL('/assets/js/service-worker.js', window.location.origin);
    // Cache-busting via timestamp do arquivo
    const timestamp = Date.now();
    swUrl.searchParams.set('v', timestamp.toString());

    navigator.serviceWorker.register(swUrl.href)
        .then((registration) => {
            console.log('Service Worker registrado:', registration.scope);

            // Verificar atualizações periodicamente
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                if (newWorker) {
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // Nova versão disponível - notificar usuário se quiser
                            console.log('Nova versão do Service Worker disponível');
                        }
                    });
                }
            });
        })
        .catch((error) => {
            console.warn('Falha ao registrar Service Worker:', error);
        });
}

// Escutar mensagens do Service Worker (ex: atualização disponível)
navigator.serviceWorker?.addEventListener('message', (event) => {
    if (event.data?.type === 'UPDATE_AVAILABLE') {
        console.log('Atualização disponível - recarregue a página');
        // Opcional: mostrar toast/notificação para o usuário
    }
});