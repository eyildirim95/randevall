<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Path tabanli tenant cozumlemesi: domain.com/{business:slug}/...
 *
 * Route model binding Business'i slug ile cozer; bu middleware
 * tenant'i container'a baglar ve isletmenin erisilebilir oldugunu dogrular.
 */
class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $business = $request->route('business');

        if (! $business instanceof Business) {
            abort(404);
        }

        // Askiya alinmis / pasif isletmelerin sayfalari yayinlanmaz.
        // Super admin inceleme icin erisebilir.
        if (! $business->isOperational() && ! $request->user()?->isSuperAdmin()) {
            abort(404);
        }

        Tenancy::set($business);

        return $next($request);
    }
}
