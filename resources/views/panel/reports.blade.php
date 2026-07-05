@extends('layouts.vertical', ['title' => 'Raporlar', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label mb-1">Başlangıç</label>
                            <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">Bitiş</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-primary">Uygula</button>
                            <a href="{{ route('panel.reports.export', [$business, 'from' => $from, 'to' => $to]) }}" class="btn btn-sm btn-soft-success">
                                <i class="ri-file-excel-2-line me-1"></i>Gelir-Gider CSV
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Ozet --}}
    <div class="row">
        @php
            $cards = [
                ['label' => 'Gelir', 'value' => number_format($summary['income'], 0, ',', '.').' '.$business->currency, 'icon' => 'ri-arrow-up-circle-line', 'color' => 'success'],
                ['label' => 'Gider', 'value' => number_format($summary['expense'], 0, ',', '.').' '.$business->currency, 'icon' => 'ri-arrow-down-circle-line', 'color' => 'danger'],
                ['label' => 'Net', 'value' => number_format($summary['net'], 0, ',', '.').' '.$business->currency, 'icon' => 'ri-scales-3-line', 'color' => 'primary'],
                ['label' => 'Randevu', 'value' => $summary['appointments_total'], 'icon' => 'ri-calendar-check-line', 'color' => 'info'],
                ['label' => 'Tamamlanma', 'value' => '%'.$summary['completion_rate'], 'icon' => 'ri-checkbox-circle-line', 'color' => 'success'],
                ['label' => 'Yeni Müşteri', 'value' => $summary['new_customers'], 'icon' => 'ri-user-add-line', 'color' => 'warning'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="col-md-4 col-xl-2">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="{{ $card['icon'] }} fs-24 text-{{ $card['color'] }}"></i>
                        <h4 class="mb-0 mt-1">{{ $card['value'] }}</h4>
                        <small class="text-muted">{{ $card['label'] }}</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Günlük Randevu &amp; Gelir</h4></div>
                <div class="card-body">
                    <div id="daily-chart"
                         data-labels='@json($dailySeries->pluck('label'))'
                         data-appointments='@json($dailySeries->pluck('appointments'))'
                         data-income='@json($dailySeries->pluck('income'))'></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Personel Performansı</h4></div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Personel</th>
                            <th>Randevu</th>
                            <th>Tamamlanan</th>
                            <th>Puan</th>
                            <th>Ciro</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($staffPerformance as $row)
                            <tr>
                                <td>
                                    <span class="badge me-1" style="background-color: {{ $row->staff?->color }}">&nbsp;</span>
                                    {{ $row->staff?->name ?? 'Silinmiş personel' }}
                                </td>
                                <td>{{ $row->total }}</td>
                                <td>{{ $row->completed }}</td>
                                <td>
                                    @if($row->avg_rating)
                                        <span class="text-warning">★</span> {{ number_format($row->avg_rating, 1) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ number_format($row->revenue, 2, ',', '.') }} {{ $business->currency }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Veri yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Hizmet Dağılımı</h4></div>
                <div class="card-body">
                    <div id="service-chart"
                         data-labels='@json($serviceBreakdown->map(fn($r) => $r->service?->name ?? "Diğer"))'
                         data-values='@json($serviceBreakdown->pluck('total'))'></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Randevu Kaynağı</h4></div>
                <div class="card-body">
                    <div class="d-flex justify-content-around text-center">
                        <div>
                            <h3 class="mb-0">{{ $sourceBreakdown['panel'] }}</h3>
                            <small class="text-muted">Panelden</small>
                        </div>
                        <div>
                            <h3 class="mb-0 text-info">{{ $sourceBreakdown['online'] }}</h3>
                            <small class="text-muted">Online Link</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">En İyi Müşteriler</h4></div>
                <div class="card-body py-2">
                    @forelse($topCustomers as $row)
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <div>
                                <a href="{{ route('panel.customers.show', [$business, $row->customer_id]) }}" class="fw-medium">{{ $row->customer?->name ?? '—' }}</a>
                                <small class="text-muted d-block">{{ $row->total }} randevu</small>
                            </div>
                            <span class="fw-semibold">{{ number_format($row->spent, 0, ',', '.') }} {{ $business->currency }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center my-3">Veri yok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script-bottom')
    @vite(['resources/js/pages/panel-reports.js'])
@endsection
