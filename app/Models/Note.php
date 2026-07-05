<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'title', 'content', 'color', 'is_pinned', 'is_task', 'is_completed', 'due_date',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'bool',
            'is_task' => 'bool',
            'is_completed' => 'bool',
            'due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
