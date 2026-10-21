<?php

use App\Http\Controllers\Admin\CaseQueueController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManageController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\ComplaintController;
use App\Models\Complaint;
use App\Models\Notification;
use App\Models\Representative;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.landing')->name('home');

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
Route::post('/auth/google/firebase', [GoogleAuthController::class, 'firebase'])->middleware('throttle:10,1')->name('google.firebase');
Route::redirect('/login', '/?auth=login')->name('login');

// These stay outside the guest middleware so a signed-in complainant can
// switch into the CODI or admin account. Guest middleware would send them
// straight back to the complainant dashboard and ignore the new credentials.
Route::post('/login', [SessionController::class, 'login'])->middleware('throttle:10,1');
Route::get('/admin/login', function () {
    if (auth()->user()?->isStaff()) {
        return redirect('/admin/dashboard');
    }

    return view('pages.auth.admin-login');
})->name('admin.login');
Route::post('/admin/login', [SessionController::class, 'adminLogin'])->middleware('throttle:10,1');

Route::middleware('guest')->group(function () {
    Route::post('/register', [SessionController::class, 'register'])->middleware('throttle:6,1');
    Route::post('/email/check-verification', [SessionController::class, 'checkVerification'])->middleware('throttle:10,1');
    Route::post('/email/resend', [SessionController::class, 'resendVerification'])->middleware('throttle:6,1');

    Route::view('/forgot-password', 'pages.auth.forgot-password')->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'send'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', fn (string $token) => view('pages.auth.reset-password', [
        'token' => $token,
        'email' => request('email'),
    ]))->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

});

Route::get('/email/verify/{id}/{hash}', [SessionController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::post('/logout', [SessionController::class, 'logout'])->middleware('auth')->name('logout');

Route::view('/terms', 'pages.info.terms')->name('terms');
Route::view('/privacy', 'pages.info.privacy')->name('privacy');
Route::view('/mission', 'pages.info.mission')->name('mission');
Route::view('/unauthorized', 'pages.unauthorized')->name('unauthorized');

Route::redirect('/admin', '/admin/dashboard');

Route::middleware(['auth', 'student'])->group(function () {
    Route::view('/dashboard', 'pages.complainant.dashboard')->name('dashboard');
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/new', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/case-tracking/{complaint}', [ComplaintController::class, 'tracking'])->name('cases.tracking');
    Route::get('/chat', function () {
        $rooms = DB::table('chat_rooms')
            ->where('complainant_id', request()->user()->id)
            ->where('is_deleted', false)
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($room) {
                $unread = DB::table('chat_room_participants')
                    ->where('room_id', $room->id)
                    ->where('user_id', request()->user()->id)
                    ->value('unread_count');
                $room->unread_count = (int) $unread;
                $room->other_name = $room->handler_name ?: 'CODI member';
                $room->other_role = 'handler';

                return $room;
            });

        return view('pages.complainant.messages', ['conversations' => $rooms]);
    })->name('chat');
    Route::get('/notifications', function () {
        $notifications = request()->user()->appNotifications()->latest('created_at')->get();

        return view('pages.complainant.notifications', ['notifications' => $notifications]);
    })->name('notifications');
    Route::view('/account', 'pages.complainant.account')->name('account');
    Route::post('/account', function (Request $request) {
        $data = $request->validate([
            'alias' => ['required', 'string', 'min:3', 'max:30'],
        ]);
        $request->user()->update(['alias' => trim($data['alias'])]);

        return back()->with('toast', [
            'title' => 'Alias saved',
            'description' => 'Your alias was updated.',
        ]);
    })->name('account.update');
    Route::post('/account/password-reset', [PasswordResetController::class, 'send'])->middleware('throttle:6,1');
    Route::view('/know-your-rights', 'pages.complainant.know-your-rights')->name('rights');
    Route::view('/learn', 'pages.complainant.learn-hub')->name('learn');
});

