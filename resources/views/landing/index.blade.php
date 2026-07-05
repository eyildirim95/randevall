<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark" style="scroll-behavior: smooth">
<head>
    <meta charset="utf-8"/>
    <title>Reserup — İşletmeniz İçin Akıllı Randevu Sistemi | Kıbrıs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="Berber, kuaför, güzellik salonu ve tüm randevulu işletmeler için online randevu, WhatsApp hatırlatma, gelir-gider takibi ve müşteri yönetimi. Kıbrıs'ın randevu sistemi."/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/scss/app.scss', 'resources/scss/icons.scss'])
    <style>
        :root {
            --lp-bg: #08070f;
            --lp-bg-soft: #0e0c1a;
            --lp-card: rgba(255, 255, 255, .03);
            --lp-border: rgba(255, 255, 255, .08);
            --lp-border-hover: rgba(165, 134, 240, .45);
            --lp-text: #e6e4f0;
            --lp-muted: #9793b3;
            --lp-primary: #7f56da;
            --lp-primary-2: #a586f0;
            --lp-accent: #22c55e;
        }

        body.landing {
            background: var(--lp-bg);
            color: var(--lp-text);
            font-feature-settings: "ss01";
            overflow-x: hidden;
        }

        .landing ::selection { background: rgba(127, 86, 218, .45); color: #fff; }

        /* ── Arka plan dokulari ─────────────────────────────── */
        .grid-bg {
            position: absolute; inset: 0; pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse 90% 65% at 50% 0%, #000 55%, transparent 100%);
        }

        .orb {
            position: absolute; border-radius: 50%; filter: blur(90px);
            pointer-events: none; opacity: .5;
            animation: orb-float 14s ease-in-out infinite;
        }
        .orb-1 { width: 480px; height: 480px; background: #5b2ea6; top: -160px; left: 8%; }
        .orb-2 { width: 360px; height: 360px; background: #2e1d7a; top: 60px; right: 4%; animation-delay: -5s; }
        .orb-3 { width: 300px; height: 300px; background: rgba(34, 197, 94, .5); bottom: -120px; left: 42%; animation-delay: -9s; }

        @keyframes orb-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, 28px) scale(1.06); }
            66% { transform: translate(-30px, -20px) scale(.96); }
        }

        /* ── Navbar ─────────────────────────────────────────── */
        .lp-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1030;
            transition: background .3s ease, border-color .3s ease, box-shadow .3s ease;
            border-bottom: 1px solid transparent;
        }
        .lp-nav.scrolled {
            background: rgba(8, 7, 15, .72);
            backdrop-filter: blur(14px) saturate(1.4);
            -webkit-backdrop-filter: blur(14px) saturate(1.4);
            border-bottom-color: var(--lp-border);
        }
        .lp-nav .nav-link { color: var(--lp-muted); font-weight: 500; transition: color .2s; }
        .lp-nav .nav-link:hover { color: #fff; }

        .btn-glow {
            background: linear-gradient(135deg, var(--lp-primary), #6d3fd1);
            color: #fff; border: 0; font-weight: 600;
            box-shadow: 0 0 0 rgba(127, 86, 218, 0);
            transition: transform .2s ease, box-shadow .3s ease;
        }
        .btn-glow:hover {
            color: #fff; transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(127, 86, 218, .45);
        }
        .btn-ghost {
            background: rgba(255,255,255,.05); color: var(--lp-text);
            border: 1px solid var(--lp-border); font-weight: 500;
            transition: border-color .2s, background .2s, transform .2s;
        }
        .btn-ghost:hover { color: #fff; border-color: rgba(255,255,255,.25); background: rgba(255,255,255,.08); transform: translateY(-2px); }

        /* ── Hero ───────────────────────────────────────────── */
        .hero { position: relative; padding: 168px 0 96px; }

        .gradient-text {
            background: linear-gradient(93deg, #fff 20%, #c9b8f5 55%, var(--lp-primary-2) 85%);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent; color: transparent;
        }

        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 7px 16px; border-radius: 100px;
            background: rgba(127, 86, 218, .12);
            border: 1px solid rgba(165, 134, 240, .3);
            color: var(--lp-primary-2); font-size: 13.5px; font-weight: 500;
        }
        .hero-badge .pulse {
            width: 7px; height: 7px; border-radius: 50%; background: var(--lp-accent);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, .55); }
            70% { box-shadow: 0 0 0 9px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        /* ── Hero mockup ────────────────────────────────────── */
        .mockup-wrap { position: relative; perspective: 1200px; }
        .mockup {
            background: linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.02));
            border: 1px solid var(--lp-border); border-radius: 18px;
            backdrop-filter: blur(10px);
            box-shadow: 0 40px 90px rgba(0, 0, 0, .55), 0 0 80px rgba(127, 86, 218, .12);
            transform: rotateX(6deg);
            animation: mockup-in 1.1s cubic-bezier(.22, .8, .32, 1) both .3s;
        }
        @keyframes mockup-in {
            from { opacity: 0; transform: rotateX(14deg) translateY(48px); }
            to { opacity: 1; transform: rotateX(6deg) translateY(0); }
        }
        .mockup-topbar { border-bottom: 1px solid var(--lp-border); padding: 12px 18px; display: flex; gap: 6px; align-items: center; }
        .mockup-dot { width: 10px; height: 10px; border-radius: 50%; }

        .cal-cell {
            border-radius: 8px; border: 1px dashed rgba(255,255,255,.08);
            height: 42px; position: relative;
        }
        .cal-event {
            position: absolute; inset: 4px; border-radius: 6px;
            font-size: 10.5px; font-weight: 600; color: #fff;
            display: flex; align-items: center; padding: 0 8px;
            white-space: nowrap; overflow: hidden;
        }

        .float-chip {
            position: absolute; z-index: 3;
            background: rgba(14, 12, 26, .88); border: 1px solid var(--lp-border);
            border-radius: 14px; padding: 12px 16px;
            backdrop-filter: blur(12px);
            box-shadow: 0 18px 50px rgba(0,0,0,.5);
            animation: chip-float 6s ease-in-out infinite;
        }
        .float-chip.chip-2 { animation-delay: -3s; }
        @keyframes chip-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        /* ── Scroll reveal ──────────────────────────────────── */
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity .7s ease, transform .7s cubic-bezier(.22,.8,.32,1); }
        .reveal.visible { opacity: 1; transform: none; }
        .reveal-d1 { transition-delay: .08s; } .reveal-d2 { transition-delay: .16s; }
        .reveal-d3 { transition-delay: .24s; } .reveal-d4 { transition-delay: .32s; }
        .reveal-d5 { transition-delay: .40s; } .reveal-d6 { transition-delay: .48s; }

        /* ── Marquee ────────────────────────────────────────── */
        .marquee { overflow: hidden; mask-image: linear-gradient(90deg, transparent, #000 12%, #000 88%, transparent); }
        .marquee-track { display: flex; gap: 48px; width: max-content; animation: marquee 26s linear infinite; }
        .marquee-item { color: var(--lp-muted); font-weight: 600; font-size: 15px; display: flex; align-items: center; gap: 10px; opacity: .75; }
        @keyframes marquee { to { transform: translateX(-50%); } }

        /* ── Section basliklari ─────────────────────────────── */
        .sec-label {
            color: var(--lp-primary-2); font-size: 13px; font-weight: 700;
            letter-spacing: .14em; text-transform: uppercase;
        }
        .sec-title { color: #fff; font-weight: 700; letter-spacing: -.02em; }
        .lp-muted { color: var(--lp-muted); }

        /* ── Feature kartlari ───────────────────────────────── */
        .feat-card {
            background: var(--lp-card); border: 1px solid var(--lp-border);
            border-radius: 16px; padding: 26px; height: 100%;
            position: relative; overflow: hidden;
            transition: transform .3s ease, border-color .3s ease, background .3s ease;
        }
        .feat-card::before {
            content: ""; position: absolute; inset: 0;
            background: radial-gradient(420px circle at var(--mx, 50%) var(--my, 0%), rgba(127,86,218,.14), transparent 65%);
            opacity: 0; transition: opacity .3s;
        }
        .feat-card:hover { transform: translateY(-5px); border-color: var(--lp-border-hover); }
        .feat-card:hover::before { opacity: 1; }
        .feat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 23px;
            background: rgba(127, 86, 218, .14); color: var(--lp-primary-2);
            border: 1px solid rgba(165, 134, 240, .25);
        }

        /* ── Adimlar ────────────────────────────────────────── */
        .step-circle {
            width: 58px; height: 58px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 21px; font-weight: 700; color: #fff;
            background: linear-gradient(135deg, var(--lp-primary), #4c2b95);
            box-shadow: 0 10px 34px rgba(127, 86, 218, .4);
        }
        .step-line { position: absolute; top: 29px; left: calc(50% + 44px); right: calc(-50% + 44px); height: 2px;
            background: linear-gradient(90deg, rgba(127,86,218,.5), rgba(127,86,218,.08)); }

        /* ── Sayaclar ───────────────────────────────────────── */
        .stat-value { font-size: 44px; font-weight: 800; color: #fff; letter-spacing: -.03em; }
        .stat-value .plus { color: var(--lp-primary-2); }

        /* ── Fiyatlandirma ──────────────────────────────────── */
        .price-card {
            background: var(--lp-card); border: 1px solid var(--lp-border);
            border-radius: 20px; height: 100%;
            transition: transform .3s ease, border-color .3s, box-shadow .3s;
        }
        .price-card:hover { transform: translateY(-6px); border-color: rgba(255,255,255,.18); }
        .price-card.featured {
            background: linear-gradient(180deg, rgba(127,86,218,.14), rgba(127,86,218,.03));
            border: 1px solid rgba(165, 134, 240, .5);
            box-shadow: 0 24px 80px rgba(127, 86, 218, .18);
        }
        .price-card.featured:hover { box-shadow: 0 28px 90px rgba(127, 86, 218, .3); }
        .price-check { color: var(--lp-accent); }

        /* ── SSS ────────────────────────────────────────────── */
        .lp-accordion .accordion-item { background: var(--lp-card); border: 1px solid var(--lp-border); border-radius: 14px !important; overflow: hidden; margin-bottom: 12px; }
        .lp-accordion .accordion-button { background: transparent; color: var(--lp-text); font-weight: 600; box-shadow: none; }
        .lp-accordion .accordion-button:not(.collapsed) { color: var(--lp-primary-2); }
        .lp-accordion .accordion-body { color: var(--lp-muted); }

        /* ── Demo formu ─────────────────────────────────────── */
        .glass-panel {
            background: rgba(255,255,255,.035); border: 1px solid var(--lp-border);
            border-radius: 24px; backdrop-filter: blur(12px);
            position: relative; overflow: hidden;
        }
        .glass-panel::before {
            content: ""; position: absolute; top: -140px; right: -100px;
            width: 340px; height: 340px; border-radius: 50%;
            background: rgba(127, 86, 218, .22); filter: blur(80px);
        }
        .landing .form-control, .landing .form-select {
            background: rgba(255,255,255,.05); border-color: var(--lp-border); color: var(--lp-text);
        }
        .landing .form-control:focus, .landing .form-select:focus {
            background: rgba(255,255,255,.07); border-color: var(--lp-primary-2);
            box-shadow: 0 0 0 .2rem rgba(127, 86, 218, .18); color: #fff;
        }
        .landing .form-control::placeholder { color: rgba(151, 147, 179, .65); }

        .lp-footer { border-top: 1px solid var(--lp-border); background: var(--lp-bg-soft); }

        @media (max-width: 991px) {
            .hero { padding-top: 128px; }
            .step-line { display: none; }
            .float-chip { display: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            .orb, .float-chip, .marquee-track, .mockup { animation: none !important; }
            .reveal { opacity: 1; transform: none; transition: none; }
        }
    </style>
</head>
<body class="landing">

{{-- ── Navbar ──────────────────────────────────────────────── --}}
<nav class="lp-nav py-3" id="lp-nav">
    <div class="container d-flex align-items-center justify-content-between">
        <a class="fs-24 fw-bold text-white text-decoration-none" href="#">reser<span style="color:var(--lp-primary-2)">up</span></a>

        <div class="d-none d-lg-flex align-items-center gap-4">
            <a class="nav-link" href="#ozellikler">Özellikler</a>
            <a class="nav-link" href="#nasil-calisir">Nasıl Çalışır</a>
            <a class="nav-link" href="#fiyatlar">Fiyatlar</a>
            <a class="nav-link" href="#sss">S.S.S</a>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm px-3 py-2">Giriş Yap</a>
            <a href="{{ route('register') }}" class="btn btn-glow btn-sm px-3 py-2">Ücretsiz Dene</a>
        </div>
    </div>
</nav>

{{-- ── Hero ────────────────────────────────────────────────── --}}
<header class="hero">
    <div class="grid-bg"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="container position-relative">
        <div class="text-center mx-auto" style="max-width: 780px">
            <span class="hero-badge reveal visible mb-4">
                <span class="pulse"></span> Kıbrıs'ın randevu sistemi — 14 gün ücretsiz
            </span>
            <h1 class="display-3 fw-bold mt-4 mb-3 gradient-text" style="letter-spacing: -.03em; line-height: 1.08">
                Randevularınız cebinizde,<br>işletmeniz kontrol altında
            </h1>
            <p class="fs-17 lp-muted mb-4 mx-auto" style="max-width: 620px">
                Berber, kuaför, güzellik salonu, klinik... Online randevu linki, WhatsApp hatırlatma,
                gelir-gider takibi ve sadık müşteri sistemi — hepsi tek panelde.
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap mb-2">
                <a href="{{ route('register') }}" class="btn btn-glow btn-lg px-4 py-3">Hemen Başla <i class="ri-arrow-right-line ms-1"></i></a>
                <a href="#ozellikler" class="btn btn-ghost btn-lg px-4 py-3">Özellikleri Keşfet</a>
            </div>
            <p class="fs-13 lp-muted mt-3">Kredi kartı gerekmez · Kurulum ücreti yok · İstediğiniz zaman iptal</p>
        </div>

        {{-- Dashboard mockup --}}
        <div class="mockup-wrap mx-auto mt-5" style="max-width: 900px">
            <div class="float-chip" style="top: -6px; left: -70px">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-whatsapp-line fs-20" style="color: var(--lp-accent)"></i>
                    <div>
                        <div class="fw-semibold fs-13 text-white">Hatırlatma gönderildi</div>
                        <div class="fs-12 lp-muted">Ahmet Y. — yarın 14:30</div>
                    </div>
                </div>
            </div>
            <div class="float-chip chip-2" style="bottom: 40px; right: -76px">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-calendar-check-line fs-20" style="color: var(--lp-primary-2)"></i>
                    <div>
                        <div class="fw-semibold fs-13 text-white">Yeni online randevu 🎉</div>
                        <div class="fs-12 lp-muted">Saç + Sakal · Cumartesi 11:00</div>
                    </div>
                </div>
            </div>

            <div class="mockup">
                <div class="mockup-topbar">
                    <span class="mockup-dot" style="background:#ff5f57"></span>
                    <span class="mockup-dot" style="background:#febc2e"></span>
                    <span class="mockup-dot" style="background:#28c840"></span>
                    <span class="fs-12 lp-muted ms-2">reserup.com/demo-berber/panel/takvim</span>
                </div>
                <div class="p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge" style="background:var(--lp-primary)">Bugün</span>
                            <span class="fw-semibold text-white fs-14">Haftalık Takvim</span>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge" style="background:rgba(127,86,218,.2); color:var(--lp-primary-2)">Mehmet Usta</span>
                            <span class="badge" style="background:rgba(34,197,94,.15); color:var(--lp-accent)">Ali Kalfa</span>
                        </div>
                    </div>
                    <div class="d-grid gap-2" style="grid-template-columns: repeat(5, 1fr)">
                        @php
                            $mockEvents = [
                                ['09:00 Saç', 'rgba(127,86,218,.85)', 0], [null, null, 1], ['10:30 Sakal', 'rgba(34,197,94,.8)', 2], [null, null, 3], ['09:15 Bakım', 'rgba(59,130,246,.8)', 4],
                                [null, null, 0], ['11:00 Saç+Sakal', 'rgba(245,158,11,.85)', 1], [null, null, 2], ['13:30 Saç', 'rgba(127,86,218,.85)', 3], [null, null, 4],
                                ['15:00 Cilt', 'rgba(34,197,94,.8)', 0], [null, null, 1], ['16:30 Saç', 'rgba(127,86,218,.85)', 2], ['17:00 Sakal', 'rgba(59,130,246,.8)', 3], [null, null, 4],
                            ];
                        @endphp
                        @foreach($mockEvents as [$label, $color])
                            <div class="cal-cell">
                                @if($label)
                                    <div class="cal-event" style="background: {{ $color }}">{{ $label }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- ── Sektor marquee ──────────────────────────────────────── --}}
<div class="py-4 border-top border-bottom" style="border-color: var(--lp-border) !important">
    <div class="marquee">
        <div class="marquee-track">
            @foreach([1, 2] as $loop_)
                <span class="marquee-item"><i class="ri-scissors-2-line"></i> Berberler</span>
                <span class="marquee-item"><i class="ri-magic-line"></i> Kuaförler</span>
                <span class="marquee-item"><i class="ri-heart-2-line"></i> Güzellik Salonları</span>
                <span class="marquee-item"><i class="ri-hospital-line"></i> Klinikler</span>
                <span class="marquee-item"><i class="ri-run-line"></i> Spor & PT</span>
                <span class="marquee-item"><i class="ri-brush-line"></i> Dövme Stüdyoları</span>
                <span class="marquee-item"><i class="ri-psychotherapy-line"></i> Terapistler</span>
                <span class="marquee-item"><i class="ri-car-washing-line"></i> Oto Detailing</span>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Sayaclar ────────────────────────────────────────────── --}}
<section class="py-5">
    <div class="container py-4">
        <div class="row text-center g-4">
            <div class="col-md-3 col-6 reveal">
                <div class="stat-value"><span data-count="7">0</span><span class="plus">/24</span></div>
                <div class="lp-muted">Online randevu</div>
            </div>
            <div class="col-md-3 col-6 reveal reveal-d1">
                <div class="stat-value"><span data-count="60">0</span><span class="plus">sn</span></div>
                <div class="lp-muted">Ortalama randevu alma süresi</div>
            </div>
            <div class="col-md-3 col-6 reveal reveal-d2">
                <div class="stat-value">%<span data-count="40">0</span><span class="plus">↓</span></div>
                <div class="lp-muted">Daha az unutulan randevu</div>
            </div>
            <div class="col-md-3 col-6 reveal reveal-d3">
                <div class="stat-value"><span data-count="14">0</span><span class="plus">gün</span></div>
                <div class="lp-muted">Ücretsiz deneme</div>
            </div>
        </div>
    </div>
</section>

{{-- ── Ozellikler ──────────────────────────────────────────── --}}
<section id="ozellikler" class="py-5 position-relative">
    <div class="container py-4">
        <div class="text-center mb-5 reveal">
            <div class="sec-label mb-2">ÖZELLİKLER</div>
            <h2 class="sec-title display-6">İşletmenizin ihtiyacı olan her şey</h2>
            <p class="lp-muted mx-auto" style="max-width: 520px">Defter, telefon ve WhatsApp karmaşasına son. Tek panel, tam kontrol.</p>
        </div>

        <div class="row g-4">
            @php
                $features = [
                    ['icon' => 'ri-calendar-2-line', 'title' => 'Akıllı Takvim', 'text' => 'Takvimde tıkla randevu oluştur, sürükle-bırak ile taşı. Personel bazlı görünüm, çakışma koruması.'],
                    ['icon' => 'ri-link', 'title' => 'Online Randevu Linki', 'text' => 'Müşterileriniz size özel linkten 7/24 kendi randevusunu alır; siz uyurken bile takvim dolar.'],
                    ['icon' => 'ri-whatsapp-line', 'title' => 'WhatsApp Bildirimleri', 'text' => 'Onay ve hatırlatma mesajları otomatik gider; unutulan randevu derdi biter.'],
                    ['icon' => 'ri-wallet-3-line', 'title' => 'Gelir-Gider Takibi', 'text' => 'Tamamlanan randevu otomatik gelire işlenir. Kira, maaş, malzeme — hepsi tek tabloda.'],
                    ['icon' => 'ri-heart-2-line', 'title' => 'Sadık Müşteri Sistemi', 'text' => 'Her ziyarette puan, hedefte ödül. Müşteriniz size bağlansın, kazancınız artsın.'],
                    ['icon' => 'ri-bar-chart-2-line', 'title' => 'Detaylı Raporlar', 'text' => 'Personel performansı, hizmet dağılımı, ciro grafikleri ve tek tıkla CSV dışa aktarım.'],
                    ['icon' => 'ri-lock-2-line', 'title' => 'Takvim Kapatma', 'text' => 'Tatil veya izin mi var? Tek tıkla takvimi kapatın, o aralıkta kimse randevu alamasın.'],
                    ['icon' => 'ri-team-line', 'title' => 'Personel Yönetimi', 'text' => 'Her personele ayrı takvim, çalışma saati ve hizmet listesi; dilerseniz panel girişi.'],
                    ['icon' => 'ri-customer-service-2-line', 'title' => 'Canlı Destek', 'text' => 'Panelden destek talebi açın; ekibimiz aynı gün yanıtlasın. Yanınızdayız.'],
                ];
            @endphp
            @foreach($features as $i => $feature)
                <div class="col-md-6 col-lg-4 reveal reveal-d{{ ($i % 3) + 1 }}">
                    <div class="feat-card">
                        <div class="feat-icon mb-3"><i class="{{ $feature['icon'] }}"></i></div>
                        <h5 class="text-white fw-semibold mb-2">{{ $feature['title'] }}</h5>
                        <p class="lp-muted mb-0 fs-14">{{ $feature['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Nasil calisir ───────────────────────────────────────── --}}
<section id="nasil-calisir" class="py-5" style="background: var(--lp-bg-soft)">
    <div class="container py-4">
        <div class="text-center mb-5 reveal">
            <div class="sec-label mb-2">NASIL ÇALIŞIR</div>
            <h2 class="sec-title display-6">3 adımda başlayın</h2>
        </div>
        <div class="row g-5 text-center">
            @php
                $steps = [
                    ['title' => 'Demo talep edin', 'text' => 'Formu doldurun, aynı gün sizi arayalım; işletmenizi birlikte kuralım.'],
                    ['title' => 'Hizmetlerinizi ekleyin', 'text' => 'Hizmetler, ücretler, personel, çalışma saatleri... 15 dakikada hazır.'],
                    ['title' => 'Linkinizi paylaşın', 'text' => "Instagram bio'nuza koyun, müşteriler 7/24 kendi randevusunu alsın."],
                ];
            @endphp
            @foreach($steps as $i => $step)
                <div class="col-md-4 position-relative reveal reveal-d{{ $i + 1 }}">
                    @if($i < 2)<div class="step-line d-none d-md-block"></div>@endif
                    <div class="step-circle mx-auto mb-3">{{ $i + 1 }}</div>
                    <h5 class="text-white fw-semibold">{{ $step['title'] }}</h5>
                    <p class="lp-muted mx-auto" style="max-width: 280px">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Fiyatlar ────────────────────────────────────────────── --}}
<section id="fiyatlar" class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5 reveal">
            <div class="sec-label mb-2">FİYATLANDIRMA</div>
            <h2 class="sec-title display-6">Basit ve şeffaf</h2>
            <p class="lp-muted">Tüm planlarda {{ $plans->first()?->trial_days ?? 14 }} gün ücretsiz deneme. Gizli ücret yok.</p>
        </div>
        <div class="row g-4 justify-content-center">
            @forelse($plans as $i => $plan)
                <div class="col-md-4 reveal reveal-d{{ $i + 1 }}">
                    <div class="price-card {{ $plan->is_featured ? 'featured' : '' }} p-4 text-center">
                        @if($plan->is_featured)
                            <span class="badge mb-3" style="background: var(--lp-primary)">EN POPÜLER</span>
                        @endif
                        <h4 class="text-white">{{ $plan->name }}</h4>
                        <p class="lp-muted fs-14">{{ $plan->description }}</p>
                        <div class="my-3">
                            <span class="stat-value" style="font-size: 40px">{{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                            <span class="lp-muted">{{ $plan->currency }}/ay</span>
                        </div>
                        <p class="lp-muted fs-13 mb-4">Yıllık ödemede {{ number_format($plan->price_yearly, 0, ',', '.') }} {{ $plan->currency }}</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="py-1"><i class="ri-check-line price-check me-2"></i><span class="lp-muted">{{ $plan->max_staff > 0 ? $plan->max_staff.' personel' : 'Sınırsız personel' }}</span></li>
                            <li class="py-1"><i class="ri-check-line price-check me-2"></i><span class="lp-muted">{{ $plan->max_appointments_per_month > 0 ? $plan->max_appointments_per_month.' randevu/ay' : 'Sınırsız randevu' }}</span></li>
                            @foreach($plan->features ?? [] as $feature)
                                <li class="py-1"><i class="ri-check-line price-check me-2"></i><span class="lp-muted">{{ $feature }}</span></li>
                            @endforeach
                        </ul>
                        <a href="{{ route('register') }}" class="btn {{ $plan->is_featured ? 'btn-glow' : 'btn-ghost' }} w-100 py-2">Ücretsiz Dene</a>
                    </div>
                </div>
            @empty
                <div class="col-md-6 text-center lp-muted">Fiyat bilgisi için bizimle iletişime geçin.</div>
            @endforelse
        </div>
    </div>
</section>

{{-- ── SSS ─────────────────────────────────────────────────── --}}
<section id="sss" class="py-5" style="background: var(--lp-bg-soft)">
    <div class="container py-4" style="max-width: 760px">
        <div class="text-center mb-5 reveal">
            <div class="sec-label mb-2">S.S.S</div>
            <h2 class="sec-title display-6">Sık sorulan sorular</h2>
        </div>
        <div class="accordion lp-accordion reveal" id="faq">
            @php
                $faqs = [
                    ['q' => 'Kurulum için teknik bilgi gerekiyor mu?', 'a' => 'Hayır. Demo talebinizden sonra işletmenizi biz kuruyoruz; size sadece hizmet ve personel bilgilerinizi girmek kalıyor. Dilerseniz onu da birlikte yapıyoruz.'],
                    ['q' => 'WhatsApp mesajları nasıl gönderiliyor?', 'a' => 'Resmi WhatsApp Business API üzerinden gönderilir. Kendi numaranızla, onay ve hatırlatma mesajları otomatik gider.'],
                    ['q' => 'Müşterilerim nasıl randevu alacak?', 'a' => 'Size özel linkiniz olur (örn. reserup.com/isletmeniz). Bu linki Instagram, Google ve WhatsApp durumunuzda paylaşırsınız; müşteri hizmet, personel ve saati seçip randevusunu alır.'],
                    ['q' => 'Deneme süresi bitince ne olur?', 'a' => 'Dilediğiniz planı seçip kartla veya havale/EFT ile ödersiniz. Verileriniz aynen korunur.'],
                    ['q' => 'Verilerim güvende mi?', 'a' => 'Her işletmenin verisi birbirinden tamamen izole tutulur, şifreler ve API anahtarları şifrelenerek saklanır. Verileriniz sadece size aittir.'],
                    ['q' => 'İptal edersem ne olur?', 'a' => 'Taahhüt yok. İstediğiniz zaman iptal edebilirsiniz; dönem sonuna kadar sistem çalışmaya devam eder.'],
                ];
            @endphp
            @foreach($faqs as $i => $faq)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $i }}">
                            {{ $faq['q'] }}
                        </button>
                    </h2>
                    <div id="faq-{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#faq">
                        <div class="accordion-body">{{ $faq['a'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Demo formu ──────────────────────────────────────────── --}}
<section id="demo" class="py-5 position-relative">
    <div class="orb" style="width: 380px; height: 380px; background: #4c2b95; bottom: -80px; right: 6%; opacity: .35"></div>
    <div class="container py-4" style="max-width: 760px">
        <div class="glass-panel p-4 p-md-5 reveal">
            <div class="position-relative">
                <div class="text-center mb-4">
                    <div class="sec-label mb-2">ÜCRETSİZ DEMO</div>
                    <h2 class="sec-title display-6">Bugün deneyin</h2>
                    <p class="lp-muted mb-0">Formu doldurun, aynı gün dönüş yapalım. 14 gün boyunca tüm özellikler ücretsiz.</p>
                </div>

                @if(session('demo_success'))
                    <div class="alert alert-success text-center">
                        <i class="ri-checkbox-circle-line me-1"></i>{{ session('demo_success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('demo.store') }}" class="row g-3">
                    @csrf
                    {{-- Honeypot: botlar doldurur, insanlar gormez --}}
                    <input type="text" name="website" value="" style="display:none" tabindex="-1" autocomplete="off" aria-hidden="true">

                    <div class="col-md-6">
                        <label class="form-label lp-muted">Adınız Soyadınız <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control py-2" required maxlength="120" value="{{ old('name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label lp-muted">Telefon <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" class="form-control py-2" required maxlength="30" placeholder="+90 5xx xxx xx xx" value="{{ old('phone') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label lp-muted">İşletme Adı</label>
                        <input type="text" name="business_name" class="form-control py-2" maxlength="150" value="{{ old('business_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label lp-muted">Sektör</label>
                        <select name="sector" class="form-select py-2">
                            <option value="">Seçin...</option>
                            @foreach(['Berber', 'Kuaför', 'Güzellik Salonu', 'Klinik / Sağlık', 'Spor / PT', 'Diğer'] as $sector)
                                <option value="{{ $sector }}" @selected(old('sector') === $sector)>{{ $sector }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label lp-muted">E-posta</label>
                        <input type="email" name="email" class="form-control py-2" maxlength="190" value="{{ old('email') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label lp-muted">Mesajınız</label>
                        <input type="text" name="message" class="form-control py-2" maxlength="1000" placeholder="İsteğe bağlı" value="{{ old('message') }}">
                    </div>
                    <div class="col-12 d-grid mt-4">
                        <button class="btn btn-glow btn-lg py-3">Demo Talep Et <i class="ri-arrow-right-line ms-1"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- ── Footer ──────────────────────────────────────────────── --}}
<footer class="lp-footer py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-6">
                <a class="fs-24 fw-bold text-white text-decoration-none" href="#">reser<span style="color:var(--lp-primary-2)">up</span></a>
                <p class="lp-muted mb-0 mt-2 fs-14">İşletmeniz için akıllı randevu sistemi — Kıbrıs 🇨🇾</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex gap-4 justify-content-md-end justify-content-start mb-2">
                    <a href="#ozellikler" class="lp-muted text-decoration-none fs-14">Özellikler</a>
                    <a href="#fiyatlar" class="lp-muted text-decoration-none fs-14">Fiyatlar</a>
                    <a href="{{ route('login') }}" class="lp-muted text-decoration-none fs-14">Giriş Yap</a>
                </div>
                <p class="lp-muted fs-13 mb-0">© {{ date('Y') }} Reserup. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </div>
</footer>

@vite(['resources/js/app.js'])
<script>
    // Body sonunda calisir; DOM hazir oldugundan dogrudan baslatilir.
    (function () {
        // Navbar scroll durumu
        const nav = document.getElementById('lp-nav');
        const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 24);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        // Scroll reveal
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        document.querySelectorAll('.reveal').forEach((el) => io.observe(el));

        // Sayaclar
        const counterIo = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                counterIo.unobserve(entry.target);

                const el = entry.target;
                const target = parseInt(el.dataset.count, 10);
                const duration = 1200;
                const start = performance.now();

                const tick = (now) => {
                    const p = Math.min((now - start) / duration, 1);
                    el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
                    if (p < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            });
        }, { threshold: 0.6 });
        document.querySelectorAll('[data-count]').forEach((el) => counterIo.observe(el));

        // Feature kartlarinda imleci takip eden isik
        document.querySelectorAll('.feat-card').forEach((card) => {
            card.addEventListener('pointermove', (e) => {
                const rect = card.getBoundingClientRect();
                card.style.setProperty('--mx', (e.clientX - rect.left) + 'px');
                card.style.setProperty('--my', (e.clientY - rect.top) + 'px');
            });
        });
    })();
</script>
</body>
</html>
