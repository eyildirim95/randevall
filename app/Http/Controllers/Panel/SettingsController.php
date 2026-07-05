<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\Messaging\WhatsAppManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Business $business): View
    {
        return view('panel.settings', compact('business'));
    }

    public function update(Business $business, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:2000'],
            'instagram' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'url', 'max:190'],
            'logo' => ['nullable', 'image', 'max:2048'],

            'slot_interval_minutes' => ['required', 'integer', Rule::in([10, 15, 20, 30, 60])],
            'min_notice_minutes' => ['required', 'integer', 'between:0,10080'],
            'max_advance_days' => ['required', 'integer', 'between:1,365'],
            'online_booking_enabled' => ['nullable', 'boolean'],
            'auto_confirm_online' => ['nullable', 'boolean'],

            'loyalty_enabled' => ['nullable', 'boolean'],
            'loyalty_points_per_visit' => ['required', 'integer', 'between:0,1000'],
            'loyalty_redeem_threshold' => ['required', 'integer', 'between:0,100000'],
            'loyalty_reward_description' => ['nullable', 'string', 'max:190'],

            'whatsapp_enabled' => ['nullable', 'boolean'],
            'birthday_greeting_enabled' => ['nullable', 'boolean'],
            'email_notifications_enabled' => ['nullable', 'boolean'],
            'reminder_hours_before' => ['required', 'integer', 'between:0,168'],
        ]);

        foreach (['online_booking_enabled', 'auto_confirm_online', 'loyalty_enabled', 'whatsapp_enabled', 'birthday_greeting_enabled', 'email_notifications_enabled'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($data['logo']);

        $business->update($data);

        return back()->with('success', 'Ayarlar kaydedildi.');
    }

    public function testWhatsApp(Business $business, Request $request, WhatsAppManager $whatsapp): RedirectResponse
    {
        $data = $request->validate([
            'test_phone' => ['required', 'string', 'max:30'],
        ]);

        $result = $whatsapp->send(
            $business,
            $data['test_phone'],
            'BooKıbrıs test mesajı — WhatsApp entegrasyonunuz çalışıyor! ✅',
            'system',
        );

        return $result->success
            ? back()->with('success', 'Test mesajı gönderildi.')
            : back()->withErrors(['test_phone' => 'Gönderim başarısız: '.$result->error]);
    }
}
