<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Yalnizca isletme sahibi/yoneticisi (veya impersonation'daki super admin)
 * erisebilen bolumler: ayarlar, gelir-gider, raporlar, personel yonetimi.
 */
class EnsureBusinessManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $business = Tenancy::current();

        $isImpersonating = $user?->isSuperAdmin()
            && $request->session()->get('impersonating_business_id') === $business?->id;

        if (! $user || ! $business || (! $user->canManage($business) && ! $isImpersonating)) {
            abort(403, 'Bu bölüme yalnızca işletme yöneticileri erişebilir.');
        }

        return $next($request);
    }
}
