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

    /*
     | Store de toast global (avisos efímeros, ej. "código no reconocido" del escaneo global).
     | Uso: $store.toast.show('mensaje', 'error' | 'success')
     */
    Alpine.store('toast', {
        visible: false,
        message: '',
        tipo: 'error',
        timer: null,
        show(message, tipo = 'error') {
            this.message = message;
            this.tipo = tipo;
            this.visible = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => { this.visible = false; }, 3500);
        },
    });
});

/*
 | Escaneo global de códigos de barra (Fase 3 — docs/barcode-spec.md §6).
 | Un lector USB/BT es un teclado: tipea el código muy rápido y termina con Enter.
 | Heurística: si el gap entre teclas es < 50ms y el buffer tiene ≥4 caracteres al llegar
 | el Enter, se trata como escaneo. Si el gap es humano (>100ms en cualquier punto), se
 | descarta el buffer acumulado hasta ahí (evita falsos positivos al tipear a mano rápido).
 |
 | Los inputs dedicados de escaneo (Ventas\Form, Alquileres\Form, data-scan-input) ya
 | manejan su propio Enter localmente (wire:keydown.enter, desde Fase 1/2) — el listener
 | global se desentiende por completo mientras el foco esté en CUALQUIER input/textarea
 | (dedicado o no), para no disparar el mismo escaneo dos veces.
 */
(function () {
    let buffer = '';
    let lastTime = 0;
    const GAP_MS = 50;
    const HUMAN_GAP_MS = 100;
    const MIN_LENGTH = 4;

    function focoEnCampoDeTexto() {
        const el = document.activeElement;
        return !!el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.isContentEditable);
    }

    function rutear(codigo) {
        let tipo;

        if (codigo.startsWith('PRD-') || /^\d+$/.test(codigo)) {
            tipo = 'venta';
        } else if (codigo.startsWith('ALQ-')) {
            tipo = 'alquiler';
        } else {
            mostrarError(`Código no reconocido: ${codigo}`);
            return;
        }

        const path = window.location.pathname;
        const destino = tipo === 'venta' ? '/ventas' : '/alquileres';
        const enFormularioDestino = path.startsWith(`${destino}/crear`) || (path.startsWith(`${destino}/`) && path.endsWith('/editar'));

        if (enFormularioDestino && window.Livewire) {
            window.Livewire.dispatch('barcode-scanned', { codigo });
        } else if (window.Livewire) {
            window.Livewire.navigate(`${destino}/crear?scan=${encodeURIComponent(codigo)}`);
        }
    }

    function mostrarError(mensaje) {
        if (window.Alpine) window.Alpine.store('toast').show(mensaje, 'error');
    }

    document.addEventListener('keydown', (e) => {
        const ahora = Date.now();
        const gap = ahora - lastTime;
        lastTime = ahora;

        if (gap > HUMAN_GAP_MS) {
            buffer = '';
        }

        if (e.key === 'Enter') {
            const codigo = buffer;
            buffer = '';

            if (focoEnCampoDeTexto()) return; // los inputs dedicados ya manejan su propio Enter
            if (codigo.length < MIN_LENGTH) return;
            if (gap > GAP_MS) return; // tecleo humano, no lector de barras

            e.preventDefault();
            rutear(codigo);
            return;
        }

        if (e.key.length === 1) {
            buffer += e.key;
        }
    });
})();
