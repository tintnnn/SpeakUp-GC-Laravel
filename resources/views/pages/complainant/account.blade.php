<x-layouts.complainant>
@php
    $user = auth()->user();
    $alias = (string) ($user?->alias ?? '');
    $email = (string) ($user?->email ?? '');
    $profileSetupComplete = (bool) ($user?->profile_setup_complete ?? false);
    $privacyAccepted = (bool) ($user?->privacy_accepted ?? false);
    $setupDate = $user?->profile_setup_completed_at;
    $setupLabel = null;
    if ($setupDate) {
        try {
            $setupLabel = \Illuminate\Support\Carbon::parse($setupDate)->format('F j, Y');
        } catch (\Throwable $e) {
            $setupLabel = null;
        }
    }
@endphp
<div
    class="min-h-full"
    x-data="{
        editingAlias: false,
        aliasInput: '',
        aliasError: '',
        showReset: false,
        showLogout: false,
        init() { this.aliasInput = this.$root.dataset.alias || ''; },
        startEdit() { this.aliasInput = this.$root.dataset.alias || ''; this.aliasError = ''; this.editingAlias = true; },
        validateAlias() {
            const trimmed = this.aliasInput.trim();
            if (!trimmed) { this.aliasError = 'Alias cannot be empty.'; return false; }
            if (trimmed.length < 3) { this.aliasError = 'Alias must be at least 3 characters.'; return false; }
            if (trimmed.length > 30) { this.aliasError = 'Alias must be 30 characters or fewer.'; return false; }
            this.aliasError = '';
            return true;
        }
    }"
    data-alias="{{ $alias }}"
