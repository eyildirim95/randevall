<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\SystemSetting;
use App\Services\Payments\Contracts\PaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayTR iFrame API entegrasyonu.
 * Dokuman: https://dev.paytr.com/iframe-api
 *
 * Gerekli sistem ayarlari: paytr_merchant_id, paytr_merchant_key, paytr_merchant_salt
 */
class PayTrPaymentProvider implements PaymentProvider
{
    public function initiate(Payment $payment): array
    {
        $merchantId = (string) SystemSetting::get('paytr_merchant_id', '');
        $merchantKey = (string) SystemSetting::get('paytr_merchant_key', '');
        $merchantSalt = (string) SystemSetting::get('paytr_merchant_salt', '');

        if ($merchantId === '' || $merchantKey === '' || $merchantSalt === '') {
            return ['type' => 'manual', 'value' => '<div class="alert alert-warning">Online ödeme yapılandırılmamış. Lütfen havale/EFT seçeneğini kullanın.</div>'];
        }

        $business = $payment->business;
        $email = $business->email ?: 'info@bookibris.com';
        $amountKurus = (int) round(((float) $payment->amount) * 100);
        $userBasket = base64_encode(json_encode([[
            'BooKıbrıs Abonelik', number_format((float) $payment->amount, 2, '.', ''), 1,
        ]]));
        $merchantOid = 'RSR'.$payment->id.'T'.time();
        $callbackUrl = route('payment.callback', $payment->token);

        $hashStr = $merchantId.request()->ip().$merchantOid.$email.$amountKurus.$userBasket.'0'.'0'.$payment->currency.'0';
        $paytrToken = base64_encode(hash_hmac('sha256', $hashStr.$merchantSalt, $merchantKey, true));

        $response = Http::asForm()->timeout(20)->post('https://www.paytr.com/odeme/api/get-token', [
            'merchant_id' => $merchantId,
            'user_ip' => request()->ip(),
            'merchant_oid' => $merchantOid,
            'email' => $email,
            'payment_amount' => $amountKurus,
            'paytr_token' => $paytrToken,
            'user_basket' => $userBasket,
            'debug_on' => config('app.debug') ? 1 : 0,
            'no_installment' => 0,
            'max_installment' => 0,
            'user_name' => $business->name,
            'user_address' => $business->address ?: '-',
            'user_phone' => $business->phone ?: '-',
            'merchant_ok_url' => route('payment.result', ['payment' => $payment->token, 'status' => 'ok']),
            'merchant_fail_url' => route('payment.result', ['payment' => $payment->token, 'status' => 'fail']),
            'timeout_limit' => 30,
            'currency' => $payment->currency === 'TRY' ? 'TL' : $payment->currency,
        ]);

        $json = $response->json();

        if (($json['status'] ?? '') === 'success') {
            $payment->forceFill([
                'provider_ref' => $merchantOid,
                'payload' => ['iframe_token' => $json['token']],
            ])->save();

            return [
                'type' => 'html',
                'value' => '<iframe src="https://www.paytr.com/odeme/guvenli/'.$json['token'].'" id="paytriframe" frameborder="0" scrolling="no" style="width:100%;min-height:600px"></iframe>',
            ];
        }

        Log::warning('PayTR token alinamadi', ['response' => $json]);

        return ['type' => 'manual', 'value' => '<div class="alert alert-danger">Ödeme başlatılamadı. Lütfen daha sonra tekrar deneyin.</div>'];
    }

    public function verifyCallback(Request $request, Payment $payment): bool
    {
        $merchantKey = (string) SystemSetting::get('paytr_merchant_key', '');
        $merchantSalt = (string) SystemSetting::get('paytr_merchant_salt', '');

        $hash = base64_encode(hash_hmac(
            'sha256',
            $request->input('merchant_oid').$merchantSalt.$request->input('status').$request->input('total_amount'),
            $merchantKey,
            true
        ));

        // Imza dogrulamasi: sahte callback'leri reddet
        if (! hash_equals($hash, (string) $request->input('hash'))) {
            Log::warning('PayTR callback imza hatasi', ['payment_id' => $payment->id]);

            return false;
        }

        return $request->input('status') === 'success'
            && $request->input('merchant_oid') === $payment->provider_ref;
    }
}
