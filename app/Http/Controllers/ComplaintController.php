<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Support\SpeakUp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplaintController extends Controller
{
    public function index(): View
    {
        $complaints = Complaint::query()
            ->where('complainant_id', auth()->id())
            ->where('is_deleted', false)
            ->latest()
            ->get();

        return view('pages.complainant.my-cases', [
            'complaints' => $complaints,
            'rows' => $complaints,
        ]);
    }

    public function create(): View
    {
        $locationGroups = DB::table('locations')
            ->orderBy('id')
            ->get()
            ->groupBy('category');

        return view('pages.complainant.file-complaint', [
            'locationGroups' => $locationGroups,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        foreach (['incident_location', 'complainant_name', 'respondent_name', 'respondent_department'] as $field) {
            $value = $request->input($field);
            if (is_array($value)) {
                $picked = collect($value)->first(fn ($item) => is_string($item) && trim($item) !== '');
                $request->merge([$field => $picked]);
            }
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if (mb_strlen(trim((string) $value)) < 20) {
                    $fail('Description must be at least 20 characters.');
                }
            }],
            'statement_of_facts' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:48'],
            'other_type_detail' => ['nullable', 'string', 'max:255'],
            'harassment_degree' => ['nullable', 'string', 'max:16'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'incident_time' => ['nullable', 'string', 'max:8'],
            'incident_location' => ['required', 'string', 'max:255'],
            'location_vicinity' => ['nullable', 'string', 'max:16'],
            'map_address' => ['nullable', 'string', 'max:512'],
            'respondent_name' => ['required', 'string', 'max:255'],
            'respondent_department' => ['nullable', 'string', 'max:255'],
            'respondent_type' => ['nullable', 'string', 'max:20'],
            'respondent_address' => ['nullable', 'string'],
            'witnesses' => ['nullable', 'string'],
            'evidence_description' => ['nullable', 'string'],
            'complainant_name' => ['nullable', 'string', 'max:255'],
            'complainant_address' => ['nullable', 'string', 'max:255'],
            'complainant_contact' => ['nullable', 'string', 'max:32'],
            'complainant_type' => ['nullable', 'string', 'max:20'],
            'sealed_name' => ['nullable', 'string', 'max:255'],
            'sealed_type' => ['nullable', 'string', 'max:32'],
            'sealed_contact' => ['nullable', 'string', 'max:32'],
            'evidence_links' => ['nullable', 'array'],
            'evidence_links.*' => ['nullable', 'string', 'max:512'],
            'evidence' => ['nullable', 'array'],
            'evidence.*' => ['file', 'max:51200'],
        ], [
            'evidence.*.max' => 'Each file must be 50 MB or smaller. Use an external link for a larger file.',
            'evidence.*.uploaded' => 'A file did not upload. Keep each file at 50 MB or smaller.',
        ]);

        $problems = [];
        if (($data['type'] ?? '') === 'other' && blank($data['other_type_detail'] ?? null)) {
            $problems['other_type_detail'] = 'Specify the complaint type.';
        }
        $respondentName = trim((string) ($data['respondent_name'] ?? ''));
        if (str_starts_with($respondentName, 'Unknown') && mb_strlen(trim((string) ($data['respondent_address'] ?? ''))) < 20) {
            $problems['respondent_address'] = 'Describe the person in at least 20 characters.';
        }
        if (! $request->boolean('is_anonymous')) {
            $name = trim((string) ($data['complainant_name'] ?? ''));
            $words = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            if (count($words) < 2) {
                $problems['complainant_name'] = 'Enter your first and last name.';
            }
            if (mb_strlen(trim((string) ($data['complainant_address'] ?? ''))) < 10) {
                $problems['complainant_address'] = 'Address must be at least 10 characters.';
            }
            if (! preg_match('/^09\d{9}$/', (string) ($data['complainant_contact'] ?? ''))) {
                $problems['complainant_contact'] = 'Contact number must be 11 digits and start with 09.';
            }
            if (blank($data['complainant_type'] ?? null)) {
                $problems['complainant_type'] = 'Select your role.';
            }
        }
        if ($problems !== []) {
            return back()->withErrors($problems)->withInput();
        }

        $user = $request->user();
        $type = $data['type'] ?: 'other';
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            $title = $type === 'other' && filled($data['other_type_detail'] ?? null)
                ? $data['other_type_detail']
                : str_replace('_', ' ', $type);
        }

        $links = array_values(array_filter($data['evidence_links'] ?? []));
        $sealed = array_filter([
            'name' => $data['sealed_name'] ?? null,
            'type' => $data['sealed_type'] ?? null,
            'contact' => $data['sealed_contact'] ?? null,
        ]);

        $complaint = Complaint::query()->create([
            'title' => $title,
            'description' => $data['description'] ?? null,
            'statement_of_facts' => $data['statement_of_facts'] ?? null,
            'type' => $type,
            'harassment_degree' => $data['harassment_degree'] ?? null,
            'incident_date' => $data['incident_date'] ?? null,
            'incident_time' => $data['incident_time'] ?? null,
            'incident_location' => $data['incident_location'] ?? null,
            'location_vicinity' => $data['location_vicinity'] ?? null,
            'map_address' => $data['map_address'] ?? null,
            'respondent_name' => $data['respondent_name'] ?? null,
            'respondent_department' => $data['respondent_department'] ?? null,
            'respondent_type' => $data['respondent_type'] ?? null,
            'respondent_address' => $data['respondent_address'] ?? null,
            'witnesses' => $data['witnesses'] ?? null,
            'additional_info' => $data['evidence_description'] ?? ($data['other_type_detail'] ?? null),
            'is_anonymous' => $request->boolean('is_anonymous'),
            'complainant_id' => $user->id,
            'complainant_email' => $user->email,
            'complainant_name' => $data['complainant_name'] ?: $user->display_name,
            'complainant_address' => $data['complainant_address'] ?? null,
            'complainant_contact' => $data['complainant_contact'] ?? null,
            'complainant_type' => $data['complainant_type'] ?? null,
            'sealed_identity' => $sealed === [] ? null : $sealed,
            'evidence_external_links' => $links === [] ? null : $links,
            'evidence_count' => count($links) + count($request->file('evidence', [])),
            'status' => 'pending',
            'stage' => 'filing',
            'filed_at' => now(),
            'response_due_at' => now()->addDays(7),
            'case_number' => 'PENDING',
        ]);

        $caseNumber = 'CASE-'.now()->format('Y').'-'.str_pad((string) $complaint->id, 4, '0', STR_PAD_LEFT);
        $complaint->update(['case_number' => $caseNumber]);

        foreach ($request->file('evidence', []) as $file) {
            if (! $file) {
                continue;
            }
            $path = $file->store('evidence/'.$complaint->id, 'local');
            DB::table('attachments')->insert([
                'attachable_type' => Complaint::class,
                'attachable_id' => $complaint->id,
                'url' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
            ]);
        }

        $user->appNotifications()->create([
            'type' => 'status_update',
            'priority' => 'normal',
            'status' => 'unread',
            'title' => 'Complaint filed',
            'message' => 'Your case '.$complaint->case_number.' was submitted and is waiting for a CODI member.',
            'complaint_id' => $complaint->id,
            'action_url' => '/case-tracking/'.$complaint->id,
            'action_label' => 'Track case',
        ]);

        SpeakUp::notifyStaffOfComplaint($complaint);

        return redirect('/case-tracking/'.$complaint->id)->with('filed_case', [
            'id' => $complaint->id,
            'case_number' => $complaint->case_number,
        ]);
    }

    public function tracking(Complaint $complaint): View
    {
        abort_unless($complaint->complainant_id === auth()->id() || auth()->user()->isStaff(), 403);

        return view('pages.complainant.case-tracking', ['complaint' => $complaint]);
    }

    /** Evidence stays off the public /storage path. Only the complainant or staff can open it. */
    public function evidence(Request $request, int $attachment): StreamedResponse
    {
        $file = DB::table('attachments')->where('id', $attachment)->first();
        abort_unless($file && $file->attachable_type === Complaint::class, 404);

        $complaint = Complaint::query()->find($file->attachable_id);
        $user = $request->user();
        abort_unless(
            $complaint && ($complaint->complainant_id === $user->id || $user->isStaff()),
            403
        );

        $stored = (string) $file->url;
        $disk = 'local';
        $path = $stored;
        if (str_starts_with($stored, '/storage/')) {
            $disk = 'public';
            $path = ltrim(substr($stored, strlen('/storage/')), '/');
        }

        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path, $file->file_name ?: 'evidence', [
            'Content-Type' => $file->mime ?: 'application/octet-stream',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
