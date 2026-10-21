import { getApps, initializeApp } from 'firebase/app';
import { getMessaging, getToken, isSupported } from 'firebase/messaging';
import { api, errorMessage } from '../http';

const TOKEN_KEY = 'speakup_fcm_token';
const SW_PATH = '/firebase-messaging-sw.js';
const SW_SCOPE = '/firebase-cloud-messaging-push-scope';

function readConfig() {
    const node = document.getElementById('speakup-push-config');
    if (!node) return { vapidKey: '', firebase: {} };
    try {
        return JSON.parse(node.textContent || '{}');
    } catch {
        return { vapidKey: '', firebase: {} };
    }
}

function waitForActive(registration) {
    if (registration.active) return Promise.resolve();
    const worker = registration.installing || registration.waiting;
    if (!worker) {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                if (registration.active) resolve();
                else reject(new Error('Service worker failed to activate'));
            }, 150);
        });
    }

    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => reject(new Error('Service worker activation timed out')), 15000);
        const onState = () => {
            if (worker.state === 'activated' || registration.active) {
                clearTimeout(timer);
                worker.removeEventListener('statechange', onState);
                resolve();
            } else if (worker.state === 'redundant') {
                clearTimeout(timer);
                worker.removeEventListener('statechange', onState);
                reject(new Error('Service worker became redundant'));
            }
        };
        worker.addEventListener('statechange', onState);
        onState();
    });
}

export function pushSettings() {
    return {
        pushEnabled: false,
        pushBusy: false,
        supported: false,
        configured: false,
        async init() {
            const config = readConfig();
            const firebase = config.firebase || {};
            this.configured = Boolean(
                config.vapidKey && firebase.apiKey && firebase.projectId && firebase.messagingSenderId && firebase.appId
            );
            this.supported = 'Notification' in window && 'serviceWorker' in navigator && 'PushManager' in window;
            if (this.supported) {
                try {
                    this.supported = await isSupported();
                } catch {
                    this.supported = false;
                }
            }
            this.pushEnabled = this.supported
                && Notification.permission === 'granted'
                && Boolean(localStorage.getItem(TOKEN_KEY));
        },
        async toggle() {
            if (this.pushBusy || !this.supported) return;
            this.pushBusy = true;
            try {
                if (this.pushEnabled) {
                    const token = localStorage.getItem(TOKEN_KEY);
                    if (token) await api('/push-token', { method: 'DELETE', body: { token } });
                    localStorage.removeItem(TOKEN_KEY);
                    this.pushEnabled = false;
                    window.toast?.({
                        title: 'Push disabled',
                        description: 'This device will no longer receive lock-screen alerts.',
                    });
                    return;
                }

                if (!this.configured) {
                    window.toast?.({
                        title: 'Could not enable push',
                        description: 'Admin setup needed: add FIREBASE_VAPID_KEY and the Firebase web config.',
                        variant: 'destructive',
                    });
                    return;
                }

                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    window.toast?.({
                        title: 'Could not enable push',
                        description: permission === 'denied' ? 'Permission denied' : 'Permission not granted',
                        variant: 'destructive',
                    });
                    return;
                }

                const config = readConfig();
                const registration = await navigator.serviceWorker.register(SW_PATH, { scope: SW_SCOPE });
                await waitForActive(registration);
                const app = getApps().length ? getApps()[0] : initializeApp(config.firebase);
                const token = await getToken(getMessaging(app), {
                    vapidKey: config.vapidKey,
                    serviceWorkerRegistration: registration,
                });
                if (!token) throw new Error('Empty push token');

                await api('/push-token', {
                    body: {
                        token,
                        userAgent: navigator.userAgent || '',
                        platform: navigator.platform || '',
                    },
                });
                localStorage.setItem(TOKEN_KEY, token);
                this.pushEnabled = true;
                window.toast?.({
                    title: 'Push enabled',
                    description: 'You will get case updates even when SpeakUp GC is closed.',
                });
            } catch (error) {
                window.toast?.({
                    title: 'Could not update push',
                    description: errorMessage(error, 'Install SpeakUp GC as an app, then try again.'),
                    variant: 'destructive',
                });
            } finally {
                this.pushBusy = false;
            }
        },
    };
}
