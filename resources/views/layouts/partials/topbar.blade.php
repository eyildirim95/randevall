@php
    $user = auth()->user();
    $impersonating = session('impersonating_business_id') && $user?->isSuperAdmin();
    // Tenant panel baglami: super admin sayfalarindaki $business degiskeniyle karismasin
    $inPanel = isset($business) && $business->exists && request()->routeIs('panel.*');
@endphp
<header>
    <div class="topbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="topbar-item">
                        <button type="button" class="button-toggle-menu topbar-button">
                            <i class="ri-menu-2-line fs-24"></i>
                        </button>
                    </div>

                    @if($impersonating && $inPanel)
                        <div class="topbar-item d-none d-md-flex">
                            <form method="POST" action="{{ route('admin.impersonate.stop') }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="ri-spy-line me-1"></i>{{ $business->name }} — Destek modundan çık
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-1">
                    {{-- Acik/Koyu mod --}}
                    <div class="topbar-item">
                        <button type="button" class="topbar-button" id="light-dark-mode">
                            <i class="ri-moon-line fs-24 light-mode"></i>
                            <i class="ri-sun-line fs-24 dark-mode"></i>
                        </button>
                    </div>

                    {{-- Tam ekran --}}
                    <div class="dropdown topbar-item d-none d-lg-flex">
                        <button type="button" class="topbar-button" data-toggle="fullscreen">
                            <i class="ri-fullscreen-line fs-24 fullscreen"></i>
                            <i class="ri-fullscreen-exit-line fs-24 quit-fullscreen"></i>
                        </button>
                    </div>

                    {{-- Bildirimler (tenant panelinde) --}}
                    @if($inPanel)
                        @php
                            $panelNotifications = \App\Models\PanelNotification::query()->latest()->limit(8)->get();
                            $unreadCount = \App\Models\PanelNotification::query()->unread()->count();
                        @endphp
                        <div class="dropdown topbar-item">
                            <button type="button" class="topbar-button position-relative" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ri-notification-3-line fs-24"></i>
                                @if($unreadCount > 0)
                                    <span class="position-absolute topbar-badge fs-10 translate-middle badge bg-danger rounded-pill">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                                @endif
                            </button>
                            <div class="dropdown-menu py-0 dropdown-lg dropdown-menu-end">
                                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 fs-16 fw-semibold">Bildirimler</h6>
                                    @if($unreadCount > 0)
                                        <form method="POST" action="{{ route('panel.notifications.read', $business) }}">
                                            @csrf
                                            <button class="btn btn-link btn-sm text-decoration-underline p-0">Tümünü okundu say</button>
                                        </form>
                                    @endif
                                </div>
                                <div data-simplebar style="max-height: 300px;">
                                    @forelse($panelNotifications as $notification)
                                        <a href="{{ $notification->url ?? '#' }}" class="dropdown-item py-2 border-bottom text-wrap {{ $notification->read_at ? 'opacity-75' : 'fw-medium' }}">
                                            <div class="d-flex gap-2 align-items-start">
                                                <i class="{{ $notification->icon() }} fs-18 flex-shrink-0"></i>
                                                <div>
                                                    <div class="fs-13">{{ $notification->title }}</div>
                                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="text-center text-muted py-4 mb-0">Bildirim yok.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Rezervasyon linki (tenant panelinde) --}}
                    @if($inPanel)
                        <div class="topbar-item d-none d-md-flex">
                            <a href="{{ route('booking.show', $business) }}" target="_blank" class="topbar-button" title="Online rezervasyon sayfası">
                                <i class="ri-external-link-line fs-24"></i>
                            </a>
                        </div>
                    @endif

                    {{-- Kullanici --}}
                    <div class="dropdown topbar-item">
                        <a type="button" class="topbar-button" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="d-flex align-items-center">
                                <span class="avatar-sm">
                                    <span class="avatar-title bg-soft-primary text-primary rounded-circle fw-bold">
                                        {{ mb_strtoupper(mb_substr($user?->name ?? '?', 0, 1)) }}
                                    </span>
                                </span>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <h6 class="dropdown-header">Merhaba {{ $user?->name }}</h6>

                            @if($user?->isSuperAdmin())
                                <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                    <i class="ri-shield-star-line align-middle me-2 fs-18"></i><span class="align-middle">Süper Admin</span>
                                </a>
                            @endif

                            @foreach($user?->businesses ?? [] as $userBusiness)
                                <a class="dropdown-item" href="{{ route('panel.dashboard', $userBusiness) }}">
                                    <i class="ri-store-2-line align-middle me-2 fs-18"></i><span class="align-middle">{{ $userBusiness->name }}</span>
                                </a>
                            @endforeach

                            <div class="dropdown-divider my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="ri-logout-box-line align-middle me-2 fs-18"></i><span class="align-middle">Çıkış Yap</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
