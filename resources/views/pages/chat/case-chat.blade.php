{{-- Port of frontend/src/views/case/CaseChat.tsx + chat panes. Root layout only (no sidebar). --}}
@php
    $complaint = $complaint ?? null;
    $messages = collect($messages ?? []);
    $error = $error ?? null;
    $loading = $loading ?? false;
    $user = auth()->user();
    $isHandler = $user ? $user->isStaff() : false;
    $complaintId = $complaintId ?? data_get($complaint, 'id');
    if ($messages->isEmpty() && $complaintId) {
        try {
            $messages = \Illuminate\Support\Facades\DB::table('messages')
                ->where('complaint_id', $complaintId)
                ->where('is_deleted', false)
                ->orderBy('created_at')
                ->get();
        } catch (\Throwable $e) {
            $messages = collect();
        }
    }
    $filesByMessage = collect();
    if ($messages->isNotEmpty()) {
        try {
            $filesByMessage = \Illuminate\Support\Facades\DB::table('attachments')
                ->where('attachable_type', 'message')
                ->whereIn('attachable_id', $messages->pluck('id'))
                ->get()
                ->groupBy('attachable_id');
        } catch (\Throwable $e) {
            $filesByMessage = collect();
        }
    }
    $title = data_get($complaint, 'title') ?: 'Case Chat';
    $caseNumber = data_get($complaint, 'case_number') ?: $complaintId;
    $assignedName = data_get($complaint, 'assigned_to_name');
    $sensitiveCase = in_array(strtolower((string) data_get($complaint, 'type')), ['sexual_harassment', 'gender_based_sexual_harassment', 'gbvh', 'gbv'], true);
    if ($sensitiveCase && ! $isHandler) {
        $assignedName = $assignedName ? 'CODI member' : null;
    }
    $hasHandler = (bool) data_get($complaint, 'assigned_representative_id') || (bool) $assignedName;
    $isAnonymous = (bool) data_get($complaint, 'is_anonymous') || data_get($complaint, 'complainant_name') === 'Anonymous';
    $complainantName = $isAnonymous ? 'Anonymous' : (data_get($complaint, 'complainant_name') ?: 'Unknown');
    $initial = strtoupper(mb_substr((string) ($hasHandler ? ($assignedName ?: 'C') : ($complainantName ?: 'C')), 0, 1));
    $sealed = data_get($complaint, 'sealed_identity');
    if (is_string($sealed)) {
        $decoded = json_decode($sealed, true);
        $sealed = is_array($decoded) ? $decoded : [];
    }
    $sealed = is_array($sealed) ? $sealed : [];
    $email = data_get($complaint, 'complainant_email');
    $description = (string) data_get($complaint, 'description', '');
    $categoryLabels = [
        'sexual_harassment' => 'Sexual Harassment',
        'gender_based_harassment' => 'Gender-Based Harassment',
        'discrimination' => 'Gender-Based Discrimination',
        'bullying' => 'Bullying/Harassment',
        'online_harassment' => 'Online Sexual Harassment',
        'other' => 'Other',
    ];
    $category = (string) (data_get($complaint, 'type') ?: '');
    $categoryLabel = $categoryLabels[$category] ?? ($category !== '' ? $category : '—');
    $quickResponses = [
        ['label' => 'Initial Greeting', 'message' => 'Hello, I am the CODI member assigned to your case. I have reviewed your complaint and will be assisting you throughout this process. Please feel free to share any additional information or evidence that may help with the investigation.'],
        ['label' => 'Request More Info', 'message' => 'Thank you for your patience. To better assist you, could you please provide more details about the incident? Specifically, any additional context, dates, times, or witnesses would be helpful.'],
        ['label' => 'Acknowledge Receipt', 'message' => 'I have received your message and the information you provided. I will review it carefully and get back to you shortly with next steps.'],
        ['label' => 'Schedule Meeting', 'message' => 'I would like to schedule a meeting to discuss your case in more detail. Please let me know your available times, and we can arrange a confidential discussion.'],
        ['label' => 'Case Update', 'message' => 'I wanted to provide you with an update on your case. We are currently in the investigation phase and gathering all necessary information. I will keep you informed of any developments.'],
    ];
