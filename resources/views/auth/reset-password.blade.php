@extends('layouts.auth', ['title' => 'Yeni Şifre Belirle'])

@section('content')
    <div class="col-xl-5">
        <div class="card auth-card">
            <div class="card-body px-3 py-5">
                <div class="mx-auto mb-4 text-center auth-logo">
                    @include('layouts.partials.brand-logo', ['href' => route('landing'), 'class' => 'fs-28', 'size' => 34])
                </div>

                <h2 class="fw-bold text-uppercase text-center fs-18">Yeni Şifre Belirle</h2>

                <div class="px-4 mt-4">
                    <form method="POST" action="{{ route('password.update') }}" class="authentication-form">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        @foreach ($errors->all() as $error)
                            <p class="text-danger mb-3">{{ $error }}</p>
                        @endforeach

                        <div class="mb-3">
                            <label class="form-label" for="email">E-posta</label>
                            <input type="email" id="email" name="email" required
                                   class="form-control bg-light bg-opacity-50 border-light py-2"
                                   value="{{ old('email', $email) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Yeni Şifre</label>
                            <input type="password" id="password" name="password" required minlength="8"
                                   class="form-control bg-light bg-opacity-50 border-light py-2"
                                   placeholder="En az 8 karakter">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password_confirmation">Yeni Şifre (Tekrar)</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                                   class="form-control bg-light bg-opacity-50 border-light py-2">
                        </div>

                        <div class="mb-1 text-center d-grid">
                            <button class="btn btn-primary py-2 fw-medium" type="submit">Şifreyi Güncelle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
