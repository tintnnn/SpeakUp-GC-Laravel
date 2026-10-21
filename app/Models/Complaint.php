<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sealed_identity' => 'array',
            'evidence_external_links' => 'array',
            'is_anonymous' => 'boolean',
            'is_archived' => 'boolean',
            'is_deleted' => 'boolean',
            'follow_up_requested' => 'boolean',
            'is_escalated' => 'boolean',
            'auto_escalated' => 'boolean',
            'sla_breached' => 'boolean',
            'incident_date' => 'date',
            'filed_at' => 'datetime',
            'response_due_at' => 'datetime',
            'assigned_at' => 'datetime',
            'accepted_at' => 'datetime',
            'closed_at' => 'datetime',
            'date_resolved' => 'datetime',
        ];
    }

    public function complainant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'complainant_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Representative::class, 'assigned_representative_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ComplaintStatusHistory::class);
    }
}
