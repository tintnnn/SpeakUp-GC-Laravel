{{-- Port of app/(protected)/layout.tsx + Sidebar.tsx + ComplainantTopBar.tsx --}}
@php
    $user = auth()->user();
    $alias = $user->alias ?: null;
    $initials = $alias
        ? strtoupper(mb_substr($alias, 0, 2))
        : strtoupper(mb_substr(preg_replace('/\s+/', '', $user->display_name ?: 'U') ?? 'U', 0, 2));
    if ($alias) {
        $initials = strtoupper(mb_substr($alias, 0, 2));
    } elseif ($user->display_name) {
        $parts = preg_split('/\s+/', trim($user->display_name));
        $initials = strtoupper(mb_substr($parts[0] ?? 'U', 0, 1).mb_substr($parts[1] ?? '', 0, 1));
    } else {
        $initials = 'U';
    }
    $display = $alias ?: (explode(' ', $user->display_name ?: 'User')[0]);
    $roleLabel = ($user->role ?: 'complainant') === 'complainant' ? 'Complainant' : ucfirst((string) ($user->role ?: 'User'));
    $unreadCases = 0;
    $nav = [
        ['name' => 'Dashboard', 'mobile' => 'Home', 'href' => '/dashboard', 'icon' => 'layout-dashboard'],
        ['name' => 'File a Complaint', 'mobile' => 'Report', 'href' => '/complaints/new', 'icon' => 'file-text'],
        ['name' => 'My Cases', 'mobile' => 'Cases', 'href' => '/complaints', 'icon' => 'briefcase', 'badge' => $unreadCases],
        ['name' => 'Know Your Rights', 'mobile' => 'Rights', 'href' => '/know-your-rights', 'icon' => 'shield-check'],
        ['name' => 'My Profile', 'mobile' => 'Profile', 'href' => '/account', 'icon' => 'user-circle', 'accent' => 'text-gray-500'],
    ];
    $subs = [
        ['label' => 'Legal Rights', 'tab' => 'laws', 'icon' => 'scale'],
        ['label' => 'How to Report', 'tab' => 'reporting', 'icon' => 'file-text'],
        ['label' => 'FAQs', 'tab' => 'faq', 'icon' => 'help-circle'],
        ['label' => 'Wellness', 'tab' => 'wellness', 'icon' => 'heart'],
        ['label' => 'GC Policies', 'tab' => 'policy', 'icon' => 'gavel'],
    ];
    $path = '/'.ltrim(request()->path(), '/');
    $active = function (string $href) use ($path): bool {
        if ($path === $href) return true;
        if ($href === '/complaints') {
            return str_starts_with($path, '/complaints/') && ! str_starts_with($path, '/complaints/new');
        }
        if ($href !== '/') return str_starts_with($path, $href.'/');
        return false;
    };
    $bellNotes = $user->appNotifications()->latest('created_at')->limit(8)->get();
    $unreadNotes = $user->appNotifications()->where('status', 'unread')->count();
@endphp
<x-layouts.app>
<div
    x-data="{
        collapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        logoutOpen: false,
        notesOpen: false,
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebarCollapsed', String(this.collapsed));
        }
    }"
    class="flex h-screen overflow-hidden"
