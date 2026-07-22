@extends('layouts.vertical', ['title' => 'Platform Genel Bakış', 'subTitle' => 'Süper Admin'])

@section('content')
    {{-- Istatistikler --}}
    <div class="row">
        @php
            $cards = [
                ['label' => 'Toplam İşletme', 'value' => $stats['business_count'], 'sub' => $stats['active_businesses'].' aktif', 'icon' => 'ri-store-2-line', 'color' => 'primary'],
                ['label' => 'Askıdaki İşletme', 'value' => $stats['suspended_businesses'], 'sub' => 'işletme', 'icon' => 'ri-pause-circle-line', 'color' => 'warning'],
                ['label' => 'Bu Ay Ciro', 'value' => number_format($stats['month_revenue'], 0, ',', '.').' ₺', 'sub' => 'onaylı ödemeler', 'icon' => 'ri-money-dollar-circle-line', 'color' => 'success'],
                ['label' => 'Bu Ay Randevu', 'value' => $stats['month_appointments'], 'sub' => 'toplam '.$stats['total_appointments'], 'icon' => 'ri-calendar-check-line', 'color' => 'info'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="card-title mb-1">{{ $card['label'] }}</h4>
                                <p class="text-muted fs-13 mb-0">{{ $card['sub'] }}</p>
                            </div>
                            <div class="avatar-md bg-soft-{{ $card['color'] }} rounded flex-centered">
                                <i class="{{ $card['icon'] }} fs-28 text-{{ $card['color'] }}"></i>
                            </div>
                        </div>
                        <h2 class="mt-3 mb-0 fw-bold">{{ $card['value'] }}</h2>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Aylık Büyüme (Son 6 Ay)</h4></div>
                <div class="card-body">
                    <div id="growth-chart"
                         data-labels='@json($growthSeries->pluck('label'))'
                         data-businesses='@json($growthSeries->pluck('businesses'))'
                         data-revenue='@json($growthSeries->pluck('revenue'))'></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Son Eklenen İşletmeler</h4>
                    <a href="{{ route('admin.businesses.index') }}" class="btn btn-sm btn-soft-primary">Tümü</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr><th>İşletme</th><th>Plan</th><th>Kayıt</th><th>Durum</th></tr>
                        </thead>
                        <tbody>
                        @foreach($recentBusinesses as $b)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.businesses.show', $b) }}" class="fw-medium">{{ $b->name }}</a>
                                    <small class="text-muted d-block">/{{ $b->slug }}</small>
                                </td>
                                <td>{{ $b->plan?->name ?? '—' }}</td>
                                <td>{{ $b->created_at->format('d.m.Y') }}</td>
                                <td>
                                    @if($b->isSuspended())
                                        <span class="badge bg-soft-danger text-danger">Askıda</span>
                                    @elseif($b->onTrial())
                                        <span class="badge bg-soft-warning text-warning">Deneme</span>
                                    @else
                                        <span class="badge bg-soft-success text-success">Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Bu Ay Gönderilen Mesajlar</h4>
                </div>
                <div class="card-body text-center py-4">
                    <h2 class="fw-bold mb-1">{{ number_format($stats['month_messages'], 0, ',', '.') }}</h2>
                    <p class="text-muted mb-0 fs-13">WhatsApp ve e-posta bildirimleri</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Bekleyen Ödemeler</h4>
                    <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="btn btn-sm btn-soft-primary">Tümü</a>
                </div>
                <div class="card-body py-2">
                    @forelse($pendingPayments as $payment)
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <div>
                                <div class="fw-medium">{{ $payment->business?->name }}</div>
                                <small class="text-muted">{{ $payment->created_at->format('d.m.Y') }}</small>
                            </div>
                            <span class="fw-semibold">{{ number_format($payment->amount, 0, ',', '.') }} {{ $payment->currency }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center my-3">Bekleyen ödeme yok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script-bottom')
    @vite(['resources/js/pages/superadmin-dashboard.js'])
@endsection
