@extends('layouts.vertical', ['title' => $customer->exists ? 'Müşteri Düzenle' : 'Yeni Müşteri', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-body">
                    <form method="POST"
                          action="{{ $customer->exists ? route('panel.customers.update', [$business, $customer]) : route('panel.customers.store', $business) }}"
                          class="row g-3">
                        @csrf
                        @if($customer->exists) @method('PUT') @endif

                        <div class="col-md-6">
                            <label class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="120" value="{{ old('name', $customer->name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefon <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" required maxlength="30" value="{{ old('phone', $customer->phone) }}" placeholder="05xx xxx xx xx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="email" class="form-control" maxlength="190" value="{{ old('email', $customer->email) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Doğum Günü</label>
                            <input type="date" name="birthday" class="form-control" value="{{ old('birthday', $customer->birthday?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cinsiyet</label>
                            <select name="gender" class="form-select">
                                <option value="">Belirtilmedi</option>
                                <option value="male" @selected(old('gender', $customer->gender) === 'male')>Erkek</option>
                                <option value="female" @selected(old('gender', $customer->gender) === 'female')>Kadın</option>
                                <option value="other" @selected(old('gender', $customer->gender) === 'other')>Diğer</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notlar</label>
                            <textarea name="notes" class="form-control" rows="3" maxlength="2000" placeholder="Tercihler, alerjiler, özel notlar...">{{ old('notes', $customer->notes) }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="is_blacklisted" value="0">
                                <input type="checkbox" class="form-check-input" id="is_blacklisted" name="is_blacklisted" value="1" @checked(old('is_blacklisted', $customer->is_blacklisted))>
                                <label class="form-check-label" for="is_blacklisted">Kara listeye al (online randevu alamaz)</label>
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary">{{ $customer->exists ? 'Güncelle' : 'Kaydet' }}</button>
                            <a href="{{ route('panel.customers.index', $business) }}" class="btn btn-light">Vazgeç</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
