@extends('layouts.vertical', ['title' => 'Müşteriler', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Ad, telefon veya e-posta ara..." value="{{ $q }}" style="min-width: 260px">
                        <button class="btn btn-sm btn-primary">Ara</button>
                    </form>
                    <a href="{{ route('panel.customers.create', $business) }}" class="btn btn-sm btn-primary">
                        <i class="ri-user-add-line me-1"></i>Yeni Müşteri
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Müşteri</th>
                            <th>Telefon</th>
                            <th>Randevu</th>
                            <th>Toplam Harcama</th>
                            <th>Sadakat Puanı</th>
                            <th>Durum</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <a href="{{ route('panel.customers.show', [$business, $customer]) }}" class="fw-medium">{{ $customer->name }}</a>
                                    @if($customer->birthday)
                                        <small class="text-muted d-block">🎂 {{ $customer->birthday->format('d.m.Y') }}</small>
                                    @endif
                                    @if($customer->records_count > 0)
                                        <small class="text-muted d-block"><i class="ri-file-list-3-line"></i> {{ $customer->records_count }} takip notu</small>
                                    @endif
                                </td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->total_appointments }} <small class="text-muted">({{ $customer->completed_appointments }} tamamlandı)</small></td>
                                <td>{{ number_format($customer->total_spent, 2, ',', '.') }} {{ $business->currency }}</td>
                                <td><span class="badge bg-soft-warning text-warning fs-12">⭐ {{ $customer->loyalty_points }}</span></td>
                                <td>
                                    @if($customer->is_blacklisted)
                                        <span class="badge bg-soft-danger text-danger">Kara Liste</span>
                                    @elseif($customer->no_show_count >= 3)
                                        <span class="badge bg-soft-warning text-warning">{{ $customer->no_show_count }}x gelmedi</span>
                                    @else
                                        <span class="badge bg-soft-success text-success">Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('panel.customers.show', [$business, $customer]) }}" class="btn btn-sm btn-soft-primary"><i class="ri-eye-line"></i></a>
                                    <a href="{{ route('panel.customers.edit', [$business, $customer]) }}" class="btn btn-sm btn-soft-secondary"><i class="ri-pencil-line"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Müşteri bulunamadı.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    {{ $customers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
