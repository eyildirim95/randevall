@extends('layouts.vertical', ['title' => 'Mesaj Kayıtları', 'subTitle' => 'Süper Admin'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.messages.index') }}" class="btn btn-sm {{ !$filters['channel'] && !$filters['status'] ? 'btn-primary' : 'btn-light' }}">Tümü</a>
                    <a href="{{ route('admin.messages.index', ['channel' => 'whatsapp']) }}" class="btn btn-sm {{ $filters['channel'] === 'whatsapp' ? 'btn-primary' : 'btn-light' }}">WhatsApp</a>
                    <a href="{{ route('admin.messages.index', ['channel' => 'sms']) }}" class="btn btn-sm {{ $filters['channel'] === 'sms' ? 'btn-primary' : 'btn-light' }}">SMS</a>
                    <a href="{{ route('admin.messages.index', ['channel' => 'email']) }}" class="btn btn-sm {{ $filters['channel'] === 'email' ? 'btn-primary' : 'btn-light' }}">E-posta</a>
                    <a href="{{ route('admin.messages.index', ['status' => 'failed']) }}" class="btn btn-sm {{ $filters['status'] === 'failed' ? 'btn-danger' : 'btn-light' }}">Başarısız</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>İşletme</th>
                            <th>Kanal</th>
                            <th>Alıcı</th>
                            <th>Tür</th>
                            <th>İçerik</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($messages as $message)
                            <tr>
                                <td>{{ $message->business?->name ?? 'Platform' }}</td>
                                <td>
                                    @php
                                        $channelMap = [
                                            'whatsapp' => ['success', 'WhatsApp'],
                                            'sms' => ['primary', 'SMS'],
                                            'email' => ['info', 'E-posta'],
                                        ];
                                        [$color, $label] = $channelMap[$message->channel] ?? ['secondary', $message->channel];
                                    @endphp
                                    <span class="badge bg-soft-{{ $color }} text-{{ $color }}">{{ $label }}</span>
                                </td>
                                <td>{{ $message->recipient }}</td>
                                <td><small>{{ $message->message_type }}</small></td>
                                <td style="max-width: 260px"><small class="text-muted">{{ Str::limit($message->body, 90) }}</small></td>
                                <td>
                                    @php
                                        $statusMap = ['sent' => ['success', 'Gönderildi'], 'queued' => ['warning', 'Kuyrukta'], 'failed' => ['danger', 'Başarısız']];
                                        [$color, $label] = $statusMap[$message->status] ?? ['secondary', $message->status];
                                    @endphp
                                    <span class="badge bg-soft-{{ $color }} text-{{ $color }}">{{ $label }}</span>
                                    @if($message->status === 'failed' && $message->provider_response)
                                        <small class="text-danger d-block">{{ Str::limit($message->provider_response, 50) }}</small>
                                    @endif
                                </td>
                                <td><small>{{ $message->created_at->format('d.m.Y H:i') }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Mesaj kaydı yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $messages->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
