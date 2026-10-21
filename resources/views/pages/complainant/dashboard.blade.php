<x-layouts.complainant>
@php
    $complaints = $complaints ?? collect();
    $notifications = $notifications ?? collect();
    $conversations = $conversations ?? collect();
    if (! $complaints instanceof \Illuminate\Support\Collection) {
        $complaints = collect($complaints);
    }
    if (! $notifications instanceof \Illuminate\Support\Collection) {
        $notifications = collect($notifications);
    }
    if (! $conversations instanceof \Illuminate\Support\Collection) {
        $conversations = collect($conversations);
    }

    $loading = (bool) ($loading ?? false);
    $user = auth()->user();
    $alias = $user?->alias;
    if ($alias) {
        $userName = $alias;
    } elseif ($user?->display_name) {
        $userName = explode(' ', trim((string) $user->display_name))[0] ?: 'User';
    } else {
        $userName = 'User';
    }
    $isReturningUser = (bool) ($user?->profile_setup_complete);
    $welcomeLabel = $isReturningUser ? 'Welcome back' : 'Welcome';

    $statusMap = [
        'draft' => 'draft',
        'submitted' => 'submitted',
        'under_review' => 'under_review',
        'requirements_pending' => 'requirements_pending',
        'validated' => 'validated',
        'investigating' => 'investigating',
        'awaiting_response' => 'awaiting_response',
        'under_deliberation' => 'under_deliberation',
        'in_mediation' => 'in_mediation',
        'awaiting_evidence' => 'awaiting_evidence',
        'on_hold' => 'on_hold',
        'escalated' => 'escalated',
        'resolved' => 'resolved',
        'dismissed' => 'dismissed',
        'withdrawn' => 'withdrawn',
        'pending' => 'submitted',
        'in_progress' => 'investigating',
        'inprogress' => 'investigating',
        'in progress' => 'investigating',
        'closed' => 'resolved',
    ];
    $mapStatus = function ($complaint) use ($statusMap) {
        $raw = strtolower(trim((string) data_get($complaint, 'status', 'submitted')));
        $key = str_replace([' ', '-'], ['_', '_'], $raw);
        if ($key === 'inprogress') {
            $key = 'inprogress';
        }
        return $statusMap[$raw] ?? $statusMap[$key] ?? 'submitted';
    };

    $inProgressStatuses = ['validated', 'investigating', 'awaiting_response', 'under_deliberation'];
    $decidedStatuses = ['resolved', 'dismissed'];
    $total = $complaints->count();
    $inProgress = $complaints->filter(fn ($c) => in_array($mapStatus($c), $inProgressStatuses, true))->count();
    $decided = $complaints->filter(fn ($c) => in_array($mapStatus($c), $decidedStatuses, true))->count();
    $recentComplaints = $complaints->take(4);
    $unreadCount = $notifications->filter(fn ($n) => data_get($n, 'status') === 'unread')->count();
    $isNewUser = ! $loading && $total === 0 && $notifications->isEmpty() && $conversations->isEmpty();
    $topNotifications = $notifications->take(2);
    $previewRoom = $conversations->first();

    $fmtDate = function ($value) {
        if (! $value) {
            return '';
        }
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('M j, Y');
        } catch (\Throwable $e) {
            return '';
        }
    };
    $ago = function ($value) {
        if (! $value) {
            return '';
        }
        try {
            return \Illuminate\Support\Carbon::parse($value)->diffForHumans();
        } catch (\Throwable $e) {
            return '';
        }
    };
    $listBadge = function (string $status) {
        if (in_array($status, ['submitted', 'under_review'], true)) {
            return ['Submitted', 'bg-yellow-50 text-yellow-700 border border-yellow-200'];
        }
        if (in_array($status, ['investigating', 'awaiting_response', 'under_deliberation'], true)) {
            return ['Ongoing Investigation', 'bg-blue-50 text-blue-700 border border-blue-200'];
        }
        if ($status === 'resolved') {
            return ['Resolved', 'bg-green-50 text-green-700 border border-green-200'];
        }
        if ($status === 'dismissed') {
            return ['Dismissed', 'bg-red-50 text-red-700 border border-red-200'];
        }
        return match ($status) {
            'investigating' => ['Investigating', 'bg-yellow-50 text-yellow-700 border border-yellow-200'],
            'under_review' => ['Under Review', 'bg-blue-50 text-blue-700 border border-blue-200'],
            'awaiting_response' => ['Awaiting Response', 'bg-orange-50 text-orange-700 border border-orange-200'],
            default => [ucwords(str_replace('_', ' ', $status)), 'bg-purple-50 text-purple-700 border border-purple-200'],
        };
    };

    $dailyTips = [
        "You have the right to file anonymously. Your identity will be protected throughout the entire process, and you can choose whether or not to reveal yourself at any time.",
        "All complaints are handled with strict confidentiality by the DEIU office. Your personal information is secured and encrypted.",
        "You can file a complaint even if you don't know the full name of the person who harassed you. A description is sufficient.",
        "The DEIU investigates all complaints fairly and impartially. Both parties will be heard during the process.",
        "You have the right to withdraw your complaint at any point during the investigation if you feel it's the right decision.",
        "Evidence such as screenshots, photos, messages, or witness statements can strengthen your case. Keep copies of all relevant materials.",
        "The Safe Spaces Act (RA 11313) protects you from gender-based harassment in streets, public spaces, online, and workplaces.",
        "You are entitled to psychological support and counseling services through the Gordon College DEIU office throughout the process.",
        "Filing a complaint does not automatically reveal your identity to the respondent. You control when and if your identity is disclosed.",
        "The Anti-Sexual Harassment Act (RA 7877) covers harassment in employment, education, and training environments.",
        "You can request a CODI member of a specific gender if it makes you more comfortable during the investigation.",
        "All DEIU proceedings are conducted in a safe, private, and respectful environment. Your dignity is always protected.",
        "You have the right to be informed of the progress and outcome of your complaint at every stage of the investigation.",
        "Retaliation against complainants is strictly prohibited and can result in additional charges against the respondent.",
    ];
    $dayOfYear = (int) now()->format('z') + 1;
    $todaysTip = $dailyTips[$dayOfYear % count($dailyTips)];

    $uid = auth()->id();
