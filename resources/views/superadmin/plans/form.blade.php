@extends('layouts.vertical', ['title' => $plan->exists ? 'Plan Düzenle' : 'Yeni Plan', 'subTitle' => 'Süper Admin'])

@section('content')
    <div class="row">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}" class="row g-3">
                        @csrf
                        @if($plan->exists) @method('PUT') @endif

                        <div class="col-md-6">
                            <label class="form-label">Plan Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="80" value="{{ old('name', $plan->name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" maxlength="80" value="{{ old('slug', $plan->slug) }}" placeholder="Boş bırakılırsa otomatik">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <input type="text" name="description" class="form-control" maxlength="500" value="{{ old('description', $plan->description) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Aylık Fiyat <span class="text-danger">*</span></label>
                            <input type="number" name="price_monthly" class="form-control" required min="0" step="0.01" value="{{ old('price_monthly', $plan->price_monthly ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Yıllık Fiyat <span class="text-danger">*</span></label>
                            <input type="number" name="price_yearly" class="form-control" required min="0" step="0.01" value="{{ old('price_yearly', $plan->price_yearly ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Para Birimi</label>
                            <select name="currency" class="form-select">
                                @foreach(['TRY', 'EUR', 'USD', 'GBP'] as $currency)
                                    <option value="{{ $currency }}" @selected(old('currency', $plan->currency ?? 'TRY') === $currency)>{{ $currency }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Maks. Personel</label>
                            <input type="number" name="max_staff" class="form-control" required min="0" value="{{ old('max_staff', $plan->max_staff ?? 3) }}">
                            <small class="text-muted">0 = sınırsız</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Maks. Müşteri</label>
                            <input type="number" name="max_customers" class="form-control" required min="0" value="{{ old('max_customers', $plan->max_customers ?? 0) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Randevu/Ay</label>
                            <input type="number" name="max_appointments_per_month" class="form-control" required min="0" value="{{ old('max_appointments_per_month', $plan->max_appointments_per_month ?? 0) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">WhatsApp/Ay</label>
                            <input type="number" name="whatsapp_quota_monthly" class="form-control" required min="0" value="{{ old('whatsapp_quota_monthly', $plan->whatsapp_quota_monthly ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Deneme Süresi (gün)</label>
                            <input type="number" name="trial_days" class="form-control" required min="0" max="90" value="{{ old('trial_days', $plan->trial_days ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sıra</label>
                            <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $plan->sort_order ?? 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Özellikler <small class="text-muted">(her satır bir özellik)</small></label>
                            <textarea name="features_text" class="form-control" rows="4" maxlength="2000">{{ old('features_text', implode("\n", $plan->features ?? [])) }}</textarea>
                        </div>
                        <div class="col-12 d-flex gap-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $plan->is_active ?? true))>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $plan->is_featured ?? false))>
                                <label class="form-check-label" for="is_featured">Öne çıkar</label>
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary">{{ $plan->exists ? 'Güncelle' : 'Oluştur' }}</button>
                            <a href="{{ route('admin.plans.index') }}" class="btn btn-light">Vazgeç</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
