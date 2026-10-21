{{-- Port of frontend/src/views/admin/ClosedCasesPage.tsx --}}
<x-layouts.admin>
@php
    $rows = $rows ?? collect();
    $user = auth()->user();
    $repRole = $user?->representative?->role;
    $isAdminNav = $user && ($user->isPlatformAdmin() || $repRole === 'admin');
    $isCodi = $isCodi ?? ($user && ! $isAdminNav && in_array($repRole, ['handler', 'codi'], true));
    $thisMonth = 0;
    $dayTotal = 0;
    $dated = 0;
    foreach ($rows as $row) {
        $filed = data_get($row, 'date_filed', data_get($row, 'dateFiled', data_get($row, 'filed_at')));
        $resolved = data_get($row, 'date_resolved', data_get($row, 'dateResolved', data_get($row, 'closed_at')));
        try {
            if ($resolved) {
                $resolvedAt = \Illuminate\Support\Carbon::parse($resolved);
                if ($resolvedAt->gte(now()->subMonth())) {
                    $thisMonth++;
                }
                if ($filed) {
                    $dayTotal += $resolvedAt->diffInDays(\Illuminate\Support\Carbon::parse($filed));
                    $dated++;
                }
            }
        } catch (\Throwable $e) {
        }
    }
    $avgDays = $dated > 0 ? (int) round($dayTotal / $dated) : 0;
    $categories = [
        'sexual_harassment' => 'Sexual Harassment',
        'gender_based_harassment' => 'Gender-Based Harassment',
        'discrimination' => 'Gender-Based Discrimination',
        'bullying' => 'Bullying/Harassment',
        'online_harassment' => 'Online Sexual Harassment',
        'other' => 'Other',
    ];
@endphp
<div
    class="w-full space-y-6 pb-10"
    x-data="{
        q: '',
        type: 'all',
        detailOpen: false,
        selected: null,
        visible(title, person, kind, label) {
            const term = this.q.toLowerCase();
            const blob = [title, person, kind, label].join(' ').toLowerCase();
            const textOk = !term || blob.includes(term);
            const typeOk = this.type === 'all' || kind === this.type;
            return textOk && typeOk;
        },
        openCase(row) { this.selected = row; this.detailOpen = true; },
        closeCase() { this.detailOpen = false; this.selected = null; }
    }"
