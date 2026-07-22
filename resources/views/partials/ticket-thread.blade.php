{{-- Ticket mesaj akisi (panel ve super admin ortak) --}}
@foreach($ticket->messages as $message)
    <div class="d-flex gap-2 mb-3 {{ $message->is_admin ? '' : 'flex-row-reverse' }}">
        <div class="avatar-sm flex-shrink-0">
            <span class="avatar-title rounded-circle fw-bold {{ $message->is_admin ? 'bg-primary text-white' : 'bg-soft-success text-success' }}">
                {{ $message->is_admin ? 'R' : mb_strtoupper(mb_substr($message->user?->name ?? '?', 0, 1)) }}
            </span>
        </div>
        <div class="flex-grow-1" style="max-width: 80%">
            <div class="card mb-0 {{ $message->is_admin ? 'bg-light bg-opacity-50' : 'border-success border' }}">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between gap-3 mb-1">
                        <strong class="fs-13">{{ $message->is_admin ? config('app.name').' Destek' : ($message->user?->name ?? 'İşletme') }}</strong>
                        <small class="text-muted">{{ $message->created_at->format('d.m.Y H:i') }}</small>
                    </div>
                    <p class="mb-0" style="white-space: pre-line">{{ $message->body }}</p>
                </div>
            </div>
        </div>
    </div>
@endforeach
