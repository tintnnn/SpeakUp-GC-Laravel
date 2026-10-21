<x-layouts.complainant>
@php
    $complaints = $complaints ?? ($cases ?? collect());
    $notifications = $notifications ?? collect();
    if (! $complaints instanceof \Illuminate\Support\Collection) {
        $complaints = collect($complaints);
    }
    if (! $notifications instanceof \Illuminate\Support\Collection) {
        $notifications = collect($notifications);
    }
    $loading = (bool) ($loading ?? false);
    $user = auth()->user();
    $userName = 'Respondent';
    if ($user?->display_name) {
        $userName = explode(' ', trim((string) $user->display_name))[0] ?: 'Respondent';
    }

    $statusText = fn ($c) => strtolower((string) data_get($c, 'status', 'submitted'));
    $hasResponse = function ($c) {
        $flag = data_get($c, 'has_response', data_get($c, 'hasResponse'));
        if ($flag !== null) {
            return (bool) $flag;
        }
        return (bool) (data_get($c, 'respondent_response') ?? data_get($c, 'respondentResponse'));
    };
    $awaitingResponse = $complaints->filter(function ($c) use ($statusText, $hasResponse) {
        return str_contains($statusText($c), 'await') || ! $hasResponse($c);
    })->count();
    $resolvedCount = $complaints->filter(fn ($c) => str_contains($statusText($c), 'resolv'))->count();
    $topNotifications = $notifications->take(3);
    $unreadCount = $notifications->filter(fn ($n) => data_get($n, 'status') === 'unread')->count();

    $fmtDate = function ($value, $format = 'M j, Y') {
        if (! $value) return '';
        try {
            return \Illuminate\Support\Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return '';
        }
    };
    $ago = function ($value) {
        if (! $value) return '';
        try {
            return \Illuminate\Support\Carbon::parse($value)->diffForHumans();
        } catch (\Throwable $e) {
            return '';
        }
    };
    $badge = function (string $status) {
        $s = strtolower($status);
        if (str_contains($s, 'resolv')) {
            return ['Resolved', 'bg-green-100 text-green-700 border-green-200'];
        }
        if (str_contains($s, 'dismiss')) {
            return ['Dismissed', 'bg-red-100 text-red-700 border-red-200'];
        }
        if (str_contains($s, 'investigat')) {
            return ['Under Investigation', 'bg-yellow-100 text-yellow-700 border-yellow-200'];
        }
        if (str_contains($s, 'review')) {
            return ['Under Review', 'bg-blue-100 text-blue-700 border-blue-200'];
        }
        if (str_contains($s, 'await')) {
            return ['Awaiting Your Response', 'bg-orange-100 text-orange-700 border-orange-200'];
        }
        return [$status, 'bg-gray-100 text-gray-700 border-gray-200'];
    };
