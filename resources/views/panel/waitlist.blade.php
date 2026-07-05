@extends('layouts.vertical', ['title' => 'Bekleme Listesi', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Bekleme Listesi</h4>
                    <small class="text-muted">Dolu günlerde müşteriler listeye kaydolur; randevu iptal olduğunda sıradakilere otomatik WhatsApp gider.</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Müşteri</th>
                            <th>Telefon</th>
                            <th>İstenen Gün</th>
                            <th>Hizmet</th>
                            <th>Personel</th>
                            <th>Durum</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($entries as $entry)
                            <tr class="{{ $entry->preferred_date->isPast() ? 'text-muted' : '' }}">
                                <td class="fw-medium">{{ $entry->customer_name }}</td>
                                <td><a href="tel:{{ $entry->customer_phone }}">{{ $entry->customer_phone }}</a></td>
                                <td>{{ $entry->preferred_date->translatedFormat('d F Y l') }}</td>
                                <td>{{ $entry->service?->name ?? 'Farketmez' }}</td>
                                <td>{{ $entry->staff?->name ?? 'Farketmez' }}</td>
                                <td>
                                    @php
                                        $map = ['waiting' => ['warning', 'Bekliyor'], 'notified' => ['success', 'Haber Verildi'], 'removed' => ['secondary', 'Kaldırıldı']];
                                        [$color, $label] = $map[$entry->status] ?? ['secondary', $entry->status];
                                    @endphp
                                    <span class="badge bg-soft-{{ $color }} text-{{ $color }}">{{ $label }}</span>
                                    @if($entry->notified_at)
                                        <small class="text-muted d-block">{{ $entry->notified_at->format('d.m.Y H:i') }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('panel.waitlist.destroy', [$business, $entry]) }}"
                                          onsubmit="return confirm('Kayıt silinsin mi?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Bekleme listesi boş.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $entries->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
