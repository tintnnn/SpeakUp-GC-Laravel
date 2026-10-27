{{-- Port of frontend/src/views/Landing.tsx (route "/") --}}
@php
    $shell = 'w-full px-4 sm:px-6 lg:px-10 xl:px-16 2xl:px-24';
    $quickActions = [
        ['icon' => 'clipboard-list', 'title' => 'File a complaint', 'desc' => 'Tell the school what happened.'],
        ['icon' => 'activity', 'title' => 'Track your case', 'desc' => 'See each update on your complaint.'],
        ['icon' => 'message-circle', 'title' => 'Message the office', 'desc' => 'Talk with the person handling your case.'],
        ['icon' => 'book-open', 'title' => 'Know your rights', 'desc' => 'Read what the school and the law say.'],
    ];
    $steps = [
        ['n' => '01', 'title' => 'Submit', 'desc' => 'Write what happened. Use your name, or keep it hidden.'],
        ['n' => '02', 'title' => 'Track', 'desc' => 'See where your complaint is, from filing to a decision.'],
        ['n' => '03', 'title' => 'Resolve', 'desc' => 'Message the school office about your case.'],
    ];
    $didYouKnow = [
        ['icon' => 'shield-check', 'color' => 'green', 'title' => 'Safe Spaces Act (RA 11313)', 'points' => ['Protects from gender-based harassment in public & online spaces', 'Covers catcalling, unwanted remarks, and cyber harassment', 'Schools must establish complaint mechanisms']],
        ['icon' => 'users', 'color' => 'purple', 'title' => 'Gender-Based Violence', 'points' => ['Includes physical, sexual, and psychological abuse', 'Right to report without fear of retaliation', 'Support: counseling, legal aid, and safe spaces']],
        ['icon' => 'x', 'color' => 'red', 'title' => 'Bawal Bastos!', 'points' => ['Unwanted touching is harassment', 'Sexual jokes create a hostile environment', 'No means no — consent must be clear and ongoing']],
    ];
    $colorMap = [
        'green' => ['bg' => 'bg-green-50 dark:bg-[#1D9E75]/15', 'icon' => 'text-green-600 dark:text-emerald-400', 'check' => 'text-green-600 dark:text-emerald-400'],
        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-500/15', 'icon' => 'text-purple-600 dark:text-purple-300', 'check' => 'text-purple-600 dark:text-purple-300'],
        'red' => ['bg' => 'bg-red-50 dark:bg-red-500/15', 'icon' => 'text-red-600 dark:text-red-400', 'check' => 'text-red-600 dark:text-red-400'],
    ];
    $trustTags = [
        ['icon' => 'shield-check', 'label' => 'Private'],
        ['icon' => 'eye-off', 'label' => 'You can hide your name'],
        ['icon' => 'users', 'label' => 'Handled by the school office'],
    ];
    $authParam = request()->query('auth');
    $initialView = $authParam === 'login' ? 'login' : ($authParam === 'signup' ? 'signup-email' : null);
@endphp
<x-layouts.app>
<div
    class="min-h-screen bg-[#f8faf9] dark:bg-[#0f1412] text-gray-900 font-sans"
    x-data="landingPage({ initialView: @js($initialView), hasAuthParam: @js((bool) $authParam) })"
