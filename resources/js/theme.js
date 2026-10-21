/**
 * Port of next-themes usage in frontend/app/providers.tsx:
 *   <ThemeProvider attribute="class" defaultTheme="light" enableSystem={false} storageKey="speakup-theme">
 *
 * The <html> element gets class "dark" when the stored theme is dark. The inline
 * script in layouts/app.blade.php applies the class before first paint to avoid a flash,
 * exactly like next-themes does.
 */
const STORAGE_KEY = 'speakup-theme';

export function readTheme() {
    try {
        const v = localStorage.getItem(STORAGE_KEY);
        return v === 'dark' ? 'dark' : 'light';
    } catch {
        return 'light';
    }
}

export function applyTheme(theme) {
    const root = document.documentElement;
    root.classList.toggle('dark', theme === 'dark');
    root.style.colorScheme = theme;
    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch {}
    window.dispatchEvent(new CustomEvent('speakup:theme', { detail: { theme } }));
}

export function installTheme(Alpine) {
    Alpine.store('theme', {
        value: readTheme(),
        get isDark() {
            return this.value === 'dark';
        },
        set(theme) {
            this.value = theme;
            applyTheme(theme);
        },
        toggle() {
            this.set(this.value === 'dark' ? 'light' : 'dark');
        },
    });
    applyTheme(readTheme());
}
