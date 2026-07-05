<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkingHour extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'staff_id', 'day_of_week', 'start_time', 'end_time',
        'break_start', 'break_end', 'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'is_closed' => 'bool',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public static function dayNames(): array
    {
        return [
            1 => 'Pazartesi',
            2 => 'Salı',
            3 => 'Çarşamba',
            4 => 'Perşembe',
            5 => 'Cuma',
            6 => 'Cumartesi',
            0 => 'Pazar',
        ];
    }
}
