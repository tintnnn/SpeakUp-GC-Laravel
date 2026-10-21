{{-- Port of frontend/src/views/admin/UsersManagement.tsx + DeleteUserModal.tsx --}}
<x-layouts.admin>
@php
    $rows = $rows ?? collect();
    $totalUsers = $totalUsers ?? $rows->filter(function ($row) {
        return ! data_get($row, 'is_admin', data_get($row, 'isAdmin', false))
            && ! data_get($row, 'representative_role', data_get($row, 'representativeRole'));
    })->count();
    $activeToday = $activeToday ?? 0;
    $newThisMonth = $newThisMonth ?? 0;
@endphp
<div
    class="w-full space-y-6 pb-10"
    x-data="{
        q: '',
        status: 'all',
        shown: {{ (int) $rows->count() }},
        menu: null,
        viewOpen: false,
        assignOpen: false,
        deleteOpen: false,
        selected: null,
        role: '',
        confirmText: '',
        visible(alias, name, email, position, rowStatus) {
            const term = this.q.toLowerCase();
            const blob = [alias, name, email, position].join(' ').toLowerCase();
            const textOk = !term || blob.includes(term);
            const statusOk = this.status === 'all'
                || (this.status === 'active' && rowStatus === 'active')
                || (this.status === 'suspended' && rowStatus === 'suspended');
            return textOk && statusOk;
        },
        recount() {
            const rows = [...this.$root.querySelectorAll('[data-user-row]')];
            this.shown = rows.filter((row) => this.visible(row.dataset.alias || '', row.dataset.name || '', row.dataset.email || '', row.dataset.position || '', row.dataset.status || '')).length;
        },
        openView(row) { this.selected = row; this.viewOpen = true; this.menu = null; },
        openAssign(row) { this.selected = row; this.role = ''; this.assignOpen = true; this.menu = null; },
        openDelete(row) { this.selected = row; this.confirmText = ''; this.deleteOpen = true; this.menu = null; }
    }"
    @input="recount()"
    @change="recount()"
