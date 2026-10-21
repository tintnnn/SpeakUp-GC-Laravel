{{-- Port of frontend/src/views/admin/AdminReportsPage.tsx (queue chrome) --}}
@php
    $complaints = $complaints ?? collect();
    $user = auth()->user();
    $rep = $user?->representative;
    $isAdmin = $user && ($user->isPlatformAdmin() || $rep?->role === 'admin');
    $isCODI = $rep && in_array($rep->role, ['handler', 'codi'], true);
    $isHandler = $isCODI;
    $repId = $rep?->id;
    $statusParam = request('status');
    $initialTab = in_array($statusParam, ['all', 'active', 'unassigned', 'investigating', 'resolved', 'needs-attention'], true) ? $statusParam : 'all';
    $onlyMine = request('assigned') === 'me';

    $statusLabel = function (string $status): string {
        return match (strtolower($status)) {
            'pending', 'submitted' => 'Pending',
            'inprogress', 'investigating', 'awaiting_response', 'under_deliberation', 'validated' => 'Investigating',
            'resolved' => 'Decision Already Made',
            'dismissed' => 'Closed',
            default => $status !== '' ? $status : 'Unknown',
        };
    };
    $statusClass = function (string $label): string {
        return match ($label) {
            'Pending' => 'bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 rounded-full text-xs',
            'Investigating' => 'bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded-full text-xs',
            'Decision Already Made' => 'bg-[#1D9E75]/10 text-[#1D9E75] border border-[#1D9E75]/20 px-3 py-1 rounded-full text-xs',
            'Closed' => 'bg-gray-50 text-gray-700 border border-gray-200 px-3 py-1 rounded-full text-xs',
            default => 'bg-gray-100 text-gray-800',
        };
    };

    $complaintIds = $complaints->pluck('id')->filter()->values();
    $filesByCase = $complaintIds->isEmpty()
        ? collect()
        : \Illuminate\Support\Facades\DB::table('attachments')
            ->where('attachable_type', \App\Models\Complaint::class)
            ->whereIn('attachable_id', $complaintIds)
            ->get()
            ->groupBy('attachable_id');
    $notesByCase = $complaintIds->isEmpty()
        ? collect()
        : \Illuminate\Support\Facades\DB::table('case_notes')
            ->whereIn('complaint_id', $complaintIds)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('complaint_id');
    $activityByCase = $complaintIds->isEmpty()
        ? collect()
        : \Illuminate\Support\Facades\DB::table('case_activities')
            ->where('is_deleted', false)
            ->whereIn('complaint_id', $complaintIds)
            ->orderBy('created_at')
            ->get()
            ->groupBy('complaint_id');
    $categoryLabels = [
        'sexual_harassment' => 'Sexual Harassment',
        'gender_based_harassment' => 'Gender-Based Harassment',
        'discrimination' => 'Gender-Based Discrimination',
        'bullying' => 'Bullying/Harassment',
        'online_harassment' => 'Online Sexual Harassment',
        'other' => 'Other',
    ];
    $pretty = fn ($value) => $value ? ucwords(str_replace('_', ' ', (string) $value)) : '';

    $rows = [];
    foreach ($complaints as $complaint) {
        $status = (string) (data_get($complaint, 'status') ?: 'pending');
        $statusKey = strtolower($status);
        $filed = data_get($complaint, 'filed_at') ?: data_get($complaint, 'created_at');
        $updated = data_get($complaint, 'updated_at') ?: $filed;
        $filedAt = $filed ? \Illuminate\Support\Carbon::parse($filed) : null;
        $updatedAt = $updated ? \Illuminate\Support\Carbon::parse($updated) : null;
        $daysOpen = $filedAt ? (int) $filedAt->diffInDays(now()) : 0;
        $daysSince = $updatedAt ? (int) $updatedAt->diffInDays(now()) : 0;
        $escalation = (int) (data_get($complaint, 'escalation_level') ?: 0);
        $followUp = (bool) data_get($complaint, 'follow_up_requested');
        $assignedId = data_get($complaint, 'assigned_representative_id') ?: data_get($complaint, 'assigned_user_id');
        $isAnonymous = (bool) data_get($complaint, 'is_anonymous');
        $sealed = data_get($complaint, 'sealed_identity');
        if (is_string($sealed)) {
            $decoded = json_decode($sealed, true);
            $sealed = is_array($decoded) ? $decoded : [];
        }
        $sealedName = is_array($sealed) ? ($sealed['name'] ?? null) : null;
        $name = data_get($complaint, 'complainant_name') ?: 'Unknown';
        $title = data_get($complaint, 'title') ?: 'No Title';
        $description = (string) (data_get($complaint, 'description') ?: '');
        $location = data_get($complaint, 'incident_location') ?: 'No location';
        $id = data_get($complaint, 'id');
        $caseActivities = $activityByCase->get($id) ?? collect();
        $activity = $caseActivities->count();
        $rows[] = [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'statusKey' => $statusKey,
            'label' => $statusLabel($status),
            'badge' => $statusClass($statusLabel($status)),
            'location' => $location,
            'anonymous' => $isAnonymous || $name === 'Anonymous',
            'sealed' => (bool) $sealedName,
            'complainant' => ($isAnonymous || $name === 'Anonymous') ? ($sealedName ? 'Anonymous · sealed ID' : 'Anonymous') : $name,
            'assignedName' => data_get($complaint, 'assigned_to_name') ?: 'Unassigned',
            'assignedRole' => data_get($complaint, 'assigned_to_role'),
            'assigned' => (bool) $assignedId,
            'mine' => $repId && (string) $assignedId === (string) $repId,
            'pending' => in_array($statusKey, ['pending', 'submitted'], true),
            'unassigned' => ! $assignedId && in_array($statusKey, ['pending', 'submitted'], true),
            'caseNumber' => data_get($complaint, 'case_number') ?: $id,
            'category' => (string) (data_get($complaint, 'type') ?: ''),
            'escalation' => $escalation,
            'hours' => (int) (data_get($complaint, 'hours_unprocessed') ?: 0),
            'followUp' => $followUp,
            'rating' => data_get($complaint, 'handling_feedback_rating'),
            'filedLabel' => $filedAt ? $filedAt->format('M d, Y H:i') : 'N/A',
            'updatedLabel' => $updatedAt ? $updatedAt->diffForHumans() : 'N/A',
            'stale' => $daysSince >= 3,
            'daysOpen' => $daysOpen,
            'attention' => $daysSince >= 3 || $escalation > 0 || $followUp,
            'activity' => $activity,
            'timestamp' => $filedAt ? $filedAt->timestamp : 0,
            'search' => strtolower($title.' '.$description.' '.$location.' '.$name),
            'email' => data_get($complaint, 'complainant_email') ?: 'N/A',
            'categoryLabel' => $categoryLabels[$complaint->type ?? ''] ?? ($pretty(data_get($complaint, 'type')) ?: 'Not specified'),
            'incidentDate' => data_get($complaint, 'incident_date') ? \Illuminate\Support\Carbon::parse(data_get($complaint, 'incident_date'))->format('M d, Y') : 'N/A',
            'incidentTime' => $pretty(data_get($complaint, 'incident_time')),
            'degree' => $pretty(data_get($complaint, 'harassment_degree')),
            'vicinity' => match ((string) data_get($complaint, 'location_vicinity')) {
                'inside' => 'Inside college vicinity',
                'outside' => 'Outside college vicinity',
                'online' => 'Online',
                default => $pretty(data_get($complaint, 'location_vicinity')) ?: 'Not specified',
            },
            'mapAddress' => data_get($complaint, 'map_address'),
            'respondent' => data_get($complaint, 'respondent_name') ?: '',
            'respondentDetail' => data_get($complaint, 'respondent_address') ?: '',
            'witnesses' => data_get($complaint, 'witnesses') ?: '',
            'statement' => data_get($complaint, 'statement_of_facts') ?: '',
            'additional' => data_get($complaint, 'additional_info') ?: '',
            'sealedName' => $sealedName,
            'sealedContact' => is_array($sealed) ? ($sealed['contact'] ?? null) : null,
            'sealedType' => is_array($sealed) ? $pretty($sealed['type'] ?? null) : '',
            'files' => ($filesByCase->get($id) ?? collect())->map(fn ($file) => [
                'name' => $file->file_name ?: 'File',
                'url' => route('evidence.show', $file->id),
                'mime' => (string) $file->mime,
            ])->all(),
            'links' => array_values(array_filter((array) (data_get($complaint, 'evidence_external_links') ?: []))),
            'notes' => ($notesByCase->get($id) ?? collect())->map(fn ($note) => [
                'name' => $note->user_name ?: 'Staff',
                'role' => in_array($note->user_role, ['handler', 'codi'], true) ? 'CODI member' : 'Admin',
                'message' => $note->message,
                'when' => $note->created_at ? \Illuminate\Support\Carbon::parse($note->created_at)->diffForHumans() : '',
            ])->all(),
            'activities' => collect($caseActivities->map(fn ($item) => [
                'title' => $pretty($item->activity_type) ?: 'Activity',
                'description' => $item->description ?: $item->findings,
                'actor' => $item->performed_by_name ?: 'Staff',
                'at' => $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at) : null,
            ]))->when($filedAt, fn ($items) => $items->push([
                'title' => 'Case submitted',
                'description' => 'Formal complaint "'.$title.'" was filed and recorded in the system.',
                'actor' => ($isAnonymous || $name === 'Anonymous') ? 'Complainant' : $name,
                'at' => $filedAt,
            ]))->sortBy(fn ($item) => $item['at']?->getTimestamp() ?? 0)->values()->map(fn ($item) => [
                'title' => $item['title'],
                'description' => $item['description'],
                'actor' => $item['actor'],
                'when' => $item['at'] ? $item['at']->format('M d, Y · g:i A') : '',
            ])->all(),
        ];
    }

    $openRows = array_values(array_filter($rows, fn ($row) => $row['statusKey'] !== 'closed'));
    $mineRows = array_values(array_filter($rows, fn ($row) => $row['mine']));
    $stats = [
        'total' => count($rows),
        'pending' => count(array_filter($rows, fn ($row) => $row['pending'])),
        'investigating' => count(array_filter($rows, fn ($row) => $row['statusKey'] === 'inprogress')),
        'resolved' => count(array_filter($rows, fn ($row) => $row['statusKey'] === 'resolved')),
    ];
    $tabCounts = [
        'all' => count($openRows),
        'active' => $stats['pending'],
        'unassigned' => count(array_filter($rows, fn ($row) => $row['unassigned'])),
        'investigating' => $stats['investigating'],
        'resolved' => $stats['resolved'],
        'needsAttention' => count(array_filter($rows, fn ($row) => $row['attention'])),
    ];
    $handlerStats = [
        'total' => count($mineRows),
        'pending' => count(array_filter($mineRows, fn ($row) => $row['pending'])),
        'inProgress' => count(array_filter($mineRows, fn ($row) => $row['statusKey'] === 'inprogress')),
        'resolved' => count(array_filter($mineRows, fn ($row) => in_array($row['statusKey'], ['resolved', 'dismissed'], true))),
        'escalated' => count(array_filter($mineRows, fn ($row) => $row['escalation'] > 0)),
    ];
    $categories = [
        ['value' => 'sexual_harassment', 'label' => 'Sexual Harassment'],
        ['value' => 'gender_based_harassment', 'label' => 'Gender-Based Harassment'],
        ['value' => 'discrimination', 'label' => 'Gender-Based Discrimination'],
        ['value' => 'bullying', 'label' => 'Bullying/Harassment'],
        ['value' => 'online_harassment', 'label' => 'Online Sexual Harassment'],
        ['value' => 'other', 'label' => 'Other'],
    ];
    $card = 'rounded-lg border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm overflow-hidden';
    $outlineBtn = 'inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-md text-sm font-medium h-9 px-3 border border-gray-200 bg-white hover:bg-gray-50';
