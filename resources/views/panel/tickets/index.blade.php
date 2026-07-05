@extends('layouts.vertical', ['title' => 'Destek Talepleri', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Destek Taleplerim</h4>
                    <a href="{{ route('panel.tickets.create', $business) }}" class="btn btn-sm btn-primary">
                        <i class="ri-add-line me-1"></i>Yeni Talep
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>#</th>
                            <th>Konu</th>
                            <th>Kategori</th>
                            <th>Öncelik</th>
                            <th>Son Hareket</th>
                            <th>Durum</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="text-muted">#{{ $ticket->id }}</td>
                                <td>
                                    <a href="{{ route('panel.tickets.show', [$business, $ticket]) }}" class="fw-medium">{{ $ticket->subject }}</a>
                                    <small class="text-muted d-block">{{ $ticket->messages_count }} mesaj · {{ $ticket->user?->name }}</small>
                                </td>
                                <td>{{ \App\Models\Ticket::categories()[$ticket->category] ?? $ticket->category }}</td>
                                <td>
                                    @php $priorityColor = ['high' => 'danger', 'normal' => 'info', 'low' => 'secondary'][$ticket->priority] ?? 'secondary'; @endphp
                                    <span class="badge bg-soft-{{ $priorityColor }} text-{{ $priorityColor }}">{{ \App\Models\Ticket::priorities()[$ticket->priority] ?? $ticket->priority }}</span>
                                </td>
                                <td><small>{{ $ticket->last_reply_at?->diffForHumans() }}</small></td>
                                <td><span class="badge bg-soft-{{ $ticket->status->color() }} text-{{ $ticket->status->color() }}">{{ $ticket->status->label() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Henüz destek talebiniz yok. Sorun mu yaşıyorsunuz? <a href="{{ route('panel.tickets.create', $business) }}">Talep oluşturun</a>.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $tickets->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
