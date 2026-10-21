{{-- Port of frontend/src/views/admin/Analytics.tsx. Recharts canvases are replaced by the original empty states, or a simple bar list when a series collection is passed. --}}
<x-layouts.admin>
@php
    $rows = $rows ?? collect();
    $user = auth()->user();
    $repRole = $user?->representative?->role;
    $isAdminNav = $user && ($user->isPlatformAdmin() || $repRole === 'admin');
    $isCodi = $isCodi ?? ($user && ! $isAdminNav && in_array($repRole, ['handler', 'codi'], true));
    $codiName = $user?->display_name ?: 'you';
    $reportsOverTime = collect($reportsOverTime ?? []);
    $userGrowth = collect($userGrowth ?? []);
    $reportsByCategory = collect($reportsByCategory ?? []);
    $reportsByStatus = collect($reportsByStatus ?? []);
    $casesByRepresentative = collect($casesByRepresentative ?? []);
    $totalReports = $totalReports ?? $rows->count();
    $resolvedReports = $resolvedReports ?? 0;
    $dailyActiveUsers = $dailyActiveUsers ?? 0;
    $averageResponseTime = $averageResponseTime ?? 0;
    $resolutionPct = $totalReports > 0 ? (int) round(($resolvedReports / $totalReports) * 100) : 0;
    $inProgress = 0;
    foreach ($reportsByStatus as $item) {
        if (data_get($item, 'status') === 'inProgress') {
            $inProgress = (int) data_get($item, 'count', 0);
        }
    }
    $userGrowthEmpty = $userGrowth->isEmpty() || $userGrowth->every(fn ($day) => (int) data_get($day, 'users', 0) === 0);
