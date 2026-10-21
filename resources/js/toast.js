/**
 * Port of the shadcn/radix toast used across the original app
 * (frontend/src/components/ui/toast.tsx, toaster.tsx, hooks/use-toast.ts).
 *
 * Behaviour preserved:
 *   - TOAST_LIMIT = 1 (a new toast replaces the visible one)
 *   - viewport: fixed top on mobile, bottom-right on >= sm, max-w 420px on >= md
 *   - toast markup: LOGO.png (28px, rounded-full, opacity-70) + title + description + close (X)
 *   - variants: default | destructive
 *   - swipe-to-dismiss is replaced by click on X / auto-dismiss after 5s
 *     (radix default duration is 5000ms via the Provider)
 */
const DURATION = 5000;
let viewport = null;
let current = null;
let timer = null;

export function installToaster() {
    if (viewport) return;
    viewport = document.createElement('ol');
    viewport.tabIndex = -1;
    viewport.className =
        'fixed top-0 z-[9999] flex max-h-screen w-full flex-col-reverse p-4 sm:bottom-0 sm:right-0 sm:top-auto sm:flex-col md:max-w-[420px]';
    viewport.setAttribute('role', 'region');
    viewport.setAttribute('aria-label', 'Notifications (F8)');
    document.body.appendChild(viewport);
}

function dismiss() {
    if (!current) return;
    const el = current;
    current = null;
    clearTimeout(timer);
    el.setAttribute('data-state', 'closed');
    el.addEventListener('animationend', () => el.remove(), { once: true });
    setTimeout(() => el.remove(), 400);
}

export function toast({ title, description, variant = 'default', duration = DURATION } = {}) {
    installToaster();
    dismiss();

    const li = document.createElement('li');
    li.setAttribute('role', 'status');
    li.setAttribute('aria-live', 'off');
    li.setAttribute('data-state', 'open');
    li.className =
        'group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-6 pr-8 shadow-lg transition-all data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full data-[state=open]:sm:slide-in-from-bottom-full ' +
        (variant === 'destructive'
            ? 'destructive group border-destructive bg-destructive text-destructive-foreground'
            : 'border bg-background text-foreground');

    const wrap = document.createElement('div');
    wrap.className = 'flex items-center gap-2';
    const img = document.createElement('img');
    img.src = '/LOGO.png';
    img.alt = 'SpeakUp GC';
    img.width = 28;
    img.height = 28;
    img.className = 'rounded-full opacity-70';
    const grid = document.createElement('div');
    grid.className = 'grid gap-1';
    if (title) {
        const t = document.createElement('div');
        t.className = 'text-sm font-semibold';
        t.textContent = title;
        grid.appendChild(t);
    }
    if (description) {
        const d = document.createElement('div');
        d.className = 'text-sm opacity-90';
        d.textContent = description;
        grid.appendChild(d);
    }
    wrap.appendChild(img);
    wrap.appendChild(grid);

    const close = document.createElement('button');
    close.type = 'button';
    close.setAttribute('toast-close', '');
    close.className =
        'absolute right-2 top-2 rounded-md p-1 text-foreground/50 opacity-0 transition-opacity hover:text-foreground focus:opacity-100 focus:outline-none focus:ring-2 group-hover:opacity-100 group-[.destructive]:text-red-300 group-[.destructive]:hover:text-red-50 group-[.destructive]:focus:ring-red-400 group-[.destructive]:focus:ring-offset-red-600';
    close.innerHTML =
        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
    close.addEventListener('click', dismiss);

    li.appendChild(wrap);
    li.appendChild(close);
    viewport.appendChild(li);
    current = li;
    timer = setTimeout(dismiss, duration);

    return { dismiss };
}

/**
 * Flash messages from the Laravel session are rendered as toasts so server-driven
 * flows (login errors, password reset, etc.) show the same feedback as the original SPA.
 */
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('speakup-flash');
    if (!el) return;
    try {
        const items = JSON.parse(el.textContent || '[]');
        items.forEach((t, i) => setTimeout(() => toast(t), 150 * i));
    } catch {}
});
