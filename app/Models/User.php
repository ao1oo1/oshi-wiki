<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'must_change_password',
        'contributor_application_id',
        'profile_comment',
        'profile_icon_path',
        'public_username',
        'staff_public_id',
        'role_id',
        'name',
        'email',
        'password',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'last_login_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }


    public function billingProfile(): HasOne
    {
        return $this->hasOne(UserBillingProfile::class);
    }

    public function hasPlusAccess(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->billingProfile?->hasPaidAccess() ?? false;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public const ROLE_STAFF = 'staff';

    public const ROLE_WRITER = 'writer';

    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }

    public function hasPermission(string $permissionKey): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role?->permissions()
            ->where('permission_key', $permissionKey)
            ->exists() ?? false;
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function isWriter(): bool
    {
        return $this->isActive()
            && ! $this->isSuperAdmin()
            && $this->hasRole(self::ROLE_WRITER);
    }

    public function isStaff(): bool
    {
        return $this->isActive()
            && ! $this->isSuperAdmin()
            && $this->hasRole(self::ROLE_STAFF);
    }

    public function canManageAllAdminFeatures(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canAccessAdmin(): bool
    {
        return $this->canManageAllAdminFeatures() || $this->isStaff();
    }

    public function canAccessWriter(): bool
    {
        return $this->isSuperAdmin() || $this->isWriter();
    }
    public function canModifyOwnedAdminContent($model): bool
    {
        if ($this->canManageAllAdminFeatures()) {
            return true;
        }

        return $this->isStaff()
            && isset($model->created_by)
            && ! is_null($model->created_by)
            && (int) $model->created_by === (int) $this->id;
    }

}
