@extends('layouts.vertical', ['title' => $customer->name, 'subTitle' => 'Müşteri Profili'])

@section('content')
    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar-xl mx-auto mb-3">
                        <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-28 fw-bold">
                            {{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}
                        </span>
                    </div>
                    <h4 class="mb-1">{{ $customer->name }}</h4>
                    <p class="text-muted mb-2">{{ $customer->phone }} @if($customer->email) · {{ $customer->email }} @endif</p>

                    @if($customer->is_blacklisted)
                        <span class="badge bg-soft-danger text-danger">Kara Liste</span>
                    @endif

                    <div class="row text-center mt-3">
                        <div class="col-4">
                            <h4 class="mb-0">{{ $customer->total_appointments }}</h4>
                            <small class="text-muted">Randevu</small>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0">{{ number_format($customer->total_spent, 0, ',', '.') }}</h4>
                            <small class="text-muted">Harcama ({{ $business->currency }})</small>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0 text-warning">⭐ {{ $customer->loyalty_points }}</h4>
                            <small class="text-muted">Puan</small>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <a href="{{ route('panel.calendar', [$business, 'customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary"><i class="ri-calendar-check-line me-1"></i>Randevu Oluştur</a>
                        <a href="{{ route('panel.customers.edit', [$business, $customer]) }}" class="btn btn-sm btn-soft-primary"><i class="ri-pencil-line me-1"></i>Düzenle</a>
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', \App\Services\Messaging\PhoneNumber::e164($customer->phone)) }}" target="_blank" class="btn btn-sm btn-soft-success"><i class="ri-whatsapp-line me-1"></i>WhatsApp</a>
                    </div>
                </div>
            </div>

            @if($customer->notes)
                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Genel Not</h4></div>
                    <div class="card-body">{{ $customer->notes }}</div>
                </div>
            @endif

            {{-- Sadakat islemleri --}}
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Sadakat Puanı</h4></div>
                <div class="card-body">
                    @if($business->loyalty_enabled)
                        @if($business->loyalty_redeem_threshold > 0 && $customer->loyalty_points >= $business->loyalty_redeem_threshold)
                            <form method="POST" action="{{ route('panel.customers.points', [$business, $customer]) }}" class="mb-3">
                                @csrf
                                <input type="hidden" name="action" value="redeem">
                                <button class="btn btn-warning w-100">
                                    🎁 Ödül Kullan ({{ $business->loyalty_redeem_threshold }} puan)
                                    @if($business->loyalty_reward_description)
                                        — {{ $business->loyalty_reward_description }}
                                    @endif
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('panel.customers.points', [$business, $customer]) }}" class="row g-2">
                            @csrf
                            <input type="hidden" name="action" value="adjust">
                            <div class="col-5">
                                <input type="number" name="points" class="form-control form-control-sm" placeholder="+/- puan" required>
                            </div>
                            <div class="col-7">
                                <input type="text" name="description" class="form-control form-control-sm" placeholder="Açıklama">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-sm btn-soft-primary w-100">Puan Ekle / Çıkar</button>
                            </div>
                        </form>

                        <hr>
                        @forelse($customer->loyaltyTransactions as $lt)
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <small>{{ $lt->description ?? $lt->reason }}</small>
                                <small class="fw-bold {{ $lt->points > 0 ? 'text-success' : 'text-danger' }}">{{ $lt->points > 0 ? '+' : '' }}{{ $lt->points }}</small>
                            </div>
                        @empty
                            <p class="text-muted mb-0"><small>Puan hareketi yok.</small></p>
                        @endforelse
                    @else
                        <p class="text-muted mb-0">Sadakat sistemi kapalı. Ayarlardan açabilirsiniz.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Gelişim &amp; İşlem Takibi</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('panel.customers.records.store', [$business, $customer]) }}" class="mb-4">
                        @csrf
                        <label class="form-label">Yeni takip notu</label>
                        <textarea name="body" class="form-control" rows="4" required maxlength="5000"
                                  placeholder="Örn: 26 numaralı dişe dolgu yapıldı · Tansiyon ilacı dozu güncellendi · Matematikte konu tekrarı tamamlandı · Saç rengi #5/71 uygulandı">{{ old('body') }}</textarea>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">Tarihli kayıtlar kronolojik olarak saklanır. Tüm sektörlerde kullanılabilir.</small>
                            <button type="submit" class="btn btn-primary btn-sm">Not Ekle</button>
                        </div>
                    </form>

                    @forelse($customer->records as $record)
                        <div class="border rounded p-3 mb-3 {{ $loop->first ? 'border-primary border-opacity-25 bg-light bg-opacity-50' : '' }}">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <span class="fw-medium">{{ $record->created_at->translatedFormat('d F Y, H:i') }}</span>
                                    @if($record->author)
                                        <small class="text-muted d-block">{{ $record->author->name }}</small>
                                    @endif
                                </div>
                                @if(auth()->user()->canManage($business) || auth()->user()->isSuperAdmin())
                                    <form method="POST" action="{{ route('panel.customers.records.destroy', [$business, $customer, $record]) }}"
                                          onsubmit="return confirm('Bu takip notu silinsin mi?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" title="Sil"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                @endif
                            </div>
                            <div class="text-break" style="white-space: pre-wrap;">{{ $record->body }}</div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="ri-file-list-3-line fs-24 d-block mb-2"></i>
                            Henüz takip notu yok. Gelişme, tedavi veya işlem detaylarını yukarıdan ekleyebilirsiniz.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Randevu Geçmişi</h4></div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th>Tarih</th>
                            <th>Hizmet</th>
                            <th>Personel</th>
                            <th>Ücret</th>
                            <th>Durum</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($customer->appointments as $appointment)
                            <tr>
                                <td>
                                    <a href="{{ route('panel.appointments.show', [$business, $appointment]) }}">
                                        {{ $appointment->starts_at->format('d.m.Y H:i') }}
                                    </a>
                                </td>
                                <td>{{ $appointment->service?->name ?? '—' }}</td>
                                <td>{{ $appointment->staff?->name ?? '—' }}</td>
                                <td>{{ number_format($appointment->price, 2, ',', '.') }}</td>
                                <td><span class="badge bg-soft-{{ $appointment->status->color() }} text-{{ $appointment->status->color() }}">{{ $appointment->status->label() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Randevu geçmişi yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
