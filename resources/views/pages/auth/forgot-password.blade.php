{{-- Port of frontend/src/views/auth/ForgotPassword.tsx --}}
<x-layouts.app>
<div class="min-h-screen bg-[#f8faf9] text-gray-900 font-sans flex flex-col">
    <main class="relative flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="absolute -left-24 -top-20 h-[22rem] w-[28rem] bg-gradient-to-br from-[#1D9E75]/20 via-emerald-200/35 to-transparent blur-3xl"></div>
            <div class="absolute -right-20 bottom-0 h-[20rem] w-[24rem] bg-gradient-to-tl from-teal-200/35 via-[#1D9E75]/10 to-transparent blur-3xl"></div>
        </div>

        <div class="relative w-full max-w-md bg-white border border-[#d4e4db] rounded-2xl shadow-xl shadow-gray-900/5 p-6 sm:p-8">
            @if (session('reset_sent'))
                <div class="text-center">
                    <div class="mx-auto h-14 w-14 bg-emerald-50 rounded-full flex items-center justify-center mb-5">
                        <x-lucide-circle-check class="h-7 w-7 text-[#1D9E75]" />
                    </div>
                    <h1 class="text-2xl font-extrabold text-[#1a2e1f] mb-1">Check your email</h1>
                    <p class="text-sm text-[#7a8f82] mb-5">We sent reset instructions to</p>
                    <div class="bg-[#f0f7f3] border border-[#d4e4db] rounded-xl px-4 py-3 mb-5">
                        <p class="font-semibold text-[#1D9E75] text-sm break-all">{{ session('reset_sent') }}</p>
                    </div>
                    <ol class="text-left text-sm text-[#4b5e52] space-y-2 mb-5 bg-[#f8faf9] border border-[#e2ece7] rounded-xl p-4 list-decimal list-inside">
                        <li>Open the SpeakUp GC email</li>
                        <li>Click Reset Password</li>
                        <li>Choose a new password</li>
                        <li>Sign in with your new password</li>
                    </ol>
                    <p class="text-xs text-[#a16207] bg-[#fef9ee] border border-[#f5d990] rounded-xl px-3 py-2.5 mb-5">
                        Can’t find it? Check spam or junk, then try again.
                    </p>
                    <div class="space-y-2">
                        <a href="/forgot-password" class="flex items-center justify-center w-full h-11 bg-white hover:bg-[#f0f7f3] text-[#1a2e1f] text-sm font-medium rounded-xl border border-[#d4e4db] transition-colors">Use a different email</a>
                        <a href="/?auth=login" class="flex items-center justify-center gap-2 w-full h-11 bg-[#1D9E75] hover:bg-[#178F65] text-white text-sm font-semibold rounded-xl transition-colors">
                            <x-lucide-arrow-left class="w-4 h-4" />
                            Back to Login
                        </a>
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center mb-6">
                    <img src="/LOGO.png" alt="" class="w-10 h-10 object-contain mb-2">
                    <span class="text-sm font-semibold text-[#7a8f82]">SpeakUp GC</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-center text-[#1a2e1f] mb-1">Reset your password</h1>
                <p class="text-sm text-center text-[#7a8f82] mb-7">Enter your Gordon College email and we’ll send a reset link.</p>

                <form method="POST" action="/forgot-password" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold uppercase tracking-wide text-[#4b5e52]">Email Address <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <x-lucide-mail class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#7a8f82]" />
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="you@gordoncollege.edu.ph" class="w-full h-11 pl-10 pr-3 rounded-xl text-sm text-[#1a2e1f] placeholder-[#9ca8a0] bg-[#f5f9f7] border-[1.5px] border-[#d4e4db] focus:outline-none focus:border-[#1D9E75] transition-colors" required>
                        </div>
                        @error('email')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full h-11 bg-[#1D9E75] hover:bg-[#178F65] text-white font-semibold text-sm rounded-xl transition-colors flex items-center justify-center gap-2 shadow-md shadow-[#1D9E75]/20">
                        <x-lucide-mail class="h-4 w-4" />
                        Send Reset Email
                    </button>
                </form>

                <p class="mt-5 text-xs text-[#7a8f82] flex items-start gap-2 bg-[#f0f7f3] border border-[#d4e4db] rounded-xl px-3 py-2.5">
                    <x-lucide-circle-alert class="h-3.5 w-3.5 mt-0.5 flex-shrink-0 text-[#1D9E75]" />
                    Reset links expire after 1 hour. Check your spam folder if you don’t see the email.
                </p>
                <p class="text-sm text-[#4b5e52] mt-5 text-center">
                    Remember your password?
                    <a href="/?auth=login" class="text-[#1D9E75] hover:underline font-semibold">Log In</a>
                </p>
            @endif
        </div>
    </main>
</div>
</x-layouts.app>
