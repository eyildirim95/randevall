<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Mail\DemoRequestReceivedMail;
use App\Models\DemoRequest;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DemoRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'business_name' => ['nullable', 'string', 'max:150'],
            'sector' => ['nullable', 'string', 'max:80'],
            'phone' => ['required', 'string', 'min:10', 'max:30'],
            'email' => ['nullable', 'email', 'max:190'],
            'message' => ['nullable', 'string', 'max:1000'],
            'website' => ['prohibited'], // honeypot: botlar doldurur, insanlar gormez
        ]);

        unset($data['website']);

        $demoRequest = DemoRequest::create([
            ...$data,
            'ip_address' => $request->ip(),
        ]);

        // Super admin'e e-posta bildirimi
        $adminEmail = SystemSetting::get('notification_email');

        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->queue(new DemoRequestReceivedMail($demoRequest));
            } catch (\Throwable $e) {
                Log::warning('Demo talebi maili gonderilemedi', ['error' => $e->getMessage()]);
            }
        }

        return back()->with('demo_success', 'Talebiniz alındı! En kısa sürede sizinle iletişime geçeceğiz.');
    }
}
