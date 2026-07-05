@extends('layouts.vertical', ['title' => 'Takvim Kapatma', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Yeni Kapatma</h4></div>
                <div class="card-body">
                    <p class="text-muted fs-13">Tatil, izin veya özel durumlarda takvimi kapatın; kapalı aralıkta online ve panel randevusu alınamaz.</p>
                    <form method="POST" action="{{ route('panel.closures.store', $business) }}" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Kapsam</label>
                            <select name="staff_id" class="form-select">
                                <option value="">Tüm işletme</option>
                                @foreach($staffList as $staff)
                                    <option value="{{ $staff->id }}">Sadece {{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Başlangıç <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="starts_at" class="form-control" required value="{{ old('starts_at') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bitiş <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="ends_at" class="form-control" required value="{{ old('ends_at') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Sebep</label>
                            <input type="text" name="reason" class="form-control" maxlength="190" placeholder="Tatil, izin, bakım..." value="{{ old('reason') }}">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100">Takvimi Kapat</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Kapatma Listesi</h4></div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Başlangıç</th>
                            <th>Bitiş</th>
                            <th>Kapsam</th>
                            <th>Sebep</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($closures as $closure)
                            <tr class="{{ $closure->ends_at->isPast() ? 'text-muted' : '' }}">
                                <td>{{ $closure->starts_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $closure->ends_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @if($closure->staff)
                                        <span class="badge bg-soft-info text-info">{{ $closure->staff->name }}</span>
                                    @else
                                        <span class="badge bg-soft-danger text-danger">Tüm işletme</span>
                                    @endif
                                </td>
                                <td>{{ $closure->reason ?? '—' }}</td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('panel.closures.destroy', [$business, $closure]) }}"
                                          onsubmit="return confirm('Kapatma kaldırılsın mı?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Kapatma kaydı yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $closures->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
