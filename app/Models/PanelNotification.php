<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PanelNotification extends Model
{
    use BelongsToTenant;

    protected $fillable = ['type', 'title', 'url'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function icon(): string
    {
        return match ($this->type) {
            'online_booking' => 'ri-calendar-check-line text-success',
            'customer_cancelled' => 'ri-close-circle-line text-danger',
            'waitlist_joined' => 'ri-timer-flash-line text-warning',
            'ticket_replied' => 'ri-customer-service-2-line text-info',
            default => 'ri-notification-3-line text-primary',
        };
    }
}
