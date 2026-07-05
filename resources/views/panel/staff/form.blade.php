@extends('layouts.vertical', ['title' => $staff->exists ? 'Personel Düzenle' : 'Yeni Personel', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-body">
                    <form method="POST"
                          action="{{ $staff->exists ? route('panel.staff.update', [$business, $staff]) : route('panel.staff.store', $business) }}"
                          class="row g-3">
                        @csrf
                        @if($staff->exists) @method('PUT') @endif

                        <div class="col-md-8">
                            <label class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="120" value="{{ old('name', $staff->name) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ünvan</label>
                            <input type="text" name="title" class="form-control" maxlength="80" value="{{ old('title', $staff->title) }}" placeholder="Usta, Stilist...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefon</label>
                            <input type="tel" name="phone" class="form-control" maxlength="30" value="{{ old('phone', $staff->phone) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="email" class="form-control" maxlength="190" value="{{ old('email', $staff->email) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Takvim Rengi</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="{{ old('color', $staff->color ?? '#7f56da') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sıra</label>
                            <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $staff->sort_order ?? 0) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Verdiği hizmetler</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($services as $service)
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="service-{{ $service->id }}" name="service_ids[]" value="{{ $service->id }}"
                                            @checked(in_array($service->id, old('service_ids', $staff->services->pluck('id')->all())))>
                                        <label class="form-check-label" for="service-{{ $service->id }}">{{ $service->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-4 flex-wrap">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $staff->is_active ?? true))>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                            <div class="form-check">
                                <input type="hidden" name="accepts_online_booking" value="0">
                                <input type="checkbox" class="form-check-input" id="accepts_online_booking" name="accepts_online_booking" value="1" @checked(old('accepts_online_booking', $staff->accepts_online_booking ?? true))>
                                <label class="form-check-label" for="accepts_online_booking">Online randevu alabilir</label>
                            </div>
                            @if(!$staff->user_id)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="create_login" name="create_login" value="1" @checked(old('create_login'))>
                                    <label class="form-check-label" for="create_login">Panele giriş hesabı oluştur (e-posta gerekli — şifre belirleme bağlantısı gönderilir)</label>
                                </div>
                            @endif
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary">{{ $staff->exists ? 'Güncelle' : 'Kaydet' }}</button>
                            <a href="{{ route('panel.staff.index', $business) }}" class="btn btn-light">Vazgeç</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
