<?php

namespace App\Services\Loyalty;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Sadik musteri sistemi: tamamlanan randevudan puan kazanimi,
 * odul kullanimi ve manuel duzeltmeler.
 */
class LoyaltyService
{
    public function awardForAppointment(Appointment $appointment): void
    {
        $business = $appointment->business;

        if (! $business->loyalty_enabled) {
            return;
        }

        // Ayni randevu icin iki kez puan verilmesin
        $already = LoyaltyTransaction::query()
            ->where('appointment_id', $appointment->id)
            ->where('reason', 'appointment_completed')
            ->exists();

        if ($already) {
            return;
        }

        $points = (int) $business->loyalty_points_per_visit;

        if ($points <= 0) {
            return;
        }

        $this->apply(
            $appointment->customer,
            $points,
            'appointment_completed',
            'Tamamlanan randevu puanı',
            $appointment->id,
        );
    }

    public function redeem(Customer $customer, ?int $createdBy = null): LoyaltyTransaction
    {
        $business = $customer->business;
        $threshold = (int) $business->loyalty_redeem_threshold;

        if ($threshold <= 0 || $customer->loyalty_points < $threshold) {
            throw new \InvalidArgumentException('Müşterinin yeterli puanı yok.');
        }

        return $this->apply(
            $customer,
            -$threshold,
            'redeemed',
            $business->loyalty_reward_description ?: 'Sadakat ödülü kullanıldı',
            null,
            $createdBy,
        );
    }

    public function adjust(Customer $customer, int $points, ?string $description, ?int $createdBy = null): LoyaltyTransaction
    {
        return $this->apply($customer, $points, 'manual', $description ?: 'Manuel puan düzeltmesi', null, $createdBy);
    }

    private function apply(Customer $customer, int $points, string $reason, ?string $description, ?int $appointmentId = null, ?int $createdBy = null): LoyaltyTransaction
    {
        return DB::transaction(function () use ($customer, $points, $reason, $description, $appointmentId, $createdBy) {
            $transaction = new LoyaltyTransaction([
                'customer_id' => $customer->id,
                'points' => $points,
                'reason' => $reason,
                'description' => $description,
                'appointment_id' => $appointmentId,
            ]);
            $transaction->business_id = $customer->business_id;
            $transaction->created_by = $createdBy;
            $transaction->save();

            $customer->increment('loyalty_points', $points);

            return $transaction;
        });
    }
}
