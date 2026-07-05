<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id', 'staff_id', 'service_id',
        'starts_at', 'ends_at', 'price', 'notes', 'source',
    ];

    /** status, public_token, created_by yalnizca kontrollu sekilde degistirilir. */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'price' => 'decimal:2',
            'status' => AppointmentStatus::class,
            'confirmation_sent_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $appointment) {
            $appointment->public_token ??= (string) Str::uuid();
        });
    }

    // ── İlişkiler ──────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(AppointmentStatusLog::class);
    }

    // ── Scope'lar ─────────────────────────────────────────────

    public function scopeBlocking($query)
    {
        return $query->whereIn('status', AppointmentStatus::blocking());
    }

    public function scopeBetween($query, $start, $end)
    {
        return $query->where('starts_at', '<', $end)->where('ends_at', '>', $start);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }

    // ── Durum yardimcilari ────────────────────────────────────

    public function isCancellable(): bool
    {
        return in_array($this->status, [AppointmentStatus::Pending, AppointmentStatus::Confirmed], true)
            && $this->starts_at->isFuture();
    }

    public function durationMinutes(): int
    {
        return (int) $this->starts_at->diffInMinutes($this->ends_at);
    }
}
