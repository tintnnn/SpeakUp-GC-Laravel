{{-- Port of the reset form in frontend/src/views/auth/ResetPassword.tsx. The token comes from Laravel's signed reset link instead of Firebase oobCode. --}}
<x-layouts.app>
<div class="min-h-screen bg-[#f8faf9] text-gray-900 font-sans flex flex-col">
    <header class="w-full px-4 sm:px-6 pt-4 z-10">
        <div class="flex h-14 items-center justify-between bg-white/90 backdrop-blur-md border border-gray-200 rounded-2xl px-5 shadow-lg shadow-gray-900/5">
            <a href="/" class="flex items-center gap-2.5 min-w-0">
                <img src="/LOGO.png" alt="SpeakUp GC" class="w-8 h-8 object-contain flex-shrink-0">
                <span class="font-bold text-gray-900 text-sm truncate">SpeakUp GC</span>
            </a>
            <a href="/?auth=login" class="text-sm font-medium text-[#1D9E75] hover:text-[#178F65] transition-colors">Log In</a>
        </div>
    </header>
    <main class="relative flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="absolute -left-24 -top-20 h-[22rem] w-[28rem] bg-gradient-to-br from-[#1D9E75]/20 via-emerald-200/35 to-transparent blur-3xl"></div>
            <div class="absolute -right-20 bottom-0 h-[20rem] w-[24rem] bg-gradient-to-tl from-teal-200/35 via-[#1D9E75]/10 to-transparent blur-3xl"></div>
        </div>
        <div class="relative w-full max-w-md bg-white border border-[#d4e4db] rounded-2xl shadow-xl shadow-gray-900/5 p-6 sm:p-8" x-data="{ showPassword: false, showConfirm: false }">
            <h1 class="text-2xl font-extrabold text-[#1a2e1f] mb-1 text-center">Choose a new password</h1>
            <p class="text-sm text-[#7a8f82] mb-6 text-center">{{ $email }}</p>
            <form method="POST" action="/reset-password" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-[#4b5e52]">New Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <x-lucide-lock class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#7a8f82]" />
                        <input :type="showPassword ? 'text' : 'password'" name="password" required class="w-full h-11 pl-10 pr-10 rounded-xl text-sm text-[#1a2e1f] bg-[#f5f9f7] border-[1.5px] border-[#d4e4db] focus:outline-none focus:border-[#1D9E75]">
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#7a8f82]">
                            <span x-show="!showPassword"><x-lucide-eye class="w-4 h-4" /></span>
                            <span x-show="showPassword" x-cloak><x-lucide-eye-off class="w-4 h-4" /></span>
                        </button>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-[#4b5e52]">Confirm Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <x-lucide-lock class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#7a8f82]" />
                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required class="w-full h-11 pl-10 pr-10 rounded-xl text-sm text-[#1a2e1f] bg-[#f5f9f7] border-[1.5px] border-[#d4e4db] focus:outline-none focus:border-[#1D9E75]">
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#7a8f82]">
                            <span x-show="!showConfirm"><x-lucide-eye class="w-4 h-4" /></span>
                            <span x-show="showConfirm" x-cloak><x-lucide-eye-off class="w-4 h-4" /></span>
                        </button>
                    </div>
                </div>
                @error('password')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                @error('email')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                <button type="submit" class="w-full h-11 bg-[#1D9E75] hover:bg-[#178F65] text-white font-semibold text-sm rounded-xl">Update Password</button>
            </form>
        </div>
    </main>
</div>
</x-layouts.app>
