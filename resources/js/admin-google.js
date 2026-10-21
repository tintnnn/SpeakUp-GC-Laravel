import { getApps, initializeApp } from 'firebase/app';
import { getAuth, GoogleAuthProvider, signInWithPopup, signOut } from 'firebase/auth';
import { api, ApiError } from './http';

const AUTH_ERROR_MESSAGES = {
    'auth/popup-closed-by-user': 'Sign-in was cancelled.',
    'auth/cancelled-popup-request': 'Sign-in was cancelled.',
    'auth/popup-blocked': 'Pop-up was blocked. Please allow pop-ups and try again.',
    'auth/unauthorized-domain': 'This domain is not authorized for Google Sign-In. Open the admin portal on an authorized domain.',
    'auth/operation-not-allowed': 'This sign-in method is not available. Please try another way.',
    'auth/user-disabled': 'This account has been disabled.',
    'auth/network-request-failed': 'Network error. Please check your connection and try again.',
    'auth/account-exists-with-different-credential': 'An account already exists with this email using a different sign-in method.',
    'auth/internal-error': 'Something went wrong. Please try again.',
};

function readable(error) {
    const code = error?.code;
    if (code && AUTH_ERROR_MESSAGES[code]) return AUTH_ERROR_MESSAGES[code];
    const message = String(error?.message || '');
    if (/admin_policy_enforced/i.test(message)) {
        return 'Google sign-in was blocked for this school account. Use an allowed admin Google account, or sign in with email and password.';
    }
    if (message && !/Firebase:/i.test(message) && !/auth\//.test(message)) return message;
    return 'Google Sign-In failed. Please try again.';
}

const button = document.getElementById('admin-google-button');
const configNode = document.getElementById('admin-firebase-config');

if (button && configNode) {
    const config = JSON.parse(configNode.textContent || '{}');
    const label = button.querySelector('[data-label]');
    const busy = button.querySelector('[data-busy]');
    const errorEl = document.getElementById('admin-google-error');

    button.addEventListener('click', async () => {
        errorEl?.classList.add('hidden');
        button.disabled = true;
        label?.classList.add('hidden');
        busy?.classList.remove('hidden');
        busy?.classList.add('inline-flex');

        try {
            const existing = getApps().find((app) => app.name === 'speakup-admin');
            const app = existing || initializeApp(config, 'speakup-admin');
            const auth = getAuth(app);
            const provider = new GoogleAuthProvider();
            provider.setCustomParameters({ prompt: 'select_account' });
            const result = await signInWithPopup(auth, provider);
            const idToken = await result.user.getIdToken();
            await signOut(auth);
            const data = await api('/auth/google/firebase', { body: { idToken } });
            window.location.assign(data.redirect || '/admin/dashboard');
        } catch (error) {
            const message = error instanceof ApiError ? error.message : readable(error);
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            }
            button.disabled = false;
            label?.classList.remove('hidden');
            busy?.classList.add('hidden');
            busy?.classList.remove('inline-flex');
        }
    });
}
