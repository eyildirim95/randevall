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
                $this->activateSubscription($subscription, $payload);
            }
        });
    }

    /**
     * Super admin: odeme bekletmeden manuel abonelik tanimlar.
     *
     * @param  array{starts_at?: \Carbon\CarbonInterface, ends_at?: \Carbon\CarbonInterface, amount?: float|string, note?: string, granted_by?: int}  $options
     */
    public function grantManualSubscription(
        Business $business,
        SubscriptionPlan $plan,
        string $period,
        array $options = [],
    ): Subscription {
        return DB::transaction(function () use ($business, $plan, $period, $options) {
            $startsAt = isset($options['starts_at'])
                ? \Carbon\Carbon::parse($options['starts_at'])
                : now();

            $price = $options['amount'] ?? ($period === 'yearly' ? $plan->price_yearly : $plan->price_monthly);

            $endsAt = isset($options['ends_at'])
                ? \Carbon\Carbon::parse($options['ends_at'])
                : ($period === 'yearly' ? $startsAt->copy()->addYear() : $startsAt->copy()->addMonth());

            $this->expireActiveSubscriptions($business);

            $subscription = Subscription::create([
                'business_id' => $business->id,
                'subscription_plan_id' => $plan->id,
                'period' => $period,
                'price' => $price,
                'currency' => $plan->currency,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => SubscriptionStatus::Active->value,
            ]);

            $payment = new Payment([
                'business_id' => $business->id,
                'subscription_id' => $subscription->id,
                'provider' => 'manual',
                'amount' => $price,
                'currency' => $plan->currency,
            ]);
            $payment->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
                'payload' => array_filter([
                    'granted_manually' => true,
                    'granted_by' => $options['granted_by'] ?? null,
                    'note' => $options['note'] ?? null,
                ]),
            ])->save();

            $this->activateSubscription($subscription, ['granted_manually' => true]);

            return $subscription->fresh(['plan']);
        });
    }

    private function expireActiveSubscriptions(Business $business): void
    {
        Subscription::query()
            ->where('business_id', $business->id)
            ->where('status', SubscriptionStatus::Active->value)
            ->update(['status' => SubscriptionStatus::Expired->value]);
    }

    private function activateSubscription(Subscription $subscription, array $payload = []): void
    {
        $subscription->forceFill(['status' => SubscriptionStatus::Active->value])->save();

        $business = $subscription->business;
        $business->forceFill([
            'subscription_plan_id' => $subscription->subscription_plan_id,
            'plan_expires_at' => $subscription->ends_at,
            'trial_ends_at' => null,
        ])->save();
    }
}
