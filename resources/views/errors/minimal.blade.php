<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    @include('layouts.partials.favicons')
    <title>@yield('code') — {{ config('app.name', 'BooKıbrıs') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
               background: radial-gradient(1200px 600px at 50% -10%, #ece7fb 0%, #f6f7fb 60%); color: #313a46; padding: 24px; }
        .box { text-align: center; max-width: 480px; }
        .brand { font-size: 22px; font-weight: 800; color: #1a1a2e; text-decoration: none; display: inline-block; margin-bottom: 28px; }
        .brand span { color: #7f56da; }
        .code { font-size: 84px; font-weight: 800; line-height: 1; color: #7f56da; margin: 0; letter-spacing: -2px; }
        h1 { font-size: 22px; margin: 12px 0 8px; color: #1a1a2e; }
        p { color: #6c757d; margin: 0 0 28px; }
        .btn { display: inline-block; background: #7f56da; color: #fff; text-decoration: none;
               padding: 12px 26px; border-radius: 10px; font-weight: 600;
               box-shadow: 0 12px 30px rgba(127,86,218,.3); transition: transform .15s; }
        .btn:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="box">
        <a href="{{ url('/') }}" class="brand">Boo<span>Kıbrıs</span></a>
        <p class="code">@yield('code')</p>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}" class="btn">Ana sayfaya dön</a>
    </div>
</body>
</html>
