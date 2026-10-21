<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Representative;
use App\Models\User;
use App\Support\SpeakUp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Google sign-in for the admin portal only. Students keep email/password.
 * The button uses the same Firebase Google popup as the original app.
 * A completed login is accepted only for an admin allow-list address
 * or an active CODI / handler / admin representative.
 */
class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if ($request->query('intent') !== 'admin' || ! config('speakup.google_admin_sign_in_enabled')) {
            return redirect('/?auth=login')->with('toast', [
                'title' => 'Sign-In Failed',
                'description' => 'Google sign-in is available on the admin portal only.',
                'variant' => 'destructive',
            ]);
        }

        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return $this->fail('Google Sign-In is not configured yet. Add the OAuth client ID and secret on the server.');
        }

        $request->session()->put('google_auth_intent', 'admin');

        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->session()->pull('google_auth_intent') !== 'admin') {
            return $this->fail('Google sign-in is available on the admin portal only.');
        }

        if ($request->filled('error')) {
            $cancelled = $request->string('error')->toString() === 'access_denied';

            return $this->fail($cancelled
                ? 'Sign-in was cancelled. Please try again.'
                : 'Google Sign-In failed. Please try again.');
        }

        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable) {
            return $this->fail('Google Sign-In failed. Please try again.');
        }

        $result = $this->loginStaff($request, strtolower((string) $google->getEmail()), $google->getName() ?: $google->getNickname(), $google->getAvatar());

        return $result instanceof User
            ? redirect('/admin/dashboard')
            : $this->fail($result);
    }

    /**
     * The admin page already signed in with Firebase GoogleAuthProvider.
     * This checks that ID token and opens the Laravel session.
     */
    public function firebase(Request $request): JsonResponse
    {
        if (! config('speakup.google_admin_sign_in_enabled')) {
            return response()->json(['message' => 'Google sign-in is available on the admin portal only.'], 403);
        }

        $idToken = $request->string('idToken')->toString();
        $apiKey = (string) config('services.firebase.api_key');
        if ($idToken === '' || $apiKey === '') {
            return response()->json(['message' => 'Google Sign-In failed. Please try again.'], 422);
        }

        try {
            $response = Http::timeout(15)->post(
                'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key='.$apiKey,
                ['idToken' => $idToken]
            );
        } catch (Throwable) {
            return response()->json(['message' => 'Network error. Please check your connection and try again.'], 502);
        }

        $profile = $response->json('users.0');
        if (! $response->ok() || ! is_array($profile)) {
            return response()->json(['message' => 'Google Sign-In failed. Please try again.'], 401);
        }

        $result = $this->loginStaff(
            $request,
            strtolower((string) ($profile['email'] ?? '')),
            $profile['displayName'] ?? null,
            $profile['photoUrl'] ?? null,
        );

        if ($result instanceof User) {
            return response()->json(['redirect' => '/admin/dashboard']);
        }

        return response()->json(['message' => $result], 403);
    }

    private function loginStaff(Request $request, string $email, ?string $name, ?string $photo): User|string
    {
        if ($email === '') {
            return 'Google did not return an email address.';
        }

        $user = User::query()->where('email', $email)->first();
        $representative = Representative::query()->where('email', $email)->where('is_active', true)->first();
        $isStaff = $representative && in_array($representative->role, ['admin', 'handler', 'codi'], true);
        $isAdmin = SpeakUp::isAdminEmail($email) || ($user?->isPlatformAdmin() ?? false);

        if (! $isAdmin && ! $isStaff) {
            return 'Access denied. You must be an administrator or CODI member to access this area.';
        }

        if ($user && ($user->is_deleted || $user->is_active === false || $user->is_suspended)) {
            return 'This account has been disabled.';
        }

        if (! $user) {
            $user = User::query()->create([
                'email' => $email,
                'password' => Str::password(40),
                'display_name' => $name,
                'photo_url' => $photo,
                'provider' => 'google',
                'is_admin' => $isAdmin,
                'is_active' => true,
                'is_deleted' => false,
                'is_registered' => true,
                'email_verified_at' => now(),
                'registered_at' => now(),
            ]);
        } else {
            $user->forceFill([
                'display_name' => $user->display_name ?: $name,
                'photo_url' => $user->photo_url ?: $photo,
                'provider' => $user->provider ?: 'google',
                'is_admin' => $user->is_admin || $isAdmin,
                'is_registered' => true,
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();
        }

        $this->touchRepresentative($user, $representative);

        Auth::login($user);
        $request->session()->regenerate();

        return $user;
    }

    private function touchRepresentative(User $user, ?Representative $representative): void
    {
        if ($representative) {
            $representative->forceFill([
                'user_id' => $user->id,
                'last_login_at' => now(),
                'last_active' => now(),
            ])->save();

            return;
        }

        if (! $user->isPlatformAdmin()) {
            return;
        }

        Representative::query()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'display_name' => $user->display_name ?: $user->email,
            'role' => 'admin',
            'permissions' => ['view_cases', 'assign_cases', 'update_status', 'escalate_cases', 'resolve_cases', 'view_evidence', 'send_messages', 'view_analytics', 'manage_representatives'],
            'is_active' => true,
            'can_assign_cases' => true,
            'can_escalate_cases' => true,
            'can_resolve_cases' => true,
            'last_login_at' => now(),
            'last_active' => now(),
        ]);
    }

    private function fail(string $description): RedirectResponse
    {
        return redirect('/admin/login')->with('toast', [
            'title' => 'Sign-In Failed',
            'description' => $description,
            'variant' => 'destructive',
        ]);
    }
}
