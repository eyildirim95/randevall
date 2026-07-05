@extends('layouts.vertical', ['title' => 'İşletmeler', 'subTitle' => 'Süper Admin'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="İşletme adı veya slug ara..." value="{{ $q }}" style="min-width: 260px">
                        <button class="btn btn-sm btn-primary">Ara</button>
                    </form>
                    <a href="{{ route('admin.businesses.create') }}" class="btn btn-sm btn-primary">
                        <i class="ri-add-line me-1"></i>Yeni İşletme
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>İşletme</th>
                            <th>Plan</th>
                            <th>Müşteri</th>
                            <th>Randevu</th>
                            <th>Kayıt</th>
                            <th>Durum</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($businesses as $b)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.businesses.show', $b) }}" class="fw-medium">{{ $b->name }}</a>
                                    <small class="text-muted d-block">/{{ $b->slug }} @if($b->city) · {{ $b->city }} @endif</small>
                                </td>
                                <td>
                                    {{ $b->plan?->name ?? '—' }}
                                    @if($b->plan_expires_at)
                                        <small class="text-muted d-block">{{ $b->plan_expires_at->format('d.m.Y') }}'e kadar</small>
                                    @endif
                                </td>
                                <td>{{ $b->customers_count }}</td>
                                <td>{{ $b->appointments_count }}</td>
                                <td>{{ $b->created_at->format('d.m.Y') }}</td>
                                <td>
                                    @if($b->isSuspended())
                                        <span class="badge bg-soft-danger text-danger">Askıda</span>
                                    @elseif($b->onTrial())
                                        <span class="badge bg-soft-warning text-warning">Deneme</span>
                                    @elseif($b->hasActivePlan())
                                        <span class="badge bg-soft-success text-success">Aktif</span>
                                    @else
                                        <span class="badge bg-soft-secondary text-secondary">Süresi Dolmuş</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.businesses.show', $b) }}" class="btn btn-sm btn-soft-primary"><i class="ri-eye-line"></i></a>
                                    <form method="POST" action="{{ route('admin.impersonate.start', $b) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-soft-info" title="Panele gir (destek modu)"><i class="ri-login-circle-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">İşletme bulunamadı.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $businesses->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
