<?php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Panel erisimi: kullanici bu isletmeye uye olmali
 * veya impersonation yapan bir super admin olmali.
 */
class EnsureBusinessAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $business = Tenancy::current();

        if (! $user || ! $business) {
            abort(403);
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors(['email' => 'Hesabınız devre dışı bırakılmış.']);
        }

        $isImpersonating = $user->isSuperAdmin()
            && $request->session()->get('impersonating_business_id') === $business->id;

        if (! $user->belongsToBusiness($business) && ! $isImpersonating) {
            abort(403, 'Bu işletmeye erişim yetkiniz yok.');
        }

        return $next($request);
    }
}
