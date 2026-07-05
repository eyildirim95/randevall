@extends('layouts.auth', ['title' => 'Randevuyu Yeniden Planla — '.$business->name])

@section('content')
    <div class="col-xl-5 col-lg-7">
        <div class="card auth-card">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <h4 class="mb-1">Randevuyu Yeniden Planla</h4>
                    <p class="text-muted mb-0">
                        {{ $business->name }} · {{ $appointment->service?->name }} · {{ $appointment->staff?->name }}
                    </p>
                    <small class="text-muted">Mevcut: {{ $appointment->starts_at->translatedFormat('d F Y l H:i') }}</small>
                </div>

                @include('layouts.partials.alerts')

                <form method="GET" action="{{ route('appointment.public.reschedule', $appointment->public_token) }}" class="d-flex gap-2 mb-3">
                    <input type="date" name="date" class="form-control" required
                           value="{{ $date?->toDateString() }}"
                           min="{{ today()->toDateString() }}"
                           max="{{ today()->addDays($business->max_advance_days)->toDateString() }}">
                    <button class="btn btn-primary flex-shrink-0">Saatleri Göster</button>
                </form>

                @if($date)
                    @if($slots->isEmpty())
                        <div class="alert alert-info text-center mb-3">Bu tarihte uygun saat yok. Başka bir gün deneyin.</div>
                    @else
                        <p class="fw-medium mb-2">{{ $date->translatedFormat('d F Y l') }} için uygun saatler:</p>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach($slots as $slot)
                                <form method="POST" action="{{ route('appointment.public.reschedule.store', $appointment->public_token) }}"
                                      onsubmit="return confirm('Randevunuz {{ $date->format('d.m.Y') }} {{ $slot }} saatine taşınsın mı?')">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                    <input type="hidden" name="time" value="{{ $slot }}">
                                    <button class="btn btn-outline-primary btn-sm">{{ $slot }}</button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                @endif

                <div class="d-grid">
                    <a href="{{ route('appointment.public.show', $appointment->public_token) }}" class="btn btn-light">← Randevuma Dön</a>
                </div>
            </div>
        </div>
    </div>
@endsection
