<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Trial => 'Deneme',
            self::Active => 'Aktif',
            self::PastDue => 'Ödeme Gecikmiş',
            self::Cancelled => 'İptal Edilmiş',
            self::Expired => 'Süresi Dolmuş',
        };
    }
}
