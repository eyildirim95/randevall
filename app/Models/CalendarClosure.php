<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarClosure extends Model
{
    use BelongsToTenant;

    protected $fillable = ['staff_id', 'starts_at', 'ends_at', 'reason'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function scopeBetween($query, $start, $end)
    {
        return $query->where('starts_at', '<', $end)->where('ends_at', '>', $start);
    }

    /** Belirli personel icin gecerli kapatmalar (geneller dahil). */
    public function scopeForStaff($query, ?int $staffId)
    {
        return $query->where(function ($q) use ($staffId) {
            $q->whereNull('staff_id');
            if ($staffId) {
                $q->orWhere('staff_id', $staffId);
            }
        });
    }
}
