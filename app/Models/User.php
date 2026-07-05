<?php

namespace App\Models;

use App\Enums\BusinessRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /** is_super_admin ve is_active bilerek fillable disinda (yetki yukseltme korumasi). */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'bool',
            'is_active' => 'bool',
        ];
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    // ── Yetki yardimcilari ────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    public function roleIn(Business $business): ?string
    {
        $pivot = $this->businesses()
            ->where('businesses.id', $business->id)
            ->first()?->pivot;

        return $pivot?->role;
    }

    public function isOwnerOf(Business $business): bool
    {
        return $this->roleIn($business) === BusinessRole::Owner->value;
    }

    public function canManage(Business $business): bool
    {
        return in_array($this->roleIn($business), BusinessRole::managers(), true);
    }

    public function belongsToBusiness(Business $business): bool
    {
        return $this->roleIn($business) !== null;
    }

    /** Yalnizca personel rolunde mi (yonetim yetkisi yok)? */
    public function isStaffOnly(Business $business): bool
    {
        return $this->roleIn($business) === BusinessRole::Staff->value && ! $this->isSuperAdmin();
    }

    /** Bu isletmedeki personel karti id'si (tenant baglaminda cagrilmali). */
    public function staffIdIn(Business $business): ?int
    {
        return Staff::query()
            ->where('business_id', $business->id)
            ->where('user_id', $this->id)
            ->value('id');
    }
}
