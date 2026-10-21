<x-layouts.complainant>
@php
    $notifications = $notifications ?? collect();
    if (! $notifications instanceof \Illuminate\Support\Collection) {
        $notifications = collect($notifications);
    }
    $loading = (bool) ($loading ?? false);
    $error = $error ?? null;

    $typeLabels = [
        'complaint_created' => 'Complaint Created',
        'complaint_assigned' => 'Case Taken',
        'case_assigned' => 'Case Taken',
        'status_update' => 'Status Updated',
        'complaint_resolved' => 'Complaint Resolved',
        'complaint_escalated' => 'Complaint Escalated',
        'complaint_reopened' => 'Complaint Reopened',
        'case_closed' => 'Case Closed',
        'new_comment' => 'New Comment',
        'evidence_requested' => 'Evidence Requested',
        'deadline_reminder' => 'Deadline Reminder',
        'action_required' => 'Action Required',
        'new_message' => 'New Message',
        'message_read' => 'Message Read',
        'evidence_uploaded' => 'Evidence Uploaded',
        'comment_added' => 'Comment Added',
        'handler_assigned' => 'Complaint Accepted',
        'handler_changed' => 'CODI member changed',
        'deadline_approaching' => 'Deadline Approaching',
        'deadline_passed' => 'Deadline Passed',
        'system_announcement' => 'System Announcement',
        'maintenance_scheduled' => 'Maintenance Scheduled',
        'account_updated' => 'Account Updated',
        'hearing_scheduled' => 'Hearing Scheduled',
        'hearing_reminder' => 'Hearing Reminder',
        'decision_issued' => 'Decision Issued',
        'respondent_notified' => 'Respondent Notified',
        'complaint_filed_against_you' => 'Complaint Filed Against You',
    ];
    $ago = function ($value) {
        if (! $value) return '';
        try {
            return \Illuminate\Support\Carbon::parse($value)->diffForHumans();
        } catch (\Throwable $e) {
            return '';
        }
    };
    $exact = function ($value) {
        if (! $value) return '';
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('M j, Y, g:i:s A');
        } catch (\Throwable $e) {
            return '';
        }
    };
    $unreadCount = $notifications->filter(fn ($n) => data_get($n, 'status') === 'unread')->count();
    $readCount = $notifications->filter(fn ($n) => data_get($n, 'status') === 'read')->count();
