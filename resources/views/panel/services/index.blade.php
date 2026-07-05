@extends('layouts.vertical', ['title' => 'Hizmetler', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Hizmet Listesi</h4>
                    <a href="{{ route('panel.services.create', $business) }}" class="btn btn-sm btn-primary">
                        <i class="ri-add-line me-1"></i>Yeni Hizmet
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Hizmet</th>
                            <th>Süre</th>
                            <th>Ücret</th>
                            <th>Personel</th>
                            <th>Online</th>
                            <th>Durum</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($services as $service)
                            <tr>
                                <td>
                                    <span class="badge me-1" style="background-color: {{ $service->color }}">&nbsp;</span>
                                    <span class="fw-medium">{{ $service->name }}</span>
                                    @if($service->category)
                                        <small class="text-muted d-block ms-3">{{ $service->category }}</small>
                                    @endif
                                </td>
                                <td>{{ $service->duration_minutes }} dk @if($service->buffer_minutes) <small class="text-muted">(+{{ $service->buffer_minutes }} dk ara)</small> @endif</td>
                                <td>{{ number_format($service->price, 2, ',', '.') }} {{ $business->currency }}</td>
                                <td>
                                    @forelse($service->staff as $staff)
                                        <span class="badge bg-soft-secondary text-secondary">{{ $staff->name }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                                <td>
                                    <span class="badge bg-soft-{{ $service->online_bookable ? 'success' : 'secondary' }} text-{{ $service->online_bookable ? 'success' : 'secondary' }}">
                                        {{ $service->online_bookable ? 'Açık' : 'Kapalı' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-soft-{{ $service->is_active ? 'success' : 'danger' }} text-{{ $service->is_active ? 'success' : 'danger' }}">
                                        {{ $service->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('panel.services.edit', [$business, $service]) }}" class="btn btn-sm btn-soft-primary"><i class="ri-pencil-line"></i></a>
                                    <form method="POST" action="{{ route('panel.services.destroy', [$business, $service]) }}" class="d-inline"
                                          onsubmit="return confirm('Hizmet silinsin mi?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Henüz hizmet eklenmemiş.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
