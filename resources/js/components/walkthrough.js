/**
 * Port of the stateful parts of frontend/src/views/Landing.tsx and
 * frontend/src/components/WalkthroughModal.tsx.
 *
 * Firebase Auth calls are replaced with Laravel JSON endpoints:
 *   POST /login                    (AuthContext.login)
 *   POST /register                 (AuthContext.register)  -> { verificationEmailSent, verificationLink }
 *   POST /email/check-verification (signup-done "Check Verification")
 *   POST /email/resend             (signup-done "Resend Email")
 */
import { api, errorMessage } from '../http';
import { validatePassword } from '../password-validation';

const WALKTHROUGH_SEEN_KEY = 'speakup_walkthrough_seen';
const VIEW_ORDER = ['choose', 'login', 'signup-email', 'signup-password', 'signup-done', 'explore'];

const LIGHT = {
    primary: '#1D9E75',
    primaryHover: '#178F65',
    primaryLight: 'rgba(26, 122, 69, 0.08)',
    primaryBorder: 'rgba(26, 122, 69, 0.2)',
    heading: '#1a2e1f',
    body: '#4b5e52',
    muted: '#7a8f82',
    placeholder: '#9ca8a0',
    modalBg: '#ffffff',
    inputBg: '#f5f9f7',
    inputBorder: '#d4e4db',
    inputFocus: '#1D9E75',
    cardBg: '#f0f7f3',
    cardBorder: '#d4e4db',
    divider: '#e2ece7',
    warningBg: '#fef9ee',
    warningBorder: '#f5d990',
    warningText: '#a16207',
    warningMuted: '#b88c2a',
    googleBg: '#ffffff',
};

const DARK = {
    primary: '#1D9E75',
    primaryHover: '#178F65',
    primaryLight: 'rgba(29, 158, 117, 0.2)',
    primaryBorder: 'rgba(29, 158, 117, 0.35)',
    heading: '#f3f4f6',
    body: '#c4cdc8',
    muted: '#8b9a94',
    placeholder: '#6b7c74',
    modalBg: '#18241f',
    inputBg: '#1c2c26',
    inputBorder: '#3d5c50',
    inputFocus: '#1D9E75',
    cardBg: '#1c2a24',
    cardBorder: '#2d5a48',
    divider: '#2a453c',
    warningBg: '#2a2318',
    warningBorder: 'rgba(251, 191, 36, 0.28)',
    warningText: '#fde68a',
    warningMuted: '#d4b36a',
    googleBg: '#1c2a24',
};

