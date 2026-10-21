import { api, errorMessage } from '../http';

const MESSAGES = {
    alias: 'Hi! I\'m Laya, your SpeakUp GC guide. 👋\n\nFirst, let\'s set up your alias — a pseudonym that will be used to protect your identity in any complaint case. It doesn\'t have to be your real name. You can use something like "BlueStar22" or any name that feels comfortable to you.\n\nYou can change this anytime from My Profile.',
    terms: 'Perfect! Now, let\'s review the Terms & Conditions. 📋\n\nThese terms outline acceptable use, your rights, and the legal framework governing SpeakUp GC under Philippine law (RA 11313) and Gordon College CODI.\n\nPlease read carefully and confirm your agreement to proceed.',
    privacy: 'Thank you for accepting the terms! 🔒\n\nNow, please review our Privacy Policy below.\n\nSpeakUp GC is committed to protecting your personal data in compliance with the Data Privacy Act of 2012 (RA 10173). Your information is handled with strict confidentiality.\n\nYou must separately agree to our privacy practices as required by Philippine law.',
    done: 'You\'re all set! 🌟\n\nWelcome to SpeakUp GC. Your profile is ready and your privacy is protected. You can now access your dashboard anytime.\n\nRemember: you are not alone, and it\'s safe to speak up.',
};

export function profileSetup() {
    return {
        step: 'alias',
        alias: '',
        aliasError: '',
        termsAccepted: false,
        privacyAccepted: false,
        saving: false,
        displayed: '',
        typing: false,
        timer: null,
        init() {
            this.play();
        },
        play() {
            clearTimeout(this.timer);
            const full = MESSAGES[this.step] || '';
            this.displayed = '';
            this.typing = true;
            let i = 0;
            const tick = () => {
                i += 1;
                this.displayed = full.slice(0, i);
                if (i < full.length) this.timer = setTimeout(tick, 14);
                else this.typing = false;
            };
            this.timer = setTimeout(tick, 100);
        },
        go(step) {
            this.step = step;
            this.play();
        },
        nextAlias() {
            const trimmed = this.alias.trim();
            if (!trimmed) {
                this.aliasError = 'Please enter an alias before continuing.';
                return;
            }
            if (trimmed.length < 3) {
                this.aliasError = 'Your alias must be at least 3 characters.';
                return;
            }
            if (trimmed.length > 30) {
                this.aliasError = 'Your alias must be 30 characters or fewer.';
                return;
            }
            this.aliasError = '';
            this.alias = trimmed;
            this.go('terms');
        },
        async finish() {
            if (!this.privacyAccepted || this.saving) return;
            this.saving = true;
            try {
                const data = await api('/account/setup', { body: { alias: this.alias } });
                this.alias = data.alias || this.alias;
                this.go('done');
            } catch (error) {
                window.toast?.({
                    title: 'Could not save profile',
                    description: errorMessage(error, 'Please try again.'),
                    variant: 'destructive',
                });
            } finally {
                this.saving = false;
            }
        },
        dashboard() {
            window.location.assign('/dashboard');
        },
    };
}
