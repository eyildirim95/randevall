@extends('layouts.vertical', ['title' => 'Genel Bakış', 'subTitle' => $business->name])

@section('content')

    {{-- Istatistik kartlari --}}
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="card-title mb-1">Bugünkü Randevular</h4>
                            <p class="text-muted fs-13 mb-0">{{ now()->translatedFormat('d F Y') }}</p>
                        </div>
                        <div class="avatar-md bg-soft-primary rounded flex-centered">
                            <i class="ri-calendar-check-line fs-28 text-primary"></i>
                        </div>
                    </div>
                    <h2 class="mt-3 mb-0 fw-bold">{{ $stats['today_count'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="card-title mb-1">Onay Bekleyen</h4>
                            <p class="text-muted fs-13 mb-0">Online talepler</p>
                        </div>
                        <div class="avatar-md bg-soft-warning rounded flex-centered">
                            <i class="ri-time-line fs-28 text-warning"></i>
                        </div>
                    </div>
                    <h2 class="mt-3 mb-0 fw-bold">{{ $stats['pending_count'] }}</h2>
                </div>
            </div>
        </div>
        @if($canViewFinance)
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="card-title mb-1">Bu Ay Gelir</h4>
                            <p class="text-muted fs-13 mb-0">Net: {{ number_format($stats['month_income'] - $stats['month_expense'], 2, ',', '.') }} {{ $business->currency }}</p>
                        </div>
                        <div class="avatar-md bg-soft-success rounded flex-centered">
                            <i class="ri-wallet-3-line fs-28 text-success"></i>
                        </div>
                    </div>
                    <h2 class="mt-3 mb-0 fw-bold">{{ number_format($stats['month_income'], 2, ',', '.') }} <small class="fs-14">{{ $business->currency }}</small></h2>
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="card-title mb-1">Müşteriler</h4>
                            <p class="text-muted fs-13 mb-0">Bu ay +{{ $stats['month_new_customers'] }} yeni</p>
                        </div>
                        <div class="avatar-md bg-soft-info rounded flex-centered">
                            <i class="ri-group-line fs-28 text-info"></i>
                        </div>
                    </div>
                    <h2 class="mt-3 mb-0 fw-bold">{{ $stats['customer_count'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Haftalik grafik --}}
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Son 7 Gün Randevu Yoğunluğu</h4>
                    <a href="{{ route('panel.reports.index', $business) }}" class="btn btn-sm btn-soft-primary">Tüm Raporlar</a>
                </div>
                <div class="card-body">
                    <div id="week-chart"
                         data-labels='@json($weekSeries->pluck('label'))'
                         data-values='@json($weekSeries->pluck('count'))'></div>
                </div>
            </div>

            {{-- Bugunun randevulari --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Bugünün Randevuları</h4>
                    <a href="{{ route('panel.calendar', $business) }}" class="btn btn-sm btn-soft-primary">Takvimi Aç</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Saat</th>
                            <th>Müşteri</th>
                            <th>Hizmet</th>
                            <th>Personel</th>
                            <th>Durum</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($todaysAppointments as $appointment)
                            <tr>
                                <td class="fw-semibold">{{ $appointment->starts_at->format('H:i') }}</td>
                                <td>
                                    <a href="{{ route('panel.appointments.show', [$business, $appointment]) }}">
                                        {{ $appointment->customer->name }}
                                    </a>
                                </td>
                                <td>{{ $appointment->service?->name ?? '—' }}</td>
                                <td>{{ $appointment->staff?->name ?? '—' }}</td>
                                <td><span class="badge bg-soft-{{ $appointment->status->color() }} text-{{ $appointment->status->color() }}">{{ $appointment->status->label() }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Bugün için randevu yok.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            {{-- Dogum gunleri --}}
            @if($birthdayCustomers->isNotEmpty())
                <div class="card border border-warning">
                    <div class="card-body">
                        <h4 class="card-title"><i class="ri-cake-2-line text-warning me-1"></i> Bugün Doğum Günü 🎉</h4>
                        @foreach($birthdayCustomers as $customer)
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span>{{ $customer->name }}</span>
                                <a href="{{ route('panel.customers.show', [$business, $customer]) }}" class="btn btn-sm btn-soft-warning">Profil</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Yaklasan randevular --}}
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Yaklaşan Randevular</h4>
                </div>
                <div class="card-body py-2">
                    @forelse($upcoming as $appointment)
                        <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-primary text-primary rounded fs-12 fw-bold">
                                    {{ $appointment->starts_at->format('d.m') }}
                                </span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-medium text-truncate">{{ $appointment->customer->name }}</div>
                                <small class="text-muted">{{ $appointment->starts_at->format('H:i') }} · {{ $appointment->service?->name }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center my-3">Yaklaşan randevu yok.</p>
                    @endforelse
                </div>
            </div>

            {{-- Sabitli notlar --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Sabitlenmiş Notlar</h4>
                    <a href="{{ route('panel.notes.index', $business) }}" class="btn btn-sm btn-soft-primary">Tümü</a>
                </div>
                <div class="card-body py-2">
                    @forelse($pinnedNotes as $note)
                        <div class="alert alert-{{ $note->color }} py-2 px-3 mb-2">
                            @if($note->title)<strong>{{ $note->title }}:</strong>@endif
                            {{ Str::limit($note->content, 100) }}
                        </div>
                    @empty
                        <p class="text-muted text-center my-3">Sabitlenmiş not yok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script-bottom')
    @vite(['resources/js/pages/panel-dashboard.js'])
@endsection
