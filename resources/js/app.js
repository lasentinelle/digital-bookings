import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('combobox', ({ items = [], selectedId = null, eventName = null } = {}) => ({
    items,
    selectedId,
    eventName,
    query: '',
    open: false,
    highlightedIndex: 0,

    init() {
        if (this.selectedId !== null && this.selectedId !== '') {
            const match = this.items.find((item) => item.id === this.selectedId);
            if (match) {
                this.query = match.label;
            }
        }
    },

    get filtered() {
        const needle = this.query.trim().toLowerCase();
        if (needle === '') {
            return this.items.slice(0, 50);
        }
        return this.items.filter((item) => item.label.toLowerCase().startsWith(needle)).slice(0, 50);
    },

    select(item) {
        this.selectedId = item.id;
        this.query = item.label;
        this.open = false;
        this.highlightedIndex = 0;
        this.dispatchSelection();
    },

    clearSelection() {
        this.selectedId = null;
        this.query = '';
        this.dispatchSelection();
    },

    dispatchSelection() {
        if (this.eventName) {
            this.$dispatch(this.eventName, { id: this.selectedId });
        }
    },

    highlightNext() {
        if (!this.open) {
            this.open = true;
        }
        if (this.highlightedIndex < this.filtered.length - 1) {
            this.highlightedIndex += 1;
        }
    },

    highlightPrev() {
        if (this.highlightedIndex > 0) {
            this.highlightedIndex -= 1;
        }
    },

    selectHighlighted() {
        if (this.open && this.filtered[this.highlightedIndex]) {
            this.select(this.filtered[this.highlightedIndex]);
        }
    },
}));

Alpine.data('linkableCombobox', ({ items = [], selectedId = null } = {}) => ({
    items,
    selectedId,
    query: '',
    open: false,

    init() {
        if (this.selectedId !== null && this.selectedId !== '') {
            const match = this.items.find((item) => item.id === this.selectedId);
            if (match) {
                this.query = match.label;
            }
        }
    },

    get filtered() {
        const needle = this.query.trim().toLowerCase();
        if (needle === '') {
            return this.items.slice(0, 50);
        }
        return this.items.filter((item) => item.label.toLowerCase().includes(needle)).slice(0, 50);
    },

    select(item) {
        this.selectedId = item.id;
        this.query = item.label;
        this.open = false;
    },
}));

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
