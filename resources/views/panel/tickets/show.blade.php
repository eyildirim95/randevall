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
                        @unless($ticket->isClosed())
                            <form method="POST" action="{{ route('panel.tickets.close', [$business, $ticket]) }}"
                                  onsubmit="return confirm('Talep kapatılsın mı?')">
                                @csrf
                                <button class="btn btn-sm btn-soft-secondary">Talebi Kapat</button>
                            </form>
                        @endunless
                    </div>
                </div>
                <div class="card-body">
                    @include('partials.ticket-thread', ['ticket' => $ticket])

                    <hr>

                    @if($ticket->isClosed())
                        <div class="alert alert-secondary mb-0 text-center">
                            Bu talep kapatıldı. Sorununuz devam ediyorsa <a href="{{ route('panel.tickets.create', $business) }}">yeni talep oluşturun</a>.
                        </div>
                    @else
                        <form method="POST" action="{{ route('panel.tickets.reply', [$business, $ticket]) }}">
                            @csrf
                            <label class="form-label fw-medium">Yanıt Yaz</label>
                            <textarea name="body" class="form-control mb-2" rows="4" required maxlength="5000" placeholder="Mesajınız...">{{ old('body') }}</textarea>
                            <button class="btn btn-primary">Gönder</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
