@extends('layouts.vertical', ['title' => $service->exists ? 'Hizmet Düzenle' : 'Yeni Hizmet', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-body">
                    <form method="POST"
                          action="{{ $service->exists ? route('panel.services.update', [$business, $service]) : route('panel.services.store', $business) }}"
                          class="row g-3">
                        @csrf
                        @if($service->exists) @method('PUT') @endif

                        <div class="col-md-8">
                            <label class="form-label">Hizmet Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="120" value="{{ old('name', $service->name) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="category" class="form-control" maxlength="80" value="{{ old('category', $service->category) }}" placeholder="Saç, Sakal, Bakım...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Süre (dakika) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_minutes" class="form-control" required min="5" max="600" step="5" value="{{ old('duration_minutes', $service->duration_minutes ?? 30) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hazırlık Süresi (dk)</label>
                            <input type="number" name="buffer_minutes" class="form-control" min="0" max="120" step="5" value="{{ old('buffer_minutes', $service->buffer_minutes ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ücret ({{ $business->currency }}) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" required min="0" step="0.01" value="{{ old('price', $service->price ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Takvim Rengi</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="{{ old('color', $service->color ?? '#22c55e') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sıra</label>
                            <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $service->sort_order ?? 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <textarea name="description" class="form-control" rows="2" maxlength="1000">{{ old('description', $service->description) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Bu hizmeti veren personel</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($staffList as $staff)
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="staff-{{ $staff->id }}" name="staff_ids[]" value="{{ $staff->id }}"
                                            @checked(in_array($staff->id, old('staff_ids', $service->staff->pluck('id')->all())))>
                                        <label class="form-check-label" for="staff-{{ $staff->id }}">{{ $staff->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-4">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $service->is_active ?? true))>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                            <div class="form-check">
                                <input type="hidden" name="online_bookable" value="0">
                                <input type="checkbox" class="form-check-input" id="online_bookable" name="online_bookable" value="1" @checked(old('online_bookable', $service->online_bookable ?? true))>
                                <label class="form-check-label" for="online_bookable">Online randevuya açık</label>
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary">{{ $service->exists ? 'Güncelle' : 'Kaydet' }}</button>
                            <a href="{{ route('panel.services.index', $business) }}" class="btn btn-light">Vazgeç</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
