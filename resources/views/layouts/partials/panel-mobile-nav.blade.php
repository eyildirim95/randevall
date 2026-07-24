@php
    $calendarNewUrl = route('panel.calendar', $business);
@endphp

<nav class="panel-mobile-nav d-lg-none" aria-label="Mobil panel menüsü">
    <a href="{{ route('panel.dashboard', $business) }}" class="panel-mobile-nav__item @if(request()->routeIs('panel.dashboard')) is-active @endif">
        <i class="ri-home-5-line"></i>
        <span>Ana Sayfa</span>
    </a>
    <a href="{{ route('panel.calendar', $business) }}" class="panel-mobile-nav__item @if(request()->routeIs('panel.calendar*')) is-active @endif">
        <i class="ri-calendar-2-line"></i>
        <span>Takvim</span>
    </a>
    <a href="{{ $calendarNewUrl }}" class="panel-mobile-nav__item panel-mobile-nav__item--primary" id="panel-mobile-new-appointment" title="Yeni randevu">
        <i class="ri-add-line"></i>
        <span>Randevu</span>
    </a>
    <a href="{{ route('panel.customers.index', $business) }}" class="panel-mobile-nav__item @if(request()->routeIs('panel.customers*')) is-active @endif">
        <i class="ri-group-line"></i>
        <span>Müşteriler</span>
    </a>
    <button type="button" class="panel-mobile-nav__item" id="panel-mobile-menu-toggle" aria-label="Menüyü aç">
        <i class="ri-menu-line"></i>
        <span>Menü</span>
    </button>
</nav>

<div class="panel-pwa-install d-none" id="panel-pwa-install" role="region" aria-label="Uygulamayı yükle">
    <div class="panel-pwa-install__inner">
        <div>
            <strong>{{ $business->name }}</strong>
            <small class="d-block text-muted">Ana ekrana ekleyerek daha hızlı erişin</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary" id="panel-pwa-install-btn">Yükle</button>
            <button type="button" class="btn btn-sm btn-light" id="panel-pwa-install-dismiss" aria-label="Kapat">&times;</button>
        </div>
    </div>
</div>