@endphp
<div class="w-full space-y-6 pb-10">
    <div class="flex items-center justify-between">
        <div>
            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500">Insights</p>
            <h1 class="text-xl font-bold text-gray-900">{{ $isCodi ? 'My Performance' : 'Analytics' }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                @if ($isCodi)
                    Live metrics for cases assigned to {{ $codiName }}.
                @else
                    Live metrics from reports and users. Charts cover the last seven days.
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-400">
            <x-lucide-sparkles class="h-3.5 w-3.5" />
            <span>Updated {{ now()->timezone('Asia/Manila')->format('M j, Y · g:i A') }}</span>
        </div>
    </div>

    <div class="rounded-lg border bg-white p-4 shadow-sm">
        <div class="flex flex-wrap gap-x-8 gap-y-3 text-sm text-gray-600">
            <div>
                <span class="font-medium text-gray-900">Period</span>
                <span class="mx-2 text-gray-300">·</span>
                Last 7 days
            </div>
            <div>
                <span class="font-medium text-gray-900">Categories</span>
                <span class="mx-2 text-gray-300">·</span>
                {{ $reportsByCategory->count() }} types
            </div>
            <div>
                <span class="font-medium text-gray-900">Resolution Rate</span>
                <span class="mx-2 text-gray-300">·</span>
                {{ $resolutionPct }}%
            </div>
            <div class="ml-auto text-xs text-gray-400">Data reflects the live reports collection</div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @if ($isCodi)
            @php
                $kpis = [
                    ['label' => 'Assigned to me', 'value' => $totalReports, 'hint' => 'All cases currently assigned to you', 'icon' => 'file-text'],
                    ['label' => 'In progress', 'value' => $inProgress, 'hint' => 'Cases under investigation', 'icon' => 'timer'],
                    ['label' => 'Resolved / Closed', 'value' => $resolvedReports, 'hint' => $totalReports > 0 ? $resolutionPct.'% completion rate' : 'No cases yet', 'icon' => 'check-circle-2'],
                    ['label' => 'Avg. resolve time', 'value' => $averageResponseTime > 0 ? $averageResponseTime.'h' : '—', 'hint' => 'Mean hours from assignment to resolution', 'icon' => 'bar-chart-3'],
                ];
            @endphp
        @else
            @php
                $kpis = [
                    ['label' => 'Active today', 'value' => $dailyActiveUsers, 'hint' => 'Users who submitted a report today', 'icon' => 'users'],
                    ['label' => 'Total reports', 'value' => $totalReports, 'hint' => 'All-time submissions', 'icon' => 'file-text'],
                    ['label' => 'Resolved', 'value' => $resolvedReports, 'hint' => $totalReports > 0 ? $resolutionPct.'% of all reports' : 'No reports yet', 'icon' => 'check-circle-2'],
                    ['label' => 'Avg. resolve time', 'value' => $averageResponseTime > 0 ? $averageResponseTime.'h' : '—', 'hint' => 'Mean hours from report to resolution', 'icon' => 'timer'],
                ];
            @endphp
        @endif
        @foreach ($kpis as $kpi)
            <div class="rounded-lg border-0 bg-white shadow-sm transition-shadow hover:shadow-md">
                <div class="p-4">
                    <div class="mb-3 flex items-start justify-between">
                        <div class="rounded-lg bg-emerald-50 p-2 dark:bg-[#1D9E75]/20">
                            <x-dynamic-component :component="'lucide-'.$kpi['icon']" class="h-5 w-5 text-[#1D9E75]" />
                        </div>
                    </div>
                    <p class="mb-1 text-xs uppercase tracking-wide text-gray-600">{{ $kpi['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $kpi['value'] }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $kpi['hint'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-lg border border-gray-200/80 bg-white shadow-sm dark:border-border dark:bg-card">
            <div class="space-y-1 border-b border-gray-100 bg-gradient-to-r from-white to-violet-50/40 pb-4 pt-5 dark:border-border dark:from-card dark:to-violet-500/10 px-6">
                <h3 class="text-base font-semibold text-gray-900">Reports over time</h3>
                <p class="text-sm text-muted-foreground">Last 7 days · daily submissions</p>
            </div>
            <div class="h-[300px] px-2 pb-4 pt-4 sm:h-[320px] sm:px-4">
                @if ($totalReports === 0 || $reportsOverTime->isEmpty())
                    <div class="flex h-full flex-col items-center justify-center text-gray-400">
                        <x-lucide-trending-up class="mb-3 h-12 w-12 opacity-20" />
                        <p class="text-sm font-medium">No reports submitted in this period</p>
                        <p class="mt-1 text-xs">Chart will appear once reports are submitted</p>
                    </div>
                @else
                    {!! \App\Support\AnalyticsCharts::line($reportsOverTime, 'count', '#1D9E75', 'Reports') !!}
                @endif
            </div>
        </div>

        @unless ($isCodi)
            <div class="overflow-hidden rounded-lg border border-gray-200/80 bg-white shadow-sm dark:border-border dark:bg-card">
                <div class="space-y-1 border-b border-gray-100 bg-gradient-to-r from-white to-emerald-50/50 px-6 pb-4 pt-5 dark:border-border dark:from-card dark:to-emerald-500/10">
                    <h3 class="text-base font-semibold text-gray-900">User growth</h3>
                    <p class="text-sm text-muted-foreground">Last 7 days · cumulative registered users</p>
                </div>
                <div class="h-[300px] px-2 pb-4 pt-4 sm:h-[320px] sm:px-4">
                    @if ($userGrowthEmpty)
                        <div class="flex h-full flex-col items-center justify-center text-gray-400">
                            <x-lucide-users class="mb-3 h-12 w-12 opacity-20" />
                            <p class="text-sm font-medium">No user data available</p>
                            <p class="mt-1 text-xs">Chart will appear once users register</p>
                        </div>
                    @else
                        {!! \App\Support\AnalyticsCharts::line($userGrowth, 'users', '#4DB894', 'Users') !!}
                    @endif
                </div>
            </div>
        @endunless
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-lg border border-gray-200/80 bg-white shadow-sm dark:border-border dark:bg-card">
            <div class="space-y-1 border-b border-gray-100 px-6 pb-4 pt-5">
                <h3 class="text-base font-semibold text-gray-900">Reports by category</h3>
                <p class="text-sm text-muted-foreground">Volume by complaint type</p>
            </div>
            <div class="h-[300px] px-2 pb-4 pt-2 sm:h-[320px] sm:px-4">
                @if ($reportsByCategory->isEmpty())
                    <div class="flex h-full flex-col items-center justify-center text-gray-400">
                        <x-lucide-file-bar-chart class="mb-3 h-12 w-12 opacity-20" />
                        <p class="text-sm font-medium">No data yet</p>
                        <p class="mt-1 text-xs">Charts will appear once reports are submitted</p>
                    </div>
                @else
                    {!! \App\Support\AnalyticsCharts::columns($reportsByCategory, 'label', 'count', 'category', 'Reports') !!}
                @endif
            </div>
        </div>
        <div class="overflow-hidden rounded-lg border border-gray-200/80 bg-white shadow-sm dark:border-border dark:bg-card">
            <div class="space-y-1 border-b border-gray-100 px-6 pb-4 pt-5">
                <h3 class="text-base font-semibold text-gray-900">Reports by status</h3>
                <p class="text-sm text-muted-foreground">Pipeline distribution</p>
            </div>
            <div class="h-[300px] px-2 pb-4 pt-2 sm:h-[320px] sm:px-4">
                @if ($reportsByStatus->isEmpty())
                    <div class="flex h-full flex-col items-center justify-center text-gray-400">
                        <x-lucide-bar-chart-3 class="mb-3 h-12 w-12 opacity-20" />
                        <p class="text-sm font-medium">No data yet</p>
                        <p class="mt-1 text-xs">Charts will appear once reports are submitted</p>
                    </div>
                @else
                    {!! \App\Support\AnalyticsCharts::columns($reportsByStatus, 'label', 'count', 'status', 'Reports') !!}
                @endif
            </div>
        </div>
    </div>

    @unless ($isCodi)
        <div class="overflow-hidden rounded-lg border border-gray-200/80 bg-white shadow-sm dark:border-border dark:bg-card">
            <div class="space-y-1 border-b border-gray-100 px-6 pb-4 pt-5">
                <h3 class="text-base font-semibold text-gray-900">Cases by Representative</h3>
                <p class="text-sm text-muted-foreground">Case load per CODI member</p>
            </div>
            <div class="h-[300px] px-2 pb-4 pt-2 sm:h-[320px] sm:px-4">
                @if ($casesByRepresentative->isEmpty())
                    <div class="flex h-full flex-col items-center justify-center text-gray-400">
                        <x-lucide-users class="mb-3 h-12 w-12 opacity-20" />
                        <p class="text-sm font-medium">No assigned cases yet</p>
                        <p class="mt-1 text-xs">Chart will appear once cases are assigned</p>
                    </div>
                @else
                    {!! \App\Support\AnalyticsCharts::columns($casesByRepresentative, 'name', 'cases', 'sky', 'Cases') !!}
                @endif
            </div>
        </div>
    @endunless
</div>
</x-layouts.admin>
