<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\User;
use App\Support\SpeakUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function show(Request $request, Complaint $complaint)
    {
        $this->authorizeRoom($request, $complaint);

        $room = DB::table('chat_rooms')->where('complaint_id', $complaint->id)->where('is_deleted', false)->first();
        if ($room) {
            DB::table('chat_room_participants')
                ->where('room_id', $room->id)
                ->where('user_id', $request->user()->id)
                ->update(['unread_count' => 0, 'last_seen_at' => now()]);
        }

        return view('pages.chat.case-chat', ['complaint' => $complaint]);
    }

    public function store(Request $request, Complaint $complaint)
    {
        $this->authorizeRoom($request, $complaint);

        $validator = Validator::make($request->all(), [
            'content' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'extensions:jpg,jpeg,png,gif,webp,heic,heif,pdf,doc,docx,txt'],
        ]);
        if ($validator->fails()) {
            return back()->with('toast', [
                'title' => 'Cannot send file',
                'description' => $validator->errors()->first(),
                'variant' => 'destructive',
            ])->withInput();
        }

        $data = $validator->validated();
        $content = trim((string) ($data['content'] ?? ''));
        $upload = $request->file('attachment');
        if ($content === '' && ! $upload) {
            return back()->with('toast', [
                'title' => 'Nothing to send',
                'description' => 'Type a message or choose a file.',
                'variant' => 'destructive',
            ]);
        }

        $user = $request->user();
        $room = DB::table('chat_rooms')->where('complaint_id', $complaint->id)->where('is_deleted', false)->first();
        if (! $room || ! $room->handler_id) {
            return back()->with('toast', [
                'title' => 'No CODI member yet',
                'description' => 'This conversation opens once a CODI member takes the case.',
                'variant' => 'destructive',
            ])->withInput();
        }

        $isHandler = (int) $user->id === (int) $room->handler_id || $user->isStaff();
        $sensitive = SpeakUp::isSensitiveCaseType($complaint->type);
        $senderName = $isHandler
            ? ($sensitive ? 'CODI member' : ($user->display_name ?: 'CODI member'))
            : ((bool) $complaint->is_anonymous ? 'Anonymous' : ($user->display_name ?: 'Complainant'));
        $now = now();
        $fileOnly = $content === '' && $upload;
        $storedContent = $fileOnly ? $upload->getClientOriginalName() : $content;
        $preview = $fileOnly
            ? 'Sent a file: '.$upload->getClientOriginalName()
            : ($upload ? $content.' · '.$upload->getClientOriginalName() : $content);

        DB::transaction(function () use ($room, $complaint, $user, $storedContent, $preview, $senderName, $isHandler, $sensitive, $now, $upload) {
            $messageId = DB::table('messages')->insertGetId([
                'chat_room_id' => $room->id,
                'complaint_id' => $complaint->id,
                'sender_id' => (string) $user->id,
                'sender_user_id' => $user->id,
                'sender_name' => $senderName,
                'sender_role' => $isHandler ? 'handler' : 'complainant',
                'type' => $upload ? (str_starts_with((string) $upload->getMimeType(), 'image/') ? 'image' : 'file') : 'text',
                'content' => $storedContent,
                'status' => 'sent',
                'is_deleted' => false,
                'created_at' => $now,
            ]);

            if ($upload) {
                $path = $upload->store('chat/'.$complaint->id, 'local');
                DB::table('attachments')->insert([
                    'attachable_type' => 'message',
                    'attachable_id' => $messageId,
                    'url' => $path,
                    'file_name' => $upload->getClientOriginalName(),
                    'mime' => $upload->getMimeType(),
                    'size' => $upload->getSize(),
                    'uploaded_by' => $user->id,
                    'uploaded_at' => $now,
                ]);
            }

            DB::table('chat_rooms')->where('id', $room->id)->update([
                'last_message_content' => mb_substr($preview, 0, 240),
                'last_message_sender_id' => $user->id,
                'last_message_sender_name' => $senderName,
                'last_message_at' => $now,
                'updated_at' => $now,
            ]);

            $recipientId = $isHandler ? $complaint->complainant_id : $room->handler_id;
            if ($recipientId && (int) $recipientId !== (int) $user->id) {
                DB::table('chat_room_participants')
                    ->where('room_id', $room->id)
                    ->where('user_id', $recipientId)
                    ->increment('unread_count');

                $caseNumber = $complaint->case_number ?: (string) $complaint->id;
                User::query()->find($recipientId)?->appNotifications()->create([
                    'type' => 'new_message',
                    'priority' => 'normal',
                    'status' => 'unread',
                    'title' => 'New Message',
                    'message' => $isHandler && $sensitive
                        ? 'You have a new message about '.$caseNumber.'.'
                        : $senderName.' sent a message about '.$caseNumber.'.',
                    'complaint_id' => $complaint->id,
                    'action_url' => '/case-chat/'.$complaint->id,
                    'action_label' => 'Open chat',
                    'created_at' => $now,
                ]);
            }
        });

        return back();
    }

    private function authorizeRoom(Request $request, Complaint $complaint): void
    {
        $user = $request->user();
        $repId = $user->representative?->id;
        $allowed = (int) $user->id === (int) $complaint->complainant_id
            || $user->isPlatformAdmin()
            || ($repId && (int) $complaint->assigned_representative_id === (int) $repId)
            || ((int) $complaint->assigned_user_id === (int) $user->id);

        abort_unless($allowed, 403);
    }

    /** Chat files stay private. Only people already allowed in the case conversation can open them. */
    public function file(Request $request, int $attachment): StreamedResponse
    {
        $file = DB::table('attachments')->where('id', $attachment)->where('attachable_type', 'message')->first();
        abort_unless($file, 404);

        $message = DB::table('messages')->where('id', $file->attachable_id)->where('is_deleted', false)->first();
        abort_unless($message && $message->complaint_id, 404);

        $complaint = Complaint::query()->find($message->complaint_id);
        abort_unless($complaint, 404);
        $this->authorizeRoom($request, $complaint);

        abort_unless(Storage::disk('local')->exists($file->url), 404);

        return Storage::disk('local')->response($file->url, $file->file_name ?: 'attachment', [
            'Content-Type' => $file->mime ?: 'application/octet-stream',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
