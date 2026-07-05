@extends('layouts.vertical', ['title' => 'Gelir - Gider', 'subTitle' => $business->name])

@section('content')
    {{-- Ozet kartlari --}}
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Toplam Gelir</p>
                        <h3 class="text-success mb-0">{{ number_format($totals['income'], 2, ',', '.') }} {{ $business->currency }}</h3>
                    </div>
                    <div class="avatar-md bg-soft-success rounded flex-centered"><i class="ri-arrow-up-circle-line fs-28 text-success"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Toplam Gider</p>
                        <h3 class="text-danger mb-0">{{ number_format($totals['expense'], 2, ',', '.') }} {{ $business->currency }}</h3>
                    </div>
                    <div class="avatar-md bg-soft-danger rounded flex-centered"><i class="ri-arrow-down-circle-line fs-28 text-danger"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Net</p>
                        <h3 class="mb-0 {{ $totals['net'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totals['net'], 2, ',', '.') }} {{ $business->currency }}</h3>
                    </div>
                    <div class="avatar-md bg-soft-primary rounded flex-centered"><i class="ri-scales-3-line fs-28 text-primary"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Yeni Kayıt</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('panel.transactions.store', $business) }}" class="row g-2">
                        @csrf
                        <div class="col-6">
                            <label class="form-label">Tür <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" id="tx-type" required>
                                <option value="income">Gelir</option>
                                <option value="expense">Gider</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select" id="tx-category">
                                <optgroup label="Gelir" id="tx-cat-income">
                                    @foreach($incomeCategories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Gider" id="tx-cat-expense">
                                    @foreach($expenseCategories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tutar <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tarih <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control" required value="{{ today()->toDateString() }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Ödeme Yöntemi</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">Nakit</option>
                                <option value="card">Kart</option>
                                <option value="transfer">Havale</option>
                                <option value="other">Diğer</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Açıklama</label>
                            <input type="text" name="description" class="form-control" maxlength="255">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100">Ekle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label mb-1">Başlangıç</label>
                            <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">Bitiş</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-1">Tür</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="">Tümü</option>
                                <option value="income" @selected($type === 'income')>Gelir</option>
                                <option value="expense" @selected($type === 'expense')>Gider</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-primary">Filtrele</button>
                            <a href="{{ route('panel.reports.export', [$business, 'from' => $from, 'to' => $to]) }}" class="btn btn-sm btn-soft-success">
                                <i class="ri-file-excel-2-line me-1"></i>CSV
                            </a>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Tarih</th>
                            <th>Tür</th>
                            <th>Kategori</th>
                            <th>Açıklama</th>
                            <th>Tutar</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transaction_date->format('d.m.Y') }}</td>
                                <td>
                                    <span class="badge bg-soft-{{ $transaction->type->value === 'income' ? 'success' : 'danger' }} text-{{ $transaction->type->value === 'income' ? 'success' : 'danger' }}">
                                        {{ $transaction->type->label() }}
                                    </span>
                                </td>
                                <td>{{ $transaction->category ?? '—' }}</td>
                                <td>
                                    {{ $transaction->description ?? '—' }}
                                    @if($transaction->appointment_id)
                                        <a href="{{ route('panel.appointments.show', [$business, $transaction->appointment_id]) }}" class="badge bg-soft-info text-info text-decoration-none">Randevu</a>
                                    @endif
                                </td>
                                <td class="fw-semibold {{ $transaction->type->value === 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type->value === 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('panel.transactions.destroy', [$business, $transaction]) }}" class="d-inline"
                                          onsubmit="return confirm('Kayıt silinsin mi?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Kayıt yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $transactions->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