>
    <div class="space-y-5">
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-[#1a3329] dark:to-[#152820] border border-green-100 dark:border-[#2d5a48] rounded-2xl p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">My Profile</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage your identity, notifications, and security settings.</p>
        </div>

        <div class="flex items-start gap-3 p-4 rounded-2xl border text-sm {{ $profileSetupComplete ? 'bg-[#F0FDF4] border-[#1D9E75]/25 text-[#178F65]' : 'bg-yellow-50 border-yellow-200 text-yellow-800' }}">
            @if ($profileSetupComplete)
                <x-lucide-circle-check class="h-4 w-4 shrink-0 mt-0.5 text-[#1D9E75]" />
            @else
                <x-lucide-shield class="h-4 w-4 shrink-0 mt-0.5 text-yellow-600" />
            @endif
            <div>
                <p class="font-semibold">
                    {{ $profileSetupComplete ? 'Profile setup complete' : 'Profile setup incomplete' }}
                </p>
                <p class="text-xs mt-0.5 opacity-80">
                    @if ($profileSetupComplete)
                        {{ $setupLabel ? 'Completed on '.$setupLabel : 'Your profile has been set up.' }}
                    @else
                        Some profile fields are missing. You can fill them in below.
                    @endif
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="space-y-5">
                <div class="rounded-lg border-0 shadow-sm bg-card text-card-foreground">
                    <div class="flex flex-col space-y-1.5 p-6 pb-3 border-b">
                        <h2 class="text-base font-semibold flex items-center gap-2 leading-none">
                            <x-lucide-user class="h-4 w-4 text-gray-500" />
                            Alias / Pseudonym
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            This alias protects your identity in complaint cases. It is never shown to respondents.
                        </p>
                    </div>
                    <div class="p-6 pt-4">
                        <form method="POST" action="/account" x-show="editingAlias" x-cloak class="space-y-3" @submit="if (!validateAlias()) $event.preventDefault()">
                            @csrf
                            <input
                                type="text"
                                name="alias"
                                x-model="aliasInput"
                                @keydown.enter.prevent="if (validateAlias()) $el.form.submit()"
                                @keydown.escape="editingAlias = false; aliasError = ''"
                                placeholder="e.g. BlueStar22"
                                maxlength="30"
                                class="flex h-10 w-full rounded-xl border bg-background px-3 py-2 text-sm"
                                :class="aliasError ? 'border-red-400' : 'border-input'"
                            >
                            <p x-show="aliasError" x-cloak class="text-xs text-red-500" x-text="aliasError"></p>
                            <div class="flex gap-2">
                                <button type="submit" class="inline-flex items-center justify-center h-9 px-3 rounded-lg bg-[#1D9E75] hover:bg-[#178F65] text-white text-sm font-medium">
                                    Save
                                </button>
                                <button type="button" @click="editingAlias = false; aliasError = ''" class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-input bg-background text-sm font-medium hover:bg-accent">
                                    Cancel
                                </button>
                            </div>
                        </form>
                        <div x-show="!editingAlias" class="flex items-center justify-between gap-4">
                            <div>
                                @if ($alias !== '')
                                    <p class="font-semibold text-gray-900">{{ $alias }}</p>
                                @else
                                    <p class="text-gray-400 italic text-sm">No alias set yet</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-0.5">Used to protect your identity in cases</p>
                            </div>
                            <button
                                type="button"
                                @click="startEdit()"
                                class="shrink-0 inline-flex items-center justify-center gap-1.5 h-9 px-3 rounded-lg border border-input bg-background text-sm font-medium hover:bg-accent"
                            >
                                <x-lucide-pencil class="h-3.5 w-3.5" /> Edit
                            </button>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border-0 shadow-sm bg-card text-card-foreground">
                    <div class="flex flex-col space-y-1.5 p-6 pb-3 border-b">
                        <h2 class="text-base font-semibold flex items-center gap-2 leading-none">
                            <x-lucide-mail class="h-4 w-4 text-gray-500" />
                            Account Information
                        </h2>
                        <p class="text-xs text-muted-foreground">Your registered email address (cannot be changed).</p>
                    </div>
                    <div class="p-6 pt-4">
                        <input
                            type="email"
                            name="email"
                            value="{{ $email }}"
                            disabled
                            class="flex h-10 w-full rounded-xl border border-input bg-gray-50 px-3 py-2 text-sm cursor-not-allowed"
                        >
                    </div>
                </div>

                <div class="rounded-lg border-0 shadow-sm bg-card text-card-foreground">
                    <div class="flex flex-col space-y-1.5 p-6 pb-3 border-b">
                        <h2 class="text-base font-semibold flex items-center gap-2 leading-none">
                            <x-lucide-key class="h-4 w-4 text-gray-500" />
                            Security
                        </h2>
                        <p class="text-xs text-muted-foreground">Manage your password.</p>
                    </div>
                    <div class="p-6 pt-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Password</p>
                                <p class="text-xs text-gray-500 mt-0.5">Send a reset link to your email</p>
                            </div>
                            <button type="button" @click="showReset = true" class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-input bg-background text-sm font-medium hover:bg-accent">
                                Reset Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-lg border-0 shadow-sm bg-card text-card-foreground">
                    <div class="flex flex-col space-y-1.5 p-6 pb-3 border-b bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-500/15 dark:to-indigo-500/10">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-base font-semibold flex items-center gap-2 leading-none">
                                    <x-lucide-bell class="h-4 w-4 text-blue-600" />
                                    Notification Preference
                                </h2>
                                <p class="text-xs text-muted-foreground mt-1">
                                    How you receive updates about your case status.
                                </p>
                            </div>
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 bg-blue-100 text-blue-700 border-blue-200 text-xs font-semibold">
                                System Default
                            </span>
                        </div>
                    </div>
                    <div class="p-6 pt-4">
                        <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-400/20 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <div class="shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <x-lucide-bell class="h-5 w-5 text-blue-600" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 text-sm">Both (Recommended)</p>
                                    <p class="text-xs text-gray-600 mt-1">Email + in-app notifications</p>
                                    <div class="mt-3 flex items-center gap-2 text-xs text-blue-700">
                                        <x-lucide-circle-check class="h-4 w-4" />
                                        <span class="font-medium">This setting is locked to ensure you never miss important case updates</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('partials.push-settings')

                <div class="rounded-lg border-0 shadow-sm bg-card text-card-foreground">
                    <div class="flex flex-col space-y-1.5 p-6 pb-3 border-b">
                        <h2 class="text-base font-semibold flex items-center gap-2 leading-none">
                            <x-lucide-shield class="h-4 w-4 text-gray-500" />
                            Privacy Notice
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            Your acceptance of SpeakUp GC's confidentiality policy.
                        </p>
                    </div>
                    <div class="p-6 pt-4 space-y-3">
                        <div class="flex items-center gap-3 p-3 rounded-xl border {{ $privacyAccepted ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                            <x-lucide-circle-check class="h-5 w-5 shrink-0 {{ $privacyAccepted ? 'text-[#1D9E75]' : 'text-gray-300' }}" />
                            <div>
                                <p class="text-sm font-medium {{ $privacyAccepted ? 'text-[#178F65]' : 'text-gray-500' }}">
                                    {{ $privacyAccepted ? 'Privacy notice accepted' : 'Privacy notice not yet accepted' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    RA 11313 (Safe Spaces Act) · Gordon College CODI
                                </p>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 leading-relaxed bg-gray-50 rounded-xl p-3 border border-gray-100">
                            All complaints are handled with strict confidentiality by the DEIU. Your identity is never disclosed to respondents without your explicit consent. You may file complaints identified or anonymously.
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-red-200 bg-red-50/30 text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6 pb-3 border-b border-red-100">
                        <h2 class="text-base font-semibold flex items-center gap-2 leading-none text-red-700">
                            <x-lucide-log-out class="h-4 w-4" />
                            Sign Out
                        </h2>
                        <p class="text-xs text-muted-foreground">Log out of your SpeakUp GC account.</p>
                    </div>
                    <div class="p-6 pt-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">End your session</p>
                                <p class="text-xs text-gray-500 mt-0.5">You can log back in anytime</p>
                            </div>
                            <button
                                type="button"
                                @click="showLogout = true"
                                class="inline-flex items-center justify-center h-9 px-3 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium"
                            >
                                <x-lucide-log-out class="h-4 w-4 mr-2" />
                                Sign Out
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showReset" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4">
            <div class="w-full max-w-lg rounded-lg border border-gray-200 bg-white p-6 shadow-lg dark:border-border dark:bg-card" @click.outside="showReset = false">
                <h2 class="text-lg font-semibold">Reset Password</h2>
                <p class="mt-2 text-sm text-gray-500">
                    We will send a password reset link to <strong>{{ $email }}</strong>.
                    Please check your email and follow the instructions.
                </p>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="showReset = false" class="inline-flex h-10 items-center rounded-md border border-gray-200 px-4 text-sm font-medium">Cancel</button>
                    <form method="POST" action="/account/password-reset">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <button type="submit" class="inline-flex h-10 items-center rounded-md bg-[#1D9E75] hover:bg-[#178F65] px-4 text-sm font-medium text-white">Send Reset Link</button>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="showLogout" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4">
            <div class="w-full max-w-lg rounded-lg border border-gray-200 bg-white p-6 shadow-lg dark:border-border dark:bg-card" @click.outside="showLogout = false">
                <h2 class="text-lg font-semibold">Are you sure you want to sign out?</h2>
                <p class="mt-2 text-sm text-gray-500">
                    You will be safely logged out of your SpeakUp GC account. You can log back in anytime.
                </p>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="showLogout = false" class="inline-flex h-10 items-center rounded-md border border-gray-200 px-4 text-sm font-medium">Cancel</button>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="inline-flex h-10 items-center rounded-md bg-red-600 hover:bg-red-700 px-4 text-sm font-medium text-white">Yes, Sign Out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-layouts.complainant>
