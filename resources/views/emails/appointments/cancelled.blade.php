<x-mail::message>
# Merhaba {{ $appointment->customer->name }},

**{{ $appointment->business->name }}** işletmesindeki **{{ $appointment->starts_at->translatedFormat('d F Y H:i') }}** tarihli randevunuz iptal edilmiştir.

@if($appointment->cancellation_reason)
> {{ $appointment->cancellation_reason }}
@endif

Dilerseniz yeni bir randevu oluşturabilirsiniz:

<x-mail::button :url="route('booking.show', $appointment->business->slug)">
Yeni Randevu Al
</x-mail::button>

{{ $appointment->business->name }}
</x-mail::message>
