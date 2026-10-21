{{-- Port of frontend/src/components/admin/AdminLayout.tsx --}}
@php
    $adminUser = auth()->user();
    $repRole = $adminUser->representative?->role;
    $isAdminNav = $adminUser->isPlatformAdmin() || $repRole === 'admin';
    $isCODI = ! $isAdminNav && in_array($repRole, ['handler', 'codi'], true);
    $roleLabel = $isAdminNav ? 'Administrator' : ($isCODI ? 'CODI' : 'Staff');
    $displayName = $adminUser->display_name ?: 'Admin';
    $seenCaseIds = \Illuminate\Support\Facades\DB::table('case_views')
        ->where('user_id', $adminUser->id)
        ->pluck('complaint_id');
    $unseenQueue = \App\Models\Complaint::query()
        ->where('is_deleted', false)
        ->whereIn('status', ['pending', 'submitted', 'inProgress'])
        ->when($seenCaseIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $seenCaseIds));
    if ($isCODI) {
        $repId = $adminUser->representative?->id;
        $unseenQueue->where(function ($query) use ($repId) {
            $query->whereNull('assigned_representative_id');
            if ($repId) {
                $query->orWhere('assigned_representative_id', $repId);
            }
        });
    }
    $pendingCount = $unseenQueue->count();
    $unreadCount = $adminUser->appNotifications()->where('status', 'unread')->count();
    $notifications = $adminUser->appNotifications()->latest('created_at')->limit(8)->get();
    $nav = $isCODI
        ? [
            ['label' => 'Dashboard', 'icon' => 'layout', 'href' => '/admin/dashboard'],
            ['label' => 'Case Queue', 'icon' => 'file-text', 'href' => '/admin/reports', 'badge' => $pendingCount, 'badgeClass' => 'bg-red-500'],
            ['label' => 'Messages', 'icon' => 'message-square', 'href' => '/admin/messages', 'badge' => 0, 'badgeClass' => 'bg-[#1D9E75]'],
            ['label' => 'Closed Cases', 'icon' => 'archive', 'href' => '/admin/closed-cases'],
            ['label' => 'My Performance', 'icon' => 'bar-chart', 'href' => '/admin/analytics'],
        ]
        : [
            ['label' => 'Dashboard', 'icon' => 'layout', 'href' => '/admin/dashboard'],
            ['label' => 'Representatives', 'icon' => 'user-plus', 'href' => '/admin/representatives'],
            ['label' => 'Users', 'icon' => 'users', 'href' => '/admin/users'],
            ['label' => 'Reports', 'icon' => 'file-text', 'href' => '/admin/reports', 'badge' => $pendingCount, 'badgeClass' => 'bg-red-500'],
            ['label' => 'Analytics', 'icon' => 'bar-chart', 'href' => '/admin/analytics'],
            ['label' => 'Closed Cases', 'icon' => 'archive', 'href' => '/admin/closed-cases'],
            ['label' => 'Compliance Reports', 'icon' => 'file-check', 'href' => '/admin/compliance-reports'],
            ['label' => 'Settings', 'icon' => 'settings', 'href' => '/admin/settings'],
        ];
@endphp
<x-layouts.app>
<div
    x-data="{
        collapsed: localStorage.getItem('adminSidebarCollapsed') === 'true',
        logoutOpen: false,
        notesOpen: false,
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('adminSidebarCollapsed', String(this.collapsed));
        }
    }"
    class="flex min-h-screen bg-gray-100 dark:bg-background"
