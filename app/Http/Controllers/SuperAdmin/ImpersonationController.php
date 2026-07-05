<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\ImpersonationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Super admin'in destek amacli isletme paneline girmesi.
 * Ayri oturum acilmaz; session isaretlenir ve loglanir.
 */
class ImpersonationController extends Controller
{
    public function start(Business $business, Request $request): RedirectResponse
    {
        $request->session()->put('impersonating_business_id', $business->id);

        $logId = ImpersonationLog::create([
            'admin_id' => $request->user()->id,
            'business_id' => $business->id,
            'started_at' => now(),
        ])->id;

        $request->session()->put('impersonation_log_id', $logId);

        return redirect()
            ->route('panel.dashboard', $business)
            ->with('success', $business->name.' paneline destek modunda girdiniz.');
    }

    public function stop(Request $request): RedirectResponse
    {
        $logId = $request->session()->pull('impersonation_log_id');

        if ($logId) {
            ImpersonationLog::query()->whereKey($logId)->update(['ended_at' => now()]);
        }

        $request->session()->forget('impersonating_business_id');

        return redirect()->route('admin.dashboard');
    }
}
