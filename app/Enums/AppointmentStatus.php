<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Onay Bekliyor',
            self::Confirmed => 'Onaylandı',
            self::Completed => 'Tamamlandı',
            self::Cancelled => 'İptal Edildi',
            self::NoShow => 'Gelmedi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'primary',
            self::Completed => 'success',
            self::Cancelled => 'danger',
            self::NoShow => 'secondary',
        };
    }

    /** Takvimi mesgul eden durumlar */
    public static function blocking(): array
    {
        return [self::Pending->value, self::Confirmed->value, self::Completed->value];
    }
}
