<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\WaitingListEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WaitlistController extends Controller
{
    public function index(Business $business): View
    {
        $entries = WaitingListEntry::query()
            ->with(['service:id,name', 'staff:id,name'])
            ->orderByRaw("CASE status WHEN 'waiting' THEN 0 WHEN 'notified' THEN 1 ELSE 2 END")
            ->orderBy('preferred_date')
            ->paginate(25);

        return view('panel.waitlist', compact('business', 'entries'));
    }

    public function destroy(Business $business, WaitingListEntry $waitingListEntry): RedirectResponse
    {
        $waitingListEntry->delete();

        return back()->with('success', 'Bekleme listesi kaydı silindi.');
    }
}
