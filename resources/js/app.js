/**
 * SpeakUp GC – client runtime for the Blade implementation.
 *
 * Replaces the React providers from frontend/app/providers.tsx:
 *   - next-themes            -> theme store (class="dark" on <html>, storageKey "speakup-theme")
 *   - shadcn <Toaster/>      -> ./toast.js (same markup/classes as components/ui/toast.tsx)
 *   - sonner <Toaster/>      -> sonner is kept (same library) – see ./sonner.js
 *   - UnsupportedBrowserGate -> ./browser-gate.js
 *   - React interactivity    -> Alpine.js components registered in ./components/*
 */
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import persist from '@alpinejs/persist';

import { installTheme } from './theme';
import { toast, installToaster } from './toast';
import { installBrowserGate } from './browser-gate';
import { installScrollAnimations } from './scroll-animations';
import { registerComponents } from './components';

window.Alpine = Alpine;
window.toast = toast;

Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(persist);

installTheme(Alpine);
installBrowserGate();
registerComponents(Alpine);

document.addEventListener('DOMContentLoaded', () => {
    installToaster();
    installScrollAnimations();
});

Alpine.start();
