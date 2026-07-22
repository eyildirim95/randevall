<?php

namespace App\Services\Messaging;

use App\Models\Business;
use App\Models\MessageLog;

/** WhatsApp + SMS icin ortak aylik plan kotasi. */
class MessageQuota
{
    public static function exceeded(Business $business): bool
    {
        $quota = (int) ($business->plan?->whatsapp_quota_monthly ?? 0);

        if ($quota <= 0) {
            return false;
        }

        return self::usedThisMonth($business) >= $quota;
    }

    public static function usedThisMonth(Business $business): int
    {
        return MessageLog::query()
            ->where('business_id', $business->id)
            ->whereIn('channel', ['whatsapp', 'sms'])
            ->where('status', 'sent')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }
}
