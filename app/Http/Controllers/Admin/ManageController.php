<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Representative;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManageController extends Controller
{
    public function storeRepresentative(Request $request)
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:handler,admin'],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $email = strtolower($data['email']);
        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            $user = User::query()->create([
                'email' => $email,
                'display_name' => $data['display_name'],
                'password' => $data['password'],
                'provider' => 'email',
                'is_admin' => $data['role'] === 'admin',
                'is_active' => true,
                'role' => $data['role'] === 'admin' ? 'admin' : 'handler',
                'email_verified_at' => now(),
                'profile_setup_complete' => true,
            ]);
        } else {
            $user->forceFill([
                'is_admin' => $data['role'] === 'admin' || $user->is_admin,
                'role' => $data['role'] === 'admin' ? 'admin' : 'handler',
                'is_active' => true,
            ])->save();
        }

        Representative::query()->updateOrCreate(
            ['email' => $email],
            [
                'user_id' => $user->id,
                'display_name' => $data['display_name'],
                'role' => $data['role'],
                'phone' => $data['phone'] ?: null,
                'department' => 'DEIU',
                'position' => $data['role'] === 'admin' ? 'Administrator' : 'CODI Member',
                'is_active' => true,
                'can_escalate_cases' => true,
                'can_resolve_cases' => true,
                'can_assign_cases' => $data['role'] === 'admin',
            ]
        );

        return back()->with('toast', [
            'title' => 'Staff saved',
            'description' => $data['display_name'].' can sign in at the CODI portal.',
        ]);
    }

    public function updateRepresentative(Request $request, Representative $representative)
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'role' => ['required', 'in:handler,admin'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $representative->update([
            'display_name' => $data['display_name'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?: null,
            'position' => $data['role'] === 'admin' ? 'Administrator' : 'CODI Member',
            'can_assign_cases' => $data['role'] === 'admin',
        ]);

        if ($representative->user) {
            $representative->user->forceFill([
                'display_name' => $data['display_name'],
                'role' => $data['role'] === 'admin' ? 'admin' : 'handler',
                'is_admin' => $data['role'] === 'admin' || $representative->user->isPlatformAdmin(),
            ])->save();
        }

        return back()->with('toast', [
            'title' => 'Staff updated',
            'description' => $data['display_name'].' was saved.',
        ]);
    }

    public function toggleRepresentative(Representative $representative)
    {
        $this->adminOnly(request());
        $representative->update(['is_active' => ! $representative->is_active]);

        return back()->with('toast', [
            'title' => $representative->is_active ? 'Staff activated' : 'Staff deactivated',
            'description' => $representative->display_name.' can '.($representative->is_active ? 'sign in again.' : 'no longer sign in.'),
        ]);
    }

    public function destroyRepresentative(Representative $representative)
    {
        $this->adminOnly(request());
        $assigned = Complaint::query()->where('assigned_representative_id', $representative->id)->where('is_deleted', false)->exists();
        if ($assigned) {
            $representative->update(['is_active' => false]);

            return back()->with('toast', [
                'title' => 'Staff deactivated',
                'description' => 'This member still has cases, so the account was deactivated instead of deleted.',
            ]);
        }

        $representative->delete();

        return back()->with('toast', [
            'title' => 'Staff removed',
            'description' => 'The representative record was deleted.',
        ]);
    }

    public function assignUser(Request $request, User $user)
    {
        $this->adminOnly($request);
        $data = $request->validate(['role' => ['required', 'in:handler,admin']]);

        Representative::query()->updateOrCreate(
            ['email' => $user->email],
            [
                'user_id' => $user->id,
                'display_name' => $user->display_name ?: $user->email,
                'role' => $data['role'],
                'department' => 'DEIU',
                'position' => $data['role'] === 'admin' ? 'Administrator' : 'CODI Member',
                'is_active' => true,
                'can_escalate_cases' => true,
                'can_resolve_cases' => true,
                'can_assign_cases' => $data['role'] === 'admin',
            ]
        );
        $user->forceFill([
            'role' => $data['role'] === 'admin' ? 'admin' : 'handler',
            'is_admin' => $data['role'] === 'admin' || $user->is_admin,
        ])->save();

        return back()->with('toast', [
            'title' => 'Role assigned',
            'description' => ($user->display_name ?: $user->email).' is now a '.($data['role'] === 'admin' ? 'administrator' : 'CODI member').'.',
        ]);
    }

    public function destroyUser(Request $request, User $user)
    {
        $this->adminOnly($request);
        abort_if($user->id === $request->user()->id || $user->isPlatformAdmin(), 403);

        $user->forceFill([
            'is_deleted' => true,
            'is_active' => false,
            'deleted_at' => now(),
            'deleted_by' => $request->user()->id,
        ])->save();

        return back()->with('toast', [
            'title' => 'User removed',
            'description' => 'The account can no longer sign in.',
        ]);
    }

    public function storeLocation(Request $request)
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:64'],
        ]);
        DB::table('locations')->insert([
            'name' => $data['name'],
            'category' => $data['category'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('toast', ['title' => 'Location added', 'description' => $data['name'].' is now on the filing form.']);
    }

    public function updateLocation(Request $request, int $location)
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:64'],
        ]);
        DB::table('locations')->where('id', $location)->update([
            'name' => $data['name'],
            'category' => $data['category'],
            'updated_at' => now(),
        ]);

        return back()->with('toast', ['title' => 'Location updated', 'description' => $data['name'].' was saved.']);
    }

    public function destroyLocation(int $location)
    {
        $this->adminOnly(request());
        DB::table('locations')->where('id', $location)->delete();

        return back()->with('toast', ['title' => 'Location deleted', 'description' => 'It will no longer appear on the filing form.']);
    }

    public function storeVideo(Request $request)
    {
        $this->adminOnly($request);

        return $this->saveVideo($request, null);
    }

    public function updateVideo(Request $request, int $video)
    {
        $this->adminOnly($request);

        return $this->saveVideo($request, $video);
    }

    public function destroyVideo(int $video)
    {
        $this->adminOnly(request());
        DB::table('mental_health_videos')->where('id', $video)->delete();

        return back()->with('toast', ['title' => 'Video deleted', 'description' => 'It was removed from the learning hub.']);
    }

    public function saveSettings(Request $request)
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'maintenance_mode' => ['nullable', 'boolean'],
            'chatbot_enabled' => ['nullable', 'boolean'],
        ]);
        foreach (['maintenance_mode', 'chatbot_enabled'] as $key) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => json_encode((bool) ($data[$key] ?? false)),
                    'updated_by' => $request->user()->id,
                    'updated_by_name' => $request->user()->display_name ?: $request->user()->email,
                    'updated_at' => now(),
                ]
            );
        }

        return back()->with('toast', ['title' => 'Settings saved', 'description' => 'Maintenance and Laya switches were stored.']);
    }

    public function escalate(Request $request, Complaint $complaint)
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $level = min(3, ((int) $complaint->escalation_level) + 1);
        $now = now();
        $complaint->update([
            'escalation_level' => $level,
            'is_escalated' => true,
            'escalated_at' => $now,
            'escalation_reason' => $data['reason'],
            'escalation_notes' => $data['notes'] ?? null,
            'last_activity_at' => $now,
        ]);
        DB::table('complaint_escalation_history')->insert([
            'complaint_id' => $complaint->id,
            'level' => $level,
            'previous_level' => max(0, $level - 1),
            'escalated_at' => $now,
            'escalated_by' => $request->user()->id,
            'escalated_by_name' => $request->user()->display_name ?: $request->user()->email,
            'reason' => $data['reason'],
            'notes' => $data['notes'] ?? null,
            'auto_escalated' => false,
        ]);

        return back()->with('toast', [
            'title' => 'Case escalated',
            'description' => ($complaint->case_number ?: 'Case').' is now level '.$level.'.',
        ]);
    }

    public function exportReports(): StreamedResponse
    {
        $this->adminOnly(request());
        $rows = Complaint::query()->where('is_deleted', false)->latest()->get();

        return $this->csv('speakup-reports.csv', ['Case', 'Title', 'Status', 'Complainant', 'Location', 'Filed'], $rows->map(fn (Complaint $row) => [
            $row->case_number,
            $row->title,
            $row->status,
            $row->is_anonymous ? 'Anonymous' : $row->complainant_name,
            $row->incident_location,
            optional($row->filed_at)->toDateTimeString(),
        ]));
    }

    public function exportCompliance(Request $request): StreamedResponse
    {
        $this->adminOnly($request);
        $rows = Complaint::query()->where('is_deleted', false)->get();

        return $this->csv('speakup-compliance.csv', ['Case', 'Type', 'Status', 'Location', 'Filed', 'Resolved'], $rows->map(fn (Complaint $row) => [
            $row->case_number,
            $row->type,
            $row->status,
            $row->incident_location,
            optional($row->filed_at)->toDateString(),
            optional($row->date_resolved ?: $row->closed_at)->toDateString(),
        ]));
    }

    private function saveVideo(Request $request, ?int $id)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'host' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'duration' => ['nullable', 'string', 'max:16'],
            'video_id' => ['required', 'string', 'max:32'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:64'],
        ]);
        $payload = [
            'title' => $data['title'],
            'host' => $data['host'] ?? null,
            'description' => $data['description'] ?? null,
            'duration' => $data['duration'] ?? null,
            'video_id' => $data['video_id'],
            'tags' => json_encode(array_values($data['tags'] ?? [])),
            'updated_at' => now(),
        ];
        if ($id) {
            DB::table('mental_health_videos')->where('id', $id)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('mental_health_videos')->insert($payload);
        }

        return back()->with('toast', ['title' => $id ? 'Video updated' : 'Video added', 'description' => $data['title'].' is in the learning hub.']);
    }

    private function csv(string $filename, array $header, $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function adminOnly(Request $request): void
    {
        $user = $request->user();
        $role = $user?->representative?->role;
        abort_unless($user && ($user->isPlatformAdmin() || $role === 'admin'), 403);
    }
}