>
    <div
        class="fixed inset-y-0 left-0 z-20 w-64 flex-shrink-0 border-r border-gray-200 bg-white dark:border-border dark:bg-card"
        :class="collapsed ? 'w-20' : 'w-64'"
    >
        <div class="flex h-full flex-col">
            <div class="border-b border-gray-200 px-6 py-6 dark:border-border">
                <div x-show="!collapsed" class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center">
                            <img src="/LOGO.png" alt="SpeakUp GC Logo" class="h-full w-full object-contain">
                        </div>
                        <h1 class="whitespace-nowrap text-xl font-bold leading-tight text-gray-900 dark:text-gray-100">SpeakUp GC</h1>
                    </div>
                    <button type="button" @click="toggle()" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200" title="Collapse sidebar">
                        <x-lucide-chevron-left class="h-4 w-4" />
                    </button>
                </div>
                <div x-show="collapsed" x-cloak class="flex w-full flex-col items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center">
                        <img src="/LOGO.png" alt="SpeakUp GC Logo" class="h-full w-full object-contain">
                    </div>
                    <button type="button" @click="toggle()" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200" title="Expand sidebar">
                        <x-lucide-chevron-right class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <nav class="flex-1 space-y-2 overflow-y-auto px-4 py-6">
                @foreach ($nav as $item)
                    @php $active = request()->is(trim($item['href'], '/')); @endphp
                    <a
                        href="{{ $item['href'] }}"
                        title="{{ $item['label'] }}"
                        class="group relative flex items-center gap-3 rounded-xl px-4 py-3.5 transition-colors {{ $active ? 'bg-[#1D9E75] text-white shadow-md dark:bg-[#1D9E75]/30 dark:text-emerald-100 dark:shadow-none dark:ring-1 dark:ring-[#1D9E75]/40' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}"
                        :class="collapsed ? 'justify-center px-3 py-3.5' : 'px-4 py-3.5'"
                    >
                        <span class="relative">
                            <x-dynamic-component :component="'lucide-'.$item['icon']" class="h-5 w-5 flex-shrink-0 {{ $active ? 'text-white dark:text-emerald-100' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-200' }}" />
                            @if (($item['badge'] ?? 0) > 0)
                                <span x-show="collapsed" x-cloak class="absolute -right-1 -top-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full px-1 text-[10px] font-bold text-white {{ $item['badgeClass'] }}">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                            @endif
                        </span>
                        <span x-show="!collapsed" class="flex flex-1 items-center gap-3">
                            <span class="flex-1 text-sm font-medium">{{ $item['label'] }}</span>
                            @if (($item['badge'] ?? 0) > 0)
                                <span class="flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-xs font-semibold text-white {{ $item['badgeClass'] }}">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </nav>

            <div x-show="!collapsed" class="border-t border-gray-200 px-5 py-4 dark:border-border">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#1D9E75] to-emerald-600">
                        <x-lucide-user class="h-5 w-5 text-white" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold leading-tight text-gray-800 dark:text-gray-100">{{ $displayName }}</p>
                        <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">{{ $roleLabel }}</p>
                    </div>
                    <x-theme-toggle />
                </div>
            </div>
            <div x-show="collapsed" x-cloak class="flex flex-col items-center gap-2 border-t border-gray-200 px-3 py-3 dark:border-border">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-[#1D9E75] to-emerald-600" title="{{ $displayName }}">
                    <x-lucide-user class="h-5 w-5 text-white" />
                </div>
                <x-theme-toggle />
            </div>

            <div class="border-t border-gray-200 bg-gray-50 px-4 py-4 dark:border-border dark:bg-background/40">
                <button
                    type="button"
                    @click="logoutOpen = true"
                    class="group flex w-full items-center gap-3 rounded-xl px-4 py-3.5 text-sm font-medium text-gray-700 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-gray-300 dark:hover:bg-red-500/10"
                    :class="collapsed ? 'justify-center px-3 py-3.5' : 'px-4 py-3.5'"
                    title="Sign Out"
                >
                    <x-lucide-log-out class="h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-red-600" />
                    <span x-show="!collapsed">Sign Out</span>
                </button>
            </div>
        </div>
    </div>

    <div class="ml-64 flex min-h-screen flex-1 flex-col" :class="collapsed ? 'ml-20' : 'ml-64'">
        <div class="sticky top-0 z-50 flex h-14 items-center justify-end border-b border-gray-200 bg-white px-4 dark:border-border dark:bg-card sm:h-16 sm:px-6">
            <div class="relative">
                <button
                    type="button"
                    @click="notesOpen = !notesOpen"
                    class="relative flex h-10 items-center gap-2 rounded-full border border-gray-200 bg-white px-3 shadow-sm transition-all hover:border-[#1D9E75]/35 hover:bg-emerald-50/70 dark:border-[#3d5c50] dark:bg-[#1c2c26] dark:text-gray-100 dark:hover:border-[#1D9E75]/50 dark:hover:bg-[#243830] sm:gap-2.5 sm:px-4 {{ $unreadCount > 0 ? 'border-red-200 bg-red-50/60 hover:bg-red-50 dark:border-red-400/40 dark:bg-[#3a2222] dark:hover:bg-[#4a2a2a]' : '' }}"
                    title="{{ $unreadCount > 0 ? $unreadCount.' unread notifications' : 'Notifications' }}"
                >
                    <x-lucide-bell class="h-[18px] w-[18px] {{ $unreadCount > 0 ? 'text-[#1D9E75]' : 'text-gray-700' }}" />
                    <span class="hidden text-sm font-medium leading-none text-gray-700 dark:text-gray-100 sm:inline">Notifications</span>
                    @if ($unreadCount > 0)
                        <span class="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-bold leading-none text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </button>
                <div x-show="notesOpen" x-cloak @click.outside="notesOpen = false" class="absolute right-0 top-12 z-50 w-[22rem] rounded-lg border border-gray-200 bg-white shadow-lg dark:border-border dark:bg-card">
                    <div class="flex items-center justify-between border-b px-4 py-3">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">Notifications</h3>
                        @if ($unreadCount > 0)
                            <form method="POST" action="/admin/notifications/read">@csrf<button class="text-xs font-medium text-[#1D9E75]">Mark all read</button></form>
                        @endif
                    </div>
                    <div class="max-h-[450px] overflow-y-auto">
                        @forelse ($notifications as $note)
                            <a href="{{ $note->action_url ?: '/admin/reports' }}" class="block border-b px-4 py-3 last:border-0 hover:bg-gray-50 {{ $note->status === 'unread' ? 'bg-emerald-50/60' : '' }}">
                                <p class="text-sm text-gray-900 dark:text-gray-100 {{ $note->status === 'unread' ? 'font-semibold' : 'font-medium' }}">{{ $note->title }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $note->message }}</p>
                                <p class="mt-1 text-[11px] text-gray-400">{{ optional($note->created_at)->timezone('Asia/Manila')->format('M d, Y • g:i A') }}</p>
                            </a>
                        @empty
                            <div class="px-4 py-10 text-center">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">No notifications</p>
                                <p class="mt-1 text-xs text-gray-500">You're all caught up!</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="border-t px-4 py-2 text-right">
                        <button type="button" @click="notesOpen = false" class="text-sm font-medium text-[#1D9E75]">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <main class="flex-1 bg-gray-50 p-8 dark:bg-background">
            {{ $slot }}
        </main>
    </div>

    <div x-show="logoutOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4" @keydown.escape.window="logoutOpen = false">
        <div class="w-full max-w-lg rounded-lg border border-gray-200 bg-white p-6 shadow-lg dark:border-border dark:bg-card" @click.outside="logoutOpen = false">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-foreground">Confirm Logout</h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-muted-foreground">Are you sure you want to logout? You'll need to log back in to access your dashboard.</p>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="logoutOpen = false" class="inline-flex h-10 items-center rounded-md border border-gray-200 px-4 text-sm font-medium hover:bg-gray-50 dark:border-border dark:hover:bg-white/5">Cancel</button>
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="inline-flex h-10 items-center rounded-md bg-red-500 px-4 text-sm font-medium text-white hover:bg-red-600">Yes, Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>
</x-layouts.app>
