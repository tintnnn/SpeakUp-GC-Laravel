<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Representative extends Model
{
    protected $fillable = [
        'firebase_id', 'user_id', 'email', 'display_name', 'role', 'department', 'position',
        'phone', 'photo_url', 'permissions', 'specializations', 'target_groups', 'working_hours',
        'active_cases', 'resolved_cases', 'total_cases_handled', 'average_response_time',
        'average_resolution_time', 'resolution_rate', 'is_active', 'online_status',
        'can_assign_cases', 'can_escalate_cases', 'can_resolve_cases', 'bio',
        'last_active', 'last_login_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'specializations' => 'array',
            'target_groups' => 'array',
            'working_hours' => 'array',
            'is_active' => 'boolean',
            'can_assign_cases' => 'boolean',
            'can_escalate_cases' => 'boolean',
            'can_resolve_cases' => 'boolean',
            'last_active' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
