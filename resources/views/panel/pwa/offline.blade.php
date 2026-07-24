<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Çevrimdışı | {{ $business->name }}</title>
    <meta name="theme-color" content="{{ config('pwa.theme_color') }}">
    <style>
        body {
            margin: 0;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, sans-serif;
            background: #f8f9fa;
            color: #323a46;
            padding: 1.5rem;
            text-align: center;
        }
        .box { max-width: 24rem; }
        h1 { font-size: 1.25rem; margin-bottom: .5rem; }
        p { color: #687d92; margin-bottom: 1rem; }
        a {
            display: inline-block;
            background: {{ config('pwa.theme_color') }};
            color: #fff;
            text-decoration: none;
            padding: .65rem 1.25rem;
            border-radius: .5rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>İnternet bağlantısı yok</h1>
        <p>{{ $business->name }} paneline şu an erişilemiyor. Bağlantınızı kontrol edip tekrar deneyin.</p>
        <a href="{{ route('panel.dashboard', $business) }}">Yeniden dene</a>
    </div>
</body>
</html>
