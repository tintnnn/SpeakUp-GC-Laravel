{{-- Port of frontend/src/views/complainant/FormalComplaint.tsx --}}
@php
    $user = auth()->user();
    $prefillName = '';
    if ($user && $user->display_name && ! str_contains($user->display_name, '@')) {
        $prefillName = $user->display_name;
    }
    $today = now()->format('Y-m-d');
    $faqs = [
        ['q' => 'Are submitted reports kept confidential?', 'a' => 'Yes. Every report submitted through SpeakUp GC is kept confidential. Only authorized DEIU and CODI personnel can open your case. Your identity is never disclosed to the respondent without your consent, in line with the Data Privacy Act (RA 10173).'],
        ['q' => 'Can I stay anonymous?', 'a' => 'Yes. You can file anonymously so the respondent never sees your name. For fairness, you may also share a sealed identity with DEIU only. That lets investigators contact you and process both sides fairly if the case goes to a formal investigation — without revealing your identity to the other party.'],
        ['q' => 'When does DEIU need a sealed identity?', 'a' => 'If a case proceeds to investigation or hearing, both sides have a right to due process. A sealed identity lets DEIU verify and contact you while you remain anonymous to the respondent. Fully anonymous filings are still reviewed, but follow-up and formal action may be limited.'],
        ['q' => 'What happens after I submit a complaint?', 'a' => 'You receive a Case ID right away. DEIU reviews the report, then a CODI member accepts it from the queue. You can track each move in My Cases: Submitted → Accepted → Investigating → Decision. You will be notified when the case is accepted and whenever the status changes.'],
        ['q' => 'How do I know my complaint was really accepted?', 'a' => 'Open My Cases or the complaint tracker. When a CODI member takes your case, the tracker moves to Accepted and you receive a notification. Messaging opens after acceptance. If nothing changes after a few days, you can request a follow-up from the case page.'],
        ['q' => 'Can I rate how my complaint was handled?', 'a' => 'Yes. Once your case is resolved or closed, you can rate the handling (1–5) and leave a short comment. This feedback stays with DEIU so they can see whether the complaint system did its job. It is not shown to the respondent.'],
    ];
    $categories = [
        ['value' => 'sexual_harassment', 'label' => 'Sexual Harassment'],
        ['value' => 'gender_based_harassment', 'label' => 'Gender-Based Harassment'],
        ['value' => 'discrimination', 'label' => 'Gender-Based Discrimination'],
        ['value' => 'bullying', 'label' => 'Bullying/Harassment'],
        ['value' => 'online_harassment', 'label' => 'Online Sexual Harassment'],
        ['value' => 'other', 'label' => 'Other'],
    ];
    $departments = [
        'Administration',
        'CCS - College of Computer Studies',
        'CHTM - College of Hospitality and Tourism Management',
        'CEAS - College of Education and Arts Sciences',
        'CBA - College of Business Administration',
        'CAHS - College of Allied Health Sciences',
    ];
    $platforms = ['Facebook', 'Messenger', 'Instagram', 'Twitter/X', 'TikTok', 'Discord', 'Telegram', 'WhatsApp', 'Email', 'SMS/Text Message', 'Zoom', 'Google Meet', 'Microsoft Teams', 'Other Platform'];
    $steps = ['Your information', 'Respondent', 'Incident details', 'Supporting files', 'Review'];
