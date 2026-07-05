@extends('layouts.vertical', ['title' => 'Notlar', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Yeni Not</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('panel.notes.store', $business) }}" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <input type="text" name="title" class="form-control" maxlength="150" placeholder="Başlık (isteğe bağlı)">
                        </div>
                        <div class="col-12">
                            <textarea name="content" class="form-control" rows="3" required maxlength="5000" placeholder="Not içeriği..."></textarea>
                        </div>
                        <div class="col-6">
                            <select name="color" class="form-select">
                                <option value="warning">Sarı</option>
                                <option value="info">Mavi</option>
                                <option value="success">Yeşil</option>
                                <option value="danger">Kırmızı</option>
                                <option value="primary">Mor</option>
                                <option value="secondary">Gri</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <input type="date" name="due_date" class="form-control" title="Son tarih">
                        </div>
                        <div class="col-12 d-flex gap-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_task" name="is_task" value="1">
                                <label class="form-check-label" for="is_task">Yapılacak iş</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_pinned" name="is_pinned" value="1">
                                <label class="form-check-label" for="is_pinned">Sabitle</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100">Ekle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="row">
                @forelse($notes as $note)
                    <div class="col-md-6">
                        <div class="card border-{{ $note->color }} border">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        @if($note->is_pinned)<i class="ri-pushpin-fill text-{{ $note->color }} me-1"></i>@endif
                                        @if($note->title)<strong>{{ $note->title }}</strong>@endif
                                    </div>
                                    <div class="d-flex gap-1">
                                        <form method="POST" action="{{ route('panel.notes.update', [$business, $note]) }}">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="toggle" value="pin">
                                            <button class="btn btn-sm btn-link p-0 text-muted" title="{{ $note->is_pinned ? 'Sabitlemeyi kaldır' : 'Sabitle' }}">
                                                <i class="ri-pushpin-{{ $note->is_pinned ? 'fill' : 'line' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('panel.notes.destroy', [$business, $note]) }}" onsubmit="return confirm('Not silinsin mi?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-link p-0 text-danger"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    </div>
                                </div>

                                <p class="mb-2 mt-1 {{ $note->is_completed ? 'text-decoration-line-through text-muted' : '' }}">{{ $note->content }}</p>

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        {{ $note->user?->name }} · {{ $note->created_at->format('d.m.Y') }}
                                        @if($note->due_date) · ⏰ {{ $note->due_date->format('d.m.Y') }} @endif
                                    </small>
                                    @if($note->is_task)
                                        <form method="POST" action="{{ route('panel.notes.update', [$business, $note]) }}">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="toggle" value="complete">
                                            <button class="btn btn-sm {{ $note->is_completed ? 'btn-soft-secondary' : 'btn-soft-success' }}">
                                                {{ $note->is_completed ? 'Geri Al' : 'Tamamlandı ✓' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card"><div class="card-body text-center text-muted py-5">Henüz not eklenmemiş.</div></div>
                    </div>
                @endforelse
            </div>
            {{ $notes->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
