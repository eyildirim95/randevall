@extends('layouts.auth', ['title' => 'Değerlendirme — '.$business->name])

@section('content')
    <div class="col-xl-5 col-lg-7">
        <div class="card auth-card">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <h4 class="mb-1">Deneyiminizi Değerlendirin</h4>
                    <p class="text-muted mb-0">
                        {{ $business->name }} · {{ $appointment->service?->name }} · {{ $appointment->starts_at->translatedFormat('d F Y') }}
                    </p>
                </div>

                @include('layouts.partials.alerts')

                <form method="POST" action="{{ route('appointment.public.rate.store', $appointment->public_token) }}">
                    @csrf

                    <div class="text-center mb-3">
                        <div class="d-flex justify-content-center gap-1 fs-32" id="star-row">
                            @foreach(range(1, 5) as $star)
                                <label style="cursor:pointer" title="{{ $star }} yıldız">
                                    <input type="radio" name="rating" value="{{ $star }}" class="d-none" required>
                                    <i class="ri-star-line text-warning star-icon" data-star="{{ $star }}"></i>
                                </label>
                            @endforeach
                        </div>
                        <small class="text-muted">Yıldıza dokunarak puan verin</small>
                    </div>

                    <div class="mb-3">
                        <textarea name="rating_comment" class="form-control" rows="3" maxlength="500"
                                  placeholder="Yorumunuz (isteğe bağlı)">{{ old('rating_comment') }}</textarea>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary py-2">Değerlendirmeyi Gönder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stars = document.querySelectorAll('.star-icon');
            const paint = (upTo) => stars.forEach((s) => {
                s.className = 'ri-star-' + (parseInt(s.dataset.star, 10) <= upTo ? 'fill' : 'line') + ' text-warning star-icon';
            });
            stars.forEach((star) => {
                star.parentElement.addEventListener('click', () => paint(parseInt(star.dataset.star, 10)));
            });
        });
    </script>
@endsection
