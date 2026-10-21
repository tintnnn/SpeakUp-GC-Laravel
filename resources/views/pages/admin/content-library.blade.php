{{-- Port of frontend/src/views/admin/ContentManagement.tsx --}}
<x-layouts.admin>
@php
    $rows = $rows ?? collect();
    $categories = [
        'Mental Wellness',
        'Anxiety Management',
        'Depression Support',
        'Stress Relief',
        'Mindfulness',
        'Self-Care',
        'Relationships',
        'Sleep Health',
        'Other',
    ];
@endphp
<div
    class="space-y-6"
    x-data="{
        open: false,
        deleteOpen: false,
        editing: false,
        videoUrl: '',
        videoId: '',
        title: '',
        host: '',
        description: '',
        duration: '',
        tags: [],
        deleteTitle: '',
        categories: @js($categories),
        extractId(url) {
            const patterns = [
                /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
                /^([a-zA-Z0-9_-]{11})$/
            ];
            for (const pattern of patterns) {
                const match = (url || '').match(pattern);
                if (match) return match[1];
            }
            return '';
        },
        onUrl() { this.videoId = this.extractId(this.videoUrl.trim()); },
        toggleTag(tag) {
            this.tags = this.tags.includes(tag) ? this.tags.filter((item) => item !== tag) : this.tags.concat([tag]);
        },
        openAdd() {
            this.editing = false;
            this.videoUrl = '';
            this.videoId = '';
            this.title = '';
            this.host = '';
            this.description = '';
            this.duration = '';
            this.tags = [];
            this.open = true;
        },
        openEdit(row) {
            this.editing = true;
            this.videoId = row.videoId || '';
            this.videoUrl = this.videoId ? 'https://www.youtube.com/watch?v=' + this.videoId : '';
            this.title = row.title || '';
            this.host = row.host || '';
            this.description = row.description || '';
            this.duration = row.duration || '';
            this.tags = row.tags || [];
            this.open = true;
        },
        close() { this.open = false; },
        askDelete(title) { this.deleteTitle = title || ''; this.deleteOpen = true; }
    }"
