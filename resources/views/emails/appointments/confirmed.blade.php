<x-mail::message>
# Merhaba {{ $appointment->customer->name }},

@if($appointment->status->value === 'pending')
**{{ $appointment->business->name }}** işletmesine randevu talebiniz alındı. Onaylandığında tekrar bilgilendirileceksiniz.
@else
**{{ $appointment->business->name }}** işletmesindeki randevunuz onaylandı. 🎉
@endif

| | |
|---|---|
| **Hizmet** | {{ $appointment->service?->name ?? '—' }} |
| **Personel** | {{ $appointment->staff?->name ?? '—' }} |
| **Tarih** | {{ $appointment->starts_at->translatedFormat('d F Y l') }} |
| **Saat** | {{ $appointment->starts_at->format('H:i') }} |

<x-mail::button :url="route('appointment.public.show', $appointment->public_token)">
Randevumu Görüntüle
</x-mail::button>

Görüşmek üzere,<br>
{{ $appointment->business->name }}
</x-mail::message>