@endphp
<x-layouts.app>
@if ($loading)
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 flex items-center justify-center p-4">
        <div class="shadow-2xl border-0 max-w-md w-full rounded-lg bg-white">
            <div class="p-8 text-center">
                <div class="flex justify-center mb-4">
                    <div class="relative">
                        <x-lucide-loader class="h-12 w-12 animate-spin text-blue-600" />
                        <x-lucide-message-circle class="h-6 w-6 text-blue-600 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2" />
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Loading Conversation</h3>
                <p class="text-gray-600 mb-4">Preparing your chat interface...</p>
                <div class="text-xs text-gray-500 space-y-1">
                    <p>Complaint ID: {{ $complaintId ?: '—' }}</p>
                    <p>User: {{ $user?->email ?? 'Not signed in' }}</p>
                    <p>Role: {{ $isHandler ? 'CODI' : 'None' }}</p>
                </div>
            </div>
        </div>
    </div>
@elseif ($error)
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 flex items-center justify-center p-4">
        <div class="shadow-2xl border-0 max-w-md w-full rounded-lg bg-white">
            <div class="p-8 text-center">
                <x-lucide-circle-alert class="h-16 w-16 text-red-500 mx-auto mb-4" />
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Unable to Load Chat</h2>
                <p class="text-gray-600 mb-2">{{ $error }}</p>
                <p class="text-sm text-gray-500 mb-6">Complaint ID: {{ $complaintId ?: '—' }}</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ url()->current() }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors inline-flex items-center gap-2 justify-center">
                        <x-lucide-refresh-cw class="h-4 w-4" />
                        Try Again
                    </a>
                    <a href="{{ $user?->homePath() ?? '/dashboard' }}" class="px-6 py-2 rounded-lg border border-gray-200 inline-flex items-center justify-center">Go to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
@elseif (! $complaintId && ! $complaint)
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 flex items-center justify-center p-4">
        <div class="shadow-2xl border-0 max-w-md w-full rounded-lg bg-white">
            <div class="p-8 text-center">
                <x-lucide-message-circle class="h-16 w-16 text-red-500 mx-auto mb-4" />
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Invalid Complaint ID</h2>
                <p class="text-gray-600 mb-6">The chat room you're trying to access doesn't exist or has been removed.</p>
                <a href="{{ $user?->homePath() ?? '/dashboard' }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors inline-flex">Go to Dashboard</a>
            </div>
        </div>
    </div>
