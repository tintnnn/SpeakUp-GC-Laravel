{{-- Port of frontend/src/views/admin/AdminLogin.tsx --}}
<x-layouts.app>
<div class="min-h-screen bg-[#f8faf9] dark:bg-[#0f1412] text-gray-900 dark:text-gray-100 font-sans flex flex-col" x-data="{ showPassword: false }">
    <main class="relative flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="absolute -left-24 -top-20 h-[22rem] w-[28rem] bg-gradient-to-br from-[#1D9E75]/20 via-emerald-200/35 to-transparent blur-3xl"></div>
            <div class="absolute -right-20 bottom-0 h-[20rem] w-[24rem] bg-gradient-to-tl from-teal-200/35 via-[#1D9E75]/10 to-transparent blur-3xl"></div>
        </div>

        <div class="relative w-full max-w-md bg-white dark:bg-card border border-[#d4e4db] dark:border-border rounded-2xl shadow-xl shadow-gray-900/5 p-6 sm:p-8">
            <div class="text-center mb-6">
                <div class="mx-auto h-14 w-14 bg-emerald-50 dark:bg-[#1D9E75]/20 rounded-full flex items-center justify-center mb-4">
                    <x-lucide-shield class="h-7 w-7 text-[#1D9E75]" />
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1a2e1f] dark:text-foreground mb-1">Admin Panel</h1>
                <p class="text-sm text-[#7a8f82] dark:text-muted-foreground">Sign in to manage cases and reports</p>
                <span class="inline-block mt-3 text-[11px] font-semibold uppercase tracking-wide text-[#1D9E75] bg-[#f0f7f3] dark:bg-[#1D9E75]/20 border border-[#d4e4db] dark:border-[#1D9E75]/30 rounded-full px-3 py-1">Admin • CODI Members</span>
            </div>

            @auth
                @unless (auth()->user()->isStaff())
                    <div class="mb-4 bg-[#f0f7f3] border border-[#d4e4db] text-[#1a2e1f] rounded-lg px-4 py-3 text-sm">
                        Signed in as {{ auth()->user()->email }}. Signing in here switches you to the CODI or admin account.
                    </div>
                @endunless
            @endauth

            @error('email')
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">{{ $message }}</div>
            @enderror

            <form method="POST" action="/admin/login" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-[#4b5e52]">Email Address <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <x-lucide-mail class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#7a8f82]" />
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full h-11 pl-10 pr-3 rounded-xl text-sm text-[#1a2e1f] placeholder-[#9ca8a0] bg-[#f5f9f7] border-[1.5px] border-[#d4e4db] focus:outline-none focus:border-[#1D9E75] transition-colors" placeholder="admin@speakupgc.com" required>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-[#4b5e52]">Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <x-lucide-lock class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#7a8f82]" />
                        <input :type="showPassword ? 'text' : 'password'" name="password" class="w-full h-11 pl-10 pr-10 rounded-xl text-sm text-[#1a2e1f] placeholder-[#9ca8a0] bg-[#f5f9f7] border-[1.5px] border-[#d4e4db] focus:outline-none focus:border-[#1D9E75] transition-colors" placeholder="Enter your password" required>
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#7a8f82] hover:text-[#1a2e1f] transition-colors">
                            <span x-show="!showPassword"><x-lucide-eye class="w-[18px] h-[18px]" /></span>
                            <span x-show="showPassword" x-cloak><x-lucide-eye-off class="w-[18px] h-[18px]" /></span>
                        </button>
                    </div>
                    <a href="/forgot-password" class="inline-block text-xs font-medium text-[#1D9E75] hover:underline">Forgot your password?</a>
                </div>
                <button type="submit" class="w-full h-11 bg-[#1D9E75] hover:bg-[#178F65] text-white font-semibold text-sm rounded-xl transition-colors flex items-center justify-center gap-2 shadow-md shadow-[#1D9E75]/20">
                    <x-lucide-shield class="h-4 w-4" />
                    Access Admin Panel
                </button>
            </form>

            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[#e2ece7]"></div></div>
                <div class="relative flex justify-center text-xs"><span class="bg-white dark:bg-card px-2 font-medium text-[#7a8f82] dark:text-muted-foreground">OR</span></div>
            </div>
            @if (is_array(session('toast')) && (session('toast.variant') === 'destructive'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">{{ session('toast.description') }}</div>
            @endif

            @if (config('speakup.google_admin_sign_in_enabled'))
            @php
                $firebaseWebConfig = [
                    'apiKey' => config('services.firebase.api_key'),
                    'authDomain' => config('services.firebase.auth_domain'),
                    'projectId' => config('services.firebase.project_id'),
                    'storageBucket' => config('services.firebase.storage_bucket'),
                    'messagingSenderId' => config('services.firebase.messaging_sender_id'),
                    'appId' => config('services.firebase.app_id'),
                    'measurementId' => config('services.firebase.measurement_id'),
                ];
            @endphp
            <div id="admin-google-error" class="hidden mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm"></div>
            <button type="button" id="admin-google-button" class="w-full h-11 bg-white dark:bg-card hover:bg-[#f0f7f3] dark:hover:bg-white/5 text-[#1a2e1f] dark:text-foreground font-medium text-sm rounded-xl border-[1.5px] border-[#d4e4db] dark:border-border transition-colors flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                <span data-label class="inline-flex items-center justify-center gap-3">
                    @include('partials.google-icon')
                    <span>Continue with Google</span>
                </span>
                <span data-busy class="hidden items-center justify-center gap-3">
                    <span class="animate-spin rounded-full h-4 w-4 border-2 border-[#1a2e1f] border-t-transparent"></span>
                    <span>Signing in…</span>
                </span>
            </button>
            <script type="application/json" id="admin-firebase-config">@json($firebaseWebConfig)</script>
            @push('scripts')
                @vite('resources/js/admin-google.js')
            @endpush
            @endif
        </div>
    </main>
</div>
</x-layouts.app>