>
    {{-- ── Inline Header (kept as-is) ─────────────────────────────── --}}
    <header class="fixed top-0 z-50 w-full" x-data="{ isMenuOpen: false }">
        <div class="{{ $shell }} pt-4">
            <div class="flex h-14 items-center justify-between bg-white/90 dark:bg-[#111614]/90 backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-2xl px-5 shadow-lg shadow-gray-900/5 dark:shadow-black/40">
                <a href="/" class="flex items-center gap-2.5 min-w-0">
                    <img src="/GC.png" alt="Gordon College" class="h-10 w-10 shrink-0 object-contain">
                    <img src="/LOGO.png" alt="SpeakUp GC" class="h-10 w-10 shrink-0 object-contain">
                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100 tracking-tight truncate">SpeakUp GC</span>
                </a>
                <nav class="hidden lg:flex items-center gap-1">
                    <a href="#" @click.prevent="window.scrollTo({ top: 0, behavior: 'smooth' })" class="px-3 py-1.5 text-sm font-medium text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 transition-colors">Home</a>
                    <a href="/#steps" class="px-3 py-1.5 text-sm font-medium text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 transition-colors">How it works</a>
                    <a href="/#about" class="px-3 py-1.5 text-sm font-medium text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 transition-colors">About</a>
                </nav>
                <div class="hidden lg:flex items-center gap-2">
                    <x-theme-toggle />
                    <button type="button" class="text-sm font-medium text-gray-600 px-4 py-2 rounded-xl hover:bg-gray-100 transition-colors" @click="openWalkthrough('login')">Log in</button>
                    <button type="button" class="text-sm font-semibold bg-[#1D9E75] hover:bg-[#178F65] text-white rounded-xl px-4 py-2 transition-colors" @click="openWalkthrough()" title="Sign up to file a complaint">Get Started</button>
                </div>
                <button type="button" class="lg:hidden p-2 rounded-lg hover:bg-gray-100" @click="isMenuOpen = !isMenuOpen">
                    <span x-show="isMenuOpen" x-cloak class="contents"><x-lucide-x class="h-5 w-5" /></span>
                    <span x-show="!isMenuOpen" class="contents"><x-lucide-menu class="h-5 w-5" /></span>
                </button>
            </div>
            <div x-show="isMenuOpen" x-cloak class="lg:hidden mt-2 bg-white/95 dark:bg-[#111614]/95 backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-2xl shadow-lg overflow-hidden">
                <nav class="flex flex-col p-3 gap-1">
                    <a href="/#steps" class="px-3 py-2.5 text-sm font-medium text-gray-700 rounded-xl hover:bg-gray-100" @click="isMenuOpen = false">How it works</a>
                    <a href="/#about" class="px-3 py-2.5 text-sm font-medium text-gray-700 rounded-xl hover:bg-gray-100" @click="isMenuOpen = false">About</a>
                    <div class="border-t border-gray-100 mt-1 pt-2 flex flex-col gap-2">
                        <div class="flex items-center justify-between px-1 py-1">
                            <span class="text-xs font-medium text-gray-500">Appearance</span>
                            <x-theme-toggle />
                        </div>
                        <button type="button" class="w-full py-2.5 text-sm font-medium text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50" @click="isMenuOpen = false; openWalkthrough('login')">Log in</button>
                        <button type="button" class="w-full py-2.5 text-sm font-semibold bg-[#1D9E75] text-white rounded-xl hover:bg-[#178F65]" @click="isMenuOpen = false; openWalkthrough()">Get Started</button>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    {{-- ── Hero ───────────────────────────────────────────────────── --}}
    <section class="relative overflow-hidden bg-white dark:bg-[#111614] border-b border-gray-200 dark:border-white/10 pt-28 pb-10 lg:pt-36 lg:pb-16">
        {{-- Soft mint mesh — organic blobs sitting behind the headline --}}
        <div class="absolute inset-0 pointer-events-none dark:opacity-[0.18]" aria-hidden="true">
            <div class="absolute -left-32 -top-24 h-[26rem] w-[38rem] bg-gradient-to-br from-[#1D9E75]/25 via-emerald-200/40 to-transparent blur-3xl" style="border-radius: 58% 42% 46% 54% / 52% 44% 56% 48%"></div>
            <div class="absolute -left-10 top-40 h-[22rem] w-[30rem] bg-gradient-to-tr from-teal-200/40 via-[#1D9E75]/15 to-transparent blur-3xl" style="border-radius: 44% 56% 62% 38% / 48% 58% 42% 52%"></div>
            <div class="absolute -right-24 -top-28 h-[30rem] w-[30rem] bg-gradient-to-bl from-emerald-100/60 via-[#1D9E75]/10 to-transparent blur-3xl" style="border-radius: 50% 50% 42% 58% / 56% 46% 54% 44%"></div>
        </div>
        {{-- Subtle dot pattern — fades out toward the right of the viewport --}}
        <div class="absolute inset-y-0 left-0 w-2/3 pointer-events-none opacity-[0.35]" style="background-image: radial-gradient(circle, #1D9E75 0.5px, transparent 0.5px); background-size: 20px 20px; mask-image: linear-gradient(to right, black 30%, transparent 100%); -webkit-mask-image: linear-gradient(to right, black 30%, transparent 100%);"></div>
        <div class="relative {{ $shell }}">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 xl:gap-20 items-center">
                {{-- Left: Text --}}
                <div class="max-w-2xl">
                    <h1 class="text-4xl sm:text-5xl xl:text-6xl font-black tracking-tight text-gray-900 mb-4">SpeakUp GC</h1>
                    <p class="text-base sm:text-lg text-gray-500 leading-relaxed mb-7">
                        A private place for Gordon College students to file a complaint, see what happens next, and talk with the school office. You can hide your name if you want.
                    </p>
                    <div class="flex flex-wrap gap-3 mb-8">
                        <button type="button" @click="openWalkthrough()" class="inline-flex items-center gap-2 bg-[#1D9E75] hover:bg-[#178F65] text-white font-semibold text-sm px-6 py-3 rounded-xl shadow-lg shadow-[#1D9E75]/20 transition-colors">
                            Get Started <x-lucide-arrow-right class="w-4 h-4" />
                        </button>
                        <a href="#steps" class="inline-flex items-center gap-1.5 border border-gray-300 dark:border-white/15 text-gray-700 font-medium text-sm px-6 py-3 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            See the 3 steps
                        </a>
                    </div>
                    <div class="flex flex-wrap gap-5">
                        @foreach ($trustTags as $t)
                            <span class="flex items-center gap-1.5 text-xs font-medium text-gray-600">
                                <x-dynamic-component :component="'lucide-'.$t['icon']" class="w-3.5 h-3.5 text-[#1D9E75]" /> {{ $t['label'] }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Right: Dashboard Illustration --}}
                <div class="hidden lg:block w-full max-w-xl ml-auto">
                    <div class="bg-white dark:bg-[#18241f] rounded-2xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.12)] dark:shadow-[0_24px_64px_-12px_rgba(0,0,0,0.55)] border border-gray-200 dark:border-white/10 overflow-hidden">
                        <div class="bg-amber-50 dark:bg-amber-500/15 border-b border-amber-200 dark:border-amber-400/30 px-5 py-2">
                            <p class="text-xs font-semibold text-amber-800 dark:text-amber-200">Example only. This is not your case.</p>
                        </div>
                        <div class="bg-green-50 dark:bg-[#1D9E75]/15 border-b border-green-200 dark:border-[#1D9E75]/25 px-5 py-3 flex items-start gap-3">
                            <div class="w-7 h-7 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <x-lucide-circle-check-big class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">Complaint Filed!</p>
                                <p class="text-xs text-gray-600">CASE-002 is now active</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="bg-gradient-to-br from-gray-50 to-white dark:from-[#141a18] dark:to-[#1c2a24] border border-gray-200 dark:border-white/10 rounded-xl p-5">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-[#1D9E75] uppercase tracking-wide">Sample case</p>
                                        <p class="text-xl font-black text-gray-900 mt-0.5">CASE-002</p>
                                        <p class="text-xs text-gray-500 mt-0.5">Sexual harassment in classroom</p>
                                    </div>
                                    <div class="bg-[#1D9E75] rounded-lg px-3 py-1.5">
                                        <p class="text-[10px] text-white font-bold">Name hidden</p>
                                    </div>
                                </div>
                                <div class="space-y-2.5">
                                    @foreach ([
                                        ['done' => true, 'title' => 'Complaint Filed', 'sub' => 'Jan 14, 2026'],
                                        ['done' => true, 'title' => 'Under Review', 'sub' => 'Jan 15, 2026'],
                                        ['active' => true, 'title' => 'Investigation Ongoing', 'sub' => 'In progress'],
                                        ['pending' => true, 'title' => 'Decision Pending', 'sub' => ''],
                                    ] as $step)
                                        @php $done = $step['done'] ?? false; $active = $step['active'] ?? false; $pending = $step['pending'] ?? false; @endphp
                                        <div class="flex items-center gap-3 {{ $pending ? 'opacity-40' : '' }}">
                                            @if ($done)
                                                <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                    <x-lucide-circle-check-big class="w-3 h-3 text-white" />
                                                </div>
                                            @elseif ($active)
                                                <div class="w-5 h-5 bg-[#1D9E75] rounded-full flex items-center justify-center flex-shrink-0 animate-pulse">
                                                    <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                                                </div>
                                            @else
                                                <div class="w-5 h-5 bg-gray-200 rounded-full flex-shrink-0"></div>
                                            @endif
                                            <div>
                                                <p class="text-sm font-bold {{ $active ? 'text-[#1D9E75]' : ($pending ? 'text-gray-400' : 'text-gray-900') }}">{{ $step['title'] }}</p>
                                                @if ($step['sub'])
                                                    <p class="text-xs {{ $active ? 'text-green-600 font-medium' : 'text-gray-500' }}">{{ $step['sub'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── How It Works (first thing after the headline) ─────────── --}}
    <section id="steps" class="pt-8 pb-6">
        <div class="{{ $shell }}">
            <div class="bg-white dark:bg-[#18241f] border border-gray-200 dark:border-white/10 rounded-xl p-5 sm:p-6">
                <p class="text-[10px] font-bold text-[#1D9E75] uppercase tracking-widest mb-1">Start here</p>
                <h2 class="text-lg font-black text-gray-900 mb-1">Three steps</h2>
                <p class="text-xs text-gray-500 mb-4">Sign up first. Then you can do these.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach ($steps as $s)
                        <div class="bg-gray-50 dark:bg-white/[0.05] rounded-lg p-3">
                            <p class="text-2xl font-black text-[#1D9E75]/30 leading-none mb-1">{{ $s['n'] }}</p>
                            <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $s['title'] }}</h3>
                            <p class="text-xs text-gray-500 leading-snug">{{ $s['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
                <button type="button" @click="openWalkthrough()" class="mt-5 inline-flex items-center gap-2 bg-[#1D9E75] hover:bg-[#178F65] text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
                    Get Started <x-lucide-arrow-right class="w-4 h-4" />
                </button>
            </div>
        </div>
    </section>

    {{-- ── What you can do after signing up ──────────────────────── --}}
    <section id="features" class="pb-6">
        <div class="{{ $shell }}">
            <p class="text-xs font-semibold text-gray-700 mb-2">After you sign up, you can:</p>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5">
                @foreach ($quickActions as $item)
                    <div class="text-left bg-white dark:bg-[#18241f] border border-gray-200 dark:border-white/10 rounded-lg px-3 py-2.5">
                        <div class="w-7 h-7 bg-green-50 dark:bg-white/5 rounded-md flex items-center justify-center mb-1.5">
                            <x-dynamic-component :component="'lucide-'.$item['icon']" class="w-3.5 h-3.5 text-[#1D9E75]" />
                        </div>
                        <h3 class="text-xs font-bold text-gray-900 leading-tight">{{ $item['title'] }}</h3>
                        <p class="text-[11px] text-gray-500 leading-snug mt-0.5">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Trust Banner ───────────────────────────────────────────── --}}
    <section class="pb-6">
        <div class="{{ $shell }}">
            <div class="bg-white dark:bg-[#18241f] border border-gray-200 dark:border-white/10 rounded-lg px-3 py-2.5 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <div class="w-8 h-8 bg-green-50 dark:bg-white/5 rounded-md flex items-center justify-center flex-shrink-0">
                    <x-lucide-shield-check class="w-4 h-4 text-[#1D9E75]" />
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-sm font-bold text-gray-900 mb-0.5">Your complaint stays private</h2>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        The school office handles it. Your name is not shown to the other person unless you agree.
                    </p>
                </div>
                <div class="flex flex-wrap gap-4 flex-shrink-0">
                    @foreach ($trustTags as $t)
                        <span class="flex items-center gap-1.5 text-xs font-medium text-gray-600">
                            <x-dynamic-component :component="'lucide-'.$t['icon']" class="w-3.5 h-3.5 text-[#1D9E75]" /> {{ $t['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ── About (compact) ────────────────────────────────────────── --}}
    <section id="about" class="pb-6">
        <div class="{{ $shell }}" data-inview>
            <div class="bg-white dark:bg-[#18241f] border border-gray-200 dark:border-white/10 rounded-xl p-5 sm:p-6 transition-all duration-500 opacity-0 translate-y-4"
                 data-inview data-inview-hidden="opacity-0 translate-y-4" data-inview-class="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <div>
                        <p class="text-[10px] font-bold text-[#1D9E75] uppercase tracking-widest mb-1">About &amp; Mission</p>
                        <h2 class="text-lg font-black text-gray-900 mb-2">
                            Every student deserves to be <span class="text-[#1D9E75]">heard.</span>
                        </h2>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            SpeakUp GC is a private way for Gordon College students to tell the school office what happened. Every complaint is read, tracked, and handled with care. The details of the law are in the section below.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-2">
                        @foreach ([
                            ['icon' => 'lock', 'title' => 'Private', 'desc' => 'You can file with your name hidden.'],
                            ['icon' => 'headphones', 'title' => 'School office', 'desc' => 'A staff member guides each step.'],
                            ['icon' => 'graduation-cap', 'title' => 'Know your rights', 'desc' => 'Plain guides on the school rules and the law.'],
                        ] as $item)
                            <div class="flex items-center gap-3 bg-gray-50 dark:bg-white/[0.05] rounded-lg p-3">
                                <div class="w-8 h-8 bg-white dark:bg-[#1c2a24] border border-gray-200 dark:border-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <x-dynamic-component :component="'lucide-'.$item['icon']" class="w-4 h-4 text-[#1D9E75]" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-900">{{ $item['title'] }}</p>
                                    <p class="text-[11px] text-gray-500">{{ $item['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Did You Know (compact) ─────────────────────────────────── --}}
    <section class="pb-6">
        <div class="{{ $shell }}">
            <div class="mb-4">
                <h2 class="text-lg font-black text-gray-900">Did You Know?</h2>
                <p class="text-xs text-gray-500">Important facts about your rights under Philippine law</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($didYouKnow as $card)
                    @php $c = $colorMap[$card['color']]; @endphp
                    <div class="bg-white dark:bg-[#18241f] border border-gray-200 dark:border-white/10 rounded-xl p-4">
                        <div class="w-9 h-9 {{ $c['bg'] }} rounded-lg flex items-center justify-center mb-2">
                            <x-dynamic-component :component="'lucide-'.$card['icon']" class="w-4 h-4 {{ $c['icon'] }}" />
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 mb-2">{{ $card['title'] }}</h3>
                        <ul class="space-y-1.5">
                            @foreach ($card['points'] as $pt)
                                <li class="flex items-start gap-1.5 text-[11px] text-gray-600 leading-snug">
                                    <x-lucide-circle-check-big class="w-3.5 h-3.5 {{ $c['check'] }} flex-shrink-0 mt-0.5" />
                                    <span>{{ $pt }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            {{-- Know Your Rights CTA --}}
            <div class="landing-signin-bar mt-4 border rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <x-lucide-lock class="w-5 h-5 text-[#1D9E75]" />
                    <div>
                        <p class="text-sm font-bold text-gray-900">Sign in to read the full guides</p>
                        <p class="text-xs text-gray-500">Policies, common questions, and what to do next</p>
                    </div>
                </div>
                <a href="/?auth=login" @click.prevent="openWalkthrough('login')" class="inline-flex items-center gap-1.5 px-5 py-2 bg-[#1D9E75] hover:bg-[#178F65] text-white text-sm font-medium rounded-xl transition-colors flex-shrink-0">
                    Sign In <x-lucide-arrow-right class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    </section>

    {{-- ── Compact Footer ─────────────────────────────────────────── --}}
    <footer class="bg-white dark:bg-[#111614] border-t border-gray-200 dark:border-white/10">
        <div class="{{ $shell }} py-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="flex items-center gap-3 min-w-0">
                    <img src="/LOGO.png" alt="GC Logo" class="w-8 h-8 object-contain flex-shrink-0">
                    <div class="min-w-0">
                        <span class="font-bold text-gray-900 text-sm block">SpeakUp GC</span>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            A private place for Gordon College students to file a complaint and talk with the school office.
                        </p>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <h4 class="text-xs font-bold text-gray-900 mb-2 sm:text-right">Quick Links</h4>
                    <ul class="flex flex-wrap gap-x-4 gap-y-1 sm:justify-end">
                        <li><a href="/#steps" class="text-xs text-gray-500 hover:text-[#1D9E75] whitespace-nowrap">How it works</a></li>
                        <li><a href="/#about" class="text-xs text-gray-500 hover:text-[#1D9E75] whitespace-nowrap">About</a></li>
                        <li><a href="/privacy" class="text-xs text-gray-500 hover:text-[#1D9E75] whitespace-nowrap">Privacy Policy</a></li>
                        <li><a href="/terms" class="text-xs text-gray-500 hover:text-[#1D9E75] whitespace-nowrap">Terms &amp; Conditions</a></li>
                        <li><a href="/mission" class="text-xs text-gray-500 hover:text-[#1D9E75] whitespace-nowrap">Mission</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-100 pt-4">
                <p class="text-[10px] text-gray-400 text-center">
                    © {{ date('Y') }} SpeakUp GC · Complaints handled under RA 11313 (Safe Spaces Act) &amp; RA 7877 (Anti-Sexual Harassment Act)
                </p>
            </div>
        </div>
    </footer>

    @include('partials.walkthrough-modal')
</div>
</x-layouts.app>
