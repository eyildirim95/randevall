@extends('layouts.vertical', ['title' => 'İşletme Ayarları', 'subTitle' => $business->name])

@section('content')
    <form method="POST" action="{{ route('panel.settings.update', $business) }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-6">
                {{-- Isletme bilgileri --}}
                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">İşletme Bilgileri</h4></div>
                    <div class="card-body row g-3">
                        <div class="col-md-8">
                            <label class="form-label">İşletme Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="150" value="{{ old('name', $business->name) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefon</label>
                            <input type="tel" name="phone" class="form-control" maxlength="30" value="{{ old('phone', $business->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="email" class="form-control" maxlength="190" value="{{ old('email', $business->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Şehir</label>
                            <input type="text" name="city" class="form-control" maxlength="80" value="{{ old('city', $business->city) }}" placeholder="İstanbul, Ankara, İzmir...">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adres</label>
                            <input type="text" name="address" class="form-control" maxlength="255" value="{{ old('address', $business->address) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Instagram</label>
                            <input type="text" name="instagram" class="form-control" maxlength="120" value="{{ old('instagram', $business->instagram) }}" placeholder="@kullaniciadi">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Web Sitesi</label>
                            <input type="url" name="website" class="form-control" maxlength="190" value="{{ old('website', $business->website) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            @if($business->logo_path)
                                <small class="text-muted">Mevcut: <img src="{{ $business->logo_url }}" height="24" alt="logo"></small>
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="form-label">Açıklama <small class="text-muted">(rezervasyon sayfasında görünür)</small></label>
                            <textarea name="description" class="form-control" rows="2" maxlength="2000">{{ old('description', $business->description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Sadakat --}}
                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Sadık Müşteri Sistemi</h4></div>
                    <div class="card-body row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="loyalty_enabled" name="loyalty_enabled" value="1" @checked(old('loyalty_enabled', $business->loyalty_enabled))>
                                <label class="form-check-label" for="loyalty_enabled">Sadakat puanı sistemi aktif</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ziyaret başına puan</label>
                            <input type="number" name="loyalty_points_per_visit" class="form-control" min="0" max="1000" value="{{ old('loyalty_points_per_visit', $business->loyalty_points_per_visit) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ödül eşiği (puan)</label>
                            <input type="number" name="loyalty_redeem_threshold" class="form-control" min="0" value="{{ old('loyalty_redeem_threshold', $business->loyalty_redeem_threshold) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ödül açıklaması</label>
                            <input type="text" name="loyalty_reward_description" class="form-control" maxlength="190" value="{{ old('loyalty_reward_description', $business->loyalty_reward_description) }}" placeholder="Ücretsiz tıraş">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                {{-- Rezervasyon ayarlari --}}
                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Online Rezervasyon</h4></div>
                    <div class="card-body row g-3">
                        <div class="col-12">
                            <div class="alert alert-info py-2 mb-0">
                                Rezervasyon linkiniz:
                                <a href="{{ route('booking.show', $business) }}" target="_blank" class="fw-bold">{{ route('booking.show', $business) }}</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="online_booking_enabled" name="online_booking_enabled" value="1" @checked(old('online_booking_enabled', $business->online_booking_enabled))>
                                <label class="form-check-label" for="online_booking_enabled">Online randevu açık</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="auto_confirm_online" name="auto_confirm_online" value="1" @checked(old('auto_confirm_online', $business->auto_confirm_online))>
                                <label class="form-check-label" for="auto_confirm_online">Otomatik onayla</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slot aralığı</label>
                            <select name="slot_interval_minutes" class="form-select">
                                @foreach([10, 15, 20, 30, 60] as $interval)
                                    <option value="{{ $interval }}" @selected(old('slot_interval_minutes', $business->slot_interval_minutes) == $interval)>{{ $interval }} dk</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Min. ön süre (dk)</label>
                            <input type="number" name="min_notice_minutes" class="form-control" min="0" max="10080" value="{{ old('min_notice_minutes', $business->min_notice_minutes) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maks. ileri gün</label>
                            <input type="number" name="max_advance_days" class="form-control" min="1" max="365" value="{{ old('max_advance_days', $business->max_advance_days) }}">
                        </div>
                    </div>
                </div>

                {{-- Bildirimler --}}
                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Bildirimler (WhatsApp, SMS &amp; E-posta)</h4></div>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="whatsapp_enabled" name="whatsapp_enabled" value="1" @checked(old('whatsapp_enabled', $business->whatsapp_enabled))>
                                <label class="form-check-label" for="whatsapp_enabled">WhatsApp bildirimleri</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="sms_enabled" name="sms_enabled" value="1" @checked(old('sms_enabled', $business->sms_enabled))>
                                <label class="form-check-label" for="sms_enabled">SMS bildirimleri</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="email_notifications_enabled" name="email_notifications_enabled" value="1" @checked(old('email_notifications_enabled', $business->email_notifications_enabled))>
                                <label class="form-check-label" for="email_notifications_enabled">E-posta bildirimleri</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hatırlatma (saat önce)</label>
                            <input type="number" name="reminder_hours_before" class="form-control" min="0" max="168" value="{{ old('reminder_hours_before', $business->reminder_hours_before) }}">
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info py-2 mb-0">
                                <i class="ri-information-line me-1"></i>
                                WhatsApp ve SMS gönderimi Randevall'un merkezi altyapısı üzerinden yapılır; API kurulumu gerekmez.
                                @if($business->plan && $business->plan->whatsapp_quota_monthly > 0)
                                    Planınıza dahil aylık mesaj kotası: <strong>{{ $business->plan->whatsapp_quota_monthly }}</strong>
                                    (bu ay kullanılan: <strong>{{ \App\Services\Messaging\MessageQuota::usedThisMonth($business) }}</strong>).
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-primary px-4">Ayarları Kaydet</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Hesap --}}
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Hesabınız</h4></div>
                <div class="card-body">
                    <p class="mb-2"><strong>{{ auth()->user()->name }}</strong><br><span class="text-muted">{{ auth()->user()->email }}</span></p>
                    @if (auth()->user()->hasVerifiedEmail())
                        <div class="alert alert-success py-2 mb-0">
                            <i class="ri-checkbox-circle-line me-1"></i> E-posta adresiniz doğrulandı.
                        </div>
                    @else
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="ri-mail-check-line me-1"></i>
                            E-posta adresinizi henüz doğrulamadınız. Güvenlik için lütfen doğrulayın.
                        </div>
                        <form method="POST" action="{{ route('verification.send') }}" class="d-flex flex-wrap gap-2">
                            @csrf
                            <button class="btn btn-sm btn-warning">Doğrulama e-postasını gönder</button>
                            <a href="{{ route('verification.notice') }}" class="btn btn-sm btn-light">Doğrulama sayfası</a>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Mesaj testleri --}}
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">WhatsApp Test</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('panel.settings.whatsapp-test', $business) }}" class="row g-2">
                        @csrf
                        <div class="col-8">
                            <input type="tel" name="test_phone" class="form-control" required maxlength="30" placeholder="05xx xxx xx xx">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-soft-success w-100"><i class="ri-whatsapp-line me-1"></i>Test Gönder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">SMS Test</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('panel.settings.sms-test', $business) }}" class="row g-2">
                        @csrf
                        <div class="col-8">
                            <input type="tel" name="test_phone" class="form-control" required maxlength="30" placeholder="05xx xxx xx xx">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-soft-primary w-100"><i class="ri-message-2-line me-1"></i>Test SMS</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