>
    <div class="flex items-center justify-between">
        <div>
            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500">Archive</p>
            <h1 class="text-xl font-bold text-gray-900">{{ $isCodi ? 'My Closed Cases' : 'Case Archive' }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $isCodi ? 'Cases you closed or were assigned to when closed' : 'All resolved and closed cases with final decisions' }}
            </p>
        </div>
        <div class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-semibold">
            <x-lucide-archive class="mr-2 h-4 w-4" />
            {{ $rows->count() }} Closed Cases
        </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-6 pt-6">
            <div class="flex gap-4">
                <div class="relative flex-1">
                    <x-lucide-search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input type="search" x-model="q" placeholder="Search by case title, complainant, or type..." class="flex h-10 w-full rounded-md border border-input bg-background py-2 pl-10 pr-3 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                </div>
                <select x-model="type" class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="all">All Categories</option>
                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="flex items-center gap-2 text-2xl font-semibold leading-none tracking-tight">
                <x-lucide-archive class="h-5 w-5 text-gray-600" />
                Closed Cases
            </h3>
        </div>
        <div class="p-6 pt-0">
            @forelse ($rows as $row)
                @php
                    $title = (string) data_get($row, 'title', '');
                    $type = (string) data_get($row, 'type', data_get($row, 'category', ''));
                    $typeLabel = $categories[$type] ?? ($type !== '' ? $type : '');
                    $complainant = (string) data_get($row, 'complainant', data_get($row, 'complainant_name', ''));
                    $isAnonymous = (bool) data_get($row, 'is_anonymous', data_get($row, 'isAnonymous', false));
                    $fmt = function ($value) {
                        if (! $value) return '';
                        try {
                            return \Illuminate\Support\Carbon::parse($value)->timezone('Asia/Manila')->format('M d, Y');
                        } catch (\Throwable $e) {
                            return '';
                        }
                    };
                    $dateFiled = $fmt(data_get($row, 'date_filed', data_get($row, 'dateFiled', data_get($row, 'filed_at'))));
                    $dateResolved = $fmt(data_get($row, 'date_resolved', data_get($row, 'dateResolved', data_get($row, 'closed_at'))));
                    $decision = (string) data_get($row, 'decision_summary', data_get($row, 'decisionSummary', ''));
                    $assigned = (string) data_get($row, 'assigned_representative', data_get($row, 'assignedRepresentative', ''));
                    $rating = data_get($row, 'handling_feedback.rating', data_get($row, 'handlingFeedback.rating'));
                    $comment = (string) data_get($row, 'handling_feedback.comment', data_get($row, 'handlingFeedback.comment', ''));
                    $payload = [
                        'caseNo' => (string) data_get($row, 'case_number', data_get($row, 'case_id', data_get($row, 'caseId', data_get($row, 'id', '')))),
                        'title' => $title,
                        'typeLabel' => $typeLabel,
                        'complainant' => $complainant,
                        'isAnonymous' => $isAnonymous,
                        'dateFiled' => $dateFiled,
                        'dateResolved' => $dateResolved,
                        'assigned' => $assigned,
                        'closedBy' => (string) data_get($row, 'closed_by_name', data_get($row, 'closedByName', '')),
                        'description' => (string) data_get($row, 'description', ''),
                        'decision' => $decision,
                        'actionTaken' => (string) data_get($row, 'action_taken', data_get($row, 'actionTaken', '')),
                        'finalNotes' => (string) data_get($row, 'final_notes', data_get($row, 'finalNotes', '')),
                        'closureDocument' => (string) data_get($row, 'closure_document', data_get($row, 'closureDocument', '')),
                        'closureDocumentName' => (string) data_get($row, 'closure_document_name', data_get($row, 'closureDocumentName', 'Closure Document')),
                        'complianceReport' => (string) data_get($row, 'compliance_report', data_get($row, 'complianceReport', '')),
                        'rating' => $rating,
                        'comment' => $comment,
                        'sealedName' => (string) data_get($row, 'sealed_identity.name', data_get($row, 'sealedIdentity.name', '')),
                        'sealedContact' => (string) data_get($row, 'sealed_identity.contact', data_get($row, 'sealedIdentity.contact', '')),
                        'sealedRole' => (string) data_get($row, 'sealed_identity.type', data_get($row, 'sealedIdentity.type', '')),
                    ];
                @endphp
                @if ($loop->first)
                    <div class="overflow-x-auto">
                        <div class="relative w-full overflow-auto">
                            <table class="w-full caption-bottom text-sm">
                                <thead class="[&_tr]:border-b">
                                    <tr class="border-b">
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Case Title & Type</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Complainant</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Date Filed</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Date Resolved</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Decision Summary</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Assigned Rep</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Documents</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Feedback</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                @endif
                                    <tr
                                        data-case
                                        data-title="{{ $title }}"
                                        data-person="{{ $complainant }}"
                                        data-type="{{ $type }}"
                                        data-label="{{ $typeLabel }}"
                                        x-show="visible($el.dataset.title, $el.dataset.person, $el.dataset.type, $el.dataset.label)"
                                        class="cursor-pointer border-b transition-colors hover:bg-gray-50 dark:hover:bg-white/5"
                                        @click="openCase(@js($payload))"
                                    >
                                        <td class="p-4 align-middle">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $title }}</p>
                                                <p class="mt-0.5 text-xs text-gray-500">{{ $typeLabel }}</p>
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <div class="flex items-center gap-2">
                                                @if ($isAnonymous)
                                                    <x-lucide-user class="h-4 w-4 text-gray-400" />
                                                @endif
                                                <span class="{{ $isAnonymous ? 'italic text-gray-500' : 'text-gray-900' }}">{{ $complainant }}</span>
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                                <x-lucide-calendar class="h-4 w-4" />
                                                {{ $dateFiled }}
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                                <x-lucide-calendar class="h-4 w-4" />
                                                {{ $dateResolved }}
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <p class="max-w-xs truncate text-sm text-gray-700">{{ $decision }}</p>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <p class="text-sm text-gray-700">{{ $assigned }}</p>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <div class="flex gap-2">
                                                @if ($payload['closureDocument'] !== '')
                                                    <a href="{{ $payload['closureDocument'] }}" target="_blank" rel="noopener noreferrer" title="Closure Document" class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent" @click.stop>
                                                        <x-lucide-file-text class="h-4 w-4" />
                                                    </a>
                                                @endif
                                                @if ($payload['complianceReport'] !== '')
                                                    <a href="{{ $payload['complianceReport'] }}" target="_blank" rel="noopener noreferrer" title="Compliance Report" class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent" @click.stop>
                                                        <x-lucide-download class="h-4 w-4" />
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">Closed</span>
                                        </td>
                                        <td class="p-4 align-middle">
                                            @if ($rating)
                                                <div class="flex items-center gap-1 text-xs text-gray-700">
                                                    <x-lucide-star class="h-3.5 w-3.5 fill-amber-400 text-amber-400" />
                                                    {{ $rating }}/5
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="p-4 align-middle">
                                            <button type="button" class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent" @click.stop="openCase(@js($payload))">
                                                <x-lucide-eye class="mr-1 h-4 w-4" />
                                                View
                                            </button>
                                        </td>
                                    </tr>
                @if ($loop->last)
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div x-show="{{ (int) $rows->count() }} > 0 && ![...$root.querySelectorAll('[data-case]')].some((row) => row.style.display !== 'none' && !row.hasAttribute('hidden'))" x-cloak class="py-12 text-center">
                        <x-lucide-archive class="mx-auto mb-4 h-16 w-16 text-gray-300" />
                        <p class="font-medium text-gray-500">No closed cases found</p>
                        <p class="mt-1 text-sm text-gray-400">Try adjusting your search criteria</p>
                    </div>
                @endif
            @empty
                <div class="py-12 text-center">
                    <x-lucide-archive class="mx-auto mb-4 h-16 w-16 text-gray-300" />
                    <p class="font-medium text-gray-500">No closed cases found</p>
                    <p class="mt-1 text-sm text-gray-400">Resolved cases will appear here</p>
                </div>
            @endforelse
        </div>
    </div>

    <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 bg-black/80" @keydown.escape.window="closeCase()"></div>
    <div x-show="detailOpen" x-cloak class="fixed left-1/2 top-1/2 z-50 flex max-h-[min(90dvh,90vh)] w-[calc(100vw-2rem)] max-w-3xl -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden border bg-background p-0 shadow-lg sm:w-full sm:rounded-lg" @click.outside="closeCase()">
        <button type="button" class="absolute right-4 top-4 z-20 rounded-sm opacity-70 hover:opacity-100" @click="closeCase()">
            <x-lucide-x class="h-4 w-4" />
            <span class="sr-only">Close</span>
        </button>
        <div class="shrink-0 space-y-1.5 border-b px-6 py-5 text-center sm:text-left">
            <h2 class="text-xl font-bold text-gray-900">Closed Case Details</h2>
            <div class="space-y-1 text-sm text-muted-foreground">
                <div>Case No: <span class="font-semibold text-gray-900" x-text="selected?.caseNo"></span></div>
                <p class="text-gray-600" x-text="selected?.title"></p>
            </div>
        </div>
        <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-emerald-100 bg-[#F4FBF7] p-3 sm:p-4 dark:border-[#2f4a40] dark:bg-[#1a2e26]">
                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-800/70">Status</p>
                    <div class="text-sm font-semibold text-gray-900"><span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">Closed</span></div>
                </div>
                <div class="rounded-xl border border-emerald-100 bg-[#F4FBF7] p-3 sm:p-4">
                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-800/70">Category</p>
                    <div class="text-sm font-semibold text-gray-900" x-text="selected?.typeLabel"></div>
                </div>
                <div class="rounded-xl border border-emerald-100 bg-[#F4FBF7] p-3 sm:p-4">
                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-800/70">Assigned CODI member</p>
                    <div class="text-sm font-semibold text-gray-900" x-text="selected?.assigned"></div>
                </div>
            </div>
            <div x-show="selected?.isAnonymous || selected?.sealedName" class="rounded-2xl border border-blue-100 bg-blue-50/40 p-4 sm:p-5">
                <h4 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                    <x-lucide-lock class="h-4 w-4 shrink-0 text-[#1D9E75]" />
                    Identity for fairness (DEIU-only)
                </h4>
                <p class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50/60 px-3 py-2.5 text-xs italic text-emerald-900/80">The respondent cannot see this. Identity is sealed for due process so both sides can be treated fairly if the case proceeds.</p>
                <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Filing mode</p>
                        <div class="text-sm font-medium text-gray-900" x-text="selected?.sealedName ? 'Anonymous to respondent · Sealed identity on file' : 'Fully anonymous'"></div>
                    </div>
                    <template x-if="selected?.sealedName">
                        <div>
                            <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Sealed name</p>
                            <div class="text-sm font-medium text-gray-900" x-text="selected.sealedName"></div>
                        </div>
                    </template>
                    <template x-if="selected?.sealedContact">
                        <div>
                            <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Sealed contact</p>
                            <div class="text-sm font-medium text-gray-900" x-text="selected.sealedContact"></div>
                        </div>
                    </template>
                </div>
                <p x-show="selected?.isAnonymous && !selected?.sealedName" class="mt-4 flex items-start gap-2 text-xs text-gray-600">
                    <x-lucide-shield class="mt-0.5 h-4 w-4 shrink-0 text-blue-600" />
                    No sealed identity was provided. Follow-up and formal investigation may be limited because DEIU cannot contact the complainant directly.
                </p>
            </div>
            <div x-show="selected?.rating" class="rounded-2xl border border-gray-100 bg-gray-50/40 p-4 sm:p-5">
                <h4 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                    <x-lucide-star class="h-4 w-4 shrink-0 text-[#1D9E75]" />
                    Complainant feedback
                </h4>
                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                    <span class="font-medium text-gray-700" x-text="(selected?.rating || 0) + '/5'"></span>
                    <span x-show="selected?.comment" class="truncate text-gray-400">· <span x-text="selected?.comment"></span></span>
                </div>
            </div>
            <div class="rounded-2xl border border-emerald-100/80 bg-white p-4 sm:p-5">
                <h4 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                    <x-lucide-file-text class="h-4 w-4 shrink-0 text-[#1D9E75]" />
                    Case Information
                </h4>
                <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Complainant</p>
                        <div class="text-sm font-medium text-gray-900" x-text="selected?.complainant"></div>
                    </div>
                    <div>
                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Date Filed</p>
                        <div class="text-sm font-medium text-gray-900" x-text="selected?.dateFiled"></div>
                    </div>
                    <div>
                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Date Closed</p>
                        <div class="text-sm font-medium text-gray-900" x-text="selected?.dateResolved"></div>
                    </div>
                    <div x-show="selected?.closedBy">
                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Closed By</p>
                        <div class="text-sm font-medium text-gray-900" x-text="selected?.closedBy"></div>
                    </div>
                </div>
                <div class="mt-4" x-show="selected?.description">
                    <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Description</p>
                    <div class="max-h-40 overflow-y-auto whitespace-pre-wrap rounded-xl border border-gray-100 bg-gray-50/90 p-3 text-sm leading-relaxed text-gray-700" x-text="selected?.description"></div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50/40 p-4 sm:p-5">
                <h4 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                    <x-lucide-archive class="h-4 w-4 shrink-0 text-[#1D9E75]" />
                    Final Decision
                </h4>
                <div class="space-y-4">
                    <div>
                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Decision Summary</p>
                        <div class="max-h-40 overflow-y-auto whitespace-pre-wrap rounded-xl border border-gray-100 bg-gray-50/90 p-3 text-sm leading-relaxed text-gray-700" x-text="selected?.decision"></div>
                    </div>
                    <div x-show="selected?.actionTaken">
                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Action Taken</p>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/90 p-3 text-sm leading-relaxed text-gray-700" x-text="selected?.actionTaken"></div>
                    </div>
                    <div x-show="selected?.finalNotes">
                        <p class="mb-1 text-[11px] font-medium uppercase tracking-wide text-gray-500">Final Notes</p>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/90 p-3 text-sm leading-relaxed text-gray-700" x-text="selected?.finalNotes"></div>
                    </div>
                </div>
            </div>
            <div x-show="selected?.closureDocument || selected?.complianceReport" class="rounded-2xl border border-emerald-100/80 bg-white p-4 sm:p-5">
                <h4 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900">
                    <x-lucide-download class="h-4 w-4 shrink-0 text-[#1D9E75]" />
                    Documents
                </h4>
                <div class="flex flex-wrap gap-3">
                    <a x-show="selected?.closureDocument" :href="selected?.closureDocument" target="_blank" rel="noopener noreferrer" class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent">
                        <x-lucide-file-text class="mr-2 h-4 w-4" />
                        <span x-text="selected?.closureDocumentName || 'Closure Document'"></span>
                    </a>
                    <a x-show="selected?.complianceReport" :href="selected?.complianceReport" target="_blank" rel="noopener noreferrer" class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent">
                        <x-lucide-download class="mr-2 h-4 w-4" />
                        Compliance Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Closed</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $rows->count() }}</p>
                    </div>
                    <x-lucide-archive class="h-12 w-12 text-gray-300" />
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">This Month</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $thisMonth }}</p>
                    </div>
                    <x-lucide-calendar class="h-12 w-12 text-gray-300" />
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Avg Resolution Time</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $avgDays }} days</p>
                    </div>
                    <x-lucide-file-text class="h-12 w-12 text-gray-300" />
                </div>
            </div>
        </div>
    </div>
</div>
</x-layouts.admin>
