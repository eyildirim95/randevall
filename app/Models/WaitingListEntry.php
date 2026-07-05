<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitingListEntry extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'customer_name', 'customer_phone', 'service_id', 'staff_id', 'preferred_date',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'notified_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }
}