>
    <div>
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">User Accounts</p>
        <h1 class="text-xl font-bold text-gray-900">Users Management</h1>
        <p class="text-sm text-gray-500 mt-1">Manage user accounts and permissions</p>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-lg border-0 bg-white text-card-foreground shadow-sm">
            <div class="p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="p-2 rounded-lg bg-emerald-50 dark:bg-[#1D9E75]/20">
                        <x-lucide-users-2 class="h-5 w-5 text-[#1D9E75]" />
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $totalUsers }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Users</p>
            </div>
        </div>
        <div class="rounded-lg border-0 bg-white text-card-foreground shadow-sm">
            <div class="p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="p-2 rounded-lg bg-emerald-50 dark:bg-[#1D9E75]/20">
                        <x-lucide-user-check class="h-5 w-5 text-[#1D9E75]" />
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $activeToday }}</p>
                <p class="text-xs text-gray-500 mt-1">Active Today</p>
            </div>
        </div>
        <div class="rounded-lg border-0 bg-white text-card-foreground shadow-sm">
            <div class="p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="p-2 rounded-lg bg-emerald-50 dark:bg-[#1D9E75]/20">
                        <x-lucide-user-plus class="h-5 w-5 text-[#1D9E75]" />
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $newThisMonth }}</p>
                <p class="text-xs text-gray-500 mt-1">New This Month</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4">
        <div class="relative flex-1 max-w-md">
            <x-lucide-search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input type="search" x-model="q" placeholder="Search by name, email, or position..." class="flex h-10 w-full rounded-md border border-input bg-background py-2 pl-10 pr-3 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 md:text-sm">
        </div>
        <select x-model="status" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm sm:w-48 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
            <option value="all">All Users</option>
            <option value="active">Active Only</option>
            <option value="suspended">Suspended Only</option>
        </select>
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <span>
                Showing <span class="font-semibold text-gray-900" x-text="shown"></span>
                <span x-text="shown === 1 ? 'user' : 'users'"></span>
            </span>
            <button type="button" class="p-1 hover:bg-gray-100 rounded transition-colors" title="Refresh" onclick="window.location.reload()">
                <x-lucide-refresh-cw class="h-4 w-4 text-gray-600" />
            </button>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm overflow-hidden">
        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead class="[&_tr]:border-b">
                    <tr class="border-b transition-colors hover:bg-muted/50">
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Alias</th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Email</th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Reports</th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Created At</th>
                        <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Actions</th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    @forelse ($rows as $row)
                        @php
                            $alias = data_get($row, 'alias');
                            $displayName = (string) data_get($row, 'display_name', data_get($row, 'displayName', ''));
                            $email = (string) data_get($row, 'email', '');
                            $hasAlias = $alias && $alias !== 'N/A';
                            $initialSource = $hasAlias ? (string) $alias : ($displayName !== '' ? $displayName : ($email !== '' ? $email : '?'));
                            $initial = strtoupper(substr($initialSource, 0, 1));
                            $position = (string) data_get($row, 'position', '');
                            $reportsCount = (int) data_get($row, 'reports_count', data_get($row, 'reportsCount', 0));
                            $suspended = (bool) data_get($row, 'is_suspended', data_get($row, 'isSuspended', false));
                            $verified = data_get($row, 'email_verified_at', data_get($row, 'emailVerified', null));
                            if ($suspended) {
                                $statusKey = 'suspended';
                            } elseif ($verified === false || $verified === 0 || $verified === '0') {
                                $statusKey = 'pending';
                            } else {
                                $statusKey = 'active';
                            }
                            $parts = explode('@', $email, 2);
                            $maskedEmail = count($parts) === 2
                                ? substr($parts[0], 0, min(4, strlen($parts[0]))).'****@'.$parts[1]
                                : $email;
                            $createdRaw = data_get($row, 'created_at', data_get($row, 'createdAt'));
                            try {
                                $createdLabel = $createdRaw ? \Illuminate\Support\Carbon::parse($createdRaw)->timezone('Asia/Manila')->format('M d, Y') : 'N/A';
                            } catch (\Throwable $e) {
                                $createdLabel = 'N/A';
                            }
                            $history = data_get($row, 'reports', []);
                            $historyPayload = [];
                            if (is_iterable($history)) {
                                foreach ($history as $report) {
                                    $reportDate = data_get($report, 'created_at', data_get($report, 'createdAt', data_get($report, 'reported_at', data_get($report, 'reportedAt'))));
                                    try {
                                        $reportDateLabel = $reportDate ? \Illuminate\Support\Carbon::parse($reportDate)->timezone('Asia/Manila')->format('M d, Y') : '';
                                    } catch (\Throwable $e) {
                                        $reportDateLabel = '';
                                    }
                                    $historyPayload[] = [
                                        'id' => (string) data_get($report, 'id', count($historyPayload)),
                                        'title' => data_get($report, 'title', 'Untitled Report'),
                                        'category' => (string) data_get($report, 'category', ''),
                                        'status' => (string) data_get($report, 'status', ''),
                                        'date' => $reportDateLabel,
                                    ];
                                }
                            }
                            $payload = [
                                'alias' => $hasAlias ? (string) $alias : ($displayName !== '' ? $displayName : 'N/A'),
                                'confirmAlias' => $hasAlias ? (string) $alias : ($displayName !== '' ? $displayName : (string) data_get($row, 'id', data_get($row, 'uid', ''))),
                                'email' => $maskedEmail,
                                'position' => $position !== '' ? $position : 'N/A',
                                'status' => $statusKey,
                                'created' => $createdLabel,
                                'reportsCount' => $reportsCount,
                                'reports' => $historyPayload,
                            ];
                        @endphp
                        <tr
                            data-user-row
                            data-alias="{{ $hasAlias ? $alias : '' }}"
                            data-name="{{ $displayName }}"
                            data-email="{{ $email }}"
                            data-position="{{ $position }}"
                            data-status="{{ $statusKey }}"
                            x-show="visible($el.dataset.alias, $el.dataset.name, $el.dataset.email, $el.dataset.position, $el.dataset.status)"
                            class="border-b transition-colors hover:bg-muted/50"
                        >
                            <td class="p-4 align-middle font-medium">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 text-xs font-semibold">{{ $initial }}</div>
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            @if (! $hasAlias)
                                                <span class="text-gray-400 italic">Anonymous User</span>
                                            @else
                                                {{ $alias }}
                                            @endif
                                        </div>
                                        @if ($position !== '')
                                            <div class="text-xs text-gray-500">{{ $position }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 align-middle text-sm text-gray-600">{{ $maskedEmail }}</td>
                            <td class="p-4 align-middle">
                                @if ($statusKey === 'suspended')
                                    <div class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-bold text-red-700">
                                        <x-lucide-ban class="h-3 w-3 mr-1" />
                                        Suspended
                                    </div>
                                @elseif ($statusKey === 'pending')
                                    <div class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                        <x-lucide-clock class="h-3 w-3 mr-1" />
                                        Pending Verification
                                    </div>
                                @else
                                    <div class="inline-flex items-center rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-bold text-green-700">
                                        <x-lucide-circle-check class="h-3 w-3 mr-1" />
                                        Active
                                    </div>
                                @endif
                            </td>
                            <td class="p-4 align-middle text-sm font-semibold text-gray-900">
                                <div class="flex items-center gap-1">
                                    <x-lucide-file-text class="h-4 w-4 text-gray-400" />
                                    {{ $reportsCount }}
                                </div>
                            </td>
                            <td class="p-4 align-middle text-sm text-gray-600">{{ $createdLabel }}</td>
                            <td class="p-4 align-middle text-right">
                                <div class="relative inline-block text-left">
                                    <button type="button" @click="menu = menu === '{{ $loop->index }}' ? null : '{{ $loop->index }}'" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-sm font-medium hover:bg-accent hover:text-accent-foreground">
                                        <x-lucide-more-horizontal class="h-4 w-4" />
                                    </button>
                                    <div x-show="menu === '{{ $loop->index }}'" x-cloak @click.outside="menu = null" class="absolute right-0 z-50 mt-1 w-48 overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md">
                                        <div class="px-2 py-1.5 text-sm font-semibold">Actions</div>
                                        <div class="-mx-1 my-1 h-px bg-muted"></div>
                                        <button type="button" class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm hover:bg-accent" @click="openView(@js($payload))">
                                            <x-lucide-eye class="mr-2 h-4 w-4" />
                                            View User
                                        </button>
                                        <div class="-mx-1 my-1 h-px bg-muted"></div>
                                        <button type="button" class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm hover:bg-accent" @click="openAssign(@js($payload))">
                                            <x-lucide-user-cog class="mr-2 h-4 w-4" />
                                            Assign as Representative
                                        </button>
                                        <div class="-mx-1 my-1 h-px bg-muted"></div>
                                        <button type="button" class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm text-red-600 hover:bg-red-50" @click="openDelete(@js($payload))">
                                            <x-lucide-user-x class="mr-2 h-4 w-4" />
                                            Delete User
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-b">
                            <td colspan="6" class="h-64 p-4 text-center align-middle">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <x-lucide-users-2 class="h-12 w-12 mb-3 opacity-40" />
                                    <p class="text-lg font-medium">No users found</p>
                                    <p class="text-sm">Try adjusting your search or filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr x-show="shown === 0 && {{ $rows->count() }} > 0" x-cloak>
                        <td colspan="6" class="h-64 p-4 text-center align-middle">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <x-lucide-users-2 class="h-12 w-12 mb-3 opacity-40" />
                                <p class="text-lg font-medium">No users found</p>
                                <p class="text-sm">Try adjusting your search or filters</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="viewOpen" x-cloak class="fixed inset-0 z-50 bg-black/80" @keydown.escape.window="viewOpen = false"></div>
    <div x-show="viewOpen" x-cloak class="fixed left-1/2 top-1/2 z-50 grid w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 gap-4 border bg-background p-6 shadow-lg sm:rounded-lg max-h-[80vh] overflow-y-auto" @click.outside="viewOpen = false">
        <button type="button" class="absolute right-4 top-4 rounded-sm opacity-70 hover:opacity-100" @click="viewOpen = false">
            <x-lucide-x class="h-4 w-4" />
            <span class="sr-only">Close</span>
        </button>
        <div class="flex flex-col space-y-1.5 text-center sm:text-left">
            <h2 class="text-lg font-semibold leading-none tracking-tight">User Details</h2>
            <p class="text-sm text-muted-foreground">View information and report history for <span x-text="selected?.alias || ''"></span></p>
        </div>
        <div class="space-y-6 py-4" x-show="selected">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500">Alias</label>
                    <p class="font-medium" x-text="selected?.alias"></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Email</label>
                    <p class="font-medium" x-text="selected?.email"></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Position</label>
                    <p class="font-medium" x-text="selected?.position"></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Status</label>
                    <div class="mt-1">
                        <span x-show="selected?.status === 'suspended'" class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700">Suspended</span>
                        <span x-show="selected?.status === 'pending'" class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Pending Verification</span>
                        <span x-show="selected?.status === 'active'" class="inline-flex items-center rounded-full border border-green-200 bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700">Active</span>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Created At</label>
                    <p class="font-medium" x-text="selected?.created"></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Total Reports</label>
                    <p class="font-medium" x-text="selected?.reportsCount ?? 0"></p>
                </div>
            </div>
            <div>
                <label class="text-sm font-bold mb-2 block">Report History</label>
                <p x-show="!(selected?.reports || []).length" class="text-sm text-gray-500 italic">No reports submitted yet</p>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <template x-for="report in (selected?.reports || [])" :key="report.id">
                        <div class="p-3 border rounded-lg bg-gray-50">
                            <p class="font-medium text-sm" x-text="report.title || 'Untitled Report'"></p>
                            <p class="text-xs text-gray-500 mt-1"><span x-text="report.category"></span> • <span x-text="report.date"></span></p>
                            <span x-show="report.status" class="mt-2 inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold" x-text="report.status"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
            <button type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent" @click="viewOpen = false">Close</button>
        </div>
    </div>

    <div x-show="assignOpen" x-cloak class="fixed inset-0 z-50 bg-black/80" @keydown.escape.window="assignOpen = false"></div>
    <div x-show="assignOpen" x-cloak class="fixed left-1/2 top-1/2 z-50 grid w-full max-w-md -translate-x-1/2 -translate-y-1/2 gap-4 border bg-background p-6 shadow-lg sm:rounded-lg" @click.outside="assignOpen = false">
        <button type="button" class="absolute right-4 top-4 rounded-sm opacity-70 hover:opacity-100" @click="assignOpen = false">
            <x-lucide-x class="h-4 w-4" />
            <span class="sr-only">Close</span>
        </button>
        <div class="flex flex-col space-y-1.5 text-center sm:text-left">
            <h2 class="text-lg font-semibold leading-none tracking-tight">Assign Representative Role</h2>
            <p class="text-sm text-muted-foreground">Assign a role to <span x-text="selected?.alias"></span> (<span x-text="selected?.email"></span>). This user will be moved to the Representatives page.</p>
        </div>
        <div class="space-y-4 py-4">
            <div>
                <label for="role" class="text-sm font-medium leading-none">Role *</label>
                <select id="role" x-model="role" class="mt-1 flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                    <option value="">Select a role</option>
                    <option value="handler">CODI member</option>
                    <option value="admin">Administrator</option>
                </select>
                <p class="text-xs text-gray-500 mt-1" x-text="role === 'handler' ? '⚙️ Can be assigned to process complaints' : (role === 'admin' ? '🔧 Full administrative access' : 'Select a role')"></p>
            </div>
        </div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
            <button type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent" @click="assignOpen = false">Cancel</button>
            <button type="button" class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90" @click="assignOpen = false">Assign Role</button>
        </div>
    </div>

    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 bg-black/80" @keydown.escape.window="deleteOpen = false"></div>
    <div x-show="deleteOpen" x-cloak class="fixed left-1/2 top-1/2 z-50 grid w-full max-w-[500px] -translate-x-1/2 -translate-y-1/2 gap-4 border bg-background p-6 shadow-lg sm:rounded-lg" @click.outside="deleteOpen = false">
        <div class="flex flex-col space-y-1.5 text-center sm:text-left">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-red-100">
                    <x-lucide-trash-2 class="h-6 w-6 text-red-600" />
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900">Delete user</h2>
                    <p class="mt-1 text-sm text-gray-500">This action cannot be undone</p>
                </div>
            </div>
        </div>
        <div class="space-y-4 py-4">
            <div class="relative w-full rounded-lg border border-red-200 bg-red-50 p-4">
                <x-lucide-alert-triangle class="absolute left-4 top-4 h-4 w-4 text-red-600" />
                <div class="pl-7 text-sm text-red-800">
                    Deleting <span class="font-semibold break-all" x-text="selected?.confirmAlias"></span> will permanently remove their account and all
                    <span class="font-semibold" x-text="selected?.reportsCount ?? 0"></span> associated report<span x-show="(selected?.reportsCount ?? 0) !== 1">s</span>.
                    This cannot be recovered.
                </div>
            </div>
            <div class="space-y-2">
                <label for="confirm-alias" class="text-sm font-medium text-gray-700">
                    Type <span class="font-mono font-semibold text-gray-900 break-all" x-text="selected?.confirmAlias"></span> to confirm
                </label>
                <input id="confirm-alias" type="text" x-model="confirmText" placeholder="Type alias here..." autocomplete="off" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 font-mono text-sm focus-visible:outline-none focus-visible:ring-2" :class="confirmText.length && confirmText === (selected?.confirmAlias || '') ? 'border-green-500 focus-visible:ring-green-500' : (confirmText.length ? 'border-red-300 focus-visible:ring-red-500' : '')">
                <div x-show="confirmText.length > 0" class="flex items-center gap-1.5 text-sm">
                    <span x-show="confirmText === (selected?.confirmAlias || '')" class="flex items-center gap-1.5 text-green-600 font-medium">
                        <x-lucide-check-circle-2 class="h-4 w-4 text-green-600" />
                        ✓ Alias confirmed
                    </span>
                    <span x-show="confirmText !== (selected?.confirmAlias || '')" class="flex items-center gap-1.5 text-red-500">
                        <x-lucide-alert-triangle class="h-4 w-4 text-red-500" />
                        Alias does not match
                    </span>
                </div>
            </div>
        </div>
        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-gray-300 bg-background px-4 text-sm font-medium hover:bg-gray-50" @click="confirmText = ''; deleteOpen = false">Cancel</button>
            <button type="button" class="inline-flex h-10 items-center justify-center rounded-md px-4 text-sm font-medium" :disabled="confirmText !== (selected?.confirmAlias || '')" :class="confirmText === (selected?.confirmAlias || '') ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-red-200 text-red-400 cursor-not-allowed'" @click="deleteOpen = false">Delete user</button>
        </div>
    </div>
</div>
</x-layouts.admin>
