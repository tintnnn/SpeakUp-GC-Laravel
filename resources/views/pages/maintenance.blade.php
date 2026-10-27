<x-layouts.app>
<div class="flex min-h-screen items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white p-8 text-center shadow-lg">
        <x-lucide-wrench class="mx-auto mb-4 h-16 w-16 text-[#1D9E75]" />
        <h1 class="mb-2 text-2xl font-bold text-gray-900">System Under Maintenance</h1>
        <p class="text-gray-600">SpeakUp GC is temporarily unavailable while the DEIU updates the system. Please check back soon.</p>
        <form method="POST" action="/logout" class="mt-6">
            @csrf
            <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-[#1D9E75] text-sm font-semibold text-white hover:bg-[#178F65]">Sign Out</button>
        </form>
    </div>
</div>
</x-layouts.app>
