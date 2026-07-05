@extends('layouts.vertical', ['title' => 'Personel', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Personel Listesi</h4>
                    <a href="{{ route('panel.staff.create', $business) }}" class="btn btn-sm btn-primary">
                        <i class="ri-user-add-line me-1"></i>Yeni Personel
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Personel</th>
                            <th>İletişim</th>
                            <th>Hizmetler</th>
                            <th>Panel Girişi</th>
                            <th>Online Randevu</th>
                            <th>Durum</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($staffList as $staff)
                            <tr>
                                <td>
                                    <span class="badge me-1" style="background-color: {{ $staff->color }}">&nbsp;</span>
                                    <span class="fw-medium">{{ $staff->name }}</span>
                                    @if($staff->title)
                                        <small class="text-muted d-block ms-3">{{ $staff->title }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $staff->phone ?? '—' }}
                                    @if($staff->email)<small class="text-muted d-block">{{ $staff->email }}</small>@endif
                                </td>
                                <td>
                                    @forelse($staff->services->take(3) as $service)
                                        <span class="badge bg-soft-secondary text-secondary">{{ $service->name }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                    @if($staff->services->count() > 3)
                                        <span class="badge bg-soft-info text-info">+{{ $staff->services->count() - 3 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-soft-{{ $staff->user_id ? 'success' : 'secondary' }} text-{{ $staff->user_id ? 'success' : 'secondary' }}">
                                        {{ $staff->user_id ? 'Var' : 'Yok' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-soft-{{ $staff->accepts_online_booking ? 'success' : 'secondary' }} text-{{ $staff->accepts_online_booking ? 'success' : 'secondary' }}">
                                        {{ $staff->accepts_online_booking ? 'Açık' : 'Kapalı' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-soft-{{ $staff->is_active ? 'success' : 'danger' }} text-{{ $staff->is_active ? 'success' : 'danger' }}">
                                        {{ $staff->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-soft-info copy-ics" title="Takvim linkini kopyala (Google/Apple Takvim)"
                                            data-url="{{ route('calendar.feed', $staff->icsToken()) }}"><i class="ri-calendar-line"></i></button>
                                    <a href="{{ route('panel.working-hours.edit', [$business, 'staff_id' => $staff->id]) }}" class="btn btn-sm btn-soft-secondary" title="Çalışma saatleri"><i class="ri-time-line"></i></a>
                                    <a href="{{ route('panel.staff.edit', [$business, $staff]) }}" class="btn btn-sm btn-soft-primary"><i class="ri-pencil-line"></i></a>
                                    <form method="POST" action="{{ route('panel.staff.destroy', [$business, $staff]) }}" class="d-inline"
                                          onsubmit="return confirm('Personel silinsin mi? Mevcut randevuları personelsiz kalır.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Henüz personel eklenmemiş.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="ri-calendar-line me-1"></i>
                        <strong>Takvim senkronu:</strong> Personelin yanındaki takvim butonuyla linki kopyalayın;
                        Google Takvim → Diğer takvimler → "+" → <em>URL ile</em> bölümüne yapıştırın. Randevular otomatik görünür.
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script-bottom')
    <script>
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.copy-ics');
            if (!btn) return;

            navigator.clipboard.writeText(btn.dataset.url).then(() => {
                const icon = btn.querySelector('i');
                icon.className = 'ri-check-line';
                setTimeout(() => { icon.className = 'ri-calendar-line'; }, 1500);
            });
        });
    </script>
@endsection
