@php
    $pushConfig = [
        'vapidKey' => (string) config('services.firebase.vapid_key'),
        'firebase' => [
            'apiKey' => (string) config('services.firebase.api_key'),
            'authDomain' => (string) config('services.firebase.auth_domain'),
            'projectId' => (string) config('services.firebase.project_id'),
            'storageBucket' => (string) config('services.firebase.storage_bucket'),
            'messagingSenderId' => (string) config('services.firebase.messaging_sender_id'),
            'appId' => (string) config('services.firebase.app_id'),
        ],
    ];
@endphp
@once
    <script type="application/json" id="speakup-push-config">@json($pushConfig)</script>
@endonce
<div class="rounded-lg border-0 bg-card text-card-foreground shadow-sm" x-data="pushSettings()">
    <div class="flex flex-col space-y-1.5 border-b bg-gradient-to-r from-emerald-50 to-teal-50 p-6 pb-3 dark:from-[#1D9E75]/15 dark:to-teal-500/10">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h2 class="flex items-center gap-2 text-base font-semibold leading-none">
                    <x-lucide-smartphone class="h-4 w-4 text-[#1D9E75]" />
                    Mobile Push (PWA)
                </h2>
                <p class="mt-1 text-xs text-muted-foreground">Lock-screen alerts when SpeakUp GC is closed or in the background.</p>
            </div>
            <span
                class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                :class="pushEnabled ? 'border-emerald-200 bg-emerald-100 text-emerald-700' : 'border-gray-200 bg-gray-100 text-gray-600'"
                x-text="pushEnabled ? 'On' : 'Off'"
            >Off</span>
        </div>
    </div>
    <div class="space-y-3 p-6 pt-4">
        <p class="text-xs leading-relaxed text-gray-600">
            <strong>iPhone:</strong> Safari → Share → Add to Home Screen, open the app, then enable push (iOS 16.4+). <strong>Android:</strong> Chrome → Install app / Add to Home screen.
        </p>
        <p x-show="!configured" x-cloak class="rounded-lg border border-amber-200 bg-amber-50 p-2 text-xs text-amber-700">
            Admin setup needed: add <code class="font-mono">FIREBASE_VAPID_KEY</code> and the Firebase web config (Firebase → Project settings → Cloud Messaging → Web Push certificates).
        </p>
        <button
            type="button"
            @click="toggle()"
            :disabled="pushBusy || !supported"
            class="inline-flex h-9 items-center justify-center rounded-lg px-3 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-60"
            :class="pushEnabled ? 'border border-gray-200 bg-white text-gray-800 hover:bg-gray-50' : 'bg-[#1D9E75] text-white hover:bg-[#178F65]'"
        >
            <span x-show="pushBusy" x-cloak class="mr-2 h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
            <x-lucide-smartphone x-show="!pushBusy" class="mr-2 h-3.5 w-3.5" />
            <span x-text="!supported ? 'Not supported on this browser' : (pushBusy ? 'Please wait…' : (pushEnabled ? 'Disable push on this device' : 'Enable push on this device'))">Enable push on this device</span>
        </button>
    </div>
</div>
