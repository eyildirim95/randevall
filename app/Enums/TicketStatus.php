<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Answered = 'answered';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Açık',
            self::Answered => 'Yanıtlandı',
            self::Closed => 'Kapalı',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::Answered => 'primary',
            self::Closed => 'secondary',
        };
    }
}