>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900 sm:text-2xl md:text-3xl">Content Library Management</h1>
            <p class="mt-1 text-gray-600">Manage mental health videos and podcasts displayed in the Learning Hub</p>
        </div>
        <button type="button" @click="openAdd()" class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <x-lucide-plus class="h-4 w-4" />
            Add Video
        </button>
    </div>

    @forelse ($rows as $row)
        @php
            $title = (string) data_get($row, 'title', '');
            $host = (string) data_get($row, 'host', '');
            $description = (string) data_get($row, 'description', '');
            $duration = (string) data_get($row, 'duration', '');
            $videoId = (string) data_get($row, 'video_id', data_get($row, 'videoId', ''));
            $tags = data_get($row, 'tags', []);
            $tags = is_array($tags) ? array_values($tags) : (is_iterable($tags) ? collect($tags)->values()->all() : []);
            $payload = compact('title', 'host', 'description', 'duration', 'videoId', 'tags');
        @endphp
        @if ($loop->first)
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @endif
        <div class="flex flex-col rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <div class="mb-4 aspect-video w-full overflow-hidden rounded-lg bg-gray-100">
                    @if ($videoId !== '')
                        <img src="https://img.youtube.com/vi/{{ $videoId }}/hqdefault.jpg" alt="{{ $title }}" class="h-full w-full object-cover">
                    @endif
                </div>
                <h3 class="line-clamp-2 text-lg font-semibold leading-none tracking-tight">{{ $title }}</h3>
                <p class="text-sm text-gray-600">By {{ $host }}</p>
            </div>
            <div class="flex-grow p-6 pt-0">
                <p class="mb-3 line-clamp-3 text-sm text-gray-600">{{ $description }}</p>
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    @forelse ($tags as $tag)
                        <span class="rounded-full bg-primary/10 px-2 py-1 text-xs text-primary">{{ $tag }}</span>
                    @empty
                        <span class="text-xs text-gray-400">No labels</span>
                    @endforelse
                    <span class="ml-auto text-xs text-gray-500">{{ $duration }}</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="inline-flex h-9 flex-1 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent" @click="openEdit(@js($payload))">
                        <x-lucide-pencil class="mr-1 h-4 w-4" />
                        Edit
                    </button>
                    <button type="button" class="inline-flex h-9 flex-1 items-center justify-center rounded-md bg-destructive px-3 text-sm font-medium text-destructive-foreground hover:bg-destructive/90" @click="askDelete(@js($title))">
                        <x-lucide-trash-2 class="mr-1 h-4 w-4" />
                        Delete
                    </button>
                </div>
            </div>
        </div>
        @if ($loop->last)
            </div>
        @endif
    @empty
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="py-12 text-center">
                <x-lucide-video class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                <h3 class="mb-2 text-lg font-semibold">No videos yet</h3>
                <p class="mb-4 text-gray-600">Start building your mental health content library by adding videos</p>
                <button type="button" @click="openAdd()" class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                    <x-lucide-plus class="mr-2 h-4 w-4" />
                    Add First Video
                </button>
            </div>
        </div>
    @endforelse

    <div x-show="open" x-cloak class="fixed inset-0 z-50 bg-black/80" @keydown.escape.window="close()"></div>
    <div x-show="open" x-cloak class="fixed left-1/2 top-1/2 z-50 grid max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 gap-4 overflow-y-auto border bg-background p-6 shadow-lg sm:rounded-lg" @click.outside="close()">
        <button type="button" class="absolute right-4 top-4 rounded-sm opacity-70 hover:opacity-100" @click="close()">
            <x-lucide-x class="h-4 w-4" />
            <span class="sr-only">Close</span>
        </button>
        <div class="flex flex-col space-y-1.5 text-center sm:text-left">
            <h2 class="text-lg font-semibold leading-none tracking-tight" x-text="editing ? 'Edit Video' : 'Add New Video'"></h2>
            <p class="text-sm text-muted-foreground" x-text="editing ? 'Update the video information below.' : 'Paste a YouTube URL and we\'ll automatically fetch the video details for you.'"></p>
        </div>
        <form class="space-y-4" @submit.prevent="close()">
            <div class="space-y-2">
                <label for="videoUrl" class="text-sm font-medium leading-none">YouTube Video URL *</label>
                <input id="videoUrl" type="text" required x-model="videoUrl" @input="onUrl()" placeholder="https://www.youtube.com/watch?v=..." class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                <p class="text-xs text-gray-500">Paste the full YouTube URL and we'll automatically get the video details</p>
            </div>
            <div x-show="videoId" x-cloak class="space-y-4">
                <div class="rounded border bg-gray-50 p-2 dark:border-border dark:bg-muted">
                    <p class="mb-2 text-xs text-gray-600">Preview:</p>
                    <img :src="'https://img.youtube.com/vi/' + videoId + '/hqdefault.jpg'" alt="Video thumbnail" class="w-full rounded">
                </div>
                <div class="space-y-2">
                    <label for="title" class="text-sm font-medium leading-none">Title *</label>
                    <input id="title" type="text" required x-model="title" placeholder="Enter video title" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                </div>
                <div class="space-y-2">
                    <label for="host" class="text-sm font-medium leading-none">Host/Creator *</label>
                    <input id="host" type="text" x-model="host" placeholder="Enter host or creator name" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                </div>
                <div class="space-y-2">
                    <label for="description" class="text-sm font-medium leading-none">Description</label>
                    <textarea id="description" rows="4" x-model="description" placeholder="Enter video description" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="duration" class="text-sm font-medium leading-none">Duration</label>
                        <input id="duration" type="text" x-model="duration" placeholder="e.g., 15 mins" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none">Labels/Tags *</label>
                        <div class="max-h-40 space-y-2 overflow-y-auto rounded-lg border bg-white p-3 dark:border-border dark:bg-card">
                            <template x-for="tag in categories" :key="tag">
                                <label class="flex cursor-pointer items-center gap-2 rounded p-1 hover:bg-gray-50">
                                    <input type="checkbox" class="h-4 w-4" :checked="tags.includes(tag)" @change="toggleTag(tag)">
                                    <span class="text-sm" x-text="tag"></span>
                                </label>
                            </template>
                        </div>
                        <p class="text-xs text-gray-500">Selected: <span x-text="tags.length"></span> <span x-text="tags.length === 1 ? 'label' : 'labels'"></span></p>
                    </div>
                </div>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                <button type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent" @click="close()">
                    <x-lucide-x class="mr-2 h-4 w-4" />
                    Cancel
                </button>
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:pointer-events-none disabled:opacity-50" :disabled="!videoId">
                    <x-lucide-save class="mr-2 h-4 w-4" />
                    <span x-text="editing ? 'Update Video' : 'Add Video'"></span>
                </button>
            </div>
        </form>
    </div>

    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 bg-black/80" @keydown.escape.window="deleteOpen = false"></div>
    <div x-show="deleteOpen" x-cloak class="fixed left-1/2 top-1/2 z-50 grid w-full max-w-lg -translate-x-1/2 -translate-y-1/2 gap-4 border bg-background p-6 shadow-lg sm:rounded-lg" @click.outside="deleteOpen = false">
        <div class="flex flex-col space-y-1.5 text-center sm:text-left">
            <h2 class="text-lg font-semibold leading-none tracking-tight">Delete Video</h2>
            <p class="text-sm text-muted-foreground">Are you sure you want to delete "<span x-text="deleteTitle"></span>"? This action cannot be undone.</p>
        </div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
            <button type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent" @click="deleteOpen = false">Cancel</button>
            <button type="button" class="inline-flex h-10 items-center justify-center rounded-md bg-red-500 px-4 text-sm font-medium text-white hover:bg-red-600" @click="deleteOpen = false">Delete</button>
        </div>
    </div>
</div>
</x-layouts.admin>