@endphp
<x-layouts.complainant>
<div
    class="min-h-full"
    x-data="{
        step: 1,
        faq: null,
        labels: ['Your information', 'Respondent', 'Incident details', 'Supporting files', 'Review'],
        hints: [
            'Your personal details as the complainant.',
            'Details about the person you are reporting.',
            'What happened, when, and where.',
            'Attach supporting evidence and files (sworn statement optional).',
            'Confirm all details are accurate before submitting.'
        ],
        isAnonymous: false,
        shareSealed: false,
        unknownRespondent: false,
        complainantName: @js($prefillName),
        address: '',
        contact: '',
        role: '',
        sealedName: '',
        sealedType: '',
        sealedContact: '',
        respondentName: '',
        respondentDepartment: '',
        respondentType: '',
        respondentAddress: '',
        type: 'sexual_harassment',
        harassmentDegree: '',
        otherDetail: '',
        title: '',
        titleManual: false,
        description: '',
        incidentDate: '',
        incidentTime: '',
        vicinity: 'inside',
        incidentLocation: '',
        customLocation: false,
        mapOpen: false,
        mapQuery: '',
        dateError: '',
        witnesses: '',
        statementOfFacts: '',
        evidenceOpen: false,
        files: [],
        links: [''],
        categoryLabel(value) {
            const labels = {
                sexual_harassment: 'Sexual Harassment',
                gender_based_harassment: 'Gender-Based Harassment',
                discrimination: 'Gender-Based Discrimination',
                bullying: 'Bullying/Harassment',
                online_harassment: 'Online Sexual Harassment',
                other: 'Other'
            };
            return labels[value] || value;
        },
        syncTitle() {
            if (this.titleManual) return;
            let label = this.categoryLabel(this.type);
            if (this.type === 'other' && this.otherDetail.trim()) label = this.otherDetail.trim();
            let datePart = 'Incident Report';
            if (this.incidentDate) {
                const d = new Date(this.incidentDate + 'T00:00:00');
                if (!isNaN(d.getTime())) datePart = d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }
            this.title = label + ' - ' + datePart;
        },
        onType() {
            this.titleManual = false;
            if (this.type !== 'other') this.otherDetail = '';
            if (this.type !== 'sexual_harassment') this.harassmentDegree = '';
            this.syncTitle();
        },
        onAnonymous() {
            if (this.isAnonymous) {
                if (this.complainantName && this.complainantName !== 'Anonymous') this.sealedName = this.sealedName || this.complainantName;
                if (this.contact && this.contact !== 'Not Disclosed') this.sealedContact = this.sealedContact || this.contact;
                if (this.role) this.sealedType = this.sealedType || this.role;
                this.shareSealed = true;
                this.complainantName = 'Anonymous';
                this.address = 'Not Disclosed';
                this.contact = 'Not Disclosed';
            } else {
                this.shareSealed = false;
                this.complainantName = this.sealedName || @js($prefillName);
                if (this.address === 'Not Disclosed') this.address = '';
                this.contact = (this.sealedContact && this.sealedContact !== 'Not Disclosed') ? this.sealedContact : '';
                if (this.sealedType) this.role = this.sealedType;
            }
        },
        onUnknown() {
            if (this.unknownRespondent) {
                this.respondentName = 'Unknown/Not Disclosed';
                this.respondentDepartment = 'Unknown';
            } else {
                this.respondentName = '';
                this.respondentDepartment = '';
            }
        },
        lettersOnly(event, key) {
            this[key] = event.target.value.replace(/[^a-zA-Z\u00C0-\u00FF\s\-'.]/g, '');
        },
        onContact(event, key) {
            this[key] = event.target.value.replace(/\D/g, '').slice(0, 11);
        },
        validMobile(value) {
            return /^09\d{9}$/.test(value || '');
        },
        onDate(event) {
            const value = event.target.value;
            const now = new Date();
            const todayStr = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
            if (value && value > todayStr) {
                this.dateError = 'Hindi pwedeng future date ang incident date. Ngayon lang ang pinakahuli: ' + todayStr;
                this.incidentDate = '';
                event.target.value = '';
                return;
            }
            this.dateError = '';
            this.incidentDate = value;
            this.syncTitle();
        },
        formattedDate() {
            if (!this.incidentDate) return '';
            const d = new Date(this.incidentDate + 'T00:00:00');
            if (isNaN(d.getTime())) return '';
            return d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        },
        shortDate() {
            if (!this.incidentDate) return '';
            const d = new Date(this.incidentDate + 'T00:00:00');
            if (isNaN(d.getTime())) return '';
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },
        onVicinity() {
            this.incidentLocation = '';
            this.customLocation = false;
            this.mapOpen = false;
        },
        onFiles(event) {
            this.files = Array.from(event.target.files || []).map((file) => ({
                name: file.name,
                size: (file.size / 1024 / 1024).toFixed(2),
                big: file.size > 50 * 1024 * 1024
            }));
        },
        removeFile(index) {
            const input = this.$refs.evidence;
            if (!input) return;
            const dt = new DataTransfer();
            Array.from(input.files || []).forEach((file, i) => { if (i !== index) dt.items.add(file); });
            input.files = dt.files;
            this.onFiles({ target: input });
        },
        filledLinks() {
            return this.links.map((link) => link.trim()).filter(Boolean);
        },
        nameError(value) {
            const name = (value || '').trim();
            if (!name) return 'Full name is required.';
            if (name.length < 4) return 'Full name must be at least 4 characters.';
            if (name.split(/\s+/).filter(Boolean).length < 2) return 'Enter both first and last name.';
            if (!/[aeiouAEIOU]/.test(name)) return 'Enter a valid name.';
            return '';
        },
        addressError(value) {
            const address = (value || '').trim();
            if (!address) return 'Address is required.';
            if (address.length < 10) return 'Address must be at least 10 characters.';
            if (/([a-zA-Z])\1{4,}/.test(address)) return 'Enter a valid address.';
            return '';
        },
        contactError(value) {
            const contact = (value || '').trim();
            if (!contact || contact === 'Not Disclosed') return 'Contact number is required.';
            if (!contact.startsWith('09') || contact.length !== 11) return 'Contact number must be 11 digits and start with 09.';
            if (/^(\d)\1{10}$/.test(contact)) return 'Enter a valid contact number.';
            return '';
        },
        stepError(n) {
            if (n === 1) {
                if (this.isAnonymous && !this.shareSealed) return '';
                const name = this.isAnonymous ? this.sealedName : this.complainantName;
                const contact = this.isAnonymous ? this.sealedContact : this.contact;
                const role = this.isAnonymous ? this.sealedType : this.role;
                return this.nameError(name) || (!this.isAnonymous ? this.addressError(this.address) : '') || this.contactError(contact) || (!role ? 'Select your role.' : '');
            }
            if (n === 2) {
                if (!(this.respondentName || '').trim()) return 'Enter the respondent name.';
                if (this.unknownRespondent && (this.respondentAddress || '').trim().length < 20) return 'Describe the person in at least 20 characters.';
                return '';
            }
            if (n === 3) {
                if (this.dateError) return 'Fix the incident date first.';
                const missing = [];
                if (!(this.title || '').trim()) missing.push('Title');
                if ((this.description || '').trim().length < 20) missing.push('Description (20 characters minimum)');
                if (!this.incidentDate) missing.push('Date');
                if (!(this.incidentLocation || '').trim()) missing.push('Location');
                if (this.type === 'other' && !(this.otherDetail || '').trim()) missing.push('Complaint type');
                return missing.length ? 'Missing: ' + missing.join(', ') + '.' : '';
            }
            return '';
        },
        next() {
            const message = this.stepError(this.step);
            if (message) {
                window.toast({ title: 'Incomplete information', description: message, variant: 'destructive' });
                return;
            }
            this.step = Math.min(this.step + 1, 5);
        },
        guardSubmit(event) {
            const oversize = Array.from(this.$refs.evidence?.files || []).find((file) => file.size > 50 * 1024 * 1024);
            if (oversize) {
                event.preventDefault();
                window.toast({ title: 'File too large', description: oversize.name + ' is over 50 MB. Use an external link instead.', variant: 'destructive' });
                return;
            }
            if (this.step < 5) {
                event.preventDefault();
                this.next();
                return;
            }
            for (const n of [1, 2, 3]) {
                const message = this.stepError(n);
                if (message) {
                    event.preventDefault();
                    this.step = n;
                    window.toast({ title: 'Incomplete information', description: message, variant: 'destructive' });
                    return;
                }
            }
        },
        init() { this.syncTitle(); }
    }"
>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Formal complaint filing</h1>
        <p class="text-sm text-gray-400 mt-1">
            Complete all steps to file a formal complaint with the DEIU office.
        </p>
    </div>

    <form method="POST" action="/complaints" enctype="multipart/form-data" @submit="guardSubmit($event)">
        @csrf
        <input type="hidden" name="is_anonymous" :value="isAnonymous ? '1' : '0'">

        <div class="flex flex-col lg:flex-row gap-6 items-start">
            <div class="w-full lg:w-[60%] flex-shrink-0">
                <div class="flex items-start gap-3 p-4 rounded-2xl relative mb-4" style="background: var(--privacy-bg); border: 1px solid var(--card-outline)">
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

                <div class="rounded-xl border border-[#1D9E75]/20 bg-white p-4 mb-6">
                    <div class="flex items-start gap-2.5 mb-3">
                        <div class="rounded-lg p-1.5 bg-[#F0FDF4] shrink-0">
                            <x-lucide-help-circle class="h-4 w-4 text-[#1D9E75]" />
                        </div>
                        <div>
                            <h2 class="font-semibold text-gray-900 text-sm">New to filing? Read this short FAQ</h2>
                            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                Confidentiality, anonymity, what happens after you submit, and how to rate the process once your case is resolved.
                            </p>
                        </div>
                    </div>
                    <div class="w-full">
                        @foreach ($faqs as $index => $item)
                            <div class="border-b border-gray-100">
                                <button type="button" class="flex w-full items-center justify-between py-3 text-left text-sm font-medium text-gray-800 hover:text-[#14532D]" @click="faq = faq === {{ $index }} ? null : {{ $index }}">
                                    <span>{{ $item['q'] }}</span>
                                    <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="faq === {{ $index }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <div x-show="faq === {{ $index }}" x-cloak class="pb-3 text-xs text-gray-600 leading-relaxed">{{ $item['a'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center">
                        @foreach ($steps as $index => $label)
                            @php $n = $index + 1; @endphp
                            <div class="flex flex-col items-center min-w-0">
                                <div
                                    class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-medium transition-colors duration-150 flex-shrink-0"
                                    :class="step > {{ $n }} ? 'bg-[#1D9E75] text-white' : (step === {{ $n }} ? 'bg-green-700 text-white ring-2 ring-[#1D9E75]/40' : 'bg-gray-100 text-gray-400 border border-gray-200')"
                                >
                                    <svg x-show="step > {{ $n }}" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    <span x-show="step <= {{ $n }}">{{ $n }}</span>
                                </div>
                                <span class="hidden sm:block text-xs mt-1 text-center whitespace-nowrap" :class="step === {{ $n }} ? 'text-gray-900 font-medium' : 'text-gray-400'">{{ $label }}</span>
                                <span x-show="step === {{ $n }}" x-cloak class="sm:hidden text-xs mt-1 text-center text-gray-900 font-medium whitespace-nowrap">{{ $label }}</span>
                            </div>
                            @if ($index < count($steps) - 1)
                                <div class="flex-1 h-px mx-1 sm:mx-2 mb-4 sm:mb-5" :class="step > {{ $n }} ? 'bg-[#1D9E75]' : 'bg-gray-200'"></div>
                            @endif
                        @endforeach
                    </div>
                    <div class="mt-3 bg-gray-100 rounded-full h-1">
                        <div class="bg-[#1D9E75] h-1 rounded-full transition-all duration-300" :style="'width: ' + (((step - 1) / 4) * 100) + '%'"></div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <div class="mb-5 pb-4 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900" x-text="labels[step - 1]"></p>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="hints[step - 1]"></p>
                    </div>

                    <div x-show="step === 1" class="space-y-4">
                        <div class="flex items-start gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <input id="anonymous" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#1D9E75] focus:ring-[#1D9E75]" x-model="isAnonymous" @change="onAnonymous()">
                            <div>
                                <label for="anonymous" class="text-sm font-medium text-gray-900 cursor-pointer">Submit anonymously</label>
                                <p class="text-xs text-blue-700 mt-0.5">Your identity will not be disclosed to the respondent. Investigators will reference your case ID only.</p>
                                <p class="text-xs text-blue-700 mt-1.5">All submitted reports stay confidential. For fairness, you can still share a sealed identity with DEIU below — the respondent will not see it.</p>
                            </div>
                        </div>

                        <div x-show="isAnonymous" x-cloak class="p-3 bg-indigo-50 border border-indigo-200 rounded-lg space-y-3">
                            <div class="flex items-start gap-3">
                                <input id="sealed-identity" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#1D9E75] focus:ring-[#1D9E75]" x-model="shareSealed">
                                <div>
                                    <label for="sealed-identity" class="text-sm font-medium text-gray-900 cursor-pointer">Share a sealed identity with DEIU only</label>
                                    <p class="text-xs text-indigo-800 mt-0.5">Recommended for fairness. DEIU/CODI can contact you and process both sides fairly if this goes to investigation. The respondent still will not see your name.</p>
                                    <p class="text-xs text-indigo-700 mt-1">Uncheck only if you want to file fully anonymous. Follow-up and formal hearings may then be limited.</p>
                                </div>
                            </div>
                            <div x-show="shareSealed" x-cloak class="space-y-3 pt-1">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Sealed full name *</label>
                                    <input name="sealed_name" x-model="sealedName" @input="lettersOnly($event, 'sealedName')" placeholder="Name for DEIU only" class="w-full text-sm px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent bg-white text-gray-900 border-gray-300">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 block mb-1.5">I am a *</label>
                                    <select name="sealed_type" x-model="sealedType" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                                        <option value="">Select your role</option>
                                        <option value="student">Student</option>
                                        <option value="faculty">Faculty</option>
                                        <option value="staff">Staff</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700 block mb-1.5">Sealed contact number *</label>
                                    <div class="relative">
                                        <x-lucide-phone class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                                        <input name="sealed_contact" :value="sealedContact" @input="onContact($event, 'sealedContact')" placeholder="09XXXXXXXXX" maxlength="11" inputmode="numeric" class="w-full text-sm pl-10 pr-3 py-2.5 border rounded-lg focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent bg-white text-gray-900 border-gray-300">
                                    </div>
                                    <p class="text-xs text-indigo-700 mt-1.5">Used only by DEIU/CODI to contact you. Never shown to the respondent.</p>
                                </div>
                            </div>
                        </div>

                        <div x-show="!isAnonymous" x-cloak class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">Full name *</label>
                                <input name="complainant_name" autocomplete="name" :value="complainantName" @input="lettersOnly($event, 'complainantName')" placeholder="Enter your full legal name" class="w-full text-sm px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent bg-white text-gray-900 border-gray-300">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">Complete address *</label>
                                <textarea name="complainant_address" x-model="address" placeholder="Street, barangay, city" class="w-full text-sm px-3 py-2 border rounded-lg resize-y min-h-[72px] leading-relaxed focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent bg-white text-gray-900 border-gray-300"></textarea>
                                <div class="flex justify-end mt-1"><span class="text-xs text-gray-500" x-text="(address || '').length + ' chars'"></span></div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">I am a *</label>
                                <select name="complainant_type" x-model="role" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                                    <option value="">Select your role</option>
                                    <option value="student">Student</option>
                                    <option value="faculty">Faculty</option>
                                    <option value="staff">Staff</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">Contact number *</label>
                                <div class="relative">
                                    <x-lucide-phone class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                                    <input name="complainant_contact" :value="contact" @input="onContact($event, 'contact')" placeholder="09XXXXXXXXX" maxlength="11" inputmode="numeric" class="w-full text-sm pl-10 pr-3 py-2.5 border rounded-lg focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent transition-colors bg-white text-gray-900" :class="validMobile(contact) ? 'border-[#1D9E75]/40 bg-[#1D9E75]/5' : 'border-gray-300'">
                                    <div x-show="validMobile(contact)" class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="h-4 w-4 text-[#1D9E75]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </div>
                                <p x-show="contact && contact.length >= 2 && !contact.startsWith('09')" class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                                    <x-lucide-triangle-alert class="h-3 w-3" />
                                    Must start with <strong>09</strong> (Philippine mobile).
                                </p>
                                <p x-show="contact.startsWith('09') && contact.length < 11" class="text-xs text-amber-600 mt-1.5 flex items-center gap-1">
                                    <x-lucide-triangle-alert class="h-3 w-3" />
                                    <span x-text="(11 - contact.length) + ' more digit(s) needed (09XXXXXXXXX).'"></span>
                                </p>
                                <p x-show="validMobile(contact)" class="text-xs text-[#1D9E75] mt-1.5">✓ Valid PH mobile number</p>
                                <p x-show="!contact" class="text-xs text-gray-500 mt-1.5">Must start with 09 · 11 digits total (e.g. 09171234567)</p>
                            </div>
                        </div>
                        <input type="hidden" name="complainant_name" :value="complainantName" :disabled="!isAnonymous">
                    </div>

                    <div x-show="step === 2" x-cloak class="space-y-4">
                        <div class="flex items-start gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <input id="unknownRespondent" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#1D9E75] focus:ring-[#1D9E75]" x-model="unknownRespondent" @change="onUnknown()">
                            <label for="unknownRespondent" class="text-sm font-medium text-gray-900 cursor-pointer">I don't know the respondent's identity</label>
                        </div>
                        <div x-show="!unknownRespondent" x-cloak class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">Full name of respondent *</label>
                                <input name="respondent_name" :value="respondentName" @input="lettersOnly($event, 'respondentName')" placeholder="Enter respondent's full name" :disabled="unknownRespondent" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">Department <span class="font-normal text-gray-400">(optional)</span></label>
                                <select name="respondent_department" x-model="respondentDepartment" :disabled="unknownRespondent" class="w-full text-sm border-gray-300 h-10 px-3 py-2 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                                    <option value="">Select...</option>
                                    <option value="not-specified">Not specified</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department }}">{{ $department }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">Respondent is a *</label>
                                <select name="respondent_type" x-model="respondentType" class="w-full text-sm border-gray-300 h-10 px-3 py-2 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                                    <option value="">Select respondent type</option>
                                    <option value="student">Student</option>
                                    <option value="faculty">Faculty</option>
                                    <option value="staff">Staff</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="respondent_name" :value="respondentName" :disabled="!unknownRespondent">
                        <input type="hidden" name="respondent_department" value="Unknown" :disabled="!unknownRespondent">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-sm font-medium text-gray-700">
                                    Physical description or identifying details
                                    <span x-show="unknownRespondent" class="text-red-500 ml-1">*</span>
                                </label>
                                <span x-show="unknownRespondent" class="text-xs font-medium" :class="respondentAddress.trim().length === 0 ? 'text-gray-400' : (respondentAddress.trim().length < 20 ? 'text-red-500' : 'text-[#1D9E75]')" x-text="respondentAddress.trim().length >= 20 ? (respondentAddress.trim().length + ' chars ✓') : (respondentAddress.trim().length + ' / 20 minimum')"></span>
                            </div>
                            <textarea name="respondent_address" x-model="respondentAddress" rows="3" :placeholder="unknownRespondent ? 'Describe the person — approximate age, height, clothing, where they are usually seen, etc.' : 'Optional — any additional identifying details'" class="w-full text-sm px-3 py-2 border rounded-lg bg-white text-gray-900 resize-y min-h-[72px] leading-relaxed focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent" :class="unknownRespondent && respondentAddress.trim().length > 0 && respondentAddress.trim().length < 20 ? 'border-red-400' : 'border-gray-300'"></textarea>
                            <p x-show="unknownRespondent" class="text-xs mt-1" :class="respondentAddress.trim().length > 0 && respondentAddress.trim().length < 20 ? 'text-red-500' : 'text-gray-400'" x-text="respondentAddress.trim().length < 20 ? 'Please provide at least 20 characters of description to help identify the respondent.' : 'Since the identity is unknown, please describe any details that may help identify the respondent.'"></p>
                        </div>
                    </div>

                    <div x-show="step === 3" x-cloak class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 block mb-1.5">Type of complaint *</label>
                            <select name="type" x-model="type" @change="onType()" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                                @foreach ($categories as $category)
                                    <option value="{{ $category['value'] }}">{{ $category['label'] }}</option>
                                @endforeach
                            </select>
                            <div x-show="type === 'sexual_harassment'" x-cloak class="mt-3">
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">Degree of harassment * <span class="ml-2 font-normal text-gray-400">(Based on RA 11313)</span></label>
                                <select name="harassment_degree" x-model="harassmentDegree" class="w-full text-sm border-gray-300 h-10 text-left px-3 py-2 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                                    <option value="">Select degree</option>
                                    <option value="light">Light — Light gestures, jokes, or comments</option>
                                    <option value="severe">Severe — Unwelcome touching, advances</option>
                                    <option value="grave">Grave — Sexual assault, rape</option>
                                </select>
                            </div>
                            <input x-show="type === 'other'" x-cloak type="text" name="other_type_detail" x-model="otherDetail" @input="titleManual = false; syncTitle()" placeholder="Please specify the type of complaint" class="mt-2 w-full text-sm px-3 py-2 border border-[#1D9E75]/60 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                            <div class="text-xs text-blue-700 mt-1 bg-blue-50 border border-blue-100 rounded px-2 py-1.5 flex items-start gap-1.5">
                                <svg class="h-3.5 w-3.5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                <p>This complaint form covers offenses under the <a href="https://www.officialgazette.gov.ph/downloads/2019/04apr/20190417-RA-11313-RRD.pdf" target="_blank" rel="noopener noreferrer" class="underline font-semibold hover:text-blue-900">Safe Spaces Act (RA 11313)</a> and the <a href="https://www.officialgazette.gov.ph/1995/02/14/republic-act-no-7877/" target="_blank" rel="noopener noreferrer" class="underline font-semibold hover:text-blue-900">Anti-Sexual Harassment Act (RA 7877)</a>.</p>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 block mb-1.5">Complaint title * <span class="ml-2 font-normal text-gray-400">Filled from type of complaint — editable</span></label>
                            <input name="title" x-model="title" @input="titleManual = true" placeholder="Title based on selected complaint type" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Updates automatically when you change the complaint type. You can edit the title anytime.</p>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-sm font-medium text-gray-700">Description *</label>
                            </div>
                            <textarea name="description" x-model="description" placeholder="Describe what happened..." rows="5" class="w-full text-sm px-3 py-2.5 border rounded-lg bg-white text-gray-900 resize-y min-h-[120px] leading-relaxed focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent" :class="description.trim().length > 0 && description.trim().length < 20 ? 'border-red-400' : (description.trim().length >= 20 ? 'border-[#1D9E75]' : 'border-gray-300')"></textarea>
                            <div class="flex items-center justify-between mt-1.5">
                                <div>
                                    <p x-show="description.trim().length > 0 && description.trim().length < 20" class="text-xs text-red-500 flex items-center gap-1">
                                        <x-lucide-triangle-alert class="h-3 w-3" />
                                        <span x-text="(20 - description.trim().length) + ' more characters needed'"></span>
                                    </p>
                                </div>
                                <span class="text-xs font-medium" :class="description.trim().length === 0 ? 'text-gray-400' : (description.trim().length < 20 ? 'text-red-500' : 'text-[#1D9E75]')" x-text="description.trim().length >= 20 ? (description.trim().length + ' chars ✓') : (description.trim().length + ' / 20 minimum')"></span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Include who was involved, what happened, and any relevant context.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="w-full">
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">Date of incident *</label>
                                <div class="relative">
                                    <x-lucide-calendar class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                                    <input type="date" name="incident_date" :value="incidentDate" @change="onDate($event)" max="{{ $today }}" class="w-full text-sm pl-10 pr-3 py-2.5 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent transition-colors" :class="dateError ? 'border-red-400 focus:ring-red-500' : (incidentDate ? 'border-[#1D9E75]/40 bg-[#1D9E75]/5' : 'border-gray-300')">
                                    <div x-show="incidentDate" class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="h-4 w-4 text-[#1D9E75]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </div>
                                <p x-show="dateError" x-cloak class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                                    <x-lucide-triangle-alert class="h-3 w-3" />
                                    <span x-text="dateError"></span>
                                </p>
                                <p x-show="incidentDate && !dateError" class="text-xs text-gray-500 mt-1.5 break-words" x-text="formattedDate()"></p>
                            </div>
                            <div class="w-full">
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">Time of incident <span class="font-normal text-gray-400">(optional)</span></label>
                                <div class="relative">
                                    <x-lucide-clock class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                                    <select name="incident_time" x-model="incidentTime" class="w-full text-sm pl-10 pr-3 py-2.5 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent" :class="incidentTime ? 'border-[#1D9E75]/40 bg-[#1D9E75]/5' : 'border-gray-300'">
                                        <option value="">Select time period</option>
                                        <option value="AM">Morning (AM)</option>
                                        <option value="PM">Afternoon/Evening (PM)</option>
                                    </select>
                                </div>
                                <p x-show="incidentTime" class="text-xs text-gray-500 mt-1.5" x-text="incidentTime === 'AM' ? 'Morning (12:00 AM - 11:59 AM)' : 'Afternoon/Evening (12:00 PM - 11:59 PM)'"></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="w-full">
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">Where it happened *</label>
                                <select name="location_vicinity" x-model="vicinity" @change="onVicinity()" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                                    <option value="inside">Inside college vicinity</option>
                                    <option value="outside">Outside college vicinity</option>
                                    <option value="online">Online / Digital platform</option>
                                </select>
                            </div>
                            <div class="w-full">
                                <label class="text-sm font-medium text-gray-700 block mb-1.5">
                                    <span x-text="vicinity === 'online' ? 'Platform *' : 'Location details'"></span>
                                    <span x-show="vicinity === 'outside'" class="text-red-500">*</span>
                                </label>
                                <select x-show="vicinity === 'online'" name="incident_location" x-model="incidentLocation" :disabled="vicinity !== 'online'" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                                    <option value="">Select platform</option>
                                    @foreach ($platforms as $platform)
                                        <option value="{{ $platform }}">{{ $platform }}</option>
                                    @endforeach
                                </select>
                                <div x-show="vicinity === 'inside'" x-cloak class="space-y-2">
                                    <select x-show="!customLocation" name="incident_location" x-model="incidentLocation" :disabled="vicinity !== 'inside' || customLocation" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                                        <option value="">Select location</option>
                                        @forelse (($locationGroups ?? collect()) as $floor => $rooms)
                                            <optgroup label="{{ $floor }}">
                                                @foreach ($rooms as $room)
                                                    <option value="{{ $room->name }}">{{ $room->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @empty
                                            <option value="__no_locations__" disabled>No locations available — use custom location</option>
                                        @endforelse
                                    </select>
                                    <input x-show="customLocation" name="incident_location" x-model="incidentLocation" :disabled="vicinity !== 'inside' || !customLocation" placeholder="Enter custom location" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                                    <button type="button" @click="customLocation = !customLocation; incidentLocation = ''" class="text-xs text-[#1D9E75] hover:text-[#178F65] underline" x-text="customLocation ? 'Use location list' : 'Enter custom location'"></button>
                                </div>
                                <input x-show="vicinity === 'outside'" x-cloak name="incident_location" x-model="incidentLocation" :disabled="vicinity !== 'outside'" placeholder="Barangay, city, or street" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                            </div>
                        </div>
                        <div x-show="vicinity === 'outside'" x-cloak class="border border-gray-200 rounded-lg p-4">
                            <button type="button" @click="mapOpen = !mapOpen" class="flex items-center justify-between w-full text-left">
                                <div class="flex items-center gap-2">
                                    <x-lucide-map-pin class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm font-medium text-gray-700">Pin exact location on map</span>
                                    <span class="text-xs text-gray-400">(optional)</span>
                                </div>
                                <svg class="h-5 w-5 text-gray-400 transition-transform" :class="mapOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div x-show="mapOpen" x-cloak class="mt-4">
                                <input x-model="mapQuery" name="map_address" placeholder="Search address (e.g., Gordon College Library)" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                            </div>
                        </div>
                        <div x-show="vicinity === 'inside'" x-cloak class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs text-blue-700"><strong>Inside college vicinity:</strong> No need to pin exact location since the incident occurred within the campus.</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 block mb-1.5">Witnesses <span class="font-normal text-gray-400">(optional)</span></label>
                            <textarea name="witnesses" x-model="witnesses" placeholder="List any witnesses with their contact information, if available." rows="2" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 resize-y min-h-[60px] leading-relaxed focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent"></textarea>
                            <div class="flex justify-end mt-1"><span class="text-xs text-gray-500" x-text="(witnesses || '').length + ' chars'"></span></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 block mb-1.5">Additional information <span class="font-normal text-gray-400">(optional)</span></label>
                            <textarea name="statement_of_facts" x-model="statementOfFacts" placeholder="Any other relevant context or background." rows="2" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 resize-y min-h-[60px] leading-relaxed focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent"></textarea>
                            <div class="flex justify-end mt-1"><span class="text-xs text-gray-500" x-text="(statementOfFacts || '').length + ' chars'"></span></div>
                        </div>
                    </div>

                    <div x-show="step === 4" x-cloak class="space-y-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start gap-2 mb-2">
                                <x-lucide-file-text class="h-4 w-4 text-blue-600 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-sm font-medium text-blue-800">Written Sworn Statement <span class="font-normal text-blue-600">(Optional)</span></p>
                                    <p class="text-xs text-blue-700 mt-0.5">You may submit a written sworn statement (sinumpaang salaysay) as supporting evidence if available. This can strengthen your complaint but is not required to proceed.</p>
                                </div>
                            </div>
                        </div>
                        <div class="border-2 border-dashed border-gray-200 rounded-lg p-8 text-center">
                            <x-lucide-upload class="mx-auto h-8 w-8 text-gray-300 mb-3" />
                            <p class="text-sm font-medium text-gray-700">Add supporting files</p>
                            <p class="text-xs text-gray-400 mt-1">Upload files or provide external links</p>
                            <p class="text-xs font-medium text-blue-600 mt-0.5">For files larger than 50MB, use external links (Google Drive, Dropbox, etc.)</p>
                            <p class="text-xs font-semibold text-amber-600 mt-1.5 bg-amber-50 border border-amber-200 rounded px-2 py-1 inline-block">⚠️ Important: Make sure external links are set to "Public" or "Anyone with link can view"</p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center mt-4">
                                <button type="button" @click="$refs.evidence.click()" class="bg-white hover:bg-gray-50 text-gray-600 text-sm px-4 py-2 border border-gray-200 rounded-lg transition-colors duration-150">Browse files (Direct Upload)</button>
                                <button type="button" @click="evidenceOpen = true" class="bg-[#1D9E75] hover:bg-[#178F65] text-white text-sm px-4 py-2 rounded-lg transition-colors duration-150 flex items-center gap-2 justify-center">
                                    <x-lucide-cloud class="h-4 w-4" />
                                    Add Files or Links
                                </button>
                            </div>
                            <input x-ref="evidence" name="evidence[]" type="file" multiple accept="image/*,video/mp4,video/webm,video/mpeg,.pdf,.doc,.docx" class="hidden" @change="onFiles($event)">
                        </div>
                        <div x-show="files.length > 0 || filledLinks().length > 0" x-cloak class="space-y-4">
                            <div x-show="files.length > 0">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2" x-text="files.length + ' file' + (files.length === 1 ? '' : 's') + ' selected'"></p>
                                <div class="space-y-2">
                                    <template x-for="(file, index) in files" :key="file.name + index">
                                        <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <x-lucide-file-text class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                                <span class="text-sm text-gray-700 truncate" x-text="file.name"></span>
                                                <span class="text-xs flex-shrink-0" :class="file.big ? 'text-red-500 font-medium' : 'text-gray-400'" x-text="'(' + file.size + ' MB)'"></span>
                                            </div>
                                            <button type="button" @click="removeFile(index)" class="text-xs text-red-600 hover:text-red-700 px-2 py-1 border border-red-200 rounded bg-red-50 hover:bg-red-100 transition-colors duration-150 flex-shrink-0 ml-2">Remove</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 space-y-1">
                            <p class="text-xs font-medium text-gray-600">Accepted file types:</p>
                            <p class="text-xs text-gray-500">• Images: JPG, PNG, GIF, WEBP, BMP</p>
                            <p class="text-xs text-gray-500">• Videos: MP4, WEBM, MPEG</p>
                            <p class="text-xs text-gray-500">• Documents: PDF, DOC, DOCX</p>
                            <p class="text-xs text-gray-500">• Maximum 50 MB per file</p>
                            <p class="text-xs text-blue-600 mt-1">You may include a <strong>written sworn statement</strong> if available.</p>
                        </div>
                    </div>

                    <div x-show="step === 5" x-cloak class="space-y-4">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Review the details below before submitting. Once submitted, your complaint will be forwarded to the DEIU office for processing.</p>
                        </div>
                        <div x-show="isAnonymous" class="flex items-start gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <x-lucide-shield class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-blue-900" x-text="shareSealed ? 'Anonymous to the respondent · Sealed identity for DEIU' : 'Fully anonymous submission'"></p>
                                <p class="text-xs text-blue-700 mt-0.5" x-text="shareSealed ? 'The respondent will not see your name. DEIU/CODI can use your sealed identity to contact you and process the case fairly.' : 'Your personal information will not be disclosed to the respondent. Follow-up may be limited without a sealed identity.'"></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-green-50 border border-[#1D9E75]/25 rounded-lg">
                            <x-lucide-shield class="w-4 h-4 text-[#1D9E75] flex-shrink-0 mt-0.5" />
                            <p class="text-xs text-[#166534] leading-relaxed">Reminder: all submitted reports are kept confidential. After you submit, track acceptance and every move in My Cases. When the case is resolved, you can rate how it was handled.</p>
                        </div>
                        <div class="divide-y divide-gray-100 border border-gray-200 rounded-lg overflow-hidden">
                            <div class="p-4 bg-white">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Complainant</p>
                                <p class="text-sm text-gray-700" x-text="complainantName || '—'"></p>
                                <div x-show="!isAnonymous">
                                    <p class="text-sm text-gray-500" x-text="address"></p>
                                    <p class="text-sm text-gray-500" x-text="contact"></p>
                                </div>
                                <p x-show="isAnonymous && shareSealed" class="text-xs text-indigo-700 mt-2" x-text="'Sealed with DEIU: ' + sealedName + (sealedContact ? ' · ' + sealedContact : '')"></p>
                            </div>
                            <div class="p-4 bg-white">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Respondent</p>
                                <p class="text-sm text-gray-700" x-text="respondentName || '—'"></p>
                                <p class="text-sm text-gray-500" x-text="respondentDepartment || ''"></p>
                            </div>
                            <div class="p-4 bg-white">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Incident</p>
                                <p class="text-sm font-medium text-gray-900" x-text="title"></p>
                                <p class="text-sm text-gray-500 mt-1" x-text="categoryLabel(type) + ' · ' + (incidentDate || '')"></p>
                                <p class="text-sm text-gray-500" x-text="incidentLocation"></p>
                                <p class="text-sm text-gray-700 mt-2 leading-relaxed" x-text="description"></p>
                            </div>
                            <div class="p-4 bg-white">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Supporting files</p>
                                <p x-show="files.length === 0 && filledLinks().length === 0" class="text-sm text-gray-400">No files or links provided</p>
                                <div x-show="files.length > 0" class="space-y-1">
                                    <p class="text-sm text-gray-600" x-text="files.length + ' file(s) attached'"></p>
                                    <template x-for="file in files" :key="file.name">
                                        <p class="text-xs text-gray-500" x-text="file.name + ' (' + file.size + ' MB)'"></p>
                                    </template>
                                </div>
                                <div x-show="filledLinks().length > 0" class="mt-2">
                                    <p class="text-sm text-gray-600" x-text="filledLinks().length + ' external link(s)'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <button type="button" x-show="step > 1" @click="step = Math.max(step - 1, 1)" class="bg-white hover:bg-gray-50 text-gray-600 text-sm px-4 py-2 border border-gray-200 rounded-lg transition-colors duration-150">Back</button>
                    </div>
                    <div>
                        <button type="button" x-show="step < 5" @click="next()" :disabled="!!stepError(step)" class="bg-[#1D9E75] hover:bg-[#178F65] disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors duration-150">Continue</button>
                        <button type="submit" x-show="step === 5" x-cloak :disabled="!!(stepError(1) || stepError(2) || stepError(3))" class="bg-[#1D9E75] hover:bg-[#178F65] disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors duration-150 inline-flex items-center gap-2">
                            <x-lucide-send class="h-4 w-4" />
                            Submit complaint
                        </button>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block lg:w-[40%] flex-shrink-0">
                <div class="sticky top-6 space-y-4">
                    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                        <h3 class="text-sm font-bold text-gray-900 mb-3">Your Progress</h3>
                        <p class="text-xs text-gray-500 mb-4">Step <span x-text="step"></span> of <span x-text="labels.length"></span></p>
                        <div class="space-y-2.5">
                            <div class="flex items-start gap-2">
                                <div x-show="complainantName || isAnonymous" class="w-4 h-4 rounded-full bg-[#1D9E75]/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-2.5 h-2.5 text-[#1D9E75]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div x-show="!(complainantName || isAnonymous)" class="w-4 h-4 rounded border-2 border-gray-300 flex-shrink-0 mt-0.5"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-700">Full Name</p>
                                    <p x-show="isAnonymous" class="text-xs text-gray-500 italic" x-text="shareSealed ? 'Anonymous · sealed identity for DEIU' : 'Anonymous'"></p>
                                    <p x-show="!isAnonymous && complainantName" class="text-xs text-[#1D9E75] truncate" x-text="complainantName"></p>
                                    <p x-show="!isAnonymous && !complainantName" class="text-xs text-gray-400">Not filled</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <div x-show="address" class="w-4 h-4 rounded-full bg-[#1D9E75]/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-2.5 h-2.5 text-[#1D9E75]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div x-show="!address" class="w-4 h-4 rounded border-2 border-gray-300 flex-shrink-0 mt-0.5"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-700">Complete Address</p>
                                    <p x-show="address" class="text-xs text-[#1D9E75] line-clamp-2" x-text="address"></p>
                                    <p x-show="!address" class="text-xs text-gray-400">Not filled</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <div x-show="contact" class="w-4 h-4 rounded-full bg-[#1D9E75]/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-2.5 h-2.5 text-[#1D9E75]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div x-show="!contact" class="w-4 h-4 rounded border-2 border-gray-300 flex-shrink-0 mt-0.5"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-700">Contact Number</p>
                                    <p x-show="contact" class="text-xs text-[#1D9E75]" x-text="contact"></p>
                                    <p x-show="!contact" class="text-xs text-gray-400">Not filled</p>
                                </div>
                            </div>
                            <div x-show="step >= 2" class="flex items-start gap-2">
                                <div x-show="respondentName" class="w-4 h-4 rounded-full bg-[#1D9E75]/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-2.5 h-2.5 text-[#1D9E75]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div x-show="!respondentName" class="w-4 h-4 rounded border-2 border-gray-300 flex-shrink-0 mt-0.5"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-700">Respondent Info</p>
                                    <p x-show="respondentName" class="text-xs text-[#1D9E75] truncate" x-text="respondentName"></p>
                                    <p x-show="!respondentName" class="text-xs text-gray-400">Not filled</p>
                                </div>
                            </div>
                            <div x-show="step >= 3" class="flex items-start gap-2">
                                <div x-show="description && incidentDate" class="w-4 h-4 rounded-full bg-[#1D9E75]/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-2.5 h-2.5 text-[#1D9E75]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div x-show="!(description && incidentDate)" class="w-4 h-4 rounded border-2 border-gray-300 flex-shrink-0 mt-0.5"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-700">Incident Details</p>
                                    <div x-show="description && incidentDate">
                                        <p class="text-xs text-[#1D9E75] font-medium" x-text="categoryLabel(type) || 'Incident'"></p>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="shortDate() + (incidentTime ? (' • ' + (incidentTime === 'AM' ? 'Morning' : 'Afternoon/Evening')) : '')"></p>
                                    </div>
                                    <p x-show="!(description && incidentDate)" class="text-xs text-gray-400">Not filled</p>
                                </div>
                            </div>
                            <div x-show="step >= 4" class="flex items-start gap-2">
                                <div x-show="files.length > 0 || filledLinks().length > 0" class="w-4 h-4 rounded-full bg-[#1D9E75]/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-2.5 h-2.5 text-[#1D9E75]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div x-show="!(files.length > 0 || filledLinks().length > 0)" class="w-4 h-4 rounded border-2 border-gray-300 flex-shrink-0 mt-0.5"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-700">Supporting Files</p>
                                    <p x-show="files.length > 0 || filledLinks().length > 0" class="text-xs text-[#1D9E75]" x-text="files.length + ' file(s)' + (filledLinks().length > 0 ? ', ' + filledLinks().length + ' link(s)' : '')"></p>
                                    <p x-show="!(files.length > 0 || filledLinks().length > 0)" class="text-xs text-gray-400">No files yet</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 border border-[#1D9E75]/25 rounded-xl p-4">
                        <div class="flex items-start gap-2 mb-2">
                            <x-lucide-shield class="h-4 w-4 text-[#1D9E75] flex-shrink-0 mt-0.5" />
                            <h4 class="text-xs font-bold text-[#0D5F47]">Your information is safe</h4>
                        </div>
                        <ul class="space-y-1.5 text-xs text-[#178F65]">
                            <li class="flex items-start gap-1.5"><span class="text-[#1D9E75] mt-0.5">•</span><span>All submitted reports are kept confidential</span></li>
                            <li class="flex items-start gap-1.5"><span class="text-[#1D9E75] mt-0.5">•</span><span>Your identity will not be disclosed to the respondent without your consent</span></li>
                            <li class="flex items-start gap-1.5"><span class="text-[#1D9E75] mt-0.5">•</span><span>You can stay anonymous and still share a sealed identity with DEIU for fairness</span></li>
                            <li class="flex items-start gap-1.5"><span class="text-[#1D9E75] mt-0.5">•</span><span>Track acceptance and every move in My Cases after you submit</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="evidenceOpen" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @keydown.escape.window="evidenceOpen = false">
            <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl" @click.outside="evidenceOpen = false">
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between rounded-t-2xl">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Submit Evidence</h2>
                        <p class="text-sm text-gray-500 mt-1">Upload files or provide external links to evidence</p>
                    </div>
                    <button type="button" @click="evidenceOpen = false" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <x-lucide-x class="w-5 h-5 text-gray-500" />
                    </button>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                        <x-lucide-circle-alert class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Why separate evidence submission?</p>
                            <p>For files larger than 50MB, use external links (Google Drive, Dropbox, etc.) to avoid upload issues. This ensures your evidence is always accessible.</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="text-sm font-semibold text-gray-900">Upload Files (Max 50MB per file)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 hover:border-[#1D9E75] transition-colors">
                            <button type="button" @click="$refs.evidence.click()" class="flex flex-col items-center justify-center w-full">
                                <x-lucide-upload class="w-10 h-10 text-gray-400 mb-3" />
                                <p class="text-sm font-medium text-gray-700 mb-1">Click to upload files</p>
                                <p class="text-xs text-gray-500">Images, videos, PDFs, documents</p>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-semibold text-gray-900">External Links (For large files)</label>
                            <button type="button" @click="links.push('')" class="text-xs font-medium text-[#1D9E75] hover:text-[#178F65] transition-colors">+ Add Link</button>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 flex items-start gap-2">
                            <x-lucide-circle-alert class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
                            <p class="text-xs text-amber-800"><strong>Important:</strong> Make sure your links are set to "Public" or "Anyone with the link can view" before submitting. Private links cannot be accessed by investigators.</p>
                        </div>
                        <template x-for="(link, index) in links" :key="index">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 relative">
                                    <x-lucide-link class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                    <input type="url" name="evidence_links[]" x-model="links[index]" placeholder="https://drive.google.com/... or https://dropbox.com/..." class="w-full text-sm pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent">
                                </div>
                                <button type="button" x-show="links.length > 1" @click="links.splice(index, 1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                    <x-lucide-x class="w-4 h-4 text-gray-500" />
                                </button>
                            </div>
                        </template>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-900">Evidence Description (Optional)</label>
                        <textarea name="evidence_description" rows="3" placeholder="Briefly describe the evidence you're submitting..." class="w-full text-sm px-3 py-2 border border-gray-300 rounded-lg resize-none bg-white text-gray-900 focus:ring-2 focus:ring-[#1D9E75] focus:border-transparent"></textarea>
                    </div>
                </div>
                <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex items-center justify-between rounded-b-2xl">
                    <div class="text-sm text-gray-600">
                        <span x-show="files.length > 0" class="flex items-center gap-2">
                            <x-lucide-circle-check class="w-4 h-4 text-[#1D9E75]" />
                            <span x-text="files.length + ' file' + (files.length === 1 ? '' : 's') + ' ready'"></span>
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="evidenceOpen = false" class="px-6 py-2 border border-gray-200 rounded-lg text-sm bg-white hover:bg-gray-50">Cancel</button>
                        <button type="button" @click="evidenceOpen = false" class="px-6 py-2 bg-[#1D9E75] hover:bg-[#178F65] text-white text-sm rounded-lg">Submit Evidence</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</x-layouts.complainant>
