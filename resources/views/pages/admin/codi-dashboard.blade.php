{{-- Port of frontend/src/views/admin/CodiDashboard.tsx --}}
<x-layouts.admin>
@php
    $rows = $rows ?? collect();
    $user = auth()->user();
    $first = trim((string) ($user?->display_name ?? ''));
    $firstName = $first !== '' ? explode(' ', $first)[0] : 'CODI Member';
    $activeQueue = $activeQueue ?? 0;
    $myAssigned = $myAssigned ?? 0;
    $investigating = $investigating ?? 0;
    $unassigned = $unassigned ?? 0;
    $needsAttention = $needsAttention ?? 0;
    $myRepId = $user?->representative?->id;
    $statusLabel = function ($status) {
        return match (strtolower((string) $status)) {
            'pending', 'submitted' => 'Submitted',
            'inprogress' => 'Investigating',
            'resolved' => 'Resolved',
            'dismissed' => 'Dismissed',
            'closed' => 'Closed',
            default => $status ?: 'Unknown',
        };
    };
@endphp
<div
    class="w-full space-y-8 pb-10"
    x-data="{
        now: new Date(),
        init() { setInterval(() => { this.now = new Date() }, 1000) },
        time() {
            return new Intl.DateTimeFormat('en-PH', { timeZone: 'Asia/Manila', hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true }).format(this.now)
        },
        date() {
            return new Intl.DateTimeFormat('en-PH', { timeZone: 'Asia/Manila', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }).format(this.now)
        }
    }"
