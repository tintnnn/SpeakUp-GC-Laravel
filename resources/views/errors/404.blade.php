{{-- Port of frontend/src/views/NotFound.tsx --}}
<x-layouts.app>
<div class="min-h-screen bg-gradient-card flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <div class="mb-8">
            <h1 class="text-8xl font-bold text-primary mb-4">404</h1>
            <h2 class="text-2xl font-semibold text-foreground mb-4">Page Not Found</h2>
            <p class="text-muted-foreground mb-8">
                The page you're looking for doesn't exist or has been moved to a secure location.
            </p>
        </div>
        <div class="space-y-4">
            <a href="/" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium h-11 px-8 gradient-hero text-primary-foreground hover:opacity-90 shadow-glow transition-trust">
                <x-lucide-house class="w-4 h-4" />
                Return to SpeakUp GC Home
            </a>
            <button type="button" onclick="window.history.back()" class="flex items-center justify-center gap-2 w-full whitespace-nowrap rounded-md text-sm font-medium h-11 px-8 border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-smooth">
                <x-lucide-arrow-left class="w-4 h-4" />
                Go Back
            </button>
        </div>
    </div>
</div>
</x-layouts.app>
