<x-layouts.complainant>
@php
    $complaints = $complaints ?? collect();
    if (! $complaints instanceof \Illuminate\Support\Collection) {
        $complaints = collect($complaints);
    }
    $loading = (bool) ($loading ?? false);

    $statusMap = [
        'draft' => 'draft', 'submitted' => 'submitted', 'under_review' => 'under_review',
        'requirements_pending' => 'requirements_pending', 'validated' => 'validated',
        'investigating' => 'investigating', 'awaiting_response' => 'awaiting_response',
        'under_deliberation' => 'under_deliberation', 'resolved' => 'resolved',
        'dismissed' => 'dismissed', 'withdrawn' => 'withdrawn', 'pending' => 'submitted',
        'in_progress' => 'investigating', 'inprogress' => 'investigating',
        'in progress' => 'investigating', 'closed' => 'resolved',
    ];
    $mapStatus = function ($complaint) use ($statusMap) {
        $raw = strtolower(trim((string) data_get($complaint, 'status', 'submitted')));
        $key = str_replace([' ', '-'], '_', $raw);
        return $statusMap[$raw] ?? $statusMap[$key] ?? 'submitted';
    };
    $normalize = function ($status) {
        return strtolower(str_replace([' ', '-'], '_', (string) $status));
    };
    $isAssigned = function ($complaint) {
        $codi = data_get($complaint, 'assignedCODI', data_get($complaint, 'assigned_codi', []));
        if (is_string($codi) && $codi !== '') {
            $codi = [$codi];
        }
        if (is_array($codi)) {
            foreach ($codi as $name) {
                if (filled($name) && $name !== 'Not yet assigned') {
                    return true;
                }
            }
        }
        $assignedTo = data_get($complaint, 'assigned_representative_id')
            ?? data_get($complaint, 'assigned_user_id')
            ?? data_get($complaint, 'assignedTo')
            ?? data_get($complaint, 'assigned_to');
        return filled($assignedTo);
    };
    $caseStep = function ($status, $assigned) use ($normalize) {
        $s = $normalize($status);
        if (in_array($s, ['resolved', 'dismissed', 'closed'], true)) {
            return 4;
        }
        if (in_array($s, ['inprogress', 'in_progress', 'investigating', 'ongoing', 'awaiting_response', 'under_deliberation'], true)) {
            return 3;
        }
        if ($assigned || in_array($s, ['validated', 'under_review', 'assigned', 'accepted'], true)) {
            return 2;
        }
        return 1;
    };
    $trackerLabel = function ($status, $assigned) use ($caseStep, $normalize) {
        $step = $caseStep($status, $assigned);
        $s = $normalize($status);
        if ($step === 4) {
            if ($s === 'dismissed') return 'Dismissed';
            if ($s === 'closed') return 'Closed';
            return 'Decision Already Made';
        }
        if ($step === 3) return 'Investigating';
        if ($step === 2) return 'Accepted';
        return 'Submitted';
    };
    $statusCfg = function ($status) {
        return match ($status) {
            'investigating' => ['color' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500'],
            'resolved' => ['color' => 'bg-green-50 text-green-700 border-green-200', 'dot' => 'bg-[#1D9E75]'],
            'dismissed' => ['color' => 'bg-red-50 text-red-700 border-red-200', 'dot' => 'bg-red-500'],
            'under_review' => ['color' => 'bg-blue-50 text-blue-700 border-blue-200', 'dot' => 'bg-blue-500'],
            'submitted' => ['color' => 'bg-violet-50 text-violet-700 border-violet-200', 'dot' => 'bg-violet-500'],
            'awaiting_response' => ['color' => 'bg-orange-50 text-orange-700 border-orange-200', 'dot' => 'bg-orange-500'],
            default => ['color' => 'bg-gray-100 text-gray-700 border-gray-200', 'dot' => 'bg-gray-400'],
        };
    };
    $fmtDate = function ($value) {
        if (! $value) return '';
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('M j, Y');
        } catch (\Throwable $e) {
            return 'Invalid Date';
        }
    };
    $formatEnum = fn ($v) => ucwords(str_replace('_', ' ', (string) $v));

    $total = $complaints->count();
    $inProgressStatuses = ['validated', 'investigating', 'awaiting_response', 'under_deliberation'];
    $inProgress = $complaints->filter(fn ($c) => in_array($mapStatus($c), $inProgressStatuses, true))->count();
    $needsAttention = $complaints->filter(function ($c) {
        $unread = (int) (data_get($c, 'unread_count') ?? data_get($c, 'unreadCount') ?? 0);
        $messages = (int) (data_get($c, 'message_unread') ?? data_get($c, 'messageUnread') ?? 0);
        $notes = (int) (data_get($c, 'notification_unread') ?? data_get($c, 'notificationUnread') ?? 0);
        return ($unread ?: ($messages + $notes)) > 0;
    })->count();
    $recentlyUpdated = $complaints->filter(function ($c) {
        $updated = data_get($c, 'updated_at') ?? data_get($c, 'updatedAt');
        if (! $updated) return false;
        try {
            return \Illuminate\Support\Carbon::parse($updated)->greaterThan(now()->subDays(7));
        } catch (\Throwable $e) {
            return false;
        }
    })->count();

    $allowedFilters = ['all', 'submitted', 'under_review', 'investigating', 'awaiting_response', 'resolved', 'dismissed'];
    $initialStatus = request('status', 'all');
    if (! in_array($initialStatus, $allowedFilters, true)) {
        $initialStatus = 'all';
    }
    $trackerSteps = [
        ['key' => 'submitted', 'label' => 'Submitted'],
        ['key' => 'accepted', 'label' => 'Accepted'],
        ['key' => 'investigating', 'label' => 'Investigating'],
        ['key' => 'decided', 'label' => 'Decision'],
    ];
    $user = auth()->user();
