<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\Messaging\SmsManager;
use App\Services\Messaging\WhatsAppManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /** Duzenlenebilir sistem ayarlari ve varsayilanlari. */
    private const KEYS = [
        'notification_email' => '',
        'bank_name' => '',
        'bank_iban' => '',
        'bank_account_holder' => '',
        'paytr_merchant_id' => '',
        'paytr_merchant_key' => '',
        'paytr_merchant_salt' => '',
        'landing_whatsapp_number' => '',
        'landing_phone' => '',
        'whatsapp_provider' => 'log',
        'whatsapp_phone_number_id' => '',
        'sms_provider' => 'log',
        'sms_username' => '',
        'sms_source_addr' => '',
    ];

    public function edit(): View
    {
        $settings = collect(self::KEYS)
            ->map(fn ($default, $key) => SystemSetting::get($key, $default));

        // API anahtari sifreli saklanir; sadece dolu/bos bilgisi gosterilir
        $settings['whatsapp_key_set'] = SystemSetting::get('whatsapp_api_key', '') !== '';
        $settings['sms_password_set'] = SystemSetting::get('sms_password', '') !== '';

        return view('superadmin.settings', ['settings' => $settings]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notification_email' => ['nullable', 'email', 'max:190'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'bank_iban' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:120'],
            'paytr_merchant_id' => ['nullable', 'string', 'max:60'],
            'paytr_merchant_key' => ['nullable', 'string', 'max:120'],
            'paytr_merchant_salt' => ['nullable', 'string', 'max:120'],
            'landing_whatsapp_number' => ['nullable', 'string', 'max:30'],
            'landing_phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_provider' => ['required', Rule::in(['meta', 'twilio', 'log'])],
            'whatsapp_phone_number_id' => ['nullable', 'string', 'max:120'],
            'whatsapp_api_key' => ['nullable', 'string', 'max:500'],
            'sms_provider' => ['required', Rule::in(['verimor', 'log'])],
            'sms_username' => ['nullable', 'string', 'max:30'],
            'sms_source_addr' => ['nullable', 'string', 'max:11'],
            'sms_password' => ['nullable', 'string', 'max:120'],
        ]);

        $apiKey = $data['whatsapp_api_key'] ?? null;
        unset($data['whatsapp_api_key']);

        $smsPassword = $data['sms_password'] ?? null;
        unset($data['sms_password']);

        foreach ($data as $key => $value) {
            SystemSetting::set($key, $value ?? '');
        }

        if ($apiKey) {
            WhatsAppManager::storeApiKey($apiKey);
        }

        if ($smsPassword) {
            SmsManager::storePassword($smsPassword);
        }

        return back()->with('success', 'Sistem ayarları kaydedildi.');
    }

    public function testWhatsApp(Request $request, WhatsAppManager $whatsapp): RedirectResponse
    {
        $data = $request->validate([
            'test_phone' => ['required', 'string', 'max:30'],
        ]);

        $result = $whatsapp->sendPlatformTest(
            $data['test_phone'],
            config('app.name').' platform testi — merkezi WhatsApp API bağlantınız çalışıyor! ✅',
        );

        return $result->success
            ? back()->with('success', 'Test mesajı gönderildi.')
            : back()->withErrors(['test_phone' => 'Gönderim başarısız: '.$result->error]);
    }

    public function testSms(Request $request, SmsManager $sms): RedirectResponse
    {
        $data = $request->validate([
            'test_phone' => ['required', 'string', 'max:30'],
        ]);

        $result = $sms->sendPlatformTest(
            $data['test_phone'],
            config('app.name').' platform testi — Verimor SMS bağlantınız çalışıyor!',
        );

        return $result->success
            ? back()->with('success', 'Test SMS gönderildi.')
            : back()->withErrors(['test_phone' => 'Gönderim başarısız: '.$result->error]);
    }
}
