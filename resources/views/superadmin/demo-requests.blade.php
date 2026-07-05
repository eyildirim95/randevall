@extends('layouts.vertical', ['title' => 'Demo Talepleri', 'subTitle' => 'Süper Admin'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.demo-requests.index') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-light' }}">Tümü</a>
                    @foreach($statuses as $key => $label)
                        <a href="{{ route('admin.demo-requests.index', ['status' => $key]) }}" class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Talep Eden</th>
                            <th>İletişim</th>
                            <th>Mesaj</th>
                            <th>Tarih</th>
                            <th>Durum</th>
                            <th class="text-center" style="width:220px">Güncelle</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($demoRequests as $demo)
                            <tr>
                                <td>
                                    <span class="fw-medium">{{ $demo->name }}</span>
                                    @if($demo->business_name)<small class="text-muted d-block">{{ $demo->business_name }} @if($demo->sector)({{ $demo->sector }})@endif</small>@endif
                                </td>
                                <td>
                                    <a href="tel:{{ $demo->phone }}">{{ $demo->phone }}</a>
                                    @if($demo->email)<small class="text-muted d-block">{{ $demo->email }}</small>@endif
                                </td>
                                <td style="max-width: 220px">
                                    <small>{{ Str::limit($demo->message, 120) ?? '—' }}</small>
                                    @if($demo->admin_notes)
                                        <small class="text-info d-block">Not: {{ Str::limit($demo->admin_notes, 80) }}</small>
                                    @endif
                                </td>
                                <td><small>{{ $demo->created_at->format('d.m.Y H:i') }}</small></td>
                                <td>
                                    @php
                                        $colorMap = ['new' => 'danger', 'contacted' => 'warning', 'converted' => 'success', 'closed' => 'secondary'];
                                        $color = $colorMap[$demo->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-soft-{{ $color }} text-{{ $color }}">{{ $statuses[$demo->status] ?? $demo->status }}</span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.demo-requests.update', $demo) }}" class="d-flex gap-1">
                                        @csrf @method('PUT')
                                        <select name="status" class="form-select form-select-sm">
                                            @foreach($statuses as $key => $label)
                                                <option value="{{ $key }}" @selected($demo->status === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="admin_notes" class="form-control form-control-sm" placeholder="Not" value="{{ $demo->admin_notes }}">
                                        <button class="btn btn-sm btn-soft-primary"><i class="ri-check-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Demo talebi yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $demoRequests->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