@endphp
<x-layouts.admin>
<div
    class="w-full space-y-8 pb-10"
    x-data="{
        search: '',
        status: 'all',
        category: 'all',
        escalation: 'all',
        tab: @js($initialTab),
        sort: 'date',
        dir: 'desc',
        selectedId: @js(request('report') ? (int) request('report') : null),
        detailTab: @js(request('tab') === 'notes' ? 'notes' : (request('tab') === 'evidence' ? 'evidence' : (request('tab') === 'activity' ? 'activity' : 'details'))),
        exportOpen: false,
        onlyMine: @js($onlyMine),
        takeId: null,
        takeNumber: '',
        takeTitle: '',
        openTake(id, number, title) {
            this.takeId = id;
            this.takeNumber = number;
            this.takeTitle = title;
        },
        statusAction: null,
        notes: '',
        decision: '',
        actionTaken: '',
        finalNotes: '',
        openStatus(id, status, label, number) {
            this.statusAction = { id, status, label, number };
            this.notes = '';
            this.decision = '';
            this.actionTaken = '';
            this.finalNotes = '';
        },
        visibleCount: {{ count($rows) }},
        rowOk(el) {
            if (!el || !el.dataset) return true;
            const d = el.dataset;
            const q = this.search.trim().toLowerCase();
            if (q && !(d.search || '').includes(q)) return false;
            if (this.tab === 'active' && d.pending !== '1') return false;
            if (this.tab === 'unassigned' && d.unassigned !== '1') return false;
            if (this.tab === 'investigating' && d.status !== 'inprogress') return false;
            if (this.tab === 'resolved' && d.status !== 'resolved') return false;
            if (this.tab === 'needs-attention' && d.attention !== '1') return false;
            if (this.tab === 'all' && d.status === 'closed') return false;
            if (this.onlyMine && d.mine !== '1') return false;
            if (this.tab === 'all' && this.status !== 'all' && d.status !== this.status.toLowerCase()) return false;
            if (this.category !== 'all' && d.category !== this.category) return false;
            if (this.escalation !== 'all' && String(d.escalation) !== String(this.escalation)) return false;
            return true;
        },
        refreshCount() {
            this.$nextTick(() => {
                const rows = this.$refs.rows ? this.$refs.rows.querySelectorAll('[data-case-row]') : [];
                this.visibleCount = [...rows].filter((el) => this.rowOk(el)).length;
            });
        },
        setSort(field) {
            if (this.sort === field) this.dir = this.dir === 'asc' ? 'desc' : 'asc';
            else { this.sort = field; this.dir = 'desc'; }
            this.applySort();
        },
        applySort() {
            const list = this.$refs.rows;
            if (!list) return;
            const rows = [...list.querySelectorAll('[data-case-row]')];
            const dir = this.dir === 'asc' ? 1 : -1;
            const field = this.sort;
            rows.sort((a, b) => {
                const av = a.dataset[field] || '';
                const bv = b.dataset[field] || '';
                if (field === 'date') return (Number(av) - Number(bv)) * dir;
                return av.localeCompare(bv) * dir;
            });
            rows.forEach((row) => list.appendChild(row));
        },
        clearFilters() {
            this.search = '';
            this.status = 'all';
            this.category = 'all';
            this.escalation = 'all';
            this.refreshCount();
        },
        init() {
            this.refreshCount();
            ['search', 'status', 'category', 'escalation', 'tab'].forEach((key) => {
                this.$watch(key, () => this.refreshCount());
            });
        }
    }"
