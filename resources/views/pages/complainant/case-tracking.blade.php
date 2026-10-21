{{-- Port of frontend/src/components/case/CaseTracking.tsx (opened by CaseTrackingPage) --}}
@php
    $complaint = $complaint ?? null;

    $fmt = function ($value, string $format = 'F j, Y') {
        if (! $value) {
            return null;
        }
        try {
            return \Illuminate\Support\Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return null;
        }
    };

    $statusKey = strtolower((string) data_get($complaint, 'status', ''));
    $assigned = (bool) (data_get($complaint, 'assigned_representative_id') || data_get($complaint, 'assigned_to_name'));
    $terminal = in_array($statusKey, ['resolved', 'dismissed', 'closed'], true);
    $investigating = in_array($statusKey, ['inprogress', 'in_progress', 'investigating', 'ongoing', 'awaiting_response', 'under_deliberation'], true);
    $accepted = $assigned || in_array($statusKey, ['validated', 'under_review', 'assigned', 'accepted'], true);
    $step = $terminal ? 4 : ($investigating ? 3 : ($accepted ? 2 : 1));
    $progress = [1 => 25, 2 => 50, 3 => 75, 4 => 100][$step] ?? 25;
    $trackerLabel = match (true) {
        $step === 4 && $statusKey === 'dismissed' => 'Dismissed',
        $step === 4 && $statusKey === 'closed' => 'Closed',
        $step === 4 => 'Decision Already Made',
        $step === 3 => 'Investigating',
        $step === 2 => 'Accepted',
        default => 'Submitted',
    };
    $statusCfg = match ($statusKey) {
        'submitted', 'pending' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-[#1D9E75]'],
        'validated' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'dot' => 'bg-blue-500'],
        'investigating', 'inprogress', 'in_progress' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
        'under_review' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-200', 'dot' => 'bg-sky-500'],
        'resolved' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-green-500'],
        'dismissed' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'dot' => 'bg-red-500'],
        default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200', 'dot' => 'bg-gray-400'],
    };
    $sensitive = in_array(strtolower((string) data_get($complaint, 'type', '')), ['sexual_harassment', 'gender_based_sexual_harassment', 'gbvh', 'gbv'], true);
    $handlerName = $sensitive && $assigned ? 'CODI member' : (data_get($complaint, 'assigned_to_name') ?: ($assigned ? 'CODI member' : 'Waiting for a CODI member'));
    $filed = data_get($complaint, 'filed_at') ?: data_get($complaint, 'created_at');
    $updated = data_get($complaint, 'updated_at') ?: $filed;
    $lastUpdate = $updated ? \Illuminate\Support\Carbon::parse($updated) : now();
    $daysSince = (int) $lastUpdate->diffInDays(now());
    $followRequested = (bool) data_get($complaint, 'follow_up_requested');
    $availableOn = $lastUpdate->copy()->addDays(3);
    $canFollow = ! $terminal && ! $followRequested && $daysSince >= 3;
    $typeLabel = \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) data_get($complaint, 'type', 'other')));
    $vicinity = (string) data_get($complaint, 'location_vicinity', '');
    $vicinityLabel = match ($vicinity) {
        'inside' => 'Inside College Vicinity',
        'outside' => 'Outside College Vicinity',
        'online' => 'Online / Digital Platform',
        default => $vicinity !== '' && $vicinity !== 'N/A' ? $vicinity : 'Not specified',
    };
    $incidentTime = data_get($complaint, 'incident_time');
    $timeLabel = match ($incidentTime) {
        'AM' => 'Morning (12:00 AM – 11:59 AM)',
        'PM' => 'Afternoon/Evening (12:00 PM – 11:59 PM)',
        default => $incidentTime,
    };
    $degree = data_get($complaint, 'harassment_degree');
    $degreeLabel = match ($degree) {
        'light' => 'Light',
        'severe' => 'Severe',
        'grave' => 'Grave',
        default => $degree ? \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $degree)) : null,
    };
    $description = (string) data_get($complaint, 'description', '');
    $statement = (string) (data_get($complaint, 'statement_of_facts') ?: '');
    $showStatement = $statement !== '' && trim($statement) !== trim($description);
    $respondentName = data_get($complaint, 'respondent_name');
    $respondentAddress = (string) data_get($complaint, 'respondent_address', '');
    $showRespondent = (bool) $respondentName || $respondentAddress !== '';
    $estimate = null;
    if (! $terminal && $filed) {
        $filedAt = \Illuminate\Support\Carbon::parse($filed);
        $estimate = match ($step) {
            1 => ['next' => 'Accepted', 'description' => 'Your complaint is in the DEIU queue. A CODI member will accept it and start moving the case forward.', 'when' => $filedAt->copy()->addDays(3), 'timeframe' => 'Usually within 3 days of filing'],
            2 => ['next' => 'Investigating', 'description' => 'Your complaint has been accepted. Investigation typically begins shortly after a CODI member reviews the file.', 'when' => $filedAt->copy()->addDays(10), 'timeframe' => 'Usually within 7–10 days of filing'],
            default => ['next' => 'Decision', 'description' => 'Investigation is underway. The CODI will gather evidence and prepare a report for the Disciplining Authority.', 'when' => $filedAt->copy()->addDays(45), 'timeframe' => 'Investigation typically takes 30–45 days'],
        };
    }
    $history = collect();
    $activities = collect();
    if ($complaint && data_get($complaint, 'id')) {
        try {
            if (is_object($complaint) && method_exists($complaint, 'statusHistory')) {
                $history = $complaint->statusHistory()->orderByDesc('created_at')->get();
            }
        } catch (\Throwable $e) {
            $history = collect();
        }
        try {
            $activities = \Illuminate\Support\Facades\DB::table('case_activities')
                ->where('complaint_id', data_get($complaint, 'id'))
                ->where('is_deleted', false)
                ->orderByDesc('created_at')
                ->get();
        } catch (\Throwable $e) {
            $activities = collect();
        }
    }
    $storyTypes = ['document_review', 'evidence_collection', 'interview', 'investigation', 'report_preparation', 'deliberation'];
    $rating = (int) data_get($complaint, 'handling_feedback_rating', 0);
    $ratingLabels = ['Poor', 'Fair', 'Good', 'Very good', 'Excellent'];
    $trackerSteps = ['Submitted', 'Accepted', 'Investigating', 'Decision'];
