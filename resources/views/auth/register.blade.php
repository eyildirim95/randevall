@extends('layouts.auth', ['title' => 'Ücretsiz Kayıt'])

@section('content')
    <div class="col-xl-6 col-lg-8">
        <div class="card auth-card">
            <div class="card-body px-3 py-4">
                <div class="mx-auto mb-3 text-center auth-logo">
                    <a href="{{ route('landing') }}" class="fw-bold fs-28 text-decoration-none">
                        <span class="text-dark">reser</span><span class="text-primary">up</span>
                    </a>
                </div>

                <h2 class="fw-bold text-center fs-18">İşletmenizi Ücretsiz Açın</h2>
                <p class="text-muted text-center mt-1 mb-4">14 gün tüm özellikler ücretsiz · Kredi kartı gerekmez</p>

                <div class="px-2 px-md-4">
                    <form method="POST" action="{{ route('register.store') }}" class="row g-3">
                        @csrf
                        <input type="text" name="website" value="" style="display:none" tabindex="-1" autocomplete="off" aria-hidden="true">

                        @foreach ($errors->all() as $error)
                            <div class="col-12 py-0"><p class="text-danger mb-0">{{ $error }}</p></div>
                        @endforeach

                        <div class="col-md-7">
                            <label class="form-label">İşletme Adı <span class="text-danger">*</span></label>
                            <input type="text" id="reg-business-name" name="business_name" class="form-control" required maxlength="150" value="{{ old('business_name') }}" placeholder="Örnek Erkek Kuaförü">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Sektör</label>
                            <select name="sector" class="form-select">
                                <option value="">Seçin...</option>
                                @foreach($sectors as $sector)
                                    <option value="{{ $sector }}" @selected(old('sector') === $sector)>{{ $sector }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Rezervasyon Adresiniz <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ config('app.url') }}/</span>
                                <input type="text" id="reg-slug" name="slug" class="form-control" required maxlength="60"
                                       pattern="[a-z0-9-]+" value="{{ old('slug') }}" placeholder="ornek-kuafor">
                                <span class="input-group-text" id="slug-status">•</span>
                            </div>
                            <small class="text-muted">Müşterileriniz bu linkten randevu alacak. Küçük harf, rakam ve tire.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Adınız Soyadınız <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="120" value="{{ old('name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefon <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" required maxlength="30" value="{{ old('phone') }}" placeholder="+90 5xx xxx xx xx">
                        </div>
                        <div class="col-12">
                            <label class="form-label">E-posta <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required maxlength="190" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Şifre <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="8" placeholder="En az 8 karakter">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Şifre (Tekrar) <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" value="1" required>
                                <label class="form-check-label fs-13 text-muted" for="terms">
                                    <a href="{{ route('legal.terms') }}" target="_blank">Kullanım Koşulları</a>’nı,
                                    <a href="{{ route('legal.privacy') }}" target="_blank">KVKK Aydınlatma Metni</a>’ni ve
                                    <a href="{{ route('legal.distance-sales') }}" target="_blank">Mesafeli Satış Sözleşmesi</a>’ni
                                    okudum, kabul ediyorum.
                                </label>
                            </div>
                        </div>

                        <div class="col-12 d-grid">
                            <button class="btn btn-primary py-2 fw-medium" type="submit">İşletmemi Aç 🚀</button>
                        </div>
                    </form>

                    <p class="mt-3 text-center mb-0 text-muted">
                        Zaten üye misiniz? <a href="{{ route('login') }}" class="fw-semibold">Giriş yapın</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script-bottom')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nameInput = document.getElementById('reg-business-name');
            const slugInput = document.getElementById('reg-slug');
            const status = document.getElementById('slug-status');
            let slugTouched = {{ old('slug') ? 'true' : 'false' }};
            let timer = null;

            const slugify = (value) => value
                .toLowerCase()
                .replaceAll('ç', 'c').replaceAll('ğ', 'g').replaceAll('ı', 'i')
                .replaceAll('ö', 'o').replaceAll('ş', 's').replaceAll('ü', 'u')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .slice(0, 60);

            function check() {
                const slug = slugInput.value.trim();
                if (!slug) { status.textContent = '•'; return; }

                clearTimeout(timer);
                timer = setTimeout(async () => {
                    try {
                        const r = await fetch('{{ route('register.check-slug') }}?slug=' + encodeURIComponent(slug), { headers: { Accept: 'application/json' } });
                        const d = await r.json();
                        status.textContent = d.available ? '✓' : '✗';
                        status.className = 'input-group-text ' + (d.available ? 'text-success' : 'text-danger');
                    } catch (e) { status.textContent = '•'; }
                }, 350);
            }

            nameInput.addEventListener('input', () => {
                if (!slugTouched) { slugInput.value = slugify(nameInput.value); check(); }
            });
            slugInput.addEventListener('input', () => { slugTouched = true; slugInput.value = slugify(slugInput.value); check(); });

            if (slugInput.value) check();
        });
    </script>
@endsection
