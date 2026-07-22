@extends('layouts.vertical', ['title' => $business->name, 'subTitle' => 'İşletme Detayı'])

@section('content')
    <div class="row">
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">İşletme Bilgileri</h4>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.businesses.edit', $business) }}" class="btn btn-sm btn-soft-primary"><i class="ri-pencil-line"></i></a>
                        <form method="POST" action="{{ route('admin.impersonate.start', $business) }}">
                            @csrf
                            <button class="btn btn-sm btn-soft-info" title="Destek modunda panele gir"><i class="ri-login-circle-line me-1"></i>Panele Gir</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" style="width: 150px">Slug</th><td><a href="{{ route('booking.show', $business) }}" target="_blank">/{{ $business->slug }}</a></td></tr>
                        <tr><th class="text-muted">Sektör</th><td>{{ $business->sector ?? '—' }}</td></tr>
                        <tr><th class="text-muted">Telefon</th><td>{{ $business->phone ?? '—' }}</td></tr>
                        <tr><th class="text-muted">E-posta</th><td>{{ $business->email ?? '—' }}</td></tr>
                        <tr><th class="text-muted">Adres</th><td>{{ $business->address }} {{ $business->city }}</td></tr>
                        <tr><th class="text-muted">Plan</th><td>{{ $business->plan?->name ?? '—' }}</td></tr>
                        <tr><th class="text-muted">Deneme Bitiş</th><td>{{ $business->trial_ends_at?->format('d.m.Y') ?? '—' }}</td></tr>
                        <tr><th class="text-muted">Plan Bitiş</th><td>{{ $business->plan_expires_at?->format('d.m.Y') ?? '—' }}</td></tr>
                        <tr><th class="text-muted">Kayıt</th><td>{{ $business->created_at->format('d.m.Y H:i') }}</td></tr>
                        <tr>
                            <th class="text-muted">Durum</th>
                            <td>
                                @if($business->isSuspended())
                                    <span class="badge bg-soft-danger text-danger">Askıda — {{ $business->suspension_reason }}</span>
                                @else
                                    <span class="badge bg-soft-success text-success">Aktif</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    <hr>
                    <div class="d-flex flex-wrap gap-2">
                        @if($business->isSuspended())
                            <form method="POST" action="{{ route('admin.businesses.activate', $business) }}">
                                @csrf
                                <button class="btn btn-sm btn-success">Aktifleştir</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.businesses.suspend', $business) }}"
                                  onsubmit="return confirm('İşletme askıya alınsın mı? Panel ve rezervasyon sayfası kapanır.')">
                                @csrf
                                <button class="btn btn-sm btn-warning">Askıya Al</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.businesses.destroy', $business) }}"
                              onsubmit="return confirm('İşletme silinsin mi? (Arşivlenir, geri alınabilir)')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Sil</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Kullanıcılar</h4></div>
                <div class="card-body py-2">
                    @foreach($business->users as $user)
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <div>
                                <span class="fw-medium">{{ $user->name }}</span>
                                <small class="text-muted d-block">{{ $user->email }}</small>
                            </div>
                            <span class="badge bg-soft-primary text-primary align-self-center">
                                {{ \App\Enums\BusinessRole::tryFrom($user->pivot->role)?->label() ?? $user->pivot->role }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="row">
                @php
                    $counts = [
                        ['label' => 'Müşteri', 'value' => $business->customers_count, 'icon' => 'ri-group-line'],
                        ['label' => 'Randevu', 'value' => $business->appointments_count, 'icon' => 'ri-calendar-check-line'],
                        ['label' => 'Personel', 'value' => $business->staff_count, 'icon' => 'ri-user-star-line'],
                        ['label' => 'Hizmet', 'value' => $business->services_count, 'icon' => 'ri-scissors-2-line'],
                    ];
                @endphp
                @foreach($counts as $count)
                    <div class="col-6 col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="{{ $count['icon'] }} fs-24 text-primary"></i>
                                <h3 class="mb-0 mt-1">{{ $count['value'] }}</h3>
                                <small class="text-muted">{{ $count['label'] }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Abonelik Geçmişi</h4>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#manual-subscription-form">
                        <i class="ri-add-line me-1"></i>Manuel Abonelik Ekle
                    </button>
                </div>

                <div id="manual-subscription-form" class="collapse @if($errors->has('plan_id') || $errors->has('period') || $errors->has('starts_at') || $errors->has('ends_at') || $errors->has('amount') || $errors->has('note')) show @endif border-bottom">
                    <div class="card-body bg-light bg-opacity-50">
                        <form method="POST" action="{{ route('admin.businesses.subscriptions.store', $business) }}" class="row g-3">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label">Plan <span class="text-danger">*</span></label>
                                <select name="plan_id" class="form-select" required>
                                    <option value="">Seçin...</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" @selected(old('plan_id', $business->subscription_plan_id) == $plan->id)>
                                            {{ $plan->name }} — {{ number_format($plan->price_monthly, 0, ',', '.') }} ₺/ay
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Dönem <span class="text-danger">*</span></label>
                                <select name="period" class="form-select" required>
                                    <option value="monthly" @selected(old('period', 'monthly') === 'monthly')>Aylık</option>
                                    <option value="yearly" @selected(old('period') === 'yearly')>Yıllık</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tutar (₺)</label>
                                <input type="number" name="amount" class="form-control" min="0" step="0.01" placeholder="Boş = plan fiyatı" value="{{ old('amount') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Başlangıç</label>
                                <input type="date" name="starts_at" class="form-control" value="{{ old('starts_at', now()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bitiş</label>
                                <input type="date" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
                                <small class="text-muted">Boş bırakılırsa döneme göre otomatik hesaplanır.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Not</label>
                                <input type="text" name="note" class="form-control" maxlength="500" placeholder="Örn: Havale ile ödendi" value="{{ old('note') }}">
                            </div>
                            <div class="col-12">
                                @foreach($errors->all() as $error)
                                    <div class="text-danger fs-13">{{ $error }}</div>
                                @endforeach
                                <button type="submit" class="btn btn-success" onclick="return confirm('Manuel abonelik eklensin mi? Mevcut aktif abonelik sonlandırılır.')">
                                    Aboneliği Tanımla
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr><th>Plan</th><th>Dönem</th><th>Tutar</th><th>Aralık</th><th>Durum</th></tr>
                        </thead>
                        <tbody>
                        @forelse($business->subscriptions->sortByDesc('created_at') as $subscription)
                            <tr>
                                <td>{{ $subscription->plan?->name }}</td>
                                <td>{{ $subscription->period === 'yearly' ? 'Yıllık' : 'Aylık' }}</td>
                                <td>{{ number_format($subscription->price, 2, ',', '.') }} {{ $subscription->currency }}</td>
                                <td><small>{{ $subscription->starts_at->format('d.m.Y') }} — {{ $subscription->ends_at->format('d.m.Y') }}</small></td>
                                <td><span class="badge bg-soft-secondary text-secondary">{{ $subscription->status->label() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Abonelik kaydı yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
