{{-- Port of frontend/src/components/ThemeToggle.tsx --}}
@props(['compact' => false])
<button
    type="button"
    x-data
    @click="$store.theme.toggle()"
    {{ $attributes->merge(['class' => 'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/10']) }}
    :aria-label="$store.theme.isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    :title="$store.theme.isDark ? 'Light mode' : 'Dark mode'"
>
    <span x-show="$store.theme.isDark" x-cloak class="contents"><x-lucide-sun class="h-4 w-4" /></span>
    <span x-show="!$store.theme.isDark" class="contents"><x-lucide-moon class="h-4 w-4" /></span>
</button>
