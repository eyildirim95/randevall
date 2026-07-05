@extends('layouts.auth', ['title' => 'Giriş Yap'])

@section('content')
    <div class="col-xl-5">

        <div class="card auth-card">
            <div class="card-body px-3 py-5">
                <div class="mx-auto mb-4 text-center auth-logo">
                    <a href="{{ route('landing') }}" class="fw-bold fs-28 text-decoration-none">
                        <span class="text-dark">Boo</span><span class="text-primary">Kıbrıs</span>
                    </a>
                </div>

                <h2 class="fw-bold text-uppercase text-center fs-18">Giriş Yap</h2>
                <p class="text-muted text-center mt-1 mb-4">Panele erişmek için e-posta adresinizi ve şifrenizi girin.</p>

                <div class="px-4">
                    <form method="POST" action="{{ route('login.attempt') }}" class="authentication-form">
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
                        <div class="mb-3">
                            <a href="{{ route('password.request') }}" class="float-end text-muted text-unline-dashed ms-1">Şifremi unuttum</a>
                            <label class="form-label" for="password">Şifre</label>
                            <input type="password" id="password" name="password" required
                                   class="form-control bg-light bg-opacity-50 border-light py-2"
                                   placeholder="Şifreniz">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                                <label class="form-check-label" for="remember">Beni hatırla</label>
                            </div>
                        </div>

                        <div class="mb-1 text-center d-grid">
                            <button class="btn btn-primary py-2 fw-medium" type="submit">Giriş Yap</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <p class="mb-0 text-center text-muted">Henüz üye değil misiniz?
            <a href="{{ route('landing') }}#demo" class="text-reset text-unline-dashed fw-bold ms-1">Demo talep edin</a>
        </p>
    </div>
@endsection