>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500">CODI Workspace</p>
            <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ $firstName }}</h1>
            <p class="mt-1 text-sm text-gray-500">Shared case queue — review priorities, take unassigned cases, and manage your investigations.</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white px-5 py-3 shadow-sm dark:border-border dark:bg-card sm:min-w-[240px]">
            <div class="mb-1 flex items-center justify-end gap-2 text-gray-400">
                <x-lucide-clock class="h-4 w-4" />
                <span class="text-[11px] font-medium uppercase tracking-wide">Philippine Time</span>
            </div>
            <p class="text-right text-2xl font-semibold tabular-nums tracking-tight text-gray-900" x-text="time()"></p>
            <p class="mt-1 text-right text-sm text-gray-500" x-text="date()"></p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="/admin/reports" class="text-left">
            <div class="h-full rounded-lg border border-gray-200 bg-card text-card-foreground shadow-sm transition-all hover:border-[#1D9E75]/40 hover:shadow-md dark:border-border">
                <div class="p-5">
                    <div class="mb-3 flex items-start justify-between">
                        <div class="rounded-xl bg-emerald-50 p-2.5 text-emerald-700 dark:bg-[#1D9E75]/20 dark:text-emerald-300"><x-lucide-inbox class="h-5 w-5" /></div>
                        <x-lucide-arrow-right class="h-4 w-4 text-gray-300" />
                    </div>
                    <p class="mb-1 text-xs uppercase tracking-wide text-gray-500">Active Queue</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $activeQueue }}</p>
                    <p class="mt-1 text-xs text-gray-500">All open cases in the system</p>
                </div>
            </div>
        </a>
        <a href="/admin/reports?assigned=me" class="text-left">
            <div class="h-full rounded-lg border border-gray-200 bg-card shadow-sm transition-all hover:border-[#1D9E75]/40 hover:shadow-md dark:border-border">
                <div class="p-5">
                    <div class="mb-3 flex items-start justify-between">
                        <div class="rounded-xl bg-blue-50 p-2.5 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300"><x-lucide-briefcase class="h-5 w-5" /></div>
                        <x-lucide-arrow-right class="h-4 w-4 text-gray-300" />
                    </div>
                    <p class="mb-1 text-xs uppercase tracking-wide text-gray-500">My Assigned</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $myAssigned }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $investigating }} currently investigating</p>
                </div>
            </div>
        </a>
        <a href="/admin/reports?status=unassigned" class="text-left">
            <div class="h-full rounded-lg border border-gray-200 bg-card shadow-sm transition-all hover:border-[#1D9E75]/40 hover:shadow-md dark:border-border">
                <div class="p-5">
                    <div class="mb-3 flex items-start justify-between">
                        <div class="rounded-xl bg-amber-50 p-2.5 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300"><x-lucide-user-plus class="h-5 w-5" /></div>
                        <x-lucide-arrow-right class="h-4 w-4 text-gray-300" />
                    </div>
                    <p class="mb-1 text-xs uppercase tracking-wide text-gray-500">Unassigned</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $unassigned }}</p>
                    <p class="mt-1 text-xs text-gray-500">Available to take from queue</p>
                </div>
            </div>
        </a>
        <a href="/admin/reports?status=needs-attention" class="text-left">
            <div class="h-full rounded-lg border border-gray-200 bg-card shadow-sm transition-all hover:border-[#1D9E75]/40 hover:shadow-md dark:border-border">
                <div class="p-5">
                    <div class="mb-3 flex items-start justify-between">
                        <div class="rounded-xl bg-red-50 p-2.5 text-red-700 dark:bg-red-500/20 dark:text-red-300"><x-lucide-alert-triangle class="h-5 w-5" /></div>
                        <x-lucide-arrow-right class="h-4 w-4 text-gray-300" />
                    </div>
                    <p class="mb-1 text-xs uppercase tracking-wide text-gray-500">Needs Attention</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $needsAttention }}</p>
                    <p class="mt-1 text-xs text-gray-500">Stale, escalated, or follow-up</p>
                </div>
            </div>
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-card text-card-foreground shadow-sm dark:border-border lg:col-span-2">
            <div class="flex flex-row items-center justify-between space-y-0 p-6 pb-3">
                <div>
                    <h3 class="text-base font-semibold leading-none tracking-tight">Recent Activity</h3>
                    <p class="mt-1 text-sm text-gray-500">Latest updates across the case queue</p>
                </div>
                <a href="/admin/reports" class="inline-flex h-9 items-center justify-center rounded-md px-3 text-sm font-medium hover:bg-accent">
                    View all
                    <x-lucide-arrow-right class="ml-1 h-4 w-4" />
                </a>
            </div>
            <div class="space-y-3 p-6 pt-0">
                @forelse ($rows as $row)
                    @php
                        $title = (string) data_get($row, 'title', 'Untitled Case');
                        $status = (string) data_get($row, 'status', '');
                        $assignedTo = data_get($row, 'assigned_to', data_get($row, 'assignedTo'));
                        $isMine = $myRepId && (string) $assignedTo === (string) $myRepId;
                        $caseNo = (string) data_get($row, 'case_number', data_get($row, 'case_id', data_get($row, 'caseId', '')));
                        $updated = data_get($row, 'last_updated', data_get($row, 'lastUpdated', data_get($row, 'updated_at', data_get($row, 'reported_at'))));
                        try {
                            $rel = $updated ? \Illuminate\Support\Carbon::parse($updated)->diffForHumans() : '';
                        } catch (\Throwable $e) {
                            $rel = '';
                        }
                        $id = data_get($row, 'id');
                    @endphp
                    <a href="/admin/reports?reportId={{ $id }}" class="block w-full rounded-xl border border-gray-200 p-4 text-left transition-all hover:border-[#1D9E75]/40 hover:bg-emerald-50/30 dark:border-border dark:hover:bg-white/5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                    <span class="truncate font-medium text-gray-900">{{ $title !== '' ? $title : 'Untitled Case' }}</span>
                                    @if ($isMine)
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-semibold text-blue-700">Assigned to you</span>
                                    @endif
                                    @if (! $assignedTo)
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-semibold text-amber-700">Unassigned</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500">
                                    {{ $caseNo }}@if ($rel !== '') · Updated {{ $rel }}@endif
                                </p>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold">{{ $statusLabel($status) }}</span>
                        </div>
                    </a>
                @empty
                    <div class="py-10 text-center text-gray-500">
                        <x-lucide-file-text class="mx-auto mb-3 h-10 w-10 text-gray-300" />
                        <p class="text-sm">No active cases in the queue.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-card text-card-foreground shadow-sm dark:border-border">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-base font-semibold leading-none tracking-tight">Quick Links</h3>
            </div>
            <div class="space-y-2 p-6 pt-0">
                <a href="/admin/reports" class="flex w-full items-center gap-3 rounded-xl border border-gray-200 p-3 text-left transition-all hover:border-[#1D9E75]/40 hover:bg-gray-50 dark:border-border dark:hover:bg-white/5">
                    <div class="rounded-lg bg-gray-100 p-2"><x-lucide-file-text class="h-4 w-4 text-gray-600" /></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900">Case Queue</p>
                        <p class="text-xs text-gray-500">Browse all open cases</p>
                    </div>
                    <x-lucide-arrow-right class="h-4 w-4 shrink-0 text-gray-300" />
                </a>
                <a href="/admin/reports?assigned=me" class="flex w-full items-center gap-3 rounded-xl border border-gray-200 p-3 text-left transition-all hover:border-[#1D9E75]/40 hover:bg-gray-50 dark:border-border">
                    <div class="rounded-lg bg-gray-100 p-2"><x-lucide-briefcase class="h-4 w-4 text-gray-600" /></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900">My Assigned Cases</p>
                        <p class="text-xs text-gray-500">Cases assigned to you</p>
                    </div>
                    <x-lucide-arrow-right class="h-4 w-4 shrink-0 text-gray-300" />
                </a>
                <a href="/admin/messages" class="flex w-full items-center gap-3 rounded-xl border border-gray-200 p-3 text-left transition-all hover:border-[#1D9E75]/40 hover:bg-gray-50 dark:border-border">
                    <div class="rounded-lg bg-gray-100 p-2"><x-lucide-message-square class="h-4 w-4 text-gray-600" /></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900">Messages</p>
                        <p class="text-xs text-gray-500">Chat with complainants</p>
                    </div>
                    <x-lucide-arrow-right class="h-4 w-4 shrink-0 text-gray-300" />
                </a>
                <a href="/admin/closed-cases" class="flex w-full items-center gap-3 rounded-xl border border-gray-200 p-3 text-left transition-all hover:border-[#1D9E75]/40 hover:bg-gray-50 dark:border-border">
                    <div class="rounded-lg bg-gray-100 p-2"><x-lucide-check-circle-2 class="h-4 w-4 text-gray-600" /></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900">Closed Cases</p>
                        <p class="text-xs text-gray-500">Your archived cases</p>
                    </div>
                    <x-lucide-arrow-right class="h-4 w-4 shrink-0 text-gray-300" />
                </a>
                <a href="/admin/analytics" class="flex w-full items-center gap-3 rounded-xl border border-gray-200 p-3 text-left transition-all hover:border-[#1D9E75]/40 hover:bg-gray-50 dark:border-border">
                    <div class="rounded-lg bg-gray-100 p-2"><x-lucide-clock class="h-4 w-4 text-gray-600" /></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900">My Performance</p>
                        <p class="text-xs text-gray-500">Your case stats</p>
                    </div>
                    <x-lucide-arrow-right class="h-4 w-4 shrink-0 text-gray-300" />
                </a>
            </div>
        </div>
    </div>
</div>
</x-layouts.admin>
