@extends('layouts.auth', ['title' => 'Randevu Bilgisi — '.$business->name])

@section('content')
    <div class="col-xl-5 col-lg-7">
        <div class="card auth-card">
            <div class="card-body p-4">
                @if(session('booked'))
                    <div class="text-center mb-3">
                        <i class="ri-checkbox-circle-fill text-success" style="font-size: 56px"></i>
                        <h4 class="mt-2">
                            {{ $appointment->status->value === 'pending' ? 'Randevu Talebiniz Alındı!' : 'Randevunuz Onaylandı!' }}
                        </h4>
                        @if($appointment->status->value === 'pending')
                            <p class="text-muted">İşletme onayladığında bilgilendirileceksiniz.</p>
                        @endif
                    </div>
                @endif

                @include('layouts.partials.alerts')

                <div class="text-center mb-3">
                    <h5 class="mb-0">{{ $business->name }}</h5>
                    @if($business->phone)<small class="text-muted">{{ $business->phone }}</small>@endif
                </div>

                <table class="table table-sm">
                    <tr><th class="text-muted">Durum</th>
                        <td><span class="badge bg-{{ $appointment->status->color() }}">{{ $appointment->status->label() }}</span></td></tr>
                    <tr><th class="text-muted">Müşteri</th><td>{{ $appointment->customer->name }}</td></tr>
                    <tr><th class="text-muted">Hizmet</th><td>{{ $appointment->service?->name ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Personel</th><td>{{ $appointment->staff?->name ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Tarih</th><td class="fw-semibold">{{ $appointment->starts_at->translatedFormat('d F Y l') }}</td></tr>
                    <tr><th class="text-muted">Saat</th><td class="fw-semibold">{{ $appointment->starts_at->format('H:i') }} — {{ $appointment->ends_at->format('H:i') }}</td></tr>
                    <tr><th class="text-muted">Ücret</th><td>{{ number_format($appointment->price, 2, ',', '.') }} {{ $business->currency }}</td></tr>
                </table>

                @if($appointment->status->value === 'completed' && ! $appointment->rated_at)
                    <div class="d-grid mb-2">
                        <a href="{{ route('appointment.public.rate', $appointment->public_token) }}" class="btn btn-warning">
                            ⭐ Deneyiminizi Değerlendirin
                        </a>
                    </div>
                @elseif($appointment->rating)
                    <div class="text-center mb-2">
                        <span class="text-warning fs-18">{{ str_repeat('★', $appointment->rating) }}{{ str_repeat('☆', 5 - $appointment->rating) }}</span>
                        <small class="text-muted d-block">Değerlendirmeniz için teşekkürler!</small>
                    </div>
                @endif

                @if($appointment->isCancellable())
                    <div class="d-grid mb-2">
                        <a href="{{ route('appointment.public.reschedule', $appointment->public_token) }}" class="btn btn-primary">
                            <i class="ri-calendar-event-line me-1"></i>Yeniden Planla
                        </a>
                    </div>
                    <form method="POST" action="{{ route('appointment.public.cancel', $appointment->public_token) }}"
                          onsubmit="return confirm('Randevunuzu iptal etmek istediğinize emin misiniz?')" class="d-grid">
                        @csrf
                        <button class="btn btn-outline-danger">Randevuyu İptal Et</button>
                    </form>
                @endif

                <div class="d-grid mt-2">
                    <a href="{{ route('booking.show', $business) }}" class="btn btn-soft-primary">Yeni Randevu Al</a>
                </div>
            </div>
        </div>
    </div>
@endsection
