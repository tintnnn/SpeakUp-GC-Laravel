<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SpeakUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $email = strtolower($data['email']);

        if (! SpeakUp::isGordonEmail($email) && ! SpeakUp::isAdminEmail($email)) {
            throw ValidationException::withMessages([
                'email' => 'Only @gordoncollege.edu.ph email addresses are allowed.',
            ]);
        }

        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => 'Too many attempts. Please try again later.',
            ]);
        }

        // Always report success when the address is allowed, matching the original
        // branded reset which does not reveal whether the account exists — except
        // Password::sendResetLink returns INVALID_USER. The original Firebase flow
        // errors on unknown users. Surface that.
        if ($status === Password::INVALID_USER) {
            throw ValidationException::withMessages([
                'email' => 'No account found with this email.',
            ]);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => 'Failed to send reset email. Please try again.',
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('toast', [
            'title' => 'Reset Email Sent',
            'description' => "We've sent password reset instructions to {$email}",
        ])->with('reset_sent', $email);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed'],
        ]);

        if ($message = SpeakUp::passwordError($data['password'])) {
            throw ValidationException::withMessages(['password' => $message]);
        }

        $status = Password::reset(
            [
                'email' => strtolower($data['email']),
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            $message = $status === Password::INVALID_TOKEN
                ? 'This password reset link is invalid or has expired. Please request a new one.'
                : 'Failed to reset password. Please try again.';

            throw ValidationException::withMessages(['email' => $message]);
        }

        return redirect('/?auth=login')->with('toast', [
            'title' => 'Password updated',
            'description' => 'Sign in with your new password.',
        ]);
    }
}
