<?php

namespace App\Models;

use App\Support\SpeakUp;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'firebase_uid',
        'email',
        'display_name',
        'alias',
        'photo_url',
        'password',
        'provider',
        'is_admin',
        'is_active',
        'is_deleted',
        'is_suspended',
        'is_registered',
        'email_verified_at',
        'role',
        'notification_preference',
        'terms_accepted',
        'privacy_accepted',
        'profile_setup_complete',
        'profile_setup_completed_at',
        'registered_at',
        'extra',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'profile_setup_completed_at' => 'datetime',
            'registered_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'is_deleted' => 'boolean',
            'is_suspended' => 'boolean',
            'is_registered' => 'boolean',
            'terms_accepted' => 'boolean',
            'privacy_accepted' => 'boolean',
            'profile_setup_complete' => 'boolean',
            'extra' => 'array',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->display_name ?: ($this->email ?? '');
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_admin || SpeakUp::isAdminEmail($this->email);
    }

    public function representative(): HasOne
    {
        return $this->hasOne(Representative::class);
    }

    public function isStaff(): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        $role = $this->representative?->role;

        return in_array($role, ['admin', 'handler', 'codi'], true) && (bool) $this->representative?->is_active;
    }

    /** Where a successful sign-in lands. Admins and CODI go to the admin shell. */
    public function homePath(): string
    {
        return $this->isStaff() ? '/admin/dashboard' : '/dashboard';
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'complainant_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
