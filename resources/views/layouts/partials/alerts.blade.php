{{-- Oturum flash mesajlari --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ri-error-warning-line me-1"></i>
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
    </div>
@endif

@auth
    @if (! auth()->user()->hasVerifiedEmail())
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center flex-wrap gap-2" role="alert">
            <i class="ri-mail-check-line me-1"></i>
            <span class="me-auto">E-posta adresinizi henüz doğrulamadınız. Güvenlik için lütfen doğrulayın.</span>
            <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-warning">Doğrulama e-postasını gönder</button>
            </form>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    @endif
@endauth
