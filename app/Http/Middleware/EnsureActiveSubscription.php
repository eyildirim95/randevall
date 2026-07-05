<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abonelik yaptirimi: deneme/plan suresi dolan isletmenin paneli
 * yalnizca abonelik/odeme sayfalarina erisebilir.
 */
class EnsureActiveSubscription
{
    /** Suresi dolsa da erisilebilen route'lar (odeme yapabilsin). */
    private const ALLOWED_ROUTES = [
        'panel.subscription.index',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $business = Tenancy::current();
        $user = $request->user();

        // Super admin (destek modu dahil) her zaman gecebilir
        if (! $business || $user?->isSuperAdmin() || $business->hasActivePlan()) {
            return $next($request);
        }

        if (in_array($request->route()?->getName(), self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        // Yonetici odemeye yonlendirilir; personel bilgilendirme ekrani gorur
        if ($user && $user->canManage($business)) {
            return redirect()
                ->route('panel.subscription.index', $business)
                ->withErrors(['subscription' => 'Aboneliğinizin süresi doldu. Panele erişmek için planınızı yenileyin.']);
        }

        return response()->view('panel.subscription-expired', ['business' => $business], 402);
    }
}
