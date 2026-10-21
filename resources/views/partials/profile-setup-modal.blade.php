<div
    x-data="profileSetup()"
    x-cloak
    class="fixed inset-0 z-[200] flex items-center justify-center overflow-y-auto p-4"
>
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="speakup-modal-shell relative flex w-full max-w-lg flex-col overflow-hidden rounded-3xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="laya-setup-title">
        <div class="shrink-0 bg-gradient-to-r from-[#1D9E75] to-[#178F65] px-6 pb-8 pt-6">
            <p class="mb-1 text-xs font-medium uppercase tracking-widest text-white/80">SpeakUp GC</p>
            <h2 id="laya-setup-title" class="text-xl font-bold text-white">Profile Setup</h2>
            <p class="mt-0.5 text-xs text-white/70">Quick one-time setup to protect your identity</p>
            <div class="mt-5 flex items-center gap-2">
                <template x-for="(item, index) in [
                    { id: 'alias', label: 'Alias' },
                    { id: 'terms', label: 'Terms' },
                    { id: 'privacy', label: 'Privacy' },
                    { id: 'done', label: 'Done' }
                ]" :key="item.id">
                    <div class="flex flex-1 items-center gap-2">
                        <span
                            class="rounded-full px-3 py-1 text-xs font-medium"
                            :class="step === item.id ? 'bg-white text-[#1D9E75]' : (['alias','terms','privacy','done'].indexOf(step) > index ? 'bg-white/30 text-white' : 'bg-white/10 text-white/40')"
                            x-text="item.label"
                        ></span>
                        <span x-show="index < 3" class="h-px flex-1 bg-white/30"></span>
                    </div>
                </template>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
            <div class="px-6 pb-4 pt-1 -mt-4">
                <div class="flex gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-md">
                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-xl border border-[#1D9E75]/20 bg-[#E8F5EE]">
                        <img src="/speakup_gc_chatbot_3d_1.png" alt="Laya" class="h-full w-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="mb-1 text-xs font-semibold text-[#1D9E75]">Laya · SpeakUp GC Guide</p>
                        <p class="max-h-[min(28vh,180px)] overflow-y-auto whitespace-pre-line pr-1 text-sm leading-relaxed text-gray-700">
                            <span x-text="displayed"></span><span x-show="typing" class="ml-0.5 inline-block h-3.5 w-1 animate-pulse rounded-sm bg-[#1D9E75]"></span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-5 px-6 pb-6 pt-1">
                <div x-show="step === 'alias'" class="space-y-3">
                    <label for="laya-alias" class="block text-sm font-medium text-gray-700">Your Alias / Pseudonym</label>
                    <input
                        id="laya-alias"
                        type="text"
                        x-model="alias"
                        @input="aliasError = ''"
                        @keydown.enter.prevent="nextAlias()"
                        maxlength="30"
                        placeholder="e.g. BlueStar22"
                        class="w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition-colors focus:border-[#1D9E75] focus:ring-2 focus:ring-[#1D9E75]/30"
                        :class="aliasError ? 'border-red-400' : 'border-gray-200'"
                    >
                    <p x-show="aliasError" x-cloak class="text-xs text-red-500" x-text="aliasError"></p>
                    <p class="text-xs text-gray-400">This alias protects your identity in cases. You can change it anytime from My Profile.</p>
                </div>

                <div x-show="step === 'terms'" x-cloak class="space-y-5">
                    <div class="max-h-[min(42vh,280px)] overflow-y-auto rounded-xl border border-[#1D9E75]/20 bg-[#F0FDF4] px-5 pb-6 pt-5 text-xs leading-relaxed text-gray-600">
                        <p class="mb-1 text-sm font-semibold text-[#178F65]">Terms &amp; Conditions — SpeakUp GC</p>
                        <p class="mt-3 font-semibold text-gray-700">1. Acceptable Use</p>
                        <p class="mt-1">SpeakUp GC is a platform for reporting gender-based violence and harassment incidents in accordance with Philippine law. You agree to use this platform only for legitimate reporting purposes and not for false, malicious, or defamatory complaints.</p>
                        <p class="mt-3 font-semibold text-gray-700">2. Eligibility</p>
                        <p class="mt-1">This platform is available to students, faculty, staff, and authorized personnel of Gordon College. By using SpeakUp GC, you confirm that you are eligible to file complaints under the Gordon College Committee on Decorum and Investigation (CODI).</p>
                        <p class="mt-3 font-semibold text-gray-700">3. Prohibited Actions</p>
                        <p class="mt-1">You may not use SpeakUp GC to: (a) submit false or fabricated reports; (b) harass, threaten, or defame any individual; (c) violate any applicable laws or regulations; or (d) interfere with the proper functioning of the platform.</p>
                        <p class="mt-3 font-semibold text-gray-700">4. Governing Law</p>
                        <p class="mt-1">This platform operates under Republic Act No. 11313 (Safe Spaces Act), Republic Act No. 7877 (Anti-Sexual Harassment Act), and the Gordon College Committee on Decorum and Investigation (GC-CODI) procedures.</p>
                        <p class="mt-3 font-semibold text-gray-700">5. Modifications</p>
                        <p class="mt-1">Gordon College reserves the right to modify these terms at any time. Continued use of the platform constitutes acceptance of any changes.</p>
                    </div>
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" x-model="termsAccepted" class="mt-0.5 h-4 w-4 shrink-0 accent-[#1D9E75]">
                        <span class="text-sm leading-snug text-gray-700">I have read and agree to the Terms &amp; Conditions.</span>
                    </label>
                </div>

                <div x-show="step === 'privacy'" x-cloak class="space-y-5">
                    <div class="max-h-[min(42vh,280px)] overflow-y-auto rounded-xl border border-[#1D9E75]/20 bg-[#F0FDF4] px-5 pb-6 pt-5 text-xs leading-relaxed text-gray-600">
                        <p class="mb-1 text-sm font-semibold text-[#178F65]">Privacy Policy — SpeakUp GC</p>
                        <p class="mt-3 font-semibold text-gray-700">Data Collection &amp; Use</p>
                        <p class="mt-1">All complaints submitted through SpeakUp GC are handled with strict confidentiality by the Diversity, Equity, and Inclusion Unit (DEIU) of Gordon College. Your identity will never be disclosed to respondents or any other party without your explicit consent.</p>
                        <p class="mt-3 font-semibold text-gray-700">Legal Compliance</p>
                        <p class="mt-1">This system complies with Republic Act No. 10173 (Data Privacy Act of 2012), Republic Act No. 11313 (Safe Spaces Act), and the Gordon College Committee on Decorum and Investigation (CODI).</p>
                        <p class="mt-3 font-semibold text-gray-700">Data Security</p>
                        <p class="mt-1">You may file complaints as an identified complainant or anonymously. Your data is stored securely and accessed only by authorized DEIU personnel for investigation purposes.</p>
                        <p class="mt-3 font-semibold text-gray-700">Your Rights</p>
                        <p class="mt-1">Under RA 10173, you have the right to access, correct, and request deletion of your personal data. You may also withdraw consent at any time, subject to legal and contractual restrictions.</p>
                    </div>
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" x-model="privacyAccepted" class="mt-0.5 h-4 w-4 shrink-0 accent-[#1D9E75]">
                        <span class="text-sm leading-snug text-gray-700">I have read and understood the Privacy Policy. I consent to my information being stored and processed by the DEIU in accordance with RA 10173.</span>
                    </label>
                </div>

                <div x-show="step === 'done'" x-cloak class="space-y-4 py-2 text-center">
                    <div class="flex justify-center">
                        <div class="rounded-full border-4 border-[#1D9E75]/20 bg-[#F0FDF4] p-4">
                            <x-lucide-circle-check class="h-10 w-10 text-[#1D9E75]" />
                        </div>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900">You're all set, <span x-text="alias"></span>!</p>
                        <p class="mt-1 text-sm text-gray-500">Your profile is ready. Welcome to SpeakUp GC.</p>
                    </div>
                    <div class="flex flex-wrap justify-center gap-2 text-xs text-gray-400">
                        <span class="rounded-full bg-gray-100 px-3 py-1">Alias: <strong class="text-gray-700" x-text="alias"></strong></span>
                        <span class="rounded-full bg-gray-100 px-3 py-1">Notifications: <strong class="text-gray-700">both</strong></span>
                        <span class="rounded-full bg-green-50 px-3 py-1 text-green-700">Terms accepted</span>
                        <span class="rounded-full bg-green-50 px-3 py-1 text-green-700">Privacy accepted</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="shrink-0 border-t border-gray-100 bg-white px-6 pb-6 pt-4">
            <button x-show="step === 'alias'" type="button" @click="nextAlias()" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-[#1D9E75] text-sm font-medium text-white hover:bg-[#178F65]">
                Next <x-lucide-arrow-right class="ml-1 h-4 w-4" />
            </button>
            <button x-show="step === 'terms'" x-cloak type="button" @click="termsAccepted && go('privacy')" :disabled="!termsAccepted" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-[#1D9E75] text-sm font-medium text-white hover:bg-[#178F65] disabled:opacity-50">
                Next <x-lucide-arrow-right class="ml-1 h-4 w-4" />
            </button>
            <button x-show="step === 'privacy'" x-cloak type="button" @click="finish()" :disabled="!privacyAccepted || saving" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-[#1D9E75] text-sm font-medium text-white hover:bg-[#178F65] disabled:opacity-50">
                <span x-show="!saving">Complete Setup</span>
                <span x-show="saving" x-cloak>Saving…</span>
            </button>
            <button x-show="step === 'done'" x-cloak type="button" @click="dashboard()" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-[#1D9E75] text-sm font-medium text-white hover:bg-[#178F65]">
                Go to My Dashboard <x-lucide-arrow-right class="ml-1 h-4 w-4" />
            </button>
        </div>
    </div>
</div>
