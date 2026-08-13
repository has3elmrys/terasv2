<script>
(function () {
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
        return;
    }

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }

    const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    const isFirefox = /firefox/i.test(window.navigator.userAgent);

    const button = document.createElement('button');
    button.textContent = 'Install App';
    button.style.cssText = 'position:fixed;bottom:16px;right:16px;z-index:9999;padding:10px 16px;border-radius:9999px;background:#d97706;color:#fff;border:none;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.3);cursor:pointer;display:none;';
    document.body.appendChild(button);

    let deferredPrompt = null;

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        button.style.display = 'block';
    });

    if (isIos || isFirefox) {
        button.style.display = 'block';
    }

    button.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            if (outcome === 'accepted') {
                button.style.display = 'none';
            }
            return;
        }

        if (isIos) {
            showInstructions('Tap the Share icon, then "Add to Home Screen".');
        } else if (isFirefox) {
            showInstructions('Firefox desktop can\'t install apps directly. Try Chrome/Edge, or install the "PWAs for Firefox" extension.');
        }
    });

    function showInstructions(message) {
        const box = document.createElement('div');
        box.textContent = message;
        box.style.cssText = 'position:fixed;bottom:70px;right:16px;z-index:9999;max-width:260px;padding:12px 16px;border-radius:8px;background:#1f2937;color:#fff;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.3);';
        document.body.appendChild(box);
        setTimeout(() => box.remove(), 6000);
    }
})();
</script>
