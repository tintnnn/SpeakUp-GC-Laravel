<x-layouts.complainant>
@php
    $conversations = $conversations ?? collect();
    if (! $conversations instanceof \Illuminate\Support\Collection) {
        $conversations = collect($conversations);
    }
    $loading = (bool) ($loading ?? false);
    $uid = auth()->id();
    $ago = function ($value) {
        if (! $value) return '';
        try {
            return \Illuminate\Support\Carbon::parse($value)->diffForHumans();
        } catch (\Throwable $e) {
            return '';
        }
    };
@endphp
<div class="min-h-full bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
        <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Messages</h1>
                <p class="text-sm text-gray-500 mt-1">Conversations with your CODI members</p>
            </div>
        </div>

        <div class="max-w-3xl">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                    <x-lucide-message-square class="h-5 w-5 text-[#1D9E75]" />
                    <h2 class="text-base font-semibold text-gray-900">Case Conversations</h2>
                </div>

                <div class="p-4">
                    @if ($loading)
                        <div class="flex items-center justify-center py-12 gap-3">
                            <x-lucide-loader class="h-6 w-6 animate-spin text-[#1D9E75]" />
                            <span class="text-sm text-gray-500">Loading conversations…</span>
                        </div>
                    @else
                        @forelse ($conversations as $room)
                            @php
                                $complaintId = data_get($room, 'complaint_id') ?? data_get($room, 'complaintId');
                                $title = data_get($room, 'complaint_title') ?? data_get($room, 'complaintTitle') ?: 'Case Chat';
                                $isActive = data_get($room, 'is_active', data_get($room, 'isActive', true));
                                $handlerName = data_get($room, 'handler_name') ?? data_get($room, 'handlerName') ?: 'CODI member';
                                $otherRole = data_get($room, 'other_role') ?? data_get($room, 'participant_role') ?? 'handler';
                                $otherName = data_get($room, 'other_name') ?? $handlerName;
                                $isStaff = in_array($otherRole, ['handler', 'codi', 'admin'], true);
                                $updated = data_get($room, 'updated_at') ?? data_get($room, 'updatedAt') ?? data_get($room, 'last_message_at');
                                $sender = data_get($room, 'last_message_sender_name') ?? data_get($room, 'lastMessage.senderName');
                                $content = data_get($room, 'last_message_content') ?? data_get($room, 'lastMessage.content');
                                $unreadMap = data_get($room, 'unreadCount');
                                $unread = 0;
                                if (is_array($unreadMap) && $uid) {
                                    $unread = (int) ($unreadMap[$uid] ?? 0);
                                }
                                if ($unread === 0) {
                                    $unread = (int) (data_get($room, 'unread_count') ?? 0);
                                }
                            @endphp
                            <a
                                href="/case-chat/{{ $complaintId }}"
                                class="block w-full text-left p-4 rounded-xl border border-gray-200 hover:border-[#1D9E75]/40 hover:bg-green-50/50 transition-all group {{ $loop->first ? '' : 'mt-2' }}"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="h-10 w-10 flex-shrink-0 rounded-full flex items-center justify-center {{ $isStaff ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                        @if ($isStaff)
                                            <x-lucide-shield class="h-4 w-4" />
                                        @else
                                            <x-lucide-user class="h-4 w-4" />
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="font-semibold text-sm text-gray-900 truncate">{{ $title }}</span>
                                                @if (! $isActive)
                                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs bg-gray-100 text-gray-500 shrink-0">Closed</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                @if ($unread > 0)
                                                    <span class="inline-flex items-center rounded-full border-transparent bg-[#1D9E75] text-white text-xs px-2.5 py-0.5 font-semibold">
                                                        {{ $unread > 9 ? '9+' : $unread }}
                                                    </span>
                                                @endif
                                                <x-lucide-chevron-right class="h-4 w-4 text-gray-300 group-hover:text-[#1D9E75] transition-colors" />
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-xs text-gray-500">{{ $isStaff ? 'CODI member: ' : '' }}{{ $otherName }}</span>
                                            <span class="text-gray-300">·</span>
                                            <span class="text-xs text-gray-400 flex items-center gap-0.5">
                                                <x-lucide-clock class="h-3 w-3" />
                                                {{ $ago($updated) }}
                                            </span>
                                        </div>

                                        @if ($content)
                                            <p class="text-sm text-gray-600 mt-1 truncate">
                                                <span class="font-medium text-gray-700">{{ $sender ?: 'Message' }}:</span>
                                                {{ $content }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-12">
                                <x-lucide-message-square class="h-12 w-12 text-gray-200 mx-auto mb-3" />
                                <h3 class="text-base font-semibold text-gray-800 mb-1">No Messages Yet</h3>
                                <p class="text-sm text-gray-500 max-w-sm mx-auto mb-4">
                                    Once a CODI member is assigned to your complaint, your conversation will appear here.
                                </p>
                                <a
                                    href="/complaints"
                                    class="inline-flex items-center justify-center h-10 px-4 rounded-md border border-[#1D9E75]/30 text-[#1D9E75] hover:bg-green-50 text-sm font-medium"
                                >
                                    View My Cases
                                </a>
                            </div>
                        @endforelse
                    @endif
                </div>
            </div>

            @if (! $loading && $conversations->isNotEmpty())
                <div class="mt-4 p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3">
                    <x-lucide-circle-alert class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-blue-800">Private & Secure</p>
                        <p class="text-xs text-blue-600 mt-0.5">
                            All messages are encrypted and only visible to you and your assigned CODI member.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
</x-layouts.complainant>
