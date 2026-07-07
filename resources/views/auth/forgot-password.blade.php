@extends('layouts.auth', ['title' => 'Şifremi Unuttum'])

@section('content')
    <div class="col-xl-5">
        <div class="card auth-card">
            <div class="card-body px-3 py-5">
                <div class="mx-auto mb-4 text-center auth-logo">
                    <a href="{{ route('landing') }}" class="fw-bold fs-28 text-decoration-none">
                        <img src="{{ asset('favicon-32.png') }}" height="34" class="me-2 align-middle" alt=""><span class="text-dark">Boo</span><span class="text-primary">Kıbrıs</span>
                    </a>
                </div>

                <h2 class="fw-bold text-uppercase text-center fs-18">Şifre Sıfırlama</h2>
                <p class="text-muted text-center mt-1 mb-4">E-posta adresinizi girin; size sıfırlama bağlantısı gönderelim.</p>

                <div class="px-4">
                    <form method="POST" action="{{ route('password.email') }}" class="authentication-form">
                        @csrf

                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        @foreach ($errors->all() as $error)
                            <p class="text-danger mb-3">{{ $error }}</p>
                        @endforeach

                        <div class="mb-3">
                            <label class="form-label" for="email">E-posta</label>
                            <input type="email" id="email" name="email" required autofocus
                                   class="form-control bg-light bg-opacity-50 border-light py-2"
                                   placeholder="ornek@isletme.com" value="{{ old('email') }}">
                        </div>

                        <div class="mb-1 text-center d-grid">
                            <button class="btn btn-primary py-2 fw-medium" type="submit">Bağlantı Gönder</button>
                        </div>
                    </form>

                    <p class="mt-3 text-center mb-0">
                        <a href="{{ route('login') }}" class="text-muted">← Girişe dön</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