@endphp
<div
    class="min-h-full bg-gray-50"
    x-data="{
        tab: 'all',
        search: '',
        show(status, title, message) {
            if (this.tab === 'unread' && status !== 'unread') return false;
            if (this.tab === 'read' && status !== 'read') return false;
            const q = this.search.toLowerCase();
            if (!q) return true;
            return (title || '').includes(q) || (message || '').includes(q);
        },
        get anyVisible() {
            this.tab; this.search;
            return [...this.$root.querySelectorAll('[data-note]')].some((el) => this.show(el.dataset.status, el.dataset.title, el.dataset.message));
        }
    }"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Notifications</h1>
            <p class="text-sm text-gray-500 mt-1">Stay updated on your cases and account activity</p>
        </div>

        @if ($error)
            <div class="mb-6 relative w-full rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                <div class="flex items-start gap-2">
                    <x-lucide-circle-alert class="h-4 w-4 mt-0.5" />
                    <p class="text-sm">{{ $error }}</p>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg border border-gray-200 p-3 mb-4 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                <div class="flex-1 relative w-full">
                    <x-lucide-search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input
                        type="search"
                        x-model="search"
                        placeholder="Search notifications…"
                        class="flex h-9 w-full rounded-md border-0 bg-transparent pl-9 pr-3 text-sm focus-visible:ring-1 focus:outline-none focus:ring-1 focus:ring-[#1D9E75]/30"
                    >
                </div>
                <div class="flex gap-2 flex-wrap">
                    @if ($unreadCount > 0)
                        <form method="POST" action="/notifications/read-all">
                            @csrf
                            <button type="submit" class="inline-flex items-center h-9 px-3 text-xs font-medium rounded-md hover:bg-accent hover:text-accent-foreground">
                                <x-lucide-check-check class="h-3.5 w-3.5 mr-1.5" />
                                Mark all read
                            </button>
                        </form>
                    @endif
                    @if ($readCount > 0)
                        <form method="POST" action="/notifications/delete-read">
                            @csrf
                            <button type="submit" class="inline-flex items-center h-9 px-3 text-xs font-medium rounded-md text-red-600 hover:bg-red-50 hover:text-red-700">
                                <x-lucide-trash-2 class="h-3.5 w-3.5 mr-1.5" />
                                Delete read
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="grid w-full grid-cols-3 mb-4 bg-white border border-gray-200 rounded-md p-1 h-10">
                <button type="button" @click="tab = 'all'" :class="tab === 'all' ? 'bg-gray-100' : ''" class="rounded-sm text-sm font-medium">
                    All ({{ $notifications->count() }})
                </button>
                <button type="button" @click="tab = 'unread'" :class="tab === 'unread' ? 'bg-gray-100' : ''" class="rounded-sm text-sm font-medium">
                    Unread ({{ $unreadCount }})
                </button>
                <button type="button" @click="tab = 'read'" :class="tab === 'read' ? 'bg-gray-100' : ''" class="rounded-sm text-sm font-medium">
                    Read ({{ $readCount }})
                </button>
            </div>

            <div class="space-y-3">
                @if ($loading)
                    <div class="w-full rounded-lg border bg-card text-card-foreground shadow-sm">
                        <div class="flex items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        </div>
                    </div>
                @elseif ($notifications->isEmpty())
                    <div class="w-full rounded-lg border bg-card text-card-foreground shadow-sm">
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <x-lucide-bell class="h-16 w-16 text-gray-400 mb-4" />
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No notifications found</h3>
                            <p class="text-gray-600">You're all caught up!</p>
                        </div>
                    </div>
                @else
                    @foreach ($notifications as $note)
                        @php
                            $id = data_get($note, 'id');
                            $status = (string) (data_get($note, 'status') ?: 'unread');
                            $title = (string) (data_get($note, 'title') ?? '');
                            $rawMessage = (string) (data_get($note, 'message') ?? '');
                            $clean = trim((string) preg_replace('/^Notes:\s*/i', '', $rawMessage));
                            $truncated = mb_strlen($clean) > 150 ? mb_substr($clean, 0, 150).'...' : $clean;
                            $type = (string) (data_get($note, 'type') ?? '');
                            $typeLabel = $typeLabels[$type] ?? ($type !== '' ? ucwords(str_replace('_', ' ', $type)) : '');
                            $created = data_get($note, 'created_at') ?? data_get($note, 'createdAt');
                            $actionUrl = data_get($note, 'action_url') ?? data_get($note, 'actionUrl');
                            $isUnread = $status === 'unread';
                        @endphp
                        <div
                            data-note
                            data-status="{{ $status }}"
                            data-title="{{ strtolower($title) }}"
                            data-message="{{ strtolower($clean) }}"
                            x-show="show($el.dataset.status, $el.dataset.title, $el.dataset.message)"
                            class="w-full transition-all border rounded-lg shadow-sm {{ $isUnread ? 'bg-blue-50/30 border-blue-100 hover:border-blue-200 hover:shadow-sm' : 'bg-white border-gray-200 hover:border-gray-300 hover:shadow-sm' }}"
                        >
                            <div class="p-4">
                                <div class="flex items-start gap-3">
                                    @if ($isUnread)
                                        <div class="flex-shrink-0 mt-1.5">
                                            <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-3 mb-1">
                                            @if ($actionUrl)
                                                <a href="{{ $actionUrl }}" class="text-sm font-semibold leading-snug {{ $isUnread ? 'text-gray-900' : 'text-gray-700' }}">
                                                    {{ $title }}
                                                </a>
                                            @else
                                                <h3 class="text-sm font-semibold leading-snug {{ $isUnread ? 'text-gray-900' : 'text-gray-700' }}">
                                                    {{ $title }}
                                                </h3>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600 mb-2 line-clamp-2 leading-relaxed">{{ $truncated }}</p>
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2 text-xs text-gray-400">
                                                <span>{{ $typeLabel }}</span>
                                                <span>•</span>
                                                <span title="{{ $exact($created) }}">{{ $ago($created) }}</span>
                                            </div>
                                            <div class="flex gap-1">
                                                @if ($isUnread && $id)
                                                    <form method="POST" action="/notifications/{{ $id }}/read">
                                                        @csrf
                                                        <button
                                                            type="submit"
                                                            class="inline-flex items-center justify-center h-7 px-2 text-xs text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md"
                                                            aria-label="Mark as read"
                                                        >
                                                            <x-lucide-check class="h-3.5 w-3.5" />
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($id)
                                                    <form method="POST" action="/notifications/{{ $id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="inline-flex items-center justify-center h-7 px-2 text-xs text-red-600 hover:text-red-700 hover:bg-red-50 rounded-md"
                                                            aria-label="Delete"
                                                        >
                                                            <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div x-show="!anyVisible" x-cloak class="w-full rounded-lg border bg-card text-card-foreground shadow-sm">
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <x-lucide-bell class="h-16 w-16 text-gray-400 mb-4" />
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No notifications found</h3>
                            <p class="text-gray-600" x-text="search ? 'Try adjusting your filters' : 'You\'re all caught up!'"></p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</x-layouts.complainant>
