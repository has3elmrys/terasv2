<script>
(function () {
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;

    // Service worker registers everywhere, including inside the installed app.
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }

    // Install button is a mobile-only affordance. On desktop it floats over
    // Filament modal footers and blocks the Save button.
    const isMobile = window.matchMedia('(max-width: 767px)').matches;

    if (isStandalone || ! isMobile) {
        return;
    }

    const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    const isFirefox = /firefox/i.test(window.navigator.userAgent);

    const button = document.createElement('button');
    button.textContent = 'Install App';
    // bottom: 84px clears the mobile bottom nav; z-index stays below Filament modals.
    button.style.cssText = 'position:fixed;bottom:84px;right:16px;z-index:30;padding:10px 16px;border-radius:9999px;background:#d97706;color:#fff;border:none;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.3);cursor:pointer;display:none;';
    document.body.appendChild(button);

    let deferredPrompt = null;
    let isEligible = false;

    const show = () => {
        if (isEligible) {
            button.style.display = 'block';
        }
    };

    const hide = () => {
        button.style.display = 'none';
    };

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        isEligible = true;
        show();
    });

    if (isIos || isFirefox) {
        isEligible = true;
        show();
    }

    // Filament dispatches these when any modal opens or closes.
    window.addEventListener('open-modal', hide);
    window.addEventListener('close-modal', show);

    button.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            if (outcome === 'accepted') {
                isEligible = false;
                hide();
            }
            return;
        }

        if (isIos) {
            showInstructions('Tap the Share icon, then "Add to Home Screen".');
        } else if (isFirefox) {
            showInstructions('Firefox can\'t install apps directly. Try Chrome/Edge, or install the "PWAs for Firefox" extension.');
        }
    });

    function showInstructions(message) {
        const box = document.createElement('div');
        box.textContent = message;
        box.style.cssText = 'position:fixed;bottom:144px;right:16px;z-index:30;max-width:260px;padding:12px 16px;border-radius:8px;background:#1f2937;color:#fff;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.3);';
        document.body.appendChild(box);
        setTimeout(() => box.remove(), 6000);
    }
})();
</script>