Route::middleware('auth')->group(function () {
    Route::get('/evidence/{attachment}', [ComplaintController::class, 'evidence'])->name('evidence.show');
    Route::get('/chat-files/{attachment}', [ChatController::class, 'file'])->name('chat.file');
    Route::get('/case-chat/{complaint}', [ChatController::class, 'show'])->name('case-chat');
    Route::post('/case-chat/{complaint}', [ChatController::class, 'store'])->middleware('throttle:30,1');

    Route::post('/notifications/read-all', function (Request $request) {
        $request->user()->appNotifications()->where('status', 'unread')->update([
            'status' => 'read',
            'read_at' => now(),
        ]);

        return back();
    });
    Route::post('/notifications/delete-read', function (Request $request) {
        $request->user()->appNotifications()->where('status', 'read')->delete();

        return back();
    });
    Route::post('/notifications/{notification}/read', function (Request $request, Notification $notification) {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->forceFill(['status' => 'read', 'read_at' => now()])->save();

        $next = $request->input('next');
        if (is_string($next) && str_starts_with($next, '/') && ! str_starts_with($next, '//')) {
            return redirect($next);
        }

        return back();
    });
    Route::delete('/notifications/{notification}', function (Request $request, Notification $notification) {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->delete();

        return back();
    });
});

Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('/admin/dashboard', DashboardController::class)->name('admin.dashboard');
    Route::post('/admin/notifications/read', function () {
        request()->user()->appNotifications()->where('status', 'unread')->update([
            'status' => 'read',
            'read_at' => now(),
        ]);

        return back();
    });

    Route::get('/admin/reports', function () {
        $viewer = request()->user();
        $complaints = Complaint::query()->where('is_deleted', false)->latest()->get();

        $openIds = $complaints
            ->filter(fn (Complaint $complaint) => in_array($complaint->status, ['pending', 'submitted', 'inProgress'], true))
            ->pluck('id');
        foreach ($openIds as $id) {
            DB::table('case_views')->updateOrInsert(
                ['user_id' => $viewer->id, 'complaint_id' => $id],
                ['viewed_at' => now()]
            );
        }
        if (request('report')) {
            $viewer->appNotifications()
                ->where('complaint_id', request('report'))
                ->where('status', 'unread')
                ->update(['status' => 'read', 'read_at' => now()]);
        }

        return view('pages.admin.reports', ['complaints' => $complaints, 'rows' => $complaints, 'title' => 'Reports']);
    })->name('admin.reports');

    Route::post('/admin/reports/{complaint}/notes', function (Request $request, Complaint $complaint) {
        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);
        $user = $request->user();
        $rep = $user->representative;
        $role = $user->isPlatformAdmin() ? 'admin' : ($rep?->role ?: 'admin');

        DB::table('case_notes')->insert([
            'complaint_id' => $complaint->id,
            'user_id' => $user->id,
            'user_name' => $user->display_name ?: $user->email,
            'user_role' => $role,
            'user_email' => $user->email,
            'message' => $data['message'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('case_activities')->insert([
            'complaint_id' => $complaint->id,
            'activity_type' => 'internal_note',
            'description' => 'Internal note added',
            'findings' => $data['message'],
            'performed_by_user_id' => $user->id,
            'performed_by_name' => $user->display_name ?: $user->email,
            'performed_by_role' => $role,
            'is_internal' => true,
            'is_deleted' => false,
            'created_at' => now(),
        ]);

        return redirect('/admin/reports?report='.$complaint->id.'&tab=notes')->with('toast', [
            'title' => 'Note Posted',
            'description' => 'Your internal note has been added to the case and Activity feed',
        ]);
    })->name('admin.reports.notes');

    Route::post('/admin/reports/{complaint}/take', [CaseQueueController::class, 'take'])
        ->middleware('throttle:20,1')
        ->name('admin.reports.take');

    Route::post('/admin/reports/{complaint}/status', [CaseQueueController::class, 'updateStatus'])
        ->middleware('throttle:20,1')
        ->name('admin.reports.status');

    Route::get('/admin/closed-cases', function () {
        $rows = Complaint::query()->where('is_deleted', false)->whereIn('status', ['resolved', 'dismissed', 'closed'])->latest()->get();

        return view('pages.admin.closed-cases', ['complaints' => $rows, 'rows' => $rows, 'title' => 'Closed Cases']);
    })->name('admin.closed-cases');

    Route::get('/admin/messages', function () {
        $user = request()->user();
        $rep = $user->representative;
        $isCodi = ! $user->isPlatformAdmin() && $rep && in_array($rep->role, ['handler', 'codi'], true);

        $rooms = DB::table('chat_rooms')
            ->where('is_deleted', false)
            ->when($isCodi, fn ($query) => $query->where('handler_id', $user->id))
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($room) use ($user) {
                $room->unread = (int) DB::table('chat_room_participants')
                    ->where('room_id', $room->id)
                    ->where('user_id', $user->id)
                    ->value('unread_count');
                $room->last_message = $room->last_message_content;

                return $room;
            });

        return view('pages.admin.messages', ['rows' => $rooms]);
    })->name('admin.messages');
    Route::get('/admin/analytics', function () {
        $user = request()->user();
        $rep = $user->representative;
        $isCodi = ! $user->isPlatformAdmin() && $rep && in_array($rep->role, ['handler', 'codi'], true);

        $complaints = Complaint::query()
            ->where('is_deleted', false)
            ->when($isCodi, fn ($query) => $query->where('assigned_representative_id', $rep->id))
            ->get();

        $categoryLabels = [
            'sexual_harassment' => 'Sexual Harassment',
            'gender_based_harassment' => 'Gender-Based Harassment',
            'discrimination' => 'Gender-Based Discrimination',
            'bullying' => 'Bullying/Harassment',
            'online_harassment' => 'Online Sexual Harassment',
            'other' => 'Other',
        ];
        $statusLabel = function (string $status): string {
            return match (strtolower($status)) {
                'pending', 'submitted' => 'Pending',
                'inprogress', 'investigating', 'awaiting_response', 'under_deliberation', 'validated' => 'Investigating',
                'resolved' => 'Decision Already Made',
                'dismissed', 'closed' => 'Closed',
                default => $status !== '' ? ucwords(str_replace('_', ' ', $status)) : 'Unknown',
            };
        };

        $reportsByCategory = $complaints
            ->groupBy(fn (Complaint $complaint) => $complaint->type ?: 'other')
            ->map(fn ($group, $category) => [
                'category' => $category,
                'label' => $categoryLabels[$category] ?? ucwords(str_replace('_', ' ', (string) $category)),
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();

        $reportsByStatus = $complaints
            ->groupBy(fn (Complaint $complaint) => $statusLabel((string) ($complaint->status ?: 'pending')))
            ->map(fn ($group, $label) => [
                'status' => $label,
                'label' => $label,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();

        $reportsOverTime = collect(range(6, 0))->map(function (int $ago) use ($complaints) {
            $day = now()->subDays($ago)->startOfDay();

            return [
                'date' => $day->format('m/d'),
                'count' => $complaints->filter(function (Complaint $complaint) use ($day) {
                    $filed = $complaint->filed_at ?: $complaint->created_at;

                    return $filed && $filed->isSameDay($day);
                })->count(),
            ];
        });

        $accounts = User::query()->where('is_deleted', false)->get(['id', 'created_at']);
        $userGrowth = collect(range(6, 0))->map(function (int $ago) use ($accounts) {
            $end = now()->subDays($ago)->endOfDay();

            return [
                'date' => $end->format('m/d'),
                'users' => $accounts->filter(fn (User $account) => $account->created_at && $account->created_at->lte($end))->count(),
            ];
        });

        $resolved = $complaints->filter(fn (Complaint $complaint) => in_array($complaint->status, ['resolved', 'closed'], true));
        $resolvedHours = $resolved->map(function (Complaint $complaint) {
            $filed = $complaint->filed_at ?: $complaint->created_at;
            $done = $complaint->date_resolved ?: $complaint->closed_at ?: $complaint->updated_at;
            if (! $filed || ! $done) {
                return null;
            }

            return max(0, $filed->diffInHours($done));
        })->filter(fn ($hours) => $hours !== null);

        $representatives = Representative::query()->get()->keyBy('id');
        $casesByRepresentative = $complaints
            ->filter(fn (Complaint $complaint) => filled($complaint->assigned_representative_id))
            ->groupBy('assigned_representative_id')
            ->map(fn ($group, $id) => [
                'name' => $representatives->get($id)?->display_name ?: 'CODI member',
                'cases' => $group->count(),
            ])
            ->sortByDesc('cases')
            ->values();

        return view('pages.admin.analytics', [
            'isCodi' => $isCodi,
            'totalReports' => $complaints->count(),
            'resolvedReports' => $resolved->count(),
            'dailyActiveUsers' => $complaints
                ->filter(function (Complaint $complaint) {
                    $filed = $complaint->filed_at ?: $complaint->created_at;

                    return $filed && $filed->isToday();
                })
                ->pluck('complainant_id')
                ->filter()
                ->unique()
                ->count(),
            'averageResponseTime' => $resolvedHours->isEmpty() ? 0 : (int) round($resolvedHours->avg()),
            'reportsOverTime' => $reportsOverTime,
            'userGrowth' => $isCodi ? collect() : $userGrowth,
            'reportsByCategory' => $reportsByCategory,
            'reportsByStatus' => $reportsByStatus,
            'casesByRepresentative' => $isCodi ? collect() : $casesByRepresentative,
        ]);
    })->name('admin.analytics');
    Route::view('/admin/compliance-reports', 'pages.admin.compliance-reports')->name('admin.compliance-reports');
    Route::get('/admin/compliance-reports/export', [ManageController::class, 'exportCompliance'])->name('admin.compliance.export');
    Route::get('/admin/reports/export', [ManageController::class, 'exportReports'])->name('admin.reports.export');
    Route::post('/admin/reports/{complaint}/escalate', [ManageController::class, 'escalate'])->name('admin.reports.escalate');

    Route::post('/admin/representatives', [ManageController::class, 'storeRepresentative'])->name('admin.representatives.store');
    Route::put('/admin/representatives/{representative}', [ManageController::class, 'updateRepresentative'])->name('admin.representatives.update');
    Route::post('/admin/representatives/{representative}/toggle', [ManageController::class, 'toggleRepresentative'])->name('admin.representatives.toggle');
    Route::delete('/admin/representatives/{representative}', [ManageController::class, 'destroyRepresentative'])->name('admin.representatives.destroy');

    Route::post('/admin/users/{user}/assign', [ManageController::class, 'assignUser'])->name('admin.users.assign');
    Route::delete('/admin/users/{user}', [ManageController::class, 'destroyUser'])->name('admin.users.destroy');

    Route::post('/admin/locations', [ManageController::class, 'storeLocation'])->name('admin.locations.store');
    Route::put('/admin/locations/{location}', [ManageController::class, 'updateLocation'])->name('admin.locations.update');
    Route::delete('/admin/locations/{location}', [ManageController::class, 'destroyLocation'])->name('admin.locations.destroy');

    Route::post('/admin/content-library', [ManageController::class, 'storeVideo'])->name('admin.content.store');
    Route::put('/admin/content-library/{video}', [ManageController::class, 'updateVideo'])->name('admin.content.update');
    Route::delete('/admin/content-library/{video}', [ManageController::class, 'destroyVideo'])->name('admin.content.destroy');

    Route::post('/admin/settings', [ManageController::class, 'saveSettings'])->name('admin.settings.save');

    Route::get('/admin/representatives', function () {
        $rows = Representative::query()->orderBy('display_name')->get();

        return view('pages.admin.representatives', ['representatives' => $rows, 'rows' => $rows, 'title' => 'Representatives']);
    })->name('admin.representatives');

    Route::get('/admin/users', function () {
        $rows = User::query()->withCount(['complaints as reports_count'])->orderBy('email')->get();

        return view('pages.admin.users', ['users' => $rows, 'rows' => $rows, 'title' => 'Users']);
    })->name('admin.users');

    Route::view('/admin/settings', 'pages.admin.settings')->name('admin.settings');
    Route::get('/admin/locations', function () {
        $rows = DB::table('locations')->orderBy('name')->get();

        return view('pages.admin.locations', ['rows' => $rows, 'title' => 'Locations']);
    })->name('admin.locations');
    Route::get('/admin/content-library', function () {
        $rows = DB::table('mental_health_videos')->orderBy('title')->get();

        return view('pages.admin.content-library', ['rows' => $rows, 'title' => 'Content Library']);
    })->name('admin.content-library');
});

Route::fallback(fn () => response()->view('errors.404', [], 404));
