<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PwaController extends Controller
{
    public function manifest(Business $business): JsonResponse
    {
        $scope = url($business->slug.'/panel/');
        $startUrl = route('panel.dashboard', $business);

        return response()->json([
            'name' => $business->name.' — '.config('app.name'),
            'short_name' => mb_substr($business->name, 0, 12),
            'description' => $business->name.' işletme paneli — randevu ve müşteri yönetimi',
            'start_url' => $startUrl,
            'scope' => $scope,
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => config('pwa.background_color'),
            'theme_color' => config('pwa.theme_color'),
            'lang' => 'tr',
            'dir' => 'ltr',
            'categories' => ['business', 'productivity'],
            'icons' => collect(config('pwa.icons'))->map(fn (array $icon) => [
                ...$icon,
                'src' => asset(ltrim($icon['src'], '/')),
            ])->values()->all(),
        ], 200, [
            'Content-Type' => 'application/manifest+json; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function serviceWorker(Business $business): Response
    {
        $cacheKey = 'randevall-panel-'.config('pwa.cache_version').'-'.$business->slug;

        return response()
            ->view('panel.pwa.service-worker', [
                'business' => $business,
                'cacheKey' => $cacheKey,
                'offlineUrl' => route('panel.pwa.offline', $business),
                'precacheUrls' => [
                    asset('apple-touch-icon.png'),
                    asset('favicon-32.png'),
                    route('panel.dashboard', $business),
                ],
            ])
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Service-Worker-Allowed', '/'.$business->slug.'/panel/');
    }

    public function offline(Business $business): Response
    {
        return response()->view('panel.pwa.offline', compact('business'));
    }
}
