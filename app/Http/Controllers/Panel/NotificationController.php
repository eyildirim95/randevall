<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\PanelNotification;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function markAllRead(Business $business): RedirectResponse
    {
        PanelNotification::query()->unread()->update(['read_at' => now()]);

        return back();
    }
}
