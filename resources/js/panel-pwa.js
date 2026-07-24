/**
 * Tenant panel PWA: service worker, kurulum banneri, mobil menu.
 */
(function () {
    const body = document.body;
    const scope = body.dataset.pwaScope;
    const swUrl = body.dataset.pwaSwUrl;

    if (!scope || !swUrl) {
        return;
    }

    body.classList.add('panel-pwa-active');

    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
        body.classList.add('panel-pwa-standalone');
    }

    // Service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register(swUrl, { scope }).catch(() => {});
        });
    }

    // Mobil menu toggle
    const menuToggle = document.getElementById('panel-mobile-menu-toggle');
    const sidebarToggle = document.querySelector('.button-toggle-menu');

    menuToggle?.addEventListener('click', () => {
        sidebarToggle?.click();
    });

    // Takvim sayfasinda alt nav randevu → modal ac
    const newAppointmentNav = document.getElementById('panel-mobile-new-appointment');
    newAppointmentNav?.addEventListener('click', (event) => {
        if (!window.location.pathname.includes('/panel/takvim')) {
            return;
        }

        event.preventDefault();
        if (typeof window.openPanelAppointmentModal === 'function') {
            window.openPanelAppointmentModal(new Date(), { keepCustomer: false });
            return;
        }
        document.getElementById('btn-new-appointment')?.click();
    });

    // Kurulum banneri
    const installBox = document.getElementById('panel-pwa-install');
    const installBtn = document.getElementById('panel-pwa-install-btn');
    const installDismiss = document.getElementById('panel-pwa-install-dismiss');
    const dismissKey = `pwa-install-dismiss:${scope}`;

    let deferredPrompt = null;

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;

        if (localStorage.getItem(dismissKey) || body.classList.contains('panel-pwa-standalone')) {
            return;
        }

        installBox?.classList.remove('d-none');
    });

    installBtn?.addEventListener('click', async () => {
        if (!deferredPrompt) {
            return;
        }

        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        installBox?.classList.add('d-none');
    });

    installDismiss?.addEventListener('click', () => {
        localStorage.setItem(dismissKey, '1');
        installBox?.classList.add('d-none');
    });

    window.addEventListener('appinstalled', () => {
        installBox?.classList.add('d-none');
        deferredPrompt = null;
    });
})();
