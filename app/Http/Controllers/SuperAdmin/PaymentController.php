<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $payments = Payment::query()
            ->with(['business', 'subscription.plan'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $totals = [
            'paid' => (float) Payment::query()->where('status', 'paid')->sum('amount'),
            'pending' => (float) Payment::query()->where('status', 'pending')->sum('amount'),
        ];

        return view('superadmin.payments', compact('payments', 'totals', 'status'));
    }

    /** Havale/EFT odemesini manuel onayla. */
    public function markPaid(Payment $payment, PaymentService $payments, Request $request): RedirectResponse
    {
        $request->validate(['confirm' => ['required', Rule::in(['1'])]]);

        if ($payment->isPaid()) {
            return back()->withErrors(['payment' => 'Bu ödeme zaten onaylı.']);
        }

        $payments->markPaid($payment, ['approved_by' => $request->user()->id, 'approved_manually' => true]);

        return back()->with('success', 'Ödeme onaylandı, abonelik aktifleştirildi.');
    }
}
