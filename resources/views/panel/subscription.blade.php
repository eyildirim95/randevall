@extends('layouts.vertical', ['title' => 'Abonelik', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Mevcut durum --}}
            <div class="card">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h4 class="mb-1">Mevcut Plan: <span class="text-primary">{{ $business->plan?->name ?? 'Plan yok' }}</span></h4>
                        <p class="text-muted mb-0">
                            @if($business->onTrial())
                                Deneme süreniz {{ $business->trial_ends_at->translatedFormat('d F Y') }} tarihinde bitiyor.
                            @elseif($business->plan_expires_at)
                                Plan bitiş: {{ $business->plan_expires_at->translatedFormat('d F Y') }}
                                @unless($business->hasActivePlan()) — <span class="text-danger fw-bold">Süresi dolmuş</span> @endunless
                            @else
                                Aktif abonelik bulunmuyor.
                            @endif
                        </p>
                    </div>
                    @if($business->onTrial())
                        <span class="badge bg-soft-warning text-warning fs-13">Deneme Sürümü</span>
                    @elseif($business->hasActivePlan())
                        <span class="badge bg-soft-success text-success fs-13">Aktif</span>
                    @else
                        <span class="badge bg-soft-danger text-danger fs-13">Pasif</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Planlar --}}
    <div class="row">
        @foreach($plans as $plan)
            <div class="col-md-4">
                <div class="card {{ $plan->is_featured ? 'border-primary border-2' : '' }}">
                    @if($plan->is_featured)
                        <div class="card-header bg-primary text-white text-center py-1"><small>EN POPÜLER</small></div>
                    @endif
                    <div class="card-body text-center">
                        <h4>{{ $plan->name }}</h4>
                        <p class="text-muted">{{ $plan->description }}</p>
                        <h2 class="fw-bold">{{ number_format($plan->price_monthly, 0, ',', '.') }} <small class="fs-14 text-muted">{{ $plan->currency }}/ay</small></h2>
                        <p class="text-muted fs-13">veya yıllık {{ number_format($plan->price_yearly, 0, ',', '.') }} {{ $plan->currency }}</p>

                        <ul class="list-unstyled text-start mb-3">
                            <li class="py-1"><i class="ri-check-line text-success me-1"></i>{{ $plan->max_staff > 0 ? $plan->max_staff.' personel' : 'Sınırsız personel' }}</li>
                            <li class="py-1"><i class="ri-check-line text-success me-1"></i>{{ $plan->max_appointments_per_month > 0 ? $plan->max_appointments_per_month.' randevu/ay' : 'Sınırsız randevu' }}</li>
                            @foreach($plan->features ?? [] as $feature)
                                <li class="py-1"><i class="ri-check-line text-success me-1"></i>{{ $feature }}</li>
                            @endforeach
                        </ul>

                        <div class="d-grid gap-2">
                            <a href="{{ route('panel.subscription.index', [$business, 'plan' => $plan->slug, 'period' => 'monthly', 'provider' => 'paytr']) }}" class="btn btn-primary">
                                Aylık Öde (Kart)
                            </a>
                            <a href="{{ route('panel.subscription.index', [$business, 'plan' => $plan->slug, 'period' => 'monthly', 'provider' => 'manual']) }}" class="btn btn-soft-secondary">
                                Havale/EFT ile Öde
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Odeme gecmisi --}}
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Ödeme Geçmişi</h4></div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Tarih</th>
                            <th>Tutar</th>
                            <th>Yöntem</th>
                            <th>Durum</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                <td class="fw-semibold">{{ number_format($payment->amount, 2, ',', '.') }} {{ $payment->currency }}</td>
                                <td>{{ ['manual' => 'Havale/EFT', 'paytr' => 'Kart (PayTR)'][$payment->provider] ?? $payment->provider }}</td>
                                <td>
                                    @php
                                        $statusMap = ['paid' => ['success', 'Ödendi'], 'pending' => ['warning', 'Bekliyor'], 'failed' => ['danger', 'Başarısız'], 'refunded' => ['secondary', 'İade']];
                                        [$color, $label] = $statusMap[$payment->status] ?? ['secondary', $payment->status];
                                    @endphp
                                    <span class="badge bg-soft-{{ $color }} text-{{ $color }}">{{ $label }}</span>
                                    @if($payment->status === 'pending')
                                        <a href="{{ route('payment.show', $payment->token) }}" class="btn btn-sm btn-soft-primary ms-1">Ödemeye Git</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Ödeme kaydı yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
