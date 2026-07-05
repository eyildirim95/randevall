@extends('layouts.vertical', ['title' => 'Duyurular', 'subTitle' => 'Süper Admin'])

@section('content')
    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Yeni Duyuru</h4></div>
                <div class="card-body">
                    <p class="text-muted fs-13">Duyurular tüm işletme panellerinin üstünde gösterilir.</p>
                    <form method="POST" action="{{ route('admin.announcements.store') }}" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <input type="text" name="title" class="form-control" required maxlength="150" placeholder="Başlık">
                        </div>
                        <div class="col-12">
                            <textarea name="body" class="form-control" rows="3" required maxlength="3000" placeholder="Duyuru metni"></textarea>
                        </div>
                        <div class="col-6">
                            <select name="level" class="form-select">
                                <option value="info">Bilgi (mavi)</option>
                                <option value="warning">Uyarı (sarı)</option>
                                <option value="success">Müjde (yeşil)</option>
                                <option value="danger">Kritik (kırmızı)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-13">Başlangıç</label>
                            <input type="datetime-local" name="starts_at" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-13">Bitiş</label>
                            <input type="datetime-local" name="ends_at" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100">Yayınla</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Duyuru Listesi</h4></div>
                <div class="card-body">
                    @forelse($announcements as $announcement)
                        <div class="alert alert-{{ $announcement->level }} d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $announcement->title }}</strong>
                                @unless($announcement->is_active)<span class="badge bg-secondary ms-1">Pasif</span>@endunless
                                <p class="mb-1">{{ $announcement->body }}</p>
                                <small class="text-muted">
                                    {{ $announcement->created_at->format('d.m.Y') }}
                                    @if($announcement->starts_at) · {{ $announcement->starts_at->format('d.m.Y H:i') }}'den itibaren @endif
                                    @if($announcement->ends_at) · {{ $announcement->ends_at->format('d.m.Y H:i') }}'e kadar @endif
                                </small>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="toggle" value="1">
                                    <button class="btn btn-sm btn-light" title="{{ $announcement->is_active ? 'Pasifleştir' : 'Aktifleştir' }}">
                                        <i class="ri-{{ $announcement->is_active ? 'pause' : 'play' }}-line"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Duyuru silinsin mi?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center my-4">Duyuru yok.</p>
                    @endforelse

                    {{ $announcements->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