@endphp
<div class="min-h-screen bg-gray-50">
    <div class="bg-white border-b border-gray-200 px-4 py-4">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-orange-100 flex items-center justify-center">
                    <x-lucide-shield class="w-5 h-5 text-orange-600" />
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900">Respondent Portal</p>
                    <p class="text-xs text-gray-500">SpeakUp GC — Gordon College DEIU</p>
                </div>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-800 transition-colors">
                    <x-lucide-log-out class="w-4 h-4" />
                    Sign out
                </button>
            </form>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">
        <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5 flex gap-4">
            <x-lucide-info class="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5" />
            <div>
                <p class="text-sm font-semibold text-orange-800 mb-1">
                    You have been formally notified of a complaint.
                </p>
                <p class="text-xs text-orange-700 leading-relaxed">
                    In accordance with Gordon College's disciplinary process, you are entitled to view the
                    complaint details below and submit a formal written response. All information is handled
                    with strict confidentiality by the DEIU office. If you need assistance, contact the
                    Guidance Counselor.
                </p>
            </div>
        </div>

        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Welcome, {{ $userName }}</h1>
                <p class="text-sm text-gray-500">Review cases filed against you and submit your formal responses.</p>
            </div>
            <button
                type="button"
                @disabled($loading || $complaints->isEmpty())
                class="shrink-0 inline-flex items-center gap-2 h-9 rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent disabled:pointer-events-none disabled:opacity-50"
            >
                <x-lucide-download class="h-4 w-4" />
                Download Report
            </button>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-lg border bg-white text-card-foreground shadow-sm">
                <div class="p-4 text-center">
                    <p class="text-3xl font-bold text-gray-900">{{ $loading ? '–' : $complaints->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Cases</p>
                </div>
            </div>
            <div class="rounded-lg border bg-white text-card-foreground shadow-sm">
                <div class="p-4 text-center">
                    <p class="text-3xl font-bold text-orange-600">{{ $loading ? '–' : $awaitingResponse }}</p>
                    <p class="text-xs text-gray-500 mt-1">Awaiting Response</p>
                </div>
            </div>
            <div class="rounded-lg border bg-white text-card-foreground shadow-sm">
                <div class="p-4 text-center">
                    <p class="text-3xl font-bold text-green-600">{{ $loading ? '–' : $resolvedCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">Resolved</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <h2 class="text-base font-semibold text-gray-900">Cases Against You</h2>

                @if ($loading)
                    <div class="flex justify-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
                    </div>
                @else
                    @forelse ($complaints as $case)
                        @php
                            $id = data_get($case, 'id');
                            $title = data_get($case, 'title') ?: 'Untitled Case';
                            $category = data_get($case, 'category') ?? data_get($case, 'type') ?: 'General';
                            $status = (string) data_get($case, 'status', 'submitted');
                            [$badgeLabel, $badgeClass] = $badge($status);
                            $filed = data_get($case, 'created_at') ?? data_get($case, 'createdAt') ?? data_get($case, 'filed_at') ?? data_get($case, 'filedDate');
                            $deadline = data_get($case, 'response_due_at') ?? data_get($case, 'responseDeadline');
                            $responded = $hasResponse($case);
                            $notified = data_get($case, 'respondent_notified', data_get($case, 'respondentNotified'));
                        @endphp
                        <div class="rounded-lg border bg-white text-card-foreground shadow-sm hover:shadow-md transition-shadow">
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-4 mb-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 truncate">{{ $title }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ $category }} · Filed {{ $fmtDate($filed) }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                </div>

                                @if ($deadline && ! $responded)
                                    <div class="flex items-center gap-1.5 text-xs text-orange-600 mb-3">
                                        <x-lucide-clock class="w-3.5 h-3.5" />
                                        Response deadline: {{ $fmtDate($deadline, 'M j') }}
                                    </div>
                                @endif

                                @if (! $notified)
                                    <div class="flex items-center gap-1.5 text-xs text-blue-600 mb-3">
                                        <x-lucide-info class="w-3.5 h-3.5" />
                                        Formal notification pending from DEIU
                                    </div>
                                @endif

                                <div class="flex gap-2">
                                    <a href="/case-tracking/{{ $id }}" class="inline-flex items-center justify-center h-9 rounded-md border border-input bg-background px-3 text-xs font-medium hover:bg-accent">
                                        <x-lucide-file-text class="w-3.5 h-3.5 mr-1" />
                                        View Details
                                    </a>
                                    <a href="/case-chat/{{ $id }}" class="inline-flex items-center justify-center h-9 rounded-md px-3 text-xs font-medium bg-orange-500 hover:bg-orange-600 text-white">
                                        <x-lucide-message-square class="w-3.5 h-3.5 mr-1" />
                                        {{ $responded ? 'View Response' : 'Submit Response' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border bg-white text-card-foreground shadow-sm">
                            <div class="py-12 text-center">
                                <x-lucide-circle-check class="w-10 h-10 text-green-400 mx-auto mb-3" />
                                <p class="text-sm text-gray-500">No complaints have been filed against you.</p>
                            </div>
                        </div>
                    @endforelse
                @endif
            </div>

            <div class="space-y-5">
                <div class="rounded-lg border bg-white text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6 pb-3">
                        <h3 class="text-sm font-semibold flex items-center gap-2 leading-none">
                            <x-lucide-bell class="w-4 h-4" />
                            Notifications
                            @if ($unreadCount > 0)
                                <span class="inline-flex items-center rounded-full bg-orange-500 text-white text-xs ml-auto px-2.5 py-0.5 font-semibold">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </h3>
                    </div>
                    <div class="p-6 pt-0 space-y-2">
                        @forelse ($topNotifications as $note)
                            @php
                                $isUnread = data_get($note, 'status') === 'unread';
                                $created = data_get($note, 'created_at') ?? data_get($note, 'createdAt');
                                $time = $ago($created);
                                $actionUrl = data_get($note, 'action_url') ?? data_get($note, 'actionUrl') ?: '/notifications';
                                $message = data_get($note, 'message') ?: data_get($note, 'title');
                            @endphp
                            <a
                                href="{{ $actionUrl }}"
                                class="block w-full text-left p-3 rounded-lg border text-xs transition-colors {{ $isUnread ? 'bg-orange-50 border-orange-200 hover:bg-orange-100' : 'bg-gray-50 border-gray-200 hover:bg-gray-100' }}"
                            >
                                <div class="flex items-start gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full mt-1 flex-shrink-0 {{ $isUnread ? 'bg-orange-500' : 'bg-gray-400' }}"></div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 line-clamp-2">{{ $message }}</p>
                                        @if ($time)
                                            <p class="text-gray-500 mt-0.5">{{ $time }}</p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-gray-500 py-2">No notifications yet.</p>
                        @endforelse
                        <a href="/notifications" class="inline-flex items-center justify-center w-full mt-1 h-9 rounded-md border border-input bg-background text-xs font-medium hover:bg-accent">
                            View all
                        </a>
                    </div>
                </div>

                <div class="rounded-lg border bg-white text-card-foreground shadow-sm border-l-4 border-l-orange-400">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2 mb-2">
                            <x-lucide-alert-triangle class="w-4 h-4 text-orange-500" />
                            Your Rights
                        </h3>
                        <ul class="text-xs text-gray-600 space-y-1.5 leading-relaxed">
                            <li>• You have the right to view the complaint filed against you.</li>
                            <li>• You may submit a formal written response through this portal.</li>
                            <li>• All proceedings are confidential.</li>
                            <li>• You may request assistance from the Guidance Counselor.</li>
                        </ul>
                    </div>
                </div>

                <a href="/chat" class="inline-flex items-center justify-center w-full h-10 rounded-md border border-input bg-background text-sm font-medium hover:bg-accent">
                    <x-lucide-message-square class="w-4 h-4 mr-2" />
                    Open Messages
                </a>
            </div>
        </div>
    </div>
</div>
</x-layouts.complainant>
