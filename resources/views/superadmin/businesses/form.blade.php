@extends('layouts.vertical', ['title' => $business->exists ? 'İşletme Düzenle' : 'Yeni İşletme', 'subTitle' => 'Süper Admin'])

@section('content')
    <div class="row">
        <div class="col-xl-7">
            <form method="POST" action="{{ $business->exists ? route('admin.businesses.update', $business) : route('admin.businesses.store') }}">
                @csrf
                @if($business->exists) @method('PUT') @endif

                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">İşletme Bilgileri</h4></div>
                    <div class="card-body row g-3">
                        <div class="col-md-7">
                            <label class="form-label">İşletme Adı <span class="text-danger">*</span></label>
                            <input type="text" name="business[name]" class="form-control" required maxlength="150" value="{{ old('business.name', $business->name) }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Slug (URL) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">/</span>
                                <input type="text" name="business[slug]" class="form-control" required maxlength="60" pattern="[a-z0-9-]+"
                                       value="{{ old('business.slug', $business->slug) }}" placeholder="orn-berber">
                            </div>
                            <small class="text-muted">Sadece küçük harf, rakam ve tire.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sektör</label>
                            <input type="text" name="business[sector]" class="form-control" maxlength="80" value="{{ old('business.sector', $business->sector) }}" placeholder="Berber, Kuaför...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefon</label>
                            <input type="tel" name="business[phone]" class="form-control" maxlength="30" value="{{ old('business.phone', $business->phone) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="business[email]" class="form-control" maxlength="190" value="{{ old('business.email', $business->email) }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Adres</label>
                            <input type="text" name="business[address]" class="form-control" maxlength="255" value="{{ old('business.address', $business->address) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Şehir</label>
                            <input type="text" name="business[city]" class="form-control" maxlength="80" value="{{ old('business.city', $business->city) }}" placeholder="İstanbul, Ankara, İzmir...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Abonelik Planı</label>
                            <select name="plan_id" class="form-select">
                                <option value="">Plan yok</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('plan_id', $business->subscription_plan_id) == $plan->id)>{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($business->exists)
                            <div class="col-md-6">
                                <label class="form-label">Plan Bitiş Tarihi</label>
                                <input type="date" name="plan_expires_at" class="form-control" value="{{ old('plan_expires_at', $business->plan_expires_at?->format('Y-m-d')) }}">
                            </div>
                        @endif
                    </div>
                </div>

                @unless($business->exists)
                    <div class="card">
                        <div class="card-header"><h4 class="card-title mb-0">İşletme Sahibi Hesabı</h4></div>
                        <div class="card-body row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="owner[name]" class="form-control" required maxlength="120" value="{{ old('owner.name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-posta <span class="text-danger">*</span></label>
                                <input type="email" name="owner[email]" class="form-control" required maxlength="190" value="{{ old('owner.email') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefon</label>
                                <input type="tel" name="owner[phone]" class="form-control" maxlength="30" value="{{ old('owner.phone') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Şifre <span class="text-danger">*</span></label>
                                <input type="text" name="owner[password]" class="form-control" required minlength="8" value="{{ old('owner.password') }}" placeholder="En az 8 karakter">
                            </div>
                        </div>
                    </div>
                @endunless

                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-primary">{{ $business->exists ? 'Güncelle' : 'İşletmeyi Oluştur' }}</button>
                    <a href="{{ route('admin.businesses.index') }}" class="btn btn-light">Vazgeç</a>
                </div>
            </form>
        </div>
    </div>
@endsection
