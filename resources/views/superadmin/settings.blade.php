@extends('layouts.vertical', ['title' => 'Sistem Ayarları', 'subTitle' => 'Süper Admin'])

@section('content')
    <div class="row">
        <div class="col-xl-7">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf

                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Bildirimler</h4></div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bildirim E-postası</label>
                            <input type="email" name="notification_email" class="form-control" value="{{ old('notification_email', $settings['notification_email']) }}" placeholder="admin@reserup.com">
                            <small class="text-muted">Demo talepleri buraya bildirilir.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Landing Telefonu</label>
                            <input type="text" name="landing_phone" class="form-control" value="{{ old('landing_phone', $settings['landing_phone']) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Landing WhatsApp</label>
                            <input type="text" name="landing_whatsapp_number" class="form-control" value="{{ old('landing_whatsapp_number', $settings['landing_whatsapp_number']) }}" placeholder="+90533...">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">Havale / EFT Bilgileri</h4></div>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Banka Adı</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $settings['bank_name']) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hesap Sahibi</label>
                            <input type="text" name="bank_account_holder" class="form-control" value="{{ old('bank_account_holder', $settings['bank_account_holder']) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">IBAN</label>
                            <input type="text" name="bank_iban" class="form-control" value="{{ old('bank_iban', $settings['bank_iban']) }}">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">WhatsApp (Merkezi API — tüm işletmeler bu hesaptan gönderir)</h4></div>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Sağlayıcı</label>
                            <select name="whatsapp_provider" class="form-select">
                                <option value="meta" @selected($settings['whatsapp_provider'] === 'meta')>Meta Cloud API</option>
                                <option value="twilio" @selected($settings['whatsapp_provider'] === 'twilio')>Twilio</option>
                                <option value="log" @selected($settings['whatsapp_provider'] === 'log')>Test (log)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">API Anahtarı / Token</label>
                            <input type="password" name="whatsapp_api_key" class="form-control" maxlength="500" autocomplete="new-password"
                                   placeholder="{{ $settings['whatsapp_key_set'] ? '••••••• (değiştirmek için yazın)' : 'Access token' }}">
                            <small class="text-muted">Şifreli saklanır. Twilio için "SID:TOKEN" biçiminde girin.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefon Numarası ID / Gönderen</label>
                            <input type="text" name="whatsapp_phone_number_id" class="form-control" maxlength="120" value="{{ old('whatsapp_phone_number_id', $settings['whatsapp_phone_number_id']) }}">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h4 class="card-title mb-0">PayTR (Kredi Kartı)</h4></div>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Merchant ID</label>
                            <input type="text" name="paytr_merchant_id" class="form-control" value="{{ old('paytr_merchant_id', $settings['paytr_merchant_id']) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Merchant Key</label>
                            <input type="password" name="paytr_merchant_key" class="form-control" value="{{ old('paytr_merchant_key', $settings['paytr_merchant_key']) }}" autocomplete="new-password">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Merchant Salt</label>
                            <input type="password" name="paytr_merchant_salt" class="form-control" value="{{ old('paytr_merchant_salt', $settings['paytr_merchant_salt']) }}" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary px-4 mb-3">Kaydet</button>
            </form>

            {{-- Merkezi WhatsApp testi (ayri form) --}}
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Merkezi WhatsApp Testi</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.whatsapp-test') }}" class="row g-2">
                        @csrf
                        <div class="col-8">
                            <input type="tel" name="test_phone" class="form-control" required maxlength="30" placeholder="+90 5xx xxx xx xx">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-soft-success w-100"><i class="ri-whatsapp-line me-1"></i>Test Gönder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
