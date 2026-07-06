import './bootstrap';

/*
 | Tooltip global para elementos con [data-tip].
 | Usa position:fixed (escapa overflow de las tablas) y delegación en document,
 | así funciona con contenido nuevo de Livewire y tras wire:navigate.
 | Estilo: fondo blanco, letras negras, grandes y anchas (bold).
 */
(function () {
    let tip;

    function ensureTip() {
        if (tip && document.body.contains(tip)) return tip;
        tip = document.createElement('div');
        tip.setAttribute('data-app-tooltip', '');
        tip.style.cssText = [
            'position:fixed',
            'z-index:9999',
            'background:#ffffff',
            'color:#000000',
            'font-weight:700',
            'font-size:16px',
            'letter-spacing:.2px',
            'line-height:1.2',
            'padding:10px 16px',
            'border-radius:10px',
            'border:1px solid #e5e5e5',
            'box-shadow:0 8px 24px rgba(0,0,0,.20)',
            'pointer-events:none',
            'white-space:nowrap',
            'opacity:0',
            'transform:translate(-50%,-100%)',
            'transition:opacity .12s ease',
        ].join(';');
        document.body.appendChild(tip);
        return tip;
    }

    function position(el) {
        const r = el.getBoundingClientRect();
        tip.style.left = (r.left + r.width / 2) + 'px';
        tip.style.top = (r.top - 10) + 'px';
    }

    let current = null;

    function show(el) {
        ensureTip();
        current = el;
        tip.textContent = el.getAttribute('data-tip');
        position(el);
        tip.style.opacity = '1';
    }

    function hide() {
        current = null;
        if (tip) tip.style.opacity = '0';
    }

    document.addEventListener('mouseover', (e) => {
        const el = e.target.closest('[data-tip]');
        if (el && el.getAttribute('data-tip')) show(el);
    });

    document.addEventListener('mouseout', (e) => {
        const el = e.target.closest('[data-tip]');
        if (el && el === current) hide();
    });

    // Re-posicionar/ocultar en scroll y navegación SPA.
    window.addEventListener('scroll', () => { if (current) position(current); }, true);
    document.addEventListener('livewire:navigating', hide);
    document.addEventListener('livewire:navigated', () => { ensureTip(); hide(); });

    ensureTip();
})();

/*
 | Store de confirmación global (reemplaza el confirm() nativo del navegador).
 | Uso en Blade:
 |   x-on:click="$store.confirm.open('¿Mensaje?', () => $wire.metodo(id))"
 | El modal vive en el layout (components/layouts/app.blade.php).
 */
document.addEventListener('alpine:init', () => {
    Alpine.store('confirm', {
        show: false,
        message: '',
        onAccept: null,
        open(message, onAccept) {
            this.message = message;
            this.onAccept = onAccept;
            this.show = true;
        },
        accept() {
            const cb = this.onAccept;
            this.close();
            if (cb) cb();
        },
        close() {
            this.show = false;
            this.onAccept = null;
        },
    });
});
