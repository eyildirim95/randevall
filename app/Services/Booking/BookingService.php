<?php

namespace App\Services\Booking;

use App\Enums\AppointmentStatus;
use App\Enums\TransactionType;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Transaction;
use App\Services\Loyalty\LoyaltyService;
use App\Services\Notifications\AppointmentNotifier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Randevu yasam dongusu: olusturma, tasima, durum degisimi, iptal.
 * Cifte rezervasyona karsi DB transaction + kilit kullanilir.
 */
class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly LoyaltyService $loyalty,
        private readonly AppointmentNotifier $notifier,
    ) {}

    /**
     * @param  array{customer_id?: int, customer_name?: string, customer_phone?: string, customer_email?: string,
     *               service_id: int, staff_id: int, starts_at: string|Carbon, notes?: string} $data
     * @param  bool $enforceWorkingHours Panelden olusturmada esnek olabilir; online'da daima true.
     */
    public function create(
        Business $business,
        array $data,
        string $source = 'panel',
        ?int $createdBy = null,
        bool $enforceWorkingHours = true,
        bool $notify = true,
        ?string $seriesId = null,
    ): Appointment {
        $service = Service::query()->findOrFail($data['service_id']);
        $staff = Staff::query()->findOrFail($data['staff_id']);

        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = $startsAt->copy()->addMinutes($service->totalMinutes());

        $this->assertMonthlyLimitNotReached($business, $startsAt, $source);

        return DB::transaction(function () use ($business, $data, $source, $createdBy, $service, $staff, $startsAt, $endsAt, $enforceWorkingHours, $notify, $seriesId) {
            // Ayni personel icin es zamanli yazmalari serilestir (cifte rezervasyon korumasi)
            Appointment::query()
                ->where('staff_id', $staff->id)
                ->blocking()
                ->between($startsAt, $endsAt)
                ->lockForUpdate()
                ->get();

            if (! $this->availability->isAvailable($business, $staff, $startsAt, $endsAt, null, $enforceWorkingHours)) {
                throw ValidationException::withMessages([
                    'starts_at' => 'Seçilen saat artık uygun değil. Lütfen başka bir saat seçin.',
                ]);
            }

            $customer = $this->resolveCustomer($business, $data);

            if ($customer->is_blacklisted && $source === 'online') {
                throw ValidationException::withMessages([
                    'customer_phone' => 'Bu telefon numarası ile online randevu alınamıyor. Lütfen işletmeyi arayın.',
                ]);
            }

            $appointment = new Appointment([
                'customer_id' => $customer->id,
                'staff_id' => $staff->id,
                'service_id' => $service->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'price' => $service->price,
                'notes' => $data['notes'] ?? null,
                'source' => $source,
            ]);

            $appointment->business_id = $business->id;
            $appointment->created_by = $createdBy;
            $appointment->series_id = $seriesId;
            $appointment->status = ($source === 'online' && ! $business->auto_confirm_online)
                ? AppointmentStatus::Pending
                : AppointmentStatus::Confirmed;
            $appointment->save();

            $appointment->statusLogs()->create([
                'to_status' => $appointment->status->value,
                'changed_by' => $createdBy,
                'note' => $source === 'online' ? 'Online rezervasyon' : 'Panelden oluşturuldu',
            ]);

            $customer->increment('total_appointments');

            if ($notify) {
                $this->notifier->confirmation($appointment);
            }

            // Online rezervasyonlar panelde bildirim olarak dusar
            if ($source === 'online') {
                \App\Services\Notifications\PanelNotifier::notify(
                    $business,
                    'online_booking',
                    sprintf('Yeni online randevu: %s — %s', $customer->name, $startsAt->format('d.m.Y H:i')),
                    route('panel.appointments.show', [$business, $appointment]),
                );
            }

            return $appointment;
        });
    }

    /**
     * Tekrarlayan randevu serisi: ilk randevu + belirli hafta araligiyla kopyalar.
     * Cakisan tarihler atlanir ve geri bildirilir.
     *
     * @return array{appointment: Appointment, created: int, skipped: array<int, string>}
     */
    public function createSeries(
        Business $business,
        array $data,
        int $repeatWeeks,
        int $repeatCount,
        ?int $createdBy = null,
        bool $enforceWorkingHours = true,
    ): array {
        $seriesId = (string) \Illuminate\Support\Str::uuid();
        $baseStart = Carbon::parse($data['starts_at']);

        $first = $this->create($business, $data, 'panel', $createdBy, $enforceWorkingHours, true, $seriesId);

        $created = 1;
        $skipped = [];

        for ($i = 1; $i < $repeatCount; $i++) {
            $nextStart = $baseStart->copy()->addWeeks($repeatWeeks * $i);

            try {
                $this->create(
                    $business,
                    [...$data, 'starts_at' => $nextStart, 'customer_id' => $first->customer_id],
                    'panel',
                    $createdBy,
                    $enforceWorkingHours,
                    notify: false, // seri kopyalari icin ayri bildirim gonderilmez
                    seriesId: $seriesId,
                );
                $created++;
            } catch (ValidationException) {
                $skipped[] = $nextStart->format('d.m.Y H:i');
            }
        }

        return ['appointment' => $first, 'created' => $created, 'skipped' => $skipped];
    }

    /** Plan bazli aylik randevu limiti (0 = sinirsiz). */
    private function assertMonthlyLimitNotReached(Business $business, Carbon $startsAt, string $source): void
    {
        $limit = (int) ($business->plan?->max_appointments_per_month ?? 0);

        if ($limit <= 0) {
            return;
        }

        // Yalnizca kotayi tuketen randevular sayilir (iptal/gelmedi haric)
        $count = Appointment::query()
            ->blocking()
            ->whereBetween('starts_at', [$startsAt->copy()->startOfMonth(), $startsAt->copy()->endOfMonth()])
            ->count();

        if ($count >= $limit) {
            throw ValidationException::withMessages([
                'starts_at' => $source === 'online'
                    ? 'Bu işletme şu anda yeni randevu kabul edemiyor. Lütfen işletmeyi arayın.'
                    : "Aylık randevu limitiniz ({$limit}) doldu. Daha yüksek bir plana geçerek limitinizi artırabilirsiniz.",
            ]);
        }
    }

    public function move(Appointment $appointment, Carbon $startsAt, ?int $staffId = null, ?Carbon $endsAt = null): Appointment
    {
        $business = $appointment->business;
        $staff = $staffId ? Staff::query()->findOrFail($staffId) : $appointment->staff;

        $endsAt ??= $startsAt->copy()->addMinutes($appointment->durationMinutes());

        return DB::transaction(function () use ($appointment, $business, $staff, $startsAt, $endsAt) {
            Appointment::query()
                ->where('staff_id', $staff->id)
                ->blocking()
                ->between($startsAt, $endsAt)
                ->lockForUpdate()
                ->get();

            // Panelden tasima: calisma saati disina da izin ver, cakismaya izin verme
            if (! $this->availability->isAvailable($business, $staff, $startsAt, $endsAt, $appointment->id, false)) {
                throw ValidationException::withMessages([
                    'starts_at' => 'Seçilen aralık başka bir randevu veya kapatma ile çakışıyor.',
                ]);
            }

            $appointment->forceFill([
                'staff_id' => $staff->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ])->save();

            return $appointment;
        });
    }

    public function updateStatus(Appointment $appointment, AppointmentStatus $newStatus, ?int $changedBy = null, ?string $note = null): Appointment
    {
        $old = $appointment->status;

        if ($old === $newStatus) {
            return $appointment;
        }

        DB::transaction(function () use ($appointment, $old, $newStatus, $changedBy, $note) {
            $appointment->forceFill(['status' => $newStatus])->save();

            $appointment->statusLogs()->create([
                'from_status' => $old->value,
                'to_status' => $newStatus->value,
                'changed_by' => $changedBy,
                'note' => $note,
            ]);

            $customer = $appointment->customer;

            if ($newStatus === AppointmentStatus::Completed) {
                $customer->increment('completed_appointments');
                $customer->increment('total_spent', (float) $appointment->price);

                // Gelir kaydi otomatik olusur
                if ((float) $appointment->price > 0 && ! Transaction::query()->where('appointment_id', $appointment->id)->exists()) {
                    $transaction = new Transaction([
                        'type' => TransactionType::Income,
                        'category' => 'Hizmet',
                        'amount' => $appointment->price,
                        'description' => sprintf('%s — %s', $appointment->service?->name ?? 'Hizmet', $customer->name),
                        'transaction_date' => $appointment->starts_at->toDateString(),
                        'appointment_id' => $appointment->id,
                        'customer_id' => $customer->id,
                    ]);
                    $transaction->business_id = $appointment->business_id;
                    $transaction->created_by = $changedBy;
                    $transaction->save();
                }

                $this->loyalty->awardForAppointment($appointment);
            }

            if ($newStatus === AppointmentStatus::NoShow) {
                $customer->increment('no_show_count');
            }
        });

        if ($old === AppointmentStatus::Pending && $newStatus === AppointmentStatus::Confirmed) {
            $this->notifier->confirmation($appointment->fresh(['business', 'customer', 'service', 'staff']));
        }

        // Tamamlanan randevu icin degerlendirme daveti (islem sonrasi)
        if ($newStatus === AppointmentStatus::Completed) {
            $this->notifier->ratingRequest($appointment);
        }

        return $appointment;
    }

    public function cancel(Appointment $appointment, ?string $reason = null, ?int $cancelledBy = null, bool $notifyCustomer = true): Appointment
    {
        DB::transaction(function () use ($appointment, $reason, $cancelledBy) {
            $old = $appointment->status;

            $appointment->forceFill([
                'status' => AppointmentStatus::Cancelled,
                'cancellation_reason' => $reason,
            ])->save();

            $appointment->statusLogs()->create([
                'from_status' => $old->value,
                'to_status' => AppointmentStatus::Cancelled->value,
                'changed_by' => $cancelledBy,
                'note' => $reason,
            ]);
        });

        if ($notifyCustomer) {
            $this->notifier->cancellation($appointment);
        }

        // Ayni gunu bekleyenlere "yer acildi" haberi ver
        \App\Jobs\NotifyWaitingList::dispatch(
            $appointment->business_id,
            $appointment->starts_at->toDateString(),
            $appointment->staff_id,
        );

        return $appointment;
    }

    /** Telefona gore musteri bul veya olustur. */
    private function resolveCustomer(Business $business, array $data): Customer
    {
        if (! empty($data['customer_id'])) {
            return Customer::query()->findOrFail($data['customer_id']);
        }

        $rawPhone = trim($data['customer_phone'] ?? '');

        if ($rawPhone === '') {
            throw ValidationException::withMessages(['customer_phone' => 'Telefon numarası gerekli.']);
        }

        // E.164'e normalize et: 0533.., +90533.., 90533.. ayni musteriye esler (mukerrer kayit onlenir)
        $phone = \App\Services\Messaging\PhoneNumber::e164($rawPhone);

        $customer = Customer::query()->where('phone', $phone)->first();

        if ($customer) {
            // Ad guncellemesi: yalnizca bos ise doldur
            if (! empty($data['customer_name']) && $customer->name === '') {
                $customer->update(['name' => $data['customer_name']]);
            }

            return $customer;
        }

        // Plan bazli musteri limiti (0 = sinirsiz)
        $customerLimit = (int) ($business->plan?->max_customers ?? 0);

        if ($customerLimit > 0 && Customer::query()->count() >= $customerLimit) {
            throw ValidationException::withMessages([
                'customer_phone' => "Müşteri limitiniz ({$customerLimit}) doldu. Daha yüksek bir plana geçebilirsiniz.",
            ]);
        }

        $customer = new Customer([
            'name' => $data['customer_name'] ?? 'Misafir',
            'phone' => $phone,
            'email' => $data['customer_email'] ?? null,
        ]);
        $customer->business_id = $business->id;
        $customer->kvkk_consent_at = now();
        $customer->save();

        return $customer;
    }
}
