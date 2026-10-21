<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Port of views/admin/AdminDashboardRedesign.tsx.
 * Counts come from the complaints table instead of a Firestore listener.
 */
class DashboardController extends Controller
{
    private const CATEGORY_COLORS = ['#0F6E56', '#1D9E75', '#4DB894', '#7DCEB3', '#9FE1CB', '#7A8F82'];

    public function __invoke(): View
    {
        $complaints = Complaint::query()->where('is_deleted', false)->get();
        $total = $complaints->count();
        $now = now();

        $pending = $complaints->filter(fn (Complaint $c) => in_array($c->status, ['pending', 'submitted'], true))->count();
        $investigating = $complaints->filter(fn (Complaint $c) => in_array($c->status, ['inProgress', 'investigating'], true))->count();
        $decided = $complaints->filter(fn (Complaint $c) => in_array($c->status, ['resolved', 'dismissed'], true))->count();

        $resolved = $complaints->filter(fn (Complaint $c) => in_array($c->status, ['resolved', 'dismissed'], true));
        $resolutionDays = $resolved->map(function (Complaint $c) {
            if (! $c->created_at || ! $c->updated_at) {
                return null;
            }

            return $c->created_at->diffInDays($c->updated_at);
        })->filter(fn ($days) => $days !== null);

        $overdue = $complaints->filter(function (Complaint $c) use ($now) {
            if (! in_array($c->status, ['pending', 'submitted', 'inProgress'], true)) {
                return false;
            }
            $due = $this->dueAt($c);

            return $due && $due->lt($now);
        })->count();

        $active = $complaints
            ->filter(fn (Complaint $c) => in_array($c->status, ['pending', 'inProgress'], true))
            ->sortByDesc('created_at')
            ->take(5)
            ->map(fn (Complaint $c) => [
                'id' => $c->id,
                'title' => $c->title ?: ($c->description ? mb_substr($c->description, 0, 40) : 'Untitled'),
                'type' => $c->type ?: 'General',
                'status' => $c->status,
                'timeAgo' => $c->created_at?->diffForHumans() ?: 'N/A',
            ])
            ->values();

        $threeDaysAgo = $now->copy()->subDays(3);
        $needsAttention = $complaints->filter(function (Complaint $c) use ($threeDaysAgo) {
            $last = $c->updated_at ?: $c->created_at;

            return in_array($c->status, ['pending', 'inProgress', 'submitted'], true)
                && $last
                && $last->lt($threeDaysAgo);
        })->count();

        $user = auth()->user();
        $representative = $user?->representative;
        $isCodi = $user && ! $user->isPlatformAdmin() && $representative && in_array($representative->role, ['handler', 'codi'], true);
        if ($isCodi) {
            $open = $complaints->filter(fn (Complaint $c) => in_array($c->status, ['pending', 'submitted', 'inProgress'], true));

            return view('pages.admin.codi-dashboard', [
                'rows' => $complaints->sortByDesc('updated_at')->take(8)->each(function (Complaint $complaint) {
                    $complaint->setAttribute('assigned_to', $complaint->assigned_representative_id);
                })->values(),
                'activeQueue' => $open->count(),
                'myAssigned' => $open->where('assigned_representative_id', $representative->id)->count(),
                'investigating' => $investigating,
                'unassigned' => $open->whereNull('assigned_representative_id')->count(),
                'needsAttention' => $needsAttention + $complaints->where('follow_up_requested', true)->count(),
            ]);
        }

        $deadlines = $complaints
            ->filter(fn (Complaint $c) => in_array($c->status, ['pending', 'submitted', 'inProgress'], true))
            ->map(function (Complaint $c) use ($now) {
                $due = $this->dueAt($c);
                if (! $due) {
                    return null;
                }
                $days = (int) $now->copy()->startOfDay()->diffInDays($due->copy()->startOfDay(), false);

                return [
                    'id' => $c->id,
                    'caseNumber' => $c->case_number ?: 'CASE-'.$c->id,
                    'title' => $c->title ?: ($c->description ? mb_substr($c->description, 0, 30) : 'Untitled'),
                    'dueLabel' => $due->format('M j, Y'),
                    'days' => $days,
                    'isOverdue' => $days < 0,
                    'isUrgent' => $days >= 0 && $days <= 2,
                ];
            })
            ->filter(fn ($row) => $row && $row['days'] <= 3)
            ->sortBy('days')
            ->take(5)
            ->values();

        $monthly = collect(range(0, 5))->map(function (int $i) use ($complaints) {
            $date = now()->startOfMonth()->subMonths(5 - $i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();
            $monthReports = $complaints->filter(fn (Complaint $c) => $c->created_at && $c->created_at->between($start, $end));
            $harassment = $monthReports->filter(fn (Complaint $c) => str_contains(strtolower((string) $c->type), 'harassment'))->count();
            $bullying = $monthReports->filter(fn (Complaint $c) => str_contains(strtolower((string) $c->type), 'bullying'))->count();
            $others = $monthReports->count() - $harassment - $bullying;

            return [
                'month' => $date->format('M'),
                'harassment' => $harassment,
                'bullying' => $bullying,
                'others' => max(0, $others),
                'total' => $monthReports->count(),
            ];
        });

        $points = $monthly->values()->map(function (array $row, int $i) use ($monthly) {
            $count = max(1, $monthly->count() - 1);

            return [
                'x' => ($i / $count) * 100,
                'y' => 240 - ($row['total'] * 24),
                'month' => $row['month'],
            ];
        });
        $polyline = $points->map(fn (array $p) => $p['x'].'%,'.$p['y'])->implode(' ');
        $firstY = $points->first()['y'] ?? 240;
        $area = 'M 0,'.$firstY.' '.$points->map(fn (array $p) => 'L '.$p['x'].'%,'.$p['y'])->implode(' ').' L 100%,240 L 0,240 Z';

        $categories = $complaints
            ->groupBy(fn (Complaint $c) => $c->type ?: 'Others')
            ->map(fn ($group, $name) => [
                'name' => $name,
                'percentage' => $total > 0 ? (int) round(($group->count() / $total) * 100) : 0,
            ])
            ->sortByDesc('percentage')
            ->values()
            ->map(function (array $row, int $index) {
                $row['color'] = self::CATEGORY_COLORS[$index % count(self::CATEGORY_COLORS)];

                return $row;
            });

        $statuses = $complaints
            ->groupBy(fn (Complaint $c) => $c->status ?: 'Unknown')
            ->map(fn ($group, $name) => [
                'name' => $name,
                'percentage' => $total > 0 ? (int) round(($group->count() / $total) * 100) : 0,
                'color' => $this->statusColor((string) $name),
            ])
            ->sortByDesc('percentage')
            ->values();

        return view('pages.admin.dashboard', [
            'metrics' => [
                ['title' => 'Total Reports', 'value' => $total, 'icon' => 'file-text', 'trend' => '+100%', 'href' => '/admin/reports'],
                ['title' => 'Pending Cases', 'value' => $pending, 'icon' => 'clock', 'trend' => '0%', 'href' => '/admin/reports?status=pending'],
                ['title' => 'Under Investigation', 'value' => $investigating, 'icon' => 'shield-alert', 'trend' => '0%', 'href' => '/admin/reports?status=inProgress'],
                ['title' => 'Decision Made', 'value' => $decided, 'icon' => 'circle-check', 'trend' => '0%', 'href' => '/admin/closed-cases'],
            ],
            'resolutionRate' => $total > 0 ? (int) round(($resolved->count() / $total) * 100) : 0,
            'avgResolutionTime' => $resolutionDays->isEmpty() ? 0 : round($resolutionDays->avg(), 1),
            'overdueCases' => $overdue,
            'followUpRequests' => $complaints->where('follow_up_requested', true)->count(),
            'needsAttention' => $needsAttention,
            'activeCases' => $active,
            'deadlines' => $deadlines,
            'monthly' => $monthly,
            'chartPoints' => $points,
            'polyline' => $polyline,
            'area' => $area,
            'peak' => (int) $monthly->max('total'),
            'categories' => $categories,
            'statuses' => $statuses,
        ]);
    }

    private function dueAt(Complaint $complaint): ?Carbon
    {
        if ($complaint->response_due_at) {
            return $complaint->response_due_at;
        }

        $filed = $complaint->filed_at ?: $complaint->created_at;

        return $filed ? $filed->copy()->addDays(7) : null;
    }

    private function statusColor(string $name): string
    {
        $key = strtolower(preg_replace('/[\s-]+/', '', $name) ?? '');
        if (str_contains($key, 'pending') || str_contains($key, 'submitted')) {
            return '#F59E0B';
        }
        if (str_contains($key, 'progress') || str_contains($key, 'investigat')) {
            return '#3B82F6';
        }
        if (str_contains($key, 'resolved') || str_contains($key, 'closed')) {
            return '#1D9E75';
        }
        if (str_contains($key, 'dismiss')) {
            return '#DC6B6B';
        }

        return '#7A8F82';
    }
}
