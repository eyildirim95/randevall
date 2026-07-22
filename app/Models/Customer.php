<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'email', 'birthday', 'gender', 'notes',
        'is_blacklisted', 'kvkk_consent_at',
    ];

    /** loyalty_points ve istatistik alanlari sadece servisler uzerinden guncellenir. */
    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'is_blacklisted' => 'bool',
            'kvkk_consent_at' => 'datetime',
            'total_spent' => 'decimal:2',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(CustomerRecord::class)->latest();
    }

    /** Telefonu normalize ederek sakla (bosluk/tire temizle). */
    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = $value ? preg_replace('/[^\d+]/', '', $value) : null;
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }
}
