{{-- Port of frontend/src/views/admin/LocationsPage.tsx --}}
<x-layouts.admin>
@php
    $rows = $rows ?? collect();
@endphp
<div
    class="space-y-6"
    x-data="{
        q: '',
        category: 'All',
        open: false,
        editing: false,
        form: { id: '', name: '', category: '' },
        shown: {{ (int) $rows->count() }},
        visible(name, cat) {
            const textOk = !this.q || (name || '').toLowerCase().includes(this.q.toLowerCase());
            const catOk = this.category === 'All' || cat === this.category;
            return textOk && catOk;
        },
        recount() {
            const items = [...this.$root.querySelectorAll('[data-location]')];
            this.shown = items.filter((item) => this.visible(item.dataset.name || '', item.dataset.category || '')).length;
        },
        openAdd() {
            this.editing = false;
            this.form = { id: '', name: '', category: '' };
            this.open = true;
        },
        openEdit(row) {
            this.editing = true;
            this.form = { id: row.id || '', name: row.name || '', category: row.category || '' };
            this.open = true;
        },
        close() { this.open = false; this.editing = false; this.form = { id: '', name: '', category: '' }; }
    }"
    @input="recount()"
>
    <div>
        <a href="/admin/settings" class="mb-3 -ml-2 inline-flex h-9 items-center rounded-md px-3 text-sm font-medium text-gray-500 hover:bg-accent hover:text-gray-900">
            <x-lucide-arrow-left class="mr-1 h-4 w-4" />
            Back to Settings
        </a>
        <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500">Configuration</p>
        <h1 class="text-xl font-bold text-gray-900">Location Management</h1>
        <p class="mt-1 text-sm text-gray-500">Manage rooms, buildings, and departments for complaint filing</p>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="flex items-center gap-2 text-2xl font-semibold leading-none tracking-tight">
                        <x-lucide-map-pin class="h-5 w-5" />
                        Locations
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        <span x-text="{{ (int) $rows->count() }}"></span> location<span x-show="{{ (int) $rows->count() }} !== 1">s</span> · <span x-text="shown"></span> shown
                    </p>
                </div>
                <button type="button" @click="openAdd()" class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                    <x-lucide-plus class="mr-2 h-4 w-4" />
                    Add Location
                </button>
            </div>
        </div>
        <div class="space-y-4 p-6 pt-0">
            <div class="relative">
                <x-lucide-search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input type="search" x-model="q" placeholder="Search locations by name…" class="flex h-10 w-full rounded-md border border-input bg-background py-2 pl-9 pr-3 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>

            <div class="inline-flex h-auto flex-wrap items-center justify-center gap-1 rounded-md bg-muted p-1 text-muted-foreground">
                @foreach (['All', 'Room', 'Building', 'Department', 'Common Area', 'Other'] as $tab)
                    <button type="button" @click="category = '{{ $tab }}'; recount()" class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-xs font-medium sm:text-sm" :class="category === '{{ $tab }}' ? 'bg-background text-foreground shadow-sm' : ''">{{ $tab }}</button>
                @endforeach
            </div>

            @forelse ($rows as $row)
                @php
                    $name = (string) data_get($row, 'name', '');
                    $category = (string) data_get($row, 'category', '');
                    $payload = ['id' => (string) data_get($row, 'id', ''), 'name' => $name, 'category' => $category];
                @endphp
                @if ($loop->first)
                    <div class="space-y-2">
                @endif
                <div
                    data-location
                    data-name="{{ $name }}"
                    data-category="{{ $category }}"
                    x-show="visible($el.dataset.name, $el.dataset.category)"
                    class="flex items-center justify-between rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-border dark:hover:bg-white/5"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <x-lucide-building-2 class="h-5 w-5 shrink-0 text-gray-400" />
                        <div class="min-w-0">
                            <p class="truncate font-medium text-gray-900">{{ $name }}</p>
                            <p class="text-sm text-gray-500">{{ $category }}</p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" class="inline-flex h-9 items-center justify-center rounded-md px-3 text-sm font-medium hover:bg-accent" @click="openEdit(@js($payload))">
                            <x-lucide-pencil class="h-4 w-4" />
                        </button>
                        <button type="button" class="inline-flex h-9 items-center justify-center rounded-md px-3 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700" @click="if (confirm('Are you sure you want to delete this location?')) { $el.closest('[data-location]').remove(); recount(); }">
                            <x-lucide-trash-2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                @if ($loop->last)
                    </div>
                @endif
            @empty
                <div class="py-8 text-center text-gray-500">
                    <x-lucide-building-2 class="mx-auto mb-4 h-12 w-12 text-gray-300" />
                    <p>No locations added yet</p>
                    <p class="mt-1 text-sm">Click "Add Location" to get started</p>
                </div>
            @endforelse

            @if ($rows->isNotEmpty())
                <div x-show="shown === 0" x-cloak class="py-8 text-center text-gray-500">
                    <x-lucide-building-2 class="mx-auto mb-4 h-12 w-12 text-gray-300" />
                    <p>No locations match your search</p>
                    <p class="mt-1 text-sm">Try a different name or category filter</p>
                </div>
            @endif
        </div>
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 bg-black/80" @keydown.escape.window="close()"></div>
    <div x-show="open" x-cloak class="fixed left-1/2 top-1/2 z-50 grid w-full max-w-lg -translate-x-1/2 -translate-y-1/2 gap-4 border bg-background p-6 shadow-lg sm:rounded-lg" @click.outside="close()">
        <button type="button" class="absolute right-4 top-4 rounded-sm opacity-70 hover:opacity-100" @click="close()">
            <x-lucide-x class="h-4 w-4" />
            <span class="sr-only">Close</span>
        </button>
        <div class="flex flex-col space-y-1.5 text-center sm:text-left">
            <h2 class="text-lg font-semibold leading-none tracking-tight" x-text="editing ? 'Edit Location' : 'Add New Location'"></h2>
            <p class="text-sm text-muted-foreground" x-text="editing ? 'Update the location details below' : 'Add a new location to the system'"></p>
        </div>
        <form @submit.prevent="close()">
            <div class="space-y-4 py-4">
                <div>
                    <label for="name" class="text-sm font-medium leading-none">Location Name *</label>
                    <input id="name" type="text" x-model="form.name" placeholder="e.g., Room 101, Main Building, Library" class="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                </div>
                <div>
                    <label for="category" class="text-sm font-medium leading-none">Category *</label>
                    <select id="category" x-model="form.category" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-[#1D9E75] dark:border-border dark:bg-card dark:text-foreground">
                        <option value="">Select category</option>
                        @foreach (['Room', 'Building', 'Department', 'Common Area', 'Other'] as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                <button type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent" @click="close()">Cancel</button>
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90" x-text="editing ? 'Update' : 'Add'"></button>
            </div>
        </form>
    </div>
</div>
</x-layouts.admin>
