{{-- Port of frontend/src/views/admin/RepresentativesManagement.tsx --}}
<x-layouts.admin>
@php
    $rows = $rows ?? collect();
    $codiCount = $rows->filter(fn ($row) => in_array(data_get($row, 'role'), ['handler', 'codi'], true))->count();
    $adminCount = $rows->filter(fn ($row) => data_get($row, 'role') === 'admin')->count();
@endphp
<div
    class="w-full space-y-6 pb-10"
    x-data="{
        open: false,
        editing: false,
        menu: null,
        form: { id: '', userId: '', email: '', displayName: '', role: 'handler', phone: '', password: '' },
        openAdd() {
            this.editing = false;
            this.form = { id: '', userId: '', email: '', displayName: '', role: 'handler', phone: '', password: '' };
            this.open = true;
            this.menu = null;
        },
        openEdit(row) {
            this.editing = true;
            this.form = {
                id: row.id || '',
                userId: row.userId || '',
                email: row.email || '',
                displayName: row.displayName || '',
                role: row.role || 'handler',
                phone: row.phone || '',
                password: ''
            };
            this.open = true;
            this.menu = null;
        },
        close() { this.open = false; this.editing = false; }
    }"
>
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Staff Management</p>
            <h1 class="text-xl font-bold text-gray-900">Representatives</h1>
            <p class="text-sm text-gray-500 mt-1">Manage Administrators and CODI members</p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="window.location.reload()" class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent hover:text-accent-foreground">
                <x-lucide-refresh-cw class="h-4 w-4 mr-1.5" />
                Refresh
            </button>
            <button type="button" @click="openAdd()" class="inline-flex h-9 items-center justify-center gap-2 rounded-md px-3 text-sm font-medium text-white" style="background-color: #1D9E75;">
                <x-lucide-user-plus class="h-4 w-4 mr-1.5" />
                Add Staff
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 bg-black/80" @keydown.escape.window="close()"></div>
    <div x-show="open" x-cloak class="fixed left-1/2 top-1/2 z-50 grid max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 gap-4 overflow-y-auto border bg-background p-6 shadow-lg sm:rounded-lg" @click.outside="close()">
        <button type="button" class="absolute right-4 top-4 z-20 rounded-sm opacity-70 hover:opacity-100" @click="close()">
            <x-lucide-x class="h-4 w-4" />
            <span class="sr-only">Close</span>
        </button>
        <div class="flex flex-col space-y-1.5 text-center sm:text-left">
            <h2 class="text-lg font-semibold leading-none tracking-tight" x-text="editing ? 'Edit Staff Member' : 'Add New Staff Member'"></h2>
            <p class="text-sm text-muted-foreground" x-text="editing ? 'Update staff member information' : 'Add a new staff member to the system'"></p>
        </div>
        <div class="relative w-full rounded-lg border border-blue-200 bg-blue-50 p-4">
            <x-lucide-info class="absolute left-4 top-4 h-4 w-4 text-blue-600" />
            <div class="pl-7 text-sm text-blue-800">
                <strong>Important:</strong> The staff member signs in at /admin/login with the email and password set here.
                <p class="mt-2 text-xs">CODI members see only the cases assigned to them.</p>
            </div>
        </div>
        <form method="POST" :action="editing ? '/admin/representatives/' + form.id : '/admin/representatives'" class="space-y-4 py-4">
            @csrf
            <input type="hidden" name="_method" :value="editing ? 'PUT' : 'POST'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="displayName" class="text-sm font-medium leading-none">Display Name / CODI Nickname *</label>
                    <input id="displayName" name="display_name" type="text" required x-model="form.displayName" placeholder="e.g. CODI 1" class="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    <p class="text-xs text-gray-500 mt-1">Shown to complainants in case chat (not the staff member's personal name).</p>
                </div>
                <div>
                    <label for="email" class="text-sm font-medium leading-none">Email *</label>
                    <input id="email" name="email" type="email" x-model="form.email" placeholder="name@gordoncollege.edu.ph" :readonly="editing" class="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50">
                </div>
                <div>
                    <label for="role" class="text-sm font-medium leading-none">Role *</label>
                    <select id="role" name="role" x-model="form.role" class="mt-1 flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="handler">CODI member</option>
                        <option value="admin">Administrator</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1" x-text="form.role === 'admin' ? 'Full system access including case assignment' : 'Can be assigned to process complaints'"></p>
                </div>
                <div>
                    <label for="phone" class="text-sm font-medium leading-none">Phone Number</label>
                    <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="11" x-model="form.phone" placeholder="09XXXXXXXXX" class="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                </div>
                <div x-show="!editing">
                    <label for="password" class="text-sm font-medium leading-none">Password *</label>
                    <input id="password" name="password" type="text" x-model="form.password" placeholder="At least 8 characters" class="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <p class="text-xs text-muted-foreground mt-1">At least 8 characters, with upper, lower, and a number</p>
                </div>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                <button type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent" @click="close()">Cancel</button>
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90" x-text="editing ? 'Update Representative' : 'Add Representative'"></button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-lg border-0 bg-white shadow-sm transition-shadow hover:shadow-md">
            <div class="p-4">
                <div class="mb-3 flex items-start justify-between">
                    <div class="rounded-lg bg-emerald-50 p-2 dark:bg-[#1D9E75]/20">
                        <x-lucide-users class="h-5 w-5 text-[#1D9E75]" />
                    </div>
                </div>
                <p class="mb-1 text-xs uppercase tracking-wide text-gray-600">Total Staff</p>
                <p class="text-2xl font-bold text-gray-900">{{ $rows->count() }}</p>
                <p class="mt-0.5 text-xs text-gray-500">All representatives</p>
            </div>
        </div>
        <div class="rounded-lg border-0 bg-white shadow-sm transition-shadow hover:shadow-md">
            <div class="p-4">
                <div class="mb-3 flex items-start justify-between">
                    <div class="rounded-lg bg-emerald-50 p-2 dark:bg-[#1D9E75]/20">
                        <x-lucide-briefcase class="h-5 w-5 text-[#1D9E75]" />
                    </div>
                </div>
                <p class="mb-1 text-xs uppercase tracking-wide text-gray-600">CODI members</p>
                <p class="text-2xl font-bold text-gray-900">{{ $codiCount }}</p>
                <p class="mt-0.5 text-xs text-gray-500">Process complaints</p>
            </div>
        </div>
        <div class="rounded-lg border-0 bg-white shadow-sm transition-shadow hover:shadow-md">
            <div class="p-4">
                <div class="mb-3 flex items-start justify-between">
                    <div class="rounded-lg bg-emerald-50 p-2 dark:bg-[#1D9E75]/20">
                        <x-lucide-shield class="h-5 w-5 text-[#1D9E75]" />
                    </div>
                </div>
                <p class="mb-1 text-xs uppercase tracking-wide text-gray-600">Administrators</p>
                <p class="text-2xl font-bold text-gray-900">{{ $adminCount }}</p>
                <p class="mt-0.5 text-xs text-gray-500">Full system access</p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 border-b p-6 pb-4 pt-6">
            <h3 class="flex items-center gap-3 text-lg font-bold leading-none tracking-tight text-gray-900">
                <div class="rounded-lg bg-emerald-50 p-2 dark:bg-[#1D9E75]/20">
                    <x-lucide-users class="h-5 w-5 text-[#1D9E75]" />
                </div>
                All Representatives
            </h3>
        </div>
        <div class="p-6 pt-6">
            @forelse ($rows as $row)
                @php
                    $name = (string) data_get($row, 'display_name', data_get($row, 'displayName', ''));
                    $parts = preg_split('/\s+/', trim($name)) ?: [];
                    $initials = '';
                    foreach (array_slice($parts, 0, 2) as $part) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                    $role = (string) data_get($row, 'role', 'handler');
                    $roleLabel = $role === 'admin' ? 'Administrator' : 'CODI member';
                    $roleColor = $role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800';
                    $isActive = (bool) data_get($row, 'is_active', data_get($row, 'isActive', true));
                    $email = (string) data_get($row, 'email', '');
                    $payload = [
                        'id' => (string) data_get($row, 'id', ''),
                        'userId' => (string) data_get($row, 'user_id', data_get($row, 'userId', '')),
                        'email' => $email,
                        'displayName' => $name,
                        'role' => $role === 'codi' ? 'handler' : $role,
                        'phone' => (string) data_get($row, 'phone', ''),
                    ];
                @endphp
                @if ($loop->first)
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @endif
                <div class="group relative rounded-xl border border-gray-200 bg-white p-5 transition-all hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-3 flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                                    <span class="text-sm font-semibold text-gray-600">{{ $initials }}</span>
                                </div>
                                <div class="flex-1">
                                    <div class="mb-0.5 flex items-center gap-2">
                                        <h3 class="text-sm font-semibold text-gray-900">{{ $name }}</h3>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $roleColor }}">{{ $roleLabel }}</span>
                                        @if (! $isActive)
                                            <span class="inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-2 py-0.5 text-[10px] font-semibold text-orange-700">Inactive</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                        <x-lucide-mail class="h-3 w-3" />
                                        <span>{{ $email }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="relative flex items-center">
                            <button type="button" @click="menu = menu === '{{ $loop->index }}' ? null : '{{ $loop->index }}'" class="inline-flex h-9 w-9 items-center justify-center rounded-md border-2 border-gray-200 p-0 hover:border-gray-300 hover:bg-gray-50">
                                <x-lucide-more-vertical class="h-5 w-5 text-gray-600" />
                            </button>
                            <div x-show="menu === '{{ $loop->index }}'" x-cloak @click.outside="menu = null" class="absolute right-0 top-10 z-50 w-48 overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md">
                                <button type="button" class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm hover:bg-accent" @click="openEdit(@js($payload))">
                                    <x-lucide-pencil class="mr-2 h-4 w-4" />
                                    Edit Details
                                </button>
                                <form method="POST" action="/admin/representatives/{{ data_get($row, 'id') }}/toggle">@csrf<button class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm hover:bg-accent">
                                    @if ($isActive)
                                        <x-lucide-circle-x class="mr-2 h-4 w-4 text-orange-600" />
                                        <span>Deactivate</span>
                                    @else
                                        <x-lucide-circle-check class="mr-2 h-4 w-4 text-green-600" />
                                        <span>Activate</span>
                                    @endif
                                </button></form>
                                <div class="-mx-1 my-1 h-px bg-muted"></div>
                                <form method="POST" action="/admin/representatives/{{ data_get($row, 'id') }}" onsubmit="return confirm('Remove this representative?')">@csrf @method('DELETE')<button class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm text-red-600 hover:bg-accent">
                                    <x-lucide-trash-2 class="mr-2 h-4 w-4" />
                                    Delete Staff
                                </button></form>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($loop->last)
                    </div>
                @endif
            @empty
                <div class="py-8 text-center">
                    <x-lucide-user class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900">No Representatives</h3>
                    <p class="text-gray-600">Representatives are managed through the Users page. Assign users as representatives to have them appear here.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
</x-layouts.admin>