@else
    <div class="fixed inset-0 z-10 flex min-h-0 flex-col overflow-hidden bg-slate-200/70" x-data="{
        body: '',
        fileName: '',
        quick: false,
        details: false,
        caseOpen: true,
        seeMore: false,
        pickFile(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) { this.fileName = ''; return; }
            const allowed = /\.(jpe?g|png|gif|webp|heic|heif|pdf|docx?|txt)$/i.test(file.name);
            if (!allowed) {
                window.toast({ title: 'File not allowed', description: 'Use an image, PDF, Word, or text file.', variant: 'destructive' });
                event.target.value = '';
                this.fileName = '';
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                window.toast({ title: 'File too large', description: file.name + ' is over 10 MB.', variant: 'destructive' });
                event.target.value = '';
                this.fileName = '';
                return;
            }
            this.fileName = file.name;
        }
    }">
        <div class="relative shrink-0 border-b border-slate-200/80 bg-white shadow-sm">
            <div class="mx-auto flex max-w-[1180px] items-center gap-2 px-3 py-2 sm:px-4">
                <a href="{{ url()->previous() }}" aria-label="Back" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-gray-700 hover:bg-gray-100">
                    <x-lucide-arrow-left class="h-4 w-4" />
                </a>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900">Case conversation</p>
                    @if ($complaint)
                        <p class="truncate text-xs text-gray-500">{{ $caseNumber }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="relative min-h-0 flex-1">
            <div class="flex h-full min-h-0 w-full flex-col">
                <div class="mx-auto flex h-full min-h-0 w-full {{ $isHandler ? 'max-w-[1180px]' : 'max-w-[720px]' }} flex-col px-2 pb-2 pt-1 sm:px-3">
                    <div class="flex min-h-0 flex-1 overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-[0_8px_30px_rgba(15,23,42,0.08)]">
                        <div class="flex min-h-0 min-w-0 flex-1 flex-col">
                            <div class="flex shrink-0 items-center gap-3 border-b border-slate-200/80 bg-gradient-to-r from-white to-emerald-50/30 px-3 py-2.5 sm:px-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#0d7a5c] to-[#1D9E75] text-sm font-bold text-white shadow-md ring-2 ring-white">
                                    @if ($isHandler && $isAnonymous)
                                        <x-lucide-user class="h-5 w-5" />
                                    @elseif (! $isHandler && ! $hasHandler)
                                        <x-lucide-shield class="h-5 w-5" />
                                    @else
                                        {{ $initial }}
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    @if ($isHandler)
                                        <p class="truncate text-[15px] font-semibold text-gray-900">{{ $complainantName }}</p>
                                        <p class="truncate text-xs text-gray-500">
                                            {{ $title }}
                                            <span class="mx-1 text-gray-300">·</span>
                                            <span class="font-mono text-[11px]">{{ $caseNumber }}</span>
                                        </p>
                                    @else
                                        <p class="truncate text-[15px] font-semibold text-gray-900">{{ $hasHandler ? $assignedName : 'Waiting for handler' }}</p>
                                        <p class="truncate text-xs text-gray-500">
                                            {{ $title }}
                                            @unless ($hasHandler)
                                                <span class="mx-1 text-gray-300">·</span>
                                                <span class="text-amber-600">A CODI member will join soon</span>
                                            @endunless
                                        </p>
                                    @endif
                                </div>
                                @if ($isHandler)
                                    <button type="button" @click="details = true" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-gray-600 hover:bg-gray-100 lg:hidden" aria-label="Case details">
                                        <x-lucide-panel-right class="h-5 w-5" />
                                    </button>
                                @endif
                            </div>

                            <div class="relative min-h-0 flex-1" style="background-color: #eef2f6; background-image: radial-gradient(circle at 1px 1px, rgba(148,163,184,0.15) 1px, transparent 0); background-size: 20px 20px;">
                                <div class="absolute inset-0 overflow-y-auto overflow-x-hidden">
                                    @php $messages = $messages ?? collect(); @endphp
                                    @forelse ($messages as $message)
                                        @php
                                            $mine = $user && (string) data_get($message, 'sender_user_id') === (string) $user->id;
                                            $when = data_get($message, 'created_at');
                                            $prev = $loop->index > 0 ? $messages[$loop->index - 1] : null;
                                            $showDate = ! $prev || ($when && \Illuminate\Support\Carbon::parse($when)->toDateString() !== \Illuminate\Support\Carbon::parse(data_get($prev, 'created_at'))->toDateString());
                                        @endphp
                                        @if ($showDate && $when)
                                            <div class="flex justify-center py-2">
                                                <span class="rounded-full border border-slate-200/70 bg-white/90 px-3 py-1 text-[11px] font-medium text-slate-500 shadow-sm">{{ \Illuminate\Support\Carbon::parse($when)->format('M j, Y') }}</span>
                                            </div>
                                        @endif
                                        @php
                                            $bubbleFiles = $filesByMessage->get(data_get($message, 'id')) ?? collect();
                                            $bubbleText = trim((string) data_get($message, 'content'));
                                            $fileOnly = $bubbleFiles->isNotEmpty() && $bubbleFiles->contains(fn ($file) => $file->file_name === $bubbleText);
                                        @endphp
                                        <div class="flex px-3 py-1 sm:px-4 {{ $mine ? 'justify-end' : 'justify-start' }}">
                                            <div class="max-w-[80%]">
                                                @unless ($mine)
                                                    <p class="mb-0.5 text-[11px] font-medium text-gray-500">{{ data_get($message, 'sender_name') ?: ($isHandler ? $complainantName : ($assignedName ?: 'CODI member')) }}</p>
                                                @endunless
                                                <div class="px-[14px] py-[10px] text-sm {{ $mine ? 'rounded-[18px] rounded-br-[4px] bg-gradient-to-br from-[#0d7a5c] to-[#1D9E75] text-white shadow-[0_2px_8px_rgba(13,122,92,0.28)]' : 'rounded-[18px] rounded-bl-md bg-white text-gray-800 shadow-[0_1px_3px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70' }}">
                                                    @if ($bubbleText !== '' && ! $fileOnly)
                                                        <p class="whitespace-pre-wrap">{{ $bubbleText }}</p>
                                                    @endif
                                                    @foreach ($bubbleFiles as $file)
                                                        @php $fileUrl = route('chat.file', $file->id); $isImage = str_starts_with((string) $file->mime, 'image/'); @endphp
                                                        @if ($isImage)
                                                            <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="{{ $bubbleText !== '' && ! $fileOnly ? 'mt-2' : '' }} block overflow-hidden rounded-lg">
                                                                <img src="{{ $fileUrl }}" alt="{{ $file->file_name }}" class="max-h-48 max-w-full rounded-lg object-cover">
                                                            </a>
                                                        @else
                                                            <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="{{ ($bubbleText !== '' && ! $fileOnly) || ! $loop->first ? 'mt-2' : '' }} flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-medium underline {{ $mine ? 'bg-white/15' : 'bg-emerald-50 text-[#178F65]' }}">
                                                                <x-lucide-paperclip class="h-3.5 w-3.5 shrink-0" />
                                                                <span class="truncate">{{ $file->file_name ?: 'Attachment' }}</span>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="flex h-full flex-col items-center justify-center px-6 py-10 text-center">
                                            <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-gray-200/80">
                                                <x-lucide-message-circle class="h-8 w-8 text-[#1D9E75]" />
                                            </div>
                                            <p class="text-sm font-semibold text-gray-900">No messages yet</p>
                                            <p class="mt-1 max-w-xs text-xs text-gray-500">
                                                @if ($isHandler)
                                                    Say hi to start this case conversation.
                                                @else
                                                    Welcome to your case chat. A handler will respond to you shortly.
                                                @endif
                                            </p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="shrink-0 border-t border-slate-200/80 bg-white/95 backdrop-blur-sm">
                                <form method="POST" action="" enctype="multipart/form-data" class="px-3 py-2" @submit="if (!body.trim() && !$refs.chatFile.files.length) { $event.preventDefault(); window.toast({ title: 'Nothing to send', description: 'Type a message or choose a file.', variant: 'destructive' }); }">
                                    @csrf
                                    <input type="hidden" name="complaint_id" value="{{ $complaintId }}">
                                    <input x-ref="chatFile" type="file" name="attachment" accept="image/*,.heic,.heif,.pdf,.doc,.docx,.txt" class="absolute h-px w-px opacity-0" @change="pickFile($event)">
                                    @if ($isHandler)
                                        <div class="relative mb-2">
                                            <button type="button" @click="quick = !quick" class="inline-flex h-10 w-10 items-center justify-center rounded-md hover:bg-green-50" title="Quick Responses">
                                                <x-lucide-message-square class="h-5 w-5 text-gray-500" />
                                            </button>
                                            <div x-show="quick" x-cloak @click.outside="quick = false" class="absolute bottom-12 left-0 z-20 w-80 rounded-md border border-gray-200 bg-white p-2 shadow-lg">
                                                <p class="px-2 py-1 text-sm font-medium text-gray-900">Quick Responses</p>
                                                @foreach ($quickResponses as $response)
                                                    <button type="button" class="block w-full rounded-md px-2 py-2 text-left hover:bg-gray-50" @click="body = @js($response['message']); quick = false">
                                                        <span class="block text-sm font-medium">{{ $response['label'] }}</span>
                                                        <span class="line-clamp-2 text-xs text-gray-500">{{ $response['message'] }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    <div x-show="fileName" x-cloak class="mb-2 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs text-emerald-900">
                                        <x-lucide-paperclip class="h-3.5 w-3.5 shrink-0" />
                                        <span class="truncate" x-text="fileName"></span>
                                        <button type="button" class="ml-auto font-medium text-[#178F65]" @click="fileName = ''; $refs.chatFile.value = ''">Remove</button>
                                    </div>
                                    <div class="flex items-end gap-2 rounded-2xl border border-slate-200 bg-white px-2 py-1.5 shadow-sm">
                                        <button type="button" @click="$refs.chatFile.click()" class="inline-flex h-10 w-10 flex-shrink-0 cursor-pointer items-center justify-center rounded-md hover:bg-green-50" title="Attach file" aria-label="Attach file">
                                            <x-lucide-paperclip class="h-5 w-5 text-gray-500" />
                                        </button>
                                        <textarea name="content" x-model="body" rows="1" placeholder="Aa" class="w-full resize-none border border-transparent bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1D9E75]/30 focus:border-[#1D9E75]" style="min-height: 36px"></textarea>
                                        <button type="submit" class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#0d7a5c] to-[#1D9E75] text-white shadow-md hover:from-[#0a6b51] hover:to-[#178F65]" aria-label="Send">
                                            <x-lucide-send class="h-5 w-5" />
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        @if ($isHandler && $complaint)
                            <aside class="hidden w-[300px] shrink-0 flex-col overflow-y-auto border-l border-slate-200/80 bg-slate-50/40 lg:flex xl:w-[320px]">
                                <div class="relative border-b bg-white px-4 pb-4 pt-5 text-center">
                                    <div class="mx-auto flex h-[72px] w-[72px] items-center justify-center rounded-full bg-gradient-to-br from-[#0d7a5c] to-[#1D9E75] text-2xl font-bold text-white shadow-lg ring-4 ring-white">
                                        @if ($isAnonymous)
                                            <x-lucide-user class="h-8 w-8" />
                                        @else
                                            {{ strtoupper(mb_substr($complainantName, 0, 1)) }}
                                        @endif
                                    </div>
                                    <h2 class="mt-3 text-base font-semibold text-gray-900">{{ $complainantName }}</h2>
                                    <p class="mt-0.5 text-xs text-gray-500">{{ $title }}</p>
                                    <p class="mt-1 font-mono text-[11px] text-emerald-700">{{ $caseNumber }}</p>
                                </div>
                                <div class="flex-1 overflow-y-auto">
                                    <div class="border-b px-4 py-3">
                                        <button type="button" @click="caseOpen = !caseOpen" class="flex w-full items-center justify-between text-left">
                                            <span class="text-sm font-semibold text-gray-900">Case information</span>
                                            <x-lucide-chevron-down class="h-4 w-4 text-gray-400" />
                                        </button>
                                        <div x-show="caseOpen" class="mt-3 space-y-3">
                                            <dl class="grid grid-cols-2 gap-x-3 gap-y-2.5">
                                                <div class="col-span-2">
                                                    <dt class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Category</dt>
                                                    <dd class="text-xs font-medium text-gray-900">{{ $categoryLabel }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Incident</dt>
                                                    <dd class="mt-0.5 text-xs font-medium text-gray-900">{{ data_get($complaint, 'incident_date') ? \Illuminate\Support\Carbon::parse(data_get($complaint, 'incident_date'))->format('M j, Y') : '—' }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Reported</dt>
                                                    <dd class="mt-0.5 text-xs font-medium text-gray-900">{{ data_get($complaint, 'filed_at') ? \Illuminate\Support\Carbon::parse(data_get($complaint, 'filed_at'))->format('M j, Y g:i A') : '—' }}</dd>
                                                </div>
                                                <div class="col-span-2">
                                                    <dt class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Location</dt>
                                                    <dd class="mt-0.5 text-xs font-medium text-gray-900">
                                                        @if (data_get($complaint, 'incident_location'))
                                                            {{ data_get($complaint, 'incident_location') }}
                                                        @else
                                                            <span class="italic text-gray-500">Not specified</span>
                                                        @endif
                                                    </dd>
                                                </div>
                                            </dl>
                                            <div>
                                                <p class="mb-1 text-[10px] font-medium uppercase tracking-wide text-gray-400">Description</p>
                                                <p class="max-h-28 text-xs text-gray-700 whitespace-pre-wrap" :class="seeMore ? '' : 'line-clamp-4'">{{ $description !== '' ? $description : 'No description provided.' }}</p>
                                                @if (strlen($description) > 180)
                                                    <button type="button" @click="seeMore = !seeMore" class="mt-1 text-[11px] font-medium text-[#1D9E75] hover:underline" x-text="seeMore ? 'See less' : 'See more'"></button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-4 py-3">
                                        <p class="text-sm font-semibold text-gray-900">Complainant</p>
                                        @if ($isAnonymous)
                                            <div class="mt-2 flex items-start gap-1.5 rounded-lg bg-blue-50 px-2.5 py-2">
                                                <x-lucide-info class="mt-0.5 h-3.5 w-3.5 shrink-0 text-blue-600" />
                                                <p class="text-[11px] leading-snug text-blue-800">Anonymous to the respondent. Sealed identity below is DEIU-only and never shown in chat.</p>
                                            </div>
                                        @endif
                                        <dl class="mt-3 space-y-2">
                                            <div>
                                                <dt class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Name</dt>
                                                <dd class="mt-0.5 text-xs font-medium text-gray-900">{{ $complainantName }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Email</dt>
                                                <dd class="mt-0.5 text-xs font-medium text-gray-900">
                                                    @if ($email && ! str_starts_with((string) $email, 'anonymous@'))
                                                        {{ $email }}
                                                    @else
                                                        <span class="italic text-gray-500">No email provided</span>
                                                    @endif
                                                </dd>
                                            </div>
                                            @if (! empty($sealed['name']))
                                                <div>
                                                    <dt class="text-[10px] font-medium uppercase tracking-wide text-indigo-500">Sealed name (DEIU)</dt>
                                                    <dd class="mt-0.5 text-xs font-medium text-gray-900">{{ $sealed['name'] }}</dd>
                                                </div>
                                                @if (! empty($sealed['contact']))
                                                    <div>
                                                        <dt class="text-[10px] font-medium uppercase tracking-wide text-indigo-500">Sealed contact</dt>
                                                        <dd class="mt-0.5 text-xs font-medium text-gray-900">{{ $sealed['contact'] }}</dd>
                                                    </div>
                                                @endif
                                            @endif
                                        </dl>
                                    </div>
                                </div>
                            </aside>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($isHandler && $complaint)
            <div x-show="details" x-cloak class="lg:hidden">
                <button type="button" class="fixed inset-0 z-40 bg-black/40" aria-label="Close details overlay" @click="details = false"></button>
                <aside class="fixed inset-y-0 right-0 z-50 flex w-[min(100%,320px)] flex-col overflow-y-auto border-l bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b px-4 py-3">
                        <p class="text-sm font-semibold text-gray-900">Case details</p>
                        <button type="button" @click="details = false" class="rounded-full p-2 hover:bg-gray-100" aria-label="Close details">
                            <x-lucide-x class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="px-4 py-4">
                        <p class="text-sm font-semibold text-gray-900">{{ $title }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ data_get($complaint, 'status') ?: 'pending' }}</p>
                        <p class="mt-3 text-sm text-gray-700 whitespace-pre-wrap">{{ $description !== '' ? $description : 'No description provided.' }}</p>
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endif
</x-layouts.app>
