<?php

namespace App\Services\Payments\Contracts;

use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentProvider
{
    /**
     * Odeme baslatir; kullanicinin yonlendirilecegi URL'i
     * veya gosterilecek HTML formunu dondurur.
     *
     * @return array{type: 'redirect'|'html'|'manual', value: string}
     */
    public function initiate(Payment $payment): array;

    /**
     * Saglayici callback'ini dogrular (imza kontrolu dahil).
     * Basarili ise true doner; Payment guncellemesini PaymentService yapar.
     */
    public function verifyCallback(Request $request, Payment $payment): bool;
}
