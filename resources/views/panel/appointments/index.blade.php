@extends('layouts.vertical', ['title' => 'Randevular', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Ara</label>
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Müşteri adı / telefon" value="{{ $filters['q'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Durum</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Tümü</option>
                                @foreach(\App\Enums\AppointmentStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Personel</label>
                            <select name="staff_id" class="form-select form-select-sm">
                                <option value="">Tümü</option>
                                @foreach($staffList as $staff)
                                    <option value="{{ $staff->id }}" @selected(($filters['staff_id'] ?? '') == $staff->id)>{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Tarih</label>
                            <input type="date" name="date" class="form-control form-control-sm" value="{{ $filters['date'] ?? '' }}">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary">Filtrele</button>
                            <a href="{{ route('panel.appointments.index', $business) }}" class="btn btn-sm btn-light">Sıfırla</a>
                            <a href="{{ route('panel.calendar', $business) }}" class="btn btn-sm btn-primary ms-auto"><i class="ri-add-line me-1"></i>Randevu Oluştur</a>
                            <a href="{{ route('panel.calendar', $business) }}" class="btn btn-sm btn-soft-primary"><i class="ri-calendar-2-line me-1"></i>Takvim</a>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Tarih / Saat</th>
                            <th>Müşteri</th>
                            <th>Hizmet</th>
                            <th>Personel</th>
                            <th>Ücret</th>
                            <th>Kaynak</th>
                            <th>Durum</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($appointments as $appointment)
                            <tr>
                                <td class="fw-medium">{{ $appointment->starts_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div>{{ $appointment->customer->name }}</div>
                                    <small class="text-muted">{{ $appointment->customer->phone }}</small>
                                </td>
                                <td>{{ $appointment->service?->name ?? '—' }}</td>
                                <td>{{ $appointment->staff?->name ?? '—' }}</td>
                                <td>{{ number_format($appointment->price, 2, ',', '.') }} {{ $business->currency }}</td>
                                <td>
                                    <span class="badge bg-soft-{{ $appointment->source === 'online' ? 'info' : 'secondary' }} text-{{ $appointment->source === 'online' ? 'info' : 'secondary' }}">
                                        {{ $appointment->source === 'online' ? 'Online' : 'Panel' }}
                                    </span>
                                </td>
                                <td><span class="badge bg-soft-{{ $appointment->status->color() }} text-{{ $appointment->status->color() }}">{{ $appointment->status->label() }}</span></td>
                                <td class="text-center">
                                    <a href="{{ route('panel.appointments.show', [$business, $appointment]) }}" class="btn btn-sm btn-soft-primary"><i class="ri-eye-line"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Kayıt bulunamadı.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    {{ $appointments->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
