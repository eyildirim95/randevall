<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'sector', 'phone', 'email', 'address', 'city',
        'logo_path', 'cover_path', 'description', 'instagram', 'website',
        'timezone', 'currency',
        'slot_interval_minutes', 'min_notice_minutes', 'max_advance_days',
        'online_booking_enabled', 'auto_confirm_online',
        'loyalty_enabled', 'loyalty_points_per_visit', 'loyalty_redeem_threshold', 'loyalty_reward_description',
        'whatsapp_enabled',
        'sms_enabled',
        'email_notifications_enabled', 'reminder_hours_before',
    ];

    /**
     * Hassas/yonetimsel alanlar fillable DISINDA tutulur (mass-assignment korumasi):
     * whatsapp_api_key, subscription_plan_id, trial_ends_at, plan_expires_at,
     * is_active, suspended_at, suspension_reason
     */
    protected function casts(): array
    {
        return [
            'online_booking_enabled' => 'bool',
            'auto_confirm_online' => 'bool',
            'loyalty_enabled' => 'bool',
            'whatsapp_enabled' => 'bool',
            'sms_enabled' => 'bool',
            'email_notifications_enabled' => 'bool',
            'is_active' => 'bool',
            'trial_ends_at' => 'datetime',
            'plan_expires_at' => 'datetime',
            'suspended_at' => 'datetime',
            'whatsapp_api_key' => 'encrypted',
        ];
    }

    // ── İlişkiler ──────────────────────────────────────────────

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    public function closures(): HasMany
    {
        return $this->hasMany(CalendarClosure::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function waitingListEntries(): HasMany
    {
        return $this->hasMany(WaitingListEntry::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ── Durum yardimcilari ────────────────────────────────────

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function hasActivePlan(): bool
    {
        if ($this->onTrial()) {
            return true;
        }

        if ($this->plan_expires_at !== null && $this->plan_expires_at->isFuture()) {
            return true;
        }

        return $this->subscriptions()
            ->where('status', SubscriptionStatus::Active)
            ->where('ends_at', '>', now())
            ->exists();
    }

    public function isOperational(): bool
    {
        return $this->is_active && ! $this->isSuspended();
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->logo_path
            ? asset('storage/'.$this->logo_path)
            : null);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
