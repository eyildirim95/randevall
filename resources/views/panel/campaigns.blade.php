@extends('layouts.vertical', ['title' => 'Kampanyalar', 'subTitle' => $business->name])

@section('content')
    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Yeni Kampanya</h4></div>
                <div class="card-body">
                    @if($quota > 0)
                        <div class="alert alert-info py-2">
                            Aylık WhatsApp kotanız: <strong>{{ $usedThisMonth }} / {{ $quota }}</strong>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('panel.campaigns.store', $business) }}" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Kampanya Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="120" placeholder="Kış indirimi duyurusu" value="{{ old('name') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Hedef Kitle <span class="text-danger">*</span></label>
                            <select name="audience" class="form-select" required>
                                @foreach(\App\Models\Campaign::audiences() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('audience') === $key)>
                                        {{ $label }} ({{ $audienceCounts[$key] }} kişi)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mesaj <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="5" required maxlength="1000"
                                      placeholder="Merhaba {ad}! Bu hafta tüm hizmetlerde %20 indirim...">{{ old('message') }}</textarea>
                            <small class="text-muted"><code>{ad}</code> yazdığınız yere müşterinin adı gelir.</small>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100" onclick="return confirm('Kampanya seçili kitleye hemen gönderilecek. Onaylıyor musunuz?')">
                                <i class="ri-send-plane-line me-1"></i>Gönder
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Kampanya Geçmişi</h4></div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Kampanya</th>
                            <th>Kitle</th>
                            <th>Gönderilen</th>
                            <th>Başarısız</th>
                            <th>Durum</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($campaigns as $campaign)
                            <tr>
                                <td>
                                    <span class="fw-medium">{{ $campaign->name }}</span>
                                    <small class="text-muted d-block">{{ Str::limit($campaign->message, 70) }}</small>
                                </td>
                                <td><small>{{ \App\Models\Campaign::audiences()[$campaign->audience] ?? $campaign->audience }}</small></td>
                                <td class="text-success fw-semibold">{{ $campaign->sent_count }}</td>
                                <td class="{{ $campaign->failed_count ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $campaign->failed_count }}</td>
                                <td>
                                    @php
                                        $map = ['draft' => ['secondary', 'Taslak'], 'sending' => ['warning', 'Gönderiliyor'], 'sent' => ['success', 'Gönderildi']];
                                        [$color, $label] = $map[$campaign->status] ?? ['secondary', $campaign->status];
                                    @endphp
                                    <span class="badge bg-soft-{{ $color }} text-{{ $color }}">{{ $label }}</span>
                                    @if($campaign->sent_at)
                                        <small class="text-muted d-block">{{ $campaign->sent_at->format('d.m.Y H:i') }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('panel.campaigns.destroy', [$business, $campaign]) }}"
                                          onsubmit="return confirm('Kampanya kaydı silinsin mi?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Henüz kampanya gönderilmedi.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $campaigns->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
