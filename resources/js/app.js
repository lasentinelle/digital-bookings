import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('copyToClipboard', (value) => ({
    copied: false,
    copy() {
        const text = String(value);
        const flash = () => {
            this.copied = true;
            setTimeout(() => { this.copied = false; }, 1500);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(flash).catch(() => {
                this.fallbackCopy(text);
                flash();
            });
            return;
        }

        this.fallbackCopy(text);
        flash();
    },
    fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.top = '0';
        textarea.style.left = '0';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
        } catch (error) {
            // Ignore; the flash still signals the click was registered.
        }
        document.body.removeChild(textarea);
    },
}));

Alpine.start();
