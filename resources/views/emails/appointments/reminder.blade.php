<x-mail::message>
# Merhaba {{ $appointment->customer->name }},

**{{ $appointment->business->name }}** işletmesindeki yaklaşan randevunuzu hatırlatırız. ⏰

| | |
|---|---|
| **Hizmet** | {{ $appointment->service?->name ?? '—' }} |
| **Personel** | {{ $appointment->staff?->name ?? '—' }} |
| **Tarih** | {{ $appointment->starts_at->translatedFormat('d F Y l') }} |
| **Saat** | {{ $appointment->starts_at->format('H:i') }} |

<x-mail::button :url="route('appointment.public.show', $appointment->public_token)">
Randevumu Görüntüle / İptal Et
</x-mail::button>

Görüşmek üzere,<br>
{{ $appointment->business->name }}
</x-mail::message>
