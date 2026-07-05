<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'name', 'title', 'phone', 'email', 'color', 'photo_path',
        'accepts_online_booking', 'is_active', 'sort_order', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'accepts_online_booking' => 'bool',
            'is_active' => 'bool',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_staff')->withTimestamps();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    /** Takvim beslemesi gizli anahtari; yoksa uretilir. */
    public function icsToken(): string
    {
        if (! $this->ics_token) {
            $this->forceFill(['ics_token' => (string) \Illuminate\Support\Str::uuid()])->save();
        }

        return $this->ics_token;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
