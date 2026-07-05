<?php

namespace App\Enums;

enum BusinessRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'İşletme Sahibi',
            self::Manager => 'Yönetici',
            self::Staff => 'Personel',
        };
    }

    /** Yonetim yetkisi olan roller */
    public static function managers(): array
    {
        return [self::Owner->value, self::Manager->value];
    }
}
