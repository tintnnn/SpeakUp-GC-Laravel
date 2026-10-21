{{-- Port of frontend/src/components/WalkthroughModal.tsx. Requires the enclosing Alpine scope from landingPage() --}}
@php $googleSignInEnabled = config('speakup.google_sign_in_enabled'); @endphp
<template x-if="walkthrough.isOpen">
<div x-data="walkthroughModal()" x-init="init()" @keydown.escape.window="handleDismiss()">
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="handleDismiss()"></div>

        {{-- Modal --}}
        <div class="relative w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-300 max-h-[90vh] flex flex-col"
             :style="{ background: colors.modalBg, border: `1px solid ${colors.cardBorder}` }">
            {{-- Close button --}}
            <button type="button" @click="handleDismiss()"
                class="absolute top-3.5 right-3.5 z-10 w-8 h-8 flex items-center justify-center rounded-full transition-all"
                :style="closeHover ? { background: colors.primaryBorder, color: colors.heading } : { background: colors.primaryLight, color: colors.muted }"
                @mouseenter="closeHover = true" @mouseleave="closeHover = false">
                <x-lucide-x class="w-[15px] h-[15px]" />
            </button>

            {{-- Progress bar --}}
            <div class="h-1 flex-shrink-0" :style="{ background: colors.divider }">
                <div class="h-full transition-all duration-500 ease-out rounded-r-full" :style="{ width: progressWidth, background: colors.primary }"></div>
            </div>

            {{-- Scrollable Content --}}
            <div class="overflow-y-auto flex-1 p-6 sm:p-7">
                {{-- Logo --}}
                <div class="flex flex-col items-center justify-center gap-1.5 mb-6">
                    <img src="/LOGO.png" alt="GC Logo" class="w-10 h-10 object-contain">
                    <span class="text-sm font-semibold tracking-tight" :style="{ color: colors.muted }">SpeakUp GC</span>
                </div>

                {{-- Animated view content (re-keyed per view so the enter animation replays) --}}
                <template x-for="v in [view]" :key="v + ':' + direction">
                <div class="animate-in fade-in duration-300" :class="direction === 'forward' ? 'slide-in-from-right-4' : 'slide-in-from-left-4'">

                    {{-- ══════════ CHOOSE: LOGIN or SIGNUP ══════════ --}}
                    <template x-if="view === 'choose'">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-center mb-1" :style="{ color: colors.heading }">Join the Community</h2>
                        <p class="text-sm text-center mb-8" :style="{ color: colors.body }">How would you like to continue?</p>

                        <div class="space-y-3">
                            <button type="button" @click="goTo('signup-email')"
                                class="w-full h-14 text-white font-medium text-sm rounded-xl transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-3"
                                :style="{ background: hover.signup ? colors.primaryHover : colors.primary }"
                                @mouseenter="hover.signup = true" @mouseleave="hover.signup = false">
                                <x-lucide-user-plus class="w-[18px] h-[18px]" />
                                Create a New Account
                            </button>
                            <button type="button" @click="goTo('login')"
                                class="w-full h-14 font-medium text-sm rounded-xl transition-all flex items-center justify-center gap-3"
                                :style="{ background: colors.cardBg, border: `1px solid ${colors.cardBorder}`, color: colors.heading }">
                                <x-lucide-log-in class="w-[18px] h-[18px]" />
                                I Already Have an Account
                            </button>
                        </div>
                    </div>
                    </template>

                    {{-- ══════════ LOGIN FORM ══════════ --}}
                    <template x-if="view === 'login'">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-center mb-1" :style="{ color: colors.heading }">Welcome back!</h2>
                        <p class="text-sm text-center mb-7" :style="{ color: colors.muted }">Sign in to continue reporting safely</p>

                        <form @submit.prevent="handleLoginSubmit()" class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide" :style="{ color: colors.body }">Email <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <x-lucide-mail class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4" x-bind:style="{ color: colors.muted }" />
                                    <input type="email" x-model="loginData.email"
                                        class="w-full h-11 pl-10 pr-3 rounded-xl text-sm focus:outline-none transition-all"
                                        :style="{ background: colors.inputBg, border: `1.5px solid ${focus.loginEmail ? colors.inputFocus : colors.inputBorder}`, color: colors.heading }"
                                        @focus="focus.loginEmail = true" @blur="focus.loginEmail = false"
                                        placeholder="you@gordoncollege.edu.ph" required :disabled="isLoginLoading || isGoogleLoading">
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide" :style="{ color: colors.body }">Password <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <x-lucide-lock class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4" x-bind:style="{ color: colors.muted }" />
                                    <input :type="showLoginPassword ? 'text' : 'password'" x-model="loginData.password"
                                        class="w-full h-11 pl-10 pr-10 rounded-xl text-sm focus:outline-none transition-all"
                                        :style="{ background: colors.inputBg, border: `1.5px solid ${focus.loginPassword ? colors.inputFocus : colors.inputBorder}`, color: colors.heading }"
                                        @focus="focus.loginPassword = true" @blur="focus.loginPassword = false"
                                        placeholder="Enter your password" required :disabled="isLoginLoading || isGoogleLoading">
                                    <button type="button" @click="showLoginPassword = !showLoginPassword" class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors" :style="{ color: colors.muted }">
                                        <span x-show="showLoginPassword" class="contents"><x-lucide-eye-off class="w-4 h-4" /></span>
                                        <span x-show="!showLoginPassword" class="contents"><x-lucide-eye class="w-4 h-4" /></span>
                                    </button>
                                </div>
                                <a href="/forgot-password" class="text-xs font-medium hover:underline" :style="{ color: colors.primary }">Forgot your password?</a>
                            </div>

                            <button type="submit" :disabled="isLoginLoading || isGoogleLoading"
                                class="w-full h-11 text-white font-semibold text-sm rounded-xl transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg"
                                :style="{ background: hover.login && !isLoginLoading ? colors.primaryHover : colors.primary }"
                                @mouseenter="hover.login = true" @mouseleave="hover.login = false">
                                <template x-if="isLoginLoading"><span class="contents"><x-lucide-loader class="animate-spin h-4 w-4" /> Logging in…</span></template>
                                <template x-if="!isLoginLoading"><span>Log In</span></template>
                            </button>
                        </form>

                        @if ($googleSignInEnabled)
                            {{-- Divider --}}
                            <div class="relative my-5">
                                <div class="absolute inset-0 flex items-center"><div class="w-full" :style="{ borderTop: `1px solid ${colors.divider}` }"></div></div>
                                <div class="relative flex justify-center text-xs"><span class="px-2 font-medium" :style="{ background: colors.modalBg, color: colors.muted }">OR</span></div>
                            </div>
                            <button type="button" :disabled="isGoogleLoading || isLoginLoading" @click="handleGoogleLogin()"
                                class="w-full h-11 font-medium text-sm rounded-xl transition-all flex items-center justify-center gap-3 disabled:opacity-50 hover:shadow-sm"
                                :style="{ background: hover.google ? colors.cardBg : colors.googleBg, border: `1.5px solid ${colors.cardBorder}`, color: colors.heading }"
                                @mouseenter="hover.google = true" @mouseleave="hover.google = false">
                                <template x-if="isGoogleLoading"><span class="contents"><x-lucide-loader class="animate-spin h-4 w-4" /> Signing in…</span></template>
                                <template x-if="!isGoogleLoading"><span class="contents">@include('partials.google-icon') Continue with Google</span></template>
                            </button>
                        @endif

                        <p class="text-sm mt-5 text-center" :style="{ color: colors.body }">
                            Need an account?
                            <button type="button" @click="goTo('signup-email')" class="hover:underline font-semibold" :style="{ color: colors.primary }">Sign Up</button>
                        </p>
                    </div>
                    </template>

                    {{-- ══════════ SIGNUP – Email Step ══════════ --}}
                    <template x-if="view === 'signup-email'">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-center mb-1" :style="{ color: colors.heading }">Create an account</h2>
                        <p class="text-sm text-center mb-6" :style="{ color: colors.body }">Join the SpeakUp GC community</p>

                        <form @submit.prevent="handleSignupEmailSubmit()" class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide" :style="{ color: colors.body }">Email <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <x-lucide-mail class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" x-bind:style="{ color: colors.muted }" />
                                    <input type="email" x-model="signupData.email"
                                        class="w-full h-11 pl-10 pr-3 rounded-lg text-sm focus:outline-none focus:ring-2 transition-all"
                                        :style="{ background: colors.inputBg, border: `1px solid ${colors.inputBorder}`, color: colors.heading }"
                                        placeholder="you@gordoncollege.edu.ph" required>
                                </div>
                            </div>

                            {{-- Terms Agreement --}}
                            <div class="flex items-start gap-3 p-3 rounded-lg" :style="{ background: colors.cardBg, border: `1px solid ${colors.cardBorder}` }">
                                <input type="checkbox" x-model="signupData.agreeTerms" class="mt-0.5 h-4 w-4 rounded cursor-pointer accent-emerald-600" required>
                                <label class="text-xs leading-relaxed cursor-pointer" :style="{ color: colors.body }">
                                    I agree to the
                                    <button type="button" @click="showTermsModal = true" class="hover:underline font-semibold" :style="{ color: colors.primary }">Terms &amp; Conditions</button>
                                    and
                                    <button type="button" @click="showPrivacyModal = true" class="hover:underline font-semibold" :style="{ color: colors.primary }">Privacy Policy</button>
                                </label>
                            </div>

                            <button type="submit" :disabled="isSignupLoading || !signupData.agreeTerms || !signupData.email"
                                class="w-full h-11 text-white font-medium text-sm rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                :style="{ background: hover.signupContinue && !isSignupLoading ? colors.primaryHover : colors.primary }"
                                @mouseenter="hover.signupContinue = true" @mouseleave="hover.signupContinue = false">
                                <template x-if="isSignupLoading"><span class="contents"><x-lucide-loader class="animate-spin h-4 w-4" /> Continuing…</span></template>
                                <template x-if="!isSignupLoading"><span>Continue</span></template>
                            </button>
                        </form>

                        @if ($googleSignInEnabled)
                            <div class="relative my-5">
                                <div class="absolute inset-0 flex items-center"><div class="w-full" :style="{ borderTop: `1px solid ${colors.divider}` }"></div></div>
                                <div class="relative flex justify-center text-xs"><span class="px-2" :style="{ background: colors.modalBg, color: colors.muted }">OR</span></div>
                            </div>
                            <button type="button" @click="handleGoogleSignup()" :disabled="isSignupGoogleLoading"
                                class="w-full h-11 font-medium text-sm rounded-lg transition-all flex items-center justify-center gap-3 disabled:opacity-50"
                                :style="{ background: colors.cardBg, border: `1px solid ${colors.cardBorder}`, color: colors.heading }">
                                <template x-if="isSignupGoogleLoading"><span class="contents"><x-lucide-loader class="animate-spin h-4 w-4" /> Signing up…</span></template>
                                <template x-if="!isSignupGoogleLoading"><span class="contents">@include('partials.google-icon') Continue with Google</span></template>
                            </button>
                        @endif

                        <p class="text-sm mt-4 text-center" :style="{ color: colors.body }">
                            Already have an account?
                            <button type="button" @click="goTo('login')" class="hover:underline font-semibold" :style="{ color: colors.primary }">Log In</button>
                        </p>
                    </div>
                    </template>

                    {{-- ══════════ SIGNUP – Password Step ══════════ --}}
                    <template x-if="view === 'signup-password'">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-center mb-1" :style="{ color: colors.heading }">Create a password</h2>
                        <p class="text-sm text-center mb-5" :style="{ color: colors.body }">Secure your account</p>

                        {{-- Email display --}}
                        <div class="rounded-lg p-3 flex items-center gap-3 mb-5" :style="{ background: colors.cardBg, border: `1px solid ${colors.cardBorder}` }">
                            <x-lucide-mail class="w-4 h-4 flex-shrink-0" x-bind:style="{ color: colors.primary }" />
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px]" :style="{ color: colors.muted }">Creating account for</p>
                                <p class="text-sm font-medium truncate" :style="{ color: colors.heading }" x-text="signupData.email"></p>
                            </div>
                            <button type="button" @click="goTo('signup-email')" class="text-[11px] hover:underline flex-shrink-0 font-semibold" :style="{ color: colors.primary }">Change</button>
                        </div>

                        <form @submit.prevent="handleSignupSubmit()" class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide" :style="{ color: colors.body }">Password <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <x-lucide-lock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" x-bind:style="{ color: colors.muted }" />
                                    <input :type="showSignupPassword ? 'text' : 'password'" x-model="signupData.password"
                                        class="w-full h-11 pl-10 pr-10 rounded-lg text-sm focus:outline-none focus:ring-2 transition-all"
                                        :style="{ background: colors.inputBg, border: `1px solid ${colors.inputBorder}`, color: colors.heading }"
                                        placeholder="Create a password" required>
                                    <button type="button" @click="showSignupPassword = !showSignupPassword" class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors" :style="{ color: colors.muted }">
                                        <span x-show="showSignupPassword" class="contents"><x-lucide-eye-off class="w-4 h-4" /></span>
                                        <span x-show="!showSignupPassword" class="contents"><x-lucide-eye class="w-4 h-4" /></span>
                                    </button>
                                </div>
                                <template x-if="signupData.password">
                                    <div class="mt-2">@include('partials.password-strength', ['model' => 'signupData.password'])</div>
                                </template>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold uppercase tracking-wide" :style="{ color: colors.body }">Confirm Password <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <x-lucide-lock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" x-bind:style="{ color: colors.muted }" />
                                    <input :type="showConfirmPassword ? 'text' : 'password'" x-model="signupData.confirmPassword"
                                        class="w-full h-11 pl-10 pr-10 rounded-lg text-sm focus:outline-none focus:ring-2 transition-all"
                                        :style="{ background: colors.inputBg, border: `1px solid ${colors.inputBorder}`, color: colors.heading }"
                                        placeholder="Confirm your password" required>
                                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors" :style="{ color: colors.muted }">
                                        <span x-show="showConfirmPassword" class="contents"><x-lucide-eye-off class="w-4 h-4" /></span>
                                        <span x-show="!showConfirmPassword" class="contents"><x-lucide-eye class="w-4 h-4" /></span>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" :disabled="isSignupLoading"
                                class="w-full h-11 text-white font-medium text-sm rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                :style="{ background: hover.createAccount && !isSignupLoading ? colors.primaryHover : colors.primary }"
                                @mouseenter="hover.createAccount = true" @mouseleave="hover.createAccount = false">
                                <template x-if="isSignupLoading"><span class="contents"><x-lucide-loader class="animate-spin h-4 w-4" /> Creating account…</span></template>
                                <template x-if="!isSignupLoading"><span>Create Account</span></template>
                            </button>
                        </form>
                    </div>
                    </template>

                    {{-- ══════════ SIGNUP DONE – Verify Email ══════════ --}}
                    <template x-if="view === 'signup-done'">
                    <div class="space-y-5 text-center">
                        <div class="flex justify-center">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center" :style="{ background: colors.primaryLight }">
                                <x-lucide-mail class="w-8 h-8" x-bind:style="{ color: colors.primary }" />
                            </div>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold mb-1" :style="{ color: colors.heading }" x-text="verificationEmailSent ? 'Check Your Email' : 'Account Created'"></h2>
                            <p class="text-sm" :style="{ color: colors.body }">
                                <template x-if="verificationEmailSent">
                                    <span>We sent a verification link to<br><span class="font-semibold" :style="{ color: colors.heading }" x-text="signupData.email"></span></span>
                                </template>
                                <template x-if="!verificationEmailSent && verificationLink">
                                    <span>We created your account for<br><span class="font-semibold" :style="{ color: colors.heading }" x-text="signupData.email"></span><br>School email delivery is blocked from the live site. Click Verify now to activate your account.</span>
                                </template>
                                <template x-if="!verificationEmailSent && !verificationLink">
                                    <span>We created your account for<br><span class="font-semibold" :style="{ color: colors.heading }" x-text="signupData.email"></span><br>but the verification email did not send. Click Resend Email below.</span>
                                </template>
                            </p>
                        </div>

                        <div class="rounded-lg p-4 text-left text-sm space-y-2" :style="{ background: colors.cardBg, border: `1px solid ${colors.cardBorder}`, color: colors.body }">
                            <p class="flex items-start gap-2"><x-lucide-check class="w-4 h-4 mt-0.5 flex-shrink-0" x-bind:style="{ color: colors.primary }" /> Click the verification link in the email</p>
                            <p class="flex items-start gap-2"><x-lucide-check class="w-4 h-4 mt-0.5 flex-shrink-0" x-bind:style="{ color: colors.primary }" /> Your account will be activated</p>
                            <p class="flex items-start gap-2"><x-lucide-check class="w-4 h-4 mt-0.5 flex-shrink-0" x-bind:style="{ color: colors.primary }" /> Sign in to access SpeakUp GC</p>
                        </div>

                        <div class="rounded-lg p-3 flex items-start gap-3 text-left" :style="{ background: colors.warningBg, border: `1px solid ${colors.warningBorder}` }">
                            <div class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center mt-0.5" :style="{ background: `${colors.warningBorder}40` }">
                                <span class="font-bold text-[10px]" :style="{ color: colors.warningText }">!</span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold mb-0.5" :style="{ color: colors.warningText }">Check Your Spam Folder</p>
                                <p class="text-[11px]" :style="{ color: colors.warningMuted }">The email may be in your Spam or Junk folder.</p>
                            </div>
                        </div>

                        <template x-if="verificationLink && !verificationEmailSent">
                            <button type="button" @click="window.location.assign(verificationLink)" class="w-full h-11 text-white font-medium text-sm rounded-lg transition-colors" :style="{ background: colors.primary }">Verify now</button>
                        </template>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <button type="button" @click="checkVerification()" :disabled="isSignupLoading"
                                class="flex-1 h-10 text-white font-medium text-sm rounded-lg transition-colors disabled:opacity-50"
                                :style="{ background: hover.check && !isSignupLoading ? colors.primaryHover : colors.primary }"
                                @mouseenter="hover.check = true" @mouseleave="hover.check = false"
                                x-text="isSignupLoading ? 'Checking…' : 'Check Verification'"></button>
                            <button type="button" @click="resendVerification()" :disabled="isSignupLoading || resendCooldown > 0"
                                class="flex-1 h-10 font-medium text-sm rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                :style="{ background: colors.cardBg, border: `1px solid ${colors.cardBorder}`, color: colors.body }"
                                x-text="resendCooldown > 0 ? `Resend Email (${resendCooldown}s)` : 'Resend Email'"></button>
                        </div>

                        <button type="button" @click="goTo('login')" class="hover:underline text-sm font-semibold" :style="{ color: colors.primary }">Go to Log In →</button>
                    </div>
                    </template>

                    {{-- ══════════ EXPLORE (final) ══════════ --}}
                    <template x-if="view === 'explore'">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-center mb-1" :style="{ color: colors.heading }">What You Can Do</h2>
                        <p class="text-sm text-center mb-5" :style="{ color: colors.body }">Once you're in, explore these features</p>
                        <div class="grid grid-cols-2 gap-2.5 mb-6">
                            @foreach ([
                                ['icon' => 'message-square', 'title' => 'Report Issues', 'desc' => 'Submit and track your reports easily'],
                                ['icon' => 'users', 'title' => 'Community', 'desc' => 'Connect with others in your area'],
                                ['icon' => 'shield', 'title' => 'Secure', 'desc' => 'Your data is always protected'],
                                ['icon' => 'sparkles', 'title' => 'AI Assistant', 'desc' => 'Get help and learn with our built-in AI'],
                            ] as $c)
                                <div class="p-3 rounded-xl transition-colors hover:shadow-sm" :style="{ background: colors.cardBg, border: `1px solid ${colors.cardBorder}` }">
                                    <x-dynamic-component :component="'lucide-'.$c['icon']" class="w-5 h-5 mb-2" x-bind:style="{ color: colors.primary }" />
                                    <p class="text-sm font-semibold mb-0.5" :style="{ color: colors.heading }">{{ $c['title'] }}</p>
                                    <p class="text-[11px] leading-snug" :style="{ color: colors.muted }">{{ $c['desc'] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" @click="handleDismiss()"
                            class="w-full h-11 text-white font-medium text-sm rounded-xl transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2"
                            :style="{ background: hover.done ? colors.primaryHover : colors.primary }"
                            @mouseenter="hover.done = true" @mouseleave="hover.done = false">
                            <x-lucide-circle-check-big class="w-4 h-4" />
                            Done — Close Walkthrough
                        </button>
                    </div>
                    </template>
                </div>
                </template>
            </div>

            {{-- Footer: back only — hidden on login, signup-email, choose, signup-done --}}
            <template x-if="view !== 'choose' && view !== 'login' && view !== 'signup-email' && view !== 'signup-done'">
                <div class="px-6 sm:px-8 pb-5 pt-3 flex items-center flex-shrink-0" :style="{ borderTop: `1px solid ${colors.divider}` }">
                    <button type="button" @click="view === 'signup-password' ? goTo('signup-email') : (view === 'explore' ? goTo('choose') : null)"
                        class="inline-flex items-center gap-1.5 text-sm font-medium transition-colors" :style="{ color: colors.muted }">
                        <x-lucide-arrow-left class="w-3.5 h-3.5" />
                        Back
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Terms & Conditions Modal --}}
    <x-terms-modal model="showTermsModal" title="Terms & Conditions">
        <div class="space-y-6 text-foreground">
            <div><h3 class="text-lg font-semibold mb-2">Welcome to SpeakUp GC</h3><p class="text-muted-foreground">By using our platform, you agree to these terms and conditions. Please read them carefully to understand your rights and responsibilities.</p></div>
            <div><h3 class="font-semibold mb-2">Acceptable Use</h3><p class="text-muted-foreground">SpeakUp GC is a platform for reporting gender-based violence and harassment incidents in accordance with Philippine law. You agree to use this platform only for legitimate reporting purposes and not for false, malicious, or defamatory complaints.</p></div>
            <div><h3 class="font-semibold mb-2">Eligibility</h3><p class="text-muted-foreground">This platform is available to students, faculty, staff, and authorized personnel of Gordon College. By using SpeakUp GC, you confirm that you are eligible to file complaints under the Gordon College Committee on Decorum and Investigation (CODI).</p></div>
            <div><h3 class="font-semibold mb-2">Prohibited Actions</h3><p class="text-muted-foreground">You may not use SpeakUp GC to: (a) submit false or fabricated reports; (b) harass, threaten, or defame any individual; (c) violate any applicable laws or regulations; or (d) interfere with the proper functioning of the platform.</p></div>
            <div><h3 class="font-semibold mb-2">Governing Law</h3><p class="text-muted-foreground">This platform operates under Republic Act No. 11313 (Safe Spaces Act), Republic Act No. 7877 (Anti-Sexual Harassment Act), and the Gordon College Committee on Decorum and Investigation (GC-CODI) procedures. All complaints are subject to investigation and resolution in accordance with these legal frameworks.</p></div>
            <div><h3 class="font-semibold mb-2">Modifications</h3><p class="text-muted-foreground">Gordon College reserves the right to modify these terms at any time. Continued use of the platform constitutes acceptance of any changes.</p></div>
            <p class="text-sm text-muted-foreground italic">Last updated: March 17, 2026</p>
        </div>
    </x-terms-modal>

    {{-- Privacy Policy Modal --}}
    <x-terms-modal model="showPrivacyModal" title="Privacy Policy">
        <div class="space-y-6 text-foreground">
            <div><h3 class="text-lg font-semibold mb-2">Privacy Policy</h3><p class="text-muted-foreground">Your privacy is our priority. SpeakUp GC is committed to protecting your personal information and ensuring a safe, confidential environment for all users.</p></div>
            <div><h3 class="font-semibold mb-2">Data Collection &amp; Use</h3><p class="text-muted-foreground">All complaints submitted through SpeakUp GC are handled with strict confidentiality by the Diversity, Equity, and Inclusion Unit (DEIU) of Gordon College. Your identity will never be disclosed to respondents or any other party without your explicit consent.</p></div>
            <div><h3 class="font-semibold mb-2">Legal Compliance</h3><p class="text-muted-foreground">This system complies with Republic Act No. 10173 (Data Privacy Act of 2012), Republic Act No. 11313 (Safe Spaces Act), and the Gordon College Committee on Decorum and Investigation (CODI).</p></div>
            <div><h3 class="font-semibold mb-2">Data Security</h3><p class="text-muted-foreground">You may file complaints as an identified complainant or anonymously. Your data is stored securely using industry-standard encryption and accessed only by authorized DEIU personnel for investigation purposes.</p></div>
            <div><h3 class="font-semibold mb-2">Your Rights</h3><p class="text-muted-foreground">Under RA 10173, you have the right to access, correct, and request deletion of your personal data. You may also withdraw consent at any time, subject to legal and contractual restrictions.</p></div>
            <p class="text-sm text-muted-foreground italic">Last updated: March 17, 2026</p>
        </div>
    </x-terms-modal>
</div>
</template>
