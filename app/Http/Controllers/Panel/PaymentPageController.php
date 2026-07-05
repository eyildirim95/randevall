<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Odeme sayfasi token ile erisilir; oturum gerektirmez
 * (odeme linki e-posta ile de paylasilabilir).
 */
class PaymentPageController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function show(Payment $payment): View
    {
        $initiation = $payment->isPaid()
            ? ['type' => 'manual', 'value' => '<div class="alert alert-success">Bu ödeme zaten tamamlanmış.</div>']
            : $this->payments->provider($payment->provider)->initiate($payment);

        return view('payments.show', [
            'payment' => $payment,
            'initiation' => $initiation,
        ]);
    }

    /** Saglayici sunucu bildirimi (imza dogrulamali). */
    public function callback(Payment $payment, Request $request): Response
    {
        $ok = $this->payments->handleCallback($request, $payment);

        // PayTR "OK" bekler
        return response($ok ? 'OK' : 'FAIL', $ok ? 200 : 400);
    }

    public function result(Payment $payment, Request $request): View
    {
        return view('payments.result', [
            'payment' => $payment->fresh(),
            'status' => $request->query('status') === 'ok' ? 'ok' : 'fail',
        ]);
    }
}
