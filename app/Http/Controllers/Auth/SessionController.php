<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyAccount;
use App\Models\Representative;
use App\Models\User;
use App\Support\SpeakUp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

/**
 * Replaces Firebase Auth in frontend/src/contexts/AuthContext.tsx for the
 * email/password paths the landing walkthrough calls.
 */
class SessionController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $user = $this->authenticate($request);

        return response()->json([
            'redirect' => $user->homePath(),
        ]);
    }

    public function adminLogin(Request $request)
    {
        $user = $this->authenticate($request);

        if (! $user->isStaff()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Access denied. You must be an administrator or CODI member to access this area.',
            ]);
        }

        return redirect('/admin/dashboard');
    }

    private function authenticate(Request $request): User
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower($data['email']);
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password. Please try again.',
            ]);
        }

        if ($user->is_deleted || $user->is_active === false || $user->is_suspended) {
            throw ValidationException::withMessages([
                'email' => 'This account has been disabled.',
            ]);
        }

        // Admins bypass email verification, matching AuthContext.login.
        if (! $user->email_verified_at && ! $user->isPlatformAdmin()) {
            throw ValidationException::withMessages([
                'email' => 'Please verify your email before logging in. Check your inbox for the verification link.',
            ]);
        }

        if (! $user->is_registered && ! SpeakUp::isAdminEmail($user->email)) {
            throw ValidationException::withMessages([
                'email' => 'You are not registered in the system. Please complete the registration process first.',
            ]);
        }

        if ($user->isPlatformAdmin() && ! $user->is_admin) {
            $user->forceFill(['is_admin' => true])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();
        $this->touchRepresentative($user);

        return $user;
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed'],
            'agree_terms' => ['accepted'],
        ]);

        $email = strtolower($data['email']);

        if (! SpeakUp::isGordonEmail($email) && ! SpeakUp::isAdminEmail($email)) {
            throw ValidationException::withMessages([
                'email' => 'Only @gordoncollege.edu.ph email addresses are allowed.',
            ]);
        }

        if ($message = SpeakUp::passwordError($data['password'])) {
            throw ValidationException::withMessages(['password' => $message]);
        }

        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This email is already registered. Please sign in instead.',
            ]);
        }

        $user = User::query()->create([
            'email' => $email,
            'password' => $data['password'],
            'display_name' => $request->string('display_name')->toString() ?: null,
            'provider' => 'email',
            'is_admin' => SpeakUp::isAdminEmail($email),
            'is_active' => true,
            'is_deleted' => false,
            'is_registered' => true,
            'terms_accepted' => true,
            'privacy_accepted' => true,
            'registered_at' => now(),
        ]);

        $link = URL::temporarySignedRoute(
            'verification.verify',
            now()->addDay(),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $emailed = $this->sendVerificationMail($user, $link);

        return response()->json([
            'verificationEmailSent' => $emailed,
            'verificationLink' => $emailed ? null : $link,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['redirect' => '/']);
        }

        return redirect('/');
    }

    public function checkVerification(Request $request): JsonResponse
    {
        $user = $this->userFromCredentials($request);

        if ($user->email_verified_at) {
            Auth::login($user);
            $request->session()->regenerate();

            return response()->json(['verified' => true, 'redirect' => $user->homePath()]);
        }

        return response()->json(['verified' => false]);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $user = $this->userFromCredentials($request);

        if ($user->email_verified_at) {
            return response()->json(['emailed' => true]);
        }

        $link = URL::temporarySignedRoute(
            'verification.verify',
            now()->addDay(),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $emailed = $this->sendVerificationMail($user, $link);

        return response()->json([
            'emailed' => $emailed,
            'verificationLink' => $emailed ? null : $link,
        ]);
    }

    public function verify(Request $request, int $id, string $hash)
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals($hash, sha1($user->email))) {
            abort(403, 'This link is invalid or has already been used.');
        }

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect('/?auth=login')->with('toast', [
            'title' => 'Email verified',
            'description' => 'You can sign in now.',
        ]);
    }

    private function userFromCredentials(Request $request): User
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', strtolower($data['email']))->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Could not check verification. Please try again.',
            ]);
        }

        return $user;
    }

    private function sendVerificationMail(User $user, string $link): bool
    {
        try {
            Mail::mailer('verification')
                ->to($user->email)
                ->send(new VerifyAccount($user, $link));

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }

    private function touchRepresentative(User $user): void
    {
        $rep = $user->representative;
        if ($rep) {
            $rep->forceFill(['last_login_at' => now(), 'last_active' => now()])->save();
        } elseif ($user->isPlatformAdmin()) {
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
    }
}