>
    @if (! $isAdmin && $isHandler)
        <div class="mb-2 grid grid-cols-1 gap-4 {{ $handlerStats['escalated'] > 0 ? 'md:grid-cols-5' : 'md:grid-cols-4' }}">
            @foreach ([
                ['Total Cases', 'Assigned to you', $handlerStats['total'], 'file-text'],
                ['Pending Review', 'Awaiting your review', $handlerStats['pending'], 'clock'],
                ['Ongoing Investigation', 'Currently handling', $handlerStats['inProgress'], 'triangle-alert'],
                ['Decision Already Made', 'Completed cases', $handlerStats['resolved'], 'circle-check'],
            ] as $metric)
                <div class="{{ $card }}">
                    <div class="flex flex-row items-center justify-between border-b border-emerald-100/60 bg-emerald-50/30 p-6 pb-3">
                        <h3 class="text-sm font-medium text-emerald-950">{{ $metric[0] }}</h3>
                        <x-dynamic-component :component="'lucide-'.$metric[3]" class="h-4 w-4 text-[#1D9E75]" />
                    </div>
                    <div class="p-6 pt-4">
                        <div class="text-2xl font-bold tabular-nums text-emerald-950">{{ $metric[2] }}</div>
                        <p class="mt-1 text-xs text-emerald-900/55">{{ $metric[1] }}</p>
                    </div>
                </div>
            @endforeach
            @if ($handlerStats['escalated'] > 0)
                <div class="{{ $card }}">
                    <div class="flex flex-row items-center justify-between border-b border-emerald-100/60 bg-emerald-50/30 p-6 pb-3">
                        <h3 class="text-sm font-medium text-emerald-950">Escalated</h3>
                        <x-lucide-triangle-alert class="h-4 w-4 text-emerald-900/65" />
                    </div>
                    <div class="p-6 pt-4">
                        <div class="text-2xl font-bold tabular-nums text-emerald-950">{{ $handlerStats['escalated'] }}</div>
                        <p class="mt-1 text-xs text-emerald-900/55">Elevated priority</p>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">{{ $isHandler ? 'Case Management' : 'Reports' }}</p>
            <h1 class="text-xl font-bold text-gray-900">{{ $isHandler ? 'Case Queue' : 'Reports Management' }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                @if ($onlyMine)
                    Cases currently assigned to you
                @elseif ($isHandler)
                    Shared queue — view all open cases and take unassigned ones to investigate
                @else
                    View and manage all incident reports submitted by users
                @endif
            </p>
        </div>
        @unless ($isHandler)
            <button type="button" @click="exportOpen = true" class="inline-flex items-center h-9 rounded-md px-3 text-sm font-medium text-white" style="background-color: #1D9E75; color: white;">
                <x-lucide-download class="h-4 w-4 mr-1.5" />
                Export PDF
            </button>
        @endunless
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Total Reports', $stats['total'], 'file-text', 'bg-emerald-100/60 ring-emerald-200/50', 'text-[#1D9E75]'],
            ['Pending', $stats['pending'], 'clock', 'bg-emerald-100/50 ring-emerald-200/40', 'text-emerald-800/80'],
            ['Ongoing Investigation', $stats['investigating'], 'triangle-alert', 'bg-emerald-100/50 ring-emerald-200/40', 'text-emerald-900/70'],
            ['Decision Already Made', $stats['resolved'], 'circle-check', 'bg-emerald-100/60 ring-emerald-200/50', 'text-[#1D9E75]'],
        ] as $stat)
            <div class="{{ $card }}">
                <div class="p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-2xl font-bold tabular-nums text-emerald-950">{{ $stat[1] }}</p>
                            <p class="text-sm text-emerald-900/55">{{ $stat[0] }}</p>
                        </div>
                        <div class="rounded-xl p-2.5 ring-1 {{ $stat[3] }}">
                            <x-dynamic-component :component="'lucide-'.$stat[2]" class="h-7 w-7 {{ $stat[4] }}" />
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="border-b border-gray-200 dark:border-border bg-white dark:bg-card rounded-lg shadow-sm">
        <nav class="flex space-x-8 px-6 overflow-x-auto" aria-label="Status tabs">
            @foreach ([
                ['all', 'All', $tabCounts['all']],
                ['active', 'Pending', $tabCounts['active']],
                ['unassigned', 'Unassigned', $tabCounts['unassigned']],
                ['investigating', 'Investigating', $tabCounts['investigating']],
                ['resolved', 'Decision Made', $tabCounts['resolved']],
                ['needs-attention', 'Needs Attention', $tabCounts['needsAttention']],
            ] as $tab)
                <button type="button" @click="tab = '{{ $tab[0] }}'" class="py-4 px-1 border-b-2 font-medium text-sm transition-colors whitespace-nowrap" :class="tab === '{{ $tab[0] }}' ? 'border-[#1D9E75] text-[#1D9E75]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    {{ $tab[1] }}
                    <span class="ml-2 py-0.5 px-2 rounded-full text-xs font-semibold" :class="tab === '{{ $tab[0] }}' ? 'bg-[#1D9E75]/10 text-[#1D9E75]' : 'bg-gray-100 text-gray-600'">{{ $tab[2] }}</span>
                </button>
            @endforeach
        </nav>
    </div>

    <div class="{{ $card }}">
        <div class="flex flex-col space-y-1.5 border-b border-gray-100 p-6 pb-4 pt-5">
            <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                <x-lucide-filter class="h-4 w-4 text-gray-500" />
                Filters
            </h3>
        </div>
        <div class="p-6 pt-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
                <div>
                    <input x-model="search" placeholder="Search reports..." class="flex h-10 w-full rounded-md border border-emerald-100/90 dark:border-border bg-white dark:bg-card px-3 py-2 text-sm focus-visible:outline-none focus-visible:border-[#1D9E75]/40 focus-visible:ring-2 focus-visible:ring-[#1D9E75]/20">
                </div>
                <select x-model="status" class="flex h-10 w-full items-center rounded-md border border-emerald-100/90 dark:border-border bg-white dark:bg-card px-3 text-sm focus:ring-[#1D9E75]/20">
                    <option value="all">All Statuses</option>
                    <option value="submitted">Pending</option>
                    <option value="inProgress">Investigating</option>
                    <option value="resolved">Decision Already Made</option>
                </select>
                <select x-model="category" class="flex h-10 w-full items-center rounded-md border border-emerald-100/90 dark:border-border bg-white dark:bg-card px-3 text-sm focus:ring-[#1D9E75]/20">
                    <option value="all">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['value'] }}">{{ $category['label'] }}</option>
                    @endforeach
                </select>
                <select x-model="escalation" class="flex h-10 w-full items-center rounded-md border border-emerald-100/90 dark:border-border bg-white dark:bg-card px-3 text-sm focus:ring-[#1D9E75]/20">
                    <option value="all">All Escalations</option>
                    <option value="0">Normal</option>
                    <option value="1">Priority</option>
                    <option value="2">Urgent</option>
                    <option value="3">Critical</option>
                </select>
                <button type="button" @click="clearFilters()" class="{{ $outlineBtn }} border-emerald-200/90 text-emerald-900 hover:bg-emerald-50/90">Clear Filters</button>
            </div>
        </div>
    </div>

    <div class="{{ $card }}">
        <div class="flex flex-col space-y-1.5 border-b border-emerald-100/70 bg-gradient-to-r from-emerald-50/40 to-transparent p-6 pb-4 pt-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-base font-semibold text-emerald-950">Reports <span class="font-normal text-emerald-800/60">(<span x-text="visibleCount">{{ count($rows) }}</span>)</span></h3>
                <div x-show="visibleCount > 0" class="flex flex-wrap items-center gap-1.5 text-xs text-gray-500">
                    <span class="mr-1">Sort:</span>
                    @foreach (array_filter([
                        ['date', 'Date'],
                        ['title', 'Title'],
                        ['status', 'Status'],
                        ['complainant', 'Complainant'],
                        $isHandler ? null : ['handler', 'CODI member'],
                    ]) as $sort)
                        <button type="button" @click="setSort('{{ $sort[0] }}')" class="inline-flex items-center gap-1 rounded-md px-2 py-1 transition-colors" :class="sort === '{{ $sort[0] }}' ? 'bg-[#1D9E75]/10 text-[#1D9E75] font-medium' : 'hover:bg-gray-100 text-gray-600'">
                            {{ $sort[1] }}
                            <span x-show="sort !== '{{ $sort[0] }}'"><x-lucide-arrow-up-down class="h-3 w-3 opacity-30" /></span>
                            <span x-show="sort === '{{ $sort[0] }}' && dir === 'asc'" x-cloak><x-lucide-arrow-up class="h-3 w-3" /></span>
                            <span x-show="sort === '{{ $sort[0] }}' && dir === 'desc'" x-cloak><x-lucide-arrow-down class="h-3 w-3" /></span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="p-0">
            @php $complaints = $complaints ?? collect(); @endphp
            @forelse ($rows as $row)
                @if ($loop->first)
                <div class="space-y-3 p-4" x-ref="rows">
                @endif
                        <div
                            data-case-row
                            data-search="{{ $row['search'] }}"
                            data-status="{{ $row['statusKey'] }}"
                            data-pending="{{ $row['pending'] ? '1' : '0' }}"
                            data-unassigned="{{ $row['unassigned'] ? '1' : '0' }}"
                            data-attention="{{ $row['attention'] ? '1' : '0' }}"
                            data-mine="{{ $row['mine'] ? '1' : '0' }}"
                            data-category="{{ $row['category'] }}"
                            data-escalation="{{ $row['escalation'] }}"
                            data-date="{{ $row['timestamp'] }}"
                            data-title="{{ strtolower($row['title']) }}"
                            data-complainant="{{ strtolower($row['complainant']) }}"
                            data-handler="{{ strtolower($row['assignedName']) }}"
                            x-show="rowOk($el)"
                            class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-4 shadow-sm"
                        >
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 flex-1 space-y-2">
                                    <div class="flex flex-wrap items-start gap-2">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="font-semibold text-gray-900 leading-snug">{{ $row['title'] }}</h3>
                                                <span class="{{ $row['badge'] }} font-medium shrink-0">{{ $row['label'] }}</span>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-400">{{ $row['caseNumber'] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600">
                                        <span class="inline-flex items-center gap-1">
                                            <x-lucide-map-pin class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                                            {{ $row['location'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            @if ($row['anonymous'])
                                                <x-lucide-lock class="h-3.5 w-3.5 text-gray-400" />
                                                {{ $row['complainant'] }}
                                            @else
                                                <x-lucide-user class="h-3.5 w-3.5 text-gray-400" />
                                                {{ $row['complainant'] }}
                                            @endif
                                        </span>
                                        @unless ($isHandler)
                                            <span class="inline-flex flex-wrap items-center gap-1.5">
                                                <x-lucide-user class="h-3.5 w-3.5 text-[#1D9E75]" />
                                                {{ $row['assignedName'] }}
                                            </span>
                                        @endunless
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($row['mine'])
                                            <span class="border-transparent bg-[#D1FAE5] text-[#065F46] text-[10px] font-semibold px-2 py-0.5 rounded-full">Assigned to you</span>
                                        @endif
                                        @if ($row['unassigned'])
                                            <span class="bg-amber-100 text-amber-800 text-[10px] font-semibold px-2 py-0.5 rounded-full">Unassigned</span>
                                        @endif
                                        @if ($row['escalation'] > 0)
                                            <span class="bg-red-100 text-red-700 border border-red-300 text-xs font-semibold px-2 rounded-full">{{ $row['hours'] }}h escalated</span>
                                        @endif
                                        @if ($row['followUp'])
                                            <span class="bg-amber-100 text-amber-700 border border-amber-300 text-xs font-semibold px-2 rounded-full inline-flex items-center gap-1">
                                                <x-lucide-message-circle class="h-3 w-3" />
                                                Follow-Up
                                            </span>
                                        @endif
                                        @if ($row['sealed'])
                                            <span class="bg-indigo-100 text-indigo-800 text-[10px] font-semibold px-2 py-0.5 rounded-full">Sealed identity</span>
                                        @endif
                                        @if ($row['rating'])
                                            <span class="bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-semibold px-2 py-0.5 rounded-full">{{ $row['rating'] }}/5 feedback</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500">
                                        <span class="inline-flex items-center gap-1">
                                            <x-lucide-calendar class="h-3 w-3" />
                                            Filed {{ $row['filedLabel'] }}
                                        </span>
                                        <span>·</span>
                                        <span class="{{ $row['stale'] ? 'text-amber-600 font-medium' : '' }}">Updated {{ $row['updatedLabel'] }}</span>
                                        <span>·</span>
                                        <span class="{{ $row['daysOpen'] >= 10 ? 'text-red-600 font-medium' : ($row['daysOpen'] >= 5 ? 'text-amber-600 font-medium' : '') }}">{{ $row['daysOpen'] }}d open</span>
                                        <span>·</span>
                                        <span>{{ $row['activity'] }} activity</span>
                                    </div>
                                </div>
                                <div class="flex shrink-0 flex-wrap items-center gap-2 border-t border-gray-100 pt-3 lg:border-t-0 lg:border-l lg:pl-4 lg:pt-0">
                                    <button type="button" title="Quick Summary" @click="selectedId = {{ (int) $row['id'] }}; detailTab = 'details'" class="{{ $outlineBtn }} text-gray-600 hover:bg-gray-50">
                                        <x-lucide-file-text class="h-4 w-4 shrink-0" />
                                        <span>Summary</span>
                                    </button>
                                    @if ($isAdmin)
                                        <button type="button" title="Escalate Case" @click="selectedId = {{ (int) $row['id'] }}; detailTab = 'details'" class="{{ $outlineBtn }} {{ $row['escalation'] > 0 ? 'text-orange-600 hover:bg-orange-50' : 'text-gray-600 hover:bg-gray-50' }}">
                                            <x-lucide-triangle-alert class="h-4 w-4 shrink-0" />
                                            <span>Escalate</span>
                                        </button>
                                    @endif
                                    @if ($isCODI && ! $row['assigned'])
                                        <button type="button" title="Take Case" @click="openTake({{ (int) $row['id'] }}, @js($row['caseNumber']), @js($row['title']))" class="{{ $outlineBtn }} text-green-600 hover:bg-green-50 border-green-300">
                                            <x-lucide-user-plus class="h-4 w-4 shrink-0" />
                                            <span>Take Case</span>
                                        </button>
                                    @endif
                                    <button type="button" title="View Report" @click="selectedId = {{ (int) $row['id'] }}; detailTab = 'details'" class="{{ $outlineBtn }}">
                                        <x-lucide-eye class="h-4 w-4 shrink-0" />
                                        <span>View</span>
                                    </button>
                                    @if ($isCODI && $row['mine'] && $row['statusKey'] !== 'closed')
                                        <a href="{{ url('/case-chat/'.$row['id']) }}" title="Chat with Complainant" class="{{ $outlineBtn }} text-blue-600 hover:text-blue-700">
                                            <x-lucide-message-circle class="h-4 w-4 shrink-0" />
                                            <span>Chat</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                @if ($loop->last)
                </div>
                @endif
            @empty
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100/70 ring-1 ring-emerald-200/50">
                        <x-lucide-file-text class="h-7 w-7 text-[#1D9E75]/70" />
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-emerald-950">No reports found</h3>
                    <p class="text-sm text-emerald-900/55">No reports have been submitted yet.</p>
                </div>
            @endforelse
            @if ($complaints->isNotEmpty())
                <div x-show="visibleCount === 0" x-cloak class="px-6 py-12 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100/70 ring-1 ring-emerald-200/50">
                        <x-lucide-file-text class="h-7 w-7 text-[#1D9E75]/70" />
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-emerald-950">No reports found</h3>
                    <p class="text-sm text-emerald-900/55">No reports match your current filters.</p>
                </div>
            @endif
        </div>
    </div>

    <div x-show="selectedId !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="if (statusAction === null) selectedId = null" @keydown.escape.window="if (statusAction === null) selectedId = null">
        <div class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xl" role="dialog" aria-modal="true">
            <div class="flex items-start justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $isAdmin ? 'Full Report Details' : 'Case Details' }}</h2>
                    @foreach ($rows as $row)
                        <p x-show="selectedId == {{ (int) $row['id'] }}" class="mt-1 text-sm text-gray-500">
                            Case No: <span class="text-base font-semibold text-gray-900">{{ $row['caseNumber'] }}</span>
                        </p>
                    @endforeach
                </div>
                <button type="button" @click="selectedId = null" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100" aria-label="Close">
                    <x-lucide-x class="h-5 w-5" />
                </button>
            </div>
            <div class="grid grid-cols-2 gap-1 border-b border-gray-100 bg-emerald-50/60 p-1 sm:grid-cols-4">
                <button type="button" @click="detailTab = 'details'" :class="detailTab === 'details' ? 'bg-white text-[#1D9E75] shadow-sm' : 'text-emerald-900/70'" class="rounded-lg px-3 py-2 text-sm font-medium">Case Details</button>
                <button type="button" @click="detailTab = 'evidence'" :class="detailTab === 'evidence' ? 'bg-white text-[#1D9E75] shadow-sm' : 'text-emerald-900/70'" class="rounded-lg px-3 py-2 text-sm font-medium">Evidence & Files</button>
                <button type="button" @click="detailTab = 'notes'" :class="detailTab === 'notes' ? 'bg-white text-[#1D9E75] shadow-sm' : 'text-emerald-900/70'" class="rounded-lg px-3 py-2 text-sm font-medium">Internal Notes</button>
                <button type="button" @click="detailTab = 'activity'" :class="detailTab === 'activity' ? 'bg-white text-[#1D9E75] shadow-sm' : 'text-emerald-900/70'" class="rounded-lg px-3 py-2 text-sm font-medium">Activity</button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                @foreach ($rows as $row)
                    <div x-show="selectedId == {{ (int) $row['id'] }}" class="space-y-4">
                        <div x-show="detailTab === 'details'" class="space-y-4">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div class="rounded-xl border border-emerald-100 bg-[#F4FBF7] p-4">
                                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-800/70">Status</p>
                                    <span class="inline-flex {{ $row['badge'] }} font-medium">{{ $row['label'] }}</span>
                                </div>
                                <div class="rounded-xl border border-emerald-100 bg-[#F4FBF7] p-4">
                                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-800/70">Category</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $row['categoryLabel'] }}</p>
                                </div>
                                <div class="rounded-xl border border-emerald-100 bg-[#F4FBF7] p-4">
                                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-800/70">Assigned CODI member</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $row['assignedName'] }}</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-emerald-100/80 bg-white p-5">
                                <h4 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900"><x-lucide-file-text class="h-4 w-4 text-[#1D9E75]" /> {{ $isAdmin ? 'Case Information' : 'Report Information' }}</h4>
                                <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Complaint Title</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $row['title'] }}</p>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Location</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $row['location'] }}</p>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Incident Date</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $row['incidentDate'] }}</p>
                                    </div>
                                    @if ($row['incidentTime'] !== '')
                                        <div>
                                            <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Incident Time</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $row['incidentTime'] }}</p>
                                        </div>
                                    @endif
                                    @if ($row['degree'] !== '')
                                        <div>
                                            <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Degree of Harassment</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $row['degree'] }}</p>
                                        </div>
                                    @endif
                                    @if ($isAdmin)
                                        <div>
                                            <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Complainant</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $row['complainant'] }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Email</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $row['email'] }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Reported On</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $row['filedLabel'] }}</p>
                                        </div>
                                    @else
                                        <p class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-3 py-2.5 text-xs italic text-emerald-900/80 sm:col-span-2">Contact admin for complainant details</p>
                                    @endif
                                </div>
                            </div>

                            @if ($row['anonymous'] || $row['sealedName'])
                                <div class="rounded-2xl border border-blue-100 bg-blue-50/40 p-5">
                                    <h4 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900"><x-lucide-lock class="h-4 w-4 text-[#1D9E75]" /> Identity for fairness (DEIU-only)</h4>
                                    <p class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50/60 px-3 py-2.5 text-xs italic text-emerald-900/80">The respondent cannot see this. Identity is sealed for due process so both sides can be treated fairly if the case proceeds.</p>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Filing mode</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $row['sealedName'] ? 'Anonymous to respondent · Sealed identity on file' : ($row['anonymous'] ? 'Fully anonymous' : 'Confidential / identified') }}</p>
                                        </div>
                                        @if ($row['sealedName'])
                                            <div>
                                                <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Sealed name</p>
                                                <p class="text-sm font-medium text-gray-900">{{ $row['sealedName'] }}</p>
                                            </div>
                                            <div>
                                                <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Sealed contact</p>
                                                <p class="text-sm font-medium text-gray-900">{{ $row['sealedContact'] ?: '—' }}</p>
                                            </div>
                                            @if ($row['sealedType'] !== '')
                                                <div>
                                                    <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Role</p>
                                                    <p class="text-sm font-medium text-gray-900">{{ $row['sealedType'] }}</p>
                                                </div>
                                            @endif
                                        @else
                                            <p class="text-xs text-gray-600 sm:col-span-2">No sealed identity was provided. Follow-up and formal investigation may be limited because DEIU cannot contact the complainant directly.</p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="rounded-2xl border border-gray-100 bg-gray-50/40 p-5">
                                <h4 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900"><x-lucide-map-pin class="h-4 w-4 text-[#1D9E75]" /> Where It Happened</h4>
                                <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Location Details</p>
                                <p class="text-sm font-medium text-gray-900">{{ $row['vicinity'] }}</p>
                                @if ($row['mapAddress'])
                                    <p class="mt-3 mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Map Address</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $row['mapAddress'] }}</p>
                                @endif
                            </div>

                            @if ($row['respondent'] !== '' || $row['respondentDetail'] !== '')
                                <div class="rounded-2xl border border-amber-100 bg-amber-50/30 p-5">
                                    <h4 class="mb-4 text-sm font-semibold text-gray-900">Respondent Information</h4>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        @if ($row['respondent'] !== '')
                                            <div>
                                                <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Name</p>
                                                <p class="text-sm font-medium text-gray-900">{{ $row['respondent'] }}</p>
                                            </div>
                                        @endif
                                        @if ($row['respondentDetail'] !== '')
                                            <div class="sm:col-span-2">
                                                <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">{{ str_starts_with($row['respondent'], 'Unknown') ? 'Physical Description' : 'Address' }}</p>
                                                <p class="text-sm font-medium text-gray-900 whitespace-pre-wrap">{{ $row['respondentDetail'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="rounded-2xl border border-emerald-100/80 bg-white p-5">
                                <h4 class="mb-4 text-sm font-semibold text-gray-900">What Happened</h4>
                                @if ($row['description'] !== '')
                                    <div class="max-h-40 overflow-y-auto rounded-xl border border-gray-100 bg-gray-50/90 p-3 text-sm leading-relaxed text-gray-700 whitespace-pre-wrap">{{ $row['description'] }}</div>
                                @else
                                    <p class="text-sm text-gray-500">No description provided.</p>
                                @endif
                                @if ($row['statement'] !== '' && $row['statement'] !== $row['description'])
                                    <p class="mb-1 mt-4 text-[11px] font-medium uppercase tracking-wide text-gray-500">Statement of Facts</p>
                                    <div class="max-h-40 overflow-y-auto rounded-xl border border-gray-100 bg-gray-50/90 p-3 text-sm leading-relaxed text-gray-700 whitespace-pre-wrap">{{ $row['statement'] }}</div>
                                @endif
                            </div>

                            @if ($row['witnesses'] !== '' || ($row['additional'] !== '' && $row['additional'] !== $row['description']))
                                <div class="rounded-2xl border border-gray-100 bg-gray-50/40 p-5">
                                    <h4 class="mb-4 text-sm font-semibold text-gray-900">Additional Information</h4>
                                    @if ($row['witnesses'] !== '')
                                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Witnesses</p>
                                        <p class="text-sm font-medium text-gray-900 whitespace-pre-wrap">{{ $row['witnesses'] }}</p>
                                    @endif
                                    @if ($row['additional'] !== '' && $row['additional'] !== $row['description'])
                                        <p class="mb-1 mt-3 text-[11px] font-medium uppercase tracking-wide text-gray-500">Notes</p>
                                        <div class="rounded-xl border border-gray-100 bg-gray-50/90 p-3 text-sm leading-relaxed text-gray-700 whitespace-pre-wrap">{{ $row['additional'] }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div x-show="detailTab === 'evidence'" x-cloak>
                            <div class="rounded-lg border-2 border-slate-200 bg-white p-4">
                                <h4 class="mb-3 text-base font-bold text-gray-900">Attachments & Evidence</h4>
                                @if (count($row['files']) === 0 && count($row['links']) === 0)
                                    <p class="text-sm italic text-gray-500">No attachments or external links found</p>
                                @else
                                    @if (count($row['files']) > 0)
                                        <p class="mb-3 text-sm font-semibold text-gray-700">{{ count($row['files']) }} File{{ count($row['files']) === 1 ? '' : 's' }} Uploaded</p>
                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            @foreach ($row['files'] as $file)
                                                <a href="{{ $file['url'] }}" target="_blank" rel="noopener" class="rounded-lg border border-gray-200 p-3 text-sm text-gray-800 hover:border-[#1D9E75]">
                                                    @if (str_starts_with($file['mime'], 'image/'))
                                                        <img src="{{ $file['url'] }}" alt="" class="mb-2 h-24 w-full rounded object-cover">
                                                    @endif
                                                    <span class="line-clamp-2">{{ $file['name'] }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if (count($row['links']) > 0)
                                        <p class="mb-2 mt-4 text-sm font-semibold text-gray-700">External links</p>
                                        <ul class="space-y-1 text-sm">
                                            @foreach ($row['links'] as $link)
                                                <li><a href="{{ $link }}" target="_blank" rel="noopener" class="break-all text-[#1D9E75] hover:underline">{{ $link }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div x-show="detailTab === 'notes'" x-cloak class="rounded-lg border-2 border-gray-200 bg-white shadow-sm">
                            <div class="border-b-2 border-gray-200 bg-gradient-to-r from-gray-50 to-white px-5 py-4">
                                <h3 class="text-xl font-bold text-gray-900">Internal Notes</h3>
                                <p class="mt-1 text-sm text-gray-600">Private communication between Admin and CODI member • Not visible to complainant</p>
                            </div>
                            <div class="space-y-6 p-6">
                                @if (count($row['notes']) === 0)
                                    <div class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 py-12 text-center">
                                        <h3 class="mb-1 text-lg font-semibold text-gray-700">No Internal Notes Yet</h3>
                                        <p class="text-sm text-gray-500">Start the conversation by adding the first note below</p>
                                    </div>
                                @else
                                    <div class="max-h-96 space-y-4 overflow-y-auto">
                                        @foreach ($row['notes'] as $note)
                                            <div class="rounded-lg border p-4 {{ $note['role'] === 'Admin' ? 'border-l-4 border-l-purple-500 bg-purple-50/30' : 'border-l-4 border-l-orange-500 bg-orange-50/30' }}">
                                                <p class="text-sm font-semibold text-gray-900">{{ $note['name'] }} <span class="ml-2 rounded-full border px-2 py-0.5 text-xs {{ $note['role'] === 'Admin' ? 'border-purple-300 bg-purple-100 text-purple-800' : 'border-orange-300 bg-orange-100 text-orange-800' }}">{{ $note['role'] }}</span> <span class="ml-2 text-xs font-normal text-gray-500">{{ $note['when'] }}</span></p>
                                                <p class="mt-2 whitespace-pre-wrap text-sm text-gray-700">{{ $note['message'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <form method="POST" action="/admin/reports/{{ $row['id'] }}/notes" class="space-y-3 border-t-2 border-gray-200 pt-6">
                                    @csrf
                                    <p class="text-sm font-medium text-gray-700">Posting as: {{ auth()->user()->display_name ?: 'You' }}</p>
                                    <textarea name="message" required rows="4" placeholder="Type your internal note here... (visible only to Admin and CODI members)" class="w-full resize-none rounded-md border-2 border-gray-300 px-3 py-2 text-sm focus:border-[#1D9E75] focus:outline-none"></textarea>
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs text-gray-500">This note will only be visible to Admin and CODI members</p>
                                        <button class="inline-flex items-center gap-2 rounded-md bg-[#1D9E75] px-4 py-2 text-sm font-medium text-white hover:bg-[#178F65]">Post Note</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div x-show="detailTab === 'activity'" x-cloak class="space-y-3">
                            <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 px-4 py-3 text-sm text-emerald-900">
                                <span class="font-semibold">{{ count($row['activities']) }}</span> recorded {{ count($row['activities']) === 1 ? 'activity' : 'activities' }}
                            </div>
                            @foreach ($row['activities'] as $item)
                                <div class="rounded-xl border border-gray-200 bg-white p-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ $item['title'] }}</p>
                                    @if ($item['description'])
                                        <p class="mt-1 text-sm text-gray-600">{{ $item['description'] }}</p>
                                    @endif
                                    <p class="mt-2 text-xs text-gray-500">{{ $item['actor'] }} · {{ $item['when'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($isCODI)
                @foreach ($rows as $row)
                    @php
                        $nextActions = match (true) {
                            in_array($row['statusKey'], ['pending', 'submitted'], true) => [
                                ['status' => 'inProgress', 'label' => 'Start Investigation', 'class' => 'border border-[#1D9E75] bg-white text-[#178F65] hover:bg-[#ECFDF5]'],
                            ],
                            in_array($row['statusKey'], ['inprogress', 'investigating'], true) => [
                                ['status' => 'resolved', 'label' => 'Mark as Resolved', 'class' => 'border border-blue-200 bg-[#EFF6FF] text-blue-700 hover:bg-[#DBEAFE]'],
                                ['status' => 'dismissed', 'label' => 'Dismiss Case', 'class' => 'border border-red-200 bg-white text-red-700 hover:bg-[#FEF2F2]'],
                            ],
                            in_array($row['statusKey'], ['resolved', 'dismissed'], true) => [
                                ['status' => 'closed', 'label' => 'Close Case', 'class' => 'border border-gray-800 bg-gray-900 text-white hover:bg-gray-800'],
                            ],
                            default => [],
                        };
                    @endphp
                    @if ($row['mine'] && $row['statusKey'] !== 'closed' && $nextActions !== [])
                        <div x-show="selectedId == {{ (int) $row['id'] }}" x-cloak class="shrink-0 border-t border-emerald-100 bg-emerald-50/40 px-6 py-4">
                            <p class="text-sm font-semibold text-gray-900">CODI Decision & Status Management</p>
                            <p class="mt-1 text-xs text-gray-600">You are assigned to this case. You can investigate, update status, and close this case.</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($nextActions as $action)
                                    <button type="button" @click="openStatus({{ (int) $row['id'] }}, @js($action['status']), @js($action['label']), @js($row['caseNumber']))" class="inline-flex h-8 items-center rounded-full px-3.5 text-xs font-medium sm:text-sm {{ $action['class'] }}">{{ $action['label'] }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>

    <div x-show="statusAction !== null" x-cloak class="fixed inset-0 flex items-center justify-center bg-black/50 p-4" style="z-index: 80" @keydown.escape.window="statusAction = null">
        <form method="POST" enctype="multipart/form-data" :action="statusAction ? '/admin/reports/' + statusAction.id + '/status' : ''" class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg" role="dialog" aria-modal="true" @click.outside="statusAction = null">
            @csrf
            <input type="hidden" name="status" :value="statusAction ? statusAction.status : ''">
            <div class="px-6 pt-6 pb-2">
                <h2 class="text-lg font-semibold text-gray-900">Confirm Status Update</h2>
                <p class="mt-1 text-sm text-gray-600">
                    <span x-text="statusAction ? statusAction.label : ''"></span>
                    for <span class="font-semibold text-gray-900" x-text="statusAction ? statusAction.number : ''"></span>
                </p>
            </div>
            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-4">
                <div x-show="statusAction && statusAction.status !== 'closed'" class="space-y-2">
                    <label class="text-sm font-semibold text-gray-900" x-text="statusAction && statusAction.status === 'resolved' ? 'Resolution Details & Investigation Summary *' : (statusAction && statusAction.status === 'inProgress' ? 'Investigation Notes *' : 'Notes (optional)')"></label>
                    <select x-show="statusAction && statusAction.status === 'inProgress'" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" @change="if ($event.target.value) notes = $event.target.value">
                        <option value="">Quick suggestions (optional)</option>
                        <option value="I will begin the preliminary review of the submitted complaint and supporting documents.">Begin preliminary review</option>
                        <option value="This case is now under ongoing investigation. Initial document review is in progress.">Investigation now underway</option>
                        <option value="Investigation has commenced. The complainant will be contacted if additional information is needed.">Contact complainant if needed</option>
                        <option value="The assigned CODI member is proceeding with the initial assessment of this complaint.">Initial assessment started</option>
                    </select>
                    <select x-show="statusAction && statusAction.status === 'resolved'" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" @change="if ($event.target.value) notes = $event.target.value">
                        <option value="">Quick suggestions (optional)</option>
                        <option value="Investigation completed. Findings have been reviewed and appropriate action has been determined.">Investigation completed</option>
                        <option value="The case has been resolved after a thorough review of all submitted evidence and statements.">Resolved after evidence review</option>
                        <option value="Resolution issued following completion of the investigation and deliberation process.">Resolution following deliberation</option>
                    </select>
                    <select x-show="statusAction && statusAction.status === 'dismissed'" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" @change="if ($event.target.value) notes = $event.target.value">
                        <option value="">Quick suggestions (optional)</option>
                        <option value="After initial review, this complaint does not meet the criteria for further investigation at this time.">Does not meet criteria</option>
                        <option value="The case has been dismissed based on insufficient evidence to proceed with a full investigation.">Insufficient evidence</option>
                        <option value="Complaint reviewed and dismissed in accordance with institutional investigation guidelines.">Reviewed per guidelines</option>
                    </select>
                    <textarea name="notes" x-model="notes" rows="4" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" :placeholder="statusAction && statusAction.status === 'inProgress' ? 'Describe the investigation steps being taken...' : 'Add notes about this status change...'"></textarea>
                    <p x-show="statusAction && (statusAction.status === 'inProgress' || statusAction.status === 'resolved')" class="text-xs font-medium text-amber-600">Required: at least 10 characters.</p>
                </div>
                <div x-show="statusAction && statusAction.status === 'closed'" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-900">Decision Summary *</label>
                        <select class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" @change="if ($event.target.value) decision = $event.target.value">
                            <option value="">Quick suggestions (optional)</option>
                            <option value="After review, the complaint findings were sustained and appropriate disciplinary action was recommended.">Findings sustained</option>
                            <option value="The investigation found insufficient basis to sustain the complaint. The case is closed accordingly.">Insufficient basis to proceed</option>
                            <option value="The matter was resolved through mediation. Both parties were informed of the outcome.">Resolved through mediation</option>
                        </select>
                        <textarea name="decision_summary" x-model="decision" rows="3" placeholder="Summarize the final decision made on this case..." class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-900">Action Taken *</label>
                        <select class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" @change="if ($event.target.value) actionTaken = $event.target.value">
                            <option value="">Quick suggestions (optional)</option>
                            <option value="A formal warning was issued to the respondent and documented in their record.">Warning issued</option>
                            <option value="The respondent was referred for counseling and follow-up monitoring was arranged.">Counseling / referral</option>
                            <option value="No further disciplinary action is required. The case file has been archived.">No further action</option>
                        </select>
                        <textarea name="action_taken" x-model="actionTaken" rows="3" placeholder="Describe the actions taken as a result of this decision..." class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-900">Final Notes (optional)</label>
                        <textarea name="final_notes" x-model="finalNotes" rows="2" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-900">Closure Document (optional)</label>
                        <input type="file" name="closure_document" accept="image/*,.pdf,.doc,.docx" class="block w-full text-sm text-gray-600">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-gray-100 px-6 py-4">
                <button type="button" @click="statusAction = null" class="{{ $outlineBtn }}">Cancel</button>
                <button type="submit" class="inline-flex h-9 items-center rounded-md bg-[#1D9E75] px-4 text-sm font-medium text-white hover:bg-[#178F65]" :disabled="statusAction && ((statusAction.status === 'inProgress' || statusAction.status === 'resolved') && notes.trim().length < 10 || statusAction.status === 'closed' && (decision.trim() === '' || actionTaken.trim() === ''))">Confirm Update</button>
            </div>
        </form>
    </div>

    <div x-show="takeId !== null" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="takeId = null">
        <div class="w-full max-w-lg rounded-lg border border-gray-200 bg-white p-6 shadow-lg" role="dialog" aria-modal="true" aria-labelledby="take-case-title" @click.outside="takeId = null">
            <h2 id="take-case-title" class="text-lg font-semibold text-gray-900">Take this case?</h2>
            <p class="mt-2 text-sm text-gray-600">
                You will be assigned to <span class="font-semibold text-gray-900" x-text="takeNumber"></span>.
            </p>
            <p class="mt-1 text-sm text-gray-500" x-text="takeTitle"></p>
            <p class="mt-3 text-sm text-gray-600">The case stays Pending until you post your first update. The complainant is notified that a CODI member accepted it.</p>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="takeId = null" class="{{ $outlineBtn }}">Cancel</button>
                <form method="POST" :action="'/admin/reports/' + takeId + '/take'">
                    @csrf
                    <button type="submit" class="inline-flex h-9 items-center rounded-md bg-[#1D9E75] px-4 text-sm font-medium text-white hover:bg-[#178F65]">Yes, take case</button>
                </form>
            </div>
        </div>
    </div>

    @unless ($isHandler)
        <div x-show="exportOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="exportOpen = false">
            <div class="w-full max-w-lg rounded-lg border border-gray-200 bg-white p-6 shadow-lg" @click.outside="exportOpen = false">
                <h2 class="text-lg font-semibold text-gray-900">Export Reports to PDF</h2>
                <p class="mt-1 text-sm text-gray-500">Export <span x-text="visibleCount"></span> report<span x-show="visibleCount !== 1">s</span> with the current filters applied.</p>
                <div class="py-4">
                    <p class="text-sm text-gray-600">The PDF will include all visible reports in a compact, professional format with:</p>
                    <ul class="mt-2 space-y-1 text-sm text-gray-600 list-disc list-inside">
                        <li>Report ID, Title, and Category</li>
                        <li>Status and Complainant information</li>
                        <li>Location and Incident dates</li>
                        <li>Escalation level (if any)</li>
                    </ul>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="exportOpen = false" class="{{ $outlineBtn }}">Cancel</button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-md bg-[#1D9E75] px-3 py-2 text-sm font-medium text-white hover:bg-[#178F65]">
                        <x-lucide-download class="h-4 w-4" />
                        Export PDF
                    </button>
                </div>
            </div>
        </div>
    @endunless
</div>
</x-layouts.admin>
