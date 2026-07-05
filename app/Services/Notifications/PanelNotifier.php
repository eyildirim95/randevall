<?php

namespace App\Services\Notifications;

use App\Models\Business;
use App\Models\PanelNotification;

/**
 * Panel ici bildirimler (topbar can menusu).
 * Tenant baglami olmadan da guvenle cagrilabilir (job/command icinden).
 */
class PanelNotifier
{
    public static function notify(Business|int $business, string $type, string $title, ?string $url = null): void
    {
        $notification = new PanelNotification([
            'type' => $type,
            'title' => mb_substr($title, 0, 190),
            'url' => $url,
        ]);
        $notification->business_id = $business instanceof Business ? $business->id : $business;
        $notification->save();
    }
}