@endphp
<x-layouts.complainant>
@if (! $complaint)
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
            <x-lucide-shield class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="text-base font-semibold text-gray-900 mb-1">Case not found</h3>
        <p class="text-sm text-gray-500">This complaint could not be located. It may have been removed or the ID is incorrect.</p>
    </div>
@else
    <div class="space-y-3" x-data="{ tab: 'timeline', order: 'latest', rating: {{ $rating }}, hover: 0, comment: @js((string) data_get($complaint, 'handling_feedback_comment', '')), expanded: false }">
        <div class="rounded-2xl border {{ $statusCfg['border'] }} {{ $statusCfg['bg'] }} p-5">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <x-lucide-shield class="h-5 w-5 text-[#1D9E75]" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-0.5">Case ID</p>
                        <p class="text-sm font-mono font-semibold text-gray-700 break-all">{{ data_get($complaint, 'case_number') ?: data_get($complaint, 'id') }}</p>
                        <h2 class="text-lg font-bold text-gray-900 mt-1 leading-tight">{{ data_get($complaint, 'title') ?: 'Untitled Case' }}</h2>
                        <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1.5">
                            <x-lucide-calendar class="h-3.5 w-3.5" />
                            Filed {{ $fmt($filed) ?: '—' }}
                        </p>
                        <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1.5">
                            <x-lucide-clock class="h-3.5 w-3.5" />
                            Last updated {{ $fmt($updated, 'F j, Y · g:i A') ?: '—' }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-row sm:flex-col items-start sm:items-end gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $statusCfg['bg'] }} {{ $statusCfg['text'] }} {{ $statusCfg['border'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusCfg['dot'] }}"></span>
                        {{ $trackerLabel }}
                    </span>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-xs font-medium text-gray-500 mb-2">Complaint tracker</p>
                <div>
                    <div class="flex items-center">
                        @foreach ($trackerSteps as $index => $label)
                            @php
                                $n = $index + 1;
                                $complete = $n < $step || $step === count($trackerSteps);
                                $active = $n === $step && $step < count($trackerSteps);
                            @endphp
                            <div class="flex flex-col items-center min-w-0">
                                <div class="rounded-full flex items-center justify-center font-bold border-2 w-6 h-6 {{ $complete ? 'bg-[#1D9E75] border-[#1D9E75] text-white' : ($active ? 'bg-white border-[#1D9E75] text-[#1D9E75]' : 'bg-white border-gray-300 text-gray-400') }}">
                                    @if ($complete)
                                        <x-lucide-circle-check class="h-3.5 w-3.5" />
                                    @else
                                        <span class="text-[10px]">{{ $n }}</span>
                                    @endif
                                </div>
                                <span class="text-[10px] mt-1.5 font-medium text-center leading-tight {{ $active ? 'text-[#1D9E75]' : ($complete ? 'text-gray-700' : 'text-gray-400') }}">{{ $label }}</span>
                            </div>
                            @if ($index < count($trackerSteps) - 1)
                                <div class="flex-1 h-0.5 mx-1.5 mb-5 {{ $n < $step ? 'bg-[#1D9E75]' : 'bg-gray-200' }}"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-between items-center mt-3 text-xs text-gray-500">
                    <span>Progress</span>
                    <span class="font-semibold text-gray-700">{{ $progress }}% Complete</span>
                </div>
                <div class="w-full bg-white/60 rounded-full h-2 mt-1 border border-gray-200">
                    <div class="h-2 rounded-full transition-all duration-700 {{ $statusKey === 'dismissed' ? 'bg-red-400' : 'bg-[#1D9E75]' }}" style="width: {{ $progress }}%"></div>
                </div>
            </div>

            @if ($assigned && ! $terminal)
                <div class="mt-3 flex items-start gap-2.5 p-3 bg-white/80 border border-blue-200 rounded-xl">
                    <x-lucide-circle-check class="h-4 w-4 text-blue-600 flex-shrink-0 mt-0.5" />
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-900">Your complaint has been accepted</p>
                        <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">
                            A CODI member has taken this case
                            @if ($fmt(data_get($complaint, 'accepted_at')))
                                on {{ $fmt(data_get($complaint, 'accepted_at')) }}
                            @endif
                            and will update you as it moves forward.
                        </p>
                    </div>
                </div>
            @endif

            @if ($estimate)
                <div class="mt-3 flex items-start gap-2.5 p-3 bg-white/80 border border-[#1D9E75]/20 rounded-xl">
                    <x-lucide-info class="h-4 w-4 text-[#1D9E75] flex-shrink-0 mt-0.5" />
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-900">Expected next step: {{ $estimate['next'] }}</p>
                        <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">{{ $estimate['description'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $estimate['timeframe'] }} · Estimated by
                            <span class="font-medium text-gray-700">{{ $estimate['when']->format('F j, Y') }}</span>
                        </p>
                    </div>
                </div>
            @endif

            <div class="mt-3 flex items-start gap-2 p-2.5 bg-white/70 border border-white rounded-xl text-xs text-gray-600">
                <x-lucide-shield class="h-3.5 w-3.5 text-[#1D9E75] flex-shrink-0 mt-0.5" />
                <span>All submitted reports are kept confidential. This complaint is filed under the <strong>Safe Spaces Act (RA 11313)</strong> and/or the <strong>Anti-Sexual Harassment Act (RA 7877)</strong>. Anti-retaliation protections apply to all parties.</span>
            </div>

            @if ($sensitive && $assigned)
                <div class="mt-3 flex items-start gap-2 p-2.5 bg-white/70 border border-white rounded-xl text-xs text-gray-600">
                    <x-lucide-user class="h-3.5 w-3.5 text-[#1D9E75] flex-shrink-0 mt-0.5" />
                    <span>For your protection, CODI member names are not shown on sensitive cases. Use Request Follow-Up or contact the DEIU office to reach the CODI member on your case.</span>
                </div>
            @endif
        </div>

        @if ($terminal)
            <div class="rounded-2xl border border-[#1D9E75]/25 bg-gradient-to-r from-emerald-50 to-green-50 p-4 sm:p-5">
                <div class="flex items-start gap-3 mb-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-white shadow-sm">
                        <x-lucide-message-square-heart class="h-5 w-5 text-[#1D9E75]" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $rating ? 'Your rating of how this was handled' : 'How was this complaint handled?' }}</h3>
                        <p class="mt-1 text-xs text-gray-600 leading-relaxed">Rate the complaint process after resolution. Your feedback is confidential and is not shared with the respondent.</p>
                    </div>
                </div>
                <form method="POST" action="" class="space-y-3">
                    @csrf
                    <input type="hidden" name="handling_feedback_rating" :value="rating">
                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach ([1, 2, 3, 4, 5] as $value)
                            <button type="button" @click="rating = {{ $value }}" @mouseenter="hover = {{ $value }}" @mouseleave="hover = 0" class="rounded-lg p-1 transition-transform hover:scale-110" aria-label="{{ $value }} star{{ $value === 1 ? '' : 's' }} — {{ $ratingLabels[$value - 1] }}">
                                <x-lucide-star class="h-7 w-7" x-bind:class="{{ $value }} <= (hover || rating) ? 'fill-amber-400 text-amber-400' : 'text-gray-300'" />
                            </button>
                        @endforeach
                        <span x-show="(hover || rating) > 0" class="ml-1 text-xs font-medium text-gray-700" x-text="['Poor','Fair','Good','Very good','Excellent'][(hover || rating) - 1]"></span>
                    </div>
                    <textarea name="handling_feedback_comment" x-model="comment" maxlength="500" placeholder="Optional: What went well, or what could the complaint system improve?" class="min-h-[88px] w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm"></textarea>
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] text-gray-400" x-text="comment.length + '/500'"></p>
                        <button type="submit" class="bg-[#1D9E75] hover:bg-[#178F65] text-white text-sm font-medium px-4 py-2 rounded-md" :disabled="rating < 1">{{ $rating ? 'Update feedback' : 'Submit feedback' }}</button>
                    </div>
                </form>
            </div>
        @endif

        @unless ($terminal)
            <div class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-4 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-200 bg-white shadow-sm">
                            <x-lucide-bell class="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Request Follow-Up</h3>
                            @if ($followRequested)
                                <p class="mt-1 text-xs text-amber-800">
                                    Follow-up requested
                                    @if ($fmt(data_get($complaint, 'follow_up_requested_at')))
                                        on {{ $fmt(data_get($complaint, 'follow_up_requested_at')) }}
                                    @endif
                                    . A CODI member will review your request.
                                </p>
                            @else
                                <p class="mt-1 text-xs text-gray-600">
                                    @if ($canFollow)
                                        No update has been recorded for {{ $daysSince }} day{{ $daysSince === 1 ? '' : 's' }}. You may ask the office to check on your case.
                                    @else
                                        Follow-up will be available after {{ max(0, 3 - $daysSince) }} more {{ max(0, 3 - $daysSince) === 1 ? 'day' : 'days' }} without an update (3-day waiting period from last case activity).
                                    @endif
                                </p>
                                @unless ($canFollow)
                                    <p class="mt-1 text-[11px] text-gray-500">
                                        Follow-up unlocks on <span class="font-medium text-gray-700">{{ $availableOn->format('F j, Y') }}</span> if no update is recorded (3-day waiting period from last activity).
                                    </p>
                                @endunless
                            @endif
                        </div>
                    </div>
                    <div class="shrink-0 sm:text-right">
                        @if ($followRequested)
                            <span class="inline-flex items-center rounded-full border border-amber-300 bg-white px-2.5 py-0.5 text-xs font-semibold text-amber-800">Follow-Up Pending</span>
                        @else
                            <form method="POST" action="">
                                @csrf
                                <input type="hidden" name="follow_up_requested" value="1">
                                <button type="submit" @disabled(! $canFollow) class="w-full sm:w-auto rounded-md px-4 py-2 text-sm font-medium {{ $canFollow ? 'bg-amber-600 text-white hover:bg-amber-700' : 'bg-gray-200 text-gray-500' }}">
                                    @if ($canFollow)
                                        Request Follow-Up
                                    @elseif (max(0, 3 - $daysSince) > 0)
                                        Available {{ $availableOn->format('M j') }}
                                    @else
                                        Not Available Yet
                                    @endif
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endunless

        <div class="w-full">
            <div class="grid w-full grid-cols-3 bg-gray-100 rounded-xl p-1" role="tablist">
                <button type="button" @click="tab = 'timeline'" class="rounded-lg text-sm py-1.5" :class="tab === 'timeline' ? 'bg-white shadow-sm font-medium' : 'text-gray-600'">Timeline</button>
                <button type="button" @click="tab = 'activities'" class="rounded-lg text-sm py-1.5" :class="tab === 'activities' ? 'bg-white shadow-sm font-medium' : 'text-gray-600'">Activities</button>
                <button type="button" @click="tab = 'details'" class="rounded-lg text-sm py-1.5" :class="tab === 'details' ? 'bg-white shadow-sm font-medium' : 'text-gray-600'">Details</button>
            </div>

            <div x-show="tab === 'timeline'" class="mt-4">
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 mb-1">Case Timeline</h3>
                            <p class="text-xs text-gray-500">Chronological history of your complaint's progress.</p>
                        </div>
                        <select x-model="order" class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#1D9E75]/20">
                            <option value="latest">Latest First</option>
                            <option value="oldest">Oldest First</option>
                        </select>
                    </div>
                    @php
                        $events = collect([[
                            'id' => 'filing',
                            'label' => 'Submission',
                            'labelClass' => 'bg-violet-100 text-violet-700',
                            'icon' => 'file-text',
                            'iconBg' => 'bg-violet-500 border-violet-500 text-white',
                            'description' => 'Complaint filed',
                            'actor' => data_get($complaint, 'complainant_name') ?: 'Complainant',
                            'role' => 'Complainant',
                            'details' => null,
                            'at' => $filed,
                        ]]);
                        if ($assigned) {
                            $events->push([
                                'id' => 'handler_assignment',
                                'label' => 'Accepted',
                                'labelClass' => 'bg-blue-100 text-blue-700',
                                'icon' => 'user',
                                'iconBg' => 'bg-blue-500 border-blue-500 text-white',
                                'description' => 'Case taken by a CODI member',
                                'actor' => $sensitive ? 'CODI member' : ($handlerName ?: 'CODI member'),
                                'role' => 'CODI member',
                                'details' => null,
                                'at' => data_get($complaint, 'accepted_at') ?: data_get($complaint, 'assigned_at'),
                            ]);
                        }
                        foreach ($history as $entry) {
                            $events->push([
                                'id' => 'status_'.$entry->id,
                                'label' => 'Status Update',
                                'labelClass' => 'bg-amber-100 text-amber-800',
                                'icon' => 'bell',
                                'iconBg' => 'bg-amber-500 border-amber-500 text-white',
                                'description' => $entry->notes ?: ('Status updated to '.\Illuminate\Support\Str::headline((string) $entry->status)),
                                'actor' => $entry->updated_by_name ?: 'CODI member',
                                'role' => null,
                                'details' => $entry->notes,
                                'at' => $entry->created_at,
                            ]);
                        }
                        $events = $events->sortByDesc(fn ($event) => $event['at'] ? \Illuminate\Support\Carbon::parse($event['at'])->timestamp : 0)->values();
                    @endphp
                    <div class="relative" :class="order === 'oldest' ? 'flex flex-col-reverse' : ''">
                        @foreach ($events as $index => $event)
                            <div class="relative flex items-start gap-4 pb-7 rounded-xl px-3 py-3 -mx-3 {{ $index === 0 ? 'bg-emerald-50/70 border border-[#1D9E75]/30 ring-1 ring-emerald-100 shadow-sm' : '' }}">
                                @if ($index < $events->count() - 1)
                                    <div class="absolute left-[30px] top-11 w-0.5 bottom-0 {{ $index === 0 ? 'bg-emerald-100' : 'bg-gray-100' }}"></div>
                                @endif
                                <div class="relative flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center shadow-sm border-2 z-10 {{ $event['iconBg'] }}">
                                    <x-dynamic-component :component="'lucide-'.$event['icon']" class="h-4 w-4" />
                                    @if ($index === 0)
                                        <span class="absolute -top-0.5 -right-0.5 h-3 w-3 rounded-full bg-red-500 ring-2 ring-white"></span>
                                    @endif
                                </div>
                                <div class="flex-grow min-w-0 pt-0.5">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $event['labelClass'] }}">{{ $event['label'] }}</span>
                                        @if ($index === 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#1D9E75] text-white">Latest</span>
                                        @endif
                                        <time class="text-xs text-gray-400 ml-auto flex-shrink-0">{{ $fmt($event['at'], 'M j, Y · g:i A') ?: '—' }}</time>
                                    </div>
                                    <h4 class="text-sm font-semibold mb-0.5 text-gray-900">{{ $event['description'] }}</h4>
                                    <p class="text-xs text-gray-500 mb-1">By: <span class="font-medium">{{ $event['actor'] }}</span>@if ($event['role']) <span class="text-gray-400 ml-1">({{ $event['role'] }})</span>@endif</p>
                                    @if ($event['details'])
                                        <p class="text-xs text-gray-600 leading-relaxed rounded-lg px-3 py-2 mt-1 border bg-gray-50 border-gray-100">{{ $event['details'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div x-show="tab === 'activities'" x-cloak class="mt-4">
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-1">Investigation Activities</h3>
                    <p class="text-xs text-gray-500 mb-5">Detailed log of all activities conducted on your case.</p>
                    @php
                        $story = $activities->filter(fn ($activity) => in_array($activity->activity_type, $storyTypes, true));
                        $audit = $activities->reject(fn ($activity) => in_array($activity->activity_type, $storyTypes, true));
                        $typeColors = [
                            'document_review' => 'bg-blue-50 border-blue-200 text-blue-700',
                            'evidence_collection' => 'bg-amber-50 border-amber-200 text-amber-700',
                            'interview' => 'bg-purple-50 border-purple-200 text-purple-700',
                            'deliberation' => 'bg-green-50 border-green-200 text-green-700',
                        ];
                    @endphp
                    @if ($story->isNotEmpty())
                        <h4 class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide">Case Progress</h4>
                        <div class="space-y-3 mb-6">
                            @foreach ($story as $activity)
                                <div class="flex gap-3 p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors">
                                    <div class="flex-shrink-0 mt-0.5"><div class="w-2 h-2 rounded-full bg-[#1D9E75] mt-1.5"></div></div>
                                    <div class="flex-grow min-w-0">
                                        <div class="flex flex-wrap items-start justify-between gap-1 mb-1">
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full border {{ $typeColors[$activity->activity_type] ?? 'bg-gray-50 border-gray-200 text-gray-700' }}">{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $activity->activity_type)) }}</span>
                                            <time class="text-xs text-gray-400">{{ $fmt($activity->created_at, 'M j, Y · g:i:s A') }}</time>
                                        </div>
                                        <p class="text-sm text-gray-700 font-medium mt-1">{{ $activity->description }}</p>
                                        <p class="text-xs text-gray-500 mt-1">By: <span class="font-medium">{{ $activity->performed_by_name ?: 'CODI member' }}</span></p>
                                        @if ($activity->findings)
                                            <div class="mt-2 bg-[#1D9E75]/5 border-l-4 border-[#1D9E75] rounded-r-lg p-3">
                                                <p class="text-xs font-semibold text-[#178F65] mb-0.5">Key Findings:</p>
                                                <p class="text-xs text-gray-700">{{ $activity->findings }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if ($audit->isNotEmpty())
                        <h4 class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide">System & Administrative Actions</h4>
                        <div class="space-y-3">
                            @foreach ($audit as $activity)
                                <div class="flex gap-3 p-3 border border-gray-200 rounded-lg bg-gray-50">
                                    <div class="flex-shrink-0 mt-0.5"><div class="w-1.5 h-1.5 rounded-full bg-gray-400 mt-1.5"></div></div>
                                    <div class="flex-grow min-w-0">
                                        <div class="flex flex-wrap items-start justify-between gap-1 mb-1">
                                            <span class="text-xs font-medium text-gray-600">{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $activity->activity_type)) }}</span>
                                            <time class="text-xs text-gray-400">{{ $fmt($activity->created_at, 'M j, Y · g:i:s A') }}</time>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">{{ $activity->description }}</p>
                                        <p class="text-xs text-gray-500 mt-1">By: <span class="font-medium">{{ $activity->performed_by_name ?: $activity->performed_by ?: 'CODI member' }}</span></p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if ($story->isEmpty() && $audit->isEmpty())
                        <div class="text-center py-10">
                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <x-lucide-file-text class="h-5 w-5 text-gray-400" />
                            </div>
                            <p class="text-sm font-medium text-gray-700">No activities yet</p>
                            <p class="text-xs text-gray-500 mt-1">Activities will appear here as the case progresses.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div x-show="tab === 'details'" x-cloak class="mt-4 space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="bg-white border border-gray-200 rounded-xl p-3">
                        <p class="text-xs text-gray-400 mb-1">Type</p>
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $typeLabel }}</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-3">
                        <p class="text-xs text-gray-400 mb-1">Filed</p>
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $fmt($filed, 'M j, Y') ?: '—' }}</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-3">
                        <p class="text-xs text-gray-400 mb-1">CODI member</p>
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $handlerName }}</p>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <x-lucide-calendar class="h-4 w-4 text-[#1D9E75]" />
                        Incident Details
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <p class="text-xs text-gray-400 mb-0.5">Complaint Title</p>
                            <p class="text-sm font-medium text-gray-800">{{ data_get($complaint, 'title') ?: 'Not specified' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Incident Date</p>
                            <p class="text-sm font-medium text-gray-800">{{ $fmt(data_get($complaint, 'incident_date')) ?: 'Not specified' }}</p>
                        </div>
                        @if ($timeLabel)
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Incident Time</p>
                                <p class="text-sm font-medium text-gray-800">{{ $timeLabel }}</p>
                            </div>
                        @endif
                        @if (data_get($complaint, 'type') === 'sexual_harassment' && $degreeLabel)
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Degree of Harassment</p>
                                <p class="text-sm font-medium text-gray-800">{{ $degreeLabel }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">{{ $vicinity === 'online' ? 'Platform' : 'Location' }}</p>
                            <p class="text-sm font-medium text-gray-800">{{ data_get($complaint, 'incident_location') ?: 'Not specified' }}</p>
                        </div>
                        @if (data_get($complaint, 'map_address') && $vicinity !== 'online')
                            <div class="col-span-2">
                                <p class="text-xs text-gray-400 mb-0.5">Exact Location (Map)</p>
                                <p class="text-sm font-medium text-gray-800">{{ data_get($complaint, 'map_address') }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Where it Happened</p>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium border {{ $vicinity === 'online' ? 'bg-purple-50 text-purple-700 border-purple-200' : ($vicinity === 'inside' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-orange-50 text-orange-700 border-orange-200') }}">{{ $vicinityLabel }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <x-lucide-file-text class="h-4 w-4 text-[#1D9E75]" />
                        Complaint Description
                    </h3>
                    @if ($description !== '')
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Description</p>
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $description }}</p>
                        </div>
                    @endif
                    @if ($showStatement)
                        <div class="mt-3">
                            <p class="text-xs text-gray-400 mb-1">Statement of Facts</p>
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $statement }}</p>
                        </div>
                    @endif
                    @if ($description === '' && ! $showStatement)
                        <p class="text-sm text-gray-500">No description provided.</p>
                    @endif
                </div>

                @if ($showRespondent)
                    <div class="bg-white border border-gray-200 rounded-2xl p-5">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <x-lucide-user class="h-4 w-4 text-[#1D9E75]" />
                            Respondent Information
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Name</p>
                                <p class="text-sm font-medium text-gray-800">{{ $respondentName ?: 'Not disclosed' }}</p>
                            </div>
                            @if ($respondentAddress !== '')
                                <div>
                                    <p class="text-xs text-gray-400 mb-0.5">Address / Description</p>
                                    <p class="text-sm font-medium text-gray-800">{{ $respondentAddress }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="mt-3 p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-700">
                            <strong>Note:</strong> Under the Safe Spaces Act (RA 11313), the respondent is notified of this complaint and the complainant's identity. Anti-retaliation provisions protect both parties.
                        </div>
                    </div>
                @endif

                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <x-lucide-shield class="h-4 w-4 text-[#1D9E75]" />
                        Assigned Personnel
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">CODI member</p>
                            @if ($assigned)
                                <p class="text-sm font-semibold text-gray-900">{{ $sensitive ? 'CODI member' : ($handlerName ?: 'CODI member') }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">Committee on Decorum and Investigation (CODI)</p>
                                @if ($sensitive)
                                    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">For your protection, CODI member names are not shown on sensitive cases. Use Request Follow-Up or contact the DEIU office to reach the CODI member on your case.</p>
                                @endif
                            @else
                                <p class="text-sm font-medium text-amber-600">Not yet assigned — pending hearing notification</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Confidentiality Level</p>
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold">{{ \Illuminate\Support\Str::headline((string) (data_get($complaint, 'confidentiality_level') ?: 'Confidential')) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <x-lucide-clock class="h-4 w-4 text-[#1D9E75]" />
                        Key Dates & Deadlines
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Filed On</p>
                            <p class="text-sm font-medium text-gray-800">{{ $fmt($filed, 'F j, Y · g:i A') ?: '—' }}</p>
                        </div>
                        @if (data_get($complaint, 'response_due_at'))
                            <div>
                                <p class="text-xs text-gray-400 mb-0.5">Response Deadline</p>
                                <p class="text-sm font-medium text-gray-800">{{ $fmt(data_get($complaint, 'response_due_at')) }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($filedNotice = session('filed_case'))
        <div
            x-data="{ open: true, copied: false }"
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" role="dialog" aria-labelledby="filed-case-title">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
                    <x-lucide-circle-check class="h-8 w-8 text-[#1D9E75]" />
                </div>
                <h2 id="filed-case-title" class="text-center text-xl font-bold text-gray-900">Complaint filed</h2>
                <p class="mt-1 text-center text-sm text-gray-500">Keep this Case ID. Use it to track your report with the DEIU.</p>
                <p class="mt-5 text-center text-xs font-semibold uppercase tracking-widest text-gray-400">Case ID</p>
                <p class="mt-1 text-center font-mono text-2xl font-bold tracking-wide text-[#1D9E75]">{{ $filedNotice['case_number'] }}</p>
                <button
                    type="button"
                    class="mt-5 w-full rounded-lg border border-[#1D9E75] py-2.5 text-sm font-medium text-[#1D9E75] hover:bg-emerald-50"
                    @click="navigator.clipboard.writeText(@js($filedNotice['case_number'])); copied = true"
                >
                    <span x-text="copied ? 'Case ID copied' : 'Copy Case ID'"></span>
                </button>
                <button type="button" @click="open = false" class="mt-2 w-full rounded-lg bg-[#1D9E75] py-2.5 text-sm font-semibold text-white hover:bg-[#178F65]">
                    View this case
                </button>
                <a href="/complaints" class="mt-3 block text-center text-sm font-medium text-gray-500 hover:text-[#1D9E75]">Go to My Cases</a>
            </div>
        </div>
    @endif
@endif
</x-layouts.complainant>
