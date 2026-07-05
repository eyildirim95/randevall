@extends('layouts.vertical', ['title' => 'Talep #'.$ticket->id, 'subTitle' => 'Destek'])

@section('content')
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h4 class="card-title mb-0">{{ $ticket->subject }}</h4>
                        <small class="text-muted">
                            {{ \App\Models\Ticket::categories()[$ticket->category] ?? $ticket->category }} ·
                            {{ \App\Models\Ticket::priorities()[$ticket->priority] ?? $ticket->priority }} öncelik ·
                            {{ $ticket->created_at->format('d.m.Y H:i') }}
                        </small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-soft-{{ $ticket->status->color() }} text-{{ $ticket->status->color() }} fs-12">{{ $ticket->status->label() }}</span>
                        <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $ticket->isClosed() ? 'open' : 'closed' }}">
                            <button class="btn btn-sm {{ $ticket->isClosed() ? 'btn-soft-success' : 'btn-soft-secondary' }}">
                                {{ $ticket->isClosed() ? 'Yeniden Aç' : 'Kapat' }}
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    @include('partials.ticket-thread', ['ticket' => $ticket])

                    <hr>

                    @if($ticket->isClosed())
                        <div class="alert alert-secondary mb-0 text-center">Talep kapalı. Yanıt yazmak için yeniden açın.</div>
                    @else
                        <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}">
                            @csrf
                            <label class="form-label fw-medium">Destek Yanıtı</label>
                            <textarea name="body" class="form-control mb-2" rows="4" required maxlength="5000" placeholder="Yanıtınız...">{{ old('body') }}</textarea>
                            <button class="btn btn-primary">Yanıtla</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">İşletme Bilgisi</h4></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-2">
                        <tr><th class="text-muted" style="width:110px">İşletme</th>
                            <td><a href="{{ route('admin.businesses.show', $ticket->business) }}">{{ $ticket->business->name }}</a></td></tr>
                        <tr><th class="text-muted">Slug</th><td>/{{ $ticket->business->slug }}</td></tr>
                        <tr><th class="text-muted">Plan</th><td>{{ $ticket->business->plan?->name ?? '—' }}</td></tr>
                        <tr><th class="text-muted">Talebi Açan</th><td>{{ $ticket->user?->name }} <small class="text-muted d-block">{{ $ticket->user?->email }}</small></td></tr>
                        <tr><th class="text-muted">Telefon</th><td>{{ $ticket->business->phone ?? '—' }}</td></tr>
                    </table>
                    <form method="POST" action="{{ route('admin.impersonate.start', $ticket->business) }}">
                        @csrf
                        <button class="btn btn-sm btn-soft-info w-100"><i class="ri-login-circle-line me-1"></i>İşletme Paneline Gir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
