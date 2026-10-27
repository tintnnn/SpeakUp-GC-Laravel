<?php

namespace App\Support;

use App\Models\Complaint;
use App\Models\Notification;
use App\Models\Representative;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SpeakUp
{
    public static function isAdminEmail(?string $email): bool
    {
        if (! $email) {
            return false;
        }

        return in_array(strtolower($email), config('speakup.admin_emails'), true);
    }

    public static function isGordonEmail(?string $email): bool
    {
        if (! $email) {
            return false;
        }

        return str_ends_with(strtolower($email), '@'.config('speakup.email_domain'));
    }

    /** Case types where the handler's name stays hidden from the complainant. */
    public static function isSensitiveCaseType(?string $type): bool
    {
        return in_array(strtolower((string) $type), [
            'sexual_harassment',
            'gender_based_sexual_harassment',
            'gbvh',
            'gbv',
        ], true);
    }

    /** Password rules from frontend/src/utils/passwordValidation.ts */
    public static function passwordError(?string $password): ?string
    {
        $password ??= '';
        $missing = [];
        if (strlen($password) < 8) {
            $missing[] = 'Password must be at least 8 characters long';
        }
        if (! preg_match('/[A-Z]/', $password)) {
            $missing[] = 'Password must contain at least one uppercase letter';
        }
        if (! preg_match('/[a-z]/', $password)) {
            $missing[] = 'Password must contain at least one lowercase letter';
        }
        if (! preg_match('/\d/', $password)) {
            $missing[] = 'Password must contain at least one number';
        }

        return $missing === [] ? null : 'Password does not meet security requirements: '.implode('. ', $missing);
    }

    /** Same rule as the React alias helper: Alias, then Alias(1), Alias(2), … */
    public static function uniqueAlias(string $desired, ?int $exceptUserId = null): string
    {
        $base = trim($desired);
        $taken = User::query()
            ->when($exceptUserId, fn ($query) => $query->where('id', '!=', $exceptUserId))
            ->whereNotNull('alias')
            ->pluck('alias')
            ->map(fn ($alias) => mb_strtolower(trim((string) $alias)))
            ->flip();

        if (! $taken->has(mb_strtolower($base))) {
            return $base;
        }

        $n = 1;
        while ($taken->has(mb_strtolower($base.'('.$n.')'))) {
            $n++;
        }

        return $base.'('.$n.')';
    }

    public static function flag(string $key, bool $default = false): bool
    {
        $value = DB::table('system_settings')->where('key', $key)->value('value');
        if ($value === null) {
            return $default;
        }
        if (is_bool($value)) {
            return $value;
        }
        $decoded = json_decode((string) $value, true);

        return (bool) ($decoded ?? $value);
    }

    /** In-app alert for admins and CODI when a complaint is filed. */
    public static function notifyStaffOfComplaint(Complaint $complaint): void
    {
        $type = str_replace('_', ' ', (string) ($complaint->type ?: 'formal'));
        $caseNumber = $complaint->case_number ?: (string) $complaint->id;
        $staffIds = Representative::query()
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'handler', 'codi'])
            ->pluck('user_id');

        $staff = User::query()
            ->with('representative')
            ->where('is_deleted', false)
            ->where(function ($query) use ($staffIds) {
                $query->where('is_admin', true)->orWhereIn('id', $staffIds);
            })
            ->get()
            ->filter(fn (User $member) => $member->id !== $complaint->complainant_id)
            ->unique('id');

        foreach ($staff as $member) {
            $already = Notification::query()
                ->where('user_id', $member->id)
                ->where('complaint_id', $complaint->id)
                ->where('type', 'complaint_created')
                ->exists();
            if ($already) {
                continue;
            }

            $isCodi = in_array($member->representative?->role, ['handler', 'codi'], true) && ! $member->isPlatformAdmin();
            $member->appNotifications()->create([
                'type' => 'complaint_created',
                'priority' => 'high',
                'status' => 'unread',
                'title' => $isCodi ? 'New Case in Queue' : 'New Complaint Submitted',
                'message' => $isCodi
                    ? 'A new '.$type.' complaint is waiting in the case queue. Case ID: '.$caseNumber
                    : 'A new '.$type.' complaint has been submitted. Case ID: '.$caseNumber,
                'complaint_id' => $complaint->id,
                'action_url' => '/admin/reports?report='.$complaint->id.'&tab=details',
                'action_label' => $isCodi ? 'Open Case Queue' : 'View Complaint',
                'created_at' => $complaint->filed_at ?: now(),
            ]);
        }
    }
}