>
    <aside class="hidden h-screen lg:flex lg:flex-shrink-0">
        <div
            class="relative flex h-screen w-60 flex-col overflow-hidden border-r border-gray-200 bg-white dark:border-border dark:bg-card"
            :class="collapsed ? 'w-20' : 'w-60'"
        >
            <div class="flex items-center justify-between border-b border-gray-200 px-4 pb-5 pt-6 dark:border-border">
                <div x-show="!collapsed" class="flex w-full items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-green-50 p-0.5">
                            <img src="/LOGO.png" alt="Gordon College" class="h-full w-full object-contain">
                        </div>
                        <span class="text-base font-bold tracking-tight text-gray-900 dark:text-gray-100">SpeakUp GC</span>
                    </div>
                    <button type="button" @click="toggle()" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700" title="Collapse sidebar">
                        <x-lucide-chevron-left class="h-4 w-4" />
                    </button>
                </div>
                <div x-show="collapsed" x-cloak class="flex w-full flex-col items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-green-50 p-0.5">
                        <img src="/LOGO.png" alt="Gordon College" class="h-full w-full object-contain">
                    </div>
                    <button type="button" @click="toggle()" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700" title="Expand sidebar">
                        <x-lucide-chevron-right class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
                @foreach ($nav as $item)
                    @php $on = $active($item['href']); @endphp
                    <div>
                        <a href="{{ $item['href'] }}" title="{{ $item['name'] }}" class="group relative flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1D9E75]/30 {{ $on ? 'bg-[#1D9E75] text-white shadow-sm dark:bg-[#1D9E75]/25 dark:text-emerald-100 dark:shadow-none dark:ring-1 dark:ring-[#1D9E75]/40' : 'text-gray-600 hover:bg-green-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}" :class="collapsed ? 'justify-center px-3 py-3' : 'px-3.5 py-2.5'">
                            <span class="flex items-center justify-center rounded-lg p-1.5 {{ $on ? 'bg-white/20' : 'bg-green-50 group-hover:bg-green-100 dark:bg-white/5 dark:group-hover:bg-white/10' }}">
                                <x-dynamic-component :component="'lucide-'.$item['icon']" class="h-[18px] w-[18px] flex-shrink-0 {{ $on ? 'text-white' : ($item['accent'] ?? 'text-[#1D9E75] group-hover:text-[#178F65]') }}" />
                            </span>
                            <span x-show="!collapsed" class="flex flex-1 items-center">
                                <span class="flex-1 text-sm font-medium">{{ $item['name'] }}</span>
                                @if (($item['badge'] ?? 0) > 0)
                                    <span class="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-semibold text-white">{{ $item['badge'] > 9 ? '9+' : $item['badge'] }}</span>
                                @endif
                            </span>
                        </a>
                        @if ($item['href'] === '/know-your-rights' && $on)
                            <div x-show="!collapsed" class="ml-5 mt-1 space-y-0.5 border-l-2 border-gray-200 pl-3">
                                @foreach ($subs as $sub)
                                    <a href="/know-your-rights?tab={{ $sub['tab'] }}" class="group flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-xs font-medium text-gray-500 transition-colors hover:bg-green-50 hover:text-[#1D9E75] {{ request('tab') === $sub['tab'] ? 'text-[#1D9E75]' : '' }}">
                                        <x-dynamic-component :component="'lucide-'.$sub['icon']" class="h-3.5 w-3.5 flex-shrink-0 text-gray-400 group-hover:text-[#1D9E75]" />
                                        <span>{{ $sub['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </nav>

            <div x-show="!collapsed" class="px-3 py-3">
                <div class="rounded-xl bg-green-50 p-4 dark:bg-[#1a3329]" style="border: 1px solid var(--card-outline)">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 rounded-lg bg-white p-2 dark:bg-card" style="border: 1px solid var(--card-outline)">
                            <x-lucide-help-circle class="h-4 w-4 text-[#1D9E75]" />
                        </div>
                        <div>
                            <p class="mb-1 text-xs font-bold text-gray-900">Need Help?</p>
                            <p class="mb-2 text-xs leading-relaxed text-gray-700">Contact DEIU for support and guidance.</p>
                            <a href="/know-your-rights?tab=faq" class="flex items-center gap-1 text-xs font-semibold text-[#1D9E75] transition-colors hover:text-[#178F65]">View FAQs →</a>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="!collapsed" class="border-t border-gray-200 px-4 py-4 dark:border-border">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#1D9E75] text-sm font-bold text-white shadow-sm ring-2 ring-white dark:ring-card">{{ $initials }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold leading-tight text-gray-900 dark:text-gray-100">{{ $display }}</p>
                        <p class="mt-0.5 text-[11px] font-medium text-gray-500 dark:text-gray-400">{{ $roleLabel }}</p>
                    </div>
                    <x-theme-toggle />
                </div>
            </div>
            <div x-show="collapsed" x-cloak class="flex flex-col items-center gap-2 border-t border-gray-200 px-3 py-3 dark:border-border">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#1D9E75] text-sm font-bold text-white" title="{{ $display }}">{{ $initials }}</div>
                <x-theme-toggle />
            </div>

            <div class="border-t border-gray-200 px-3 py-3">
                <button type="button" @click="logoutOpen = true" class="group flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-500 transition-colors hover:bg-red-50 hover:text-red-600" :class="collapsed ? 'justify-center px-3 py-3' : 'px-3.5 py-2.5'" title="Sign Out">
                    <x-lucide-log-out class="h-5 w-5 flex-shrink-0 text-gray-400 transition-colors group-hover:text-red-500" />
                    <span x-show="!collapsed">Sign Out</span>
                </button>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
        <header class="sticky top-0 z-50 flex h-14 shrink-0 items-center justify-end border-b border-gray-200 bg-white px-4 dark:border-border dark:bg-card sm:h-16 sm:px-6">
            <div class="relative">
                <button type="button" @click="notesOpen = !notesOpen" class="relative flex h-10 items-center gap-2 rounded-full border border-gray-200 bg-white px-3 shadow-sm hover:border-[#1D9E75]/35 hover:bg-emerald-50/70 dark:border-[#3d5c50] dark:bg-[#1c2c26] sm:px-4" title="Notifications">
                    <x-lucide-bell class="h-[18px] w-[18px] text-gray-700" />
                    <span class="hidden text-sm font-medium text-gray-700 dark:text-gray-100 sm:inline">Notifications</span>
                    @if ($unreadNotes > 0)
                        <span class="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white">{{ $unreadNotes > 9 ? '9+' : $unreadNotes }}</span>
                    @endif
                </button>
                <div x-show="notesOpen" x-cloak @click.outside="notesOpen = false" class="absolute right-0 top-12 z-50 w-80 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-border dark:bg-card">
                    <div class="flex items-center justify-between border-b px-4 py-3">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">Notifications</h3>
                        @if ($unreadNotes > 0)
                            <form method="POST" action="/notifications/read-all">@csrf<button class="text-xs font-medium text-[#1D9E75]">Mark all read</button></form>
                        @endif
                    </div>
                    <div class="max-h-[450px] overflow-y-auto">
                        @forelse ($bellNotes as $note)
                            <form method="POST" action="/notifications/{{ $note->id }}/read">
                                @csrf
                                <input type="hidden" name="next" value="{{ $note->action_url ?: '/notifications' }}">
                                <button type="submit" class="block w-full border-b px-4 py-3 text-left last:border-0 hover:bg-gray-50 {{ $note->status === 'unread' ? 'bg-emerald-50/60' : '' }}">
                                    <p class="text-sm text-gray-900 {{ $note->status === 'unread' ? 'font-semibold' : 'font-medium' }}">{{ $note->title }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">{{ $note->message }}</p>
                                    <p class="mt-1 text-[11px] text-gray-400">{{ optional($note->created_at)->timezone('Asia/Manila')->format('M d, Y • g:i A') }}</p>
                                </button>
                            </form>
                        @empty
                            <div class="px-4 py-8 text-center">
                                <p class="text-sm font-medium">No notifications</p>
                                <p class="mt-1 text-xs text-gray-500">You're all caught up!</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="border-t px-4 py-2 text-right"><a href="/notifications" class="text-sm font-medium text-[#1D9E75]">View all notifications</a></div>
                </div>
            </div>
        </header>
        {{-- Top padding lives on an inner wrapper, not the scrollport. Padding on the
             scrolling <main> leaves a gap above position:sticky, so the Rights tabs
             looked like they were floating over the card. --}}
        <main class="flex-1 overflow-y-auto bg-white px-6 pb-[calc(6rem+env(safe-area-inset-bottom))] pt-0 dark:bg-background lg:px-8 lg:pb-8 lg:pt-6">
            <div class="pt-4 lg:pt-0">
                {{ $slot }}
            </div>
        </main>
    </div>

    <div class="pointer-events-none fixed inset-x-0 bottom-0 z-50 px-3 pb-[max(0.625rem,env(safe-area-inset-bottom))] lg:hidden">
        <nav aria-label="Primary" class="pointer-events-auto mx-auto flex w-full max-w-md items-center gap-0.5 rounded-[26px] border border-gray-200/70 bg-white/85 p-1.5 shadow-[0_12px_32px_-10px_rgba(16,48,36,0.35)] backdrop-blur-xl">
            @foreach ($nav as $item)
                @php $on = $active($item['href']); @endphp
                <a href="{{ $item['href'] }}" class="relative flex flex-1 flex-col items-center gap-1 rounded-[20px] px-1 py-2 {{ $on ? 'bg-[#1D9E75]/10' : '' }}">
                    <span class="relative flex h-6 w-6 items-center justify-center">
                        <x-dynamic-component :component="'lucide-'.$item['icon']" class="h-5 w-5 {{ $on ? 'text-[#1D9E75]' : 'text-gray-400' }}" />
                    </span>
                    <span class="text-[10px] leading-none {{ $on ? 'font-bold text-[#1D9E75]' : 'font-semibold text-gray-500' }}">{{ $item['mobile'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div x-show="logoutOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4">
        <div class="w-full max-w-lg rounded-lg border border-gray-200 bg-white p-6 shadow-lg dark:border-border dark:bg-card" @click.outside="logoutOpen = false">
            <h2 class="text-lg font-semibold">Sign out?</h2>
            <p class="mt-2 text-sm text-gray-500">Are you sure you want to sign out of SpeakUp GC?</p>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="logoutOpen = false" class="inline-flex h-10 items-center rounded-md border border-gray-200 px-4 text-sm font-medium">Cancel</button>
                <form method="POST" action="/logout">@csrf<button class="inline-flex h-10 items-center rounded-md bg-red-600 px-4 text-sm font-medium text-white hover:bg-red-700">Sign Out</button></form>
            </div>
        </div>
    </div>
</div>
@if (! $user->profile_setup_complete && ! $user->isStaff())
    @include('partials.profile-setup-modal')
@endif
</x-layouts.app>
