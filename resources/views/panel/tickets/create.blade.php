@extends('layouts.vertical', ['title' => 'Yeni Destek Talebi', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('panel.tickets.store', $business) }}" class="row g-3">
                        @csrf

                        <div class="col-12">
                            <label class="form-label">Konu <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required maxlength="190"
                                   placeholder="Sorununuzu kısaca özetleyin" value="{{ old('subject') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                @foreach(\App\Models\Ticket::categories() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Öncelik <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                @foreach(\App\Models\Ticket::priorities() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('priority', 'normal') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mesajınız <span class="text-danger">*</span></label>
                            <textarea name="body" class="form-control" rows="6" required maxlength="5000"
                                      placeholder="Sorununuzu olabildiğince detaylı anlatın; ekran, tarih ve hata mesajı gibi bilgiler çözümü hızlandırır.">{{ old('body') }}</textarea>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary">Talebi Gönder</button>
                            <a href="{{ route('panel.tickets.index', $business) }}" class="btn btn-light">Vazgeç</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
