@extends('layouts.vertical', ['title' => 'Destek Talepleri', 'subTitle' => 'Süper Admin'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-light' }}">Tümü</a>
                    <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" class="btn btn-sm {{ $status === 'open' ? 'btn-warning' : 'btn-light' }}">
                        Açık <span class="badge bg-dark">{{ $counts['open'] }}</span>
                    </a>
                    <a href="{{ route('admin.tickets.index', ['status' => 'answered']) }}" class="btn btn-sm {{ $status === 'answered' ? 'btn-primary' : 'btn-light' }}">
                        Yanıtlandı <span class="badge bg-dark">{{ $counts['answered'] }}</span>
                    </a>
                    <a href="{{ route('admin.tickets.index', ['status' => 'closed']) }}" class="btn btn-sm {{ $status === 'closed' ? 'btn-secondary' : 'btn-light' }}">
                        Kapalı <span class="badge bg-dark">{{ $counts['closed'] }}</span>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>#</th>
                            <th>İşletme</th>
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
                                    <span class="fw-medium">{{ $ticket->business?->name }}</span>
                                    <small class="text-muted d-block">{{ $ticket->user?->name }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="fw-medium">{{ $ticket->subject }}</a>
                                    <small class="text-muted d-block">{{ $ticket->messages_count }} mesaj</small>
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
                            <tr><td colspan="7" class="text-center text-muted py-4">Destek talebi yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $tickets->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
