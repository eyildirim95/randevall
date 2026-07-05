<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name', 'message', 'audience'];

    /** status ve sayaclar yalnizca gonderim isi tarafindan guncellenir. */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function audiences(): array
    {
        return [
            'all' => 'Tüm müşteriler',
            'loyal' => 'Sadık müşteriler (3+ tamamlanan randevu)',
            'recent' => 'Son 90 günde gelenler',
        ];
    }
}