@endphp
<div class="w-full space-y-6" x-data="{ showPrivacy: true }">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $welcomeLabel }}, <span class="text-[#1D9E75]">{{ $userName }}</span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">
            {{ $isReturningUser ? "Here's what's happening with your cases today." : "Glad you're here — let's get you set up and ready." }}
        </p>
    </div>

    <div
        x-show="showPrivacy"
        x-cloak
        class="flex items-start gap-3 p-4 rounded-2xl relative"
        style="background: var(--privacy-bg); border: 1px solid var(--card-outline)"
    >
        <div class="rounded-lg p-2 shrink-0 bg-[#DCFCE7]">
            <x-lucide-lock class="h-4 w-4 text-[#166534]" />
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-[#14532D]">All submitted reports are kept confidential</p>
            <p class="text-xs mt-0.5 leading-relaxed text-[#166534]">
                Every report is handled with strict confidentiality by the DEIU office.
                Your identity is <strong>never disclosed</strong> to respondents without your consent.
                You may file anonymously, and you can share a sealed identity with DEIU only so both sides can be treated fairly if the case proceeds.
            </p>
        </div>
        <button
            type="button"
            @click="showPrivacy = false"
            class="shrink-0 p-1 hover:bg-green-200/50 rounded-lg transition-colors"
            aria-label="Dismiss"
        >
            <x-lucide-x class="h-4 w-4 text-[#166534]" />
        </button>
    </div>

    <div class="grid grid-cols-3 gap-4 lg:gap-6">
        @foreach ([
            ['label' => 'Total Filed', 'value' => $total, 'icon' => 'file-text'],
            ['label' => 'Ongoing Investigation', 'value' => $inProgress, 'icon' => 'folder-search'],
            ['label' => 'Resolved', 'value' => $decided, 'icon' => 'gavel'],
        ] as $stat)
            <div
                class="bg-white rounded-xl p-3 sm:p-4 flex flex-col items-center text-center"
                style="border: 1px solid var(--card-outline)"
            >
                <x-dynamic-component :component="'lucide-'.$stat['icon']" class="h-6 w-6 sm:h-7 sm:w-7 text-[#1D9E75] mb-2 sm:mb-3" stroke-width="1.5" />
                <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $loading ? '–' : $stat['value'] }}</p>
                <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div
                class="rounded-2xl overflow-hidden"
                style="background: var(--card-surface); border: 1px solid var(--card-outline)"
            >
                <div
                    class="flex items-center justify-between px-5 py-3.5"
                    style="border-bottom: 1px solid var(--card-divider)"
                >
                    <p class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <x-lucide-file-text class="h-4 w-4 text-gray-400" />
                        Recent Cases
                    </p>
                    @if ($total > 0)
                        <a
                            href="/complaints"
                            class="text-xs text-[#1D9E75] hover:text-[#178F65] font-medium flex items-center gap-1"
                        >
                            View all ({{ $total }}) <x-lucide-arrow-right class="h-3 w-3" />
                        </a>
                    @endif
                </div>

                <div class="p-4 min-h-[160px]">
                    @if ($loading)
                        <div class="flex items-center justify-center h-full py-16">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#1D9E75]"></div>
                        </div>
                    @elseif ($recentComplaints->isNotEmpty())
                        <div class="space-y-2">
                            @foreach ($recentComplaints as $idx => $complaint)
                                @php
                                    $status = $mapStatus($complaint);
                                    [$badgeLabel, $badgeClass] = $listBadge($status);
                                    $title = data_get($complaint, 'title') ?: 'Untitled Complaint';
                                    $name = data_get($complaint, 'complainant_name') ?? data_get($complaint, 'complainantName') ?: 'Anonymous';
                                    $created = data_get($complaint, 'created_at') ?? data_get($complaint, 'createdAt');
                                    $caseId = data_get($complaint, 'id');
                                    $caseLabel = data_get($complaint, 'case_number') ?: $caseId;
                                @endphp
                                <a
                                    href="/case-tracking/{{ $caseId }}"
                                    class="block w-full text-left p-3 rounded-xl transition-colors hover:bg-[#F0FDF4] border-[0.5px] border-transparent hover:border-[#6BB89A]"
                                >
                                    <div class="flex items-start gap-3">
                                        <div class="rounded-lg p-2 shrink-0" style="background: #DCFCE7">
                                            <x-lucide-file-text class="h-4 w-4 text-[#1D9E75]" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $title }}</p>
                                                <span title="Confidential">
                                                    <x-lucide-shield class="h-3.5 w-3.5 text-gray-400 shrink-0" />
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                {{ $name }} · {{ $caseLabel }} · {{ $fmtDate($created) }}
                                            </p>
                                            <div class="mt-2">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach

                            @if ($recentComplaints->count() === 1)
                                <div class="mt-6 pt-6 border-t-2 border-gray-200">
                                    <div class="text-center py-4">
                                        <x-lucide-file-text class="h-8 w-8 text-gray-300 mx-auto mb-2" />
                                        <p class="text-xs text-gray-400">No other recent cases</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Your cases will appear here as you file them</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-full py-6 text-center px-6">
                            <div
                                class="rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center"
                                style="background: #DCFCE7"
                            >
                                <x-lucide-shield class="h-8 w-8 text-[#1D9E75]" />
                            </div>
                            <p class="text-gray-800 font-semibold text-sm">No cases yet</p>
                            <p class="text-xs text-gray-500 mt-2 max-w-xs leading-relaxed">
                                If you've experienced harassment, bullying, or any misconduct, you can file a complaint confidentially.
                                Once submitted, a CODI member will review and update you every step of the way.
                            </p>
                            <a
                                href="/complaints/new"
                                class="inline-flex items-center justify-center mt-5 h-9 rounded-md bg-[#1D9E75] hover:bg-[#178F65] text-white px-5 text-sm font-medium"
                            >
                                <x-lucide-plus class="h-4 w-4 mr-1.5" /> File a Complaint
                            </a>
                            <p class="text-xs text-[#166534] mt-3 flex items-center gap-1.5 font-medium">
                                <x-lucide-lock class="h-3.5 w-3.5" /> Reports stay confidential. You can file anonymously and still share a sealed identity with DEIU.
                            </p>
                        </div>
                    @endif
                </div>

                <div class="px-4 pb-4">
                    <div class="pt-4" style="border-top: 1px solid var(--card-divider)">
                        <div class="rounded-xl p-4 border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-[#2a2318] dark:to-[#241e16] dark:border-amber-900/50">
                            <div class="flex items-start gap-3">
                                <div class="rounded-lg p-2 bg-white dark:bg-white/5 shadow-sm shrink-0">
                                    <x-lucide-lightbulb class="h-5 w-5 text-amber-600 dark:text-amber-300" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-amber-900 dark:text-amber-100 mb-2">Know Your Rights - Tip of the Day</p>
                                    <p class="text-xs text-amber-800 dark:text-amber-200/90 leading-relaxed">
                                        {{ $todaysTip }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <a
                            href="/know-your-rights"
                            class="block w-full mt-3 py-2.5 rounded-lg border border-green-200 text-sm font-medium text-gray-700 hover:bg-green-50 transition-colors text-center"
                        >
                            Learn More About Your Rights
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div
                class="rounded-2xl overflow-hidden"
                style="background: var(--card-surface); border: 1px solid var(--card-outline)"
            >
                <div
                    class="flex items-center justify-between px-5 py-3.5"
                    style="border-bottom: 1px solid var(--card-divider)"
                >
                    <p class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <x-lucide-message-square class="h-4 w-4 text-gray-400" />
                        Messages
                    </p>
                    <a href="/chat" class="text-xs text-[#1D9E75] hover:text-[#178F65] font-medium">See all</a>
                </div>
                <div class="p-4">
                    @if (! $previewRoom)
                        <div class="text-center py-3">
                            <x-lucide-message-square class="h-7 w-7 text-gray-200 mx-auto mb-1" />
                            <p class="text-xs text-gray-400">Messages with your CODI member will appear here once a case is filed.</p>
                        </div>
                    @else
                        @php
                            $roomTitle = data_get($previewRoom, 'complaint_title') ?? data_get($previewRoom, 'complaintTitle') ?: 'Case Chat';
                            $preview = data_get($previewRoom, 'last_message_content')
                                ?? data_get($previewRoom, 'lastMessage.content')
                                ?: 'Open chat';
                            $complaintId = data_get($previewRoom, 'complaint_id') ?? data_get($previewRoom, 'complaintId');
                            $unreadMap = data_get($previewRoom, 'unreadCount');
                            $unread = 0;
                            if (is_array($unreadMap) && $uid) {
                                $unread = (int) ($unreadMap[$uid] ?? 0);
                            }
                            if ($unread === 0) {
                                $unread = (int) (data_get($previewRoom, 'unread_count') ?? 0);
                            }
                        @endphp
                        <a
                            href="/case-chat/{{ $complaintId }}"
                            class="block w-full text-left p-3 rounded-xl transition-colors hover:bg-[#F0FDF4]"
                            style="background: var(--card-tint); border: 1px solid var(--card-outline)"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-gray-800 truncate">{{ $roomTitle }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $preview }}</p>
                                </div>
                                @if ($unread > 0)
                                    <span class="bg-[#1D9E75] text-white text-xs rounded-full px-1.5 py-0.5 font-medium shrink-0">
                                        {{ $unread > 9 ? '9+' : $unread }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endif
                </div>
            </div>

            @if ($isNewUser)
                <div
                    class="rounded-2xl overflow-hidden"
                    style="background: var(--card-surface); border: 1px solid var(--card-outline)"
                >
                    <div class="px-5 py-3.5" style="border-bottom: 1px solid var(--card-divider)">
                        <p class="text-sm font-semibold text-gray-800">Getting Started</p>
                        <p class="text-xs text-gray-400 mt-0.5">Follow these steps to get the most out of SpeakUp GC</p>
                    </div>
                    <div class="p-4 space-y-2">
                        @foreach ([
                            ['step' => 1, 'label' => 'Know your rights', 'desc' => 'Read RA 11313, confidentiality rules, and the filing FAQ', 'link' => '/know-your-rights?tab=faq'],
                            ['step' => 2, 'label' => 'Track your case', 'desc' => 'See if DEIU accepted your complaint and follow every move in My Cases', 'link' => '/complaints'],
                        ] as $item)
                            <a
                                href="{{ $item['link'] }}"
                                class="w-full text-left flex items-start gap-3 p-3 rounded-xl transition-colors hover:bg-[#F0FDF4]"
                                style="border: 1px solid var(--card-outline)"
                            >
                                <div
                                    class="rounded-full w-6 h-6 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold"
                                    style="background: #DCFCE7; color: #1D9E75"
                                >
                                    {{ $item['step'] }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800">{{ $item['label'] }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $item['desc'] }}</p>
                                </div>
                                <x-lucide-arrow-right class="h-3.5 w-3.5 text-gray-300 shrink-0 mt-1" />
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div
                    class="rounded-2xl overflow-hidden"
                    style="background: var(--card-surface); border: 1px solid var(--card-outline)"
                >
                    <div
                        class="flex items-center justify-between px-5 py-3.5"
                        style="border-bottom: 1px solid var(--card-divider)"
                    >
                        <p class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                            <x-lucide-bell class="h-4 w-4 text-gray-400" />
                            Notifications
                            @if ($unreadCount > 0)
                                <span class="bg-[#1D9E75] text-white text-xs rounded-full px-2 py-0.5 font-medium">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </p>
                        <a href="/notifications" class="text-xs text-[#1D9E75] hover:text-[#178F65] font-medium">See all</a>
                    </div>
                    <div class="p-4">
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
                                class="block w-full text-left p-3 rounded-xl transition-colors {{ $loop->first ? '' : 'mt-3' }}"
                                style="background: {{ $isUnread ? 'var(--privacy-bg)' : 'var(--muted-surface)' }}; border: 1px solid {{ $isUnread ? 'var(--card-outline)' : 'var(--card-divider)' }}"
                            >
                                <div class="flex items-start gap-2">
                                    <div
                                        class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0"
                                        style="background: {{ $isUnread ? '#1D9E75' : '#D1D5DB' }}"
                                    ></div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-gray-800 leading-snug">{{ $message }}</p>
                                        @if ($time)
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $time }}</p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="py-3 text-center">
                                <x-lucide-bell class="h-6 w-6 text-gray-200 mx-auto mb-1" />
                                <p class="text-xs text-gray-400">You're all caught up.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
</x-layouts.complainant>
