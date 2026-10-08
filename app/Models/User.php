<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    const ROLE_ADMIN = 1;
    const ROLE_MANAGER = 2;
    const ROLE_USER = 3; // Both Buyer and Seller

    const STATUS_ACTIVE = 1;
    const STATUS_BLOCKED = 2;
    const STATUS_SUSPENDED = 3;

    const CREATED_BY_ADMIN = 1;


    protected $fillable = [
        'profile_completed_at',
        'name',
        'phone',
        'email',
        'password',
        'role',
        'profile_photo',
        'status',
        'created_by',
        'blocked_reason',
        'phone_verified_at',
        'last_login_at',
        'pincode',
        'address',
        'city',
        'state',
        'latitude',
        'longitude',
        'location_updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'profile_completed_at' => 'datetime',
            'role' => 'integer',
            'status' => 'integer',
            'created_by' => 'integer',
            'password' => 'hashed',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'location_updated_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public static function getRoleList(): array
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_USER => 'User',
        ];
    }

    public function getRoleNameAttribute(): string
    {
        return self::getRoleList()[$this->role] ?? 'Unknown';
    }

    public static function getStatusList(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_BLOCKED => 'Blocked',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_BLOCKED => 'danger',
            self::STATUS_SUSPENDED => 'warning',
            default => 'secondary',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'ti ti-check',
            self::STATUS_BLOCKED => 'ti ti-ban',
            self::STATUS_SUSPENDED => 'ti ti-alert-triangle',
            default => 'ti ti-info-circle',
        };
    }

    public function getAvatarHtmlAttribute(): string
    {
        if (!empty($this->profile_photo)) {
            $url = filter_var($this->profile_photo, FILTER_VALIDATE_URL)
                ? $this->profile_photo
                : asset('storage/' . $this->profile_photo);
            return '<span class="avatar" style="background-image: url(' . e($url) . ')"></span>';
        }

        $initial = mb_strtoupper(mb_substr($this->name ?? 'U', 0, 1));
        $colors = ['blue', 'green', 'cyan', 'yellow', 'red'];
        $index = abs(crc32($this->name ?? 'U')) % count($colors);
        $colorClass = $colors[$index];

        return '<span class="avatar bg-' . $colorClass . '-lt fw-bold">' . e($initial) . '</span>';
    }

    // ─── Helpers ──────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // ─── Scopes ───────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeAdmin(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeManager(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_MANAGER);
    }

    public function scopeUsers(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_USER);
    }

    // ─── Relationships ────────────────────────

    public function loginHistories()
    {
        // If LoginHistory model exists
        return $this->hasMany(LoginHistory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // ─── API Response ────────────────────────

    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'is_profile_complete' => !is_null($this->profile_completed_at),
            'profile_completed_at' => $this->profile_completed_at,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'role' => $this->role,
            'role_name' => $this->role_name,
            'status' => $this->status,
            'status_name' => $this->status_name,
            'profile_photo' => $this->profile_photo ? asset('storage/' . $this->profile_photo) : null,
            'address' => $this->address,
            'state' => $this->state,
            'city' => $this->city,
            'pincode' => $this->pincode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'created_at' => $this->created_at,
        ];
    }
}
