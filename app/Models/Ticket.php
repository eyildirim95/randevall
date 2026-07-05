<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use BelongsToTenant;

    protected $fillable = ['subject', 'category', 'priority'];

    /** status, last_reply_at, closed_at yalnizca kontrollu akislarla degisir. */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'last_reply_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at');
    }

    public function isClosed(): bool
    {
        return $this->status === TicketStatus::Closed;
    }

    public static function categories(): array
    {
        return [
            'technical' => 'Teknik Sorun',
            'billing' => 'Ödeme / Abonelik',
            'feature' => 'Özellik Talebi',
            'other' => 'Diğer',
        ];
    }

    public static function priorities(): array
    {
        return [
            'low' => 'Düşük',
            'normal' => 'Normal',
            'high' => 'Yüksek',
        ];
    }
}
