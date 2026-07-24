{{-- Tenant panel PWA meta --}}
<link rel="manifest" href="{{ url($business->slug.'/panel/manifest.webmanifest') }}">
<meta name="theme-color" content="{{ config('pwa.theme_color') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ $business->name }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
