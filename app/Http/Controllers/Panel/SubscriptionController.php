<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Business $business, Request $request): View|RedirectResponse
    {
        // Plan secimi gonderildiyse odeme baslat
        if ($request->isMethod('get') && $request->filled('plan')) {
            return $this->startPayment($business, $request);
        }

        return view('panel.subscription', [
            'business' => $business,
            'plans' => SubscriptionPlan::query()->active()->get(),
            'payments' => Payment::query()
                ->where('business_id', $business->id)
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    private function startPayment(Business $business, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::exists('subscription_plans', 'slug')->where('is_active', true)],
            'period' => ['required', Rule::in(['monthly', 'yearly'])],
            'provider' => ['nullable', Rule::in(['manual', 'paytr'])],
        ]);

        $plan = SubscriptionPlan::query()->where('slug', $data['plan'])->firstOrFail();

        $payment = app(PaymentService::class)->createForPlan(
            $business,
            $plan,
            $data['period'],
            $data['provider'] ?? 'manual',
        );

        return redirect()->route('payment.show', $payment->token);
    }
}
