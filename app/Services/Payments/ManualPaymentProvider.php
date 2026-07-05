<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\SystemSetting;
use App\Services\Payments\Contracts\PaymentProvider;
use Illuminate\Http\Request;

/**
 * Havale/EFT ile odeme: banka bilgileri gosterilir,
 * super admin odemeyi panelden manuel onaylar.
 */
class ManualPaymentProvider implements PaymentProvider
{
    public function initiate(Payment $payment): array
    {
        $iban = SystemSetting::get('bank_iban', '');
        $bankName = SystemSetting::get('bank_name', '');
        $accountHolder = SystemSetting::get('bank_account_holder', '');

        return [
            'type' => 'manual',
            'value' => view('payments.manual-instructions', [
                'payment' => $payment,
                'iban' => $iban,
                'bankName' => $bankName,
                'accountHolder' => $accountHolder,
            ])->render(),
        ];
    }

    public function verifyCallback(Request $request, Payment $payment): bool
    {
        // Manuel odemede callback yok; super admin onayi gerekir.
        return false;
    }
}
