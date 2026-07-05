@extends('layouts.vertical', ['title' => 'Abonelik Planları', 'subTitle' => 'Süper Admin'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Planlar</h4>
                    <a href="{{ route('admin.plans.create') }}" class="btn btn-sm btn-primary"><i class="ri-add-line me-1"></i>Yeni Plan</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Plan</th>
                            <th>Aylık</th>
                            <th>Yıllık</th>
                            <th>Limitler</th>
                            <th>İşletme</th>
                            <th>Durum</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($plans as $plan)
                            <tr>
                                <td>
                                    <span class="fw-medium">{{ $plan->name }}</span>
                                    @if($plan->is_featured)<span class="badge bg-soft-warning text-warning ms-1">Öne Çıkan</span>@endif
                                    <small class="text-muted d-block">{{ $plan->slug }} · {{ $plan->trial_days }} gün deneme</small>
                                </td>
                                <td>{{ number_format($plan->price_monthly, 0, ',', '.') }} {{ $plan->currency }}</td>
                                <td>{{ number_format($plan->price_yearly, 0, ',', '.') }} {{ $plan->currency }}</td>
                                <td>
                                    <small>
                                        {{ $plan->max_staff > 0 ? $plan->max_staff.' personel' : 'Sınırsız personel' }} ·
                                        {{ $plan->max_appointments_per_month > 0 ? $plan->max_appointments_per_month.' randevu/ay' : 'Sınırsız randevu' }}
                                    </small>
                                </td>
                                <td>{{ $plan->businesses_count }}</td>
                                <td>
                                    <span class="badge bg-soft-{{ $plan->is_active ? 'success' : 'danger' }} text-{{ $plan->is_active ? 'success' : 'danger' }}">
                                        {{ $plan->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-sm btn-soft-primary"><i class="ri-pencil-line"></i></a>
                                    <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" class="d-inline" onsubmit="return confirm('Plan silinsin mi?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Plan bulunamadı.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
