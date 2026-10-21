{{-- Port of frontend/src/views/admin/CodiMessages.tsx --}}
<x-layouts.admin>
@php
    $rows = $rows ?? collect();
    $unreadTotal = $unreadTotal ?? (int) $rows->sum(fn ($row) => (int) data_get($row, 'unread', data_get($row, 'unread_count', 0)));
@endphp
<div class="w-full space-y-6 pb-10">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500">Inbox</p>
            <h1 class="text-xl font-bold text-gray-900">Messages</h1>
            <p class="mt-1 text-sm text-gray-500">Conversations with complainants on your assigned cases.</p>
        </div>
        @if ($unreadTotal > 0)
            <div class="inline-flex w-fit items-center rounded-full border border-transparent bg-[#1D9E75] px-2.5 py-0.5 text-xs font-semibold text-white">
                {{ $unreadTotal }} unread
            </div>
        @endif
    </div>

    <div class="max-w-3xl overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-border dark:bg-card">
        <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4">
            <x-lucide-message-square class="h-5 w-5 text-[#1D9E75]" />
            <h2 class="text-base font-semibold text-gray-900">Case Conversations</h2>
        </div>
        <div class="p-4">
            @forelse ($rows as $row)
                @php
                    $title = (string) data_get($row, 'complaint_title', data_get($row, 'complaintTitle', 'Case Chat'));
                    $name = (string) data_get($row, 'complainant_name', data_get($row, 'complainantName', 'Complainant'));
                    $last = data_get($row, 'last_message', data_get($row, 'lastMessage'));
                    $lastText = is_string($last) ? $last : (string) data_get($last, 'content', '');
                    if ($last && $lastText === '') {
                        $lastText = 'Attachment';
                    }
                    $active = (bool) data_get($row, 'is_active', data_get($row, 'isActive', true));
                    $unread = (int) data_get($row, 'unread', data_get($row, 'unread_count', 0));
                    $complaintId = data_get($row, 'complaint_id', data_get($row, 'complaintId', data_get($row, 'id')));
                    $updated = data_get($row, 'updated_at', data_get($row, 'updatedAt'));
                    try {
                        $rel = $updated ? \Illuminate\Support\Carbon::parse($updated)->diffForHumans() : '';
                    } catch (\Throwable $e) {
                        $rel = '';
                    }
                @endphp
                @if ($loop->first)
                    <div class="space-y-2">
                @endif
                <a href="/case-chat/{{ $complaintId }}" class="group block w-full rounded-xl border border-gray-200 p-4 text-left transition-all hover:border-[#1D9E75]/40 hover:bg-green-50/50 dark:border-border dark:hover:bg-white/5">
                    <div class="flex items-start gap-3">
                        <div class="relative flex h-10 w-10 flex-shrink-0 overflow-hidden rounded-full">
                            <div class="flex h-full w-full items-center justify-center rounded-full bg-green-100 text-green-700">
                                <x-lucide-user class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="truncate text-sm font-semibold text-gray-900">{{ $title !== '' ? $title : 'Case Chat' }}</span>
                                    @if (! $active)
                                        <span class="inline-flex shrink-0 items-center rounded-full border bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500">Closed</span>
                                    @endif
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    @if ($unread > 0)
                                        <span class="inline-flex items-center rounded-full bg-[#1D9E75] px-2.5 py-0.5 text-xs font-semibold text-white">{{ $unread > 9 ? '9+' : $unread }}</span>
                                    @endif
                                    <x-lucide-chevron-right class="h-4 w-4 text-gray-300 transition-colors group-hover:text-[#1D9E75]" />
                                </div>
                            </div>
                            <div class="mt-0.5 flex items-center gap-1.5">
                                <span class="text-xs text-gray-500">{{ $name !== '' ? $name : 'Complainant' }}</span>
                                @if ($rel !== '')
                                    <span class="text-gray-300">·</span>
                                    <span class="flex items-center gap-0.5 text-xs text-gray-400">
                                        <x-lucide-clock class="h-3 w-3" />
                                        {{ $rel }}
                                    </span>
                                @endif
                            </div>
                            @if ($lastText !== '')
                                <p class="mt-1 truncate text-sm text-gray-600">{{ $lastText }}</p>
                            @endif
                        </div>
                    </div>
                </a>
                @if ($loop->last)
                    </div>
                @endif
            @empty
                <div class="py-12 text-center">
                    <x-lucide-inbox class="mx-auto mb-3 h-12 w-12 text-gray-200" />
                    <h3 class="mb-1 text-base font-semibold text-gray-800">No Messages Yet</h3>
                    <p class="mx-auto mb-4 max-w-sm text-sm text-gray-500">Conversations appear here once you take a case and the complainant sends a message.</p>
                    <a href="/admin/reports" class="inline-flex h-10 items-center justify-center rounded-md border border-[#1D9E75]/30 bg-background px-4 text-sm font-medium text-[#1D9E75] hover:bg-green-50">Go to Case Queue</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
</x-layouts.admin>
