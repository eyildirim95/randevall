@extends('layouts.auth', ['title' => 'E-posta Doğrulama'])

@section('content')
    <div class="col-xl-5">
        <div class="card auth-card">
            <div class="card-body px-3 py-5">
                <div class="mx-auto mb-4 text-center auth-logo">
                    <a href="{{ route('landing') }}" class="fw-bold fs-28 text-decoration-none">
                        <span class="text-dark">Boo</span><span class="text-primary">Kıbrıs</span>
                    </a>
                </div>

                <h2 class="fw-bold text-uppercase text-center fs-18">E-posta Doğrulama</h2>
                <p class="text-muted text-center mt-1 mb-4">
                    <strong>{{ auth()->user()->email }}</strong> adresine bir doğrulama bağlantısı gönderdik.
                    Lütfen gelen kutunuzu kontrol edin.
                </p>

                <div class="px-4">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <div class="mb-3 d-grid">
                            <button class="btn btn-primary py-2 fw-medium" type="submit">Bağlantıyı Tekrar Gönder</button>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between mt-3">
                        @php($business = auth()->user()->businesses()->first())
                        @if ($business)
                            <a href="{{ route('panel.dashboard', $business) }}" class="text-muted">Panele git →</a>
                        @else
                            <span></span>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-link p-0 text-muted text-decoration-none" type="submit">Çıkış yap</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