@endphp
@if ($loading)
    <div class="min-h-full flex items-center justify-center bg-white">
        <div class="text-center">
            <div class="animate-spin rounded-full h-10 w-10 border-2 border-[#1D9E75] border-t-transparent mx-auto mb-4"></div>
            <p class="text-gray-500 text-sm">Loading your complaints...</p>
        </div>
    </div>
@elseif (! $user)
    <div class="min-h-full flex items-center justify-center bg-white">
        <div class="text-center">
            <x-lucide-alert-triangle class="h-10 w-10 text-red-500 mx-auto mb-4" />
            <h2 class="text-lg font-semibold mb-2">Authentication Required</h2>
            <p class="text-gray-500 text-sm mb-4">Please log in to view your complaints.</p>
            <a href="/login" class="inline-flex items-center justify-center h-10 px-4 rounded-md bg-[#1D9E75] hover:bg-[#178F65] text-white text-sm font-medium">Go to Login</a>
        </div>
    </div>
@else
<div
    class="min-h-full"
    x-data="{
        search: '',
        status: '{{ $initialStatus }}',
        match(title, description, st) {
            const q = this.search.toLowerCase();
            const okSearch = !q || (title || '').includes(q) || (description || '').includes(q);
            const okStatus = this.status === 'all' || st === this.status;
            return okSearch && okStatus;
        },
        get anyVisible() {
            this.search; this.status;
            return [...this.$root.querySelectorAll('[data-case]')].some((el) => this.match(el.dataset.title, el.dataset.description, el.dataset.status));
        },
        clearFilters() { this.search = ''; this.status = 'all'; }
    }"
