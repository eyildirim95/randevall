<?php

namespace App\Services\Notifications;

use App\Jobs\SendSmsMessage;
use App\Jobs\SendWhatsAppMessage;
use App\Mail\AppointmentCancelledMail;
use App\Mail\AppointmentConfirmedMail;
use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;

/**
 * Randevu bildirimleri: WhatsApp + SMS + e-posta.
 * Gonderimler kuyruga atilir; web istegini bloklamaz.
 */
class AppointmentNotifier
{
    public function confirmation(Appointment $appointment): void
    {
        $business = $appointment->business;
        $customer = $appointment->customer;
        $text = $this->confirmationText($appointment);

        if ($business->whatsapp_enabled && $customer->phone) {
            SendWhatsAppMessage::dispatch(
                $business->id,
                $customer->phone,
                $text,
                'appointment_confirmation',
                $appointment->id,
            );
        }

        if ($business->sms_enabled && $customer->phone) {
            SendSmsMessage::dispatch(
                $business->id,
                $customer->phone,
                $text,
                'appointment_confirmation',
                $appointment->id,
            );
        }

        if ($business->email_notifications_enabled && $customer->email) {
            Mail::to($customer->email)->queue(new AppointmentConfirmedMail($appointment));
        }

        $appointment->forceFill(['confirmation_sent_at' => now()])->saveQuietly();
    }

    public function reminder(Appointment $appointment): void
    {
        $business = $appointment->business;
        $customer = $appointment->customer;
        $text = $this->reminderText($appointment);

        if ($business->whatsapp_enabled && $customer->phone) {
            SendWhatsAppMessage::dispatch(
                $business->id,
                $customer->phone,
                $text,
                'appointment_reminder',
                $appointment->id,
            );
        }

        if ($business->sms_enabled && $customer->phone) {
            SendSmsMessage::dispatch(
                $business->id,
                $customer->phone,
                $text,
                'appointment_reminder',
                $appointment->id,
            );
        }

        if ($business->email_notifications_enabled && $customer->email) {
            Mail::to($customer->email)->queue(new AppointmentReminderMail($appointment));
        }

        $appointment->forceFill(['reminder_sent_at' => now()])->saveQuietly();
    }

    public function cancellation(Appointment $appointment): void
    {
        $business = $appointment->business;
        $customer = $appointment->customer;
        $text = $this->cancellationText($appointment);

        if ($business->whatsapp_enabled && $customer->phone) {
            SendWhatsAppMessage::dispatch(
                $business->id,
                $customer->phone,
                $text,
                'appointment_cancelled',
                $appointment->id,
            );
        }

        if ($business->sms_enabled && $customer->phone) {
            SendSmsMessage::dispatch(
                $business->id,
                $customer->phone,
                $text,
                'appointment_cancelled',
                $appointment->id,
            );
        }

        if ($business->email_notifications_enabled && $customer->email) {
            Mail::to($customer->email)->queue(new AppointmentCancelledMail($appointment));
        }
    }

    /** Tamamlanan randevu sonrasi degerlendirme daveti. */
    public function ratingRequest(Appointment $appointment): void
    {
        $business = $appointment->business;
        $customer = $appointment->customer;

        if (! $customer->phone) {
            return;
        }

        $text = sprintf(
            "Merhaba %s! %s ziyaretiniz için teşekkürler. 🙏\n\nDeneyiminizi 30 saniyede değerlendirir misiniz?\n%s",
            $customer->name,
            $business->name,
            route('appointment.public.rate', $appointment->public_token),
        );

        if ($business->whatsapp_enabled) {
            SendWhatsAppMessage::dispatch(
                $business->id,
                $customer->phone,
                $text,
                'rating_request',
                $appointment->id,
            );
        }

        if ($business->sms_enabled) {
            SendSmsMessage::dispatch(
                $business->id,
                $customer->phone,
                $text,
                'rating_request',
                $appointment->id,
            );
        }
    }

    private function confirmationText(Appointment $a): string
    {
        $status = $a->status->value === 'pending'
            ? "Randevu talebiniz alındı, onaylandığında bilgilendirileceksiniz."
            : "Randevunuz onaylandı.";

        return sprintf(
            "Merhaba %s! %s\n\n📍 %s\n💈 %s\n👤 %s\n🗓 %s\n\nRandevunuzu görüntülemek veya iptal etmek için: %s",
            $a->customer->name,
            $status,
            $a->business->name,
            $a->service?->name ?? 'Hizmet',
            $a->staff?->name ?? '-',
            $a->starts_at->translatedFormat('d F Y l H:i'),
            route('appointment.public.show', $a->public_token),
        );
    }

    private function reminderText(Appointment $a): string
    {
        return sprintf(
            "Merhaba %s! Yarınki randevunuzu hatırlatırız. 🗓\n\n📍 %s\n💈 %s\n👤 %s\n🕐 %s\n\nDetay: %s",
            $a->customer->name,
            $a->business->name,
            $a->service?->name ?? 'Hizmet',
            $a->staff?->name ?? '-',
            $a->starts_at->translatedFormat('d F Y l H:i'),
            route('appointment.public.show', $a->public_token),
        );
    }

    private function cancellationText(Appointment $a): string
    {
        return sprintf(
            "Merhaba %s, %s işletmesindeki %s tarihli randevunuz iptal edilmiştir.\n\nYeni randevu için: %s",
            $a->customer->name,
            $a->business->name,
            $a->starts_at->translatedFormat('d F Y H:i'),
            route('booking.show', $a->business->slug),
        );
    }
}
