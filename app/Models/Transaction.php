<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'type', 'category', 'amount', 'description', 'transaction_date',
        'payment_method', 'appointment_id', 'customer_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeIncome($query)
    {
        return $query->where('type', TransactionType::Income->value);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', TransactionType::Expense->value);
    }

    public static function incomeCategories(): array
    {
        return ['Hizmet', 'Ürün Satışı', 'Diğer Gelir'];
    }

    public static function expenseCategories(): array
    {
        return ['Kira', 'Maaş', 'Fatura', 'Malzeme', 'Vergi', 'Pazarlama', 'Diğer Gider'];
    }
}
