@extends('layouts.vertical', ['title' => 'Randevu Detayı', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Randevu Bilgileri</h4>
                    <span class="badge bg-soft-{{ $appointment->status->color() }} text-{{ $appointment->status->color() }} fs-12">{{ $appointment->status->label() }}</span>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <th class="text-muted" style="width: 160px">Müşteri</th>
                            <td>
                                <a href="{{ route('panel.customers.show', [$business, $appointment->customer]) }}">{{ $appointment->customer->name }}</a>
                                <small class="text-muted d-block">{{ $appointment->customer->phone }}</small>
                            </td>
                        </tr>
                        <tr><th class="text-muted">Tarih</th><td>{{ $appointment->starts_at->translatedFormat('d F Y l') }}</td></tr>
                        <tr><th class="text-muted">Saat</th><td>{{ $appointment->starts_at->format('H:i') }} — {{ $appointment->ends_at->format('H:i') }}</td></tr>
                        <tr><th class="text-muted">Hizmet</th><td>{{ $appointment->service?->name ?? '—' }}</td></tr>
                        <tr><th class="text-muted">Personel</th><td>{{ $appointment->staff?->name ?? '—' }}</td></tr>
                        <tr><th class="text-muted">Ücret</th><td>{{ number_format($appointment->price, 2, ',', '.') }} {{ $business->currency }}</td></tr>
                        <tr><th class="text-muted">Kaynak</th><td>{{ $appointment->source === 'online' ? 'Online rezervasyon' : 'Panel' }}</td></tr>
                        @if($appointment->notes)
                            <tr><th class="text-muted">Not</th><td>{{ $appointment->notes }}</td></tr>
                        @endif
                        @if($appointment->cancellation_reason)
                            <tr><th class="text-muted">İptal Nedeni</th><td>{{ $appointment->cancellation_reason }}</td></tr>
                        @endif
                        <tr>
                            <th class="text-muted">Müşteri Linki</th>
                            <td>
                                <code class="user-select-all">{{ route('appointment.public.show', $appointment->public_token) }}</code>
                            </td>
                        </tr>
                    </table>

                    @if($appointment->isCancellable() || $appointment->status->value === 'pending')
                        <hr>
                        <div class="d-flex flex-wrap gap-2">
                            @if($appointment->status->value === 'pending')
                                <form method="POST" action="{{ route('panel.appointments.status', [$business, $appointment]) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button class="btn btn-primary btn-sm"><i class="ri-check-line me-1"></i>Onayla</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('panel.appointments.status', [$business, $appointment]) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button class="btn btn-success btn-sm"><i class="ri-checkbox-circle-line me-1"></i>Tamamlandı</button>
                            </form>
                            <form method="POST" action="{{ route('panel.appointments.status', [$business, $appointment]) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="no_show">
                                <button class="btn btn-secondary btn-sm"><i class="ri-user-unfollow-line me-1"></i>Gelmedi</button>
                            </form>
                            <form method="POST" action="{{ route('panel.appointments.status', [$business, $appointment]) }}"
                                  onsubmit="return confirm('Randevu iptal edilsin mi? Müşteriye bildirim gönderilir.')">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button class="btn btn-danger btn-sm"><i class="ri-close-circle-line me-1"></i>İptal Et</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Duzenleme --}}
            @if(in_array($appointment->status->value, ['pending', 'confirmed'], true))
                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Randevuyu Düzenle</h4></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('panel.appointments.update', [$business, $appointment]) }}" class="row g-2">
                            @csrf @method('PUT')
                            <div class="col-md-6">
                                <label class="form-label">Hizmet</label>
                                <select name="service_id" class="form-select" required>
                                    @foreach(\App\Models\Service::query()->ordered()->get() as $service)
                                        <option value="{{ $service->id }}" @selected($appointment->service_id === $service->id)>{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Personel</label>
                                <select name="staff_id" class="form-select" required>
                                    @foreach(\App\Models\Staff::query()->ordered()->get() as $staff)
                                        <option value="{{ $staff->id }}" @selected($appointment->staff_id === $staff->id)>{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tarih &amp; Saat</label>
                                <input type="datetime-local" name="starts_at" class="form-control" required
                                       value="{{ $appointment->starts_at->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ücret ({{ $business->currency }})</label>
                                <input type="number" name="price" class="form-control" step="0.01" min="0" required value="{{ $appointment->price }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Not</label>
                                <textarea name="notes" class="form-control" rows="2" maxlength="1000">{{ $appointment->notes }}</textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary">Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-xl-5">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Durum Geçmişi</h4></div>
                <div class="card-body">
                    @forelse($appointment->statusLogs->sortByDesc('created_at') as $log)
                        <div class="d-flex gap-2 py-2 border-bottom">
                            <i class="ri-history-line text-muted"></i>
                            <div>
                                <div class="fw-medium">
                                    @if($log->from_status){{ \App\Enums\AppointmentStatus::from($log->from_status)->label() }} → @endif
                                    {{ \App\Enums\AppointmentStatus::from($log->to_status)->label() }}
                                </div>
                                <small class="text-muted">
                                    {{ $log->created_at->format('d.m.Y H:i') }}
                                    @if($log->changedBy) · {{ $log->changedBy->name }} @endif
                                    @if($log->note) · {{ $log->note }} @endif
                                </small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Kayıt yok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
