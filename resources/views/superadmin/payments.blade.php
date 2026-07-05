@extends('layouts.vertical', ['title' => 'Ödemeler', 'subTitle' => 'Süper Admin'])

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Toplam Tahsilat</p>
                        <h3 class="text-success mb-0">{{ number_format($totals['paid'], 2, ',', '.') }} ₺</h3>
                    </div>
                    <div class="avatar-md bg-soft-success rounded flex-centered"><i class="ri-checkbox-circle-line fs-28 text-success"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Bekleyen</p>
                        <h3 class="text-warning mb-0">{{ number_format($totals['pending'], 2, ',', '.') }} ₺</h3>
                    </div>
                    <div class="avatar-md bg-soft-warning rounded flex-centered"><i class="ri-time-line fs-28 text-warning"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex gap-2">
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-light' }}">Tümü</a>
                    <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-primary' : 'btn-light' }}">Bekleyen</a>
                    <a href="{{ route('admin.payments.index', ['status' => 'paid']) }}" class="btn btn-sm {{ $status === 'paid' ? 'btn-primary' : 'btn-light' }}">Ödendi</a>
                    <a href="{{ route('admin.payments.index', ['status' => 'failed']) }}" class="btn btn-sm {{ $status === 'failed' ? 'btn-primary' : 'btn-light' }}">Başarısız</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>İşletme</th>
                            <th>Plan</th>
                            <th>Tutar</th>
                            <th>Yöntem</th>
                            <th>Tarih</th>
                            <th>Durum</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>
                                    @if($payment->business)
                                        <a href="{{ route('admin.businesses.show', $payment->business) }}" class="fw-medium">{{ $payment->business->name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $payment->subscription?->plan?->name ?? '—' }}</td>
                                <td class="fw-semibold">{{ number_format($payment->amount, 2, ',', '.') }} {{ $payment->currency }}</td>
                                <td>{{ ['manual' => 'Havale/EFT', 'paytr' => 'PayTR'][$payment->provider] ?? $payment->provider }}</td>
                                <td>{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @php
                                        $statusMap = ['paid' => ['success', 'Ödendi'], 'pending' => ['warning', 'Bekliyor'], 'failed' => ['danger', 'Başarısız'], 'refunded' => ['secondary', 'İade']];
                                        [$color, $label] = $statusMap[$payment->status] ?? ['secondary', $payment->status];
                                    @endphp
                                    <span class="badge bg-soft-{{ $color }} text-{{ $color }}">{{ $label }}</span>
                                </td>
                                <td class="text-center">
                                    @if($payment->status === 'pending')
                                        <form method="POST" action="{{ route('admin.payments.mark-paid', $payment) }}"
                                              onsubmit="return confirm('Ödeme onaylansın mı? Abonelik aktifleştirilir.')">
                                            @csrf
                                            <input type="hidden" name="confirm" value="1">
                                            <button class="btn btn-sm btn-success">Onayla</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Ödeme kaydı yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $payments->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
