{{-- Port of frontend/src/views/admin/AdminDashboardRedesign.tsx --}}
<x-layouts.admin>
<div class="w-full space-y-6 pb-10" x-data="{
    now: new Date(),
    init() { setInterval(() => { this.now = new Date() }, 1000) },
    time() {
        return new Intl.DateTimeFormat('en-PH', { timeZone: 'Asia/Manila', hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true }).format(this.now)
    },
    date() {
        return new Intl.DateTimeFormat('en-PH', { timeZone: 'Asia/Manila', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }).format(this.now)
    }
}">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-muted-foreground">Admin Overview</p>
            <h1 class="text-xl font-bold text-gray-900 dark:text-foreground">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-muted-foreground">System-wide case management and analytics overview</p>
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

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        @foreach ($metrics as $metric)
            <a href="{{ $metric['href'] }}" class="rounded-lg border border-transparent bg-white p-4 shadow-sm transition-shadow hover:shadow-md dark:border-border dark:bg-card">
                <div class="mb-3 flex items-start justify-between">
                    <div class="rounded-lg bg-emerald-50 p-2 dark:bg-[#1D9E75]/20">
                        <x-dynamic-component :component="'lucide-'.$metric['icon']" class="h-5 w-5 text-[#1D9E75]" />
                    </div>
                    <span class="inline-flex items-center rounded-full border-0 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-[#1D9E75] dark:bg-[#1D9E75]/20 dark:text-emerald-200">
                        <x-lucide-trending-up class="mr-1 h-3 w-3" />
                        {{ $metric['trend'] }}
                    </span>
                </div>
                <p class="mb-1 text-xs uppercase tracking-wide text-gray-600 dark:text-muted-foreground">{{ $metric['title'] }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-foreground">{{ $metric['value'] }}</p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-muted-foreground">vs. last 30 days</p>
            </a>
        @endforeach
    </div>

    @if ($needsAttention > 0)
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-red-100 p-2">
                        <x-lucide-shield-alert class="h-5 w-5 text-red-600" />
                    </div>
                    <div>
                        <p class="font-semibold text-red-900">Reports Needing Follow-Up</p>
                        <p class="text-xs text-red-700">{{ $needsAttention }} cases with no update for 3+ days</p>
                    </div>
                </div>
                <a href="/admin/reports" class="inline-flex h-9 items-center rounded-md bg-red-600 px-3 text-sm font-medium text-white hover:bg-red-700">View All</a>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-card">
            <div class="flex items-start gap-3">
                <div class="rounded-lg bg-emerald-50 p-2 dark:bg-[#1D9E75]/20"><x-lucide-circle-check class="h-5 w-5 text-[#1D9E75]" /></div>
                <div>
                    <p class="mb-1 text-xs uppercase tracking-wide text-gray-600">Case Resolution Rate</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $resolutionRate }}%</p>
                    <p class="mt-0.5 text-xs text-gray-500">Resolved / Total</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-card">
            <div class="flex items-start gap-3">
                <div class="rounded-lg bg-emerald-50 p-2 dark:bg-[#1D9E75]/20"><x-lucide-clock class="h-5 w-5 text-[#1D9E75]" /></div>
                <div>
                    <p class="mb-1 text-xs uppercase tracking-wide text-gray-600">Avg Resolution Time</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $avgResolutionTime }}d</p>
                    <p class="mt-0.5 text-xs text-gray-500">Days to resolve</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-card">
            <div class="flex items-start gap-3">
                <div class="rounded-lg bg-red-100 p-2 dark:bg-red-500/20"><x-lucide-shield-alert class="h-5 w-5 text-red-600 dark:text-red-400" /></div>
                <div>
                    <p class="mb-1 text-xs uppercase tracking-wide text-gray-600">Overdue Cases</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $overdueCases }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">Past 7-day deadline</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-card">
            <div class="flex items-start gap-3">
                <div class="rounded-lg bg-amber-100 p-2 dark:bg-amber-500/20"><x-lucide-file-text class="h-5 w-5 text-amber-600 dark:text-amber-400" /></div>
                <div>
                    <p class="mb-1 text-xs uppercase tracking-wide text-gray-600">Follow-Up Requests</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $followUpRequests }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">Needing attention</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <div class="space-y-6 lg:col-span-4">
            <div class="rounded-lg bg-white shadow-sm dark:bg-card">
                <div class="px-6 pb-4 pt-6"><h2 class="text-base font-semibold">Recent Complaints</h2></div>
                <div class="px-6 pb-6">
                    @if ($activeCases->isEmpty())
                        <div class="py-12 text-center">
                            <x-lucide-file-text class="mx-auto mb-3 h-12 w-12 text-gray-300" />
                            <p class="text-sm text-gray-500">No active cases</p>
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach ($activeCases as $case)
                                <a href="/admin/reports?reportId={{ $case['id'] }}" class="block rounded-lg border border-gray-100 p-3 transition-all hover:border-gray-200 hover:shadow-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <p class="mb-1 truncate text-sm font-medium text-gray-900">{{ $case['title'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $case['type'] }} • {{ $case['timeAgo'] }}</p>
                                        </div>
                                        @if ($case['status'] === 'inProgress')
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">In Progress</span>
                                        @elseif ($case['status'] === 'resolved')
                                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Decision Made</span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Pending</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <a href="/admin/reports" class="mt-4 flex w-full items-center justify-center gap-2 border-t pt-4 text-sm font-medium text-[#1D9E75]">
                            View all complaints
                            <x-lucide-chevron-right class="h-4 w-4" />
                        </a>
                    @endif
                </div>
            </div>

            <div class="rounded-lg bg-white shadow-sm dark:bg-card">
                <div class="px-6 pb-4 pt-6"><h2 class="text-base font-semibold">Monthly Reports</h2></div>
                <div class="px-6 pb-6">
                    @php $visibleMonths = $monthly->filter(fn ($row) => $row['total'] > 0); @endphp
                    @if ($visibleMonths->isEmpty())
                        <div class="py-8 text-center">
                            <x-lucide-bar-chart-3 class="mx-auto mb-3 h-12 w-12 text-gray-300" />
                            <p class="text-sm text-gray-500">No reports yet</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($visibleMonths as $row)
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between">
                                        <span class="w-8 text-xs font-medium text-gray-700">{{ $row['month'] }}</span>
                                        <span class="text-xs text-gray-500">{{ $row['total'] }} total</span>
                                    </div>
                                    <div class="flex h-10 gap-0.5 overflow-hidden rounded bg-gray-100">
                                        @if ($row['harassment'] > 0)
                                            <div class="h-full" style="width: {{ ($row['harassment'] / $row['total']) * 100 }}%; background-color: #0F6E56" title="Sexual Harassment: {{ $row['harassment'] }}"></div>
                                        @endif
                                        @if ($row['bullying'] > 0)
                                            <div class="h-full" style="width: {{ ($row['bullying'] / $row['total']) * 100 }}%; background-color: #1D9E75" title="Bullying: {{ $row['bullying'] }}"></div>
                                        @endif
                                        @if ($row['others'] > 0)
                                            <div class="h-full" style="width: {{ ($row['others'] / $row['total']) * 100 }}%; background-color: #4DB894" title="Others: {{ $row['others'] }}"></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-4 flex flex-wrap items-center gap-3 border-t pt-4 text-xs">
                        <div class="flex items-center gap-1.5"><span class="h-3 w-3 rounded" style="background-color:#0F6E56"></span><span class="text-gray-600">Sexual Harassment</span></div>
                        <div class="flex items-center gap-1.5"><span class="h-3 w-3 rounded" style="background-color:#1D9E75"></span><span class="text-gray-600">Bullying</span></div>
                        <div class="flex items-center gap-1.5"><span class="h-3 w-3 rounded" style="background-color:#4DB894"></span><span class="text-gray-600">Others</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="rounded-lg bg-white shadow-sm dark:bg-card">
                <div class="px-6 pb-4 pt-6">
                    <h2 class="text-base font-semibold">Case Trends</h2>
                    <p class="mt-1 text-xs text-gray-500">Monthly case submissions over the last 6 months</p>
                </div>
                <div class="px-6 pb-6">
                    <div class="relative flex h-64 items-end justify-between gap-2 pb-8">
                        <div class="absolute bottom-8 left-0 top-0 flex flex-col justify-between text-xs text-gray-400">
                            @foreach ([10, 8, 6, 4, 2, 0] as $tick)<span>{{ $tick }}</span>@endforeach
                        </div>
                        <div class="relative ml-8 h-full flex-1">
                            <div class="absolute inset-0 flex flex-col justify-between">
                                @foreach (range(0, 5) as $line)<div class="border-t border-gray-100"></div>@endforeach
                            </div>
                            <svg class="absolute inset-0 h-full w-full" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="lineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="#1D9E75" stop-opacity="0.2" />
                                        <stop offset="100%" stop-color="#1D9E75" stop-opacity="0" />
                                    </linearGradient>
                                </defs>
                                <path d="{{ $area }}" fill="url(#lineGradient)" />
                                <polyline points="{{ $polyline }}" fill="none" stroke="#1D9E75" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                @foreach ($chartPoints as $point)
                                    <circle cx="{{ $point['x'] }}%" cy="{{ $point['y'] }}" r="5" fill="white" stroke="#1D9E75" stroke-width="2" />
                                    <circle cx="{{ $point['x'] }}%" cy="{{ $point['y'] }}" r="3" fill="#1D9E75" />
                                @endforeach
                            </svg>
                            <div class="absolute -bottom-6 left-0 right-0 flex justify-between text-xs text-gray-500">
                                @foreach ($monthly as $row)<span>{{ $row['month'] }}</span>@endforeach
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-center gap-6 border-t pt-4">
                        <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full" style="background-color:#1D9E75"></span><span class="text-xs text-gray-600">Total Cases</span></div>
                        <span class="text-xs text-gray-500">Peak: {{ $peak }} cases</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="rounded-lg bg-white shadow-sm dark:bg-card">
                <div class="px-6 pb-4 pt-6">
                    <h2 class="text-base font-semibold">Upcoming Deadlines</h2>
                    <p class="mt-1 text-xs text-gray-500">Based on each case’s stored response due date (7 days from filing)</p>
                </div>
                <div class="px-6 pb-6">
                    @if ($deadlines->isEmpty())
                        <div class="py-8 text-center">
                            <x-lucide-clock class="mx-auto mb-3 h-12 w-12 text-gray-300" />
                            <p class="text-sm text-gray-500">No urgent deadlines</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($deadlines as $deadline)
                                <a href="/admin/reports?reportId={{ $deadline['id'] }}" class="block rounded-lg border p-3 transition-all hover:shadow-sm {{ $deadline['isOverdue'] ? 'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10' : ($deadline['isUrgent'] ? 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10' : 'border-gray-200 bg-white dark:border-border dark:bg-card') }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="mb-1 truncate text-xs font-medium text-gray-900">{{ $deadline['caseNumber'] }}</p>
                                            <p class="truncate text-xs text-gray-500">{{ $deadline['title'] }}</p>
                                            <p class="mt-1 text-[10px] text-gray-400">Due {{ $deadline['dueLabel'] }}</p>
                                        </div>
                                        <span class="shrink-0 whitespace-nowrap rounded-full px-2 py-0.5 text-[10px] font-medium text-white {{ $deadline['isOverdue'] ? 'bg-red-500' : ($deadline['isUrgent'] ? 'bg-amber-500' : 'bg-green-500') }}">
                                            @if ($deadline['isOverdue'])
                                                Overdue by {{ abs($deadline['days']) }}d
                                            @elseif ($deadline['days'] === 0)
                                                Due Today
                                            @elseif ($deadline['days'] === 1)
                                                Due Tomorrow
                                            @else
                                                Due in {{ $deadline['days'] }} days
                                            @endif
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="rounded-lg bg-white shadow-sm dark:bg-card">
            <div class="px-6 pb-4 pt-6">
                <h2 class="text-base font-semibold">Reports by Category</h2>
                <p class="mt-1 text-xs text-gray-500">Distribution of complaint types</p>
            </div>
            <div class="px-6 pb-6">
                @if ($categories->isEmpty())
                    <div class="py-8 text-center"><x-lucide-bar-chart-3 class="mx-auto mb-3 h-12 w-12 text-gray-300" /><p class="text-sm text-gray-500">No data yet</p></div>
                @else
                    <div class="space-y-3">
                        @foreach ($categories as $cat)
                            <div>
                                <div class="mb-1 flex items-center justify-between"><span class="text-xs font-medium text-gray-700">{{ $cat['name'] }}</span><span class="text-xs text-gray-500">{{ $cat['percentage'] }}%</span></div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-100"><div class="h-full rounded-full" style="width: {{ $cat['percentage'] }}%; background-color: {{ $cat['color'] }}"></div></div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="rounded-lg bg-white shadow-sm dark:bg-card">
            <div class="px-6 pb-4 pt-6">
                <h2 class="text-base font-semibold">Reports by Status</h2>
                <p class="mt-1 text-xs text-gray-500">Current case status distribution</p>
            </div>
            <div class="px-6 pb-6">
                @if ($statuses->isEmpty())
                    <div class="py-8 text-center"><x-lucide-bar-chart-3 class="mx-auto mb-3 h-12 w-12 text-gray-300" /><p class="text-sm text-gray-500">No data yet</p></div>
                @else
                    <div class="space-y-3">
                        @foreach ($statuses as $stat)
                            <div>
                                <div class="mb-1 flex items-center justify-between"><span class="text-xs font-medium capitalize text-gray-700">{{ str_replace('_', ' ', $stat['name']) }}</span><span class="text-xs text-gray-500">{{ $stat['percentage'] }}%</span></div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-100"><div class="h-full rounded-full" style="width: {{ $stat['percentage'] }}%; background-color: {{ $stat['color'] }}"></div></div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-lg bg-white shadow-sm dark:bg-card">
        <div class="px-6 pb-4 pt-6"><h2 class="text-base font-semibold">Quick Actions</h2></div>
        <div class="px-6 pb-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                @foreach ([
                    ['href' => '/admin/reports', 'icon' => 'user-plus', 'label' => 'Assign Case'],
                    ['href' => '/admin/compliance-reports', 'icon' => 'file-check', 'label' => 'Generate Report'],
                    ['href' => '/admin/reports', 'icon' => 'shield-alert', 'label' => 'View Escalations'],
                    ['href' => '/admin/analytics', 'icon' => 'bar-chart-3', 'label' => 'Export Analytics'],
                ] as $action)
                    <a href="{{ $action['href'] }}" class="group rounded-lg border border-gray-200 p-6 text-center transition-all hover:border-gray-300 hover:bg-gray-50 hover:shadow-sm dark:border-border dark:hover:border-[#1D9E75]/40 dark:hover:bg-white/5">
                        <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 transition-transform group-hover:scale-110 dark:bg-[#1D9E75]/20">
                            <x-dynamic-component :component="'lucide-'.$action['icon']" class="h-5 w-5 text-[#1D9E75]" />
                        </div>
                        <p class="text-sm font-semibold text-gray-900">{{ $action['label'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
</x-layouts.admin>
