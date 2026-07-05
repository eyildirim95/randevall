<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    protected $fillable = [
        'name', 'business_name', 'sector', 'phone', 'email', 'message', 'ip_address',
    ];

    /** status ve admin_notes yalnizca super admin panelinden guncellenir. */
    public static function statuses(): array
    {
        return [
            'new' => 'Yeni',
            'contacted' => 'İletişime Geçildi',
            'converted' => 'Müşteriye Dönüştü',
            'closed' => 'Kapatıldı',
        ];
    }
}
