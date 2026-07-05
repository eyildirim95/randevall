<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <style>
        :root { --brand: #7f56da; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
               color: #313a46; background: #f6f7fb; line-height: 1.7; }
        .legal-nav { background: #fff; border-bottom: 1px solid #eaedf1; padding: 16px 0; }
        .legal-wrap { max-width: 860px; margin: 0 auto; padding: 0 20px; }
        .brand { font-size: 22px; font-weight: 800; color: #1a1a2e; text-decoration: none; }
        .brand span { color: var(--brand); }
        .legal-card { background: #fff; border: 1px solid #eaedf1; border-radius: 14px;
                      padding: 40px; margin: 32px auto; }
        h1 { font-size: 28px; margin: 0 0 6px; color: #1a1a2e; }
        h2 { font-size: 19px; margin: 28px 0 10px; color: #1a1a2e; }
        .updated { color: #8a94a6; font-size: 14px; margin-bottom: 24px; }
        a { color: var(--brand); }
        ul { padding-left: 20px; }
        .legal-links { margin-top: 28px; padding-top: 20px; border-top: 1px solid #eaedf1; font-size: 14px; }
        .legal-links a { margin-right: 16px; text-decoration: none; color: #6c757d; }
        footer { text-align: center; color: #8a94a6; font-size: 13px; padding: 24px 0 40px; }
        .placeholder { background: #fff7e6; border: 1px dashed #e0a800; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <nav class="legal-nav">
        <div class="legal-wrap">
            <a href="{{ route('landing') }}" class="brand">Boo<span>Kıbrıs</span></a>
        </div>
    </nav>

    <div class="legal-wrap">
        <div class="legal-card">
            <h1>@yield('title')</h1>
            <p class="updated">Son güncelleme: {{ \Carbon\Carbon::parse(config('legal.updated_at', '2026-07-05'))->translatedFormat('d F Y') }}</p>

            @yield('content')

            <div class="legal-links">
                <a href="{{ route('legal.terms') }}">Kullanım Koşulları</a>
                <a href="{{ route('legal.privacy') }}">Gizlilik &amp; KVKK</a>
                <a href="{{ route('legal.distance-sales') }}">Mesafeli Satış Sözleşmesi</a>
                <a href="{{ route('legal.cookies') }}">Çerez Politikası</a>
            </div>
        </div>
    </div>

    <footer>© {{ date('Y') }} {{ config('app.name') }}. Tüm hakları saklıdır.</footer>
</body>
</html>