>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Cases</h1>
                <p class="text-sm text-gray-400 mt-1">Track and manage all your filed complaints</p>
            </div>
            @if ($total > 0)
                <a href="/complaints/new" class="inline-flex items-center justify-center bg-[#1D9E75] hover:bg-[#178F65] text-white shadow-sm w-full sm:w-auto h-10 px-4 rounded-md text-sm font-medium">
                    <x-lucide-plus class="h-4 w-4 mr-2" />
                    File New Complaint
                </a>
            @endif
        </div>

        <div
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
        </div>

        <div class="grid grid-cols-3 gap-3">
            @foreach ([
                ['label' => 'Needs Your Attention', 'value' => $needsAttention, 'icon' => 'bell'],
                ['label' => 'Ongoing Investigation', 'value' => $inProgress, 'icon' => 'folder-search'],
                ['label' => 'Recently Updated', 'value' => $recentlyUpdated, 'icon' => 'clock'],
            ] as $stat)
                <div
                    class="bg-white rounded-xl p-4 flex flex-col items-center text-center"
                    style="border: 1px solid var(--card-outline)"
                >
                    <x-dynamic-component :component="'lucide-'.$stat['icon']" class="h-7 w-7 text-[#1D9E75] mb-3" stroke-width="1.5" />
                    <p class="text-2xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                    <p class="text-xs text-gray-500 font-medium mt-1">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        @if ($total > 0)
            <div class="bg-white rounded-xl p-4" style="border: 1px solid var(--card-outline)">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <x-lucide-search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 h-4 w-4" />
                        <input
                            type="search"
                            x-model="search"
                            placeholder="Search by title or description..."
                            class="flex h-10 w-full rounded-md border border-gray-200 bg-white pl-9 pr-3 text-sm"
                        >
                    </div>
                    <select
                        x-model="status"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#1D9E75]/20 bg-white h-10"
                    >
                        <option value="all">All Status</option>
                        <option value="submitted">Submitted</option>
                        <option value="under_review">Submitted (Under Review)</option>
                        <option value="investigating">Ongoing Investigation</option>
                        <option value="awaiting_response">Ongoing Investigation (Awaiting Response)</option>
                        <option value="resolved">Resolved</option>
                        <option value="dismissed">Dismissed</option>
                    </select>
                    <button
                        type="button"
                        x-show="search || status !== 'all'"
                        x-cloak
                        @click="clearFilters()"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg bg-white h-10"
                    >
                        <x-lucide-x class="h-3.5 w-3.5" /> Clear
                    </button>
                </div>
            </div>
        @endif

        @if ($complaints->isEmpty())
            <div class="bg-white rounded-xl border-2 border-dashed p-12 text-center" style="border-color: var(--card-outline)">
                <div class="max-w-sm mx-auto">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <x-lucide-folder-open class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 mb-2">No Complaints Filed Yet</h3>
                    <p class="text-gray-500 text-sm mb-6">When you file a complaint, it will appear here for you to track.</p>
                    <a href="/complaints/new" class="inline-flex items-center justify-center h-10 px-4 rounded-md bg-[#1D9E75] hover:bg-[#178F65] text-white text-sm font-medium">
                        <x-lucide-plus class="h-4 w-4 mr-2" />
                        File a Complaint
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-3" x-show="anyVisible">
                @foreach ($complaints as $complaint)
                    @php
                        $status = $mapStatus($complaint);
                        $assigned = $isAssigned($complaint);
                        $cfg = $statusCfg($status);
                        $step = $caseStep($status, $assigned);
                        $progress = [1 => 25, 2 => 50, 3 => 75, 4 => 100][$step] ?? 25;
                        $label = $trackerLabel($status, $assigned);
                        $terminal = in_array($status, ['resolved', 'dismissed', 'closed'], true);
                        $title = (string) (data_get($complaint, 'title') ?: 'Untitled Complaint');
                        $description = (string) (data_get($complaint, 'description') ?? '');
                        $type = data_get($complaint, 'type') ?? data_get($complaint, 'category') ?? 'other';
                        $id = data_get($complaint, 'id');
                        $messageUnread = (int) (data_get($complaint, 'message_unread') ?? data_get($complaint, 'messageUnread') ?? 0);
                        $notificationUnread = (int) (data_get($complaint, 'notification_unread') ?? data_get($complaint, 'notificationUnread') ?? 0);
                        $unreadCount = (int) (data_get($complaint, 'unread_count') ?? data_get($complaint, 'unreadCount') ?? 0);
                        if ($unreadCount === 0) {
                            $unreadCount = $messageUnread + $notificationUnread;
                        }
                        $hasUpdate = $unreadCount > 0;
                        $hasCaseUpdate = $notificationUnread > 0;
                        $codi = data_get($complaint, 'assignedCODI', data_get($complaint, 'assigned_codi', []));
                        $hasCodi = $assigned;
                        $rating = (int) (data_get($complaint, 'handling_feedback_rating') ?? data_get($complaint, 'handlingFeedback.rating') ?? 0);
                        $feedbackComment = data_get($complaint, 'handling_feedback_comment') ?? data_get($complaint, 'handlingFeedback.comment');
                        $filed = data_get($complaint, 'filed_at') ?? data_get($complaint, 'filingDate') ?? data_get($complaint, 'filing_date') ?? data_get($complaint, 'created_at');
                        $updated = data_get($complaint, 'updated_at') ?? data_get($complaint, 'updatedAt');
                        $respondent = data_get($complaint, 'respondent_name') ?? data_get($complaint, 'respondentName') ?: 'Not Specified';
                        $location = data_get($complaint, 'incident_location') ?? data_get($complaint, 'incidentLocation') ?? data_get($complaint, 'location');
                        if ($messageUnread > 0 && $notificationUnread > 0) {
                            $unreadTitle = $messageUnread.' unread message'.($messageUnread === 1 ? '' : 's').', '.$notificationUnread.' case update'.($notificationUnread === 1 ? '' : 's');
                        } elseif ($messageUnread > 0) {
                            $unreadTitle = $messageUnread.' unread message'.($messageUnread === 1 ? '' : 's');
                        } else {
                            $unreadTitle = $notificationUnread.' case update'.($notificationUnread === 1 ? '' : 's');
                        }
                    @endphp
                    <div
                        data-case
                        data-title="{{ strtolower($title) }}"
                        data-description="{{ strtolower($description) }}"
                        data-status="{{ $status }}"
                        x-show="match($el.dataset.title, $el.dataset.description, $el.dataset.status)"
                        class="bg-white rounded-xl p-5 hover:shadow-md transition-all duration-200 {{ $hasUpdate ? 'border-2 border-amber-400' : '' }}"
                        @if (! $hasUpdate) style="border: 1px solid var(--card-outline)" @endif
                    >
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start gap-2">
                                    <h3 class="font-semibold text-gray-900 text-base truncate">{{ $title }}</h3>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $cfg['color'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $cfg['dot'] }}"></span>
                                        {{ $label }}
                                    </span>
                                    @if ($hasCaseUpdate)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            New update
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                        {{ $formatEnum($type) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <div class="flex justify-between text-xs text-gray-500 mb-2">
                                <span>Complaint tracker</span>
                                <span class="font-medium text-gray-700">{{ $progress }}%</span>
                            </div>
                            <div>
                                <div class="flex items-center">
                                    @foreach ($trackerSteps as $sIdx => $stage)
                                        @php
                                            $stepNumber = $sIdx + 1;
                                            $complete = $stepNumber < $step || $step === count($trackerSteps);
                                            $active = $stepNumber === $step && $step < count($trackerSteps);
                                        @endphp
                                        <div class="flex flex-col items-center min-w-0">
                                            <div class="rounded-full flex items-center justify-center font-bold border-2 transition-all w-5 h-5 {{ $complete ? 'bg-[#1D9E75] border-[#1D9E75] text-white' : ($active ? 'bg-white border-[#1D9E75] text-[#1D9E75]' : 'bg-white border-gray-300 text-gray-400') }}">
                                                @if ($complete)
                                                    <x-lucide-circle-check class="h-3 w-3" />
                                                @else
                                                    <span class="text-[9px]">{{ $stepNumber }}</span>
                                                @endif
                                            </div>
                                            <span class="text-[9px] mt-1 font-medium text-center leading-tight {{ $active ? 'text-[#1D9E75]' : ($complete ? 'text-gray-700' : 'text-gray-400') }}">
                                                {{ $stage['label'] }}
                                            </span>
                                        </div>
                                        @if ($sIdx < count($trackerSteps) - 1)
                                            <div class="flex-1 h-0.5 mx-1 mb-4 {{ $stepNumber < $step ? 'bg-[#1D9E75]' : 'bg-gray-200' }}"></div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 mt-3">
                                <div class="bg-[#1D9E75] h-2 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                            </div>
                            @if ($assigned && ! $terminal)
                                <p class="text-[11px] text-[#166534] mt-2">
                                    Accepted — a CODI member has taken this case and will move it forward.
                                </p>
                            @endif
                            @if ($rating)
                                <div class="mt-2">
                                    <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                        <div class="flex items-center">
                                            @for ($star = 1; $star <= 5; $star++)
                                                <x-lucide-star class="h-3.5 w-3.5 {{ $star <= $rating ? 'fill-amber-400 text-amber-400' : 'text-gray-300' }}" />
                                            @endfor
                                        </div>
                                        <span class="font-medium text-gray-700">{{ $rating }}/5</span>
                                        @if ($feedbackComment)
                                            <span class="text-gray-400 truncate max-w-[180px]">· {{ $feedbackComment }}</span>
                                        @endif
                                    </div>
                                </div>
                            @elseif ($terminal)
                                <p class="text-[11px] text-amber-700 mt-2">
                                    This case is resolved. Open details to rate how it was handled.
                                </p>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-x-6 gap-y-3 mb-6 text-sm">
                            <div class="flex items-center gap-2 text-gray-600">
                                <x-lucide-calendar class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" />
                                <div>
                                    <span class="text-xs text-gray-400 block">Filed</span>
                                    <span class="text-xs font-medium text-gray-700">{{ $fmtDate($filed) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <x-lucide-user class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" />
                                <div class="min-w-0">
                                    <span class="text-xs text-gray-400 block">Respondent</span>
                                    <span class="text-xs font-medium text-gray-700 truncate block">{{ $respondent }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <x-lucide-map-pin class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" />
                                <div class="min-w-0">
                                    <span class="text-xs text-gray-400 block">Location</span>
                                    <span class="text-xs font-medium text-gray-700 truncate block">{{ $location ?: 'Not specified' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <x-lucide-clock class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" />
                                <div>
                                    <span class="text-xs text-gray-400 block">Last Update</span>
                                    <span class="text-xs font-medium text-gray-700">{{ $fmtDate($updated) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 pt-1 border-t border-gray-100">
                            <div class="flex gap-2">
                                <a
                                    href="/case-tracking/{{ $id }}"
                                    class="flex-1 flex items-center justify-center gap-2 py-2 px-3 text-sm font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <x-lucide-eye class="h-4 w-4" />
                                    View Details
                                </a>
                                @if ($hasCodi)
                                    <a
                                        href="/case-chat/{{ $id }}"
                                        class="relative flex-1 flex items-center justify-center gap-2 py-2 px-3 text-sm font-medium text-[#1D9E75] border border-[#1D9E75]/30 rounded-lg hover:bg-green-50 transition-colors"
                                    >
                                        <x-lucide-message-square class="h-4 w-4" />
                                        Message
                                        @if ($hasUpdate)
                                            <span
                                                class="absolute -top-1.5 -right-1.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                                                title="{{ $unreadTitle }}"
                                            >
                                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                            </span>
                                        @endif
                                    </a>
                                @else
                                    <button
                                        type="button"
                                        disabled
                                        class="flex-1 flex items-center justify-center gap-2 py-2 px-3 text-sm font-medium text-gray-400 border border-gray-200 rounded-lg cursor-not-allowed bg-gray-50"
                                    >
                                        <x-lucide-message-square class="h-4 w-4" />
                                        Message
                                    </button>
                                @endif
                            </div>
                            @if (! $hasCodi)
                                <p class="flex items-center gap-1.5 text-xs text-amber-600 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                                    <x-lucide-alert-triangle class="h-3.5 w-3.5 flex-shrink-0" />
                                    No CODI member has taken this case yet. Messaging will be available once a CODI member takes it.
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div x-show="!anyVisible" x-cloak class="bg-white rounded-xl border-2 border-dashed p-12 text-center" style="border-color: var(--card-outline)">
                <div class="max-w-sm mx-auto">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <x-lucide-folder-open class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 mb-2">No Matching Complaints</h3>
                    <p class="text-gray-500 text-sm mb-6">Try adjusting your search or filters.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endif
</x-layouts.complainant>
