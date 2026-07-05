<?php

namespace App\Services\Payments;

use App\Enums\SubscriptionStatus;
use App\Models\Business;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Payments\Contracts\PaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Abonelik odemeleri: baslatma, callback isleme, manuel onay.
 */
class PaymentService
{
    public function provider(string $name): PaymentProvider
    {
        return match ($name) {
            'paytr' => new PayTrPaymentProvider(),
            default => new ManualPaymentProvider(),
        };
    }

    /** Plan secimi sonrasi bekleyen odeme + abonelik kaydi olusturur. */
    public function createForPlan(Business $business, SubscriptionPlan $plan, string $period, string $provider = 'manual'): Payment
    {
        $price = $period === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        return DB::transaction(function () use ($business, $plan, $period, $provider, $price) {
            $subscription = Subscription::create([
                'business_id' => $business->id,
                'subscription_plan_id' => $plan->id,
                'period' => $period,
                'price' => $price,
                'currency' => $plan->currency,
                'starts_at' => now(),
                'ends_at' => $period === 'yearly' ? now()->addYear() : now()->addMonth(),
                'status' => SubscriptionStatus::PastDue->value,
            ]);

            return Payment::create([
                'business_id' => $business->id,
                'subscription_id' => $subscription->id,
                'provider' => $provider,
                'amount' => $price,
                'currency' => $plan->currency,
            ]);
        });
    }

    /** Saglayici callback'i: imza dogrulanirsa odemeyi ve aboneligi aktive eder. */
    public function handleCallback(Request $request, Payment $payment): bool
    {
        if ($payment->isPaid()) {
            return true; // idempotent
        }

        $verified = $this->provider($payment->provider)->verifyCallback($request, $payment);

        if (! $verified) {
            return false;
        }

        $this->markPaid($payment, $request->all());

        return true;
    }

    /** Odemeyi tamamla ve aboneligi/plani isletmeye isle. */
    public function markPaid(Payment $payment, array $payload = []): void
    {
        DB::transaction(function () use ($payment, $payload) {
            $payment->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
                'payload' => array_merge($payment->payload ?? [], $payload),
            ])->save();

            $subscription = $payment->subscription;

            if ($subscription) {
                $subscription->forceFill(['status' => SubscriptionStatus::Active->value])->save();

                $business = $payment->business;
                $business->forceFill([
                    'subscription_plan_id' => $subscription->subscription_plan_id,
                    'plan_expires_at' => $subscription->ends_at,
                    'trial_ends_at' => null,
                ])->save();
            }
        });
    }
}
