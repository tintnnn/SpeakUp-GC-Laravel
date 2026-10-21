{{-- Port of frontend/src/views/admin/Settings.tsx + PushNotificationSettings.tsx --}}
<x-layouts.admin>
@php
    $user = auth()->user();
    $provider = $user?->provider ?? 'password';
    $isPasswordAccount = ! $user || $provider === '' || $provider === 'password';
    $isAdmin = true;
    if ($user) {
        $repRole = $user->representative?->role;
        $isAdmin = $user->isPlatformAdmin() || $repRole === 'admin';
    }
    $maintenanceMode = (bool) ($maintenanceMode ?? data_get($settings ?? [], 'maintenance_mode', false));
    $chatbotEnabled = (bool) ($chatbotEnabled ?? data_get($settings ?? [], 'chatbot_enabled', false));
    $locationCount = $locationCount ?? (($locations ?? collect())->count());
@endphp
<div
    class="space-y-6"
    x-data="{
        emailOn: true,
        urgentOn: true,
        maintenance: {{ $maintenanceMode ? 'true' : 'false' }},
        autoArchive: true,
        chatbot: {{ $chatbotEnabled ? 'true' : 'false' }}
    }"
>
    <div>
        <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500">Configuration</p>
        <h1 class="text-xl font-bold text-gray-900">Settings</h1>
        <p class="mt-1 text-sm text-gray-500">Configure system preferences and security</p>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="flex items-center gap-2 text-2xl font-semibold leading-none tracking-tight">
                <x-lucide-bell class="h-5 w-5" />
                Notification Preferences
            </h3>
            <p class="text-sm text-muted-foreground">Configure how you want to receive notifications about reports and user activity</p>
        </div>
        <div class="space-y-4 p-6 pt-0">
            <div class="flex items-center justify-between">
                <div>
                    <label for="email-notifications" class="text-sm font-medium leading-none">Email Notifications</label>
                    <p class="text-sm text-gray-500">Receive updates via email</p>
                </div>
                <button id="email-notifications" type="button" role="switch" :aria-checked="emailOn" @click="emailOn = !emailOn" class="inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors" :class="emailOn ? 'bg-primary' : 'bg-input'">
                    <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg transition-transform" :class="emailOn ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
            <div class="h-px w-full shrink-0 bg-border"></div>
            <div class="flex items-center justify-between">
                <div>
                    <label for="urgent-alerts" class="text-sm font-medium leading-none">Urgent Alerts</label>
                    <p class="text-sm text-gray-500">Get immediate notifications for urgent reports</p>
                </div>
                <button id="urgent-alerts" type="button" role="switch" :aria-checked="urgentOn" @click="urgentOn = !urgentOn" class="inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors" :class="urgentOn ? 'bg-primary' : 'bg-input'">
                    <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg transition-transform" :class="urgentOn ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
        </div>
    </div>

    @include('partials.push-settings')

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="flex items-center gap-2 text-2xl font-semibold leading-none tracking-tight">
                <x-lucide-shield class="h-5 w-5" />
                Security Settings
            </h3>
            <p class="text-sm text-muted-foreground">Manage your admin account security preferences</p>
        </div>
        <div class="space-y-4 p-6 pt-0">
            @if ($isPasswordAccount)
                <form class="space-y-4" @submit.prevent>
                    <div>
                        <label for="current-password" class="text-sm font-medium leading-none">Current Password</label>
                        <input id="current-password" type="password" autocomplete="current-password" class="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    </div>
                    <div>
                        <label for="new-password" class="text-sm font-medium leading-none">New Password</label>
                        <input id="new-password" type="password" autocomplete="new-password" class="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    </div>
                    <div>
                        <label for="confirm-password" class="text-sm font-medium leading-none">Confirm New Password</label>
                        <input id="confirm-password" type="password" autocomplete="new-password" class="mt-1 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    </div>
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90">Update Password</button>
                </form>
            @else
                <div class="space-y-3">
                    <p class="text-sm text-gray-500">Your account uses Google Sign-In. To set or change a password, request a reset link below.</p>
                    <button type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent">Send Password Reset Email</button>
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="flex items-center gap-2 text-2xl font-semibold leading-none tracking-tight">
                <x-lucide-alert-circle class="h-5 w-5" />
                System Preferences
            </h3>
            <p class="text-sm text-muted-foreground">Configure system-wide settings and defaults</p>
        </div>
        <div class="space-y-4 p-6 pt-0">
            <div class="flex items-center justify-between">
                <div>
                    <label for="maintenance-mode" class="text-sm font-medium leading-none">Maintenance Mode</label>
                    <p class="text-sm text-gray-500">Temporarily disable user access</p>
                </div>
                <button id="maintenance-mode" type="button" role="switch" :aria-checked="maintenance" @click="maintenance = !maintenance" class="inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors" :class="maintenance ? 'bg-primary' : 'bg-input'">
                    <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg transition-transform" :class="maintenance ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
            <div class="h-px w-full shrink-0 bg-border"></div>
            <div class="flex items-center justify-between">
                <div>
                    <label for="auto-archive" class="text-sm font-medium leading-none">Auto-Archive Reports</label>
                    <p class="text-sm text-gray-500">Automatically archive resolved reports after 30 days</p>
                </div>
                <button id="auto-archive" type="button" role="switch" :aria-checked="autoArchive" @click="autoArchive = !autoArchive" class="inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors" :class="autoArchive ? 'bg-primary' : 'bg-input'">
                    <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg transition-transform" :class="autoArchive ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
            @if ($isAdmin)
                <div class="h-px w-full shrink-0 bg-border"></div>
                <div class="flex items-center justify-between">
                    <div class="flex items-start gap-2 pr-4">
                        <x-lucide-bot class="mt-0.5 h-4 w-4 flex-shrink-0 text-qc-sage" />
                        <div>
                            <label for="chatbot-enabled" class="text-sm font-medium leading-none">AI Chatbot Assistant</label>
                            <p class="text-sm text-gray-500">Allow complainants to use the virtual assistant (Laya) for FAQs and case status inquiries.</p>
                        </div>
                    </div>
                    <button id="chatbot-enabled" type="button" role="switch" :aria-checked="chatbot" @click="chatbot = !chatbot" class="inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors" :class="chatbot ? 'bg-primary' : 'bg-input'">
                        <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg transition-transform" :class="chatbot ? 'translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="flex items-center gap-2 text-2xl font-semibold leading-none tracking-tight">
                <x-lucide-map-pin class="h-5 w-5" />
                Location Management
            </h3>
            <p class="text-sm text-muted-foreground">Manage rooms, buildings, and departments for complaint filing</p>
        </div>
        <div class="p-6 pt-0">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-500">{{ $locationCount }} location{{ $locationCount === 1 ? '' : 's' }}</p>
                <a href="/admin/locations" class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                    Manage Locations
                    <x-lucide-arrow-right class="ml-2 h-4 w-4" />
                </a>
            </div>
        </div>
    </div>
</div>
</x-layouts.admin>
