{{-- Port of frontend/src/components/layout/InfoPageLayout.tsx --}}
@props(['title', 'description', 'sections', 'lastUpdated' => null, 'links'])
<x-layouts.app>
<div class="min-h-screen bg-[#f8faf9] dark:bg-[#0f1412] text-gray-900 font-sans">
    <div class="max-w-3xl landscape:max-w-5xl mx-auto px-4 sm:px-6 py-10">
        <a href="/" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 mb-8 transition-colors">
            <x-lucide-house class="w-4 h-4" />
            Home
        </a>

        <div class="landscape:flex landscape:gap-10 landscape:items-start">
            <div class="landscape:min-w-0 landscape:flex-1">
                <header class="mb-8 pb-6 border-b border-gray-200">
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 mb-2">{{ $title }}</h1>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $description }}</p>
                    @if ($lastUpdated)
                        <p class="text-xs text-gray-400 mt-3">Last updated: {{ $lastUpdated }}</p>
                    @endif
                </header>

                <div class="space-y-6">
                    @foreach ($sections as $section)
                        <section>
                            <h2 class="text-sm font-bold text-gray-900 mb-2">{{ $section['title'] }}</h2>
                            @if (is_array($section['content']))
                                <ul class="space-y-1.5">
                                    @foreach ($section['content'] as $item)
                                        <li class="text-sm text-gray-600 leading-relaxed pl-4 relative before:content-['•'] before:absolute before:left-0 before:text-gray-400">{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-600 leading-relaxed">{{ $section['content'] }}</p>
                            @endif
                        </section>
                    @endforeach
                </div>
            </div>

            <aside class="mt-10 pt-6 border-t border-gray-200 landscape:mt-0 landscape:pt-0 landscape:border-t-0 landscape:border-l landscape:pl-8 landscape:w-52 landscape:shrink-0 landscape:sticky landscape:top-10">
                <p class="text-xs text-gray-400 mb-3">Related</p>
                <div class="flex flex-wrap gap-x-4 gap-y-1 landscape:flex-col landscape:gap-2">
                    @foreach ($links as $link)
                        <a href="{{ $link['href'] }}" class="text-sm text-[#1D9E75] hover:text-[#178F65] transition-colors">{{ $link['label'] }}</a>
                    @endforeach
                </div>
            </aside>
        </div>
    </div>
</div>
</x-layouts.app>