/** Landing page scope: owns the modal open state (Landing.tsx) */
export function landingPage({ initialView = null, hasAuthParam = false } = {}) {
    return {
        walkthrough: { isOpen: false, initialView: null },

        init() {
            // ?auth=login | ?auth=signup opens the modal directly and cleans the URL (router.replace('/'))
            if (initialView) {
                this.walkthrough.initialView = initialView;
                this.walkthrough.isOpen = true;
                try {
                    window.history.replaceState({}, '', '/');
                } catch {}
            }
            // First visit: open the walkthrough after 800ms
            const hasSeen = localStorage.getItem(WALKTHROUGH_SEEN_KEY);
            if (!hasSeen && !hasAuthParam) {
                setTimeout(() => {
                    this.walkthrough.initialView = null;
                    this.walkthrough.isOpen = true;
                }, 800);
            }
            // Hash navigation (/#features, /#about)
            if (location.hash) {
                const el = document.getElementById(location.hash.replace('#', ''));
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        openWalkthrough(view) {
            this.walkthrough.initialView = view || null;
            this.walkthrough.isOpen = true;
        },

        closeWalkthrough() {
            this.walkthrough.isOpen = false;
            this.walkthrough.initialView = null;
        },
    };
}

/** Modal scope (WalkthroughModal.tsx) – reads `walkthrough` from the landingPage parent scope */
export function walkthroughModal() {
    return {
        view: 'choose',
        direction: 'forward',
        closeHover: false,
        hover: {},
        focus: {},

        loginData: { email: '', password: '' },
        showLoginPassword: false,
        needsVerification: false,
        isLoginLoading: false,
        isGoogleLoading: false,

        signupData: { email: '', password: '', confirmPassword: '', agreeTerms: false },
        showSignupPassword: false,
        showConfirmPassword: false,
        isSignupLoading: false,
        isSignupGoogleLoading: false,
        showTermsModal: false,
        showPrivacyModal: false,
        resendCooldown: 0,
        verificationEmailSent: true,
        verificationLink: null,
        _cooldownTimer: null,

        get colors() {
            return this.$store.theme.isDark ? DARK : LIGHT;
        },

        get progressWidth() {
            const v = this.view;
            return v === 'choose' ? '20%' : v === 'login' ? '50%' : v === 'signup-email' ? '40%' : v === 'signup-password' ? '70%' : v === 'signup-done' ? '90%' : '100%';
        },

        init() {
            this.view = this.walkthrough.initialView || 'choose';
            this.direction = 'forward';
            document.body.style.overflow = 'hidden';
            this.$watch('view', (v) => {
                if (v === 'signup-done' && this.verificationEmailSent) this.startCooldown(60);
            });
        },

        destroy() {
            document.body.style.overflow = '';
            clearInterval(this._cooldownTimer);
        },

        startCooldown(seconds) {
            this.resendCooldown = seconds;
            clearInterval(this._cooldownTimer);
            this._cooldownTimer = setInterval(() => {
                this.resendCooldown -= 1;
                if (this.resendCooldown <= 0) clearInterval(this._cooldownTimer);
            }, 1000);
        },

        handleDismiss() {
            localStorage.setItem(WALKTHROUGH_SEEN_KEY, 'true');
            this.closeWalkthrough();
        },

        goTo(next) {
            const curIdx = VIEW_ORDER.indexOf(this.view);
            const nextIdx = VIEW_ORDER.indexOf(next);
            this.direction = nextIdx >= curIdx ? 'forward' : 'back';
            this.view = next;
        },

        finishRedirect(redirect) {
            localStorage.setItem(WALKTHROUGH_SEEN_KEY, 'true');
            window.location.assign(redirect || '/dashboard');
        },

        // ─── Login handlers ───
        async handleLoginSubmit() {
            if (!this.loginData.email || !this.loginData.password) {
                window.toast({ title: 'Missing fields', description: 'Please enter both email and password.', variant: 'destructive' });
                return;
            }
            this.isLoginLoading = true;
            this.needsVerification = false;
            try {
                const res = await api('/login', { body: { email: this.loginData.email, password: this.loginData.password } });
                window.toast({ title: 'Login Successful', description: 'Welcome back! Redirecting...' });
                this.finishRedirect(res?.redirect);
            } catch (error) {
                const message = errorMessage(error, 'Invalid email or password. Please try again.');
                if (message.includes('verify your email')) {
                    this.needsVerification = true;
                    window.toast({ title: 'Email not verified', description: 'Your account is saved. Use Resend verification email below.', variant: 'destructive' });
                } else {
                    window.toast({ title: 'Login failed', description: message, variant: 'destructive' });
                }
                this.isLoginLoading = false;
            }
        },

        async handleGoogleLogin() {
            this.isGoogleLoading = true;
            window.location.assign('/auth/google/redirect?intent=login');
        },

        // ─── Signup handlers ───
        async handleSignupEmailSubmit() {
            const emailRegex = /^[^\s@]+@gordoncollege\.edu\.ph$/;
            if (!emailRegex.test(this.signupData.email)) {
                window.toast({ title: 'Invalid Email Domain', description: 'Only @gordoncollege.edu.ph email addresses are allowed.', variant: 'destructive' });
                return;
            }
            if (!this.signupData.agreeTerms) {
                window.toast({ title: 'Error', description: 'You must agree to the Terms & Conditions and Privacy Policy', variant: 'destructive' });
                return;
            }
            this.goTo('signup-password');
            window.toast({ title: 'Email Accepted!', description: 'Please create a secure password for your account.' });
        },

        async handleSignupSubmit() {
            const pv = validatePassword(this.signupData.password);
            if (!pv.isValid) {
                window.toast({ title: 'Password Requirements Not Met', description: 'Must have 8+ chars, uppercase, lowercase, and a number', variant: 'destructive' });
                return;
            }
            if (this.signupData.password !== this.signupData.confirmPassword) {
                window.toast({ title: 'Error', description: 'Passwords do not match', variant: 'destructive' });
                return;
            }
            this.isSignupLoading = true;
            try {
                const result = await api('/register', {
                    body: { email: this.signupData.email, password: this.signupData.password, password_confirmation: this.signupData.confirmPassword, agree_terms: true },
                });
                this.verificationEmailSent = !!result.verificationEmailSent;
                this.verificationLink = result.verificationLink || null;
                this.goTo('signup-done');
                window.toast({
                    title: 'Account created!',
                    description: result.verificationEmailSent
                        ? 'Please check your email to verify your account.'
                        : result.verificationLink
                          ? 'Email could not be sent. Use Verify now to activate your account.'
                          : 'Account created, but verification could not be completed. Try Resend Email.',
                });
            } catch (error) {
                const message = errorMessage(error, 'Failed to create account.');
                if (message.includes('already registered')) {
                    const recovered = await this.recoverUnverifiedSignup();
                    if (recovered) return;
                }
                window.toast({ title: 'Error', description: message, variant: 'destructive' });
            } finally {
                this.isSignupLoading = false;
            }
        },

        async handleGoogleSignup() {
            this.isSignupGoogleLoading = true;
            window.location.assign('/auth/google/redirect?intent=signup');
        },

        async recoverUnverifiedSignup() {
            try {
                const sent = await api('/email/resend', { body: { email: this.signupData.email, password: this.signupData.password } });
                if (sent.alreadyVerified) {
                    this.loginData.email = this.signupData.email;
                    this.loginData.password = this.signupData.password;
                    this.goTo('login');
                    window.toast({ title: 'Already verified', description: 'This email is already registered. Sign in with the same password.', variant: 'destructive' });
                    return true;
                }
                this.verificationEmailSent = !!sent.emailed;
                this.verificationLink = null;
                this.goTo('signup-done');
                window.toast({
                    title: sent.emailed ? 'Verification sent' : 'Account already exists',
                    description: sent.emailed
                        ? 'This email was already registered. A new verification link is in your inbox. Check spam too.'
                        : 'This email is registered but not verified yet. Use Resend Email below.',
                    variant: sent.emailed ? 'default' : 'destructive',
                });
                return true;
            } catch {
                return false;
            }
        },

        async resendFromLogin() {
            if (this.resendCooldown > 0) return;
            if (!this.loginData.email || !this.loginData.password) {
                window.toast({ title: 'Missing fields', description: 'Enter the email and password you used when you signed up.', variant: 'destructive' });
                return;
            }
            this.isLoginLoading = true;
            try {
                const sent = await api('/email/resend', { body: { email: this.loginData.email, password: this.loginData.password } });
                if (sent.alreadyVerified) {
                    this.needsVerification = false;
                    window.toast({ title: 'Already verified', description: 'You can sign in now.' });
                    return;
                }
                if (sent.emailed) {
                    this.signupData.email = this.loginData.email;
                    this.signupData.password = this.loginData.password;
                    this.verificationEmailSent = true;
                    this.verificationLink = null;
                    this.needsVerification = false;
                    this.goTo('signup-done');
                    window.toast({ title: 'Verification sent', description: 'Check your inbox and spam folder, then click the link.' });
                    return;
                }
                window.toast({ title: 'Resend failed', description: 'Could not send the verification email. Please try again in a few minutes.', variant: 'destructive' });
            } catch (err) {
                window.toast({ title: 'Resend failed', description: errorMessage(err, 'Could not resend verification email. Please try again.'), variant: 'destructive' });
            } finally {
                this.isLoginLoading = false;
            }
        },

        async checkVerification() {
            try {
                this.isSignupLoading = true;
                if (!this.signupData.email || !this.signupData.password) {
                    window.toast({ title: 'Missing credentials', variant: 'destructive' });
                    return;
                }
                const res = await api('/email/check-verification', { body: { email: this.signupData.email, password: this.signupData.password } });
                if (res.verified) {
                    window.toast({ title: 'Email Verified', description: 'Redirecting to dashboard.' });
                    this.finishRedirect(res.redirect);
                    return;
                }
                window.toast({ title: 'Not Verified', description: 'Email not yet verified.', variant: 'destructive' });
            } catch (err) {
                window.toast({ title: 'Check Failed', description: errorMessage(err, 'Could not check verification. Please try again.'), variant: 'destructive' });
            } finally {
                this.isSignupLoading = false;
            }
        },

        async resendVerification() {
            if (this.resendCooldown > 0) return;
            try {
                this.isSignupLoading = true;
                if (!this.signupData.email || !this.signupData.password) {
                    window.toast({ title: 'Missing credentials', variant: 'destructive' });
                    return;
                }
                const sent = await api('/email/resend', { body: { email: this.signupData.email, password: this.signupData.password } });
                if (sent.emailed) {
                    this.verificationEmailSent = true;
                    this.verificationLink = null;
                    window.toast({ title: 'Verification Sent', description: 'Check inbox and spam.' });
                    this.startCooldown(60);
                    return;
                }
                if (sent.verificationLink) {
                    this.verificationLink = sent.verificationLink;
                    window.toast({ title: 'Use Verify now', description: 'Email still cannot send. Click Verify now to activate your account.' });
                    return;
                }
                window.toast({ title: 'Resend Failed', description: 'Could not send the verification email. Please try again in a few minutes.', variant: 'destructive' });
            } catch (err) {
                window.toast({ title: 'Resend Failed', description: errorMessage(err, 'Could not resend verification email. Please try again.'), variant: 'destructive' });
            } finally {
                this.isSignupLoading = false;
            }
        },
    };
}
