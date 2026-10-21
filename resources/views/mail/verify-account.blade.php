<x-mail::message>
# Verify your SpeakUp GC account

Click the button below to verify **{{ $user->email }}** and activate your account. This link expires in 24 hours.

<x-mail::button :url="$url">
Verify email
</x-mail::button>

If you did not create an account, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
