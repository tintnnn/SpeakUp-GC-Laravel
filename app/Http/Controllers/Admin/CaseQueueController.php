<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Support\SpeakUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaseQueueController extends Controller
{
    /** CODI self-assign. Status stays pending until the first case update. */
    public function take(Request $request, Complaint $complaint)
    {
        $user = $request->user();
        $rep = $user->representative;

        if (! $rep || ! $rep->is_active || ! in_array($rep->role, ['handler', 'codi'], true)) {
            abort(403);
        }

        if ($complaint->is_deleted || in_array($complaint->status, ['resolved', 'dismissed', 'closed'], true)) {
            return back()->with('toast', [
                'title' => 'Cannot take case',
                'description' => 'This case is already closed.',
                'variant' => 'destructive',
            ]);
        }

        $name = $rep->display_name ?: ($user->display_name ?: $user->email);
        $now = now();
        $sensitive = SpeakUp::isSensitiveCaseType($complaint->type);
        $title = $complaint->title ?: 'your complaint';
        $caseNumber = $complaint->case_number ?: (string) $complaint->id;

        $assigned = DB::transaction(function () use ($complaint, $user, $rep, $name, $now, $sensitive, $title) {
            $updated = Complaint::query()
                ->whereKey($complaint->id)
                ->whereNull('assigned_representative_id')
                ->update([
                    'assigned_representative_id' => $rep->id,
                    'assigned_user_id' => $user->id,
                    'assigned_to_name' => $name,
                    'assigned_to_role' => $rep->role,
                    'assigned_by' => $user->id,
                    'assigned_by_name' => $name,
                    'assigned_at' => $now,
                    'accepted_at' => $now,
                    'accepted_by' => $user->id,
                    'accepted_by_name' => $name,
                    'last_activity_at' => $now,
                    'time_to_assignment_hours' => $complaint->filed_at
                        ? (int) $complaint->filed_at->diffInHours($now)
                        : null,
                    'updated_at' => $now,
                ]);

            if ($updated === 0) {
                return false;
            }

            DB::table('complaint_handler_history')->insert([
                'complaint_id' => $complaint->id,
                'handler_user_id' => $user->id,
                'handler_representative_id' => $rep->id,
                'handler_name' => $name,
                'handler_role' => $rep->role,
                'assigned_at' => $now,
                'assigned_by' => $user->id,
                'assigned_by_name' => $name,
            ]);

            DB::table('case_activities')->insert([
                'complaint_id' => $complaint->id,
                'activity_type' => 'assignment',
                'description' => $sensitive ? 'Case taken by a CODI member' : 'Case taken by '.$name,
                'findings' => $sensitive
                    ? 'A CODI member has taken your case. You will be contacted when the investigation begins.'
                    : $name.' has taken this case. The investigation will begin once the CODI member reviews the complaint.',
                'performed_by_user_id' => $user->id,
                'performed_by_name' => $sensitive ? 'CODI member' : $name,
                'performed_by_role' => $rep->role,
                'is_internal' => false,
                'metadata' => json_encode([
                    'assignedHandler' => $sensitive ? null : $name,
                    'maskHandlerIdentity' => $sensitive,
                ]),
                'is_deleted' => false,
                'created_at' => $now,
            ]);

            $rep->increment('active_cases');
            $rep->increment('total_cases_handled');

            $this->openChatRoom($complaint, $user, $name, $sensitive, $now);

            if ($complaint->complainant_id) {
                User::query()->find($complaint->complainant_id)?->appNotifications()->create([
                    'type' => 'handler_assigned',
                    'priority' => 'high',
                    'status' => 'unread',
                    'title' => 'Complaint Accepted',
                    'message' => $sensitive
                        ? 'Your complaint has been accepted. A CODI member has taken your case. You will be contacted for updates regarding "'.$title.'".'
                        : 'Your complaint has been accepted. A CODI member has taken "'.$title.'" and will start moving it forward.',
                    'complaint_id' => $complaint->id,
                    'action_url' => '/case-tracking/'.$complaint->id,
                    'action_label' => 'View case',
                    'created_at' => $now,
                ]);
            }

            return true;
        });

        if (! $assigned) {
            return back()->with('toast', [
                'title' => 'Already assigned',
                'description' => 'Another CODI member already took this case.',
                'variant' => 'destructive',
            ]);
        }

        return redirect('/admin/reports')->with('toast', [
            'title' => 'Case Taken',
            'description' => 'You are now assigned to '.$caseNumber.'. Status remains Pending until you post your first update.',
        ]);
    }

    /** Next CODI step: investigate, record a decision, or close. Matches the original status ladder. */
    public function updateStatus(Request $request, Complaint $complaint)
    {
        $user = $request->user();
        $rep = $user->representative;

        if (! $rep || ! $rep->is_active || ! in_array($rep->role, ['handler', 'codi'], true)) {
            abort(403);
        }

        if ((int) $complaint->assigned_representative_id !== (int) $rep->id) {
            return back()->with('toast', [
                'title' => 'Not your case',
                'description' => 'Only the assigned CODI member can update this case.',
                'variant' => 'destructive',
            ]);
        }

        $current = (string) $complaint->status;
        $allowed = [
            'pending' => ['inProgress'],
            'submitted' => ['inProgress'],
            'inProgress' => ['resolved', 'dismissed'],
            'resolved' => ['closed'],
            'dismissed' => ['closed'],
        ];

        $data = $request->validate([
            'status' => ['required', 'in:inProgress,resolved,dismissed,closed'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'decision_summary' => ['nullable', 'string', 'max:5000'],
            'action_taken' => ['nullable', 'string', 'max:5000'],
            'final_notes' => ['nullable', 'string', 'max:5000'],
            'closure_document' => ['nullable', 'file', 'max:10240', 'extensions:jpg,jpeg,png,gif,webp,pdf,doc,docx'],
        ]);

        $next = $data['status'];
        if (! in_array($next, $allowed[$current] ?? [], true)) {
            return back()->with('toast', [
                'title' => 'Invalid status change',
                'description' => 'That step is not available from the current status.',
                'variant' => 'destructive',
            ]);
        }

        $notes = trim((string) ($data['notes'] ?? ''));
        if (in_array($next, ['inProgress', 'resolved'], true) && strlen($notes) < 10) {
            return back()->with('toast', [
                'title' => 'Description required',
                'description' => 'Write at least 10 characters about this investigation update.',
                'variant' => 'destructive',
            ]);
        }
        if ($notes !== '' && strlen($notes) < 10) {
            return back()->with('toast', [
                'title' => 'Description too short',
                'description' => 'Notes need at least 10 characters.',
                'variant' => 'destructive',
            ]);
        }

        $decision = trim((string) ($data['decision_summary'] ?? ''));
        $actionTaken = trim((string) ($data['action_taken'] ?? ''));
        $finalNotes = trim((string) ($data['final_notes'] ?? ''));
        if ($next === 'closed' && ($decision === '' || $actionTaken === '')) {
            return back()->with('toast', [
                'title' => 'Closure details required',
                'description' => 'Add a decision summary and the action taken before closing the case.',
                'variant' => 'destructive',
            ]);
        }

        $name = $rep->display_name ?: ($user->display_name ?: $user->email);
        $now = now();
        $sensitive = SpeakUp::isSensitiveCaseType($complaint->type);
        $title = $complaint->title ?: 'your complaint';
        $caseNumber = $complaint->case_number ?: (string) $complaint->id;
        $labels = [
            'inProgress' => 'Ongoing Investigation',
            'resolved' => 'Decision Already Made',
            'dismissed' => 'Dismissed',
            'closed' => 'Closed',
        ];
        $stages = [
            'inProgress' => 'investigating',
            'resolved' => 'decision',
            'dismissed' => 'decision',
            'closed' => 'closed',
        ];

        DB::transaction(function () use ($request, $complaint, $user, $rep, $current, $next, $notes, $decision, $actionTaken, $finalNotes, $name, $now, $sensitive, $title, $stages) {
            $update = [
                'status' => $next,
                'stage' => $stages[$next],
                'last_activity_at' => $now,
                'last_updated_by' => $user->id,
                'last_updated_by_name' => $sensitive ? 'CODI member' : $name,
                'updated_at' => $now,
            ];
            if ($next === 'inProgress' && ! $complaint->processing_started_at) {
                $update['processing_started_at'] = $now;
            }
            if (in_array($next, ['resolved', 'dismissed', 'closed'], true)) {
                $update['date_resolved'] = $complaint->date_resolved ?: $now;
            }
            if ($next === 'closed') {
                $update['closed_at'] = $now;
                $update['closed_by'] = $user->id;
                $update['closed_by_name'] = $sensitive ? 'CODI member' : $name;
                $update['decision_summary'] = $decision;
                $update['action_taken'] = $actionTaken;
                $update['final_notes'] = $finalNotes !== '' ? $finalNotes : null;
                if ($file = $request->file('closure_document')) {
                    $update['closure_document_url'] = $file->store('closures/'.$complaint->id, 'local');
                    $update['closure_document_name'] = $file->getClientOriginalName();
                }
            }

            Complaint::query()->whereKey($complaint->id)->where('status', $current)->update($update);

            DB::table('complaint_status_history')->insert([
                'complaint_id' => $complaint->id,
                'status' => $next,
                'previous_status' => $current,
                'stage' => $stages[$next],
                'updated_by' => $user->id,
                'updated_by_name' => $sensitive ? 'CODI member' : $name,
                'notes' => $next === 'closed'
                    ? trim("Decision: {$decision}\n\nActions: {$actionTaken}".($finalNotes !== '' ? "\n\nNotes: {$finalNotes}" : ''))
                    : ($notes !== '' ? $notes : null),
                'created_at' => $now,
            ]);

            DB::table('case_activities')->insert([
                'complaint_id' => $complaint->id,
                'activity_type' => 'status_change',
                'description' => 'Status changed from '.$current.' to '.$next,
                'findings' => $next === 'closed' ? $decision : ($notes !== '' ? $notes : null),
                'performed_by_user_id' => $user->id,
                'performed_by_name' => $sensitive ? 'CODI member' : $name,
                'performed_by_role' => $rep->role,
                'is_internal' => false,
                'is_deleted' => false,
                'created_at' => $now,
            ]);

            if (in_array($current, ['pending', 'submitted', 'inProgress'], true) && in_array($next, ['resolved', 'dismissed', 'closed'], true) && $rep->active_cases > 0) {
                $rep->decrement('active_cases');
            }

            if ($next === 'closed') {
                DB::table('chat_rooms')->where('complaint_id', $complaint->id)->update([
                    'status' => 'closed',
                    'is_active' => false,
                    'closed_at' => $now,
                    'closed_by' => $user->id,
                    'updated_at' => $now,
                ]);
            }

            $complainantCopy = match ($next) {
                'inProgress' => 'Your complaint is now being reviewed and processed by our team.',
                'resolved' => 'Your complaint has been resolved. Please check the resolution details.',
                'dismissed' => 'Your complaint has been reviewed and dismissed. Please check the details for more information.',
                'closed' => 'Your case "'.$title.'" has been officially closed and archived. Final decision: '.mb_substr($decision, 0, 100),
                default => 'Your case status was updated.',
            };
            $complainantTitle = match ($next) {
                'inProgress' => 'Investigation started',
                'resolved' => 'Decision recorded',
                'dismissed' => 'Complaint dismissed',
                'closed' => 'Case officially closed',
                default => 'Case update',
            };

            if ($complaint->complainant_id) {
                User::query()->find($complaint->complainant_id)?->appNotifications()->create([
                    'type' => $next === 'closed' ? 'case_closed' : 'status_update',
                    'priority' => in_array($next, ['resolved', 'dismissed', 'closed'], true) ? 'high' : 'normal',
                    'status' => 'unread',
                    'title' => $complainantTitle,
                    'message' => $complainantCopy,
                    'complaint_id' => $complaint->id,
                    'action_url' => '/case-tracking/'.$complaint->id,
                    'action_label' => 'View case',
                    'created_at' => $now,
                ]);
            }
        });

        return redirect('/admin/reports?report='.$complaint->id)->with('toast', [
            'title' => $next === 'closed' ? 'Case Closed Successfully' : 'Status Updated',
            'description' => $next === 'closed'
                ? $caseNumber.' is closed and archived. The complainant has been notified.'
                : $caseNumber.' is now '.$labels[$next].'.',
        ]);
    }

    private function openChatRoom(Complaint $complaint, User $handler, string $handlerName, bool $sensitive, $now): void
    {
        $anonymous = (bool) $complaint->is_anonymous;
        $complainantName = $anonymous ? 'Anonymous' : ($complaint->complainant_name ?: 'Complainant');
        $room = DB::table('chat_rooms')->where('complaint_id', $complaint->id)->first();

        if ($room) {
            DB::table('chat_rooms')->where('id', $room->id)->update([
                'handler_id' => $handler->id,
                'handler_name' => $sensitive ? 'CODI member' : $handlerName,
                'complainant_name' => $complainantName,
                'is_active' => true,
                'updated_at' => $now,
            ]);
            $roomId = $room->id;
        } else {
            $roomId = DB::table('chat_rooms')->insertGetId([
                'complaint_id' => $complaint->id,
                'complaint_title' => $complaint->title,
                'complainant_id' => $complaint->complainant_id,
                'complainant_name' => $complainantName,
                'handler_id' => $handler->id,
                'handler_name' => $sensitive ? 'CODI member' : $handlerName,
                'status' => 'active',
                'is_active' => true,
                'is_deleted' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($complaint->complainant_id) {
            DB::table('chat_room_participants')->updateOrInsert(
                ['room_id' => $roomId, 'user_id' => $complaint->complainant_id],
                ['name' => $complainantName, 'role' => 'complainant', 'joined_at' => $now, 'unread_count' => 0]
            );
        }

        DB::table('chat_room_participants')->updateOrInsert(
            ['room_id' => $roomId, 'user_id' => $handler->id],
            ['name' => $handlerName, 'role' => 'handler', 'joined_at' => $now, 'unread_count' => 0]
        );
    }
}
