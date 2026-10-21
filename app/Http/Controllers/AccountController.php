<?php

namespace App\Http\Controllers;

use App\Support\SpeakUp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function completeSetup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'alias' => ['required', 'string', 'min:3', 'max:30'],
        ]);

        $user = $request->user();
        $alias = SpeakUp::uniqueAlias($data['alias'], $user->id);

        $user->forceFill([
            'alias' => $alias,
            'notification_preference' => 'both',
            'terms_accepted' => true,
            'privacy_accepted' => true,
            'profile_setup_complete' => true,
            'profile_setup_completed_at' => now(),
        ])->save();

        $preferences = DB::table('notification_preferences')->where('user_id', $user->id);
        if ($preferences->exists()) {
            $preferences->update([
                'email_address' => $user->email,
                'in_app_enabled' => true,
                'email_enabled' => true,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('notification_preferences')->insert([
                'user_id' => $user->id,
                'email_address' => $user->email,
                'in_app_enabled' => true,
                'push_enabled' => false,
                'email_enabled' => true,
                'updated_at' => now(),
            ]);
        }

        return response()->json(['alias' => $alias]);
    }

    public function storePushToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'userAgent' => ['nullable', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'max:64'],
        ]);

        $user = $request->user();
        $hash = hash('sha256', $data['token']);
        $existing = DB::table('fcm_tokens')->where('token_hash', $hash)->first();

        if ($existing) {
            DB::table('fcm_tokens')->where('token_hash', $hash)->update([
                'user_id' => $user->id,
                'token' => $data['token'],
                'user_agent' => $data['userAgent'] ?? null,
                'platform' => $data['platform'] ?? null,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('fcm_tokens')->insert([
                'user_id' => $user->id,
                'token' => $data['token'],
                'token_hash' => $hash,
                'user_agent' => $data['userAgent'] ?? null,
                'platform' => $data['platform'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('notification_preferences')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'email_address' => $user->email,
                'in_app_enabled' => true,
                'email_enabled' => true,
                'push_enabled' => true,
                'updated_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function destroyPushToken(Request $request): JsonResponse
    {
        $token = trim((string) $request->input('token', ''));
        if ($token !== '') {
            DB::table('fcm_tokens')
                ->where('token_hash', hash('sha256', $token))
                ->where('user_id', $request->user()->id)
                ->delete();
        }

        return response()->json(['ok' => true]);
    }

    public function messagingServiceWorker(): Response
    {
        $config = json_encode([
            'apiKey' => (string) config('services.firebase.api_key'),
            'authDomain' => (string) config('services.firebase.auth_domain'),
            'projectId' => (string) config('services.firebase.project_id'),
            'storageBucket' => (string) config('services.firebase.storage_bucket'),
            'messagingSenderId' => (string) config('services.firebase.messaging_sender_id'),
            'appId' => (string) config('services.firebase.app_id'),
            'measurementId' => (string) config('services.firebase.measurement_id'),
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        $script = <<<JS
importScripts('https://www.gstatic.com/firebasejs/11.0.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/11.0.2/firebase-messaging-compat.js');

firebase.initializeApp({$config});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
  const title = payload.notification?.title || payload.data?.title || 'SpeakUp GC';
  const body = payload.notification?.body || payload.data?.body || payload.data?.message || '';
  const actionUrl = payload.data?.actionUrl || payload.fcmOptions?.link || '/notifications';
  const tag = payload.data?.notificationId || payload.data?.tag || 'speakup-gc';

  self.registration.showNotification(title, {
    body,
    icon: '/icon-192x192.png',
    badge: '/icon-192x192.png',
    tag,
    renotify: true,
    data: { actionUrl, ...(payload.data || {}) },
    vibrate: [120, 60, 120],
  });
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const target = event.notification.data?.actionUrl || '/notifications';
  const url = new URL(target, self.location.origin).href;

  event.waitUntil(
    (async () => {
      const clientsList = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
      for (const client of clientsList) {
        if ('focus' in client) {
          await client.focus();
          if ('navigate' in client) {
            try { await client.navigate(url); } catch (_) {}
          }
          return;
        }
      }
      if (self.clients.openWindow) await self.clients.openWindow(url);
    })()
  );
});
JS;

        return response($script, 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Service-Worker-Allowed' => '/',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
