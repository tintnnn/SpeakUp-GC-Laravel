{{-- Port of frontend/src/components/TermsModal.tsx. Usage: wrap in an Alpine scope exposing `open` --}}
@props(['model', 'title'])
<template x-if="{{ $model }}">
<div class="fixed inset-0 z-[100] flex items-center justify-center">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="{{ $model }} = false"></div>

    {{-- Modal --}}
    <div class="relative bg-background rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto border border-border/50">
        {{-- Header --}}
        <div class="sticky top-0 bg-background border-b border-border/30 px-6 py-4 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-foreground">{{ $title }}</h2>
            <button type="button" @click="{{ $model }} = false" class="p-1 hover:bg-accent rounded-lg transition-colors">
                <x-lucide-x class="w-6 h-6 text-foreground" />
            </button>
        </div>

        {{-- Content --}}
        <div class="px-6 py-6">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        <div class="border-t border-border/30 px-6 py-4 bg-muted/20 sticky bottom-0">
            <button type="button" @click="{{ $model }} = false" class="w-full bg-gradient-to-r from-primary to-secondary text-primary-foreground font-semibold py-2.5 rounded-lg hover:shadow-lg transition-all duration-300">
                Got it, close
            </button>
        </div>
    </div>
</div>
</template>
