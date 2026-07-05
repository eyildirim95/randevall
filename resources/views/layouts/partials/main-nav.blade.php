@php
    $user = auth()->user();
    // Tenant panel baglami: super admin sayfalarindaki $business degiskeniyle karismasin
    $isPanel = isset($business) && $business->exists && request()->routeIs('panel.*');
    $canManage = $isPanel && ($user?->canManage($business) || ($user?->isSuperAdmin() && session('impersonating_business_id') === $business->id));
    $homeUrl = $isPanel ? route('panel.dashboard', $business) : route('admin.dashboard');
@endphp
<div class="main-nav">
    <div class="logo-box">
        <a href="{{ $homeUrl }}" class="logo-dark">
            <span class="logo-sm fw-bold fs-22 text-primary">R</span>
            <span class="logo-lg fw-bold fs-22 text-dark">Boo<span class="text-primary">Kıbrıs</span></span>
        </a>
        <a href="{{ $homeUrl }}" class="logo-light">
            <span class="logo-sm fw-bold fs-22 text-primary">R</span>
            <span class="logo-lg fw-bold fs-22 text-white">Boo<span class="text-primary">Kıbrıs</span></span>
        </a>
    </div>

    <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
        <i class="ri-menu-2-line fs-24 button-sm-hover-icon"></i>
    </button>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">

            @if($isPanel)
                <li class="menu-title">{{ $business->name }}</li>

                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('panel.dashboard')) active @endif" href="{{ route('panel.dashboard', $business) }}">
                        <span class="nav-icon"><i class="ri-dashboard-2-line"></i></span>
                        <span class="nav-text">Genel Bakış</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('panel.calendar*')) active @endif" href="{{ route('panel.calendar', $business) }}">
                        <span class="nav-icon"><i class="ri-calendar-2-line"></i></span>
                        <span class="nav-text">Takvim</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('panel.appointments*')) active @endif" href="{{ route('panel.appointments.index', $business) }}">
                        <span class="nav-icon"><i class="ri-calendar-check-line"></i></span>
                        <span class="nav-text">Randevular</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('panel.customers*')) active @endif" href="{{ route('panel.customers.index', $business) }}">
                        <span class="nav-icon"><i class="ri-group-line"></i></span>
                        <span class="nav-text">Müşteriler</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('panel.notes*')) active @endif" href="{{ route('panel.notes.index', $business) }}">
                        <span class="nav-icon"><i class="ri-sticky-note-line"></i></span>
                        <span class="nav-text">Notlar</span>
                    </a>
                </li>
                @php $answeredTickets = \App\Models\Ticket::query()->where('status', 'answered')->count(); @endphp
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('panel.tickets*')) active @endif" href="{{ route('panel.tickets.index', $business) }}">
                        <span class="nav-icon"><i class="ri-customer-service-2-line"></i></span>
                        <span class="nav-text">Destek
                            @if($answeredTickets > 0)
                                <span class="badge bg-primary rounded-pill ms-1">{{ $answeredTickets }}</span>
                            @endif
                        </span>
                    </a>
                </li>

                @if($canManage)
                    <li class="menu-title">Yönetim</li>

                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('panel.services*')) active @endif" href="{{ route('panel.services.index', $business) }}">
                            <span class="nav-icon"><i class="ri-scissors-2-line"></i></span>
                            <span class="nav-text">Hizmetler</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('panel.staff*')) active @endif" href="{{ route('panel.staff.index', $business) }}">
                            <span class="nav-icon"><i class="ri-user-star-line"></i></span>
                            <span class="nav-text">Personel</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('panel.working-hours*')) active @endif" href="{{ route('panel.working-hours.edit', $business) }}">
                            <span class="nav-icon"><i class="ri-time-line"></i></span>
                            <span class="nav-text">Çalışma Saatleri</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('panel.closures*')) active @endif" href="{{ route('panel.closures.index', $business) }}">
                            <span class="nav-icon"><i class="ri-lock-2-line"></i></span>
                            <span class="nav-text">Takvim Kapatma</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('panel.waitlist*')) active @endif" href="{{ route('panel.waitlist.index', $business) }}">
                            <span class="nav-icon"><i class="ri-timer-flash-line"></i></span>
                            <span class="nav-text">Bekleme Listesi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('panel.campaigns*')) active @endif" href="{{ route('panel.campaigns.index', $business) }}">
                            <span class="nav-icon"><i class="ri-megaphone-line"></i></span>
                            <span class="nav-text">Kampanyalar</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('panel.transactions*')) active @endif" href="{{ route('panel.transactions.index', $business) }}">
                            <span class="nav-icon"><i class="ri-wallet-3-line"></i></span>
                            <span class="nav-text">Gelir - Gider</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('panel.reports*')) active @endif" href="{{ route('panel.reports.index', $business) }}">
                            <span class="nav-icon"><i class="ri-bar-chart-2-line"></i></span>
                            <span class="nav-text">Raporlar</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('panel.settings*')) active @endif" href="{{ route('panel.settings.edit', $business) }}">
                            <span class="nav-icon"><i class="ri-settings-3-line"></i></span>
                            <span class="nav-text">Ayarlar</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('panel.subscription*')) active @endif" href="{{ route('panel.subscription.index', $business) }}">
                            <span class="nav-icon"><i class="ri-vip-crown-2-line"></i></span>
                            <span class="nav-text">Abonelik</span>
                        </a>
                    </li>
                @endif

            @else
                <li class="menu-title">Süper Admin</li>

                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif" href="{{ route('admin.dashboard') }}">
                        <span class="nav-icon"><i class="ri-dashboard-2-line"></i></span>
                        <span class="nav-text">Genel Bakış</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('admin.businesses*')) active @endif" href="{{ route('admin.businesses.index') }}">
                        <span class="nav-icon"><i class="ri-store-2-line"></i></span>
                        <span class="nav-text">İşletmeler</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('admin.plans*')) active @endif" href="{{ route('admin.plans.index') }}">
                        <span class="nav-icon"><i class="ri-price-tag-3-line"></i></span>
                        <span class="nav-text">Planlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('admin.payments*')) active @endif" href="{{ route('admin.payments.index') }}">
                        <span class="nav-icon"><i class="ri-bank-card-line"></i></span>
                        <span class="nav-text">Ödemeler</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('admin.demo-requests*')) active @endif" href="{{ route('admin.demo-requests.index') }}">
                        <span class="nav-icon"><i class="ri-user-voice-line"></i></span>
                        <span class="nav-text">Demo Talepleri</span>
                    </a>
                </li>
                @php $openTickets = \App\Models\Ticket::query()->where('status', 'open')->count(); @endphp
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('admin.tickets*')) active @endif" href="{{ route('admin.tickets.index') }}">
                        <span class="nav-icon"><i class="ri-customer-service-2-line"></i></span>
                        <span class="nav-text">Destek
                            @if($openTickets > 0)
                                <span class="badge bg-danger rounded-pill ms-1">{{ $openTickets }}</span>
                            @endif
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('admin.announcements*')) active @endif" href="{{ route('admin.announcements.index') }}">
                        <span class="nav-icon"><i class="ri-megaphone-line"></i></span>
                        <span class="nav-text">Duyurular</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('admin.messages*')) active @endif" href="{{ route('admin.messages.index') }}">
                        <span class="nav-icon"><i class="ri-chat-3-line"></i></span>
                        <span class="nav-text">Mesaj Kayıtları</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('admin.reports*')) active @endif" href="{{ route('admin.reports.index') }}">
                        <span class="nav-icon"><i class="ri-bar-chart-2-line"></i></span>
                        <span class="nav-text">Raporlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('admin.settings*')) active @endif" href="{{ route('admin.settings.edit') }}">
                        <span class="nav-icon"><i class="ri-settings-3-line"></i></span>
                        <span class="nav-text">Sistem Ayarları</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
